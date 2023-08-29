<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageQueueStorageInterface
{
    public function all(): array;

    public function allByConnectionID(int $connectionID): array;

    public function save(int $connectionID, string $event, array $data): self;

    public function remove(string $id): self;
}