<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * What happens if the customer sends the item back.
 *
 * A non-empty returns policy URL currently implies that returns are accepted, even where the accepts returns
 * attribute says otherwise. For a final sale item, set accepts returns to false and leave both the policy URL and the
 * returns deadline unset.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait Returns
{
    private ?bool $acceptsReturns = null;
    private ?int $returnDeadlineInDays = null;
    private ?string $returnPolicy = null;
    private ?bool $acceptsExchanges = null;

    /**
     * Whether returns are accepted for this item.
     *
     * Left unset, this is taken as true where a returns policy URL is given, and false where one is not.
     *
     * @return $this
     */
    public function setAcceptsReturns(bool $acceptsReturns): static
    {
        $this->acceptsReturns = $acceptsReturns;

        return $this;
    }

    /**
     * The number of days a customer has to return the item. Only supply this where returns are accepted.
     *
     * @return $this
     */
    public function setReturnDeadlineInDays(int $days): static
    {
        $this->returnDeadlineInDays = Validate::integer('return_deadline_in_days', $days, 1, \PHP_INT_MAX);

        return $this;
    }

    /**
     * The public returns policy which applies to this item.
     *
     * @return $this
     */
    public function setReturnPolicy(string $returnPolicy): static
    {
        $this->returnPolicy = Validate::url('return_policy', $returnPolicy);

        return $this;
    }

    /**
     * Whether exchanges are accepted for this item. Requires a configured integration.
     *
     * @return $this
     */
    public function setAcceptsExchanges(bool $acceptsExchanges): static
    {
        $this->acceptsExchanges = $acceptsExchanges;

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function returnsAttributes(): array
    {
        return Attributes::set([
            'accepts_returns' => $this->acceptsReturns,
            'return_deadline_in_days' => $this->returnDeadlineInDays,
            'return_policy' => $this->returnPolicy,
            'accepts_exchanges' => $this->acceptsExchanges
        ]);
    }
}
