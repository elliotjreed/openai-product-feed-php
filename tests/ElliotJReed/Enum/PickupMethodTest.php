<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Enum;

use ElliotJReed\Enum\PickupMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PickupMethod::class)]
final class PickupMethodTest extends TestCase
{
    public function testEnumCasesExist(): void
    {
        $this->assertSame('in_store', PickupMethod::IN_STORE->value);
        $this->assertSame('reserve', PickupMethod::RESERVE->value);
        $this->assertSame('not_supported', PickupMethod::NOT_SUPPORTED->value);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(PickupMethod::IN_STORE, PickupMethod::from('in_store'));
        $this->assertSame(PickupMethod::RESERVE, PickupMethod::from('reserve'));
        $this->assertSame(PickupMethod::NOT_SUPPORTED, PickupMethod::from('not_supported'));
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(3, PickupMethod::cases());
    }
}
