<?php

declare(strict_types=1);

namespace ElliotJReed\ValueObject;

final readonly class UnitPricing
{
    public function __construct(
        public float $measure,
        public string $measureUnit,
        public float $base,
        public string $baseUnit
    ) {
    }

    public function toString(): string
    {
        return \sprintf('%s %s / %s %s', $this->measure, $this->measureUnit, $this->base, $this->baseUnit);
    }
}
