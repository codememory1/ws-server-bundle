<?php

namespace Codememory\WebSocketServerBundle\Adapter\Swoole;

use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Interfaces\ConnectionRequestInterface;
use Codememory\WebSocketServerBundle\Interfaces\FrameInterface;
use function is_array;
use function is_string;
use const JSON_THROW_ON_ERROR;
use JsonException;
use OpenSwoole\WebSocket\Frame as SwooleFrame;

final class Frame implements FrameInterface
{
    public function __construct(
        private readonly SwooleFrame $frame,
        private readonly ConnectionRequestInterface $connectionRequest
    ) {
    }

    public function getConnectionRequest(): ConnectionRequestInterface
    {
        return $this->connectionRequest;
    }

    public function getFullData(): array
    {
        try {
            return json_decode($this->frame->data, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }
    }

    public function getData(): array
    {
        return $this->getFullData()['data'] ?? [];
    }

    public function getHeaders(): array
    {
        return $this->getFullData()['headers'] ?? [];
    }

    public function getEventType(): ?string
    {
        return $this->getFullData()['event'] ?? null;
    }

    public function dataIsValid(): bool
    {
        return (bool) (array_key_exists('event', $this->getFullData())
            && array_key_exists('headers', $this->getFullData())
            && array_key_exists('data', $this->getFullData())
            && is_string($this->getEventType())
            && !empty($this->getEventType())
            && is_array($this->getData())
            && is_array($this->getHeaders()));
    }

    public function getOpcode(): ?Opcode
    {
        return match ($this->frame->opcode) {
            1 => Opcode::TEXT,
            2 => Opcode::BINARY,
            8 => Opcode::CLOSE,
            9 => Opcode::PING,
            10 => Opcode::PONG
        };
    }

    public function isFinish(): bool
    {
        return $this->frame->finish;
    }
}