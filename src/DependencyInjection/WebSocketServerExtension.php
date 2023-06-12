<?php

namespace Codememory\WebSocketServerBundle\DependencyInjection;

use Codememory\WebSocketServerBundle\Command\WebSocketServerCommand;
use Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent;
use Codememory\WebSocketServerBundle\EventListener\MessageHandlerException\SaveExceptionToLogEventListener;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Psr\Log\LoggerInterface;
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

        $this->registerServer($config['server'], $config['adapter'], $container);
        $this->registerWebSocketServerCommand($container);
    }

    private function registerWebSocketServerCommand(ContainerBuilder $container): void
    {
        $container
            ->register(WebSocketServerCommand::class, WebSocketServerCommand::class)
            ->setAutowired(true)
            ->addTag('console.command', ['command' => 'codememory:ws-server:start']);
    }

    private function registerServer(array $server, array $adapter, ContainerBuilder $container): void
    {
        $container
            ->register(ServerInterface::class, $adapter['server_service'])
            ->setArguments([
                '$connectionStorage' => new Reference($adapter['connection_storage_service']),
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class)
            ])
            ->addMethodCall('setProtocol', [
                '$protocol' => $server['protocol']
            ])
            ->addMethodCall('setHost', [
                '$host' => $server['host']
            ])
            ->addMethodCall('setPort', [
                '$port' => $server['port']
            ]);
    }

    private function registerSaveExceptionToLogEventListener(ContainerBuilder $container): void
    {
        $container
            ->register(SaveExceptionToLogEventListener::class, SaveExceptionToLogEventListener::class)
            ->setArguments([
                '$logger' => new Reference(LoggerInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => MessageHandlerExceptionEvent::NAME,
                'method' => 'onMessageHandlerException'
            ]);
    }
}