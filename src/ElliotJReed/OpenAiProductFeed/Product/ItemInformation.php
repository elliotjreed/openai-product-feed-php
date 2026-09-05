<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Attribute\Dimensions;
use ElliotJReed\OpenAiProductFeed\Enum\AgeGroup;
use ElliotJReed\OpenAiProductFeed\Enum\Condition;
use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;
use ElliotJReed\OpenAiProductFeed\Enum\Gender;
use ElliotJReed\OpenAiProductFeed\Enum\SizeSystem;
use ElliotJReed\OpenAiProductFeed\Enum\WeightUnit;
use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * The physical characteristics and classification of the item, which drive categorisation, filtering and search
 * relevance.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait ItemInformation
{
    private const float MAXIMUM_MEASUREMENT = 1000000.0;

    private ?string $condition = null;
    private ?string $productCategory = null;
    private ?string $material = null;
    private ?string $colour = null;
    private ?string $size = null;
    private ?string $sizeSystem = null;
    private ?string $gender = null;
    private ?string $ageGroup = null;
    private ?Dimensions $dimensions = null;
    private ?string $length = null;
    private ?string $width = null;
    private ?string $height = null;
    private ?string $dimensionsUnit = null;
    private ?string $weight = null;
    private ?string $itemWeightUnit = null;
    private ?bool $digital = null;

    /**
     * The condition of the item being sold.
     *
     * @return $this
     */
    public function setCondition(Condition | string $condition): static
    {
        $this->condition = Validate::enum('condition', $condition, Condition::class)->value;

        return $this;
    }

    /**
     * Your own category path for the item, from broad to specific, separated by a greater-than sign, for example
     * "Apparel & Accessories > Shoes".
     *
     * @return $this
     */
    public function setProductCategory(string $productCategory): static
    {
        $this->productCategory = Validate::text('product_category', $productCategory);

        return $this;
    }

    /**
     * The principal materials the item is made from.
     *
     * @return $this
     */
    public function setMaterial(string $material): static
    {
        $this->material = Validate::text('material', $material);

        return $this;
    }

    /**
     * The selected colour, which must be consistent with the product image. Rendered as the "color" field.
     *
     * Where colour is one of the options distinguishing a variant, set it in the variant options as well.
     *
     * @return $this
     */
    public function setColour(string $colour): static
    {
        $this->colour = Validate::text('color', $colour);

        return $this;
    }

    /**
     * The selected size label.
     *
     * Where size is one of the options distinguishing a variant, set it in the variant options as well.
     *
     * @return $this
     */
    public function setSize(string $size): static
    {
        $this->size = Validate::text('size', $size);

        return $this;
    }

    /**
     * The national sizing system the size label is expressed in.
     *
     * @return $this
     */
    public function setSizeSystem(SizeSystem | string $sizeSystem): static
    {
        $this->sizeSystem = Validate::enum('size_system', $sizeSystem, SizeSystem::class)->value;

        return $this;
    }

    /**
     * The gender the item is intended for.
     *
     * @return $this
     */
    public function setGender(Gender | string $gender): static
    {
        $this->gender = Validate::enum('gender', $gender, Gender::class)->value;

        return $this;
    }

    /**
     * The age group the item is intended for.
     *
     * @return $this
     */
    public function setAgeGroup(AgeGroup | string $ageGroup): static
    {
        $this->ageGroup = Validate::enum('age_group', $ageGroup, AgeGroup::class)->value;

        return $this;
    }

    /**
     * The item's overall dimensions as a single object, which is the form the specification prefers.
     *
     * Where this is set it takes precedence over the separate length, width, height and dimensions unit attributes.
     *
     * @return $this
     */
    public function setDimensions(Dimensions $dimensions): static
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * The item's length as a separate attribute, which requires the dimensions unit to be set as well.
     *
     * @return $this
     */
    public function setLength(float | int | string $length): static
    {
        $this->length = Validate::decimal('length', $length, 0.0, self::MAXIMUM_MEASUREMENT, exclusiveMinimum: true);

        return $this;
    }

    /**
     * The item's width as a separate attribute, which requires the dimensions unit to be set as well.
     *
     * @return $this
     */
    public function setWidth(float | int | string $width): static
    {
        $this->width = Validate::decimal('width', $width, 0.0, self::MAXIMUM_MEASUREMENT, exclusiveMinimum: true);

        return $this;
    }

    /**
     * The item's height as a separate attribute, which requires the dimensions unit to be set as well.
     *
     * @return $this
     */
    public function setHeight(float | int | string $height): static
    {
        $this->height = Validate::decimal('height', $height, 0.0, self::MAXIMUM_MEASUREMENT, exclusiveMinimum: true);

        return $this;
    }

    /**
     * The unit the separate length, width and height attributes are measured in.
     *
     * @return $this
     */
    public function setDimensionsUnit(DimensionsUnit | string $dimensionsUnit): static
    {
        $this->dimensionsUnit = Validate::enum('dimensions_unit', $dimensionsUnit, DimensionsUnit::class)->value;

        return $this;
    }

    /**
     * The net weight of the item, excluding its packaging, along with the unit it is measured in.
     *
     * The unit is taken as an argument rather than set separately, because the specification requires it whenever a
     * weight is given.
     *
     * @return $this
     */
    public function setWeight(float | int | string $weight, WeightUnit | string $unit): static
    {
        $this->weight = Validate::decimal('weight', $weight, 0.0, self::MAXIMUM_MEASUREMENT, exclusiveMinimum: true);
        $this->itemWeightUnit = Validate::enum('item_weight_unit', $unit, WeightUnit::class)->value;

        return $this;
    }

    /**
     * Whether the item is delivered digitally rather than physically. Requires a configured integration.
     *
     * @return $this
     */
    public function setDigital(bool $digital): static
    {
        $this->digital = $digital;

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function itemInformationAttributes(): array
    {
        return Attributes::set([
            'condition' => $this->condition,
            'product_category' => $this->productCategory,
            'material' => $this->material,
            'color' => $this->colour,
            'size' => $this->size,
            'size_system' => $this->sizeSystem,
            'gender' => $this->gender,
            'age_group' => $this->ageGroup,
            'dimensions' => $this->dimensions,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'dimensions_unit' => $this->dimensionsUnit,
            'weight' => $this->weight,
            'item_weight_unit' => $this->itemWeightUnit,
            'is_digital' => $this->digital
        ]);
    }
}
