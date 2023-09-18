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