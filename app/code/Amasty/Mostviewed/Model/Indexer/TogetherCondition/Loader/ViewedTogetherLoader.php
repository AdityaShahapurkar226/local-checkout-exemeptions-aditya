<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer\TogetherCondition\Loader;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\ResourceModel\Group\TogetherCondition\ViewedTogetherIndex;
use Amasty\Mostviewed\Model\ResourceModel\Product\LoadViews;

class ViewedTogetherLoader implements LoaderInterface
{
    /**
     * @var LoadViews
     */
    private $loadViews;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(LoadViews $loadViews, ConfigProvider $configProvider)
    {
        $this->loadViews = $loadViews;
        $this->configProvider = $configProvider;
    }

    public function execute(int $sourceProductId, int $storeId): array
    {
        $viewsData = $this->loadViews->execute(
            $sourceProductId,
            [$storeId],
            $this->configProvider->getGatheredPeriod($storeId)
        );

        $result = [];
        foreach ($viewsData as $viewData) {
            $productId = $viewData['id'];
            if (!isset($result[$productId])) {
                $result[$productId] = [
                    ViewedTogetherIndex::PRODUCT_ID_COLUMN => $productId,
                    ViewedTogetherIndex::COUNT_COLUMN => $viewData['cnt'],
                    ViewedTogetherIndex::SOURCE_PRODUCT_ID_COLUMN => $sourceProductId,
                    ViewedTogetherIndex::STORE_ID_COLUMN => $storeId
                ];
            } else {
                $result[$productId]['count'] += $viewData['cnt'];
            }
        }

        return $result;
    }
}
