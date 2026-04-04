<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Zend_Db_Expr;

class LoadViews
{
    public const QUERY_LIMIT = 1000;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $productId, array $storeIds, int $period): array
    {
        $tableName = $this->resourceConnection->getTableName('report_viewed_product_index');
        $connection = $this->resourceConnection->getConnection();

        //get visitors who viewed this product
        $visitors = $connection->select()->from(['t2' => $tableName], ['visitor_id'])
            ->where('product_id = ?', $productId)
            ->where('visitor_id IS NOT NULL')
            ->where('store_id IN (?)', $storeIds)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit(self::QUERY_LIMIT);

        //get customers who viewed this product
        $customers = $connection->select()->from(['t2' => $tableName], ['customer_id'])
            ->where('product_id = ?', $productId)
            ->where('customer_id IS NOT NULL')
            ->where('store_id IN (?)', $storeIds)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit(self::QUERY_LIMIT);

        $visitors = array_unique($connection->fetchCol($visitors));
        $customers = array_unique($connection->fetchCol($customers));
        $customers = array_diff($customers, $visitors);

        // get related products
        $fields = [
            'id'  => 't.product_id',
            'cnt' => new Zend_Db_Expr('COUNT(*)'),
        ];
        $productsByVisitor = [];
        if (!empty($visitors)) {
            $productsByVisitor = $connection->select()->from(['t' => $tableName], $fields)
                ->where('t.visitor_id IN (?)', $visitors)
                ->where('t.product_id != ?', $productId)
                ->where('store_id IN (?)', $storeIds)
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
                ->where('store_id IN (?)', $storeIds)
                ->group('t.product_id')
                ->order('cnt DESC')
                ->limit(self::QUERY_LIMIT);
            $productsByCustomer = $connection->fetchAll($productsByCustomer);
        }

        return array_merge($productsByVisitor, $productsByCustomer);
    }
}
