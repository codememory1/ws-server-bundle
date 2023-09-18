# WebSocket Server Bundle
#### This pandle is a simple solution to raise a WebSocket Server in a matter of seconds, by default it is supported by Swoole as one of the most powerful frameworks

### Install
```shell 
$ composer require codememory/ws-server-bundle
```

#### Register this bundle if symfony flex didn't do it for you
```php
// config/bundles.php

<?php

return [
    ...
    Codememory\WebSocketServerBundle\WebSocketServerBundle::class => ['all' => true]
];

```

### Configuration
- __server__:
  - __adapter__: Service adapter if you decide to implement your own server. Default: "*Swoole*", Default Service: "*WebSocketServerBundle::DEFAULT_SERVER_SERVICE*"
  - __protocol__: Server Protocol. Default: "*websocket*"
  - __host__: Server Host. Default: "*127.0.0.1*"
  - __port__: Server port. Default: "*8079*"


- __event_listeners__: Set of message listeners
  - { event: "TEST", listener: "App\WebSocketEventListeners\TestHandler" }: Example event listener


- __config__: Server configuration, depending on the server, the default is Swoole, so look at the swoole documentation. Default: "*[]*"


### Default waiting message

```json
{
  "event": "MESSAGE_EVENT_NAME",
  "data": {}
}
```

### An example implementation of an event listener handler

```php
namespace App\WebSocket\EventListeners;

use Codememory\WebSocketServerBundle\Interfaces\MessageEventListenerInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class TestHandler implements MessageEventListenerInterface 
{
    public function handle(ServerInterface $server, MessageInterface $message) : void
    {
        // Reply to a message with event "RESPONSE_EVENT"
        $server->sendMessage($message->getSenderConnectionID(), 'RESPONSE_EVENT', [
            'message' => 'Hello World'
        ]);
    }
}

// Don't forget to register this listeners in the bundle configuration
```

### Registering an event listener

```yaml
# config/packages/codememory_ws_server.yaml
codememory_ws_server:
  event_listeners:
    - { event: 'TEST', listener: App\WebSocket\EventListeners\TestHandler }
```

### Events
- __codememory.ws_server.connection_open__: Connection open
  - class: Codememory\WebSocketServerBundle\Event\ConnectionOpenEvent


- __codememory.ws_server.connection_closed__: Connection closed
  - class: Codememory\WebSocketServerBundle\Event\ConnectionClosedEvent


- __codememory.ws_server.message__: Received a new message from the connection
  - class: Codememory\WebSocketServerBundle\Event\MessageEvent


- __codememory.ws_server.message_handler_exception__: Exception handler during message listener call
  - class: Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent


- __codememory.ws_server.message_sent__: A message has been sent to the connection
  - class: Codememory\WebSocketServerBundle\Event\MessageSentEvent

- __codememory.ws_server.start_server__: The server starts up. Usually, in this event, the necessary child processes are added
  - class: Codememory\WebSocketServerBundle\Event\StartServerEvent


- __codememory.ws_server.start_worker__: Server startup worker starts start
  - class: Codememory\WebSocketServerBundle\Event\StartWorkerEvent


### Let's implement the task of sending messages to specific users by their ID in the database

#### First, let's create a Listener for the Open Connection event and save all connections in redis
```php
<?php

use Codememory\WebSocketServerBundle\Event\ConnectionOpenEvent;
use Predis\Client;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(ConnectionOpenEvent::NAME, 'onOpen')]
readonly class SaveConnectionToRedisEventListener
{
    public function __construct(
        private Client $client
    ) {
    }

    public function onOpen(ConnectionOpenEvent $event): void
    {
        // We save the new connection in the hash table
        $this->client->hset('websocket:connections', $event->connectionID, json_encode([
            'connection_id' => $event->connectionID,
            'websocket_sec_key' => $event->secWebsocketKey
        ]));
    }
}
```

#### Now let's create an EventListener on the CONNECT message

> Please note that in the current primer we will not use JWT, but will immediately pick up the user ID. In your example, instead of passing user_id, you can pass a JWT token, check its validity and get user_id from it

