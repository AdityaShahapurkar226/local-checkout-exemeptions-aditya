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
namespace Webkul\Marketplace\Controller\Order\Invoice;

/**
 * Webkul Marketplace Order Invoice Create Controller.
 */
class Create extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Invoice Create Action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $sellerId = $helper->getCustomerId();
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($order = $this->_initOrder()) {
                $orderId = $this->getRequest()->getParam('id');

                $paymentCode = '';
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                }

                $itemsData = $this->getCurrentSellerItemsData(
                    $orderId,
                    $sellerId,
                    $paymentCode
                );

                $items = $itemsData['items'];
                $currencyRate = $itemsData['currencyRate'];
                $codCharges = $itemsData['codCharges'];
                $tax = $itemsData['tax'];
                $couponAmount = $itemsData['couponAmount'];
                $shippingAmount = $itemsData['shippingAmount'];

                $invoice = $this->invoiceService->prepareInvoice($order, $items);

                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("The invoice can't be created without products. Add products and try again.")
                    );
                }

                $currentCouponAmount = $currencyRate * $couponAmount;
                $currentShippingAmount = $currencyRate * $shippingAmount;
                $currentTaxAmount = $currencyRate * $tax;
                $currentCodcharges = $currencyRate * $codCharges;
                $invoice->setBaseDiscountAmount(-$couponAmount);
                $invoice->setDiscountAmount(-$currentCouponAmount);
                $invoice->setShippingAmount($currentShippingAmount);
                $invoice->setBaseShippingInclTax($shippingAmount);
                $invoice->setBaseShippingAmount($shippingAmount);
                if ($paymentCode == 'mpcashondelivery') {
                    $invoice->setMpcashondelivery($currentCodcharges);
                    $invoice->setBaseMpcashondelivery($codCharges);
                }
                $invoice->setGrandTotal(
                    $invoice->getSubtotal() +
                    $currentShippingAmount +
                    $currentCodcharges +
                    $currentTaxAmount -
                    $currentCouponAmount
                );
                $invoice->setBaseGrandTotal(
                    $invoice->getBaseSubtotal() +
                    $shippingAmount +
                    $codCharges +
                    $tax -
                    $couponAmount
                );
                $this->_coreRegistry->register('current_invoice', $invoice);

                $resultPage = $this->_resultPageFactory->create();
                if ($helper->getIsSeparatePanel()) {
                    $resultPage->addHandle('marketplace_layout2_order_invoice_create');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('New invoice for Order #%1', $order->getRealOrderId())
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
     * Get Current Seller items to create invoice
     *
     * @param int $orderId
     * @param int $sellerId
     * @param string $paymentCode
     * @return array
     */
    public function getCurrentSellerItemsData($orderId, $sellerId, $paymentCode)
    {
        // calculate charges for ordered items for current seller
        $codCharges = 0;
        $couponAmount = 0;
        $tax = 0;
        $currencyRate = 1;
        $sellerItemsToInvoice = [];
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
            $qtyToInvoice = $orderedQty - $saleproduct->getQtyInvoiced();
            $sellerItemsToInvoice[$orderItemId] = $qtyToInvoice;
            $currencyRate = $saleproduct->getCurrencyRate();
            if ($paymentCode == 'mpcashondelivery') {
                $appliedCodCharges = $saleproduct->getCodCharges() / $orderedQty;
                $codCharges = $codCharges + ($appliedCodCharges * $qtyToInvoice);
            }
            $appliedTax = $saleproduct->getTotalTax() / $orderedQty;
            $tax = $tax + ($appliedTax * $qtyToInvoice);
            if ($saleproduct->getIsCoupon()) {
                $appliedAmount = $saleproduct->getAppliedCouponAmount() / $orderedQty;
                $couponAmount = $couponAmount + ($appliedAmount * $qtyToInvoice);
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
            'items' => $sellerItemsToInvoice,
            'currencyRate' => $currencyRate,
            'codCharges' => $codCharges,
            'tax' => $tax,
            'couponAmount' => $couponAmount,
            'shippingAmount' => $shippingAmount
        ];
        return $data;
    }
}
