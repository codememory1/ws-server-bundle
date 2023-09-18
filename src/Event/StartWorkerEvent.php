<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StartWorkerEvent
{
    public const NAME = 'codememory.ws_server.start_worker';

    public function __construct(
        public readonly InputInterface $input,
        public readonly OutputInterface $output,
        public readonly ServerInterface $server
    ) {
    }
}