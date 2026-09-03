<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed;

use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidFeedState;
use ElliotJReed\OpenAiProductFeed\Feed;
use ElliotJReed\OpenAiProductFeed\Internal\JsonLinesWriter;
use ElliotJReed\OpenAiProductFeed\Internal\Writer;
use ElliotJReed\OpenAiProductFeed\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(InvalidFeedState::class)]
#[CoversClass(JsonLinesWriter::class)]
#[CoversClass(Writer::class)]
#[CoversClass(Feed::class)]
final class FeedTest extends TestCase
{
    public function testItWritesNothingForAnEmptyFeed(): void
    {
        $this->assertSame('', new Feed()->toJsonLines());
    }

    public function testItWritesOneJsonObjectPerLine(): void
    {
        $jsonLines = new Feed()
            ->addProduct(ProductTest::minimalProduct())
            ->addProduct(ProductTest::minimalProduct()->setItemId('TRAIL-BLK-11'))
            ->toJsonLines();

        $lines = \explode("\n", \rtrim($jsonLines, "\n"));

        $this->assertCount(2, $lines);
        $this->assertSame('TRAIL-BLK-10', \json_decode($lines[0], true)['item_id']);
        $this->assertSame('TRAIL-BLK-11', \json_decode($lines[1], true)['item_id']);
    }

    public function testItEndsEveryLineWithANewline(): void
    {
        $this->assertStringEndsWith("\n", new Feed()->addProduct(ProductTest::minimalProduct())->toJsonLines());
    }

    /**
     * The keys are compared without regard to their order, which carries no meaning in JSON. This library emits
     * them in the order of the specification's field reference, which groups the seller's name with the rest of
     * the merchant information rather than among the basic product data.
     */
    public function testItWritesTheAttributesTheSpecificationsOwnExampleContains(): void
    {
        $jsonLines = new Feed()->addProduct(
            new Product()
                ->setItemId('MUG-350-BLUE')
                ->setTitle('Blue ceramic mug, 350 mL')
                ->setDescription('Dishwasher-safe glazed ceramic mug with a handle.')
                ->setUrl('https://example.com/products/mug-blue')
                ->setBrand('Northline')
                ->setSellerName('Northline Home')
                ->setImageUrl('https://example.com/images/mug-blue.jpg')
                ->setPrice('18.00', 'USD')
                ->setAvailability('in_stock')
        )->toJsonLines();

        $this->assertEqualsCanonicalizing(
            \json_decode(
                '{"item_id":"MUG-350-BLUE","title":"Blue ceramic mug, 350 mL",' .
                '"description":"Dishwasher-safe glazed ceramic mug with a handle.",' .
                '"url":"https://example.com/products/mug-blue","brand":"Northline",' .
                '"seller_name":"Northline Home","image_url":"https://example.com/images/mug-blue.jpg",' .
                '"price":"18.00 USD","availability":"in_stock"}',
                true
            ),
            \json_decode(\trim($jsonLines), true)
        );
    }

    public function testItLeavesForwardSlashesAndAccentedCharactersUnescaped(): void
    {
        $jsonLines = new Feed()->addProduct(ProductTest::minimalProduct()->setBrand('Café Nord'))->toJsonLines();

        $this->assertStringContainsString('https://example.com/products/trail', $jsonLines);
        $this->assertStringContainsString('Café Nord', $jsonLines);
    }

    public function testItWritesNestedObjectsAndListsInTheirNativeJsonForm(): void
    {
        $attributes = ProductTest::fullyPopulatedProduct();
        $feed = new Feed();

        $decoded = \json_decode(\trim($feed->withoutValidation()->addProduct($attributes)->toJsonLines()), true);

        $this->assertSame(['color' => 'Black', 'size' => '10'], $decoded['variant_dict']);
        $this->assertSame(
            ['https://example.com/images/trail-side.jpg', 'https://example.com/images/trail-sole.jpg'],
            $decoded['additional_image_urls']
        );
        $this->assertTrue($decoded['is_eligible_search']);
        $this->assertSame(254, $decoded['review_count']);
    }

