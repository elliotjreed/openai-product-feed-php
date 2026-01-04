<?php

declare(strict_types=1);

namespace ElliotJReed\Serializer;

use DateTime;
use ElliotJReed\Entity\Product;
use ElliotJReed\ValueObject\GeoAvailability;
use ElliotJReed\ValueObject\GeoPrice;
use ElliotJReed\ValueObject\Shipping;
use Money\Money;
use RuntimeException;

final class CsvSerializer implements SerializerInterface
{
    /** @var string[] */
    private array $headers = [
        'enable_search',
        'enable_checkout',
        'id',
        'gtin',
        'mpn',
        'title',
        'description',
        'link',
        'condition',
        'product_category',
        'brand',
        'material',
        'dimensions',
        'length',
        'width',
        'height',
        'weight',
        'age_group',
        'image_link',
        'additional_image_link',
        'video_link',
        'model_3d_link',
        'price',
        'sale_price',
        'sale_price_effective_date',
        'unit_pricing_measure',
        'pricing_trend',
        'availability',
        'availability_date',
        'inventory_quantity',
        'expiration_date',
        'pickup_method',
        'pickup_sla',
        'item_group_id',
        'item_group_title',
        'color',
        'size',
        'size_system',
        'gender',
        'offer_id',
        'custom_variant1_category',
        'custom_variant1_option',
        'custom_variant2_category',
        'custom_variant2_option',
        'custom_variant3_category',
        'custom_variant3_option',
        'shipping',
        'delivery_estimate',
        'seller_name',
        'seller_url',
        'seller_privacy_policy',
        'seller_tos',
        'return_policy',
        'return_window',
        'popularity_score',
        'return_rate',
        'warning',
        'warning_url',
        'age_restriction',
        'product_review_count',
        'product_review_rating',
        'store_review_count',
        'store_review_rating',
        'q_and_a',
        'raw_review_data',
        'related_product_id',
        'relationship_type',
        'geo_price',
        'geo_availability',
    ];

    public function serialize(Product $product): string
    {
        $handle = \fopen('php://temp', 'r+');
        if (false === $handle) {
            throw new RuntimeException('Failed to open temporary stream');
        }

        \fputcsv($handle, $this->headers, escape: "\\");
        \fputcsv($handle, $this->productToRow($product), escape: "\\");

        \rewind($handle);
        $csv = \stream_get_contents($handle);
        \fclose($handle);

        return \rtrim((string) $csv, "\n");
    }

    /**
     * @param Product[] $products
     */
    public function serializeMany(array $products): string
    {
        $handle = \fopen('php://temp', 'r+');
        if (false === $handle) {
            throw new RuntimeException('Failed to open temporary stream');
        }

        \fputcsv($handle, $this->headers, escape: "\\");
        foreach ($products as $product) {
            \fputcsv($handle, $this->productToRow($product), escape: "\\");
        }

        \rewind($handle);
        $csv = \stream_get_contents($handle);
        \fclose($handle);

        return \rtrim((string) $csv, "\n");
    }

    /**
     * @param Product[] $products
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
     * @return array<int, mixed>
     */
    private function productToRow(Product $product): array
    {
        return [
            $product->enableSearch ? 'true' : 'false',
            $product->enableCheckout ? 'true' : 'false',
            $product->id,
            $product->gtin,
            $product->mpn,
            $product->title,
            $product->description,
            $product->link,
            $product->condition?->value,
            $product->productCategory,
            $product->brand,
            $product->material,
            $this->formatDimensions($product),
            $this->formatDimension($product->length, $product->dimensionUnit),
            $this->formatDimension($product->width, $product->dimensionUnit),
            $this->formatDimension($product->height, $product->dimensionUnit),
            $this->formatDimension($product->weight, $product->weightUnit),
            $product->ageGroup?->value,
            $product->imageLink,
            \implode(',', $product->additionalImageLink),
            $product->videoLink,
            $product->model3dLink,
            $this->formatMoney($product->price),
            $this->formatMoney($product->salePrice),
            $product->salePriceEffectiveDate?->toString(),
            $product->unitPricing?->toString(),
            $product->pricingTrend,
            $product->availability?->value,
            $this->formatDateTime($product->availabilityDate),
            $product->inventoryQuantity,
            $this->formatDateTime($product->expirationDate),
            $product->pickupMethod?->value,
            $product->pickupSla,
            $product->itemGroupId,
            $product->itemGroupTitle,
            $product->color,
            $product->size,
            $product->sizeSystem,
            $product->gender?->value,
            $product->offerId,
            $product->customVariant1Category,
            $product->customVariant1Option,
            $product->customVariant2Category,
            $product->customVariant2Option,
            $product->customVariant3Category,
            $product->customVariant3Option,
            \implode(',', \array_map(fn (Shipping $s) => $s->toString(), $product->shipping)),
            $this->formatDateTime($product->deliveryEstimate),
            $product->sellerName,
            $product->sellerUrl,
            $product->sellerPrivacyPolicy,
            $product->sellerTos,
            $product->returnPolicy,
            $product->returnWindow,
            $product->popularityScore,
            $product->returnRate,
            $product->warning,
            $product->warningUrl,
            $product->ageRestriction,
            $product->productReviewCount,
            $product->productReviewRating,
            $product->storeReviewCount,
            $product->storeReviewRating,
            $product->qAndA,
            $product->rawReviewData,
            \implode(',', $product->relatedProductId),
            $product->relationshipType?->value,
            \implode(',', \array_map(fn (GeoPrice $gp) => $gp->toString(), $product->geoPrice)),
            \implode(',', \array_map(fn (GeoAvailability $ga) => $ga->toString(), $product->geoAvailability)),
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
