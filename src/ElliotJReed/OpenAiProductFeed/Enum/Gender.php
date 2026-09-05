<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * The gender the item is intended for.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum Gender: string
{
    case Male = 'male';

    case Female = 'female';

    case Unisex = 'unisex';
}
