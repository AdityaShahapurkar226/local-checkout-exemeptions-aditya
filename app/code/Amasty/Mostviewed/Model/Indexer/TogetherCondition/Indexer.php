<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer\TogetherCondition;

use Amasty\Mostviewed\Model\Indexer\TogetherCondition\Specification\ConditionSpecification;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class Indexer
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetProductIds
     */
    private $getProductIds;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var int
     */
    private $batchSize;

    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        GetProductIds $getProductIds,
        ActiveTableSwitcher $activeTableSwitcher,
        int $batchSize = 1000
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->getProductIds = $getProductIds;
        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->batchSize = $batchSize;
    }

    public function reindex(ConditionSpecification $conditionSpecification): void
    {
        $replicaTableName = $this->resourceConnection->getTableName($conditionSpecification->getReplicaTableName());
        $this->resourceConnection->getConnection()->delete($replicaTableName);

        $insertData = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = (int) $store->getId();
            foreach ($this->getProductIds->execute($storeId) as $sourceProductId) {
                array_push(
                    $insertData,
                    ...$conditionSpecification->getLoader()->execute($sourceProductId, $storeId)
                );
                if (count($insertData) >= $this->batchSize) {
                    $this->insertData($replicaTableName, $insertData);
                    $insertData = [];
                }
            }
        }

        if (!empty($insertData)) {
            $this->insertData($replicaTableName, $insertData);
        }

        $this->activeTableSwitcher->switchTable($this->resourceConnection->getConnection(), [
            $this->resourceConnection->getTableName($conditionSpecification->getTableName())
        ]);
    }

    private function insertData(string $tableName, array $insertData): void
    {
        $this->resourceConnection->getConnection()->insertArray(
            $tableName,
            array_keys(reset($insertData)),
            $insertData
        );
    }
}
