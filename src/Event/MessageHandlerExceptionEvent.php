<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Throwable;

final readonly class MessageHandlerExceptionEvent
{
    public const NAME = 'codememory.ws_server.message_handler_exception';

    public function __construct(
        public ServerInterface $server,
        public int $connectionID,
        public Throwable $throwable
    ) {
    }
}