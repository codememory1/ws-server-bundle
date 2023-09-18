<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageEventListenerInterface
{
    public function handle(ServerInterface $server, MessageInterface $message): void;
}