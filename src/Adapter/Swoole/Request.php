<?php

namespace Codememory\WebSocketServerBundle\Adapter\Swoole;

use Codememory\WebSocketServerBundle\Interfaces\ConnectionRequestInterface;
use Codememory\WebSocketServerBundle\Interfaces\RequestInterface;
use Codememory\WebSocketServerBundle\Service\ConnectionRequest;
use OpenSwoole\Http\Request as SwooleRequest;

final class Request implements RequestInterface
{
    private readonly ConnectionRequestInterface $connectionRequest;

    public function __construct(
        private readonly SwooleRequest $request
    ) {
        $this->connectionRequest = new ConnectionRequest($this->request->fd);
    }

    public function getConnectionRequest(): ConnectionRequestInterface
    {
        return $this->connectionRequest;
    }
}