<?php

namespace Codememory\WebSocketServerBundle\Service;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionRequestInterface;

final class ConnectionRequest implements ConnectionRequestInterface
{
    public function __construct(
        private readonly string|int $connectionId
    ) {
    }

    public function getConnectionId(): string|int
    {
        return $this->connectionId;
    }
}