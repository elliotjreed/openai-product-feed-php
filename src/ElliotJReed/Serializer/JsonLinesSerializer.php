<?php

declare(strict_types=1);

namespace ElliotJReed\Serializer;

use DateTime;
use ElliotJReed\Entity\Product;
use ElliotJReed\ValueObject\GeoAvailability;
use ElliotJReed\ValueObject\GeoPrice;
use ElliotJReed\ValueObject\Shipping;
use JsonException;
use Money\Money;
use RuntimeException;

final class JsonLinesSerializer implements SerializerInterface
{
    /**
     * @throws JsonException
     */
    public function serialize(Product $product): string
    {
        return \json_encode($this->productToArray($product), \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param Product[] $products
     *
     * @throws JsonException
     */
    public function serializeMany(array $products): string
    {
        $lines = [];
        foreach ($products as $product) {
            $lines[] = $this->serialize($product);
        }

        return \implode("\n", $lines);
    }

    /**
     * @param Product[] $products
     *
     * @throws JsonException
     */
    public function serializeToFile(array $products, string $filePath, bool $compress = true): void
    {
        $content = $this->serializeMany($products);

        if ($compress) {
            $handle = \gzopen($filePath, 'wb');
            if (false === $handle) {
                throw new RuntimeException(\sprintf('Failed to open file for writing: %s', $filePath));
            }
            \gzwrite($handle, $content);
            \gzclose($handle);
        } else {
            $written = \file_put_contents($filePath, $content);
            if (false === $written) {
                throw new RuntimeException(\sprintf('Failed to write to file: %s', $filePath));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function productToArray(Product $product): array
    {
        return [
            'enable_search' => $product->enableSearch,
            'enable_checkout' => $product->enableCheckout,
            'id' => $product->id,
            'gtin' => $product->gtin,
            'mpn' => $product->mpn,
            'title' => $product->title,
            'description' => $product->description,
            'link' => $product->link,
            'condition' => $product->condition?->value,
            'product_category' => $product->productCategory,
            'brand' => $product->brand,
            'material' => $product->material,
            'dimensions' => $this->formatDimensions($product),
            'length' => $this->formatDimension($product->length, $product->dimensionUnit),
            'width' => $this->formatDimension($product->width, $product->dimensionUnit),
            'height' => $this->formatDimension($product->height, $product->dimensionUnit),
            'weight' => $this->formatDimension($product->weight, $product->weightUnit),
            'age_group' => $product->ageGroup?->value,
            'image_link' => $product->imageLink,
            'additional_image_link' => $product->additionalImageLink,
            'video_link' => $product->videoLink,
            'model_3d_link' => $product->model3dLink,
            'price' => $this->formatMoney($product->price),
            'sale_price' => $this->formatMoney($product->salePrice),
            'sale_price_effective_date' => $product->salePriceEffectiveDate?->toString(),
            'unit_pricing_measure' => $product->unitPricing?->toString(),
            'pricing_trend' => $product->pricingTrend,
            'availability' => $product->availability?->value,
            'availability_date' => $this->formatDateTime($product->availabilityDate),
            'inventory_quantity' => $product->inventoryQuantity,
            'expiration_date' => $this->formatDateTime($product->expirationDate),
            'pickup_method' => $product->pickupMethod?->value,
            'pickup_sla' => $product->pickupSla,
            'item_group_id' => $product->itemGroupId,
            'item_group_title' => $product->itemGroupTitle,
            'color' => $product->color,
            'size' => $product->size,
            'size_system' => $product->sizeSystem,
            'gender' => $product->gender?->value,
            'offer_id' => $product->offerId,
            'custom_variant1_category' => $product->customVariant1Category,
            'custom_variant1_option' => $product->customVariant1Option,
            'custom_variant2_category' => $product->customVariant2Category,
            'custom_variant2_option' => $product->customVariant2Option,
            'custom_variant3_category' => $product->customVariant3Category,
            'custom_variant3_option' => $product->customVariant3Option,
            'shipping' => \array_map(fn (Shipping $s): string => $s->toString(), $product->shipping),
            'delivery_estimate' => $this->formatDateTime($product->deliveryEstimate),
            'seller_name' => $product->sellerName,
            'seller_url' => $product->sellerUrl,
            'seller_privacy_policy' => $product->sellerPrivacyPolicy,
            'seller_tos' => $product->sellerTos,
            'return_policy' => $product->returnPolicy,
            'return_window' => $product->returnWindow,
            'popularity_score' => $product->popularityScore,
            'return_rate' => $product->returnRate,
            'warning' => $product->warning,
            'warning_url' => $product->warningUrl,
            'age_restriction' => $product->ageRestriction,
            'product_review_count' => $product->productReviewCount,
            'product_review_rating' => $product->productReviewRating,
            'store_review_count' => $product->storeReviewCount,
            'store_review_rating' => $product->storeReviewRating,
            'q_and_a' => $product->qAndA,
            'raw_review_data' => $product->rawReviewData,
            'related_product_id' => $product->relatedProductId,
            'relationship_type' => $product->relationshipType?->value,
            'geo_price' => \array_map(fn (GeoPrice $gp): string => $gp->toString(), $product->geoPrice),
            'geo_availability' => \array_map(fn (GeoAvailability $ga): string => $ga->toString(), $product->geoAvailability),
        ];
    }

    private function formatMoney(?Money $money): ?string
    {
        if (null === $money) {
            return null;
        }

        $amount = $money->getAmount();
        $currency = $money->getCurrency()->getCode();
        $formattedAmount = \number_format((float) $amount / 100, 2, '.', '');

        return \sprintf('%s %s', $formattedAmount, $currency);
    }

    private function formatDateTime(?DateTime $dateTime): ?string
    {
        return $dateTime?->format('Y-m-d');
    }

    private function formatDimension(?float $value, ?string $unit): ?string
    {
        if (null === $value || null === $unit) {
            return null;
        }

        return \sprintf('%s %s', $value, $unit);
    }

    private function formatDimensions(Product $product): ?string
    {
        if (null !== $product->dimensions) {
            return $product->dimensions->toString();
        }

        if (null !== $product->length && null !== $product->width && null !== $product->height && null !== $product->dimensionUnit) {
            return \sprintf(
                '%sx%sx%s %s',
                $product->length,
                $product->width,
                $product->height,
                $product->dimensionUnit
            );
        }

        return null;
    }
}
