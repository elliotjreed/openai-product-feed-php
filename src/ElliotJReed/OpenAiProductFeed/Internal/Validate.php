<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Internal;

use BackedEnum;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;

/**
 * The validation rules shared by every attribute setter. Each method either returns the value in the form OpenAI
 * expects, or throws so that the mistake surfaces at the point it is made.
 *
 * @internal
 */
final class Validate
{
    /**
     * The C0 control characters, excluding tab, line feed and carriage return. They carry no meaning in feed content
     * and are almost always an artefact of the source data rather than something the merchant intended.
     */
    private const string CONTROL_CHARACTERS = '/[\x{0}-\x{8}\x{B}\x{C}\x{E}-\x{1F}\x{7F}]/u';

    /**
     * A plain decimal number: optional sign, digits, and an optional fractional part. Thousands separators and
     * exponent notation are excluded, both of which the specification prohibits.
     */
    private const string PLAIN_DECIMAL = '/^-?\d+(\.\d+)?$/';

    /**
     * The precision a floating point measurement is rendered to before its trailing zeros are trimmed. Six places
     * is far beyond any physical measurement a feed carries, and keeps the value clear of exponent notation.
     */
    private const int DECIMAL_PLACES = 6;

    /**
     * Trims and sanitises a text value, enforcing a maximum length only where the specification states one.
     */
    public static function text(string $attribute, string $value, ?int $maximumLength = null): string
    {
        if (!\mb_check_encoding($value, 'UTF-8')) {
            throw InvalidAttribute::notUtf8($attribute);
        }

        $sanitised = \trim((string) \preg_replace(self::CONTROL_CHARACTERS, '', $value));

        if ('' === $sanitised) {
            throw InvalidAttribute::blank($attribute);
        }

        $length = \mb_strlen($sanitised);
        if (null !== $maximumLength && $length > $maximumLength) {
            throw InvalidAttribute::tooLong($attribute, $maximumLength, $length);
        }

        return $sanitised;
    }

    /**
     * Validates an absolute, publicly resolvable HTTP or HTTPS URL. Relative URLs and other schemes are rejected,
     * since OpenAI has to be able to fetch the page or image without any further context.
     */
    public static function url(string $attribute, string $value): string
    {
        if (!\mb_check_encoding($value, 'UTF-8')) {
            throw InvalidAttribute::notUtf8($attribute);
        }

        $url = \trim((string) \preg_replace(self::CONTROL_CHARACTERS, '', $value));

        if ('' === $url) {
            throw InvalidAttribute::blank($attribute);
        }

        $scheme = \strtolower((string) \parse_url($url, \PHP_URL_SCHEME));
        if (!\in_array($scheme, ['http', 'https'], true) || false === \filter_var($url, \FILTER_VALIDATE_URL)) {
            throw InvalidAttribute::invalidUrl($attribute, $url);
        }

        return $url;
    }

    /**
     * Formats an amount and currency in the "79.99 USD" form OpenAI expects. Numeric strings are preserved verbatim
     * so that merchants storing prices as decimal strings do not lose precision through a float conversion.
     */
    public static function money(string $attribute, float | int | string $amount, string $currency): string
    {
        $code = self::currency($currency);
        $formatted = \is_string($amount)
            ? self::plainDecimalString($attribute, $amount)
            : \number_format((float) $amount, 2, '.', '');

        if (\str_starts_with($formatted, '-')) {
            throw InvalidAttribute::negative($attribute, $formatted);
        }

        return $formatted . ' ' . $code;
    }

    public static function currency(string $currency): string
    {
        $code = \strtoupper(\trim($currency));

        if (!Currency::exists($code)) {
            throw InvalidAttribute::invalidCurrency($currency);
        }

        return $code;
    }

    public static function country(string $attribute, string $country): string
    {
        $code = \strtoupper(\trim($country));

        if (!Country::exists($code)) {
            throw InvalidAttribute::invalidCountry($attribute, $code);
        }

        return $code;
    }

    /**
     * Returns a decimal as the string the specification calls for, preserving the precision of a value given as a
     * string rather than rounding it through a float.
     *
     * @param bool $exclusiveMinimum whether the minimum is a bound the value must exceed rather than merely reach,
     *                               used for the measurements the specification describes as positive
     */
    public static function decimal(
        string $attribute,
        float | int | string $value,
        float $minimum,
        float $maximum,
        bool $exclusiveMinimum = false
    ): string {
        $formatted = self::plainDecimal($attribute, $value);
        $number = (float) $formatted;

        if ($exclusiveMinimum && $number <= $minimum) {
            throw InvalidAttribute::notGreaterThanZero($attribute, $formatted);
        }

        if ($number < $minimum || $number > $maximum) {
            throw InvalidAttribute::outOfRange($attribute, $formatted, (string) $minimum, (string) $maximum);
        }

        return $formatted;
    }

