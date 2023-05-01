<?php

namespace Codememory\WebSocketServerBundle\Service\ConnectionStorage;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionRequestInterface;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Codememory\WebSocketServerBundle\Service\ConnectionRequest;
use Predis\Client;

class ConnectionRedisStorage implements ConnectionStorageInterface
{
    public function __construct(
        protected readonly Client $client
    ) {
    }

    protected function getConnectionKey(int|string $id): string
    {
        return "websocket:connection:#{$id}";
    }

    public function allConnections(): array
    {
        $connections = [];

        foreach ($this->client->keys($this->getConnectionKey('*')) as $key) {
            $data = json_decode($this->client->get($key), true);

            $connections[$data['connection_id']] = new ConnectionRequest($data['connection_id']);
        }

        return $connections;
    }

    public function getConnection(int|string $id): ?ConnectionRequestInterface
    {
        if (1 === $this->client->exists($this->getConnectionKey($id))) {
            $data = json_decode($this->client->get($this->getConnectionKey($id)), true);

            return new ConnectionRequest($data['connection_id']);
        }

        return null;
    }

    public function deleteConnection(string|int $id): void
    {
        if (1 === $this->client->exists($this->getConnectionKey($id))) {
            $this->client->del($this->getConnectionKey($id));
        }
    }

    public function save(ConnectionRequestInterface $connectionRequest): void
    {
        $this->client->set($this->getConnectionKey($connectionRequest->getConnectionId()), json_encode([
            'connection_id' => $connectionRequest->getConnectionId()
        ]));
    }
}