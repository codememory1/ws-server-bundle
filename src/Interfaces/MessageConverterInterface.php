<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageConverterInterface
{
    public function convert(mixed $message): mixed;
}