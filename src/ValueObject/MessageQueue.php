<?php

namespace Codememory\WebSocketServerBundle\ValueObject;

use Codememory\WebSocketServerBundle\Interfaces\MessageQueueInterface;

final readonly class MessageQueue implements MessageQueueInterface
{
    public function __construct(
        private string $id,
        private int $connectionID,
        private string $event,
        private array $data
    ) {
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getConnectionID(): int
    {
        return $this->connectionID;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getData(): array
    {
        return $this->data;
    }
}