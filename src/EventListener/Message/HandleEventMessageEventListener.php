<?php

namespace Codememory\WebSocketServerBundle\EventListener\Message;

use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent;
use Codememory\WebSocketServerBundle\Interfaces\MessageEventExtractorInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageEventHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final readonly class HandleEventMessageEventListener
{
    /**
     * @param array<string, MessageEventHandlerInterface> $eventListeners
     */
    public function __construct(
        private array $eventListeners,
        private EventDispatcherInterface $eventDispatcher,
        private MessageEventExtractorInterface $messageEventExtractor
    ) {
    }

    public function onMessage(MessageEvent $event): void
    {
        try {
            $eventName = $this->messageEventExtractor->extractEventName($event->message);

            if (array_key_exists($eventName, $this->eventListeners)) {
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