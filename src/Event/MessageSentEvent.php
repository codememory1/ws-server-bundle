<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final class MessageSentEvent
{
    public const NAME = 'codememory.ws_server.message_sent';

    public function __construct(
        public readonly ServerInterface $server,
        public readonly int $connectionID,
        public readonly string $event,
        public readonly array $data,
        public readonly bool $success,
        public readonly Opcode $opcode,
        public readonly ?int $flags = null
    ) {
    }
}