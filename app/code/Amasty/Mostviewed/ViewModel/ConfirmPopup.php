<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\ViewModel;

use Amasty\Mostviewed\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ConfirmPopup implements ArgumentInterface
{
    public const IMAGE_TYPE = 'ammostviewed_popup_product_image';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ConfigProvider $configProvider,
        ImageBuilder $imageBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->configProvider = $configProvider;
        $this->imageBuilder = $imageBuilder;
    }

    public function getProductName(int $productId): ?string
    {
        try {
            $product = $this->productRepository->getById($productId);
            $result = $product->getName();
        } catch (NoSuchEntityException $e) {
            $result = null;
        }

        return $result;
    }

    public function getProductImage(int $productId): ?string
    {
        try {
            $product = $this->productRepository->getById($productId);
            $result = $this->getImageHtml($product);
        } catch (NoSuchEntityException $e) {
            $result = null;
        }

        return $result;
    }

    public function getHeader(): string
    {
        return $this->configProvider->getConfirmationTitle();
    }

    private function getImageHtml(ProductInterface $product): string
    {
        $block = $this->imageBuilder->setProduct($product)
            ->setImageId(self::IMAGE_TYPE)
            ->create();

        $html = $block->toHtml();

        return $html;
    }
}
