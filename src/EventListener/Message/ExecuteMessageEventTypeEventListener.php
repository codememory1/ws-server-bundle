<?php

namespace Codememory\WebSocketServerBundle\EventListener\Message;

use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent;
use Codememory\WebSocketServerBundle\Interfaces\MessageEventHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class ExecuteMessageEventTypeEventListener
{
    /**
     * @param array<int, MessageEventHandlerInterface> $handlers
     */
    public function __construct(
        private readonly array $handlers,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function onMessage(MessageEvent $event): void
    {
        try {
            if (array_key_exists($event->frame->getEventType(), $this->handlers)) {
                $this->handlers[$event->frame->getEventType()]->handle($event->server, $event->frame);
            }
        } catch (Throwable $e) {
            $this->eventDispatcher->dispatch(new MessageHandlerExceptionEvent(
                $event->server,
                $event->frame->getConnectionRequest(),
                $e
            ), MessageHandlerExceptionEvent::NAME);
        }
    }
}