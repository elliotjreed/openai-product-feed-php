<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Enum;

use ElliotJReed\Enum\Condition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Condition::class)]
final class ConditionTest extends TestCase
{
    public function testEnumCasesExist(): void
    {
        $this->assertSame('new', Condition::NEW->value);
        $this->assertSame('refurbished', Condition::REFURBISHED->value);
        $this->assertSame('used', Condition::USED->value);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(Condition::NEW, Condition::from('new'));
        $this->assertSame(Condition::REFURBISHED, Condition::from('refurbished'));
        $this->assertSame(Condition::USED, Condition::from('used'));
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(3, Condition::cases());
    }
}