    public function testItStreamsEachProductAsItIsAdded(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new Feed()->stream($stream);

        $feed->addProduct(ProductTest::minimalProduct());
        \rewind($stream);
        $afterOne = \stream_get_contents($stream);

        $feed->addProduct(ProductTest::minimalProduct()->setItemId('TRAIL-BLK-11'));
        $feed->end();
        \rewind($stream);
        $afterTwo = \stream_get_contents($stream);
        \fclose($stream);

        $this->assertCount(1, \explode("\n", \rtrim($afterOne, "\n")));
        $this->assertCount(2, \explode("\n", \rtrim($afterTwo, "\n")));
    }

    public function testItLeavesTheStreamOpenForTheCallerToClose(): void
    {
        $stream = \fopen('php://memory', 'w+');

        new Feed()->stream($stream)->addProduct(ProductTest::minimalProduct())->end();

        $this->assertIsResource($stream);
        \fclose($stream);
    }

    public function testItThrowsWhenStreamingToSomethingWhichIsNotAStream(): void
    {
        $this->expectException(InvalidFeedState::class);
        $this->expectExceptionMessage('The feed must be streamed to a writable stream resource');

        new Feed()->stream('feed.jsonl');
    }

    public function testItThrowsWhenStreamedTwice(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new Feed()->stream($stream);

        try {
            $this->expectException(InvalidFeedState::class);
            $this->expectExceptionMessage('This feed is being streamed');

            $feed->stream($stream);
        } finally {
            \fclose($stream);
        }
    }

    public function testItThrowsWhenAskedForItsContentsWhileStreaming(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new Feed()->stream($stream);

        try {
            $this->expectException(InvalidFeedState::class);
            $this->expectExceptionMessage('Write the products to the stream and call end() instead');

            $feed->toJsonLines();
        } finally {
            \fclose($stream);
        }
    }

    public function testItThrowsWhenEndedWithoutBeingStreamed(): void
    {
        $this->expectException(InvalidFeedState::class);
        $this->expectExceptionMessage('This feed is not being streamed. Call stream() before calling end().');

        new Feed()->end();
    }

    public function testItThrowsWhenAProductIsAddedAfterTheFeedHasEnded(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new Feed()->stream($stream)->addProduct(ProductTest::minimalProduct());
        $feed->end();

        try {
            $this->expectException(InvalidFeedState::class);
            $this->expectExceptionMessage('This feed has been ended and can no longer be written to.');

            $feed->addProduct(ProductTest::minimalProduct());
        } finally {
            \fclose($stream);
        }
    }

    public function testItThrowsWhenStreamingIsAskedForAfterTheFirstProductHasBeenAdded(): void
    {
        $feed = new Feed()->addProduct(ProductTest::minimalProduct());
        $stream = \fopen('php://memory', 'w+');

        try {
            $this->expectException(InvalidFeedState::class);
            $this->expectExceptionMessage(
                'The stream() method configures the feed and must be called before the first product is added.'
            );

            $feed->stream($stream);
        } finally {
            \fclose($stream);
        }
    }

    public function testItThrowsWhenEndedTwice(): void
    {
        $stream = \fopen('php://memory', 'w+');
        $feed = new Feed()->stream($stream);
        $feed->end();

        try {
            $this->expectException(InvalidFeedState::class);
            $this->expectExceptionMessage('This feed has been ended and can no longer be written to.');

            $feed->end();
        } finally {
            \fclose($stream);
        }
    }

    public function testItRejectsAProductWhichDoesNotSatisfyTheSpecification(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The title attribute is required, but has not been set on this product.');

        new Feed()->addProduct(new Product()->setItemId('TRAIL-BLK-10'));
    }

    public function testItWritesAnIncompleteProductWhenValidationIsTurnedOff(): void
    {
        $jsonLines = new Feed()
            ->withoutValidation()
            ->addProduct(new Product()->setItemId('TRAIL-BLK-10'))
            ->toJsonLines();

        $this->assertSame(['item_id' => 'TRAIL-BLK-10'], \json_decode(\trim($jsonLines), true));
    }

    public function testItReturnsTheSameContentsWhenAskedTwice(): void
    {
        $feed = new Feed()->addProduct(ProductTest::minimalProduct());

        $this->assertSame($feed->toJsonLines(), $feed->toJsonLines());
    }
}
