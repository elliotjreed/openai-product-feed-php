<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * Aggregated review counts and ratings, for the item itself and for the store selling it.
 *
 * Use the aggregate which matches the item on this row. Where reviews are shared across the variants of a product,
 * repeat the same aggregate on each variant row rather than summing them.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait Reviews
{
    private const float MAXIMUM_RATING = 5.0;
    private const int RATING_DECIMAL_PLACES = 2;

    private ?int $reviewCount = null;
    private ?string $starRating = null;
    private ?int $storeReviewCount = null;
    private ?string $storeStarRating = null;

    /**
     * The number of reviews of this item. Zero means the item has no reviews; leaving it unset means the count is not
     * known.
     *
     * @return $this
     */
    public function setReviewCount(int $reviewCount): static
    {
        $this->reviewCount = Validate::integer('review_count', $reviewCount, 0, \PHP_INT_MAX);

        return $this;
    }

    /**
     * The average rating of this item on a scale of nought to five, rounded to two decimal places.
     *
     * Pair this with a review count greater than zero.
     *
     * @return $this
     */
    public function setStarRating(float | int | string $starRating): static
    {
        $this->starRating = Validate::fixedDecimal(
            'star_rating',
            $starRating,
            0.0,
            self::MAXIMUM_RATING,
            self::RATING_DECIMAL_PLACES
        );

        return $this;
    }

    /**
     * The number of reviews of the store or seller. Requires a configured integration.
     *
     * @return $this
     */
    public function setStoreReviewCount(int $storeReviewCount): static
    {
        $this->storeReviewCount = Validate::integer('store_review_count', $storeReviewCount, 0, \PHP_INT_MAX);

        return $this;
    }

    /**
     * The average rating of the store or seller on a scale of nought to five, rounded to two decimal places.
     *
     * Draw this from the same population as the store review count. Requires a configured integration.
     *
     * @return $this
     */
    public function setStoreStarRating(float | int | string $storeStarRating): static
    {
        $this->storeStarRating = Validate::fixedDecimal(
            'store_star_rating',
            $storeStarRating,
            0.0,
            self::MAXIMUM_RATING,
            self::RATING_DECIMAL_PLACES
        );

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function reviewAttributes(): array
    {
        return Attributes::set([
            'review_count' => $this->reviewCount,
            'star_rating' => $this->starRating,
            'store_review_count' => $this->storeReviewCount,
            'store_star_rating' => $this->storeStarRating
        ]);
    }
}
