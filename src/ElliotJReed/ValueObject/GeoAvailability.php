<?php

declare(strict_types=1);

namespace ElliotJReed\ValueObject;

use ElliotJReed\Enum\Availability;

final readonly class GeoAvailability
{
    public function __construct(
        public Availability $availability,
        public string $region
    ) {
    }

    public function toString(): string
    {
        return \sprintf('%s (%s)', $this->availability->value, $this->region);
    }
}
