<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final readonly class RemoveConnectionEvent
{
    public const NAME = 'codememory.ws_server.remove_connection';

    public function __construct(
        public ServerInterface $server,
        public int $connectionID
    ) {
    }
}