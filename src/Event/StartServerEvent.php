<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class StartServerEvent
{
    public const NAME = 'codememory.ws_server.start_server';

    public function __construct(
        public readonly ServerInterface $server
    ) {
    }
}