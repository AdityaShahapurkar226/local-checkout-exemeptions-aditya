<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Order\Creditmemo;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Model\Url as CustomerUrl;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\SaleslistFactory;

/**
 * Webkul Marketplace Order Creditmemo Create Controller.
 */
class Create extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Initialize order model instance.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        try {
            $order = $this->_orderRepository->get($id);
            $tracking = $this->orderHelper->getOrderinfo($id);
            if ($tracking) {
                if ($tracking->getOrderId() == $id) {
                    if (!$id) {
                        $this->messageManager->addError(
                            __('This order no longer exists.')
                        );
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                        return false;
                    }
                } else {
                    $this->messageManager->addError(
                        __('You are not authorize to manage this order.')
                    );
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                    return false;
                }
            } else {
                $this->messageManager->addError(
                    __('You are not authorize to manage this order.')
                );
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(
                __('This order no longer exists.')
            );
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            return false;
        } catch (InputException $e) {
            $this->messageManager->addError(
                __('This order no longer exists.')
            );
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);

        return $order;
    }

    /**
     * Creditmemo Create Action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($order = $this->_initOrder()) {
                $paymentCode = '';
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                }
                if ($paymentCode == 'mpcashondelivery') {
                    $adminPayStatus = $this->getAdminPayStatus($order->getId());
                    if ($adminPayStatus) {
                        $this->messageManager->addError(
                            __('You can not create credit memo for this order.')
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            'marketplace/order/view',
                            [
                                'id' => $order->getId(),
                                '_secure' => $this->getRequest()->isSecure(),
                            ]
                        );
                    }
                }

                // create creditmemo totals
                $data = [];
                $invoice = $this->_initInvoice();

                if (!$order->canCreditmemo()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('We can\'t create credit memo for the order.')
                    );
                }

                $itemsData = $this->getCurrentSellerItemsRefundData(
                    $order->getId(),
                    $helper->getCustomerId(),
                    $paymentCode
                );
                $items = $itemsData['items'];
                $currencyRate = $itemsData['currencyRate'];
                $codCharges = $itemsData['codCharges'];
                $tax = $itemsData['tax'];
                $couponAmount = $itemsData['couponAmount'];
                $shippingAmount = $itemsData['shippingAmount'];

                if ($invoice) {
                    $creditmemo = $this->_creditmemoFactory->createByInvoice($invoice, $items);
                } else {
                    $creditmemo = $this->_creditmemoFactory->createByOrder($order, $items);
                }

                $currentCouponAmount = $currencyRate * $couponAmount;
                $currentShippingAmount = $currencyRate * $shippingAmount;
                $currentTaxAmount = $currencyRate * $tax;
                $currentCodcharges = $currencyRate * $codCharges;
                $creditmemo->setBaseDiscountAmount(-$couponAmount);
                $creditmemo->setDiscountAmount(-$currentCouponAmount);
                $creditmemo->setShippingAmount($currentShippingAmount);
                $creditmemo->setBaseShippingInclTax($shippingAmount);
                $creditmemo->setBaseShippingAmount($shippingAmount);
                if ($paymentCode == 'mpcashondelivery') {
                    $creditmemo->setMpcashondelivery($currentCodcharges);
                    $creditmemo->setBaseMpcashondelivery($codCharges);
                }
                $creditmemo->setGrandTotal(
                    $creditmemo->getSubtotal() +
                    $currentShippingAmount +
                    $currentCodcharges +
                    $currentTaxAmount -
                    $currentCouponAmount
                );
                $creditmemo->setBaseGrandTotal(
                    $creditmemo->getBaseSubtotal() +
                    $shippingAmount +
                    $codCharges +
                    $tax -
                    $couponAmount
                );
                $this->_coreRegistry->register('current_creditmemo', $creditmemo);
                
                $resultPage = $this->_resultPageFactory->create();
                if ($helper->getIsSeparatePanel()) {
                    $resultPage->addHandle('marketplace_layout2_order_creditmemo_create');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('New Credit Memo for Order #%1', $order->getRealOrderId())
                );

                return $resultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    [
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
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
     * Get Current Seller items to create creditmemo
     *
     * @param int $orderId
     * @param int $sellerId
     * @param string $paymentCode
     * @return array
     */
    public function getCurrentSellerItemsRefundData($orderId, $sellerId, $paymentCode)
    {
        // calculate charges for ordered items for current seller
        $codCharges = 0;
        $couponAmount = 0;
        $tax = 0;
        $currencyRate = 1;
        $sellerItemsToRefund = [];
        $collection = $this->saleslistFactory->create()
                ->getCollection()
                ->addFieldToFilter(
                    'main_table.order_id',
                    ['eq' => $orderId]
                )
                ->addFieldToFilter(
                    'main_table.seller_id',
                    ['eq' => $sellerId]
                )
                ->getSellerOrderCollection();
        foreach ($collection as $saleproduct) {
            $orderItemId = $saleproduct->getOrderItemId();
            $orderedQty = $saleproduct->getQtyOrdered();
            $orderedInvoicedQty = $saleproduct->getQtyInvoiced();
            $qtyToRefund = $orderedInvoicedQty - $saleproduct->getQtyRefunded();
            $sellerItemsToRefund[$orderItemId] = $qtyToRefund;
            $currencyRate = $saleproduct->getCurrencyRate();
            if ($paymentCode == 'mpcashondelivery') {
                $appliedCodCharges = $saleproduct->getCodCharges() / $orderedQty;
                $codCharges = $codCharges + ($appliedCodCharges * $qtyToRefund);
            }
            $appliedTax = $saleproduct->getTotalTax() / $orderedQty;
            $tax = $tax + ($appliedTax * $qtyToRefund);
            if ($saleproduct->getIsCoupon()) {
                $appliedAmount = $saleproduct->getAppliedCouponAmount() / $orderedQty;
                $couponAmount = $couponAmount + ($appliedAmount * $qtyToRefund);
            }
        }

        // calculate shipment for the seller order if applied
        $shippingAmount = 0;
        $marketplaceOrder = $this->orderHelper->getOrderinfo($orderId);
        $savedInvoiceId = $marketplaceOrder->getInvoiceId();
        if (!$savedInvoiceId) {
            $trackingsdata = $this->mpOrdersModel->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($trackingsdata as $tracking) {
                $shippingAmount = $tracking->getShippingCharges();
            }
        }
        $data = [
            'items' => $sellerItemsToRefund,
            'currencyRate' => $currencyRate,
            'codCharges' => $codCharges,
            'tax' => $tax,
            'couponAmount' => $couponAmount,
            'shippingAmount' => $shippingAmount
        ];
        return $data;
    }

    /**
     * Get Admin paystatus
     *
     * @param int $orderId
     * @return int
     */
    public function getAdminPayStatus($orderId)
    {
        $adminPayStatus = 0;
        $collection = $this->saleslistFactory->create()
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            $orderId
        )
        ->addFieldToFilter(
            'seller_id',
            $this->getCustomerId()
        );
        foreach ($collection as $saleproduct) {
            $adminPayStatus = $saleproduct->getAdminPayStatus();
        }
        return $adminPayStatus;
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
}
