<?php

namespace Ahy\EstateApiIntegration\Model;

use Ahy\EstateApiIntegration\Api\RestrictedProductCheckerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Checks if a product is restricted based on its categories.
 *
 * A product is considered restricted if it belongs to certain predefined restricted categories.
 */
class RestrictedProductChecker implements RestrictedProductCheckerInterface
{
    /**
     * List of restricted category IDs
     */
    private const RESTRICTED_CATEGORY_IDS = [
        921, // shooting sports
    ];

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Determine if the product is restricted by checking its category path against restricted categories.
     *
     * @param int $productId The ID of the product to check
     * @return array Returns ['is_restricted' => bool] indicating restriction status
     */
    public function isRestricted(int $productId): array
    {
        try {
            $product = $this->productRepository->getById($productId);
            $categoryIds = $product->getCategoryIds();

            foreach ($categoryIds as $categoryId) {
                try {
                    $category = $this->categoryRepository->get($categoryId);
                    $pathIds = $category->getPathIds();

                    if (!empty(array_intersect($pathIds, self::RESTRICTED_CATEGORY_IDS))) {
                        return ['is_restricted' => true];
                    }
                } catch (NoSuchEntityException $e) {
                    // Skip missing categories
                    continue;
                }
            }

            return ['is_restricted' => false];
        } catch (NoSuchEntityException $e) {
            return ['is_restricted' => false];
        }
    }
}
