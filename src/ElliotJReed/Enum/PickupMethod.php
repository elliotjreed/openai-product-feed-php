<?php

declare(strict_types=1);

namespace ElliotJReed\Enum;

enum PickupMethod: string
{
    case IN_STORE = 'in_store';
    case RESERVE = 'reserve';
    case NOT_SUPPORTED = 'not_supported';
}
