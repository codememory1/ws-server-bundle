<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageQueueStorageInterface
{
    /**
     * @return array<int, MessageQueueInterface>
     */
    public function all(): array;

    /**
     * @return array<int, MessageQueueInterface>
     */
    public function allByConnectionID(int $connectionID): array;

    public function save(int $connectionID, string $event, array $data): self;

    public function remove(MessageQueueInterface $messageQueue): self;
}