```php
<?php

use Codememory\WebSocketServerBundle\Interfaces\MessageEventListenerInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Predis\Client;

readonly class ConnectEventListener implements MessageEventListenerInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    public function handle(ServerInterface $server, MessageInterface $message): void
    {
        $data = $message->getData();
        
        if (array_key_exists('user_id', $data) && is_int($data['user_id'])) {
          // Here we bind the user to the ws connection and save it to a new hash table
          $this->client->hset($this->buildKey($data['user_id']), $message->getSenderConnectionID(), json_encode([
              'timestamp' => time()
          ]));
        }
    }
    
    private function buildKey(int $userId): string
    {
        return "websocket:user:$userId:connections";
    }
}

// Don't worry about registering this EventListener in codememory_ws_server.yaml
```

#### Now let's create a manager that will save messages to a queue that need to be sent to a specific user
```php
<?php

use Predis\Client;

final readonly class WebSocketMessageQueueManager
{
    public const HASH_TABLE_NAME = 'websocket:queue:messages';

    public function __construct(
        private Client $client
    ) {
    }

    public function sendMessage(int $userId, string $event, array $data): void
    {
        // We get all ws connections by user ID
        $connections = $this->client->hgetall("websocket:user:$userId:connections");

        foreach ($connections as $id => $userConnectionData) {
            // Receiving information about the connection by connection identifier
            $connection = $this->client->hget('websocket:connections', $id);

            if (null !== $connection) {
                $connectionData = json_decode($connection, true);

                // We save the message in a hash table, as the key we indicate the connection ID to which we need to send and its websocket-sec-key (to ensure security)
                $this->client->hset(
                    self::HASH_TABLE_NAME,
                    $this->buildMessageField($id, $connectionData['websocket_sec_key']),
                    json_encode([
                      'event' => $event,
                      'data' => $data
                    ])
                );
            }
        }
    }
    
    private function buildMessageField(int $connectionId, string $webSocketSecKey): string
    {
        return "{$connectionId}_{$webSocketSecKey}";
    }
}
```

#### And as a final step, we will add a process that will watch redis and check the existence of messages that need to be sent to the user
```php
<?php

use App\Services\WebSocketMessageQueueManager;
use Codememory\WebSocketServerBundle\Event\StartServerEvent;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Throwable;

#[AsEventListener(StartServerEvent::NAME, 'onStart')]
final readonly class ProcessForSendingMessagesFromQueueEventListener
{
    public function __construct(
        private Client $client,
        private LoggerInterface $logger
    ) {
    }

    public function onStart(StartServerEvent $event): void
    {
        try {
            $event->server->addProcess(function () use ($event) {
                // Receive all messages from the queue
                $messages = $this->client->hgetall(WebSocketMessageQueueManager::HASH_TABLE_NAME);
                
                foreach ($messages as $for => $message) {
                    [$connectionID, $webSocketSecKey] = explode('_', $for);
                    
                    // We check that the message that was added to the queue belongs to the same connection that is connected
                    if ($this->connectionCheck($connectionID, $webSocketSecKey)) {
                        $message = json_decode($message, true);

                        $event->server->sendMessage($connectionID, $message['event'], $message['data']);
                        
                        // We remove the message from the queue so that it is not sent again
                        $this->client->hdel(WebSocketMessageQueueManager::HASH_TABLE_NAME, [$for]);
                    }
                }
            });
        } catch (Throwable $e) {
            $this->logger->critical($e, [
                'origin' => self::class,
                'detail' => 'An error occurred while adding a process to send messages from a queue.'
            ]);
        }
    }

    private function connectionCheck(int $connectionID, string $webSocketSecKey): bool
    {
        $connection = $this->client->hget('websocket:connections', $connectionID);

        if (null !== $connection) {
            $connectionData = json_decode($connection, true);

            if ($connectionData['websocket_sec_key'] === $webSocketSecKey) {
                return true;
            }
        }

        return false;
    }
}
```

#### That's all, this example is not ideal and requires changes and depends on your needs

> Now if we want to send a message to a user with ID 500, we just need to use our manager anywhere in the code and the sendMessage method