<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface ConnectionStorageInterface
{
    public function all(): array;

    public function exist(int $id): bool;

    public function remove(int $id): self;

    public function insert(int $id): self;

    public function update(int $id): self;
}