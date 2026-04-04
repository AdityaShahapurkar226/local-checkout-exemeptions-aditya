<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model;

use \Amasty\Mostviewed\Api\Data\PackProductInterface;
use Magento\Framework\Model\AbstractModel;

class PackProduct extends AbstractModel implements PackProductInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\Mostviewed\Model\ResourceModel\PackProduct::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(PackProductInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(PackProductInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPackId()
    {
        return $this->_getData(PackProductInterface::PACK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPackId($packId)
    {
        $this->setData(PackProductInterface::PACK_ID, $packId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(PackProductInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(PackProductInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->_getData(PackProductInterface::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        $this->setData(PackProductInterface::PRODUCT_ID, $productId);

        return $this;
    }
}
