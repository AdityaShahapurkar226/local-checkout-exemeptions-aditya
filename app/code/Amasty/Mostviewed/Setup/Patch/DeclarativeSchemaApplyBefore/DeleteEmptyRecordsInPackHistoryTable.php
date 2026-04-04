<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\PackHistoryTable;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DeleteEmptyRecordsInPackHistoryTable implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): DeleteEmptyRecordsInPackHistoryTable
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(PackHistoryTable::TABLE_NAME);

        if ($connection->isTableExists($tableName)) {
            $connection->delete(
                $tableName,
                sprintf(
                    '%s = 0 AND %s = 0',
                    PackHistoryTable::PACK_COLUMN,
                    PackHistoryTable::ORDER_COLUMN
                )
            );
        }

        return $this;
    }
}
