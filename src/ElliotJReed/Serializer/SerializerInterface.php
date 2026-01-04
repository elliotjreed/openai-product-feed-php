<?php

declare(strict_types=1);

namespace ElliotJReed\Serializer;

use ElliotJReed\Entity\Product;

interface SerializerInterface
{
    /**
     * Serialize a single product to string format.
     */
    public function serialize(Product $product): string;

    /**
     * Serialize multiple products to string format.
     *
     * @param Product[] $products
     */
    public function serializeMany(array $products): string;

    /**
     * Serialize products to file (with optional gzip compression).
     *
     * @param Product[] $products
     */
    public function serializeToFile(array $products, string $filePath, bool $compress = true): void;
}
