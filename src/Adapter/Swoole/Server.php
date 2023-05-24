<?php

namespace Codememory\WebSocketServerBundle\Adapter\Swoole;

use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Event\ConnectEvent;
use Codememory\WebSocketServerBundle\Event\ConnectionClosedEvent;
use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Event\StartEvent;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionRequestInterface;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionStorageInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Codememory\WebSocketServerBundle\Service\ConnectionRequest;
use LogicException;
use OpenSwoole\Http\Request as SwooleRequest;
use OpenSwoole\Process;
use OpenSwoole\WebSocket\Frame as SwooleFrame;
use OpenSwoole\WebSocket\Server as SwooleServer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class Server implements ServerInterface
{
    private ?string $protocol = null;
    private ?string $host = null;
    private ?int $port = null;
    private ?SwooleServer $server = null;

    /**
     * @var array<int, object>
     */
    private array $process = [];

    public function __construct(
        private readonly ConnectionStorageInterface $connectionStorage,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): ServerInterface
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): ServerInterface
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(int $port): ServerInterface
    {
        $this->port = $port;

        return $this;
    }

    public function sendMessage(ConnectionRequestInterface $connectionRequest, string $event, array $data, Opcode $opcode = Opcode::TEXT, ?int $flags = null): bool
    {
        $opcode = match ($opcode) {
            Opcode::TEXT => 1,
            Opcode::BINARY => 2,
            Opcode::CLOSE => 8,
            Opcode::PING => 9,
            Opcode::PONG => 10
        };

        $is = $this->server?->push($connectionRequest->getConnectionId(), json_encode([
            'event' => $event,
            'data' => $data
        ]), $opcode, $flags);

        return null === $is ? false : $is;
    }

    public function disconnect(ConnectionRequestInterface $connectionRequest, ?int $code = null, ?string $reason = null): bool
    {
        $is = $this->server?->disconnect($connectionRequest->getConnectionId(), $code ?: SwooleServer::WEBSOCKET_CLOSE_NORMAL, $reason ?: '');

        return null === $is ? false : $is;
    }

    public function addProcess(object $process): ServerInterface
    {
        $this->process[] = $process;

        return $this;
    }

    public function getConnectionRequests(): array
    {
        return $this->connectionStorage->allConnections();
    }

    public function connectionExist(int|string $id): bool
    {
        return null !== $this->connectionStorage->getConnection($id);
    }

    public function start(): bool
    {
        $this->throwIfInvalidURI();

        $this->server = new SwooleServer("{$this->getProtocol()}://{$this->getHost()}", $this->getPort());

        $this->onOpen();
        $this->onMessage();
        $this->onClose();

        $this->eventDispatcher->dispatch(new StartEvent($this), StartEvent::NAME);
        
        /** @var Process $process */
        foreach ($this->process as $process) {
            $this->server->addProcess($process);
        }

        return $this->server->start();
    }

    private function onOpen(): void
    {
        $this->server?->on('Open', function(SwooleServer $swooleServer, SwooleRequest $swooleRequest): void {
            $this->connectionStorage->save(new ConnectionRequest($swooleRequest->fd));

            $this->eventDispatcher->dispatch(new ConnectEvent($this, new Request($swooleRequest)), ConnectEvent::NAME);
        });
    }

    private function onMessage(): void
    {
        $this->server?->on('Message', function(SwooleServer $swooleServer, SwooleFrame $swooleFrame): void {
            $frame = new Frame($swooleFrame, $this->connectionStorage->getConnection($swooleFrame->fd));

            if ($frame->dataIsValid()) {
                $this->eventDispatcher->dispatch(new MessageEvent($this, $frame), MessageEvent::NAME);
            }
        });
    }

    private function onClose(): void
    {
        $this->server?->on('Close', function(SwooleServer $swooleServer, string|int $connectionId): void {
            $connectionRequest = $this->connectionStorage->getConnection($connectionId);

            if (null !== $connectionRequest) {
                $this->eventDispatcher->dispatch(new ConnectionClosedEvent($this, $connectionRequest), ConnectionClosedEvent::NAME);
            }

            $this->connectionStorage->deleteConnection($connectionId);
        });
    }

    private function throwIfInvalidURI(): void
    {
        if (null === $this->getProtocol()) {
            throw new LogicException('Protocol not specified, call the setProtocol method on the server');
        }

        if (null === $this->getHost()) {
            throw new LogicException('Host not specified, call the setHost method on the server');
        }

        if (null === $this->getPort()) {
            throw new LogicException('Port not specified, call the setPort method on the server');
        }
    }
}