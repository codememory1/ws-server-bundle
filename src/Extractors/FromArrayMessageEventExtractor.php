<?php

namespace Codememory\WebSocketServerBundle\Extractors;

use Codememory\WebSocketServerBundle\Interfaces\MessageEventExtractorInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use function is_string;

final class FromArrayMessageEventExtractor implements MessageEventExtractorInterface
{
    public function extractEventName(MessageInterface $message): ?string
    {
        $messageValue = $message->getValue();

        if (array_key_exists('event', $messageValue) && is_string($messageValue['event'])) {
            return $messageValue['event'];
        }

        return null;
    }
}