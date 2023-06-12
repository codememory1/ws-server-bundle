<?php

namespace Codememory\WebSocketServerBundle\EventListener\MessageHandlerException;

use Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent;
use Psr\Log\LoggerInterface;

final class SaveExceptionToLogEventListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function onMessageHandlerException(MessageHandlerExceptionEvent $event): void
    {
        $this->logger->critical($event->exception, [
            'connection_id' => $event->connectionRequest->getConnectionId()
        ]);
    }
}