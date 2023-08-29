<?php

namespace Codememory\WebSocketServerBundle\ConnectionStorage;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Predis\Client;

readonly class RedisConnectionStorage implements ConnectionStorageInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    protected function buildKey(int|string $id): string
    {
        return "websocket:connection:{$id}";
    }

    public function all(): array
    {
        $connections = [];

        foreach ($this->client->keys($this->buildKey('*')) as $key) {
            $connection = json_decode($this->client->get($key), true);

            $connections[$connection['connection_id']] = $connection;
        }

        return $connections;
    }

    public function exist(int $id): bool
    {
        return 1 === $this->client->exists($this->buildKey($id));
    }

    public function remove(int $id): ConnectionStorageInterface
    {
        if (1 === $this->client->exists($this->buildKey($id))) {
            $this->client->del($this->buildKey($id));
        }

        return $this;
    }

    public function insert(int $id): ConnectionStorageInterface
    {
        $this->client->set($this->buildKey($id), json_encode([
            'connection_id' => $id,
            'created_at' => time(),
            'updated_at' => time()
        ]));

        return $this;
    }

    public function update(int $id): ConnectionStorageInterface
    {
        if (1 === $this->client->exists($this->buildKey($id))) {
            $connection = json_decode($this->client->get($this->buildKey($id)), true);

            $connection['updated_at'] = time();

            $this->client->set($this->buildKey($id), json_encode($connection));
        }

        return $this;
    }
}