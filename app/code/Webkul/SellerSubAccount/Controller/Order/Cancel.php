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

namespace Webkul\SellerSubAccount\Controller\Order;

class Cancel extends \Webkul\Marketplace\Controller\Order\Cancel
{
    /**
     * Constructor function
     *
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper
     * @param \Webkul\Marketplace\Helper\Orders $flag
     * @param \Webkul\Marketplace\Model\Saleslist $collection
     * @param \Webkul\Marketplace\Model\Orders $trackingcoll
     */

    /**
     * Cancel order.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Data::class
        );
        $sellerSubAccountHelper = $this->_objectManager->create(
            \Webkul\SellerSubAccount\Helper\Data::class
        );
        $flag = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Orders::class
        );
        $collection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Saleslist::class
        );
        $trackingcoll = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Orders::class
        );

        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($order = $this->_initOrder()) {
                try {
                    $sellerId = $this->_customerSession->getCustomerId();
                    $subAccount = $sellerSubAccountHelper->getCurrentSubAccount();
                    if ($subAccount->getId()) {
                        $sellerId = $sellerSubAccountHelper->getSubAccountSellerId();
                    }
                    $flag->cancelorder($order, $sellerId);
                    if ($flag) {
                        $this->checkPayment($order, $sellerId, $collection, $trackingcoll);
                    } else {
                        $this->messageManager->addError(
                            __('You are not permitted to cancel this order.')
                        );

                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/history',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('We can\'t send the email order right now. <br>'.$e->getMessage())
                    );
                }

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/view',
                    [
                        'id' => $order->getEntityId(),
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Check Payment
     *
     * @param collection $order
     * @param int $sellerId
     * @param collection $collection
     * @param collection $trackingcoll
     * @return void
     */
    private function checkPayment($order, $sellerId, $collection, $trackingcoll)
    {
        $paidCanceledStatus = \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_CANCELED;
        $paymentCode = '';
        $paymentMethod = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }
        $orderId = $this->getRequest()->getParam('id');
        $collection
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        );
        foreach ($collection as $saleproduct) {
            $saleproduct->setCpprostatus(
                $paidCanceledStatus
            );
            $saleproduct->setPaidStatus(
                $paidCanceledStatus
            );
            if ($paymentCode == 'mpcashondelivery') {
                $saleproduct->setCollectCodStatus(
                    $paidCanceledStatus
                );
                $saleproduct->setAdminPayStatus(
                    $paidCanceledStatus
                );
            }
            $saleproduct->save();
        }
        $trackingcoll
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            $orderId
        )
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        foreach ($trackingcoll as $tracking) {
            $tracking->setTrackingNumber('canceled');
            $tracking->setCarrierName('canceled');
            $tracking->setIsCanceled(1);
            $tracking->save();
        }
        $this->messageManager->addSuccess(
            __('The order has been cancelled.')
        );
    }
}
