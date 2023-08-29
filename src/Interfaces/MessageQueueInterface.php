<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageQueueInterface
{
    public function addMessageToQueue(int $connectionID, string $event, array $data): self;
}