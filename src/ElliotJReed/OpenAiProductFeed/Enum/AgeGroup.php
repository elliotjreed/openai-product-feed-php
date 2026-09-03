<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * The age group the item is intended for.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum AgeGroup: string
{
    /** Up to three months old. */
    case Newborn = 'newborn';

    /** Between three and twelve months old. */
    case Infant = 'infant';

    /** Between one and five years old. */
    case Toddler = 'toddler';

    /** Between five and thirteen years old. */
    case Kids = 'kids';

    /** Teenagers and adults. */
    case Adult = 'adult';
}
