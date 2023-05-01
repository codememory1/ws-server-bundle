<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

use Codememory\WebSocketServerBundle\Enum\Opcode;

interface ServerInterface
{
    public function getProtocol(): ?string;

    public function setProtocol(string $protocol): self;

    public function getHost(): ?string;

    public function setHost(string $host): self;

    public function getPort(): ?int;

    public function setPort(int $port): self;

    public function sendMessage(ConnectionRequestInterface $connectionRequest, string $event, array $data, Opcode $opcode = Opcode::TEXT, ?int $flags = null): bool;

    public function disconnect(ConnectionRequestInterface $connectionRequest, ?int $code = null, ?string $reason = null): bool;

    public function addProcess(object $process): self;

    /**
     * @return array<int, ConnectionRequestInterface>
     */
    public function getConnectionRequests(): array;

    public function connectionExist(string|int $id): bool;

    public function start(): bool;
}