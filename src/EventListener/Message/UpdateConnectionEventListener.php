<?php

namespace Codememory\WebSocketServerBundle\EventListener\Message;

use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;

final readonly class UpdateConnectionEventListener
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage
    ) {
    }

    public function onMessage(MessageEvent $event): void
    {
        $this->connectionStorage->update($event->message->getSenderConnectionID());
    }
}