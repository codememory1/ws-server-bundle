<?php

namespace Codememory\WebSocketServerBundle\DependencyInjection;

use Codememory\WebSocketServerBundle\Command\WebSocketServerCommand;
use Codememory\WebSocketServerBundle\ConnectionStorage\RedisConnectionStorage;
use Codememory\WebSocketServerBundle\Converters\ToArrayMessageConverter;
use Codememory\WebSocketServerBundle\Event\ConnectionClosedEvent;
use Codememory\WebSocketServerBundle\Event\ConnectionOpenEvent;
use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Event\MessageHandlerExceptionEvent;
use Codememory\WebSocketServerBundle\Event\StartServerEvent;
use Codememory\WebSocketServerBundle\EventListener\ConnectionClosed\RemoveConnectionEventListener;
use Codememory\WebSocketServerBundle\EventListener\ConnectionOpen\SaveConnectionEventListener;
use Codememory\WebSocketServerBundle\EventListener\Message\HandleEventMessageEventListener;
use Codememory\WebSocketServerBundle\EventListener\Message\UpdateConnectionEventListener;
use Codememory\WebSocketServerBundle\EventListener\MessageHandlerException\SaveExceptionToLogEventListener;
use Codememory\WebSocketServerBundle\EventListener\StartServer\SendMessageFromQueueEventListener;
use Codememory\WebSocketServerBundle\Extractors\FromArrayMessageEventExtractor;
use Codememory\WebSocketServerBundle\Extractors\FromArrayMessageHeadersExtractor;
use Codememory\WebSocketServerBundle\Extractors\FromArrayMessageInputDataExtractor;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageConverterInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageEventExtractorInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageHeadersExtractorInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInputDataExtractorInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageQueueStorageInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Codememory\WebSocketServerBundle\Interfaces\URLBuilderInterface;
use Codememory\WebSocketServerBundle\MessageQueueStorage\RedisMessageQueueStorage;
use Codememory\WebSocketServerBundle\Server\Swoole\Server;
use Codememory\WebSocketServerBundle\Service\URLBuilder;
use Codememory\WebSocketServerBundle\WebSocketServerBundle;
use Predis\Client as RedisClient;
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
            ->register(WebSocketServerBundle::DEFAULT_CONNECTION_STORAGE_SERVICE, RedisConnectionStorage::class)
            ->setArgument('$client', new Reference(RedisClient::class));

        $container
            ->register(WebSocketServerBundle::DEFAULT_MESSAGE_QUEUE_STORAGE_SERVICE, RedisMessageQueueStorage::class)
            ->setArgument('$client', new Reference(RedisClient::class));

        $container->register(WebSocketServerBundle::DEFAULT_MESSAGE_CONVERTER_SERVICE, ToArrayMessageConverter::class);
        $container->register(WebSocketServerBundle::DEFAULT_MESSAGE_EVENT_EXTRACTOR_SERVICE, FromArrayMessageEventExtractor::class);
        $container->register(WebSocketServerBundle::DEFAULT_MESSAGE_HEADERS_EXTRACTOR_SERVICE, FromArrayMessageHeadersExtractor::class);
        $container->register(WebSocketServerBundle::DEFAULT_MESSAGE_INPUT_DATA_EXTRACTOR_SERVICE, FromArrayMessageInputDataExtractor::class);

        $container
            ->register(WebSocketServerBundle::DEFAULT_SERVER_SERVICE, Server::class)
            ->setArguments([
                '$URLBuilder' => new Reference(URLBuilderInterface::class),
                '$messageConverter' => new Reference($config['converters']['message']),
                '$connectionStorage' => new Reference($config['storages']['connection']),
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class)
            ])
            ->addMethodCall('setProtocol', [$config['server']['protocol']])
            ->addMethodCall('setHost', [$config['server']['host']])
            ->addMethodCall('setPort', [$config['server']['port']])
            ->addMethodCall('setAutoDisconnect', [$config['server']['auto_disconnect']])
            ->addMethodCall('setConfig', [$config['config']]);

        $container->setAlias(ConnectionStorageInterface::class, $config['storages']['connection']);
        $container->setAlias(MessageQueueStorageInterface::class, $config['storages']['message_queue']);
        $container->setAlias(ServerInterface::class, $config['server']['adapter']);
        $container->setAlias(MessageConverterInterface::class, $config['converters']['message']);
        $container->setAlias(MessageEventExtractorInterface::class, $config['extractors']['message_event']);
        $container->setAlias(MessageHeadersExtractorInterface::class, $config['extractors']['message_headers']);
        $container->setAlias(MessageInputDataExtractorInterface::class, $config['extractors']['message_input_data']);
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
            ->register(SendMessageFromQueueEventListener::class, SendMessageFromQueueEventListener::class)
            ->setArguments([
                '$messageQueueStorage' => new Reference(MessageQueueStorageInterface::class),
                '$logger' => new Reference(LoggerInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => StartServerEvent::NAME,
                'method' => 'onStart'
            ]);

        $container
            ->register(SaveConnectionEventListener::class, SaveConnectionEventListener::class)
            ->setArguments([
                '$connectionStorage' => new Reference(ConnectionStorageInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => ConnectionOpenEvent::NAME,
                'method' => 'onOpen'
            ]);

        $container
            ->register(RemoveConnectionEventListener::class, RemoveConnectionEventListener::class)
            ->setArguments([
                '$connectionStorage' => new Reference(ConnectionStorageInterface::class),
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => ConnectionClosedEvent::NAME,
                'method' => 'onClosed'
            ]);

        $container
            ->register(HandleEventMessageEventListener::class, HandleEventMessageEventListener::class)
            ->setArguments([
                '$eventListeners' => $eventListeners,
                '$eventDispatcher' => new Reference(EventDispatcherInterface::class),
                '$messageEventExtractor' => new Reference(MessageEventExtractorInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => MessageEvent::NAME,
                'method' => 'onMessage'
            ]);

        $container
            ->register(UpdateConnectionEventListener::class, UpdateConnectionEventListener::class)
            ->setArguments([
                '$connectionStorage' => new Reference(ConnectionStorageInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => MessageEvent::NAME,
                'method' => 'onMessage'
            ]);

        $container
            ->register(SaveExceptionToLogEventListener::class, SaveExceptionToLogEventListener::class)
            ->setArguments([
                '$logger' => new Reference(LoggerInterface::class)
            ])
            ->addTag('kernel.event_listener', [
                'event' => MessageHandlerExceptionEvent::NAME,
                'method' => 'onMessageException'
            ]);
    }
}