<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

use Codememory\WebSocketServerBundle\Enum\CloseCode;
use Codememory\WebSocketServerBundle\Enum\Opcode;

interface ServerInterface
{
    public function getProtocol(): string;

    public function setProtocol(string $protocol): self;

    public function getHost(): string;

    public function setHost(string $host): self;

    public function getPort(): int;

    public function setPort(int $port): self;

    public function getAutoDisconnect(): ?int;

    public function setAutoDisconnect(?int $seconds): self;

    public function getConfig(): array;

    public function setConfig(array $config): self;

    public function addProcess(callable $callback): self;

    public function sendMessage(int $connectionID, string $event, array $data, Opcode $opcode = Opcode::TEXT, ?int $flags = null): bool;

    public function disconnect(int $connectionID, CloseCode $code = CloseCode::NORMAL, ?string $reason = null): bool;

    public function start(): bool;
}