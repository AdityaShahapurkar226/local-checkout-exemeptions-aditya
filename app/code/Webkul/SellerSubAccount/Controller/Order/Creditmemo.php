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

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Controller\Result\RedirectFactory;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class Creditmemo extends \Webkul\Marketplace\Controller\Order\Creditmemo
{

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $_creditmemoFactory;
    /**
     * Constructor
     *
     * @param \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Model\Orders $trackingsdata
     * @param \Webkul\Marketplace\Model\Saleslist $collection
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @param \Webkul\Marketplace\Model\Orders $trackingcol1
     * @param \Webkul\SellerSubAccount\Logger\SellerSubAccountLogger $Logger
     * @param RequestInterface $request
     * @param \Magento\Framework\App\Action\Context $context
     * @param OrderRepositoryInterface $_orderRepository
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $_creditmemoFactory
     * @param MessageManager $messageManager
     * @param RedirectFactory $resultRedirectFactory
     * @param MpOrdersModel $mpOrdersModel
     * @param SaleslistFactory $saleslistFactory
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Model\Orders $trackingsdata,
        \Webkul\Marketplace\Model\Saleslist $collection,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        \Webkul\Marketplace\Model\Orders $trackingcol1,
        \Webkul\SellerSubAccount\Logger\SellerSubAccountLogger $Logger,
        RequestInterface $request,
        \Magento\Framework\App\Action\Context $context,
        OrderRepositoryInterface $_orderRepository,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order\CreditmemoFactory $_creditmemoFactory,
        MessageManager $messageManager,
        RedirectFactory $resultRedirectFactory,
        MpOrdersModel $mpOrdersModel,
        SaleslistFactory $saleslistFactory,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->sellerSubAccountHelper = $sellerSubAccountHelper;
        $this->customerUrl = $customerUrl;
        $this->_customerSession = $customerSession;
        $this->trackingsdata = $trackingsdata;
        $this->collection = $collection;
        $this->helper = $helper;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->trackingcol1 = $trackingcol1;
        $this->Logger = $Logger;
        $this->request = $request;
        $this->_eventManager = $context->getEventManager();
        $this->_actionFlag = $context->getActionFlag();
        $this->_orderRepository = $_orderRepository;
        $this->orderHelper = $orderHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->_invoiceRepository = $invoiceRepository;
        $this->_creditmemoFactory = $_creditmemoFactory;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->mpOrdersModel = $mpOrdersModel;
        $this->saleslistFactory = $saleslistFactory;
        $this->_stockConfiguration = $stockConfiguration;
    }

    /**
     * Initialize creditmemo model instance.
     *
     * @param OrderRepositoryInterface $order
     * @return void
     */
    protected function _initOrderCreditmemo($order)
    {
        $data = $this->getRequest()->getPost('creditmemo');

        $creditmemo = false;

        $sellerId = $this->_customerSession->getCustomerId();
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
        if ($subAccount->getId()) {
            $sellerId = $this->sellerSubAccountHelper->getSubAccountSellerId();
        }
        $orderId = $order->getId();

        $invoice = $this->_initCreditmemoInvoice($order);
        $items = [];
        $itemsarray = [];
        $shippingAmount = 0;
        $codcharges = 0;
        $paymentCode = '';
        $paymentMethod = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }
        $trackingsdata = $this->mpOrdersModel->create()
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        );
        foreach ($trackingsdata as $tracking) {
            $shippingAmount = $tracking->getShippingCharges();
            if ($paymentCode == 'mpcashondelivery') {
                $codcharges = $tracking->getCodCharges();
            }
        }
        if (isset($data['shipping_amount'])) {
            $data['shipping_amount'] = $shippingAmount;
        }
        $this->getRequest()->setPostValue('creditmemo', $data);
        $refundData = $this->getRequest()->getParams();
        $codCharges = 0;
        $tax = 0;
        $collection = $this->saleslistFactory->create()
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
            if ($paymentCode == 'mpcashondelivery') {
                $codCharges = $codCharges + $saleproduct->getCodCharges();
            }
            $tax = $tax + $saleproduct->getTotalTax();
            array_push($items, $saleproduct['order_item_id']);
        }

        $savedData = $this->_getItemData($order, $items);

        $qtys = [];
        foreach ($savedData as $orderItemId => $itemData) {
            if (isset($itemData['qty']) && $itemData['qty']) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
            if (isset($refundData['creditmemo']['items'][$orderItemId]['back_to_stock'])) {
                $backToStock[$orderItemId] = true;
            }
        }

        if (empty($refundData['creditmemo']['shipping_amount'])) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_positive'])) {
            $refundData['creditmemo']['adjustment_positive'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_negative'])) {
            $refundData['creditmemo']['adjustment_negative'] = 0;
        }
        if (!$shippingAmount >= $refundData['creditmemo']['shipping_amount']) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        $refundData['creditmemo']['qtys'] = $qtys;

        if ($invoice) {
            $creditmemo = $this->_creditmemoFactory->createByInvoice(
                $invoice,
                $refundData['creditmemo']
            );
        } else {
            $creditmemo = $this->_creditmemoFactory->createByOrder(
                $order,
                $refundData['creditmemo']
            );
        }

        /*
         * Process back to stock flags
         */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            $parentId = $orderItem->getParentItemId();
            if (isset($backToStock[$orderItem->getId()])) {
                $creditmemoItem->setBackToStock(true);
            } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                $creditmemoItem->setBackToStock(true);
            } elseif (empty($savedData)) {
                $creditmemoItem->setBackToStock(
                    $this->_stockConfiguration->isAutoReturnEnabled()
                );
            } else {
                $creditmemoItem->setBackToStock(false);
            }
        }

        $this->_coreRegistry->register('current_creditmemo', $creditmemo);

        return $creditmemo;
    }

    /**
     * Save creditmemo.
     */
    public function execute()
    {
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        if (!$isPartner) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $orderId = $this->getRequest()->getParam('id');
        $sellerId = $this->_customerSession->getCustomerId();
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
        if ($subAccount->getId()) {
            $sellerId = $this->sellerSubAccountHelper->getSubAccountSellerId();
        }
        $order = $this->_initOrder();
        if (empty($order)) {
            return $this->resultRedirectFactory->create()
            ->setPath(
                '*/*/history',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        try {
            $creditmemo = $this->_initOrderCreditmemo($order);
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }
                $data = $this->getRequest()->getParam('creditmemo');

                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    $creditmemo->setCustomerNote($data['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                if (isset($data['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                }
                $creditmemoManagement = $this->creditmemoManagement;
                $creditmemo = $creditmemoManagement
                ->refund($creditmemo, (bool) $data['do_offline'], !empty($data['send_email']));

                /*update records*/
                $creditmemoIds = [];
                $trackingcol1 = $this->mpOrdersModel->create()
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['eq' => $orderId]
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $sellerId]
                );
                foreach ($trackingcol1 as $tracking) {
                    if ($tracking->getCreditmemoId()) {
                        $creditmemoIds = explode(',', $tracking->getCreditmemoId());
                    }
                    $creditmemoId = $creditmemo->getId();
                    if ($creditmemoId && !in_array($creditmemoId, $creditmemoIds)) {
                        array_push($creditmemoIds, $creditmemo->getId());
                        $tracking->setCreditmemoId(implode(',', $creditmemoIds));
                        $tracking->save();
                    }
                }

                if (!empty($data['send_email'])) {
                    $this->_creditmemoSender->send($creditmemo);
                }

                $this->messageManager->addSuccess(__('You created the credit memo.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Creditmemo execute : ".$e->getMessage()
            );
            $this->logger->critical($e);
            $this->messageManager->addError(
                __('We can\'t save the credit memo right now.').$e->getMessage()
            );
        }

        return $this->resultRedirectFactory->create()->setPath(
            '*/*/view',
            [
                'id' => $order->getEntityId(),
                '_secure' => $this->getRequest()->isSecure(),
            ]
        );
    }
}
