<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Attribute\Dimensions;
use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(Dimensions::class)]
final class DimensionsTest extends TestCase
{
    public function testItRendersEveryDimensionAlongsideItsUnit(): void
    {
        $this->assertSame(
            ['length' => '30', 'width' => '20', 'height' => '12', 'unit' => 'cm'],
            new Dimensions(DimensionsUnit::Centimetres)
                ->setLength('30')
                ->setWidth('20')
                ->setHeight('12')
                ->toArray()
        );
    }

    public function testItAcceptsTheUnitAsAString(): void
    {
        $this->assertSame(
            ['length' => '30', 'width' => '20', 'unit' => 'in'],
            new Dimensions('in')->setLength('30')->setWidth('20')->toArray()
        );
    }

    public function testItRendersOnlyTheTwoDimensionsWhichAreSet(): void
    {
        $this->assertSame(
            ['width' => '20', 'height' => '12', 'unit' => 'mm'],
            new Dimensions(DimensionsUnit::Millimetres)->setWidth('20')->setHeight('12')->toArray()
        );
    }

    public function testItPreservesTheDecimalPrecisionOfADimensionGivenAsAString(): void
    {
        $this->assertSame(
            ['length' => '30.500', 'width' => '20.0', 'unit' => 'cm'],
            new Dimensions(DimensionsUnit::Centimetres)->setLength('30.500')->setWidth('20.0')->toArray()
        );
    }

    public function testItThrowsWhenFewerThanTwoDimensionsAreSet(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The dimensions attribute must be given at least two of length, width and height, 1 given.'
        );

        new Dimensions(DimensionsUnit::Centimetres)->setLength('30')->toArray();
    }

    public function testItThrowsWhenNoDimensionsAreSet(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('at least two of length, width and height, 0 given');

        new Dimensions(DimensionsUnit::Centimetres)->toArray();
    }

    public function testItThrowsWhenADimensionIsNotPositive(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The length attribute must be greater than 0, "0" given.');

        new Dimensions(DimensionsUnit::Centimetres)->setLength('0');
    }

    public function testItThrowsWhenTheUnitIsNotSupported(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            '"yards" is not a valid value for the dimensions_unit attribute. Allowed values: in, cm, ft, m, mm.'
        );

        new Dimensions('yards');
    }
}
