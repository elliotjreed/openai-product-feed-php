<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed;

use ElliotJReed\OpenAiProductFeed\CsvFeed;
use ElliotJReed\OpenAiProductFeed\Enum\Delimiter;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidFeedState;
use ElliotJReed\OpenAiProductFeed\Internal\Columns;
use ElliotJReed\OpenAiProductFeed\Internal\DelimitedWriter;
use ElliotJReed\OpenAiProductFeed\Internal\Writer;
use ElliotJReed\OpenAiProductFeed\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(InvalidFeedState::class)]
#[CoversClass(Columns::class)]
#[CoversClass(DelimitedWriter::class)]
#[CoversClass(Writer::class)]
#[CoversClass(CsvFeed::class)]
final class CsvFeedTest extends TestCase
{
    public function testItWritesEverySpecificationColumnAsItsHeader(): void
    {
        $this->assertSame(Columns::all(), self::rows(new CsvFeed()->toCsv())[0]);
    }

    public function testItWritesTheHeaderEvenWhenNoProductsAreAdded(): void
    {
        $this->assertCount(1, self::rows(new CsvFeed()->toCsv()));
    }

    public function testItWritesOneRowPerProductUnderTheMatchingColumns(): void
    {
        $rows = self::rows(new CsvFeed()->addProduct(ProductTest::minimalProduct())->toCsv());
        $row = \array_combine($rows[0], $rows[1]);

        $this->assertCount(2, $rows);
        $this->assertSame('TRAIL-BLK-10', $row['item_id']);
        $this->assertSame('79.99 USD', $row['price']);
        $this->assertSame('Northline Outdoor', $row['seller_name']);
    }

    public function testItLeavesUnsetAttributesAsEmptyCells(): void
    {
        $rows = self::rows(new CsvFeed()->addProduct(ProductTest::minimalProduct())->toCsv());
        $row = \array_combine($rows[0], $rows[1]);

        $this->assertSame('', $row['sale_price']);
        $this->assertSame('', $row['gtin']);
    }

    public function testItNarrowsTheColumnsToThoseAskedFor(): void
    {
        $rows = self::rows(
            new CsvFeed()
                ->withColumns('item_id', 'title', 'price', 'availability')
                ->addProduct(ProductTest::minimalProduct())
                ->toCsv()
        );

        $this->assertSame(['item_id', 'title', 'price', 'availability'], $rows[0]);
        $this->assertSame(['TRAIL-BLK-10', 'Trail running shoes — black, size 10', '79.99 USD', 'in_stock'], $rows[1]);
    }

    public function testItThrowsWhenAskedForAColumnTheSpecificationDoesNotDocument(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            '"enable_search" is not a field in the OpenAI product feed specification. ' .
            'Did you mean "is_eligible_search"?'
        );

