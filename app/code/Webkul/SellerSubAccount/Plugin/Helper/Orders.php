<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Plugin\Helper;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;

class Orders
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var ObjectManagerInterface
     */
    public $_objectManager;

    /**
     * @param HelperData             $helper
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        HelperData $helper,
        ObjectManagerInterface $objectManager
    ) {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
    }

    /**
     * Get Order Info.
     *
     * @param \Webkul\Marketplace\Helper\Orders $helperData
     * @param \Closure $proceed
     * @param int $orderId
     *
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function aroundGetOrderinfo(
        \Webkul\Marketplace\Helper\Orders $helperData,
        \Closure $proceed,
        $orderId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed($orderId);
            return $result;
        }
        $data = [];
        
        $model = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Orders::class
        )->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $this->_helper->getSubAccountSellerId()
        )
        ->addFieldToFilter(
            'order_id',
            $orderId
        );

        $salesOrder = $this->_objectManager->create(
            \Webkul\Marketplace\Model\ResourceModel\Orders\Collection::class
        )->getTable('sales_order');

        $model->getSelect()->join(
            $salesOrder.' as so',
            'main_table.order_id = so.entity_id',
            ["order_approval_status" => "order_approval_status"]
        )->where("so.order_approval_status=1");
        foreach ($model as $tracking) {
            $data = $tracking;
        }

        return $data;
    }
}
