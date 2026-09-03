<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * Whether the item can be found in ChatGPT, and whether it can be bought there.
 *
 * These flags do not change how the item appears on your own site. Checkout also needs an integration OpenAI has
 * enabled separately: setting the flag does not complete that onboarding on its own.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait SearchAndCheckout
{
    private ?bool $eligibleForSearch = null;
    private ?bool $eligibleForCheckout = null;
    private ?string $sellerPrivacyPolicy = null;
    private ?string $sellerTermsOfService = null;

    /**
     * Whether the item may be surfaced in search results. Left unset, it is taken as true.
     *
     * Setting this to false disables checkout for the item as well.
     *
     * @return $this
     */
    public function setEligibleForSearch(bool $eligibleForSearch): static
    {
        $this->eligibleForSearch = $eligibleForSearch;

        return $this;
    }

    /**
     * Whether the item may be bought directly within ChatGPT.
     *
     * This only opts the item in where it is also eligible for search, and requires both the seller's privacy policy
     * and terms of service.
     *
     * @return $this
     */
    public function setEligibleForCheckout(bool $eligibleForCheckout): static
    {
        $this->eligibleForCheckout = $eligibleForCheckout;

        return $this;
    }

    /**
     * The seller's public privacy policy, which checkout requires.
     *
     * @return $this
     */
    public function setSellerPrivacyPolicy(string $sellerPrivacyPolicy): static
    {
        $this->sellerPrivacyPolicy = Validate::url('seller_privacy_policy', $sellerPrivacyPolicy);

        return $this;
    }

    /**
     * The seller's public terms of service, which checkout requires. Rendered as the "seller_tos" field.
     *
     * @return $this
     */
    public function setSellerTermsOfService(string $sellerTermsOfService): static
    {
        $this->sellerTermsOfService = Validate::url('seller_tos', $sellerTermsOfService);

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function searchAndCheckoutAttributes(): array
    {
        return Attributes::set([
            'is_eligible_search' => $this->eligibleForSearch,
            'is_eligible_checkout' => $this->eligibleForCheckout,
            'seller_privacy_policy' => $this->sellerPrivacyPolicy,
            'seller_tos' => $this->sellerTermsOfService
        ]);
    }
}
