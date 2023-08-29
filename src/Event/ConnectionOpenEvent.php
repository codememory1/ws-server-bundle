<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final readonly class ConnectionOpenEvent
{
    public const NAME = 'codememory.ws_server.connection_open';

    public function __construct(
        public ServerInterface $server,
        public int $connectionID
    ) {
    }
}