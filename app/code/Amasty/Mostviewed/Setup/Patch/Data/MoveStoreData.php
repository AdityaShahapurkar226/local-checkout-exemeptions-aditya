<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\Data;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Pack\Store\Table;
use Amasty\Mostviewed\Model\ResourceModel\Pack as PackResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MoveStoreData implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [
            ConvertOldSettings::class
        ];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return MoveStoreData
     */
    public function apply()
    {
        $oldData = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(PackResource::PACK_TABLE),
            [PackInterface::PACK_ID, PackInterface::STORE_ID]
        );
        $insertQuery = $this->resourceConnection->getConnection()->insertFromSelect(
            $oldData,
            $this->resourceConnection->getTableName(Table::NAME),
            [Table::PACK_COLUMN, Table::STORE_COLUMN]
        );
        $this->resourceConnection->getConnection()->query($insertQuery);

        return $this;
    }
}
