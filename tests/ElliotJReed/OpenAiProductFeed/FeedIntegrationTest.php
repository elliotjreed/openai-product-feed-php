<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed;

use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;
use ElliotJReed\OpenAiProductFeed\CsvFeed;
use ElliotJReed\OpenAiProductFeed\Enum\Availability;
use ElliotJReed\OpenAiProductFeed\Feed;
use ElliotJReed\OpenAiProductFeed\Internal\Columns;
use ElliotJReed\OpenAiProductFeed\Product;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class FeedIntegrationTest extends TestCase
{
    private const int LARGE_CATALOGUE = 200000;

    public function testAFullyPopulatedProductRoundTripsThroughJsonLines(): void
    {
        $product = ProductTest::fullyPopulatedProduct();
        $expected = $product->attributes();

        $decoded = \json_decode(
            \trim(new Feed()->withoutValidation()->addProduct($product)->toJsonLines()),
            true,
            flags: \JSON_THROW_ON_ERROR
        );

        $this->assertSame($expected, $decoded);
    }

    public function testAFullyPopulatedProductRoundTripsThroughCsv(): void
    {
        $product = ProductTest::fullyPopulatedProduct();
        $csv = new CsvFeed()->withoutValidation()->addProduct($product)->toCsv();

        $stream = \fopen('php://memory', 'w+');
        \fwrite($stream, $csv);
        \rewind($stream);
        $header = \fgetcsv($stream, 0, ',', '"', '');
        $row = \fgetcsv($stream, 0, ',', '"', '');
        \fclose($stream);

        $cells = \array_combine($header, $row);

        $this->assertSame(Columns::all(), $header);
        $this->assertSame('TRAIL-BLK-10', $cells['item_id']);
        $this->assertSame('79.99 USD', $cells['price']);
        $this->assertSame('true', $cells['listing_has_variations']);
        $this->assertSame('false', $cells['is_digital']);
        $this->assertSame('254', $cells['review_count']);
        $this->assertSame(['color' => 'Black', 'size' => '10'], \json_decode($cells['variant_dict'], true));
        $this->assertSame('US,CA', $cells['target_countries']);
    }

    public function testTextContainingDelimitersAndNewlinesSurvivesTheCsvRoundTrip(): void
    {
        $description = "A shoe, with a \"waterproof\" lining.\r\nIt has 30cm\tof lace.";

        $csv = new CsvFeed()
            ->withColumns('item_id', 'description')
            ->addProduct(ProductTest::minimalProduct()->setDescription($description))
            ->toCsv();

        $stream = \fopen('php://memory', 'w+');
        \fwrite($stream, $csv);
        \rewind($stream);
        \fgetcsv($stream, 0, ',', '"', '');
        $row = \fgetcsv($stream, 0, ',', '"', '');
        \fclose($stream);

        $this->assertSame($description, $row[1]);
    }

    public function testEveryProductOfAVariantGroupIsWritten(): void
    {
        $feed = new Feed();

        foreach (['Black' => 'BLK', 'Navy' => 'NVY'] as $colour => $code) {
            $feed->addProduct(
                new Product()
                    ->setItemId('TRAIL-' . $code . '-10')
                    ->setGroupId('TRAIL')
                    ->setListingHasVariations(true)
                    ->setVariantOptions(new VariantOptions(['color' => $colour, 'size' => '10']))
                    ->setTitle(\sprintf('Trail running shoes — %s, size 10', \strtolower($colour)))
                    ->setDescription('Waterproof trail shoes with a rubber outsole.')
                    ->setUrl('https://example.com/products/trail?color=' . \strtolower($colour))
                    ->setBrand('Northline')
                    ->setSellerName('Northline Outdoor')
                    ->setImageUrl('https://example.com/images/trail-' . \strtolower($colour) . '.jpg')
                    ->setPrice('79.99', 'USD')
                    ->setAvailability(Availability::InStock)
            );
        }

        $lines = \explode("\n", \rtrim($feed->toJsonLines(), "\n"));

        $this->assertCount(2, $lines);
        $this->assertSame('TRAIL', \json_decode($lines[0], true)['group_id']);
        $this->assertSame(['color' => 'Navy', 'size' => '10'], \json_decode($lines[1], true)['variant_dict']);
    }

    public function testAGzippedJsonLinesFeedIsReadableAsGzip(): void
    {
        $path = \tempnam(\sys_get_temp_dir(), 'feed') . '.jsonl.gz';
        $stream = \gzopen($path, 'wb9');

        $feed = new Feed()->stream($stream);
        $feed->addProduct(ProductTest::minimalProduct());
        $feed->end();
        \gzclose($stream);

        $contents = \gzdecode((string) \file_get_contents($path));
        \unlink($path);

        $this->assertSame('TRAIL-BLK-10', \json_decode(\trim((string) $contents), true)['item_id']);
    }

    /**
     * The price is quoted because it contains a space, which is how PHP writes a delimited file and which every
     * conforming parser reads back unchanged.
     */
    public function testAGzippedCsvFeedIsReadableAsGzip(): void
    {
        $path = \tempnam(\sys_get_temp_dir(), 'feed') . '.csv.gz';
        $stream = \gzopen($path, 'wb9');

        $feed = new CsvFeed()->withColumns('item_id', 'price')->stream($stream);
        $feed->addProduct(ProductTest::minimalProduct());
        $feed->end();
        \gzclose($stream);

        $contents = \gzdecode((string) \file_get_contents($path));
        \unlink($path);

        $this->assertSame("item_id,price\nTRAIL-BLK-10,\"79.99 USD\"\n", $contents);
    }

    #[Group('slow')]
    public function testStreamingALargeCatalogueKeepsMemoryUseConstant(): void
    {
        $stream = \fopen('php://temp', 'w+');
        $feed = new Feed()->stream($stream);

        $feed->addProduct(self::catalogueProduct(0));
        \gc_collect_cycles();
        $baseline = \memory_get_usage();

        for ($index = 1; $index < self::LARGE_CATALOGUE; ++$index) {
            $feed->addProduct(self::catalogueProduct($index));
        }
        $feed->end();

        \gc_collect_cycles();
        $growth = \memory_get_usage() - $baseline;

        $bytes = \fstat($stream)['size'];
        \fclose($stream);

        $this->assertGreaterThan(20000000, $bytes, 'The catalogue should produce a feed of a meaningful size.');
        $this->assertLessThan(
            1000000,
            $growth,
            \sprintf('Memory grew by %d bytes while streaming %d products.', $growth, self::LARGE_CATALOGUE)
        );
    }

    #[Group('slow')]
    public function testStreamingALargeDelimitedCatalogueKeepsMemoryUseConstant(): void
    {
        $stream = \fopen('php://temp', 'w+');
        $feed = new CsvFeed()->stream($stream);

        $feed->addProduct(self::catalogueProduct(0));
        \gc_collect_cycles();
        $baseline = \memory_get_usage();

        for ($index = 1; $index < self::LARGE_CATALOGUE; ++$index) {
            $feed->addProduct(self::catalogueProduct($index));
        }
        $feed->end();

        \gc_collect_cycles();
        $growth = \memory_get_usage() - $baseline;
        \fclose($stream);

        $this->assertLessThan(
            1000000,
            $growth,
            \sprintf('Memory grew by %d bytes while streaming %d rows.', $growth, self::LARGE_CATALOGUE)
        );
    }

    private static function catalogueProduct(int $index): Product
    {
        return new Product()
            ->setItemId('SKU-' . $index)
            ->setTitle('Trail running shoes, model ' . $index)
            ->setDescription('Waterproof trail shoes with a rubber outsole and a breathable mesh lining.')
            ->setUrl('https://example.com/products/' . $index)
            ->setBrand('Northline')
            ->setSellerName('Northline Outdoor')
            ->setImageUrl('https://example.com/images/' . $index . '.jpg')
            ->setPrice('79.99', 'USD')
            ->setAvailability(Availability::InStock);
    }
}
