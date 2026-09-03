<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * The condition of the item being sold.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum Condition: string
{
    /** Brand new, unopened and unused. */
    case New = 'new';

    /** Professionally restored to working order, with a warranty where applicable. */
    case Refurbished = 'refurbished';

    /** Previously used, including open-box items. */
    case Used = 'used';
}
