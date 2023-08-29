<?php

namespace Codememory\WebSocketServerBundle\Enum;

enum CloseCode: int
{
    case NORMAL = 1000;
    case GOING_AWAY = 1001;
    case PROTOCOL_ERROR = 1002;
    case DATA_ERROR = 1003;
    case STATUS_ERROR = 1005;
    case ABNORMAL = 1006;
    case MESSAGE_ERROR = 1007;
    case POLICY_ERROR = 1008;
    case MESSAGE_TOO_BIG = 1009;
    case EXTENSION_MISSING = 1010;
    case SERVER_ERROR = 1011;
    case CLOSE_TLS = 1015;
}