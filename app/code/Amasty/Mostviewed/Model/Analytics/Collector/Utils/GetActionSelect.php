<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics\Collector\Utils;

use Amasty\Mostviewed\Api\Data\ViewInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class GetActionSelect
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
     * Get analytics action select
     *
     * @param string $type
     * @return Select
     */
    public function execute(string $type): Select
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName('mostviewed_' . $type . '_temp'),
            [
                'counter'    => 'count(*)',
                'version_id' => 'max(id)'
            ]
        )->group(ViewInterface::BLOCK_ID);

        return $select;
    }
}
