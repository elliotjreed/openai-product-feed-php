<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * Whether the item can be ordered right now.
 *
 * Submit the current status with every row. None of these values schedule an automatic change, so update the feed
 * whenever a product's availability changes.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum Availability: string
{
    /** The item is available to order now. */
    case InStock = 'in_stock';

    /** The item cannot be ordered at the moment. */
    case OutOfStock = 'out_of_stock';

    /** The item has not been released yet, and orders are being taken ahead of its release. */
    case PreOrder = 'pre_order';

    /** The item has been released but is temporarily unavailable, and orders are still being taken. */
    case Backorder = 'backorder';

    /** The availability of the item is genuinely not known. */
    case Unknown = 'unknown';
}
