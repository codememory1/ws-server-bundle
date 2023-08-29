<?php

namespace Codememory\WebSocketServerBundle\DependencyInjection;

use Codememory\WebSocketServerBundle\WebSocketServerBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('codememory_ws_server');
        $rootNode = $builder->getRootNode();

        $this->addServerSection($rootNode);
        $this->addConverterSection($rootNode);
        $this->addExtractorSection($rootNode);
        $this->addStorageSection($rootNode);
        $this->addEventListenerSection($rootNode);
        $this->addConfigSection($rootNode);

        return $builder;
    }

    private function addServerSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('server')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('adapter')
                            ->cannotBeEmpty()
                            ->defaultValue(WebSocketServerBundle::DEFAULT_SERVER_SERVICE)
                            ->info('Server adapter. By default the server is written in swoole')
                        ->end()
                        ->scalarNode('protocol')
                            ->cannotBeEmpty()
                            ->defaultValue('websocket')
                            ->info('Protocol for connection, For example: websocket')
                        ->end()
                        ->scalarNode('host')
                            ->cannotBeEmpty()
                            ->defaultValue('127.0.0.1')
                            ->info('Server host, for example 127.0.0.1')
                        ->end()
                        ->integerNode('port')
                            ->defaultValue(8079)
                            ->info('Server port')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addConverterSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('converters')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('message')
                            ->cannotBeEmpty()
                            ->defaultValue(WebSocketServerBundle::DEFAULT_MESSAGE_CONVERTER_SERVICE)
                            ->info('Service converter message to the desired format and type')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addExtractorSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('extractors')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('message_event')
                            ->cannotBeEmpty()
                            ->defaultValue(WebSocketServerBundle::DEFAULT_MESSAGE_EVENT_EXTRACTOR_SERVICE)
                            ->info('Extractor of the event name from the sent event')
                        ->end()
                        ->scalarNode('message_headers')
                            ->cannotBeEmpty()
                            ->defaultValue(WebSocketServerBundle::DEFAULT_MESSAGE_HEADERS_EXTRACTOR_SERVICE)
                            ->info('Header extractor from sent message')
                        ->end()
                        ->scalarNode('message_input_data')
                            ->cannotBeEmpty()
                            ->defaultValue(WebSocketServerBundle::DEFAULT_MESSAGE_INPUT_DATA_EXTRACTOR_SERVICE)
                            ->info('Extractor of input data from sent message')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addStorageSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('storages')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('connection')
                            ->cannotBeEmpty()
                            ->defaultValue(WebSocketServerBundle::DEFAULT_CONNECTION_STORAGE_SERVICE)
                            ->info('Connection storage service')
                        ->end()
                        ->scalarNode('message_queue')
                            ->cannotBeEmpty()
                            ->defaultValue(WebSocketServerBundle::DEFAULT_MESSAGE_QUEUE_STORAGE_SERVICE)
                            ->info('Queue message storage service')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addEventListenerSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('event_listeners')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('event')
                                ->cannotBeEmpty()
                                ->info('The name of the event for which the listener is created')
                            ->end()
                            ->scalarNode('listener')
                                ->info('Event handler service')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addConfigSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('config')
                    ->variablePrototype()
                    ->end()
                ->end()
            ->end();
    }
}