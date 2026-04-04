<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Model;

use \Webkul\MpApi\Api\Data\CreditMemoItemInterface;

class CreditMemoItem extends \Magento\Framework\DataObject implements CreditMemoItemInterface
{
    /**
     * @inheritDoc
     */
    public function getQty()
    {
        return $this->getData(CreditMemoItemInterface::QTY);
    }
   
    /**
     * @inheritDoc
     */
    public function setQty($qty)
    {
        return $this->setData(CreditMemoItemInterface::QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function getBackToStock()
    {
        return $this->getData(CreditMemoItemInterface::BACK_TO_STOCK);
    }
   
    /**
     * @inheritDoc
     */
    public function setBackToStock($qty)
    {
        return $this->setData(CreditMemoItemInterface::BACK_TO_STOCK, $qty);
    }
    /**
     * @inheritDoc
     */
    public function getItemId()
    {
        return $this->getData(CreditMemoItemInterface::ITEM_ID);
    }
   
    /**
     * @inheritDoc
     */
    public function setItemId($itemId)
    {
        return $this->setData(CreditMemoItemInterface::ITEM_ID, $itemId);
    }
}
