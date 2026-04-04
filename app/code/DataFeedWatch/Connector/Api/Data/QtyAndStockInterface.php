<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */
namespace DataFeedWatch\Connector\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * Interface QtyAndStockInterface
 * @package DataFeedWatch\Connector\Api
 */
interface QtyAndStockInterface extends ExtensibleDataInterface
{
    /**
     * @return float|null
     */
    public function getQty();

    /**
     * @return bool
     */
    public function getManageStock();

    /**
     * @return bool
     */
    public function getIsInStock();

    /**
     * @param float|null $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * @param bool $manageStock
     * @return $this
     */
    public function setManageStock($manageStock);

    /**
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock);

    /**
     * @return \DataFeedWatch\Connector\Api\Data\QtyAndStockExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \DataFeedWatch\Connector\Api\Data\QtyAndStockExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \DataFeedWatch\Connector\Api\Data\QtyAndStockExtensionInterface $extensionAttributes
    );
}