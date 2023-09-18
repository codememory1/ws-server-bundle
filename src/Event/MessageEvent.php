<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class MessageEvent
{
    public const NAME = 'codememory.ws_server.message';

    public function __construct(
        public readonly ServerInterface $server,
        public readonly MessageInterface $message
    ) {
    }
}