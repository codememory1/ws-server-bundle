<?php

namespace Codememory\WebSocketServerBundle\Enum;

enum Opcode
{
    case TEXT;
    case BINARY;
    case CLOSE;
    case PING;
    case PONG;
}