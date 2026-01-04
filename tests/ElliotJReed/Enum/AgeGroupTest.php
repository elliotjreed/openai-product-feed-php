<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Enum;

use ElliotJReed\Enum\AgeGroup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AgeGroup::class)]
final class AgeGroupTest extends TestCase
{
    public function testEnumCasesExist(): void
    {
        $this->assertSame('newborn', AgeGroup::NEWBORN->value);
        $this->assertSame('infant', AgeGroup::INFANT->value);
        $this->assertSame('toddler', AgeGroup::TODDLER->value);
        $this->assertSame('kids', AgeGroup::KIDS->value);
        $this->assertSame('adult', AgeGroup::ADULT->value);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(AgeGroup::NEWBORN, AgeGroup::from('newborn'));
        $this->assertSame(AgeGroup::INFANT, AgeGroup::from('infant'));
        $this->assertSame(AgeGroup::TODDLER, AgeGroup::from('toddler'));
        $this->assertSame(AgeGroup::KIDS, AgeGroup::from('kids'));
        $this->assertSame(AgeGroup::ADULT, AgeGroup::from('adult'));
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(5, AgeGroup::cases());
    }
}
