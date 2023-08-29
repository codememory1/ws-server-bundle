<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

use Codememory\WebSocketServerBundle\Enum\CloseCode;
use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Enum\StatsMode;

interface ServerInterface
{
    public function getProtocol(): string;

    public function setProtocol(string $protocol): self;

    public function getHost(): string;

    public function setHost(string $host): self;

    public function getPort(): int;

    public function setPort(int $port): self;

    public function getConfig(): array;

    public function setConfig(array $config): self;

    public function addProcess(callable $callback): self;

    public function sendMessage(int $connectionID, string $event, array $data, Opcode $opcode = Opcode::TEXT, ?int $flags = null): bool;

    public function disconnect(int $connectionID, CloseCode $code = CloseCode::NORMAL, ?string $reason = null): bool;

    public function existConnection(int $id): bool;

    public function tick(int $ms, callable $callback): self;

    public function getStats(StatsMode $mode = StatsMode::DEFAULT): array|string|false;

    public function on(string $event, callable $callback): self;

    public function task(mixed $data, int $dstWorkerID = -1, ?callable $finishCallback = null): ?int;

    public function taskWait(mixed $data, float $timeout = 0.5, int $dstWorkerID = -1): string|bool;

    public function taskWaitMulti(array $tasks, float $timeout = 0.5): bool|array;

    public function toggleConnection(int $connectionID, bool $isPause = true): bool;

    public function start(): bool;
}