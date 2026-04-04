<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics;

use Magento\Framework\Model\AbstractModel;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic as AnalyticResource;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;

class Analytic extends AbstractModel implements AnalyticInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(AnalyticResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->_getData(AnalyticInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(AnalyticInterface::TYPE, $type);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCounter()
    {
        return $this->_getData(AnalyticInterface::COUNTER);
    }

    /**
     * @inheritdoc
     */
    public function setCounter($counter)
    {
        $this->setData(AnalyticInterface::COUNTER, $counter);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBlockId()
    {
        return $this->_getData(AnalyticInterface::BLOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setBlockId($blockId)
    {
        $this->setData(AnalyticInterface::BLOCK_ID, $blockId);

        return $this;
    }

    /**
     * @return int
     */
    public function getVersionId()
    {
        return $this->_getData(AnalyticInterface::VERSION_ID);
    }

    /**
     * @param int $versionId
     *
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     */
    public function setVersionId($versionId)
    {
        $this->setData(AnalyticInterface::VERSION_ID, $versionId);

        return $this;
    }
}
