<?php

namespace Codememory\WebSocketServerBundle;

use Codememory\WebSocketServerBundle\DependencyInjection\WebSocketServerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class WebSocketServerBundle extends Bundle
{
    // Converters
    public const DEFAULT_MESSAGE_CONVERTER_SERVICE = 'codememory.ws_server.default_message_converter';

    // Extractors
    public const DEFAULT_MESSAGE_EVENT_EXTRACTOR_SERVICE = 'codememory.ws_server.default_message_event_extractor';
    public const DEFAULT_MESSAGE_HEADERS_EXTRACTOR_SERVICE = 'codememory.ws_server.default_message_headers_extractor';
    public const DEFAULT_MESSAGE_INPUT_DATA_EXTRACTOR_SERVICE = 'codememory.ws_server.default_message_input_data_extractor';

    // Servers
    public const DEFAULT_SERVER_SERVICE = 'codememory.ws_server.default_server';

    // Storages
    public const DEFAULT_CONNECTION_STORAGE_SERVICE = 'codememory.ws_server.default_connection_storage';
    public const DEFAULT_MESSAGE_QUEUE_STORAGE_SERVICE = 'codememory.ws_server.default_message_queue_storage';

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new WebSocketServerExtension();
    }
}