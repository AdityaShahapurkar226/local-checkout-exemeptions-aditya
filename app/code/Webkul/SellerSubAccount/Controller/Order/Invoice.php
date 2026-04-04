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

/**
 * Webkul Marketplace Order Invoice Controller.
 */
class Invoice extends \Webkul\Marketplace\Controller\Order\Invoice
{
    /**
     * Do Invoice Execution
     *
     * @param Order $order
     * @return void
     */
    public function doInvoiceExecution($order)
    {
        $helper = $this->_objectManager->create(
            \Webkul\SellerSubAccount\Helper\Data::class
        );
        try {
                $sellerId = "";
                $customerId = $this->_customerSession->getCustomerId();
                $subAccount = $helper->getCurrentSubAccount();
            if (!$subAccount->getId()) {
                $sellerId = $helper->getCustomerId();
            } else {
                $sellerId = $helper->getSubAccountSellerId();
            }
            $this->checkInvoice($customerId, $order, $sellerId);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t save the invoice right now.')
            );
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Check Invoice
     *
     * @param int $customerId
     * @param int $order
     * @param int $sellerId
     * @return void
     */
    private function checkInvoice($customerId, $order = null, $sellerId = null)
    {
        $marketplaceOrder = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Orders::class
        );
        $invoice = $this->_objectManager->create(
            \Magento\Sales\Model\Service\InvoiceService::class
        );

        $orderId = $order->getId();
        if ($order->canUnhold()) {
            $this->messageManager->addError(
                __('Can not create invoice as order is in HOLD state')
            );
        } else {
            $data = [];
            $data['send_email'] = 1;
            $marketplaceOrder = $marketplaceOrder->getOrderinfo($orderId);
            $invoiceId = $marketplaceOrder->getInvoiceId();
            if (!$invoiceId) {
                $items = [];
                $itemsarray = [];
                $shippingAmount = 0;
                $couponAmount = 0;
                $codcharges = 0;
                $paymentCode = '';
                $paymentMethod = '';
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                }

                $collection = $this->_objectManager->create(
                    \Webkul\Marketplace\Model\Orders::class
                )->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    $orderId
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['in'=> [$customerId, $sellerId]]
                );
                
                foreach ($collection as $tracking) {
                    $shippingAmount = $tracking->getShippingCharges();
                    $couponAmount = $tracking->getCouponAmount();
                    if ($paymentCode == 'mpcashondelivery') {
                        $codcharges = $tracking->getCodCharges();
                    }
                }
                $codCharges = 0;
                $tax = 0;
                $collection = $this->_objectManager->create(
                    \Webkul\Marketplace\Model\Saleslist::class
                )->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['eq' => $orderId]
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['in'=> [$customerId, $sellerId]]
                );
                foreach ($collection as $saleproduct) {
                    if ($paymentCode == 'mpcashondelivery') {
                        $codCharges = $codCharges + $saleproduct->getCodCharges();
                    }
                    $tax = $tax + $saleproduct->getTotalTax();
                    array_push($items, $saleproduct['order_item_id']);
                }

                $itemsarray = $this->_getItemQtys($order, $items);
                $invoice;
                if (count($itemsarray) > 0 && $order->canInvoice()) {
                    $invoice = $this->_objectManager->create(
                        \Magento\Sales\Model\Service\InvoiceService::class
                    )->prepareInvoice($order, $itemsarray['data']);
                    if (!$invoice) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('We can\'t save the invoice right now.')
                        );
                    }
                    
                    if (!$invoice->getTotalQty()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('You can\'t create an invoice without products.')
                        );
                    }
                    $this->_coreRegistry->register(
                        'current_invoice',
                        $invoice
                    );
                    if (!empty($data['capture_case'])) {
                        $invoice->setRequestedCaptureCase(
                            $data['capture_case']
                        );
                    }

                    if (!empty($data['comment_text'])) {
                        $invoice->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );

                        $invoice->setCustomerNote($data['comment_text']);
                        $invoice->setCustomerNoteNotify(
                            isset($data['comment_customer_notify'])
                        );
                    }
                    $invoice->setBaseDiscountAmount($couponAmount);
                    $invoice->setDiscountAmount($couponAmount);
                    $invoice->setShippingAmount($shippingAmount);
                    $invoice->setBaseShippingInclTax($shippingAmount);
                    $invoice->setBaseShippingAmount($shippingAmount);
                    $invoice->setSubtotal($itemsarray['subtotal']);
                    $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
                    if ($paymentCode == 'mpcashondelivery') {
                        $invoice->setMpcashondelivery($codCharges);
                    }
                    $invoice->setGrandTotal(
                        $itemsarray['subtotal'] +
                        $shippingAmount +
                        $codcharges +
                        $tax -
                        $couponAmount
                    );
                    $invoice->setBaseGrandTotal(
                        $itemsarray['subtotal'] +
                        $shippingAmount +
                        $codcharges +
                        $tax -
                        $couponAmount
                    );
                    $invoice->register();
     
                    $invoice->getOrder()->setCustomerNoteNotify(
                        !empty($data['send_email'])
                    );
                    $invoice->getOrder()->setIsInProcess(true);

                    $transactionSave = $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();
                    $invoiceId = $invoice->getId();

                    $this->_invoiceSender->send($invoice);
                    $this->messageManager->addSuccess(
                        __('Invoice has been created for this order.')
                    );
                }
                /*update mpcod table records*/
                if ($invoiceId != '') {
                    if ($paymentCode == 'mpcashondelivery') {
                        $saleslistColl = $this->_objectManager->create(
                            \Webkul\Marketplace\Model\Saleslist::class
                        )->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($saleslistColl as $saleslist) {
                            $saleslist->setCollectCodStatus(1);
                            $saleslist->save();
                        }
                    }
                    $trackingcol1 = $this->_objectManager->create(
                        \Webkul\Marketplace\Model\Orders::class
                    )->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        $orderId
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    foreach ($trackingcol1 as $row) {
                        $invoiceIds = explode(',', $row->getInvoiceId());
                        array_push($invoiceIds, $invoiceId);
                        $row->setInvoiceId(implode(',', $invoiceIds));
                        if ($row->getShipmentId()) {
                            $row->setOrderStatus('complete');
                        } else {
                            $row->setOrderStatus('processing');
                        }
                        $row->save();
                    }
                }
            } else {
                $this->messageManager->addError(
                    __('Cannot create Invoice for this order.')
                );
            }
        }
    }
}
