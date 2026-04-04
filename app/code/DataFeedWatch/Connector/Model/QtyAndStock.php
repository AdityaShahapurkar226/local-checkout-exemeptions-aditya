<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Model;

use DataFeedWatch\Connector\Api\Data\QtyAndStockInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class QtyAndStock
 * @package DataFeedWatch\Connector\Model
 */
class QtyAndStock extends AbstractExtensibleObject implements QtyAndStockInterface
{
    const KEY_QTY = 'qty';
    const KEY_MANAGE_STOCK = 'manage_stock';
    const KEY_IS_IN_STOCK = 'is_in_stock';

    public function getQty()
    {
        return $this->_get(self::KEY_QTY);
    }

    public function getManageStock()
    {
        return $this->_get(self::KEY_MANAGE_STOCK);
    }

    public function getIsInStock()
    {
        return $this->_get(self::KEY_IS_IN_STOCK);
    }

    /**
     * @return \DataFeedWatch\Connector\Api\Data\QtyAndStockExtensionInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @param \DataFeedWatch\Connector\Api\Data\QtyAndStockExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\DataFeedWatch\Connector\Api\Data\QtyAndStockExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param float|null $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->setData(self::KEY_QTY, $qty);
        return $this;
    }

    /**
     * @param bool $manageStock
     * @return $this
     */
    public function setManageStock($manageStock)
    {
        $this->setData(self::KEY_MANAGE_STOCK, $manageStock);
        return $this;
    }

    /**
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock)
    {
        $this->setData(self::KEY_IS_IN_STOCK, $isInStock);
        return $this;
    }
}