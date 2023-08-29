<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Enum\Opcode;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;

final readonly class MessageSentEvent
{
    public const NAME = 'codememory.ws_server.message_sent';

    public function __construct(
        public ServerInterface $server,
        public int $connectionID,
        public string $event,
        public array $data,
        public bool $success,
        public Opcode $opcode,
        public ?int $flags = null
    ) {
    }
}