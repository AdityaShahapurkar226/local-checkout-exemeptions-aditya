<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup;

use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Api\Data\ClickInterface;
use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Api\Data\ViewInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $connection->dropTable($setup->getTable(RuleIndex::MAIN_TABLE));
        $connection->dropTable($setup->getTable(Pack::PACK_PRODUCT_TABLE));
        $connection->dropTable($setup->getTable(GroupInterface::TABLE_NAME));
        $connection->dropTable($setup->getTable(Pack::PACK_TABLE));
        $connection->dropTable($setup->getTable(AnalyticInterface::MAIN_TABLE));
        $connection->dropTable($setup->getTable(ClickInterface::MAIN_TABLE));
        $connection->dropTable($setup->getTable(ViewInterface::MAIN_TABLE));
    }
}
