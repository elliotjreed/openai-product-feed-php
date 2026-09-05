<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed;

use ElliotJReed\OpenAiProductFeed\Enum\Delimiter;
use ElliotJReed\OpenAiProductFeed\Internal\DelimitedWriter;
use ElliotJReed\OpenAiProductFeed\Internal\ProductRules;

/**
 * A product feed rendered as a delimited file: comma-delimited for a ".csv", or tab-delimited for a ".tsv" or ".txt".
 *
 * A delimited feed needs its columns named in a header row before any product is written, so by default it writes
 * every column the specification documents. An empty cell means the attribute has not been set, which is exactly what
 * the specification says it means, and this is what allows a catalogue of any size to be streamed. Call withColumns()
 * to narrow the file to the columns you actually populate.
 *
 * Small catalogues can be built in memory and rendered with toCsv(). Large catalogues should be streamed instead:
 * call stream() with a writable stream resource, add each product as you read it from your source, then call end().
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
class CsvFeed
{
    private readonly DelimitedWriter $writer;

    private bool $validate = true;

    public function __construct()
    {
        $this->writer = new DelimitedWriter();
    }

    /**
     * Narrows the feed to the given columns, in the order given, rather than writing every column the specification
     * documents.
     *
     * Every name must be one the specification documents; the legacy aliases OpenAI still accepts are rejected in
     * favour of the current name. This configures the feed, so call it before adding the first product.
     *
     * @return $this
     */
    public function withColumns(string ...$columns): static
    {
        $this->writer->setColumns(...$columns);

        return $this;
    }

    /**
     * Separates the cells with tabs rather than commas, for a ".tsv" or ".txt" feed.
     *
     * @return $this
     */
    public function withTabSeparated(): static
    {
        return $this->withDelimiter(Delimiter::Tab);
    }

    /**
     * Separates the cells with the given delimiter. This configures the feed, so call it before adding the first
     * product.
     *
     * @return $this
     */
    public function withDelimiter(Delimiter $delimiter): static
    {
        $this->writer->setDelimiter($delimiter);

        return $this;
    }

    /**
     * Writes each product as it is given, without checking that it carries every attribute the specification
     * requires.
     *
     * Use this for a delta feed, whose rows deliberately carry only the attributes which have changed. Each
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
     * Adds a product to the feed. Where the feed is being streamed, the row is written out immediately and is not
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
     * Directs this feed's output to a writable stream, such as one returned by fopen("feed.csv", "wb"),
     * gzopen("feed.csv.gz", "wb9") or fopen("php://output", "wb"). The header row is written immediately.
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
     * Closes the feed and returns the whole file as a string. Use stream() instead for large catalogues.
     */
    public function toCsv(): string
    {
        return $this->writer->toString();
    }
}
