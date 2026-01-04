<?php

declare(strict_types=1);

namespace ElliotJReed\Entity;

use DateTime;
use ElliotJReed\Enum\AgeGroup;
use ElliotJReed\Enum\Availability;
use ElliotJReed\Enum\Condition;
use ElliotJReed\Enum\Gender;
use ElliotJReed\Enum\PickupMethod;
use ElliotJReed\Enum\RelationshipType;
use ElliotJReed\ValueObject\DateRange;
use ElliotJReed\ValueObject\Dimensions;
use ElliotJReed\ValueObject\GeoAvailability;
use ElliotJReed\ValueObject\GeoPrice;
use ElliotJReed\ValueObject\Shipping;
use ElliotJReed\ValueObject\UnitPricing;
use Money\Money;

final class Product
{
    // OpenAI Flags
    public bool $enableSearch = false;
    public bool $enableCheckout = false;

    // Basic Product Data
    public ?string $id = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $gtin = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $mpn = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $title = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $description = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $link = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }

    // Item Information
    public ?Condition $condition = null;
    public ?string $productCategory = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $brand = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $material = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?Dimensions $dimensions = null;
    public ?float $length = null;
    public ?float $width = null;
    public ?float $height = null;
    public ?string $dimensionUnit = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?float $weight = null;
    public ?string $weightUnit = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?AgeGroup $ageGroup = null;

    // Media
    public ?string $imageLink = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    /** @var string[] */
    public array $additionalImageLink = [];
    public ?string $videoLink = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $model3dLink = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }

    // Price & Promotions
    public ?Money $price = null;
    public ?Money $salePrice = null;
    public ?DateRange $salePriceEffectiveDate = null;
    public ?UnitPricing $unitPricing = null;
    public ?string $pricingTrend = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }

    // Availability & Inventory
    public ?Availability $availability = null;
    public ?DateTime $availabilityDate = null;
    public ?int $inventoryQuantity = null;
    public ?DateTime $expirationDate = null;
    public ?PickupMethod $pickupMethod = null;
    public ?string $pickupSla = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }

    // Variants
    public ?string $itemGroupId = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $itemGroupTitle = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $color = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $size = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $sizeSystem = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?Gender $gender = null;
    public ?string $offerId = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $customVariant1Category = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $customVariant1Option = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $customVariant2Category = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $customVariant2Option = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $customVariant3Category = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $customVariant3Option = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }

    // Fulfillment
    /** @var Shipping[] */
    public array $shipping = [];
    public ?DateTime $deliveryEstimate = null;

    // Merchant Info
    public ?string $sellerName = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $sellerUrl = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $sellerPrivacyPolicy = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $sellerTos = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }

    // Returns
    public ?string $returnPolicy = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?int $returnWindow = null;

    // Performance Signals
    public ?float $popularityScore = null;
    public ?float $returnRate = null;

    // Compliance
    public ?string $warning = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $warningUrl = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?int $ageRestriction = null;

    // Reviews & Q&A
    public ?int $productReviewCount = null;
    public ?float $productReviewRating = null;
    public ?int $storeReviewCount = null;
    public ?float $storeReviewRating = null;
    public ?string $qAndA = null {
        set(?string $value) => null !== $value ? \trim($value) : null;
    }
    public ?string $rawReviewData = null;

    // Related Products
    /** @var string[] */
    public array $relatedProductId = [];
    public ?RelationshipType $relationshipType = null;

    // Geo Tagging
    /** @var GeoPrice[] */
    public array $geoPrice = [];
    /** @var GeoAvailability[] */
    public array $geoAvailability = [];
}
