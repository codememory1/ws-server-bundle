<?php

namespace Codememory\WebSocketServerBundle\Interfaces;

interface MessageInputDataExtractorInterface
{
    public function extractInputData(MessageInterface $message): array;
}