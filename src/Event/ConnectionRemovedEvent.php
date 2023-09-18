<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class ConnectionRemovedEvent
{
    public const NAME = 'codememory.ws_server.connection_removed';

    public function __construct(
        public readonly ServerInterface $server,
        public readonly int $connectionID
    ) {
    }
}