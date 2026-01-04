<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Enum;

use ElliotJReed\Enum\RelationshipType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelationshipType::class)]
final class RelationshipTypeTest extends TestCase
{
    public function testEnumCasesExist(): void
    {
        $this->assertSame('part_of_set', RelationshipType::PART_OF_SET->value);
        $this->assertSame('required_part', RelationshipType::REQUIRED_PART->value);
        $this->assertSame('often_bought_with', RelationshipType::OFTEN_BOUGHT_WITH->value);
        $this->assertSame('substitute', RelationshipType::SUBSTITUTE->value);
        $this->assertSame('different_brand', RelationshipType::DIFFERENT_BRAND->value);
        $this->assertSame('accessory', RelationshipType::ACCESSORY->value);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(RelationshipType::PART_OF_SET, RelationshipType::from('part_of_set'));
        $this->assertSame(RelationshipType::REQUIRED_PART, RelationshipType::from('required_part'));
        $this->assertSame(RelationshipType::OFTEN_BOUGHT_WITH, RelationshipType::from('often_bought_with'));
        $this->assertSame(RelationshipType::SUBSTITUTE, RelationshipType::from('substitute'));
        $this->assertSame(RelationshipType::DIFFERENT_BRAND, RelationshipType::from('different_brand'));
        $this->assertSame(RelationshipType::ACCESSORY, RelationshipType::from('accessory'));
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(6, RelationshipType::cases());
    }
}
