<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Analytics;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;

class Analytic extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AnalyticInterface::MAIN_TABLE, AnalyticInterface::ID);
    }
}
