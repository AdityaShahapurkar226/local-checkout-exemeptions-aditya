<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack;
use Magento\Framework\App\ResourceConnection;

class LoadPacksOptions
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(bool $onlyEnabled = false): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(Pack::PACK_TABLE),
            [PackInterface::PACK_ID, PackInterface::NAME]
        )->order(PackInterface::NAME);
        if ($onlyEnabled) {
            $select->where(PackInterface::STATUS, 1);
        }

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }
}
