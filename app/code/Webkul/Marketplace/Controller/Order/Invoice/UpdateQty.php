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

use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul Marketplace Order Invoice UpdateQty Controller.
 */
class UpdateQty extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Invoice Create UpdateQty Action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $helper = $this->helper;
            $sellerId = $helper->getCustomerId();
            $isPartner = $helper->isSeller();
            if ($isPartner == 1) {
                if ($order = $this->_initOrder()) {
                    $orderId = $order->getId();
                    $invoiceData = $this->getRequest()->getParam('invoice', []);
                    $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];

                    $paymentCode = '';
                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }

                    $itemsData = $this->getCurrentSellerInvoiceItemsData(
                        $orderId,
                        $sellerId,
                        $paymentCode,
                        $invoiceItems
                    );
    
                    $items = $itemsData['items'];
                    $currencyRate = $itemsData['currencyRate'];
                    $codCharges = $itemsData['codCharges'];
                    $tax = $itemsData['tax'];
                    $couponAmount = $itemsData['couponAmount'];
                    $shippingAmount = $itemsData['shippingAmount'];

                    if (!$order->canInvoice()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The order does not allow an invoice to be created.')
                        );
                    }

                    $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);

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
                    // Save invoice comment text in current invoice object in order to display it in corresponding view
                    $invoiceRawCommentText = $invoiceData['comment_text'];
                    $invoice->setCommentText($invoiceRawCommentText);

                    $resultPage = $this->_resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
                    $responseData = $resultPage->getLayout()->getBlock(
                        'marketplace_order_new_invoice_items'
                    )->toHtml();
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This order is no longer exists.')
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } catch (LocalizedException $e) {
            $responseData = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $responseData = ['error' => true, 'message' => $e->getMessage()];
        }
        if (is_array($responseData)) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($responseData);
            return $resultJson;
        } else {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($responseData);
            return $resultRaw;
        }
    }
}