    /**
     * Returns a decimal rounded to a fixed number of places, as the specification requires for the ratings it says
     * should be expressed "to two decimal places".
     */
    public static function fixedDecimal(
        string $attribute,
        float | int | string $value,
        float $minimum,
        float $maximum,
        int $decimalPlaces
    ): string {
        $number = (float) self::plainDecimal($attribute, $value);

        if ($number < $minimum || $number > $maximum) {
            throw InvalidAttribute::outOfRange($attribute, self::plainDecimal($attribute, $value), (string) $minimum, (string) $maximum);
        }

        return \number_format($number, $decimalPlaces, '.', '');
    }

    public static function integer(string $attribute, int $value, int $minimum, int $maximum): int
    {
        if ($value < $minimum) {
            throw InvalidAttribute::belowMinimum($attribute, (string) $value, (string) $minimum);
        }

        if ($value > $maximum) {
            throw InvalidAttribute::outOfRange($attribute, (string) $value, (string) $minimum, (string) $maximum);
        }

        return $value;
    }

    /**
     * @template T of BackedEnum
     *
     * @param string|T        $value
     * @param class-string<T> $enumClass
     *
     * @return T
     */
    public static function enum(string $attribute, string | BackedEnum $value, string $enumClass): BackedEnum
    {
        if ($value instanceof $enumClass) {
            return $value;
        }

        $given = $value instanceof BackedEnum ? (string) $value->value : \trim($value);
        $case = $enumClass::tryFrom($given);

        if (null === $case) {
            throw InvalidAttribute::invalidValue($attribute, $given, \array_column($enumClass::cases(), 'value'));
        }

        return $case;
    }

    /**
     * Validates a GTIN, which must be exactly 8, 12, 13 or 14 digits and carry a valid GS1 modulo 10 check digit.
     * Spaces and hyphens are stripped, since they are a common artefact of how identifiers are printed, and leading
     * zeros are preserved because they are significant.
     */
    public static function gtin(string $gtin): string
    {
        $digits = \str_replace([' ', '-'], '', \trim($gtin));

        if (1 !== \preg_match('/^\d{8}$|^\d{12,14}$/', $digits)) {
            throw InvalidAttribute::invalidGtinLength($gtin);
        }

        if ($digits[-1] !== self::gtinCheckDigit(\substr($digits, 0, -1))) {
            throw InvalidAttribute::invalidGtinCheckDigit($digits);
        }

        return $digits;
    }

    /**
     * Normalises a number to a plain decimal string in its most natural form, so that a whole number is not padded
     * with decimal places it does not need.
     */
    private static function plainDecimal(string $attribute, float | int | string $value): string
    {
        if (\is_string($value)) {
            return self::plainDecimalString($attribute, $value);
        }

        if (\is_int($value)) {
            return (string) $value;
        }

        $formatted = \rtrim(\rtrim(\number_format($value, self::DECIMAL_PLACES, '.', ''), '0'), '.');

        return '' === $formatted ? '0' : $formatted;
    }

    /**
     * Accepts a number already expressed as a string, preserving it verbatim so that a merchant storing values as
     * decimal strings does not lose precision through a float conversion. The thousands separators and exponent
     * notation the specification prohibits are rejected.
     */
    private static function plainDecimalString(string $attribute, string $value): string
    {
        $trimmed = \trim($value);

        if (1 !== \preg_match(self::PLAIN_DECIMAL, $trimmed)) {
            throw InvalidAttribute::invalidNumber($attribute, $value);
        }

        return $trimmed;
    }

    /**
     * Calculates the GS1 modulo 10 check digit, weighting the digits alternately by three and one from the right.
     */
    private static function gtinCheckDigit(string $withoutCheckDigit): string
    {
        $sum = 0;

        foreach (\array_reverse(\str_split($withoutCheckDigit)) as $position => $digit) {
            $sum += (int) $digit * (0 === $position % 2 ? 3 : 1);
        }

        return (string) ((10 - $sum % 10) % 10);
    }
}
