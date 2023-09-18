<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class ConnectionOpenEvent
{
    public const NAME = 'codememory.ws_server.connection_open';

    public function __construct(
        public readonly ServerInterface $server,
        public readonly int|string $connectionID,
        public readonly string $secWebsocketKey
    ) {
    }
}