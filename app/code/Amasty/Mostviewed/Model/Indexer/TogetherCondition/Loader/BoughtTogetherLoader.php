<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer\TogetherCondition\Loader;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\ResourceModel\Group\TogetherCondition\BoughtTogetherIndex;
use Amasty\Mostviewed\Model\ResourceModel\Product\LoadBoughtTogether;
use Amasty\Mostviewed\Model\ResourceModel\Product\LoadChildIds;

class BoughtTogetherLoader implements LoaderInterface
{
    /**
     * @var LoadBoughtTogether
     */
    private $loadBoughtTogether;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoadChildIds
     */
    private $loadChildIds;

    public function __construct(
        LoadBoughtTogether $loadBoughtTogether,
        ConfigProvider $configProvider,
        LoadChildIds $loadChildIds
    ) {
        $this->loadBoughtTogether = $loadBoughtTogether;
        $this->configProvider = $configProvider;
        $this->loadChildIds = $loadChildIds;
    }

    public function execute(int $sourceProductId, int $storeId): array
    {
        $result = $this->loadBoughtTogether->execute(
            $this->loadChildIds->execute($sourceProductId) ?: [$sourceProductId],
            [$storeId],
            $this->configProvider->getGatheredPeriod($storeId),
            $this->configProvider->getOrderStatus()
        );

        return array_map(function (array $productData) use ($sourceProductId, $storeId) {
            $productData[BoughtTogetherIndex::PRODUCT_ID_COLUMN] = $productData['id'];
            $productData[BoughtTogetherIndex::COUNT_COLUMN] = $productData['cnt'];
            $productData[BoughtTogetherIndex::STORE_ID_COLUMN] = $storeId;
            $productData[BoughtTogetherIndex::SOURCE_PRODUCT_ID_COLUMN] = $sourceProductId;
            unset($productData['id']);
            unset($productData['cnt']);

            return $productData;
        }, $result);
    }
}
