<?php

namespace Codememory\WebSocketServerBundle\Converters;

use Codememory\WebSocketServerBundle\Interfaces\MessageConverterInterface;
use function is_array;
use function is_string;
use const JSON_ERROR_NONE;

final class ToArrayMessageConverter implements MessageConverterInterface
{
    public function convert(mixed $message): array
    {
        if (is_array($message)) {
            return $message;
        }

        if (is_string($message)) {
            $message = json_decode($message, true);

            if (empty($message) || JSON_ERROR_NONE !== json_last_error() || !is_array($message)) {
                return [];
            }

            return $message;
        }

        return [];
    }
}