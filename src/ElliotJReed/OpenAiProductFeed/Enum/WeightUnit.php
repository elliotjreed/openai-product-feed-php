<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * The unit the net weight of an item is measured in.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum WeightUnit: string
{
    case Grams = 'g';

    case Kilograms = 'kg';

    case Ounces = 'oz';

    case Pounds = 'lb';
}
