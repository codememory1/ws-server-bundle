<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface ConnectionStorageInterface
{
    /**
     * @return array<int, ConnectionRequestInterface>
     */
    public function allConnections(): array;

    public function getConnection(string|int $id): ?ConnectionRequestInterface;

    public function deleteConnection(string|int $id): void;

    public function save(ConnectionRequestInterface $connectionRequest): void;
}