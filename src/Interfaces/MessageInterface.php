<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

use Codememory\WebSocketServerBundle\Enum\Opcode;

interface MessageInterface
{
    public function getSenderConnectionID(): int;

    public function getEvent(): ?string;

    public function getData(): array;

    public function getFullMessage(): array;

    public function getOpcode(): ?Opcode;

    public function isFinish(): bool;
}