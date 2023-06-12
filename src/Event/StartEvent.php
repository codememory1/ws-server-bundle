<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class StartEvent
{
    public const NAME = 'codememory.ws_server.start';

    public function __construct(
        public readonly ServerInterface $server
    ) {
    }
}