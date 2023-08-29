<?php

namespace Codememory\WebSocketServerBundle\Enum;

enum StatsMode: int
{
    case DEFAULT = 0;
    case JSON = 1;
    case OPENMETRICS = 2;
}