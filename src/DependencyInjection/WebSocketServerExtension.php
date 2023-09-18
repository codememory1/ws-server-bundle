<?php

namespace Codememory\WebSocketServerBundle\DependencyInjection;

use Codememory\WebSocketServerBundle\Command\WebSocketServerCommand;
use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\EventListener\Message\HandleEventMessageEventListener;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Codememory\WebSocketServerBundle\Interfaces\URLBuilderInterface;
use Codememory\WebSocketServerBundle\Server\Swoole\Server;
use Codememory\WebSocketServerBundle\Utils\URLBuilder;
use Codememory\WebSocketServerBundle\WebSocketServerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class WebSocketServerExtension extends Extension
{
    public function getAlias(): string
    {
        return 'codememory_ws_server';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $webSocketEventListeners = $this->buildWebSocketEventListeners($config);

        $this->registerServices($config, $container);
        $this->registerWebSocketServerCommand($container);
        $this->registerEventListeners($webSocketEventListeners, $container);
    }

    private function buildWebSocketEventListeners(array $config): array
    {
        $eventListeners = [];

        foreach ($config['event_listeners'] as $eventListener) {
            $eventListeners[$eventListener['event']] = new Reference($eventListener['listener']);
        }

        return $eventListeners;
    }

    private function registerServices(array $config, ContainerBuilder $container): void
    {
        $container->register(URLBuilderInterface::class, URLBuilder::class);

        $container
            ->register(WebSocketServerBundle::DEFAULT_SERVER_SERVICE, Server::class)
            ->setArguments([
                '$URLBuilder' => new Reference(URLBuilderInterface::class),
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class)
            ])
            ->addMethodCall('setProtocol', [$config['server']['protocol']])
            ->addMethodCall('setHost', [$config['server']['host']])
            ->addMethodCall('setPort', [$config['server']['port']])
            ->addMethodCall('setConfig', [$config['config']]);

        $container->setAlias(ServerInterface::class, $config['server']['adapter']);
    }

    private function registerWebSocketServerCommand(ContainerBuilder $container): void
    {
        $container
            ->register(WebSocketServerCommand::class, WebSocketServerCommand::class)
            ->setArguments([
                '$server' => new Reference(ServerInterface::class),
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class)
            ])
            ->addTag('console.command', ['command' => 'codememory:ws-server:start']);
    }

    private function registerEventListeners(array $eventListeners, ContainerBuilder $container): void
    {
        $container
            ->register(HandleEventMessageEventListener::class, HandleEventMessageEventListener::class)
            ->setArguments([
                '$eventListeners' => $eventListeners,
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => MessageEvent::NAME,
                'method' => 'onMessage'
            ]);
    }
}