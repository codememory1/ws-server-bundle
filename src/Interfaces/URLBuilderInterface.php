<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface URLBuilderInterface
{
    public function build(string $protocol, string $host): string;
}