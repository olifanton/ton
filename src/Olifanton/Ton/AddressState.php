<?php declare(strict_types=1);

namespace Olifanton\Ton;

enum AddressState : string
{
    case ACTIVE = "active";
    case UNINITIALIZED = "uninitialized";
    case FROZEN = "frozen";
    case UNKNOWN = "unknown";
}
