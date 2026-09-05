[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](code-of-conduct.md)

# openai-product-feed

A PHP library for building [OpenAI product feeds](https://developers.openai.com/commerce/specs/file-upload/products)
and rendering them as JSON Lines or CSV, so your products can be surfaced, and bought, inside ChatGPT.

It covers the full product feed specification, including the
[Ads attributes](https://developers.openai.com/ads/product-feeds) which decide whether an item is processed for a
campaign.

- **Fluent setters** for every attribute, with the attribute's own description on hover in your IDE.
- **Validation as you set** - a value OpenAI would reject throws immediately, rather than failing when your feed is
  ingested hours later. The rules which span several attributes, such as a sale price having to undercut the price it
  discounts, are checked as each product is added.
- **Enums** for every value set the specification restricts, so an invalid value cannot be spelled.
- **Both accepted formats**, JSON Lines and CSV/TSV, from the same `Product` objects.
- **Constant memory streaming** for large catalogues. 200,000 products producing a 67.5MB feed peaks at 4MB of memory.

## Contents

- [Installation](#installation)
- [Quick start](#quick-start)
  - [On required attributes](#on-required-attributes)
  - [On field names and British English](#on-field-names-and-british-english)
- [Rendering a feed](#rendering-a-feed)
  - [As JSON Lines](#as-json-lines)
  - [As CSV or TSV](#as-csv-or-tsv)
  - [Streaming large catalogues](#streaming-large-catalogues)
  - [Compressed feeds](#compressed-feeds)
- [Variants](#variants)
- [Structured attributes](#structured-attributes)
- [Search, checkout and ads](#search-checkout-and-ads)
- [Delta feeds](#delta-feeds)
- [Validation and exceptions](#validation-and-exceptions)
- [Enumerations](#enumerations)
- [Attribute reference](#attribute-reference)
- [A complete example](#a-complete-example)
- [Development](#development)

## Installation

```bash
composer require elliotjreed/openai-product-feed
```

PHP 8.4 or above is required.

## Quick start

```php
<?php

use ElliotJReed\OpenAiProductFeed\Enum\Availability;
use ElliotJReed\OpenAiProductFeed\Enum\Condition;
use ElliotJReed\OpenAiProductFeed\Feed;
use ElliotJReed\OpenAiProductFeed\Product;

$feed = new Feed();

$feed->addProduct(
    (new Product())
        ->setItemId('TRAIL-BLK-10')
        ->setTitle('Trail running shoes - black, size 10')
        ->setDescription('Waterproof trail shoes with a rubber outsole and mesh lining.')
        ->setUrl('https://example.com/products/trail?color=black&size=10')
        ->setBrand('Northline')
        ->setSellerName('Northline Outdoor')
        ->setImageUrl('https://example.com/images/trail-black.jpg')
        ->setPrice('79.99', 'USD')
        ->setAvailability(Availability::InStock)
        ->setCondition(Condition::New)
        ->setGtin('09506000134352')
);

echo $feed->toJsonLines();
```

```jsonl
{"item_id":"TRAIL-BLK-10","title":"Trail running shoes - black, size 10","description":"Waterproof trail shoes with a rubber outsole and mesh lining.","url":"https://example.com/products/trail?color=black&size=10","brand":"Northline","image_url":"https://example.com/images/trail-black.jpg","price":"79.99 USD","availability":"in_stock","gtin":"09506000134352","condition":"new","seller_name":"Northline Outdoor"}
```

### On required attributes

Nine attributes are required for every item: `item_id`, `title`, `description`, `url`, `brand`, `seller_name`,
`image_url`, `availability` and `price`. Unlike most feed specifications, these are required unconditionally rather
than depending on the category or country, so this library enforces all nine as each product is added to a feed.

A handful of further attributes are conditionally required, and those conditions are enforced too - a variant needs
its group, checkout needs the seller's policies, and so on. See
[Validation and exceptions](#validation-and-exceptions) for the complete list.

### On field names and British English

Method names use British English, as does all documentation. Feed field names are OpenAI's and are left exactly as the
specification defines them, so two setters read a little differently from the field they write:

```php
$product->setColour('Navy');                                    // writes the "color" field
$product->setSellerTermsOfService('https://example.com/terms'); // writes the "seller_tos" field
```

The specification also still accepts a number of superseded names, such as `id` for `item_id` and `enable_search` for
`is_eligible_search`. This library always writes the current name. Asking for a superseded one by mistake tells you
which name replaced it:

```php
(new CsvFeed())->withColumns('item_id', 'enable_search');
// InvalidAttribute: "enable_search" is not a field in the OpenAI product feed specification.
//                   Did you mean "is_eligible_search"?
```

## Rendering a feed

### As JSON Lines

JSON Lines is the format OpenAI prefers, and the one to reach for unless you have a reason not to. Nested attributes
such as `variant_dict` and `dimensions` are written as real JSON objects, lists as real JSON arrays, and booleans and
numbers keep their JSON types.

`toJsonLines()` builds the whole feed in memory and returns it. It closes the feed, so no further products can be
added afterwards, but it may be called repeatedly and returns the same document each time.

```php
file_put_contents('feed.jsonl', (new Feed())->addProduct($product)->toJsonLines());
```

### As CSV or TSV

A delimited feed needs its columns named in a header row before any product is written, so `CsvFeed` writes every
column the specification documents by default. An empty cell means the attribute has not been set, which is exactly
what the specification says it means.

```php
use ElliotJReed\OpenAiProductFeed\CsvFeed;

echo (new CsvFeed())->addProduct($product)->toCsv();
```

Where you only populate a handful of attributes, `withColumns()` narrows the file to those columns, in the order you
give them:

```php
echo (new CsvFeed())
    ->withColumns('item_id', 'title', 'price', 'availability')
    ->addProduct($product)
    ->toCsv();
```

```csv
item_id,title,price,availability
TRAIL-BLK-10,"Trail running shoes - black, size 10","79.99 USD",in_stock
```

`withTabSeparated()` switches the delimiter for a `.tsv` or `.txt` feed. Both configure the feed, so call them before
adding the first product.

```php
(new CsvFeed())->withTabSeparated()->addProduct($product)->toCsv();
```

Because a delimited file is flat, attributes which are objects in JSON Lines are written as embedded JSON, lists are
written as comma-separated values with any comma inside a value percent-encoded as `%2C`, and booleans become the
lowercase words `true` and `false`. Cells containing a delimiter, a quote or a newline are quoted, and read back
unchanged by any conforming parser.

### Streaming large catalogues

Feeds routinely contain hundreds of thousands of products. Pass a writable stream resource to `stream()`, add each
product as you read it from your database, then call `end()`. Every record is written to the stream as it is added, so
memory use stays constant no matter how large the catalogue is.

```php
$stream = fopen('feed.jsonl', 'wb');

$feed = (new Feed())->stream($stream);

foreach ($repository->products() as $row) {
    $feed->addProduct(
        (new Product())
            ->setItemId($row['sku'])
            ->setTitle($row['name'])
            // ...
    );
}

$feed->end();
fclose($stream);
```

The stream is left open for you to close. `stream()` configures the feed, so call it before the first product, and
`toJsonLines()` is unavailable once a feed is being streamed.

A `CsvFeed` streams the same way, writing its header row the moment it is given the stream:

```php
$stream = fopen('feed.csv', 'wb');

$feed = (new CsvFeed())->withColumns('item_id', 'price', 'availability')->stream($stream);

foreach ($repository->products() as $row) {
    $feed->addProduct($product);
}

$feed->end();
fclose($stream);
```

To write a feed straight to the browser or to standard output, stream to `php://output`.

### Compressed feeds

OpenAI accepts gzip-compressed feeds, and for a catalogue of any size they are what you want to be uploading.
`gzopen()` returns an ordinary stream resource, so a compressed feed needs nothing special:

```php
$stream = gzopen('feed.jsonl.gz', 'wb9');

$feed = (new Feed())->stream($stream);
$feed->addProduct($product);
$feed->end();

gzclose($stream);
```

The same applies to `feed.csv.gz` with a `CsvFeed`.

## Variants

A variant row needs three attributes together: the group every variant of the product shares, the flag marking the
listing as having variations, and the options which distinguish this particular variant. Setting one without the
others is rejected, as is a group identifier which merely repeats the item's own.

```php
use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;

$product
    ->setItemId('TRAIL-BLK-10')
    ->setGroupId('TRAIL')
    ->setListingHasVariations(true)
    ->setVariantOptions(new VariantOptions(['color' => 'Black', 'size' => '10']));
```

Options can also be added one at a time, which keeps them in the order you add them:

```php
$options = (new VariantOptions())
    ->add('color', 'Black')
    ->add('size', '10');
```

Each variant is its own product with its own price, availability, URL and images. Keep the options consistent with the
equivalent top level attributes - `setColour('Black')` alongside a `color` option of `Black` - because OpenAI does not
reconcile conflicts between them.

## Structured attributes

Four attributes are richer than a single value, and each has a small class of its own.

`Dimensions` is the object form of an item's measurements, which the specification prefers over the separate `length`,
`width`, `height` and `dimensions_unit` fields. At least two of the three measurements are needed, all sharing one
unit:

```php
use ElliotJReed\OpenAiProductFeed\Attribute\Dimensions;
use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;

$product->setDimensions(
    (new Dimensions(DimensionsUnit::Centimetres))
        ->setLength('30')
        ->setWidth('20')
        ->setHeight('12')
);
```

`Shipping` renders a delivery charge as the four part `country:region:service_class:price` tuple, which only
integrations configured for it accept. The region is optional:

```php
use ElliotJReed\OpenAiProductFeed\Attribute\Shipping;

$product->setShipping((new Shipping('US', 'Overnight', '16.00', 'USD'))->setRegion('CA'));
// US:CA:Overnight:16.00 USD
```

Every other integration uses `setShippingPrice()` instead. They are alternatives, and setting both is rejected.

`AdsMetadata` carries the custom filter metadata an Ads campaign can target. Use only the keys configured for your
integration rather than inventing your own:

```php
use ElliotJReed\OpenAiProductFeed\Attribute\AdsMetadata;

$product->setAdsMetadata(new AdsMetadata(['custom_label_0' => 'summer']));
```

Weight is set alongside its unit, because the specification requires the unit whenever a weight is given:

```php
use ElliotJReed\OpenAiProductFeed\Enum\WeightUnit;

$product->setWeight('0.75', WeightUnit::Kilograms);
```

## Search, checkout and ads

Three flags decide where an item appears. They change nothing about how it appears on your own site.

```php
$product
    ->setEligibleForSearch(true)     // is_eligible_search - defaults to true when left unset
    ->setEligibleForCheckout(true)   // is_eligible_checkout
    ->setEligibleForAds(true)        // is_ads_eligible
    ->setSellerPrivacyPolicy('https://example.com/privacy')
    ->setSellerTermsOfService('https://example.com/terms');
```

Checkout only opts an item in where it is also eligible for search, and needs both the seller's privacy policy and its
terms of service, all of which is enforced. Checkout and Ads each need an integration OpenAI has enabled separately:
setting the flag does not complete that onboarding on its own.

## Delta feeds

Where you are sending only the attributes which have changed, rather than a full catalogue, a record deliberately will
not carry all nine required attributes. `withoutValidation()` writes each product as given, leaving the cross-attribute
checks aside. Individual values are still validated as they are set.

```php
echo (new Feed())
    ->withoutValidation()
    ->addProduct((new Product())->setItemId('TRAIL-BLK-10')->setAvailability(Availability::OutOfStock))
    ->toJsonLines();
```

```jsonl
{"item_id":"TRAIL-BLK-10","availability":"out_of_stock"}
```

## Validation and exceptions

Every exception this library throws implements `OpenAiProductFeedException`, so all of them can be caught together.

`InvalidAttribute` extends `InvalidArgumentException` and is thrown when a value would be rejected. Most are thrown by
the setter itself, at the point the mistake is made:

```php
$product->setTitle(str_repeat('a', 151));
// InvalidAttribute: The title attribute must not exceed 150 characters, 151 characters given.

$product->setUrl('/products/trail');
// InvalidAttribute: The url attribute must be a valid HTTP or HTTPS URL, "/products/trail" given.

$product->setGtin('3234567890127');
// InvalidAttribute: The gtin attribute "3234567890127" has an invalid check digit.

$product->setAvailability('sold_out');
// InvalidAttribute: "sold_out" is not a valid value for the availability attribute.
//                   Allowed values: in_stock, out_of_stock, pre_order, backorder, unknown.
```

The rest span more than one attribute, and cannot be judged until every value is known, so they are checked as the
product is added to a feed:

| Rule | Raised when |
| :--- | :--- |
| Required attributes | Any of the nine required attributes has not been set |
| Sale price | `sale_price` is not strictly lower than `price` |
| Currency | `sale_price` or `shipping_price` uses a different currency from `price` |
| Delivery charge | Both `shipping_price` and `shipping` are set, which are alternatives |
| Variant group | `group_id` repeats `item_id` |
| Variant completeness | Any of `group_id`, `listing_has_variations` or `variant_dict` is set without the others |
| Measurement units | `length`, `width` or `height` is set without `dimensions_unit` |
| Checkout eligibility | `is_eligible_checkout` is true while `is_eligible_search` is false |
| Checkout policies | `is_eligible_checkout` is true without `seller_privacy_policy` and `seller_tos` |
| Returns | `return_deadline_in_days` is set while `accepts_returns` is false |

```php
try {
    $feed->addProduct($product);
} catch (InvalidAttribute $exception) {
    $this->logger->warning('Skipping product', ['error' => $exception->getMessage()]);
}
```

`InvalidFeedState` extends `LogicException` and is thrown when a feed is used in a way its state does not allow -
adding a product after `end()`, asking a streamed feed for its contents as a string, or configuring a feed after the
first product has been added.

One piece of the specification's guidance is documented rather than enforced: a `star_rating` should be paired with a
`review_count` greater than zero, and a `store_star_rating` with a positive `store_review_count`. Both are phrased as
advice rather than a requirement, so the library leaves the judgement to you.

## Enumerations

Every restricted value set has an enum. Setters accept either the enum case or its string value, so an existing string
column needs no translation.

| Enum | Values |
| :--- | :--- |
| `Availability` | `in_stock`, `out_of_stock`, `pre_order`, `backorder`, `unknown` |
| `Condition` | `new`, `refurbished`, `used` |
| `Gender` | `male`, `female`, `unisex` |
| `AgeGroup` | `newborn`, `infant`, `toddler`, `kids`, `adult` |
| `DimensionsUnit` | `in`, `cm`, `ft`, `m`, `mm` |
| `WeightUnit` | `g`, `kg`, `oz`, `lb` |
| `SizeSystem` | `US`, `UK`, `EU`, `DE`, `FR`, `JP`, `CN`, `IT`, `BR`, `MEX`, `AU` |
| `Delimiter` | `,` and a tab, for a `CsvFeed`'s output |

```php
$product->setAvailability(Availability::InStock);
$product->setAvailability('in_stock');           // equivalent
```

Note that `SizeSystem` is not a set of country codes: Mexico is `MEX` rather than `MX`, as the specification spells it.

## Attribute reference

The 53 attributes the specification documents, in the order this library writes them.

### Basic product data

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setItemId()` | `item_id` | Required. Stable and unique per item or variant |
| `setTitle()` | `title` | Required. Maximum 150 characters |
| `setDescription()` | `description` | Required. Maximum 5,000 characters |
| `setUrl()` | `url` | Required. Absolute HTTP or HTTPS |
| `setBrand()` | `brand` | Required |
| `setImageUrl()` | `image_url` | Required. A direct image URL |

### Price and availability

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setPrice()` | `price` | Required. Amount and ISO 4217 currency |
| `setSalePrice()` | `sale_price` | Must be lower than the price, in the same currency |
| `setAvailability()` | `availability` | Required. `Availability` enum |

### Variants

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setGroupId()` | `group_id` | Required for variants. Must differ from the item id |
| `setListingHasVariations()` | `listing_has_variations` | Required for variants. Set on every variant row |
| `setVariantOptions()` | `variant_dict` | Required for variants. A `VariantOptions` object |
| `setOfferId()` | `offer_id` | Unique within the feed |
| `setGtin()` | `gtin` | 8, 12, 13 or 14 digits with a valid check digit |
| `setMpn()` | `mpn` | Manufacturer's part number |

### Item information

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setCondition()` | `condition` | `Condition` enum |
| `setProductCategory()` | `product_category` | Category path separated by `>` |
| `setMaterial()` | `material` | |
| `setColour()` | `color` | |
| `setSize()` | `size` | |
| `setSizeSystem()` | `size_system` | `SizeSystem` enum |
| `setGender()` | `gender` | `Gender` enum |
| `setAgeGroup()` | `age_group` | `AgeGroup` enum |
| `setDimensions()` | `dimensions` | A `Dimensions` object, which takes precedence over the separate fields |
| `setLength()` | `length` | Requires the dimensions unit |
| `setWidth()` | `width` | Requires the dimensions unit |
| `setHeight()` | `height` | Requires the dimensions unit |
| `setDimensionsUnit()` | `dimensions_unit` | `DimensionsUnit` enum |
| `setWeight()` | `weight`, `item_weight_unit` | Net weight and its `WeightUnit`, set together |
| `setDigital()` | `is_digital` | Requires a configured integration |

### Media

| Method | Field | Notes |
| :--- | :--- | :--- |
| `addAdditionalImageUrl()` | `additional_image_urls` | Adds one further image |
| `setAdditionalImageUrls()` | `additional_image_urls` | Replaces every additional image |

### Merchant information

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setSellerName()` | `seller_name` | Required |
| `setSellerUrl()` | `seller_url` | The seller's storefront or profile |
| `setMarketplaceSeller()` | `marketplace_seller` | Required for third-party marketplace offers |
| `setStoreCountry()` | `store_country` | ISO 3166-1 alpha-2 |

### Fulfilment

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setShippingPrice()` | `shipping_price` | In the same currency as the price |
| `setShipping()` | `shipping` | A `Shipping` tuple, as an alternative to the shipping price |

### Returns

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setAcceptsReturns()` | `accepts_returns` | |
| `setReturnDeadlineInDays()` | `return_deadline_in_days` | Only where returns are accepted |
| `setReturnPolicy()` | `return_policy` | |
| `setAcceptsExchanges()` | `accepts_exchanges` | Requires a configured integration |

### Reviews

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setReviewCount()` | `review_count` | |
| `setStarRating()` | `star_rating` | Nought to five, to two decimal places |
| `setStoreReviewCount()` | `store_review_count` | Requires a configured integration |
| `setStoreStarRating()` | `store_star_rating` | Requires a configured integration |

### Geo targeting

| Method | Field | Notes |
| :--- | :--- | :--- |
| `addTargetCountry()` | `target_countries` | Adds one destination country |
| `setTargetCountries()` | `target_countries` | Replaces every destination country |

The standard OpenAI format targets the United States regardless of these, so confirm that multi-country support has
been enabled for your integration before relying on them.

### Search, checkout and ads

| Method | Field | Notes |
| :--- | :--- | :--- |
| `setEligibleForSearch()` | `is_eligible_search` | Defaults to true when left unset |
| `setEligibleForCheckout()` | `is_eligible_checkout` | Needs search eligibility and both seller policies |
| `setSellerPrivacyPolicy()` | `seller_privacy_policy` | Required for checkout |
| `setSellerTermsOfService()` | `seller_tos` | Required for checkout |
| `setEligibleForAds()` | `is_ads_eligible` | Must be true for an item to appear in an Ads campaign |
| `setAdsMetadata()` | `ads_metadata` | An `AdsMetadata` object |

## A complete example

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use ElliotJReed\OpenAiProductFeed\Attribute\AdsMetadata;
use ElliotJReed\OpenAiProductFeed\Attribute\Dimensions;
use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;
use ElliotJReed\OpenAiProductFeed\Enum\AgeGroup;
use ElliotJReed\OpenAiProductFeed\Enum\Availability;
use ElliotJReed\OpenAiProductFeed\Enum\Condition;
use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;
use ElliotJReed\OpenAiProductFeed\Enum\Gender;
use ElliotJReed\OpenAiProductFeed\Enum\SizeSystem;
use ElliotJReed\OpenAiProductFeed\Enum\WeightUnit;
use ElliotJReed\OpenAiProductFeed\Exception\OpenAiProductFeedException;
use ElliotJReed\OpenAiProductFeed\Feed;
use ElliotJReed\OpenAiProductFeed\Product;

$stream = gzopen('feed.jsonl.gz', 'wb9');
$feed = (new Feed())->stream($stream);

try {
    $feed->addProduct(
        (new Product())
            ->setItemId('TRAIL-BLK-10')
            ->setTitle('Trail running shoes - black, size 10')
            ->setDescription('Waterproof trail shoes with a rubber outsole and a breathable mesh lining.')
            ->setUrl('https://example.com/products/trail?color=black&size=10')
            ->setBrand('Northline')
            ->setImageUrl('https://example.com/images/trail-black.jpg')
            ->setAdditionalImageUrls([
                'https://example.com/images/trail-side.jpg',
                'https://example.com/images/trail-sole.jpg',
            ])
            ->setPrice('79.99', 'USD')
            ->setSalePrice('59.99', 'USD')
            ->setAvailability(Availability::InStock)
            ->setGroupId('TRAIL')
            ->setListingHasVariations(true)
            ->setVariantOptions(new VariantOptions(['color' => 'Black', 'size' => '10']))
            ->setOfferId('northline-TRAIL-BLK-10')
            ->setGtin('09506000134352')
            ->setMpn('NL-TRAIL-10-BLK')
            ->setCondition(Condition::New)
            ->setProductCategory('Apparel & Accessories > Shoes')
            ->setMaterial('Leather and rubber')
            ->setColour('Black')
            ->setSize('10')
            ->setSizeSystem(SizeSystem::UnitedStates)
            ->setGender(Gender::Unisex)
            ->setAgeGroup(AgeGroup::Adult)
            ->setDimensions(
                (new Dimensions(DimensionsUnit::Centimetres))
                    ->setLength('30')
                    ->setWidth('20')
                    ->setHeight('12')
            )
            ->setWeight('0.75', WeightUnit::Kilograms)
            ->setSellerName('Northline Outdoor')
            ->setSellerUrl('https://example.com/stores/northline')
            ->setStoreCountry('US')
            ->setShippingPrice('5.00', 'USD')
            ->setAcceptsReturns(true)
            ->setReturnDeadlineInDays(30)
            ->setReturnPolicy('https://example.com/returns')
            ->setReviewCount(254)
            ->setStarRating('4.50')
            ->setTargetCountries(['US'])
            ->setEligibleForSearch(true)
            ->setEligibleForCheckout(true)
            ->setSellerPrivacyPolicy('https://example.com/privacy')
            ->setSellerTermsOfService('https://example.com/terms')
            ->setEligibleForAds(true)
            ->setAdsMetadata(new AdsMetadata(['custom_label_0' => 'summer']))
    );
} catch (OpenAiProductFeedException $exception) {
    echo 'Skipped a product: ' . $exception->getMessage() . PHP_EOL;
}

$feed->end();
gzclose($stream);
```

## Development

### Installing Composer

For instructions on how to install Composer visit [getcomposer.org](https://getcomposer.org/download/).

### Installing

After cloning this repository, change into the newly created directory and run:

```bash
composer install
```

or if you have Docker installed:

```bash
docker run --rm --interactive --tty --volume $PWD:/app composer install
```

This will install all the dependencies needed to run the test suite.

## Running the Tests

### Unit tests

```bash
composer phpunit
```

The two tests which stream a 200,000 product catalogue are in the `slow` group, and can be left out while iterating:

```bash
vendor/bin/phpunit --exclude-group slow
```

#### Debugging

To stop on the first failing test:

```bash
composer phpunit:debug
```

## Code formatting

```bash
composer phpcs
```

This runs PHP-CS-Fixer followed by PHP_CodeSniffer against `src` and `tests`.

### Running everything

```bash
composer test
```

This runs the unit tests with a coverage report, then the code style checks.

### Outdated dependencies

```bash
composer outdated
```

### Validating Composer configuration

```bash
composer validate --no-check-publish
```

### Running via GNU Make

```bash
make test
```

This runs the unit tests with coverage, the code style checks, `composer validate` and `composer outdated`.

### Running the tests on a Continuous Integration platform (eg. Github Actions)

```bash
composer phpunit:ci
composer phpcs:ci
```

#### Github Actions

The workflow in `.github/workflows/php.yml` runs the test suite against PHP 8.4 and 8.5 on every push.

## Built With

- [PHP](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [PHPUnit](https://phpunit.de/)
- [PHP-CS-Fixer](https://cs.symfony.com/)
- [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)

## License

This project is licensed under the MIT Licence - see the [LICENCE.md](LICENCE.md) file for details.
