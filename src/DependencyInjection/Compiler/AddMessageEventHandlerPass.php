<?php

namespace Codememory\WebSocketServerBundle\DependencyInjection\Compiler;

use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\EventListener\Message\ExecuteMessageEventTypeEventListener;
use Codememory\WebSocketServerBundle\WebSocketServerBundle;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class AddMessageEventHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $handlers = $container->findTaggedServiceIds(WebSocketServerBundle::MESSAGE_EVENT_HANDLER_TAG);
        $references = [];

        foreach ($handlers as $id => $tags) {
            if (!array_key_exists('event', $tags[0])) {
                throw new LogicException(sprintf('The %s tag expects the event key from the %s service', WebSocketServerBundle::MESSAGE_EVENT_HANDLER_TAG, $id));
            }

            $references[$tags[0]['event']] = new Reference($id);
        }

        $container
            ->register(ExecuteMessageEventTypeEventListener::class, ExecuteMessageEventTypeEventListener::class)
            ->setArguments([
                '$handlers' => $references,
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => MessageEvent::NAME,
                'method' => 'onMessage'
            ]);
    }
}