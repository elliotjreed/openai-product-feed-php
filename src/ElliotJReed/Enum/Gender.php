<?php

declare(strict_types=1);

namespace ElliotJReed\Enum;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case UNISEX = 'unisex';
}