        new CsvFeed()->withColumns('item_id', 'enable_search');
    }

    public function testItThrowsWhenTheColumnsAreNarrowedAfterTheFirstProduct(): void
    {
        $feed = new CsvFeed()->addProduct(ProductTest::minimalProduct());

        $this->expectException(InvalidFeedState::class);
        $this->expectExceptionMessage(
            'The withColumns() method configures the feed and must be called before the first product is added.'
        );

        $feed->withColumns('item_id');
    }

    public function testItRendersStructuredAttributesAsEmbeddedJson(): void
    {
        $rows = self::rows(
            new CsvFeed()->withoutValidation()->addProduct(ProductTest::fullyPopulatedProduct())->toCsv()
        );
        $row = \array_combine($rows[0], $rows[1]);

        $this->assertSame(['color' => 'Black', 'size' => '10'], \json_decode($row['variant_dict'], true));
        $this->assertSame(
            ['length' => '30', 'width' => '20', 'height' => '12', 'unit' => 'cm'],
            \json_decode($row['dimensions'], true)
        );
    }

    public function testItRendersListsAsCommaSeparatedValues(): void
    {
        $rows = self::rows(
            new CsvFeed()->withoutValidation()->addProduct(ProductTest::fullyPopulatedProduct())->toCsv()
        );
        $row = \array_combine($rows[0], $rows[1]);

        $this->assertSame(
            'https://example.com/images/trail-side.jpg,https://example.com/images/trail-sole.jpg',
            $row['additional_image_urls']
        );
        $this->assertSame('US,CA', $row['target_countries']);
    }

    public function testItPercentEncodesACommaWithinAListedUrl(): void
    {
        $rows = self::rows(
            new CsvFeed()
                ->addProduct(
                    ProductTest::minimalProduct()->addAdditionalImageUrl('https://example.com/a,b.jpg')
                )
                ->toCsv()
        );
        $row = \array_combine($rows[0], $rows[1]);

        $this->assertSame('https://example.com/a%2Cb.jpg', $row['additional_image_urls']);
    }

    public function testItRendersBooleansAsLowercaseText(): void
    {
        $rows = self::rows(
            new CsvFeed()
                ->addProduct(ProductTest::minimalProduct()->setEligibleForSearch(true)->setDigital(false))
                ->toCsv()
        );
        $row = \array_combine($rows[0], $rows[1]);

        $this->assertSame('true', $row['is_eligible_search']);
        $this->assertSame('false', $row['is_digital']);
    }

    public function testItQuotesCellsContainingCommasQuotesAndNewlines(): void
    {
        $description = "A shoe, with a \"waterproof\" lining.\nSecond line.";
        $rows = self::rows(
            new CsvFeed()
                ->withColumns('item_id', 'description')
                ->addProduct(ProductTest::minimalProduct()->setDescription($description))
                ->toCsv()
        );

        $this->assertSame($description, $rows[1][1]);
    }

    public function testItWritesTabSeparatedValuesWhenAsked(): void
    {
        $csv = new CsvFeed()
            ->withTabSeparated()
            ->withColumns('item_id', 'title')
            ->addProduct(ProductTest::minimalProduct())
            ->toCsv();

        $this->assertSame("item_id\ttitle", \explode("\n", $csv)[0]);
    }

    public function testItThrowsWhenTheDelimiterIsChangedAfterTheFirstProduct(): void
    {
        $feed = new CsvFeed()->addProduct(ProductTest::minimalProduct());

        $this->expectException(InvalidFeedState::class);
        $this->expectExceptionMessage('must be called before the first product is added');

        $feed->withTabSeparated();
    }

    public function testItStreamsTheHeaderAsSoonAsTheStreamIsGiven(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new CsvFeed()->withColumns('item_id', 'title')->stream($stream);

        \rewind($stream);
        $header = \stream_get_contents($stream);

        $feed->addProduct(ProductTest::minimalProduct());
        $feed->end();
        \fclose($stream);

        $this->assertSame("item_id,title\n", $header);
    }

    public function testItStreamsEachProductAsItIsAdded(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new CsvFeed()->withColumns('item_id')->stream($stream);

        $feed->addProduct(ProductTest::minimalProduct());
        $feed->addProduct(ProductTest::minimalProduct()->setItemId('TRAIL-BLK-11'));
        $feed->end();

        \rewind($stream);
        $contents = \stream_get_contents($stream);
        \fclose($stream);

        $this->assertSame("item_id\nTRAIL-BLK-10\nTRAIL-BLK-11\n", $contents);
    }

    public function testItThrowsWhenAskedForItsContentsWhileStreaming(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new CsvFeed()->stream($stream);

        try {
            $this->expectException(InvalidFeedState::class);
            $this->expectExceptionMessage('call end() instead of calling toCsv()');

            $feed->toCsv();
        } finally {
            \fclose($stream);
        }
    }

    public function testItRejectsAProductWhichDoesNotSatisfyTheSpecification(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The brand attribute is required, but has not been set on this product.');

        new CsvFeed()->addProduct(
            new Product()
                ->setItemId('TRAIL-BLK-10')
                ->setTitle('Trail running shoes')
                ->setDescription('Waterproof trail shoes.')
                ->setUrl('https://example.com/products/trail')
                ->setImageUrl('https://example.com/images/trail.jpg')
                ->setPrice('79.99', 'USD')
                ->setAvailability('in_stock')
                ->setSellerName('Northline Outdoor')
        );
    }

    public function testItUsesTheDelimiterEnumDirectly(): void
    {
        $csv = new CsvFeed()->withDelimiter(Delimiter::Tab)->withColumns('item_id')->toCsv();

        $this->assertSame("item_id\n", $csv);
    }

    /**
     * @return list<list<string>>
     */
    private static function rows(string $csv, string $delimiter = ','): array
    {
        $stream = \fopen('php://memory', 'w+');
        \fwrite($stream, $csv);
        \rewind($stream);

        $rows = [];
        while (false !== ($row = \fgetcsv($stream, 0, $delimiter, '"', ''))) {
            $rows[] = $row;
        }
        \fclose($stream);

        return $rows;
    }
}
