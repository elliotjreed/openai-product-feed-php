<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Attribute\AdsMetadata;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(AdsMetadata::class)]
final class AdsMetadataTest extends TestCase
{
    public function testItMapsFilterKeysToTheirValues(): void
    {
        $this->assertSame(
            ['custom_label_0' => 'summer', 'custom_label_1' => 'clearance'],
            new AdsMetadata()->add('custom_label_0', 'summer')->add('custom_label_1', 'clearance')->toArray()
        );
    }

    public function testItAcceptsTheMetadataAsAConstructorArgument(): void
    {
        $this->assertSame(
            ['custom_label_0' => 'summer'],
            new AdsMetadata(['custom_label_0' => 'summer'])->toArray()
        );
    }

    public function testItThrowsWhenAKeyIsBlank(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The ads_metadata key attribute must not be empty.');

        new AdsMetadata()->add('', 'summer');
    }

    public function testItThrowsWhenAValueIsBlank(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The ads_metadata "custom_label_0" value attribute must not be empty.');

        new AdsMetadata()->add('custom_label_0', '  ');
    }
}
