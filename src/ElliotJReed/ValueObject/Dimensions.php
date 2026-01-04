<?php

declare(strict_types=1);

namespace ElliotJReed\ValueObject;

final readonly class Dimensions
{
    public function __construct(
        public float $length,
        public float $width,
        public float $height,
        public string $unit
    ) {
    }

    public function toString(): string
    {
        return \sprintf('%sx%sx%s %s', $this->length, $this->width, $this->height, $this->unit);
    }
}
