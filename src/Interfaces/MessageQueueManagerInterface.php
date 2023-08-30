<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageQueueManagerInterface
{
    public function addMessageToQueue(int $connectionID, string $event, array $data): self;
}