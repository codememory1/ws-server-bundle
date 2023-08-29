<?php

namespace Codememory\WebSocketServerBundle\Server;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageConverterInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Codememory\WebSocketServerBundle\Interfaces\URLBuilderInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractServer implements ServerInterface
{
    protected string $protocol = 'websocket';
    protected string $host = '0.0.0.0';
    protected int $port = 8079;
    protected array $config = [];
    protected array $processes = [];

    public function __construct(
        protected readonly URLBuilderInterface $URLBuilder,
        protected readonly MessageConverterInterface $messageConverter,
        protected readonly ConnectionStorageInterface $connectionStorage,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): ServerInterface
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): ServerInterface
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): ServerInterface
    {
        $this->port = $port;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): ServerInterface
    {
        $this->config = $config;

        return $this;
    }

    public function addProcess(callable $callback): ServerInterface
    {
        $this->processes[] = $callback;

        return $this;
    }

    public function tick(int $ms, callable $callback): ServerInterface
    {
        throw new RuntimeException(sprintf('Server "%s" does not support ticks', self::class));
    }
}