<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageEventHandlerInterface
{
    public function handle(ServerInterface $server, MessageInterface $message): void;
}