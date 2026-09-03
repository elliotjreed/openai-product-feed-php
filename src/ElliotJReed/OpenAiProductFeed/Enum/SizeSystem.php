<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Enum;

/**
 * The national sizing system a size label is expressed in.
 *
 * These are the values OpenAI accepts, which are not all ISO 3166-1 alpha-2 country codes: Mexico is submitted as
 * "MEX" rather than "MX".
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
enum SizeSystem: string
{
    case UnitedStates = 'US';

    case UnitedKingdom = 'UK';

    case Europe = 'EU';

    case Germany = 'DE';

    case France = 'FR';

    case Japan = 'JP';

    case China = 'CN';

    case Italy = 'IT';

    case Brazil = 'BR';

    case Mexico = 'MEX';

    case Australia = 'AU';
}
