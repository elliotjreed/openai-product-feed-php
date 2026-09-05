<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Exception;

use InvalidArgumentException;

/**
 * Thrown as soon as a value is set which OpenAI would reject, so that the mistake surfaces at the point it is made
 * rather than when the feed is ingested.
 */
final class InvalidAttribute extends InvalidArgumentException implements OpenAiProductFeedException
{
    public static function tooLong(string $attribute, int $maximumLength, int $lengthGiven): self
    {
        return new self(\sprintf(
            'The %s attribute must not exceed %d characters, %d characters given.',
            $attribute,
            $maximumLength,
            $lengthGiven
        ));
    }

    public static function missing(string $attribute): self
    {
        return new self(\sprintf(
            'The %s attribute is required, but has not been set on this product.',
            $attribute
        ));
    }

    public static function blank(string $attribute): self
    {
        return new self(\sprintf('The %s attribute must not be empty.', $attribute));
    }

    public static function notUtf8(string $attribute): self
    {
        return new self(\sprintf('The %s attribute must be valid UTF-8 text.', $attribute));
    }

    public static function invalidUrl(string $attribute, string $url): self
    {
        return new self(\sprintf(
            'The %s attribute must be a valid HTTP or HTTPS URL, "%s" given.',
            $attribute,
            $url
        ));
    }

    public static function invalidCurrency(string $currency): self
    {
        return new self(\sprintf('"%s" is not a valid ISO 4217 currency code.', $currency));
    }

    public static function invalidCountry(string $attribute, string $country): self
    {
        return new self(\sprintf(
            '"%s" is not a valid ISO 3166-1 alpha-2 country code for the %s attribute.',
            $country,
            $attribute
        ));
    }

    public static function invalidNumber(string $attribute, string $valueGiven): self
    {
        return new self(\sprintf(
            'The %s attribute must be a number using a full stop as the decimal separator, without thousands ' .
            'separators or exponent notation, "%s" given.',
            $attribute,
            $valueGiven
        ));
    }

    public static function negative(string $attribute, string $valueGiven): self
    {
        return new self(\sprintf('The %s attribute must not be negative, "%s" given.', $attribute, $valueGiven));
    }

    public static function notGreaterThanZero(string $attribute, string $valueGiven): self
    {
        return new self(\sprintf('The %s attribute must be greater than 0, "%s" given.', $attribute, $valueGiven));
    }

    public static function outOfRange(string $attribute, string $valueGiven, string $minimum, string $maximum): self
    {
        return new self(\sprintf(
            'The %s attribute must be between %s and %s, %s given.',
            $attribute,
            $minimum,
            $maximum,
            $valueGiven
        ));
    }

    public static function belowMinimum(string $attribute, string $valueGiven, string $minimum): self
    {
        return new self(\sprintf(
            'The %s attribute must not be less than %s, %s given.',
            $attribute,
            $minimum,
            $valueGiven
        ));
    }

    public static function tooFewDimensions(int $numberGiven): self
    {
        return new self(\sprintf(
            'The dimensions attribute must be given at least two of length, width and height, %d given.',
            $numberGiven
        ));
    }

    public static function invalidGtinLength(string $gtin): self
    {
        return new self(\sprintf('The gtin attribute must be exactly 8, 12, 13 or 14 digits, "%s" given.', $gtin));
    }

    public static function invalidGtinCheckDigit(string $gtin): self
    {
        return new self(\sprintf('The gtin attribute "%s" has an invalid check digit.', $gtin));
    }

    /**
     * @param string $expectation how the value should be expressed, phrased to follow "must be", for example
     *                            "in the format country:region:service_class:price"
     */
    public static function invalidFormat(string $attribute, string $valueGiven, string $expectation): self
    {
        return new self(\sprintf(
            'The %s attribute must be %s, "%s" given.',
            $attribute,
            $expectation,
            $valueGiven
        ));
    }

    /**
     * @param list<string> $allowedValues
     */
    public static function invalidValue(string $attribute, string $valueGiven, array $allowedValues): self
    {
        return new self(\sprintf(
            '"%s" is not a valid value for the %s attribute. Allowed values: %s.',
            $valueGiven,
            $attribute,
            \implode(', ', $allowedValues)
        ));
    }

    public static function unknownColumn(string $column, string $closest): self
    {
        return new self(\sprintf(
            '"%s" is not a field in the OpenAI product feed specification. Did you mean "%s"?',
            $column,
            $closest
        ));
    }

    public static function notLowerThan(string $attribute, string $other, string $value, string $otherValue): self
    {
        return new self(\sprintf(
            'The %s attribute must be lower than the %s attribute, "%s" and "%s" given.',
            $attribute,
            $other,
            $value,
            $otherValue
        ));
    }

    public static function mismatchedCurrency(string $attribute, string $expected, string $given): self
    {
        return new self(\sprintf(
            'The %s attribute must use the same currency as the price attribute, %s and %s given.',
            $attribute,
            $expected,
            $given
        ));
    }

    public static function mutuallyExclusive(string $attribute, string $other): self
    {
        return new self(\sprintf(
            'The %s and %s attributes are alternatives, so only one of them may be set.',
            $attribute,
            $other
        ));
    }

    public static function duplicatedIdentifier(string $attribute, string $other, string $value): self
    {
        return new self(\sprintf(
            'The %s attribute must differ from the %s attribute, "%s" given for both.',
            $attribute,
            $other,
            $value
        ));
    }

    public static function incompleteVariant(string $missing): self
    {
        return new self(\sprintf(
            'A variant needs all of the group_id, listing_has_variations and variant_dict attributes, but %s has ' .
            'not been set on this product.',
            $missing
        ));
    }

    public static function requiredWhenAnyOf(string $attribute, string $others): self
    {
        return new self(\sprintf(
            'The %s attribute is required when the %s attributes are set.',
            $attribute,
            $others
        ));
    }

    public static function requiredWhenTrue(string $attribute, string $flag): self
    {
        return new self(\sprintf(
            'The %s attribute is required when the %s attribute is true.',
            $attribute,
            $flag
        ));
    }

    public static function permittedOnlyWhenTrue(string $attribute, string $flag): self
    {
        return new self(\sprintf(
            'The %s attribute may only be true where the %s attribute is also true.',
            $attribute,
            $flag
        ));
    }

    public static function permittedOnlyWhenNotFalse(string $attribute, string $flag): self
    {
        return new self(\sprintf(
            'The %s attribute may only be set where the %s attribute is not false.',
            $attribute,
            $flag
        ));
    }
}
