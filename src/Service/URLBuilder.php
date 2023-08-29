<?php

namespace Codememory\WebSocketServerBundle\Service;

use Codememory\WebSocketServerBundle\Interfaces\URLBuilderInterface;

final class URLBuilder implements URLBuilderInterface
{
    public function build(string $protocol, string $host): string
    {
        if ('' === $protocol) {
            return "{$host}";
        }

        return "{$protocol}://{$host}";
    }
}