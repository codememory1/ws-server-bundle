<?php

namespace Codememory\WebSocketServerBundle\MessageQueueStorage;

use Codememory\WebSocketServerBundle\Interfaces\MessageQueueStorageInterface;
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

        foreach ($this->client->keys($this->buildKey('*', '*')) as $key) {
            $messages[] = json_decode($this->client->get($key), true);
        }

        return $messages;
    }

    public function allByConnectionID(int $connectionID): array
    {
        $messages = [];

        foreach ($this->client->keys($this->buildKey('*', $connectionID)) as $key) {
            $messages[] = json_decode($this->client->get($key), true);
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

    public function remove(string $id): MessageQueueStorageInterface
    {
        if (1 === $this->client->exists($this->buildKey($id))) {
            $this->client->del($this->buildKey($id));
        }

        return $this;
    }

    private function buildKey(string $id): string
    {
        return "websocket:message:#{$id}";
    }
}