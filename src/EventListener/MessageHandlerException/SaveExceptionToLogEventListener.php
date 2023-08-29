<?php

namespace Codememory\WebSocketServerBundle\EventListener\MessageHandlerException;

use Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent;
use Psr\Log\LoggerInterface;

final readonly class SaveExceptionToLogEventListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function onMessageException(MessageHandlerExceptionEvent $event): void
    {
        $this->logger->critical($event->exception, [
            'connection_id' => $event->connectionID
        ]);
    }
}