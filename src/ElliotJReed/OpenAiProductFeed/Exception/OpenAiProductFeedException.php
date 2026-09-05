<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Exception;

use Throwable;

/**
 * Implemented by every exception thrown by this library, allowing all of them to be caught with a single catch block.
 */
interface OpenAiProductFeedException extends Throwable
{
}
