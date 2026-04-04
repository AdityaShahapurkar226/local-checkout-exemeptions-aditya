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
namespace Webkul\MpApi\Model\ResourceModel\Order\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Webkul\Marketplace\Api\Data\OrdersInterfaceFactory;
use Magento\Sales\Model\Order;

class Authorization extends \Magento\Sales\Model\ResourceModel\Order\Plugin\Authorization
{
    /**
     * @param UserContextInterface $userContext
     * @param OrdersInterfaceFactory $mpOrderFactory
     */
    public function __construct(
        UserContextInterface $userContext,
        OrdersInterfaceFactory $mpOrderFactory
    ) {
        $this->mpOrderFactory = $mpOrderFactory;
        parent::__construct($userContext);
    }

    /**
     * @inheritDoc
     */
    protected function isAllowed(Order $order)
    {
        $flag = $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
        ? $order->getCustomerId() == $this->userContext->getUserId()
        : true;
        if (!$flag) {
            $mpOrderColl = $this->mpOrderFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("order_id", $order->getEntityId());
            foreach ($mpOrderColl as $order) {
                if ($order->getSellerId() == $this->userContext->getUserId()) {
                    return true;
                }
            }
        }
        return $flag;
    }
}
