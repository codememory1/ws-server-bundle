<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\RequestInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class ConnectEvent
{
    public const NAME = 'codememory.ws_server.connect';

    public function __construct(
        public readonly ServerInterface $server,
        public readonly RequestInterface $request
    ) {
    }
}