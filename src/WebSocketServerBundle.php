<?php

namespace Codememory\WebSocketServerBundle;

use Codememory\WebSocketServerBundle\DependencyInjection\Compiler\AddMessageEventHandlerPass;
use Codememory\WebSocketServerBundle\DependencyInjection\WebSocketServerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class WebSocketServerBundle extends Bundle
{
    public const MESSAGE_EVENT_HANDLER_TAG = 'codememory.ws_server.event_handler';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddMessageEventHandlerPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new WebSocketServerExtension();
    }
}