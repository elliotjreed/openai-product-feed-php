<?php

declare(strict_types=1);

namespace ElliotJReed\ValueObject;

use DateTime;

final readonly class DateRange
{
    public function __construct(
        public DateTime $startDate,
        public DateTime $endDate
    ) {
    }

    public function toString(): string
    {
        return \sprintf('%s / %s', $this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d'));
    }
}
