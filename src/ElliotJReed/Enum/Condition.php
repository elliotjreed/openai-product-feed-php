<?php

declare(strict_types=1);

namespace ElliotJReed\Enum;

enum Condition: string
{
    case NEW = 'new';
    case REFURBISHED = 'refurbished';
    case USED = 'used';
}
