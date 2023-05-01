<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface ConnectionRequestInterface
{
    public function getConnectionId(): string|int;
}