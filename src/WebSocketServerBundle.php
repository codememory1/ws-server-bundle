<?php

namespace Codememory\WebSocketServerBundle;

use Codememory\WebSocketServerBundle\DependencyInjection\WebSocketServerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class WebSocketServerBundle extends Bundle
{
    // Servers
    public const DEFAULT_SERVER_SERVICE = 'codememory.ws_server.default_server';

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new WebSocketServerExtension();
    }
}