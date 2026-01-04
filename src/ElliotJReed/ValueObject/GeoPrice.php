<?php

declare(strict_types=1);

namespace ElliotJReed\ValueObject;

use Money\Money;

final readonly class GeoPrice
{
    public function __construct(
        public Money $price,
        public string $region
    ) {
    }

    public function toString(): string
    {
        $formattedAmount = \number_format((float) $this->price->getAmount() / 100, 2, '.', '');

        return \sprintf(
            '%s %s (%s)',
            $formattedAmount,
            $this->price->getCurrency()->getCode(),
            $this->region
        );
    }
}
