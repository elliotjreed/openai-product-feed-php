<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Serializer;

use DateTime;
use ElliotJReed\Entity\Product;
use ElliotJReed\Enum\Availability;
use ElliotJReed\Enum\Condition;
use ElliotJReed\Serializer\JsonLinesSerializer;
use ElliotJReed\ValueObject\DateRange;
use ElliotJReed\ValueObject\Dimensions;
use ElliotJReed\ValueObject\Shipping;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonLinesSerializer::class)]
final class JsonLinesSerializerTest extends TestCase
{
    private JsonLinesSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new JsonLinesSerializer();
    }

    public function testSerializeSingleProduct(): void
    {
        $product = $this->createBasicProduct();

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertSame('SKU12345', $decoded['id']);
        $this->assertSame('Men\'s Trail Running Shoes', $decoded['title']);
        $this->assertTrue($decoded['enable_search']);
        $this->assertSame('79.99 USD', $decoded['price']);
    }

    public function testSerializeProductWithEnums(): void
    {
        $product = new Product();
        $product->condition = Condition::NEW;
        $product->availability = Availability::IN_STOCK;

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertSame('new', $decoded['condition']);
        $this->assertSame('in_stock', $decoded['availability']);
    }

    public function testSerializeProductWithDateTime(): void
    {
        $product = new Product();
        $product->availabilityDate = new DateTime('2025-12-01');
        $product->expirationDate = new DateTime('2026-01-15');

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertSame('2025-12-01', $decoded['availability_date']);
        $this->assertSame('2026-01-15', $decoded['expiration_date']);
    }

    public function testSerializeProductWithDimensions(): void
    {
        $product = new Product();
        $product->dimensions = new Dimensions(12.0, 8.0, 5.0, 'in');

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertSame('12x8x5 in', $decoded['dimensions']);
    }

    public function testSerializeProductWithIndividualDimensions(): void
    {
        $product = new Product();
        $product->length = 12.5;
        $product->width = 8.0;
        $product->height = 5.25;
        $product->dimensionUnit = 'cm';

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertSame('12.5x8x5.25 cm', $decoded['dimensions']);
        $this->assertSame('12.5 cm', $decoded['length']);
        $this->assertSame('8 cm', $decoded['width']);
        $this->assertSame('5.25 cm', $decoded['height']);
    }

    public function testSerializeProductWithDateRange(): void
    {
        $product = new Product();
        $product->salePriceEffectiveDate = new DateRange(
            new DateTime('2025-07-01'),
            new DateTime('2025-07-15')
        );

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertSame('2025-07-01 / 2025-07-15', $decoded['sale_price_effective_date']);
    }

    public function testSerializeProductWithArrays(): void
    {
        $product = new Product();
        $product->additionalImageLink = ['https://example.com/img1.jpg', 'https://example.com/img2.jpg'];
        $product->relatedProductId = ['SKU111', 'SKU222'];

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertIsArray($decoded['additional_image_link']);
        $this->assertCount(2, $decoded['additional_image_link']);
        $this->assertIsArray($decoded['related_product_id']);
        $this->assertCount(2, $decoded['related_product_id']);
    }

    public function testSerializeProductWithShipping(): void
    {
        $product = new Product();
        $shipping = new Shipping('US', 'CA', 'Overnight', new Money('1600', new Currency('USD')));
        $product->shipping = [$shipping];

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertIsArray($decoded['shipping']);
        $this->assertCount(1, $decoded['shipping']);
        $this->assertSame('US:CA:Overnight:16.00 USD', $decoded['shipping'][0]);
    }

    public function testSerializeManyProducts(): void
    {
        $product1 = $this->createBasicProduct();
        $product2 = $this->createBasicProduct();
        $product2->id = 'SKU99999';

        $jsonl = $this->serializer->serializeMany([$product1, $product2]);

        $lines = \explode("\n", $jsonl);
        $this->assertCount(2, $lines);

        $decoded1 = \json_decode($lines[0], true);
        $decoded2 = \json_decode($lines[1], true);

        $this->assertSame('SKU12345', $decoded1['id']);
        $this->assertSame('SKU99999', $decoded2['id']);
    }

    public function testSerializeToFileWithoutCompression(): void
    {
        $product = $this->createBasicProduct();
        $filePath = \sys_get_temp_dir() . '/test_product.jsonl';

        $this->serializer->serializeToFile([$product], $filePath, false);

        $this->assertFileExists($filePath);
        $content = \file_get_contents($filePath);
        $decoded = \json_decode($content, true);

        $this->assertSame('SKU12345', $decoded['id']);

        \unlink($filePath);
    }

    public function testSerializeToFileWithCompression(): void
    {
        $product = $this->createBasicProduct();
        $filePath = \sys_get_temp_dir() . '/test_product.jsonl.gz';

        $this->serializer->serializeToFile([$product], $filePath, true);

        $this->assertFileExists($filePath);

        $content = \gzdecode(\file_get_contents($filePath));
        $decoded = \json_decode($content, true);

        $this->assertSame('SKU12345', $decoded['id']);

        \unlink($filePath);
    }

    public function testSerializeNullValues(): void
    {
        $product = new Product();

        $json = $this->serializer->serialize($product);
        $decoded = \json_decode($json, true);

        $this->assertNull($decoded['id']);
        $this->assertNull($decoded['price']);
        $this->assertNull($decoded['condition']);
    }

    private function createBasicProduct(): Product
    {
        $product = new Product();
        $product->enableSearch = true;
        $product->enableCheckout = true;
        $product->id = 'SKU12345';
        $product->title = 'Men\'s Trail Running Shoes';
        $product->description = 'Waterproof trail shoe';
        $product->link = 'https://example.com/product/SKU12345';
        $product->price = new Money('7999', new Currency('USD'));
        $product->availability = Availability::IN_STOCK;
        $product->inventoryQuantity = 25;

        return $product;
    }
}
