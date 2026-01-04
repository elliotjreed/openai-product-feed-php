<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Enum;

use ElliotJReed\Enum\Availability;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Availability::class)]
final class AvailabilityTest extends TestCase
{
    public function testEnumCasesExist(): void
    {
        $this->assertSame('in_stock', Availability::IN_STOCK->value);
        $this->assertSame('out_of_stock', Availability::OUT_OF_STOCK->value);
        $this->assertSame('preorder', Availability::PREORDER->value);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(Availability::IN_STOCK, Availability::from('in_stock'));
        $this->assertSame(Availability::OUT_OF_STOCK, Availability::from('out_of_stock'));
        $this->assertSame(Availability::PREORDER, Availability::from('preorder'));
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(3, Availability::cases());
    }
}
