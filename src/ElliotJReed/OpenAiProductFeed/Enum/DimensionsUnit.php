<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * The unit the length, width and height of an item are measured in.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum DimensionsUnit: string
{
    case Inches = 'in';

    case Centimetres = 'cm';

    case Feet = 'ft';

    case Metres = 'm';

    case Millimetres = 'mm';
}
