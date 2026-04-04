<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel;

use Amasty\Mostviewed\Model\ResourceModel\Product\LoadBoughtTogether;
use Magento\Framework\App\ObjectManager;

class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    public const QUERY_LIMIT = 1000;

    /**
     * @param int $productId
     * @param int $storeId
     * @param int $period
     * @return array
     */
    public function getProductViewesData($productId, $storeId, $period)
    {
        $tableName = $this->getTable('report_viewed_product_index');
        //TODO code refactoring - move select to resource model
        $connection = $this->getConnection();
        //get visitors who viewed this product
        $visitors = $connection->select()->from(['t2' => $tableName], ['visitor_id'])
            ->where('product_id = ?', $productId)
            ->where('visitor_id IS NOT NULL')
            ->where('store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit(self::QUERY_LIMIT);

        //get customers who viewed this product
        $customers = $connection->select()->from(['t2' => $tableName], ['customer_id'])
            ->where('product_id = ?', $productId)
            ->where('customer_id IS NOT NULL')
            ->where('store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit(self::QUERY_LIMIT);

        $visitors = array_unique($connection->fetchCol($visitors));
        $customers = array_unique($connection->fetchCol($customers));
        $customers = array_diff($customers, $visitors);

        // get related products
        $fields = [
            'id'  => 't.product_id',
            'cnt' => new \Zend_Db_Expr('COUNT(*)'),
        ];
        $productsByVisitor = [];
        if (!empty($visitors)) {
            $productsByVisitor = $connection->select()->from(['t' => $tableName], $fields)
                ->where('t.visitor_id IN (?)', $visitors)
                ->where('t.product_id != ?', $productId)
                ->where('store_id = ?', $storeId)
                ->group('t.product_id')
                ->order('cnt DESC')
                ->limit(self::QUERY_LIMIT);
            $productsByVisitor = $connection->fetchAll($productsByVisitor);
        }

        $productsByCustomer = [];
        if (!empty($customers)) {
            $productsByCustomer = $connection->select()->from(['t' => $tableName], $fields)
                ->where('t.customer_id IN (?)', $customers)
                ->where('t.product_id != ?', $productId)
                ->where('store_id = ?', $storeId)
                ->group('t.product_id')
                ->order('cnt DESC')
                ->limit(self::QUERY_LIMIT);
            $productsByCustomer = $connection->fetchAll($productsByCustomer);
        }
        return array_merge($productsByVisitor, $productsByCustomer);
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @param int $period
     * @param int $orderStatus
     * @return array
     * @deprecared
     */
    public function getBoughtTogetherProductData(array $productIds, $storeId, $period, $orderStatus)
    {
        return ObjectManager::getInstance()->get(LoadBoughtTogether::class)
            ->execute($productIds, [$storeId], (int) $period, [(int) $orderStatus]);
    }
}
