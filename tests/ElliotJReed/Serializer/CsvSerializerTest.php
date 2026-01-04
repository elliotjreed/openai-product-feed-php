<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\Serializer;

use DateTime;
use ElliotJReed\Entity\Product;
use ElliotJReed\Enum\Availability;
use ElliotJReed\Enum\Condition;
use ElliotJReed\Serializer\CsvSerializer;
use ElliotJReed\ValueObject\Dimensions;
use ElliotJReed\ValueObject\Shipping;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvSerializer::class)]
final class CsvSerializerTest extends TestCase
{
    private CsvSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new CsvSerializer();
    }

    public function testSerializeSingleProduct(): void
    {
        $product = $this->createBasicProduct();

        $csv = $this->serializer->serialize($product);
        $lines = \explode("\n", $csv);

        $this->assertCount(2, $lines); // Header + 1 row
        $this->assertStringContainsString('enable_search', $lines[0]);
        $this->assertStringContainsString('SKU12345', $lines[1]);
    }

    public function testSerializeProductWithEnums(): void
    {
        $product = new Product();
        $product->condition = Condition::NEW;
        $product->availability = Availability::IN_STOCK;

        $csv = $this->serializer->serialize($product);
        $lines = \explode("\n", $csv);
        $values = \str_getcsv($lines[1], escape: "\\");

        $headerLine = \str_getcsv($lines[0], escape: "\\");
        $conditionIndex = \array_search('condition', $headerLine, true);
        $availabilityIndex = \array_search('availability', $headerLine, true);

        $this->assertSame('new', $values[$conditionIndex]);
        $this->assertSame('in_stock', $values[$availabilityIndex]);
    }

    public function testSerializeProductWithDateTime(): void
    {
        $product = new Product();
        $product->availabilityDate = new DateTime('2025-12-01');

        $csv = $this->serializer->serialize($product);
        $lines = \explode("\n", $csv);

        $this->assertStringContainsString('2025-12-01', $csv);
    }

    public function testSerializeProductWithDimensions(): void
    {
        $product = new Product();
        $product->dimensions = new Dimensions(12.0, 8.0, 5.0, 'in');

        $csv = $this->serializer->serialize($product);

        $this->assertStringContainsString('12x8x5 in', $csv);
    }

    public function testSerializeProductWithBooleans(): void
    {
        $product = new Product();
        $product->enableSearch = true;
        $product->enableCheckout = false;

        $csv = $this->serializer->serialize($product);
        $lines = \explode("\n", $csv);
        $values = \str_getcsv($lines[1], escape: "\\");

        $this->assertSame('true', $values[0]);
        $this->assertSame('false', $values[1]);
    }

    public function testSerializeProductWithArrays(): void
    {
        $product = new Product();
        $product->additionalImageLink = ['https://example.com/img1.jpg', 'https://example.com/img2.jpg'];

        $csv = $this->serializer->serialize($product);

        $this->assertStringContainsString('https://example.com/img1.jpg,https://example.com/img2.jpg', $csv);
    }

    public function testSerializeProductWithMoney(): void
    {
        $product = new Product();
        $product->price = new Money('7999', new Currency('USD'));
        $product->salePrice = new Money('5999', new Currency('USD'));

        $csv = $this->serializer->serialize($product);

        $this->assertStringContainsString('79.99 USD', $csv);
        $this->assertStringContainsString('59.99 USD', $csv);
    }

    public function testSerializeManyProducts(): void
    {
        $product1 = $this->createBasicProduct();
        $product2 = $this->createBasicProduct();
        $product2->id = 'SKU99999';

        $csv = $this->serializer->serializeMany([$product1, $product2]);
        $lines = \explode("\n", $csv);

        $this->assertCount(3, $lines); // Header + 2 rows
        $this->assertStringContainsString('enable_search', $lines[0]);
        $this->assertStringContainsString('SKU12345', $lines[1]);
        $this->assertStringContainsString('SKU99999', $lines[2]);
    }

    public function testSerializeToFileWithoutCompression(): void
    {
        $product = $this->createBasicProduct();
        $filePath = \sys_get_temp_dir() . '/test_product.csv';

        $this->serializer->serializeToFile([$product], $filePath, false);

        $this->assertFileExists($filePath);
        $content = \file_get_contents($filePath);

        $this->assertStringContainsString('SKU12345', $content);
        $this->assertStringContainsString('enable_search', $content);

        \unlink($filePath);
    }

    public function testSerializeToFileWithCompression(): void
    {
        $product = $this->createBasicProduct();
        $filePath = \sys_get_temp_dir() . '/test_product.csv.gz';

        $this->serializer->serializeToFile([$product], $filePath, true);

        $this->assertFileExists($filePath);

        $content = \gzdecode(\file_get_contents($filePath));

        $this->assertStringContainsString('SKU12345', $content);
        $this->assertStringContainsString('enable_search', $content);

        \unlink($filePath);
    }

    public function testCsvEscaping(): void
    {
        $product = new Product();
        $product->title = 'Product with "quotes" and, commas';
        $product->description = 'Description with
newline';

        $csv = $this->serializer->serialize($product);

        $lines = \explode("\n", $csv);
        $this->assertGreaterThanOrEqual(2, \count($lines));
    }

    public function testSerializeProductWithShipping(): void
    {
        $product = new Product();
        $shipping1 = new Shipping('US', 'CA', 'Overnight', new Money('1600', new Currency('USD')));
        $shipping2 = new Shipping('US', 'NY', 'Standard', new Money('500', new Currency('USD')));
        $product->shipping = [$shipping1, $shipping2];

        $csv = $this->serializer->serialize($product);

        $this->assertStringContainsString('US:CA:Overnight:16.00 USD', $csv);
        $this->assertStringContainsString('US:NY:Standard:5.00 USD', $csv);
    }

    public function testHeaderRow(): void
    {
        $product = new Product();
        $csv = $this->serializer->serialize($product);
        $lines = \explode("\n", $csv);
        $headers = \str_getcsv($lines[0], escape: "\\");

        $this->assertContains('enable_search', $headers);
        $this->assertContains('enable_checkout', $headers);
        $this->assertContains('id', $headers);
        $this->assertContains('title', $headers);
        $this->assertContains('price', $headers);
        $this->assertContains('availability', $headers);
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
