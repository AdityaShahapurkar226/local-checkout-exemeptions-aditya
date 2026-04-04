<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Widget;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Block\Product\BundlePackWrapper;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\ResourceModel\Pack as PackResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;

class Pack extends Template
{
    public const MAIN_PRODUCT_ID = 'main_product_id';
    public const BUNDLE_PACK_ID = 'bundle_pack_id';

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var GroupValidator
     */
    private $groupValidator;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PackResource
     */
    private $packResource;

    public function __construct(
        PackRepositoryInterface $packRepository,
        GroupValidator $groupValidator,
        ProductRepositoryInterface $productRepository,
        PackResource $packResource,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->packRepository = $packRepository;
        $this->groupValidator = $groupValidator;
        $this->productRepository = $productRepository;
        $this->packResource = $packResource;
    }

    /**
     * @return string
     */
    public function getPackHtml(): string
    {
        $html = '';
        if (($bundlePack = $this->getBundlePack()) && ($mainProduct = $this->getMainProduct())) {
            $html .= $this->getBundlePackBlock()->setBundles([$bundlePack])
                ->setProduct($mainProduct)
                ->toHtml();
        }

        return $html;
    }

    private function getBundlePackBlock(): BundlePackWrapper
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock('amrelated.bundle.page.wrapper');
        if (!$block) {
            $block = $layout->createBlock(
                BundlePackWrapper::class,
                'amrelated.bundle.page.wrapper',
                [ 'data' => []]
            );
        }

        return $block;
    }

    private function getBundlePack(): ?PackInterface
    {
        try {
            $bundlePack = $this->packRepository->getById($this->getBundlePackId(), true);
            if (!$this->groupValidator->validate($bundlePack) || !$this->validateBundlePackByStoreId($bundlePack)) {
                $bundlePack = null;
            }
        } catch (NoSuchEntityException $e) {
            $bundlePack = null;
        }

        return $bundlePack;
    }

    private function getBundlePackId(): int
    {
        return (int) $this->getData(self::BUNDLE_PACK_ID);
    }

    private function validateBundlePackByStoreId(PackInterface $bundlePack): bool
    {
        return (bool) array_intersect(
            [$this->_storeManager->getStore()->getId(), Store::DEFAULT_STORE_ID],
            $bundlePack->getExtensionAttributes()->getStores()
        );
    }

    private function getMainProduct(): ?ProductInterface
    {
        try {
            $product = $this->productRepository->getById($this->getMainProductId());
            $product = $product->getStatus() == Status::STATUS_ENABLED ? $product : null;
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        return $product;
    }

    private function getMainProductId(): ?int
    {
        $availableParentIds = $this->packResource->getParentIdsByPack($this->getBundlePackId());
        $mainProductId = $this->getData(self::MAIN_PRODUCT_ID);

        if (!$mainProductId) {
            $mainProductId = $availableParentIds[0] ?? null;
        } elseif (!in_array($mainProductId, $availableParentIds, true)) {
            $mainProductId = null;
        }

        if ($mainProductId !== null) {
            $mainProductId = (int) $mainProductId;
        }

        return $mainProductId;
    }
}
