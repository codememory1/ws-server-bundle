<?php

namespace Codememory\WebSocketServerBundle\EventListener\StartServer;

use Codememory\WebSocketServerBundle\Event\StartServerEvent;
use Codememory\WebSocketServerBundle\Interfaces\MessageQueueStorageInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class SendMessageFromQueueEventListener
{
    public function __construct(
        private MessageQueueStorageInterface $messageQueueStorage,
        private LoggerInterface $logger
    ) {
    }

    public function onStart(StartServerEvent $event): void
    {
        try {
            $event->server->addProcess(function() use ($event): void {
                foreach ($this->messageQueueStorage->all() as $message) {
                    $event->server->sendMessage($message['connection_id'], $message['event'], $message['data']);
                }
            });
        } catch (Throwable $e) {
            $this->logger->critical($e, [
                'origin' => self::class,
                'detail' => 'An error occurred while adding the process of sending messages from the queue'
            ]);
        }
    }
}