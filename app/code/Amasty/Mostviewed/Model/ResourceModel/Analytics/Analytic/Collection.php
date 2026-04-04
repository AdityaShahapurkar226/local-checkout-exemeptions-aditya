<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Model\Analytics\Analytic;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic as AnalyticResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = AnalyticInterface::ID;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Analytic::class, AnalyticResource::class);
    }

    /**
     * @param int $blockId
     *
     * @return void
     */
    public function deleteByBlockId(int $blockId)
    {
        $this->getConnection()->delete($this->getMainTable(), [AnalyticInterface::BLOCK_ID . ' = ?' => $blockId]);
    }
}
