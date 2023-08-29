<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final readonly class ConnectionClosedEvent
{
    public const NAME = 'codememory.ws_server.connection_closed';

    public function __construct(
        public ServerInterface $server,
        public int $connectionID
    ) {
    }
}