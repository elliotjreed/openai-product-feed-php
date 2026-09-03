<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Enum\Delimiter;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidFeedState;

/**
 * Renders products as a delimited feed: a header row naming the columns, then one row per product.
 *
 * A delimited feed is flat, so the attributes the specification defines as objects are written as embedded JSON, and
 * the ones defined as lists are written as comma-separated values. Booleans become the lowercase words OpenAI reads
 * them as, and an attribute which has not been set leaves its cell empty.
 *
 * @internal
 */
final class DelimitedWriter extends Writer
{
    private const int JSON_FLAGS = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR;

    /**
     * The separator between the values of a list. A comma within a value would be read as the end of that value, so
     * the specification calls for it to be percent-encoded.
     */
    private const string LIST_SEPARATOR = ',';
    private const string ENCODED_LIST_SEPARATOR = '%2C';

    /** @var list<string> */
    private array $columns;

    private Delimiter $delimiter = Delimiter::Comma;

    public function __construct()
    {
        $this->columns = Columns::all();
    }

    /**
     * Narrows the feed to the given columns, which must all be fields the specification documents.
     */
    public function setColumns(string ...$columns): void
    {
        $this->assertNotStarted('withColumns');

        foreach ($columns as $column) {
            if (!Columns::exists($column)) {
                throw InvalidAttribute::unknownColumn($column, Columns::closestTo($column));
            }
        }

        $this->columns = \array_values($columns);
    }

    public function setDelimiter(Delimiter $delimiter): void
    {
        $this->assertNotStarted('withDelimiter');

        $this->delimiter = $delimiter;
    }

    /**
     * @param resource $stream
     */
    protected function start(mixed $stream): void
    {
        $this->writeRow($stream, $this->columns);
    }

    /**
     * @param resource                                                 $stream
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    protected function writeRecord(mixed $stream, array $attributes): void
    {
        $this->writeRow(
            $stream,
            \array_map(
                static fn (string $column): string => self::cell($attributes[$column] ?? null),
                $this->columns
            )
        );
    }

    protected function contentsMethod(): string
    {
        return 'toCsv';
    }

    /**
     * Renders a single value as the text of one cell.
     */
    private static function cell(mixed $value): string
    {
        return match (true) {
            null === $value => '',
            \is_bool($value) => $value ? 'true' : 'false',
            \is_array($value) && \array_is_list($value) => \implode(
                self::LIST_SEPARATOR,
                \array_map(
                    static fn (string $item): string => \str_replace(
                        self::LIST_SEPARATOR,
                        self::ENCODED_LIST_SEPARATOR,
                        $item
                    ),
                    $value
                )
            ),
            \is_array($value) => (string) \json_encode($value, self::JSON_FLAGS),
            default => (string) $value
        };
    }

    /**
     * @param resource     $stream
     * @param list<string> $row
     */
    private function writeRow(mixed $stream, array $row): void
    {
        \fputcsv($stream, $row, $this->delimiter->value, '"', '');
    }

    private function assertNotStarted(string $method): void
    {
        if ($this->hasStarted()) {
            throw InvalidFeedState::alreadyStarted($method);
        }
    }
}
