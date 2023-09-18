<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final readonly class ConnectionRemovedEvent
{
    public const NAME = 'codememory.ws_server.connection_removed';

    public function __construct(
        public ServerInterface $server,
        public int $connectionID
    ) {
    }
}