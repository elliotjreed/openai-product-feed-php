<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(VariantOptions::class)]
final class VariantOptionsTest extends TestCase
{
    public function testItMapsOptionNamesToTheSelectedValues(): void
    {
        $this->assertSame(
            ['color' => 'Black', 'size' => '10'],
            new VariantOptions()->add('color', 'Black')->add('size', '10')->toArray()
        );
    }

    public function testItAcceptsTheOptionsAsAConstructorArgument(): void
    {
        $this->assertSame(
            ['color' => 'Black', 'size' => '10'],
            new VariantOptions(['color' => 'Black', 'size' => '10'])->toArray()
        );
    }

    public function testItKeepsOptionsInTheOrderTheyWereAdded(): void
    {
        $this->assertSame(
            ['size', 'color', 'fit'],
            \array_keys(new VariantOptions()->add('size', '10')->add('color', 'Black')->add('fit', 'Wide')->toArray())
        );
    }

    public function testItReplacesAnOptionSetTwice(): void
    {
        $this->assertSame(
            ['color' => 'Navy'],
            new VariantOptions()->add('color', 'Black')->add('color', 'Navy')->toArray()
        );
    }

    public function testItThrowsWhenAnOptionNameIsBlank(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The variant_dict option name attribute must not be empty.');

        new VariantOptions()->add('  ', 'Black');
    }

    public function testItThrowsWhenAnOptionValueIsBlank(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The variant_dict "color" option attribute must not be empty.');

        new VariantOptions()->add('color', '');
    }

    public function testItIsEmptyBeforeAnyOptionIsAdded(): void
    {
        $this->assertSame([], new VariantOptions()->toArray());
    }
}
