<?php

namespace Codememory\WebSocketServerBundle\Command;

use Codememory\WebSocketServerBundle\Event\StartWorkerEvent;
use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class WebSocketServerCommand extends Command
{
    public function __construct(
        private readonly ServerInterface $server,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $style = new SymfonyStyle($input, $output);
        $listening = sprintf(
            '%s://%s:%s',
            $this->server->getProtocol(),
            $this->server->getHost(),
            $this->server->getPort()
        );

        $style->info('The server is successful running');
        $style->text("Listening: <fg=bright-cyan>{$listening}</>");

        $this->eventDispatcher->dispatch(new StartWorkerEvent($input, $output, $this->server), StartWorkerEvent::NAME);

        $this->server->start();
    }
}