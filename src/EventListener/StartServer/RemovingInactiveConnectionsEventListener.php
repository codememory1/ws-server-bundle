<?php

namespace Codememory\WebSocketServerBundle\EventListener\StartServer;

use Codememory\WebSocketServerBundle\Event\StartServerEvent;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class RemovingInactiveConnectionsEventListener
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage,
        private LoggerInterface $logger
    ) {
    }

    public function onStart(StartServerEvent $event): void
    {
        try {
            $event->server->addProcess(function() use ($event): void {
                foreach ($this->connectionStorage->all() as $connection) {
                    if (!$event->server->existConnection($connection->getConnectionID())) {
                        $this->connectionStorage->remove($connection->getConnectionID());
                    }
                }
            });
        } catch (Throwable $e) {
            $this->logger->critical($e, [
                'origin' => self::class,
                'detail' => 'An error occurred while deleting inactive connections'
            ]);
        }
    }
}