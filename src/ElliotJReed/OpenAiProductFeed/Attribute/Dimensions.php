<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * The overall dimensions of an item, as the object form the specification prefers.
 *
 * At least two of the length, width and height must be given, all sharing a single unit. Where this object is set it
 * takes precedence over the separate length, width, height and dimensions_unit attributes.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
final class Dimensions implements StructuredAttribute
{
    private const float MAXIMUM = 1000000.0;

    private readonly DimensionsUnit $unit;

    private ?string $length = null;
    private ?string $width = null;
    private ?string $height = null;

    /**
     * @param DimensionsUnit|string $unit the unit all three measurements are expressed in
     */
    public function __construct(DimensionsUnit | string $unit)
    {
        $this->unit = Validate::enum('dimensions_unit', $unit, DimensionsUnit::class);
    }

    /**
     * The longest side of the item, as a positive number.
     */
    public function setLength(float | int | string $length): self
    {
        $this->length = Validate::decimal('length', $length, 0.0, self::MAXIMUM, exclusiveMinimum: true);

        return $this;
    }

    /**
     * The width of the item, as a positive number.
     */
    public function setWidth(float | int | string $width): self
    {
        $this->width = Validate::decimal('width', $width, 0.0, self::MAXIMUM, exclusiveMinimum: true);

        return $this;
    }

    /**
     * The height of the item, as a positive number.
     */
    public function setHeight(float | int | string $height): self
    {
        $this->height = Validate::decimal('height', $height, 0.0, self::MAXIMUM, exclusiveMinimum: true);

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $measurements = \array_filter([
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height
        ], static fn (?string $measurement): bool => null !== $measurement);

        if (\count($measurements) < 2) {
            throw InvalidAttribute::tooFewDimensions(\count($measurements));
        }

        return [...$measurements, 'unit' => $this->unit->value];
    }
}
