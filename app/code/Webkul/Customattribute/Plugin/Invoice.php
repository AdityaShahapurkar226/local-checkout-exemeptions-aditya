<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Webkul\Marketplace\Model\Notification;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Webkul\Marketplace\Model\SellerFactory as MpSellerModel;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection;

class Invoice extends \Webkul\Marketplace\Controller\Order\Invoice
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeData;

    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    protected $orderCollection;

    /**
     * @param Context                                           $context
     * @param PageFactory                                       $resultPageFactory
     * @param InvoiceSender                                     $invoiceSender
     * @param ShipmentSender                                    $shipmentSender
     * @param ShipmentFactory                                   $shipmentFactory
     * @param Shipment                                          $shipment
     * @param CreditmemoSender                                  $creditmemoSender
     * @param CreditmemoRepositoryInterface                     $creditmemoRepository
     * @param CreditmemoFactory                                 $creditmemoFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface     $invoiceRepository
     * @param StockConfigurationInterface                       $stockConfiguration
     * @param OrderRepositoryInterface                          $orderRepository
     * @param OrderManagementInterface                          $orderManagement
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Webkul\Marketplace\Helper\Orders                 $orderHelper
     * @param NotificationHelper                                $notificationHelper
     * @param HelperData                                        $helper
     * @param \Magento\Sales\Api\CreditmemoManagementInterface  $creditmemoManagement
     * @param SaleslistFactory                                  $saleslistFactory
     * @param CustomerUrl                                       $customerUrl
     * @param DateTime                                          $date
     * @param FileFactory                                       $fileFactory
     * @param \Webkul\Marketplace\Model\Order\Pdf\Creditmemo    $creditmemoPdf
     * @param \Webkul\Marketplace\Model\Order\Pdf\Invoice       $invoicePdf
     * @param MpOrdersModel                                     $mpOrdersModel
     * @param InvoiceCollection                                 $invoiceCollection
     * @param \Magento\Sales\Api\InvoiceManagementInterface     $invoiceManagement
     * @param \Magento\Catalog\Model\ProductFactory             $productModel
     * @param MpSellerModel                                     $mpSellerModel
     * @param \Psr\Log\LoggerInterface                          $logger
     * @param \Magento\Weee\Helper\Data                         $weeeData
     * @param Collection                                        $orderCollection
     * @param \Magento\Sales\Model\Service\InvoiceService       $invoiceSerivce
     * @param \Magento\Framework\DB\Transaction                 $dbTransaction
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        Shipment $shipment,
        CreditmemoSender $creditmemoSender,
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        StockConfigurationInterface $stockConfiguration,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        NotificationHelper $notificationHelper,
        HelperData $helper,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        SaleslistFactory $saleslistFactory,
        CustomerUrl $customerUrl,
        DateTime $date,
        FileFactory $fileFactory,
        \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $creditmemoPdf,
        \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf,
        MpOrdersModel $mpOrdersModel,
        InvoiceCollection $invoiceCollection,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Catalog\Model\ProductFactory $productModel,
        MpSellerModel $mpSellerModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Weee\Helper\Data $weeeData,
        Collection  $orderCollection,
        \Magento\Sales\Model\Service\InvoiceService $invoiceSerivce,
        \Magento\Framework\DB\Transaction $dbTransaction
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $invoiceSender,
            $shipmentSender,
            $shipmentFactory,
            $shipment,
            $creditmemoSender,
            $creditmemoRepository,
            $creditmemoFactory,
            $invoiceRepository,
            $stockConfiguration,
            $orderRepository,
            $orderManagement,
            $coreRegistry,
            $customerSession,
            $orderHelper,
            $notificationHelper,
            $helper,
            $creditmemoManagement,
            $saleslistFactory,
            $customerUrl,
            $date,
            $fileFactory,
            $creditmemoPdf,
            $invoicePdf,
            $mpOrdersModel,
            $invoiceCollection,
            $invoiceManagement,
            $productModel,
            $mpSellerModel,
            $logger
        );
        $this->weeeData = $weeeData;
        $this->orderCollection = $orderCollection;
        $this->invoiceSerivce = $invoiceSerivce;
        $this->dbTransaction = $dbTransaction;
    }
    /**
     * Generate invoice
     *
     * @param object $order
     */
    protected function doInvoiceExecution($order)
    {
        try {
            $helper = $this->helper;
                
            $sellerId = $this->_customerSession->getCustomerId();
            $orderId = $order->getId();
            if ($order->canUnhold()) {
                $this->messageManager->addError(
                    __('Can not create invoice as order is in HOLD state')
                );
            } else {
                $data = [];
                $data['send_email'] = 1;
                $marketplaceOrder = $this->orderHelper->getOrderinfo($orderId);
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
                        $couponAmount = $tracking->getCouponAmount();
                        if ($paymentCode == 'mpcashondelivery') {
                            $codcharges = $tracking->getCodCharges();
                        }
                    }
                    $codCharges = 0;
                    $tax = 0;
                    $currencyRate = 1;
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
                        $currencyRate = $saleproduct->getCurrencyRate();
                        if ($paymentCode == 'mpcashondelivery') {
                            $codCharges = $codCharges + $saleproduct->getCodCharges();
                        }
                        $tax = $tax + $saleproduct->getTotalTax();
                        array_push($items, $saleproduct['order_item_id']);
                    }
                    $weeeTotal=0;
                    $weeeBaseTotal =0;
                    $itemsarray = $this->_getItemQtys($order, $items);
                    $items = $order->getAllItems();
                    $store = $order->getStore();
                    $orderItems = $this->getOrderItems($order);
                    $weeeTotal = $this->weeeData->getTotalAmounts($orderItems, $store);
                    $weeeBaseTotal = $this->weeeData->getBaseTotalAmounts($orderItems, $store);
                    if (count($itemsarray) > 0 && $order->canInvoice()) {
                        $invoice = $this->invoiceSerivce->prepareInvoice($order, $itemsarray['data']);
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
    
                        $currentCouponAmount = $currencyRate * $couponAmount;
                        $currentShippingAmount = $currencyRate * $shippingAmount;
                        $currentTaxAmount = $currencyRate * $tax;
                        $currentCodcharges = $currencyRate * $codcharges;
                        $invoice->setBaseDiscountAmount($couponAmount);
                        $invoice->setDiscountAmount($currentCouponAmount);
                        $invoice->setShippingAmount($currentShippingAmount);
                        $invoice->setBaseShippingInclTax($shippingAmount);
                        $invoice->setBaseShippingAmount($shippingAmount);
                        $invoice->setSubtotal($itemsarray['subtotal']);
                        $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
                        if ($paymentCode == 'mpcashondelivery') {
                            $invoice->setMpcashondelivery($currentCodcharges);
                            $invoice->setBaseMpcashondelivery($codCharges);
                        }
                        $invoice->setGrandTotal(
                            $itemsarray['subtotal'] +
                            $currentShippingAmount +
                            $currentCodcharges +
                            $weeeTotal+
                            $currentTaxAmount -
                            $currentCouponAmount
                        );
                        $invoice->setBaseGrandTotal(
                            $itemsarray['baseSubtotal'] +
                            $shippingAmount +
                            $weeeBaseTotal+
                            $codcharges +
                            $tax -
                            $couponAmount
                        );
    
                        $invoice->register();
    
                        $invoice->getOrder()->setCustomerNoteNotify(
                            !empty($data['send_email'])
                        );
                        $invoice->getOrder()->setIsInProcess(true);
    
                        $transactionSave = $this->dbTransaction
                        ->addObject(
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
                    $this->updateMpcodTable($invoiceId, $orderId, $sellerId, $paymentCode);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doInvoiceExecution : ".$e->getMessage()
            );
            $this->messageManager->addError(
                __('We can\'t save the invoice right now.')
            );
            $this->messageManager->addError($e->getMessage());
        }
    }
    
    /**
     * Get item qtyss
     *
     * @param object $order
     * @param array $items
     * @return array
     */
    protected function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = (int)($item->getQtyOrdered() - $item->getQtyInvoiced());

                $_item = $item;

                // for bundle product
                $bundleitems = $this->getMergedArray([$_item], $_item->getChildrenItems());

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int)(
                                $_bundleitem->getQtyOrdered() - $item->getQtyInvoiced()
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return ['data' => $data,'subtotal' => $subtotal,'baseSubtotal' => $baseSubtotal];
    }

    /**
     * Get merged array
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public function getMergedArray($array1, $array2)
    {
        return array_merge($array1, $array2);
    }

    /**
     * Ppdate marketplace COD table
     *
     * @param int $invoiceId
     * @param int $orderId
     * @param int $sellerId
     * @param string $paymentCode
     * @return void
     */
    public function updateMpcodTable($invoiceId, $orderId, $sellerId, $paymentCode)
    {
        if ($invoiceId != '') {
            if ($paymentCode == 'mpcashondelivery') {
                $saleslistColl = $this->saleslistFactory->create()
                ->getCollection()
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

            $trackingcol1 = $this->mpOrdersModel->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($trackingcol1 as $row) {
                $row->setInvoiceId($invoiceId);
                if ($row->getShipmentId()) {
                    $row->setOrderStatus('complete');
                } else {
                    $row->setOrderStatus('processing');
                }
                $row->save();
            }
        }
    }

    /**
     * Get seller order  items
     *
     * @param object $order
     * @return array
     */
    public function getOrderItems($order)
    {
        $orderAmountData = $this->orderCollection
        ->addFieldToFilter(
            'main_table.order_id',
            $order->getId()
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        );
        $salesCreditmemoItem = $this->orderCollection->getTable('sales_order_item');
        $orderAmountData->getSelect()->join(
            $salesCreditmemoItem.' as creditmemo_item',
            'creditmemo_item.item_id = main_table.order_item_id'
        )->where('creditmemo_item.order_id = '.$order->getId());

        return $orderAmountData;
    }
}
