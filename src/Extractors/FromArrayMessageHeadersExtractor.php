<?php

namespace Codememory\WebSocketServerBundle\Extractors;

use Codememory\WebSocketServerBundle\Interfaces\MessageHeadersExtractorInterface;
use Codememory\WebSocketServerBundle\Interfaces\MessageInterface;
use function is_array;

final class FromArrayMessageHeadersExtractor implements MessageHeadersExtractorInterface
{
    public function extractHeaders(MessageInterface $message): array
    {
        $messageValue = $message->getValue();

        if (array_key_exists('headers', $messageValue) && is_array($messageValue['headers'])) {
            $headers = [];

            foreach ($messageValue['headers'] as $name => $value) {
                if (!is_array($value)) {
                    $headers[(string) $name] = (string) $value;
                }
            }

            return $headers;
        }

        return [];
    }
}