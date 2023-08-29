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
        $this->client->set($this->buildKey(Uuid::uuid4()->toString(), $connectionID), json_encode([
            'connection_id' => $connectionID,
            'event' => $event,
            'data' => $data
        ]));

        return $this;
    }

    private function buildKey(string $id, int|string $connectionID): string
    {
        return "websocket:message:#{$id}:connection:{$connectionID}";
    }
}