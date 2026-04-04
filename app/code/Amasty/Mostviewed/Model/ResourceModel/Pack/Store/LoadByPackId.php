<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Store;

use Amasty\Mostviewed\Model\Pack\Store\Table as StoreTable;
use Magento\Framework\App\ResourceConnection;

class LoadByPackId
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $packId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName(StoreTable::NAME),
            [StoreTable::STORE_COLUMN]
        )->where(sprintf('%s = ?', StoreTable::PACK_COLUMN), $packId);

        return $connection->fetchCol($select);
    }
}
