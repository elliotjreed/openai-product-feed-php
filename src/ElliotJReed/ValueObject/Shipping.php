<?php

declare(strict_types=1);

namespace ElliotJReed\ValueObject;

use Money\Money;

final readonly class Shipping
{
    public function __construct(
        public string $country,
        public string $region,
        public string $serviceClass,
        public Money $price
    ) {
    }

    public function toString(): string
    {
        $formattedAmount = \number_format((float) $this->price->getAmount() / 100, 2, '.', '');

        return \sprintf(
            '%s:%s:%s:%s %s',
            $this->country,
            $this->region,
            $this->serviceClass,
            $formattedAmount,
            $this->price->getCurrency()->getCode()
        );
    }
}
