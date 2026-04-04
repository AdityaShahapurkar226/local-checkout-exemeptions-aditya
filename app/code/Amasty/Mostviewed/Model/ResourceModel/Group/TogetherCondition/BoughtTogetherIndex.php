<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Group\TogetherCondition;

use Magento\Framework\App\ResourceConnection;

class BoughtTogetherIndex
{
    public const TABLE_NAME = 'amasty_mostviewed_product_bought_index';
    public const REPLICA_TABLE_NAME = 'amasty_mostviewed_product_bought_index_replica';

    public const SOURCE_PRODUCT_ID_COLUMN = 'source_product_id';
    public const PRODUCT_ID_COLUMN = 'product_id';
    public const STORE_ID_COLUMN = 'store_id';
    public const COUNT_COLUMN = 'count';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function loadData(int $sourceProductId, int $storeId): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(self::TABLE_NAME),
            [self::PRODUCT_ID_COLUMN, self::COUNT_COLUMN]
        )->where(
            self::SOURCE_PRODUCT_ID_COLUMN . '= ?',
            $sourceProductId
        )->where(
            self::STORE_ID_COLUMN . ' = ?',
            $storeId
        );
        return $this->resourceConnection->getConnection()->fetchAll($select);
    }

    public function isIndexNotEmpty(): bool
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(self::TABLE_NAME),
            [self::PRODUCT_ID_COLUMN]
        )->limit(1);
        return (bool) $this->resourceConnection->getConnection()->fetchOne($select);
    }
}
