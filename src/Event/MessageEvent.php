<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final readonly class MessageEvent
{
    public const NAME = 'codememory.ws_server.message';

    public function __construct(
        public ServerInterface $server,
        public MessageInterface $message
    ) {
    }
}