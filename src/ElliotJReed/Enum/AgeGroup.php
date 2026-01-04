<?php

declare(strict_types=1);

namespace ElliotJReed\Enum;

enum AgeGroup: string
{
    case NEWBORN = 'newborn';
    case INFANT = 'infant';
    case TODDLER = 'toddler';
    case KIDS = 'kids';
    case ADULT = 'adult';
}
