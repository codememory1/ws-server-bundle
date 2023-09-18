<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Throwable;

final class MessageHandlerExceptionEvent
{
    public const NAME = 'codememory.ws_server.message_handler_exception';

    public function __construct(
        public readonly ServerInterface $server,
        public readonly int $connectionID,
        public readonly Throwable $throwable
    ) {
    }
}