<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionRequestInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class ConnectionClosedEvent
{
    public const NAME = 'codememory.ws_server.connection_closed';

    public function __construct(
        public readonly ServerInterface $server,
        public readonly ConnectionRequestInterface $connectionRequest
    ) {
    }
}