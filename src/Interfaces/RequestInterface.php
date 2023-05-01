<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface RequestInterface
{
    public function getConnectionRequest(): ConnectionRequestInterface;
}