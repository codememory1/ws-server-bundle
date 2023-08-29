<?php

namespace Codememory\WebSocketServerBundle\EventListener\ConnectionOpen;

use Codememory\WebSocketServerBundle\Event\ConnectionOpenEvent;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;

final readonly class SaveConnectionEventListener
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage
    ) {
    }

    public function onOpen(ConnectionOpenEvent $event): void
    {
        $this->connectionStorage->insert($event->connectionID);
    }
}