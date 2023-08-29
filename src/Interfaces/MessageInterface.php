<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

use Codememory\WebSocketServerBundle\Enum\Opcode;

interface MessageInterface
{
    public function getSenderConnectionID(): int;

    public function getValue(): mixed;

    public function getOpcode(): ?Opcode;

    public function isFinish(): bool;
}