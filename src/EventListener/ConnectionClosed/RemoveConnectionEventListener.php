<?php

namespace Codememory\WebSocketServerBundle\EventListener\ConnectionClosed;

use Codememory\WebSocketServerBundle\Event\ConnectionClosedEvent;
use Codememory\WebSocketServerBundle\Event\RemoveConnectionEvent;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class RemoveConnectionEventListener
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function onClosed(ConnectionClosedEvent $event): void
    {
        $this->connectionStorage->remove($event->connectionID);

        $this->eventDispatcher->dispatch(new RemoveConnectionEvent($event->server, $event->connectionID), RemoveConnectionEvent::NAME);
    }
}