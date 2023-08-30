<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageQueueInterface
{
    public function getID(): string;

    public function getConnectionID(): int;

    public function getEvent(): string;

    public function getData(): array;
}