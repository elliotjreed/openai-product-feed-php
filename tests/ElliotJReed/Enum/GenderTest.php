<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Enum;

use ElliotJReed\Enum\Gender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Gender::class)]
final class GenderTest extends TestCase
{
    public function testEnumCasesExist(): void
    {
        $this->assertSame('male', Gender::MALE->value);
        $this->assertSame('female', Gender::FEMALE->value);
        $this->assertSame('unisex', Gender::UNISEX->value);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(Gender::MALE, Gender::from('male'));
        $this->assertSame(Gender::FEMALE, Gender::from('female'));
        $this->assertSame(Gender::UNISEX, Gender::from('unisex'));
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(3, Gender::cases());
    }
}
