<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageEventExtractorInterface
{
    public function extractEventName(MessageInterface $message): ?string;
}