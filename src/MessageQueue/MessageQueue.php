<?php

namespace Codememory\WebSocketServerBundle\MessageQueue;

use Codememory\WebSocketServerBundle\Event\AddedMessageToQueueEvent;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageQueueInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageQueueStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class MessageQueue implements MessageQueueInterface
{
    public function __construct(
        private MessageQueueStorageInterface $messageQueueStorage,
        private ConnectionStorageInterface $connectionStorage,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function addMessageToQueue(int $connectionID, string $event, array $data): MessageQueueInterface
    {
        if ($this->connectionStorage->exist($connectionID)) {
            $this->messageQueueStorage->save($connectionID, $event, $data);

            $this->eventDispatcher->dispatch(new AddedMessageToQueueEvent($connectionID, $event, $data), AddedMessageToQueueEvent::NAME);
        }

        return $this;
    }
}