<?php

declare(strict_types=1);

namespace ElliotJReed\Enum;

enum RelationshipType: string
{
    case PART_OF_SET = 'part_of_set';
    case REQUIRED_PART = 'required_part';
    case OFTEN_BOUGHT_WITH = 'often_bought_with';
    case SUBSTITUTE = 'substitute';
    case DIFFERENT_BRAND = 'different_brand';
    case ACCESSORY = 'accessory';
}
