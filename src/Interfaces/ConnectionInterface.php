<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

use DateTimeInterface;

interface ConnectionInterface
{
    public function getConnectionID(): int;

    public function getCreatedAt(): DateTimeInterface;
}