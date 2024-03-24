<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Models;

enum BridgeType : string
{
    case SSE = "sse";
    case JS = "js";
}
