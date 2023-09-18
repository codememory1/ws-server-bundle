<?php

namespace Codememory\WebSocketServerBundle\EventListener\Message;

use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent;
use Codememory\WebSocketServerBundle\Interfaces\MessageEventListenerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class HandleEventMessageEventListener
{
    /**
     * @param array<string, MessageEventListenerInterface> $eventListeners
     */
    public function __construct(
        private readonly array $eventListeners,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function onMessage(MessageEvent $event): void
    {
        try {
            $eventName = $event->message->getEvent();

            if (null !== $eventName && array_key_exists($eventName, $this->eventListeners)) {
                $this->eventListeners[$eventName]->handle($event->server, $event->message);
            }
        } catch (Throwable $e) {
            $this->eventDispatcher->dispatch(new MessageHandlerExceptionEvent(
                $event->server,
                $event->message->getSenderConnectionID(),
                $e
            ), MessageHandlerExceptionEvent::NAME);
        }
    }
}