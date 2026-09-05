<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * The character separating the cells of a delimited feed.
 *
 * OpenAI accepts comma-delimited files as ".csv", and tab-delimited files as either ".tsv" or ".txt".
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum Delimiter: string
{
    /** Comma-delimited, written with a ".csv" extension. */
    case Comma = ',';

    /** Tab-delimited, written with a ".tsv" or ".txt" extension. */
    case Tab = "\t";
}
