<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\ResourceModel\Stock;

use Magento\Framework\Api;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'alert_stock_id';

    /**
     * Define stock collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\ProductStockAlert\Model\Stock::class,
            \Bss\ProductStockAlert\Model\ResourceModel\Stock::class
        );
    }

    /**
     * @return $this
     */
    public function joinRelationTable()
    {
        $this->getSelect()->joinLeft(
            ['relation' => $this->getTable('catalog_product_relation')],
            'main_table.product_id = relation.parent_id',
            ['child_id']
        )->joinInner(
            ['website' => $this->getTable('store_website')],
            'main_table.website_id = website.website_id',
            ['website_code' => 'code']
        );
        return $this;
    }
}
