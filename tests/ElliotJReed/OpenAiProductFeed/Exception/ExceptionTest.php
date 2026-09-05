<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Exception;

use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidFeedState;
use ElliotJReed\OpenAiProductFeed\Exception\OpenAiProductFeedException;
use ElliotJReed\OpenAiProductFeed\Feed;
use ElliotJReed\OpenAiProductFeed\Product;
use ElliotJReed\Tests\OpenAiProductFeed\ProductTest;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(InvalidFeedState::class)]
final class ExceptionTest extends TestCase
{
    public function testEveryExceptionCanBeCaughtThroughTheOneInterface(): void
    {
        $this->assertInstanceOf(OpenAiProductFeedException::class, InvalidAttribute::blank('title'));
        $this->assertInstanceOf(OpenAiProductFeedException::class, InvalidFeedState::notStreaming());
    }

    public function testAnInvalidValueIsAnInvalidArgument(): void
    {
        $this->assertInstanceOf(InvalidArgumentException::class, InvalidAttribute::blank('title'));
    }

    public function testMisusingAFeedIsALogicError(): void
    {
        $this->assertInstanceOf(LogicException::class, InvalidFeedState::notStreaming());
    }

    public function testABadValueAndAMisusedFeedAreCaughtByTheSameHandler(): void
    {
        $caught = [];

        $operations = [
            static fn (): Product => new Product()->setTitle(''),
            static fn (): Feed => new Feed()->end(),
            static fn (): Feed => new Feed()->addProduct(new Product())
        ];

        foreach ($operations as $operation) {
            try {
                $operation();
            } catch (OpenAiProductFeedException $exception) {
                $caught[] = $exception::class;
            }
        }

        $this->assertSame([InvalidAttribute::class, InvalidFeedState::class, InvalidAttribute::class], $caught);
    }

    public function testAFeedNamesTheAttributeWhichCausedItToRejectAProduct(): void
    {
        try {
            new Feed()->addProduct(ProductTest::minimalProduct()->setSalePrice('99.99', 'USD'));
        } catch (InvalidAttribute $exception) {
            $this->assertStringContainsString('sale_price', $exception->getMessage());

            return;
        }

        $this->fail('A sale price above the price should be rejected.');
    }

    public function testTheMessagesNameTheAttributeAndWhatWasWrongWithIt(): void
    {
        $this->assertSame(
            'The title attribute must not exceed 150 characters, 151 characters given.',
            InvalidAttribute::tooLong('title', 150, 151)->getMessage()
        );
        $this->assertSame(
            'The gtin attribute "3234567890127" has an invalid check digit.',
            InvalidAttribute::invalidGtinCheckDigit('3234567890127')->getMessage()
        );
        $this->assertSame(
            'The shipping region attribute must be free of the ":" separator, "CA:SF" given.',
            InvalidAttribute::invalidFormat('shipping region', 'CA:SF', 'free of the ":" separator')->getMessage()
        );
    }
}
