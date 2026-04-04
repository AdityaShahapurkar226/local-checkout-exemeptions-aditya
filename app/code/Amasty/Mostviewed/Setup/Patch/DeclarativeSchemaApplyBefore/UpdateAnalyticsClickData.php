<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Api\Data\ClickInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateAnalyticsClickData implements DataPatchInterface
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
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return UpdateAnalyticsClickData
     */
    public function apply()
    {
        $connection = $this->resourceConnection->getConnection();

        $connection->startSetup();
        $clicksTableName = $this->resourceConnection->getTableName(ClickInterface::MAIN_TABLE);
        if ($connection->isTableExists($clicksTableName)
            && !$connection->tableColumnExists($clicksTableName, ClickInterface::CLICK_TYPE)
        ) {
            $analyticsTableName = $this->resourceConnection->getTableName(AnalyticInterface::MAIN_TABLE);
            $connection->update(
                $analyticsTableName,
                [AnalyticInterface::TYPE => 'click_block'],
                sprintf('%s = "click"', AnalyticInterface::TYPE)
            );

        }
        $connection->endSetup();

        return $this;
    }
}
