<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Attribute\StructuredAttribute;

/**
 * Builds the ordered map of field names and values each product trait contributes to the feed.
 *
 * Unlike an RSS feed, where an element may be repeated, each field appears at most once per product, so a map rather
 * than a list of pairs is the natural representation. Only null means "not set": false, zero and the empty string are
 * all values a merchant may deliberately submit.
 *
 * @internal
 */
final class Attributes
{
    /**
     * @param array<string, scalar|list<string>|StructuredAttribute|null> $values keyed by feed field name
     *
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    public static function set(array $values): array
    {
        $attributes = [];

        foreach ($values as $field => $value) {
            if (null === $value) {
                continue;
            }

            $attributes[$field] = $value instanceof StructuredAttribute ? $value->toArray() : $value;
        }

        return $attributes;
    }
}
