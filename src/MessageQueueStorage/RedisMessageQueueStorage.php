<?php

namespace Codememory\WebSocketServerBundle\MessageQueueStorage;

use Codememory\WebSocketServerBundle\Interfaces\MessageQueueInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageQueueStorageInterface;
use Codememory\WebSocketServerBundle\ValueObject\MessageQueue;
use Predis\Client;
use Ramsey\Uuid\Uuid;

final readonly class RedisMessageQueueStorage implements MessageQueueStorageInterface
{
    public function __construct(
        protected Client $client
    ) {
    }

    public function all(): array
    {
        $messages = [];

        foreach ($this->client->keys($this->buildKey('*')) as $key) {
            $messageData = json_decode($this->client->get($key), true);
            $message = new MessageQueue(
                $messageData['id'],
                $messageData['connection_id'],
                $messageData['event'],
                $messageData['data']
            );

            $messages[] = $message;
        }

        return $messages;
    }

    public function allByConnectionID(int $connectionID): array
    {
        $messages = [];

        foreach ($this->client->keys($this->buildKey('*')) as $key) {
            $messageData = json_decode($this->client->get($key), true);

            if ($messageData['connection_id'] === $connectionID) {
                $message = new MessageQueue(
                    $messageData['id'],
                    $messageData['connection_id'],
                    $messageData['event'],
                    $messageData['data']
                );

                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function save(int $connectionID, string $event, array $data): MessageQueueStorageInterface
    {
        $id = Uuid::uuid4()->toString();

        $this->client->set($this->buildKey($id), json_encode([
            'id' => $id,
            'connection_id' => $connectionID,
            'event' => $event,
            'data' => $data
        ]));

        return $this;
    }

    public function remove(MessageQueueInterface $messageQueue): MessageQueueStorageInterface
    {
        if (1 === $this->client->exists($this->buildKey($messageQueue->getID()))) {
            $this->client->del($this->buildKey($messageQueue->getID()));
        }

        return $this;
    }

    private function buildKey(string $id): string
    {
        return "websocket:message:#{$id}";
    }
}