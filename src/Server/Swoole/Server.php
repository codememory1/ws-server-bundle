<?php

namespace Codememory\WebSocketServerBundle\Server\Swoole;

use Codememory\WebSocketServerBundle\Enum\CloseCode;
use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Enum\StatsMode;
use Codememory\WebSocketServerBundle\Event\ConnectionClosedEvent;
use Codememory\WebSocketServerBundle\Event\ConnectionOpenEvent;
use Codememory\WebSocketServerBundle\Event\ConnectionRemovedEvent;
use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Event\MessageSentEvent;
use Codememory\WebSocketServerBundle\Event\StartServerEvent;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Codememory\WebSocketServerBundle\Server\AbstractServer;
use OpenSwoole\Http\Request as SwooleRequest;
use OpenSwoole\Process;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server as SwooleServer;

class Server extends AbstractServer
{
    protected ?SwooleServer $server = null;

    public function disconnect(int $connectionID, CloseCode $code = CloseCode::NORMAL, ?string $reason = null): bool
    {
        if (null !== $this->server) {
            return $this->server->disconnect($connectionID, $code->value, $reason ?: '');
        }

        return false;
    }

    public function sendMessage(int $connectionID, string $event, array $data, Opcode $opcode = Opcode::TEXT, ?int $flags = null): bool
    {
        $opcodeNumber = match ($opcode) {
            Opcode::TEXT => 1,
            Opcode::BINARY => 2,
            Opcode::CLOSE => 8,
            Opcode::PING => 9,
            Opcode::PONG => 10
        };

        if (null === $this->server) {
            return false;
        }

        $isSuccess = false;

        if ($this->existConnection($connectionID)) {
            $isSuccess = $this->server->push($connectionID, json_encode([
                'event' => $event,
                'data' => $data
            ]), $opcodeNumber, $flags);
        } else {
            $this->eventDispatcher->dispatch(new ConnectionRemovedEvent($this, $connectionID), ConnectionRemovedEvent::NAME);
        }

        $this->eventDispatcher->dispatch(new MessageSentEvent(
            $this,
            $connectionID,
            $event,
            $data,
            $isSuccess,
            $opcode,
            $flags
        ), MessageSentEvent::NAME);

        return $isSuccess;
    }

    public function existConnection(int $id): bool
    {
        return null !== $this->server && $this->server->exists($id);
    }

    public function tick(int $ms, callable $callback): ServerInterface
    {
        $this->server?->tick($ms, $callback);

        return $this;
    }

    public function getStats(StatsMode $mode = StatsMode::DEFAULT): array|string|false
    {
        return $this->server->stats($mode->value);
    }

    public function on(string $event, callable $callback): ServerInterface
    {
        $this->server?->on($event, $callback);

        return $this;
    }

    public function task(mixed $data, int $dstWorkerID = -1, ?callable $finishCallback = null): ?int
    {
        return $this->server?->task($data, $dstWorkerID, $finishCallback);
    }

    public function taskWait(mixed $data, float $timeout = 0.5, int $dstWorkerID = -1): string|bool
    {
        return $this->server?->taskwait($data, $timeout, $dstWorkerID);
    }

    public function taskWaitMulti(array $tasks, float $timeout = 0.5): bool|array
    {
        return $this->server?->taskWaitMulti($tasks, $timeout);
    }

    public function toggleConnection(int $connectionID, bool $isPause = true): bool
    {
        if (null !== $this->server && $this->existConnection($connectionID)) {
            return $isPause ? $this->server->pause($connectionID) : $this->server->resume($connectionID);
        }

        return false;
    }

    public function start(): bool
    {
        $this->init();

        $this->eventDispatcher->dispatch(new StartServerEvent($this), StartServerEvent::NAME);

        foreach ($this->processes as $callback) {
            $this->server->addProcess(new Process($callback));
        }

        return $this->server->start();
    }

    private function init(): void
    {
        $this->server = new SwooleServer(
            $this->URLBuilder->build($this->getProtocol(), $this->getHost()),
            $this->getPort()
        );

        if ([] !== $this->getConfig()) {
            $this->server->set($this->getConfig());
        }

        $this->onOpen();
        $this->onMessage();
        $this->onClose();
    }

    private function onOpen(): void
    {
        $this->server->on('Open', function(SwooleServer $server, SwooleRequest $request): void {
            $this->eventDispatcher->dispatch(new ConnectionOpenEvent(
                $this,
                $request->fd,
                $request->header['sec-websocket-key']
            ), ConnectionOpenEvent::NAME);
        });
    }

    private function onMessage(): void
    {
        $this->server->on('Message', function(SwooleServer $server, Frame $frame): void {
            $this->eventDispatcher->dispatch(new MessageEvent($this, new Message($frame)), MessageEvent::NAME);
        });
    }

    private function onClose(): void
    {
        $this->server->on('Close', function(SwooleServer $server, int $fd): void {
            $this->eventDispatcher->dispatch(new ConnectionClosedEvent($this, $fd), ConnectionClosedEvent::NAME);
        });
    }
}