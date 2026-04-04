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
namespace Bss\ProductStockAlert\Model\ResourceModel;

/**
 * Product alert for back in stock resource model
 */
class Stock extends \Bss\ProductStockAlert\Model\ResourceModel\AbstractResource
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        $connectionName = null
    ) {
        $this->dateFactory = $dateFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_product_alert_stock', 'alert_stock_id');
    }

    /**
     * Before save action
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId() === null
            && $object->getCustomerEmail()
            && $object->getProductId()
            && $object->getWebsiteId()
        ) {
            if ($row = $this->_getAlertRow($object)) {
                $object->addData($row);
                $object->setStatus(0);
            }
        }
        if ($object->getAddDate() === null) {
            $object->setAddDate($this->dateFactory->create()->gmtDate());
            $object->setStatus(0);
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param $setup
     * @param $query
     * @return mixed
     */
    public function executeQuery($setup, $query)
    {
        return  $setup->getConnection()->query($query);
    }

    /**
     * @param $store
     * @return mixed
     */
    public function executeReset($store)
    {
        return $store->reset();
    }

    /**
     * @param int $stockId
     * @param array $data
     * @return $this
     */
    public function updateStock($stockId, $data)
    {
        try {
            $this->beginTransaction();
            $this->getConnection()->update(
                $this->getConnection()->getTableName($this->getMainTable()),
                $data,
                [
                    'alert_stock_id = ?' => $stockId
                ]
            );
            $this->commit();
        } catch (\Exception $e) {
            return $this;
        }
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @param int $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasEmail(
        $customerId,
        $productId,
        $websiteId
    ) {
        try {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable()
            )->where(
                'customer_id = :customer_id'
            )->where(
                'product_id  = :product_id'
            )->where(
                'website_id  = :website_id'
            );
            $bind = [
                ':customer_id' => $customerId,
                ':product_id' => $productId,
                ':website_id' => $websiteId,
            ];
            $data = $this->getConnection()->fetchRow($select, $bind);
            return is_array($data) && isset($data['alert_stock_id']) && count($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $noticeIdsShouldBeRemove
     * @return $this
     */
    public function removeNoiticeIds($noticeIdsShouldBeRemove)
    {
        try {
            $this->beginTransaction();
            $this->getConnection()->delete(
                $this->getConnection()->getTableName($this->getMainTable()),
                [
                    'alert_stock_id IN (?)' => $noticeIdsShouldBeRemove
                ]
            );
            $this->commit();
        } catch (\Exception $e) {
            return $this;
        }
    }

    /**
     * @param array $condition
     * @param array $columns
     * @return array
     */
    public function getStockNotice($condition, $columns = [])
    {
        if (empty($columns)) {
            $columns = ['alert_stock_id'];
        }
        if (!isset($columns['alert_stock_id'])) {
            $columns[] = 'alert_stock_id';
        }
        if (empty($condition)) {
            return [];
        }
        try {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable(),
                $columns
            );
            foreach ($condition as $col => $bind) {
                if (is_array($bind)) {
                    $select->where(
                        $col . ' IN(?)',
                        $bind
                    );
                } else {
                    $select->where(
                        $col . ' =?',
                        $bind
                    );
                }
            }
            $data = $this->getConnection()->fetchAll($select);
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
