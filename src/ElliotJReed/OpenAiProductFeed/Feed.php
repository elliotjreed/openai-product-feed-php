<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed;

use ElliotJReed\OpenAiProductFeed\Internal\JsonLinesWriter;
use ElliotJReed\OpenAiProductFeed\Internal\ProductRules;

/**
 * A product feed rendered as JSON Lines, which is the format OpenAI prefers.
 *
 * Small catalogues can be built in memory and rendered with toJsonLines(). Large catalogues should be streamed
 * instead: call stream() with a writable stream resource, add each product as you read it from your source, then call
 * end(). Each record is written to the stream as it is added, so memory use stays constant no matter how many
 * products the feed contains.
 *
 * Every product is checked against the rules which span more than one attribute as it is added, so an offer OpenAI
 * would reject is caught here rather than during ingestion. Call withoutValidation() to skip that check, which is
 * what you want when building a delta feed carrying only the attributes which have changed.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
class Feed
{
    private readonly JsonLinesWriter $writer;

    private bool $validate = true;

    public function __construct()
    {
        $this->writer = new JsonLinesWriter();
    }

    /**
     * Writes each product as it is given, without checking that it carries every attribute the specification
     * requires.
     *
     * Use this for a delta feed, whose records deliberately carry only the attributes which have changed. Each
     * individual value is still validated as it is set.
     *
     * @return $this
     */
    public function withoutValidation(): static
    {
        $this->validate = false;

        return $this;
    }

    /**
     * Adds a product to the feed. Where the feed is being streamed, the product is written out immediately and is not
     * retained in memory.
     *
     * @return $this
     */
    public function addProduct(Product $product): static
    {
        $attributes = $product->attributes();

        if ($this->validate) {
            ProductRules::assertSatisfied($attributes);
        }

        $this->writer->write($attributes);

        return $this;
    }

    /**
     * Directs this feed's output to a writable stream, such as one returned by fopen("feed.jsonl", "wb"),
     * gzopen("feed.jsonl.gz", "wb9") or fopen("php://output", "wb").
     *
     * @param resource $stream
     *
     * @return $this
     */
    public function stream(mixed $stream): static
    {
        $this->writer->streamTo($stream);

        return $this;
    }

    /**
     * Closes a streamed feed. The stream itself is left open for the caller to close.
     */
    public function end(): void
    {
        $this->writer->end();
    }

    /**
     * Closes the feed and returns the whole document as a string. Use stream() instead for large catalogues.
     */
    public function toJsonLines(): string
    {
        return $this->writer->toString();
    }
}
