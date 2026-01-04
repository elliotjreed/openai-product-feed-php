<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Entity;

use DateTime;
use ElliotJReed\Entity\Product;
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
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Product::class)]
final class ProductTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $product = new Product();

        $this->assertFalse($product->enableSearch);
        $this->assertFalse($product->enableCheckout);
        $this->assertSame([], $product->additionalImageLink);
        $this->assertSame([], $product->shipping);
        $this->assertSame([], $product->relatedProductId);
        $this->assertSame([], $product->geoPrice);
        $this->assertSame([], $product->geoAvailability);
    }

    public function testSetOpenAIFlags(): void
    {
        $product = new Product();
        $product->enableSearch = true;
        $product->enableCheckout = true;

        $this->assertTrue($product->enableSearch);
        $this->assertTrue($product->enableCheckout);
    }

    public function testSetBasicProductData(): void
    {
        $product = new Product();
        $product->id = 'SKU12345';
        $product->gtin = '123456789543';
        $product->mpn = 'GPT5';
        $product->title = 'Men\'s Trail Running Shoes Black';
        $product->description = 'Waterproof trail shoe with cushioned sole';
        $product->link = 'https://example.com/product/SKU12345';

        $this->assertSame('SKU12345', $product->id);
        $this->assertSame('123456789543', $product->gtin);
        $this->assertSame('GPT5', $product->mpn);
        $this->assertSame('Men\'s Trail Running Shoes Black', $product->title);
        $this->assertSame('Waterproof trail shoe with cushioned sole', $product->description);
        $this->assertSame('https://example.com/product/SKU12345', $product->link);
    }

    public function testPropertyHookTrimsStrings(): void
    {
        $product = new Product();
        $product->title = '  Padded Title  ';
        $product->description = '  Padded Description  ';
        $product->brand = '  Padded Brand  ';

        $this->assertSame('Padded Title', $product->title);
        $this->assertSame('Padded Description', $product->description);
        $this->assertSame('Padded Brand', $product->brand);
    }

    public function testSetItemInformation(): void
    {
        $product = new Product();
        $product->condition = Condition::NEW;
        $product->productCategory = 'Apparel & Accessories > Shoes';
        $product->brand = 'OpenAI';
        $product->material = 'Leather';
        $product->weight = 1.5;
        $product->weightUnit = 'lb';
        $product->ageGroup = AgeGroup::ADULT;

        $this->assertSame(Condition::NEW, $product->condition);
        $this->assertSame('Apparel & Accessories > Shoes', $product->productCategory);
        $this->assertSame('OpenAI', $product->brand);
        $this->assertSame('Leather', $product->material);
        $this->assertSame(1.5, $product->weight);
        $this->assertSame('lb', $product->weightUnit);
        $this->assertSame(AgeGroup::ADULT, $product->ageGroup);
    }

    public function testSetDimensions(): void
    {
        $product = new Product();
        $dimensions = new Dimensions(12.0, 8.0, 5.0, 'in');
        $product->dimensions = $dimensions;

        $this->assertSame($dimensions, $product->dimensions);
    }

    public function testSetIndividualDimensions(): void
    {
        $product = new Product();
        $product->length = 12.0;
        $product->width = 8.0;
        $product->height = 5.0;
        $product->dimensionUnit = 'in';

        $this->assertSame(12.0, $product->length);
        $this->assertSame(8.0, $product->width);
        $this->assertSame(5.0, $product->height);
        $this->assertSame('in', $product->dimensionUnit);
    }

    public function testSetMediaFields(): void
    {
        $product = new Product();
        $product->imageLink = 'https://example.com/image1.jpg';
        $product->additionalImageLink = ['https://example.com/image2.jpg', 'https://example.com/image3.jpg'];
        $product->videoLink = 'https://youtu.be/12345';
        $product->model3dLink = 'https://example.com/model.glb';

        $this->assertSame('https://example.com/image1.jpg', $product->imageLink);
        $this->assertCount(2, $product->additionalImageLink);
        $this->assertSame('https://youtu.be/12345', $product->videoLink);
        $this->assertSame('https://example.com/model.glb', $product->model3dLink);
    }

    public function testSetPriceAndPromotions(): void
    {
        $product = new Product();
        $product->price = new Money('7999', new Currency('USD'));
        $product->salePrice = new Money('5999', new Currency('USD'));
        $product->salePriceEffectiveDate = new DateRange(
            new DateTime('2025-07-01'),
            new DateTime('2025-07-15')
        );
        $product->unitPricing = new UnitPricing(16.0, 'oz', 1.0, 'oz');
        $product->pricingTrend = 'Lowest price in 6 months';

        $this->assertInstanceOf(Money::class, $product->price);
        $this->assertSame('7999', $product->price->getAmount());
        $this->assertInstanceOf(Money::class, $product->salePrice);
        $this->assertSame('5999', $product->salePrice->getAmount());
        $this->assertInstanceOf(DateRange::class, $product->salePriceEffectiveDate);
        $this->assertInstanceOf(UnitPricing::class, $product->unitPricing);
        $this->assertSame('Lowest price in 6 months', $product->pricingTrend);
    }

    public function testSetAvailabilityAndInventory(): void
    {
        $product = new Product();
        $product->availability = Availability::IN_STOCK;
        $product->availabilityDate = new DateTime('2025-12-01');
        $product->inventoryQuantity = 25;
        $product->expirationDate = new DateTime('2025-12-01');
        $product->pickupMethod = PickupMethod::IN_STORE;
        $product->pickupSla = '1 day';

        $this->assertSame(Availability::IN_STOCK, $product->availability);
        $this->assertInstanceOf(DateTime::class, $product->availabilityDate);
        $this->assertSame(25, $product->inventoryQuantity);
        $this->assertInstanceOf(DateTime::class, $product->expirationDate);
        $this->assertSame(PickupMethod::IN_STORE, $product->pickupMethod);
        $this->assertSame('1 day', $product->pickupSla);
    }

    public function testSetVariantFields(): void
    {
        $product = new Product();
        $product->itemGroupId = 'SHOE123GROUP';
        $product->itemGroupTitle = 'Men\'s Trail Running Shoes';
        $product->color = 'Blue';
        $product->size = '10';
        $product->sizeSystem = 'US';
        $product->gender = Gender::MALE;
        $product->offerId = 'SKU12345-Blue-79.99';

        $this->assertSame('SHOE123GROUP', $product->itemGroupId);
        $this->assertSame('Men\'s Trail Running Shoes', $product->itemGroupTitle);
        $this->assertSame('Blue', $product->color);
        $this->assertSame('10', $product->size);
        $this->assertSame('US', $product->sizeSystem);
        $this->assertSame(Gender::MALE, $product->gender);
        $this->assertSame('SKU12345-Blue-79.99', $product->offerId);
    }

    public function testSetCustomVariants(): void
    {
        $product = new Product();
        $product->customVariant1Category = 'Size_Type';
        $product->customVariant1Option = 'Petite';
        $product->customVariant2Category = 'Wood_Type';
        $product->customVariant2Option = 'Oak';
        $product->customVariant3Category = 'Cap_Type';
        $product->customVariant3Option = 'Snapback';

        $this->assertSame('Size_Type', $product->customVariant1Category);
        $this->assertSame('Petite', $product->customVariant1Option);
        $this->assertSame('Wood_Type', $product->customVariant2Category);
        $this->assertSame('Oak', $product->customVariant2Option);
        $this->assertSame('Cap_Type', $product->customVariant3Category);
        $this->assertSame('Snapback', $product->customVariant3Option);
    }

    public function testSetFulfillment(): void
    {
        $product = new Product();
        $shipping = new Shipping('US', 'CA', 'Overnight', new Money('1600', new Currency('USD')));
        $product->shipping = [$shipping];
        $product->deliveryEstimate = new DateTime('2025-08-12');

        $this->assertCount(1, $product->shipping);
        $this->assertInstanceOf(Shipping::class, $product->shipping[0]);
        $this->assertInstanceOf(DateTime::class, $product->deliveryEstimate);
    }

    public function testSetMerchantInfo(): void
    {
        $product = new Product();
        $product->sellerName = 'Example Store';
        $product->sellerUrl = 'https://example.com/store';
        $product->sellerPrivacyPolicy = 'https://example.com/privacy';
        $product->sellerTos = 'https://example.com/terms';

        $this->assertSame('Example Store', $product->sellerName);
        $this->assertSame('https://example.com/store', $product->sellerUrl);
        $this->assertSame('https://example.com/privacy', $product->sellerPrivacyPolicy);
        $this->assertSame('https://example.com/terms', $product->sellerTos);
    }

    public function testSetReturns(): void
    {
        $product = new Product();
        $product->returnPolicy = 'https://example.com/returns';
        $product->returnWindow = 30;

        $this->assertSame('https://example.com/returns', $product->returnPolicy);
        $this->assertSame(30, $product->returnWindow);
    }

    public function testSetPerformanceSignals(): void
    {
        $product = new Product();
        $product->popularityScore = 4.7;
        $product->returnRate = 2.0;

        $this->assertSame(4.7, $product->popularityScore);
        $this->assertSame(2.0, $product->returnRate);
    }

    public function testSetCompliance(): void
    {
        $product = new Product();
        $product->warning = 'Contains lithium battery';
        $product->warningUrl = 'https://example.com/warning';
        $product->ageRestriction = 21;

        $this->assertSame('Contains lithium battery', $product->warning);
        $this->assertSame('https://example.com/warning', $product->warningUrl);
        $this->assertSame(21, $product->ageRestriction);
    }

    public function testSetReviewsAndQA(): void
    {
        $product = new Product();
        $product->productReviewCount = 254;
        $product->productReviewRating = 4.6;
        $product->storeReviewCount = 2000;
        $product->storeReviewRating = 4.8;
        $product->qAndA = 'Q: Is this waterproof? A: Yes';
        $product->rawReviewData = '{"reviews": []}';

        $this->assertSame(254, $product->productReviewCount);
        $this->assertSame(4.6, $product->productReviewRating);
        $this->assertSame(2000, $product->storeReviewCount);
        $this->assertSame(4.8, $product->storeReviewRating);
        $this->assertSame('Q: Is this waterproof? A: Yes', $product->qAndA);
        $this->assertSame('{"reviews": []}', $product->rawReviewData);
    }

    public function testSetRelatedProducts(): void
    {
        $product = new Product();
        $product->relatedProductId = ['SKU67890', 'SKU11111'];
        $product->relationshipType = RelationshipType::PART_OF_SET;

        $this->assertCount(2, $product->relatedProductId);
        $this->assertSame(RelationshipType::PART_OF_SET, $product->relationshipType);
    }

    public function testSetGeoTagging(): void
    {
        $product = new Product();
        $geoPrice = new GeoPrice(new Money('7999', new Currency('USD')), 'California');
        $geoAvailability = new GeoAvailability(Availability::IN_STOCK, 'Texas');

        $product->geoPrice = [$geoPrice];
        $product->geoAvailability = [$geoAvailability];

        $this->assertCount(1, $product->geoPrice);
        $this->assertInstanceOf(GeoPrice::class, $product->geoPrice[0]);
        $this->assertCount(1, $product->geoAvailability);
        $this->assertInstanceOf(GeoAvailability::class, $product->geoAvailability[0]);
    }

    public function testNullableFields(): void
    {
        $product = new Product();

        $this->assertNull($product->id);
        $this->assertNull($product->gtin);
        $this->assertNull($product->condition);
        $this->assertNull($product->dimensions);
        $this->assertNull($product->price);
        $this->assertNull($product->availability);
        $this->assertNull($product->gender);
    }
}
