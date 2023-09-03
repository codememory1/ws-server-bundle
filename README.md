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


- __converters__:
  - - __message__: Service for converting a message from a client. Default: "from json to array", Default Service: "*WebSocketServerBundle::DEFAULT_MESSAGE_CONVERTER_SERVICE*"


- __extractors__: Please note, if you are creating a custom message converter, then you need to override all extractors
  - __message_event__: Extractor to get the name of the event from the message. Default Service: "*WebSocketServerBundle::DEFAULT_MESSAGE_EVENT_EXTRACTOR_SERVICE*"
  - __message_headers__: Extractor getting header from message. Default Service: "*WebSocketServerBundle::DEFAULT_MESSAGE_HEADERS_EXTRACTOR_SERVICE*"
  - __message_input_data__: Extractor receiving input data. Default Service: "*WebSocketServerBundle::DEFAULT_MESSAGE_INPUT_DATA_EXTRACTOR_SERVICE*"


- __storages__: 
  - __connection__: Storage service, for storing connections. Default "*Redis*", Default service: "*WebSocketServerBundle::DEFAULT_CONNECTION_STORAGE_SERVICE*"
  - __message_queue__: Storage service, for storing queue messages. Default "*Redis*", Default service: "*WebSocketServerBundle::DEFAULT_MESSAGE_QUEUE_STORAGE_SERVICE*"


- __event_listeners__: Set of message listeners
  - { event: "TEST", listener: "App\WebSocketEventListeners\TestHandler" }: Example event listener


- __config__: Server configuration, depending on the server, the default is Swoole, so look at the swoole documentation. Default: "*[]*"


### Default waiting message

```json
{
  "event": "MESSAGE_EVENT_NAME",
  "headers": {},
  "input_data": {}
}
```

### An example implementation of an event listener handler

```php
namespace App\WebSocket\EventListeners;

use Codememory\WebSocketServerBundle\Interfaces\MessageEventHandlerInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class TestHandler implements MessageEventHandlerInterface 
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
- __codememory.ws_server.added_message_to_queue__: The message to send to the connection has been added to the queue


- __codememory.ws_server.connection_open__: Connection open
  - class: Codememory\WebSocketServerBundle\Event\AddedMessageToQueueEvent


- __codememory.ws_server.connection_closed__: Connection closed
  - class: Codememory\WebSocketServerBundle\Event\ConnectionClosedEvent


- __codememory.ws_server.message__: Received a new message from the connection
  - class: Codememory\WebSocketServerBundle\Event\MessageEvent


- __codememory.ws_server.message_handler_exception__: Exception handler during message listener call
  - class: Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent


- __codememory.ws_server.message_sent__: A message has been sent to the connection
  - class: Codememory\WebSocketServerBundle\Event\MessageSentEvent


- __codememory.ws_server.remove_connection__: Removing a connection from the connection store
  - class: Codememory\WebSocketServerBundle\Event\RemoveConnectionEvent


- __codememory.ws_server.start_server__: The server starts up. Usually, in this event, the necessary child processes are added
  - class: Codememory\WebSocketServerBundle\Event\StartServerEvent


- __codememory.ws_server.start_worker__: Server startup worker starts start
  - class: Codememory\WebSocketServerBundle\Event\StartWorkerEvent


### Custom message converter
> Note! If you create your own message converter, you need to override all message extractors

> If your converter returns an array of default extractor keys, you don't need to redefine extractors

_So! Let's create a message converter where we expect the message to be json. This is an example of a default converter_
```php
namespace App\WebSocket\Converters\Message;

use Codememory\WebSocketServerBundle\Interfaces\MessageConverterInterface;

