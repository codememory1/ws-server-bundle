<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

use Codememory\WebSocketServerBundle\Enum\Opcode;

interface FrameInterface
{
    public function getConnectionRequest(): ConnectionRequestInterface;

    public function getFullData(): array;

    public function getData(): array;

    public function getHeaders(): array;

    public function getEventType(): ?string;

    public function dataIsValid(): bool;

    public function getOpcode(): ?Opcode;

    public function isFinish(): bool;
}