<?php

namespace Codememory\WebSocketServerBundle\DependencyInjection;

use Codememory\WebSocketServerBundle\Adapter\Swoole\Server;
use Codememory\WebSocketServerBundle\Service\ConnectionStorage\ConnectionRedisStorage;
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
        $this->addAdapterSection($rootNode);

        return $builder;
    }

    private function addServerSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('server')
                    ->addDefaultsIfNotSet()
                    ->children()
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
                            ->defaultValue(80)
                            ->info('Server port')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addAdapterSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('server_service')
                            ->cannotBeEmpty()
                            ->defaultValue(Server::class)
                            ->info('Server Service ID')
                        ->end()
                        ->scalarNode('connection_storage_service')
                            ->cannotBeEmpty()
                            ->isRequired()
                            ->info('Connection storage service ID')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}