final class FromSerializeToArrayMessageConverter implements MessageConverterInterface {
    public function convert(mixed $message) : array
    {
        if (is_array($message)) {
            return $message;
        }
        
        if (is_string($message)) {
            $message = json_decode($message, true);

            if (empty($message) || JSON_ERROR_NONE !== json_last_error() || !is_array($message)) {
                return [];
            }

            return $message;
        }

        return [];
    }
}
```

### Using your own converter

```yaml
# config/packages/codememory_ws_server.yaml
codememory_ws_server:
  converters:
    message: App\WebSocket\Converters\Message\FromSerializeToArrayMessageConverter
```

### Extractors
> The principle of implementing your own extractor is the same

### Extractor interfaces
- _Codememory\WebSocketServerBundle\Interfaces\MessageEventExtractorInterface_


- _Codememory\WebSocketServerBundle\Interfaces\MessageHeadersExtractorInterface_


- _Codememory\WebSocketServerBundle\Interfaces\MessageInputDataExtractorInterface_


### Let's now see how to implement our connection store

> Please note that this code is just an example and in real projects use a more universal approach that will ideally suit you in terms of speed.

```php
namespace App\WebSocket\Storages\Connection;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Codememory\WebSocketServerBundle\ValueObject\Connection as ConnectionVO;
use Doctrine\ORM\EntityManagerInterface;

// Our repository will run Doctrine ORM

#[ORM\Entity(repositoryClass: WebSocketConnectionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class WebSocketConnection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;
    
    #[ORM\Column(unique: true)]
    private ?int $connectionId = null;
    
    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;
    
    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = new DateTimeImmutable();

        return $this;
    }
    
    // ... Other Getters and Setters
}

final class DatabaseConnectionStorage implements ConnectionStorageInterface 
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WebSocketConnectionRepository $webSocketConnectionRepository
    ) {}
    
    public function all() : array
    {
        // We need to bring the entity to a specific interface. Will use default ValueObject
        return array_map(static function (WebSocketConnection $connection) {
            return new ConnectionVO($connection->getConnectionId(), $connection->getCreatedAt());
        }, $this->webSocketConnectionRepository->findAll());
    }
    
    public function exist(int $id) : bool
    {
        return null !== $this->webSocketConnectionRepository->findOneBy(['connectionId' => $id]);
    }
    
    public function remove(int $id) : ConnectionStorageInterface
    {
        $connection = $this->webSocketConnectionRepository->findOneBy(['connectionId' => $id]);
        
        if (null !== $connection) {
            $this->em->remove($connection);
            $this->em->flush($connection);
        }
        
        return $this;
    }
    
    // In this case, we have nothing to update in the connection, so we will skip this method
    public function update(int $id) : ConnectionStorageInterface
    {
        return $this;
    }
    
    public function insert(int $id) : ConnectionStorageInterface
    {
        $connection = new WebSocketConnection();
        
        $connection->setConnectionId($id);
        
        $this->em->persist($connection);
        $this->em->flush($connection);
        
        return $connection
    }
}
```

### Let's now apply our connection storage

```yaml
# config/packages/codememory_ws_server.yaml
codememory_ws_server:
  storages:
    connection: App\WebSocket\Storages\Connection\DatabaseConnectionStorage
```

### Custom queue message storage
> The principle of implementing the queue message store is the same as the connection store. You only need to implement the interface _Codememory\WebSocketServerBundle\Interfaces\MessageQueueStorageInterface_


### Let's now see how to use queue messages

```php

use Codememory\WebSocketServerBundle\Interfaces\MessageQueueManagerInterface;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;

// Let's imagine that we have a certain api route that creates an order, and after creating an order, we need to notify all users
final class TestController extends AbstractController
{
    #[Route('/order/create', methods: 'POST')]
    public function createOrder(MessageQueueManagerInterface $messageQueueManager, ConnectionStorageInterface $connectionStorage): Response
    {
        // Order is created
        foreach ($connectionStorage->all() as $connection) {
            $messageQueueManager->addMessageToQueue($connection->getConnectionID(), 'CREATED_ORDER', [
                'order_id' => 1
            ]);
        }
        
        // Now after creating an order, each connection will receive a message via WebSocket about a new order
    }
}
```