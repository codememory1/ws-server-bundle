<?php

namespace Codememory\WebSocketServerBundle\Server\Swoole;

use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Interfaces\MessageConverterInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use OpenSwoole\WebSocket\Frame;

final readonly class Message implements MessageInterface
{
    public function __construct(
        protected MessageConverterInterface $messageConverter,
        protected Frame $frame
    ) {
    }

    public function getSenderConnectionID(): int
    {
        return $this->frame->fd;
    }

    public function getValue(): mixed
    {
        return $this->messageConverter->convert($this->frame->data);
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