<?php

namespace Codememory\WebSocketServerBundle\Event;

final readonly class AddedMessageToQueueEvent
{
    public const NAME = 'codememory.ws_server.added_message_to_queue';

    public function __construct(
        public int $connectionID,
        public string $event,
        public array $data
    ) {
    }
}