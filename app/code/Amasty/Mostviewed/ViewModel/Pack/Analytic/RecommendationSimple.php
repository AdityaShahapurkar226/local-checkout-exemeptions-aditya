<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\ViewModel\Pack\Analytic;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Controller\Adminhtml\Pack\Edit;
use Amasty\Mostviewed\Model\Backend\Pack\Registry;
use Amasty\Mostviewed\Model\Pack\Analytic\GetOrdersCount;
use Amasty\Mostviewed\Model\Pack\Analytic\GetSales;
use Amasty\Mostviewed\Model\Pack\Analytic\GetViews;
use Amasty\Mostviewed\Model\Pack\Analytic\IsPackBad;
use IntlDateFormatter;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RecommendationSimple implements ArgumentInterface
{
    public const ONE_DAY = 86400;

    /**
     * @var Registry
     */
    private $packRegistry;

    /**
     * @var IsPackBad
     */
    private $isPackBad;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var GetOrdersCount
     */
    private $getOrdersCount;

    /**
     * @var GetViews
     */
    private $getViews;

    /**
     * @var GetSales
     */
    private $getSales;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        Registry $packRegistry,
        IsPackBad $isPackBad,
        TimezoneInterface $timezone,
        GetOrdersCount $getOrdersCount,
        GetViews $getViews,
        GetSales $getSales,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->packRegistry = $packRegistry;
        $this->isPackBad = $isPackBad;
        $this->timezone = $timezone;
        $this->getOrdersCount = $getOrdersCount;
        $this->getViews = $getViews;
        $this->getSales = $getSales;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getPack(): PackInterface
    {
        return $this->packRegistry->get();
    }

    public function isVisible(): bool
    {
        return (bool) $this->getPack()->getPackId();
    }

    public function getFormattedDate(): string
    {
        return $this->timezone->formatDateTime(
            $this->getPack()->getCreatedAt(),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::MEDIUM
        );
    }

    public function getDaysAgo(): int
    {
        $dateDiff = $this->timezone->date()->getTimestamp()
            - $this->timezone->date($this->getPack()->getCreatedAt())->getTimestamp();
        return (int) floor($dateDiff / self::ONE_DAY);
    }

    public function getQtySold(): int
    {
        return $this->getOrdersCount->execute((int) $this->getPack()->getPackId());
    }

    public function isPackBad(): bool
    {
        return $this->isPackBad->execute($this->getPack());
    }

    /**
     * @return ProductInterface[]
     */
    public function getMainProducts(): array
    {
        return $this->getProducts($this->getPack()->getParentIds());
    }

    public function isSalesFound(int $productId): bool
    {
        return !empty($this->getSales->execute($productId, $this->getPack()->getExtensionAttributes()->getStores()));
    }

    public function getSalesProposal(int $productId): string
    {
        $productIds = $this->getSales->execute($productId, $this->getPack()->getExtensionAttributes()->getStores());
        return $this->getProductNames($productIds);
    }

    public function isViewsFound(int $productId): bool
    {
        return !empty($this->getViews->execute($productId, $this->getPack()->getExtensionAttributes()->getStores()));
    }

    public function getViewProposal(int $productId): string
    {
        $productIds = $this->getViews->execute($productId, $this->getPack()->getExtensionAttributes()->getStores());
        return $this->getProductNames($productIds);
    }

    private function getProductNames(array $productIds): string
    {
        $productNames = [];

        foreach ($this->getProducts($productIds) as $product) {
            $productNames[] = $product->getName();
        }

        return implode(', ', $productNames);
    }

    private function getProducts(array $productIds): array
    {
        $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');
        return $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }
}
