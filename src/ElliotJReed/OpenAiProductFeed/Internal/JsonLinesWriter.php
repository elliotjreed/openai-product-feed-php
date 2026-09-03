<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Internal;

/**
 * Renders products as JSON Lines: one JSON object per line, which is the format OpenAI prefers.
 *
 * Nested objects and lists are written in their native JSON form, and booleans and numbers keep their JSON types, so
 * nothing has to be flattened into text the way a delimited feed requires.
 *
 * @internal
 */
final class JsonLinesWriter extends Writer
{
    /**
     * Slashes and non-ASCII characters are left as they are: escaping them is valid JSON but makes a feed far harder
     * to read, and every URL in a product feed is full of slashes.
     */
    private const int JSON_FLAGS = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR;

    /**
     * @param resource                                                 $stream
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    protected function writeRecord(mixed $stream, array $attributes): void
    {
        \fwrite($stream, \json_encode((object) $attributes, self::JSON_FLAGS) . "\n");
    }

    protected function contentsMethod(): string
    {
        return 'toJsonLines';
    }
}
