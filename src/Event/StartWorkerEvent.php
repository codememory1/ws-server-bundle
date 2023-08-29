<?php

namespace Codememory\WebSocketServerBundle\Event;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class StartWorkerEvent
{
    public const NAME = 'codememory.ws_server.start_worker';

    public function __construct(
        public InputInterface $input,
        public OutputInterface $output,
        public ServerInterface $server
    ) {
    }
}