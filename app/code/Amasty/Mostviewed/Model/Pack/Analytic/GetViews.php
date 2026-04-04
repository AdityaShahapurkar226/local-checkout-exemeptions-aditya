<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Analytic;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\ResourceModel\Product\LoadViews;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class GetViews
{
    public const LIMIT = 3;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var LoadViews
     */
    private $loadViews;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        LoadViews $loadViews,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider
    ) {
        $this->loadViews = $loadViews;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
    }

    public function execute(int $productId, ?array $storeIds = null, ?int $period = null): array
    {
        if (!isset($this->cache[$productId])) {
            if ($storeIds === null || in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
                $storeIds = array_keys($this->storeManager->getStores());
            }
            if ($period === null) {
                $period = $this->configProvider->getPackAnalyticPeriod();
            }

            $products = array_slice(
                $this->loadViews->execute($productId, $storeIds, $period),
                0,
                self::LIMIT
            );

            $this->cache[$productId] = array_column($products, 'id');
        }

        return $this->cache[$productId];
    }
}
