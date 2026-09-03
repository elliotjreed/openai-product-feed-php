<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * Who is selling the item, so that the offer can be attributed correctly.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait MerchantInformation
{
    private ?string $sellerName = null;
    private ?string $sellerUrl = null;
    private ?string $marketplaceSeller = null;
    private ?string $storeCountry = null;

    /**
     * Required. The name of the seller supplying this offer, as a real name rather than a placeholder.
     *
     * On a third-party marketplace offer this names the individual seller, while the marketplace itself is named by
     * the marketplace seller attribute.
     *
     * @return $this
     */
    public function setSellerName(string $sellerName): static
    {
        $this->sellerName = Validate::text('seller_name', $sellerName);

        return $this;
    }

    /**
     * The seller's storefront or profile page.
     *
     * @return $this
     */
    public function setSellerUrl(string $sellerUrl): static
    {
        $this->sellerUrl = Validate::url('seller_url', $sellerUrl);

        return $this;
    }

    /**
     * The marketplace checkout takes place on, for a third-party marketplace offer. Requires a configured integration.
     *
     * @return $this
     */
    public function setMarketplaceSeller(string $marketplaceSeller): static
    {
        $this->marketplaceSeller = Validate::text('marketplace_seller', $marketplaceSeller);

        return $this;
    }

    /**
     * The country the seller's store operates from, as an ISO 3166-1 alpha-2 code. Requires a configured integration.
     *
     * @return $this
     */
    public function setStoreCountry(string $storeCountry): static
    {
        $this->storeCountry = Validate::country('store_country', $storeCountry);

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function merchantInformationAttributes(): array
    {
        return Attributes::set([
            'seller_name' => $this->sellerName,
            'seller_url' => $this->sellerUrl,
            'marketplace_seller' => $this->marketplaceSeller,
            'store_country' => $this->storeCountry
        ]);
    }
}
