<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Attribute;

/**
 * Implemented by the attributes the specification defines as objects rather than plain values.
 *
 * These are written as nested JSON objects in a JSON Lines feed, and as an embedded JSON string in a delimited feed,
 * which is how the specification says to represent them in CSV and TSV.
 */
interface StructuredAttribute
{
    /**
     * The object's keys and values, in the order they were set.
     *
     * @return array<string, string>
     */
    public function toArray(): array;
}
