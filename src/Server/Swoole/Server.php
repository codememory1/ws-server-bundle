<?php

namespace Codememory\WebSocketServerBundle\Server\Swoole;

use Codememory\WebSocketServerBundle\Enum\CloseCode;
use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Event\ConnectionClosedEvent;
use Codememory\WebSocketServerBundle\Event\ConnectionOpenEvent;
use Codememory\WebSocketServerBundle\Event\MessageEvent;
use Codememory\WebSocketServerBundle\Event\MessageSentEvent;
use Codememory\WebSocketServerBundle\Event\RemoveConnectionEvent;
use Codememory\WebSocketServerBundle\Event\StartServerEvent;
use Codememory\WebSocketServerBundle\Server\AbstractServer;
use OpenSwoole\Http\Request as SwooleRequest;
use OpenSwoole\Process;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server as SwooleServer;

class Server extends AbstractServer
{
    private ?SwooleServer $server = null;

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

        if ($this->server->exists($connectionID)) {
            $isSuccess = $this->server->push($connectionID, json_encode([
                'event' => $event,
                'data' => $data
            ]), $opcodeNumber, $flags);
        } else {
            $this->connectionStorage->remove($connectionID);

            $this->eventDispatcher->dispatch(new RemoveConnectionEvent($this, $connectionID), RemoveConnectionEvent::NAME);
        }

        $this->eventDispatcher->dispatch(new MessageSentEvent($this, $connectionID, $event, $data, $isSuccess, $opcode, $flags), MessageSentEvent::NAME);

        return $isSuccess;
    }

    public function start(): bool
    {
        $this->init();

        $this->eventDispatcher->dispatch(new StartServerEvent($this), StartServerEvent::NAME);

        return $this->server->start();
    }

    private function init(): void
    {
        $this->server = new SwooleServer($this->URLBuilder->build($this->getProtocol(), $this->getHost(), $this->getPort()));

        if ([] !== $this->getConfig()) {
            $this->server->set($this->getConfig());
        }

        $this->onOpen();
        $this->onMessage();
        $this->onClose();

        foreach ($this->processes as $callback) {
            $this->server->addProcess(new Process($callback));
        }
    }

    private function onOpen(): void
    {
        $this->server->on('Open', function(SwooleServer $server, SwooleRequest $request): void {
            $this->eventDispatcher->dispatch(new ConnectionOpenEvent($this, $request->fd), ConnectionOpenEvent::NAME);
        });
    }

    private function onMessage(): void
    {
        $this->server->on('Message', function(SwooleServer $server, Frame $frame): void {
            $this->eventDispatcher->dispatch(
                new MessageEvent($this, new Message($this->messageConverter, $frame)),
                MessageEvent::NAME
            );
        });
    }

    private function onClose(): void
    {
        $this->server->on('Close', function(SwooleServer $server, int $connectionID): void {
            $this->eventDispatcher->dispatch(new ConnectionClosedEvent($this, $connectionID), ConnectionClosedEvent::NAME);
        });
    }
}