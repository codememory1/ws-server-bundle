<?php

namespace Codememory\WebSocketServerBundle\Extractors;

use Codememory\WebSocketServerBundle\Interfaces\MessageInputDataExtractorInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use function is_array;

final class FromArrayMessageInputDataExtractor implements MessageInputDataExtractorInterface
{
    public function extractInputData(MessageInterface $message): array
    {
        $messageValue = $message->getValue();

        if (array_key_exists('input_data', $messageValue) && is_array($messageValue['input_data'])) {
            return $messageValue['input_data'];
        }

        return [];
    }
}