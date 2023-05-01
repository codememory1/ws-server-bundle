<?php

namespace Codememory\WebSocketServerBundle\Command;

use Codememory\WebSocketServerBundle\Interfaces\ServerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class WebSocketServerCommand extends Command
{
    public function __construct(
        private readonly ServerInterface $server
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $style = new SymfonyStyle($input, $output);

        $style->info('Сервер запущен');

        $this->server->start();
    }
}