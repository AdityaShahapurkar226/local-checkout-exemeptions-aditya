<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Store;

use Amasty\Mostviewed\Model\Pack\Store\Table;
use Magento\Framework\App\ResourceConnection;

class DeleteByPackId
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $packId): void
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(Table::NAME),
            [sprintf('%s = ?', Table::PACK_COLUMN) => $packId]
        );
    }
}
