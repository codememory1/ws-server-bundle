<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageHeadersExtractorInterface
{
    /**
     * @return array<string, string>
     */
    public function extractHeaders(MessageInterface $message): array;
}