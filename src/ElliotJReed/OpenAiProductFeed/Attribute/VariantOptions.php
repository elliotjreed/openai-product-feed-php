<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * The variant_dict attribute: the options which distinguish this variant from the others in its group, mapped to the
 * values selected for it.
 *
 * Setting this requires the group_id attribute, which must differ from the item_id, and the listing_has_variations
 * attribute set to true on every variant row.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
final class VariantOptions implements StructuredAttribute
{
    /** @var array<string, string> */
    private array $options = [];

    /**
     * @param array<string, string> $options the option names mapped to their selected values, for example
     *                                       ["color" => "Black", "size" => "10"]
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $option => $value) {
            $this->add($option, $value);
        }
    }

    /**
     * Adds an option and the value selected for it, for example "color" and "Black". Setting the same option twice
     * replaces the earlier value. Keep these consistent with the equivalent top level attributes, such as colour and
     * size, since OpenAI does not reconcile conflicts between them.
     */
    public function add(string $option, string $value): self
    {
        $name = Validate::text('variant_dict option name', $option);

        $this->options[$name] = Validate::text(\sprintf('variant_dict "%s" option', $name), $value);

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
