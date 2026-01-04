[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](code-of-conduct.md)

# OpenAI Product Feed

A PHP library for generating product feeds according to the [OpenAI Product Feed Specification](https://platform.openai.com/docs/guides/products).

## Features

- **Modern PHP 8.4** - Uses property hooks for clean getter/setter syntax
- **Type-Safe** - Backed enums and value objects for data integrity
- **Multiple Formats** - Export to JSON Lines (.jsonl.gz) or CSV (.csv.gz)
- **Money Handling** - Proper currency formatting using moneyphp/money
- **Immutable Value Objects** - Thread-safe, predictable data structures
- **Comprehensive** - Supports all ~66 fields from the OpenAI specification

## Requirements

PHP 8.4 or above and Composer is expected to be installed.

### Installing Composer

For instructions on how to install Composer visit [getcomposer.org](https://getcomposer.org/download/).

### Installing

After cloning this repository, change into the newly created directory and run:

```bash
composer install
```

or if you have installed Composer locally in your current directory:

```bash
php composer.phar install
```

This will install all dependencies needed for the project.

Henceforth, the rest of this README will assume `composer` is installed globally (ie. if you are using `composer.phar` you will need to use `composer.phar` instead of `composer` in your terminal / command-line).

## Usage

### Basic Example

```php
<?php

use ElliotJReed\Entity\Product;
use ElliotJReed\Enum\Availability;
use ElliotJReed\Enum\Condition;
use ElliotJReed\Serializer\JsonLinesSerializer;
use ElliotJReed\Serializer\CsvSerializer;
use Money\Currency;
use Money\Money;

// Create a product
$product = new Product();
$product->enableSearch = true;
$product->enableCheckout = true;
$product->id = 'SKU12345';
$product->title = 'Men\'s Trail Running Shoes';
$product->description = 'Waterproof trail shoe with cushioned sole for all-day comfort';
$product->link = 'https://example.com/products/trail-shoes';
$product->condition = Condition::NEW;
$product->brand = 'TrailRunner';
$product->price = new Money('7999', new Currency('USD')); // $79.99
$product->availability = Availability::IN_STOCK;
$product->inventoryQuantity = 150;
$product->imageLink = 'https://example.com/images/trail-shoes-main.jpg';

// Serialize to JSON Lines format
$jsonSerializer = new JsonLinesSerializer();
$json = $jsonSerializer->serialize($product);
echo $json;

// Serialize to CSV format
$csvSerializer = new CsvSerializer();
$csv = $csvSerializer->serialize($product);
echo $csv;
```

### Complete Product Example

```php
<?php

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

$product = new Product();

// OpenAI Flags
$product->enableSearch = true;
$product->enableCheckout = true;

// Basic Product Data
$product->id = 'SKU12345';
$product->gtin = '123456789543';
$product->mpn = 'TRAIL-BLK-10';
$product->title = 'Men\'s Trail Running Shoes - Black';
$product->description = 'Waterproof trail running shoes with cushioned sole, perfect for outdoor adventures';
$product->link = 'https://example.com/products/trail-shoes-black';

// Item Information
$product->condition = Condition::NEW;
$product->productCategory = 'Apparel & Accessories > Shoes > Athletic Shoes';
$product->brand = 'TrailRunner Pro';
$product->material = 'Synthetic mesh and rubber';
$product->dimensions = new Dimensions(12.0, 5.0, 6.0, 'in');
$product->weight = 1.5;
$product->weightUnit = 'lb';
$product->ageGroup = AgeGroup::ADULT;

// Media
$product->imageLink = 'https://example.com/images/trail-shoes-main.jpg';
$product->additionalImageLink = [
    'https://example.com/images/trail-shoes-side.jpg',
    'https://example.com/images/trail-shoes-sole.jpg',
];
$product->videoLink = 'https://youtube.com/watch?v=example';

// Price & Promotions
$product->price = new Money('12999', new Currency('USD')); // $129.99
$product->salePrice = new Money('9999', new Currency('USD')); // $99.99
$product->salePriceEffectiveDate = new DateRange(
    new DateTime('2026-01-01'),
    new DateTime('2026-01-31')
);
$product->unitPricing = new UnitPricing(1.0, 'pair', 1.0, 'pair');
$product->pricingTrend = 'Lowest price in 6 months';

// Availability & Inventory
$product->availability = Availability::IN_STOCK;
$product->inventoryQuantity = 150;
$product->pickupMethod = PickupMethod::IN_STORE;
$product->pickupSla = '2 hours';

// Variants
$product->itemGroupId = 'TRAIL-SHOES-GROUP';
$product->itemGroupTitle = 'Trail Running Shoes';
$product->color = 'Black';
$product->size = '10';
$product->sizeSystem = 'US';
$product->gender = Gender::MALE;
$product->offerId = 'SKU12345-BLACK-10';

// Fulfillment
$product->shipping = [
    new Shipping('US', 'CA', 'Standard', new Money('595', new Currency('USD'))),
    new Shipping('US', 'NY', 'Express', new Money('1295', new Currency('USD'))),
];
$product->deliveryEstimate = new DateTime('2026-01-15');

// Merchant Info
$product->sellerName = 'Athletic Footwear Store';
$product->sellerUrl = 'https://example.com/store';
$product->sellerPrivacyPolicy = 'https://example.com/privacy';
$product->sellerTos = 'https://example.com/terms';

// Returns
$product->returnPolicy = 'https://example.com/returns';
$product->returnWindow = 30;

// Performance Signals
$product->popularityScore = 4.7;
$product->returnRate = 2.5;

// Compliance
$product->warning = 'May contain materials that require special care';

// Reviews & Q&A
$product->productReviewCount = 254;
$product->productReviewRating = 4.6;
$product->storeReviewCount = 2000;
$product->storeReviewRating = 4.8;
$product->qAndA = 'Q: Are these waterproof? A: Yes, fully waterproof with sealed seams.';

// Related Products
$product->relatedProductId = ['SKU67890', 'SKU11111'];
$product->relationshipType = RelationshipType::OFTEN_BOUGHT_WITH;

// Geo Tagging
$product->geoPrice = [
    new GeoPrice(new Money('12999', new Currency('USD')), 'California'),
    new GeoPrice(new Money('11999', new Currency('USD')), 'Texas'),
];
$product->geoAvailability = [
    new GeoAvailability(Availability::IN_STOCK, 'California'),
    new GeoAvailability(Availability::OUT_OF_STOCK, 'Alaska'),
];
```

### Serializing Multiple Products

```php
<?php

use ElliotJReed\Serializer\JsonLinesSerializer;
use ElliotJReed\Serializer\CsvSerializer;

// Array of products
$products = [$product1, $product2, $product3];

// Serialize to JSON Lines (one product per line)
$jsonSerializer = new JsonLinesSerializer();
$jsonLines = $jsonSerializer->serializeMany($products);

// Serialize to CSV (with header row)
$csvSerializer = new CsvSerializer();
$csv = $csvSerializer->serializeMany($products);
```

### Writing to Compressed Files

The library supports automatic gzip compression for both JSON Lines and CSV formats:

```php
<?php

use ElliotJReed\Serializer\JsonLinesSerializer;
use ElliotJReed\Serializer\CsvSerializer;

$products = [...]; // Your products array

// Write to compressed JSON Lines file (.jsonl.gz)
$jsonSerializer = new JsonLinesSerializer();
$jsonSerializer->serializeToFile($products, '/path/to/products.jsonl.gz', compress: true);

// Write to compressed CSV file (.csv.gz)
$csvSerializer = new CsvSerializer();
$csvSerializer->serializeToFile($products, '/path/to/products.csv.gz', compress: true);

// Write uncompressed files
$jsonSerializer->serializeToFile($products, '/path/to/products.jsonl', compress: false);
$csvSerializer->serializeToFile($products, '/path/to/products.csv', compress: false);
```

### Using Value Objects

The library provides immutable value objects for complex field types:

```php
<?php

use DateTime;
use ElliotJReed\ValueObject\Dimensions;
use ElliotJReed\ValueObject\DateRange;
use ElliotJReed\ValueObject\UnitPricing;
use ElliotJReed\ValueObject\Shipping;
use ElliotJReed\ValueObject\GeoPrice;
use ElliotJReed\ValueObject\GeoAvailability;
use ElliotJReed\Enum\Availability;
use Money\Currency;
use Money\Money;

// Dimensions (Length x Width x Height)
$dimensions = new Dimensions(12.0, 8.0, 5.0, 'in');
echo $dimensions->toString(); // "12x8x5 in"

// Date Range (ISO 8601)
$dateRange = new DateRange(
    new DateTime('2026-01-01'),
    new DateTime('2026-01-31')
);
echo $dateRange->toString(); // "2026-01-01 / 2026-01-31"

// Unit Pricing
$unitPricing = new UnitPricing(16.0, 'oz', 1.0, 'oz');
echo $unitPricing->toString(); // "16 oz / 1 oz"

// Shipping
$shipping = new Shipping('US', 'CA', 'Overnight', new Money('1600', new Currency('USD')));
echo $shipping->toString(); // "US:CA:Overnight:16.00 USD"

// Geo-specific Price
$geoPrice = new GeoPrice(new Money('7999', new Currency('USD')), 'California');
echo $geoPrice->toString(); // "79.99 USD (California)"

// Geo-specific Availability
$geoAvailability = new GeoAvailability(Availability::IN_STOCK, 'Texas');
echo $geoAvailability->toString(); // "in_stock (Texas)"
```

### Using Enums

Type-safe enums for categorical values:

```php
<?php

use ElliotJReed\Enum\Condition;
use ElliotJReed\Enum\Availability;
use ElliotJReed\Enum\AgeGroup;
use ElliotJReed\Enum\Gender;
use ElliotJReed\Enum\PickupMethod;
use ElliotJReed\Enum\RelationshipType;

// Product Condition
$condition = Condition::NEW;          // 'new'
$condition = Condition::REFURBISHED;  // 'refurbished'
$condition = Condition::USED;         // 'used'

// Availability
$availability = Availability::IN_STOCK;      // 'in_stock'
$availability = Availability::OUT_OF_STOCK;  // 'out_of_stock'
$availability = Availability::PREORDER;      // 'preorder'

// Age Group
$ageGroup = AgeGroup::ADULT;    // 'adult'
$ageGroup = AgeGroup::KIDS;     // 'kids'
$ageGroup = AgeGroup::INFANT;   // 'infant'

// Gender
$gender = Gender::MALE;    // 'male'
$gender = Gender::FEMALE;  // 'female'
$gender = Gender::UNISEX;  // 'unisex'

// Pickup Method
$pickup = PickupMethod::IN_STORE;       // 'in_store'
$pickup = PickupMethod::RESERVE;        // 'reserve'
$pickup = PickupMethod::NOT_SUPPORTED;  // 'not_supported'

// Relationship Type
$relationship = RelationshipType::PART_OF_SET;        // 'part_of_set'
$relationship = RelationshipType::OFTEN_BOUGHT_WITH;  // 'often_bought_with'
$relationship = RelationshipType::SUBSTITUTE;         // 'substitute'
```

### Property Hooks (PHP 8.4)

The Product entity uses modern PHP 8.4 property hooks for automatic string trimming:

```php
<?php

use ElliotJReed\Entity\Product;

$product = new Product();

// Automatically trims whitespace
$product->title = '  Product Title  ';
echo $product->title; // "Product Title" (trimmed)

$product->description = '  Description with spaces  ';
echo $product->description; // "Description with spaces" (trimmed)
```

### Money Formatting

The library uses [moneyphp/money](https://github.com/moneyphp/money) for proper currency handling:

```php
<?php

use Money\Currency;
use Money\Money;

// Money amounts are stored in cents/smallest unit
$price = new Money('7999', new Currency('USD')); // $79.99
$salePrice = new Money('5999', new Currency('USD')); // $59.99

// Serializers automatically format as "79.99 USD"
$product->price = $price;
$product->salePrice = $salePrice;

// When serialized, outputs: "79.99 USD" and "59.99 USD"
```

## API Reference

### Product Entity

The `Product` class contains all ~66 fields from the OpenAI specification:

- **OpenAI Flags**: `enableSearch`, `enableCheckout`
- **Basic Data**: `id`, `gtin`, `mpn`, `title`, `description`, `link`
- **Item Info**: `condition`, `productCategory`, `brand`, `material`, `dimensions`, `weight`, etc.
- **Media**: `imageLink`, `additionalImageLink`, `videoLink`, `model3dLink`
- **Pricing**: `price`, `salePrice`, `salePriceEffectiveDate`, `unitPricing`, `pricingTrend`
- **Availability**: `availability`, `inventoryQuantity`, `expirationDate`, `pickupMethod`, etc.
- **Variants**: `itemGroupId`, `color`, `size`, `gender`, custom variants
- **Fulfillment**: `shipping`, `deliveryEstimate`
- **Merchant**: `sellerName`, `sellerUrl`, `sellerPrivacyPolicy`, `sellerTos`
- **Returns**: `returnPolicy`, `returnWindow`
- **Performance**: `popularityScore`, `returnRate`
- **Compliance**: `warning`, `warningUrl`, `ageRestriction`
- **Reviews**: `productReviewCount`, `productReviewRating`, `qAndA`, `rawReviewData`
- **Related**: `relatedProductId`, `relationshipType`
- **Geo**: `geoPrice`, `geoAvailability`

### Serializers

Both serializers implement `SerializerInterface`:

```php
interface SerializerInterface
{
    public function serialize(Product $product): string;
    public function serializeMany(array $products): string;
    public function serializeToFile(array $products, string $filePath, bool $compress = true): void;
}
```

**JsonLinesSerializer** - Outputs newline-delimited JSON (JSONL format)
**CsvSerializer** - Outputs CSV with header row

## Running the Tests

### Unit tests

Unit testing in this project is via [PHPUnit](https://phpunit.de/).

All unit tests can be run by executing:

```bash
composer phpunit
```

#### Debugging

To have PHPUnit stop and report on the first failing test encountered, run:

```bash
composer phpunit:debug
```

## Code formatting

A standard for code style can be important when working in teams, as it means that less time is spent by developers processing what they are reading (as everything will be consistent).

Code formatting is automated via [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).
PHP-CS-Fixer will not format line lengths which do form part of the PSR-2 coding standards so these will product warnings when checked by [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).

These can be run by executing:

```bash
composer phpcs
```

### Running everything

All of the tests can be run by executing:

```bash
composer test
```

### Outdated dependencies

Checking for outdated Composer dependencies can be performed by executing:

```bash
composer outdated
```

### Validating Composer configuration

Checking that the [composer.json](composer.json) is valid can be performed by executing:

```bash
composer validate --no-check-publish
```

### Running via GNU Make

If GNU [Make](https://www.gnu.org/software/make/) is installed, you can replace the above `composer` command prefixes with `make`.

All of the tests can be run by executing:

```bash
make test
```

### Running the tests on a Continuous Integration platform (eg. Github Actions)

Specific output formats better suited to CI platforms are included as Composer scripts.

To output unit test coverage in text and Clover XML format (which can be used for services such as [Coveralls](https://coveralls.io/)):

```
composer phpunit:ci
```

To output PHP-CS-Fixer (dry run) and PHPCS results in checkstyle format (which GitHub Actions will use to output a readable format):

```
composer phpcs:ci
```

#### Github Actions

Look at the example in [.github/workflows/main.yml](.github/workflows/main.yml).

## Built With

  - [PHP](https://secure.php.net/)
  - [Composer](https://getcomposer.org/)
  - [PHPUnit](https://phpunit.de/)
  - [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer)
  - [GNU Make](https://www.gnu.org/software/make/)

## License

This project is licensed under the MIT License - see the [LICENCE.md](LICENCE.md) file for details.
