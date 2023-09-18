<?php

namespace Codememory\WebSocketServerBundle\Server\Swoole;

use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use function is_array;
use function is_string;
use const JSON_ERROR_NONE;
use OpenSwoole\WebSocket\Frame;

final class Message implements MessageInterface
{
    private ?array $fullMessage = null;

    public function __construct(
        private readonly Frame $frame
    ) {
    }

    public function getSenderConnectionID(): int
    {
        return $this->frame->fd;
    }

    public function getEvent(): ?string
    {
        $event = $this->getFullMessage()['event'] ?? null;

        if (!is_string($event)) {
            return null;
        }

        return $event;
    }

    public function getData(): array
    {
        $data = $this->getFullMessage()['data'] ?? [];

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    public function getFullMessage(): array
    {
        if (null === $this->fullMessage) {
            if (null === $this->frame->data) {
                return [];
            }

            $message = json_decode($this->frame->data, true);

            if (empty($message) || JSON_ERROR_NONE !== json_last_error() || !is_array($message)) {
                return [];
            }

            return $message;
        }

        return $this->fullMessage;
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