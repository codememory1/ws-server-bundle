<?php

namespace Codememory\WebSocketServerBundle\ValueObject;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionInterface;
use DateTimeInterface;

final readonly class Connection implements ConnectionInterface
{
    public function __construct(
        private int $id,
        private DateTimeInterface $createdAt
    ) {
    }

    public function getConnectionID(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }
}