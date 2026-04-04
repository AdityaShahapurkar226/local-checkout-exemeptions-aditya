<?php 

namespace Ahy\EstateApiIntegration\Api;

interface RestrictedProductCheckerInterface
{
    /**
     * @param int $productId
     * @return array
     */
    public function isRestricted(int $productId): array;
}
