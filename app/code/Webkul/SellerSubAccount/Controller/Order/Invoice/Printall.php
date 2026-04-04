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

namespace Webkul\SellerSubAccount\Controller\Order\Invoice;

use Magento\Framework\App\Filesystem\DirectoryList;
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
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Webkul\Marketplace\Model\SellerFactory as MpSellerModel;

class Printall extends \Webkul\Marketplace\Controller\Order\Invoice\Printall
{
    /**
     * @var ShipmentSender
     */
    protected $_shipmentSender;
    /**
     * @var InvoiceSender
     */
    protected $_invoiceSender;
    /**
     * @var ShipmentFactory
     */
    protected $_shipmentFactory;

    /**
     * @var Shipment
     */
    protected $_shipment;
     /**
      * @var \Magento\Sales\Api\InvoiceRepositoryInterface
      */
    protected $_invoiceRepository;

    /**
     * @var StockConfigurationInterface
     */
    protected $_stockConfiguration;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var CreditmemoSender
     */
    protected $_creditmemoSender;

    /**
     * @var CreditmemoRepositoryInterface;
     */
    protected $_creditmemoRepository;

    /**
     * @var \Webkul\Marketplace\Helper\Orders
     */
    protected $orderHelper;

    /**
     * @var NotificationHelper
     */
    protected $notificationHelper;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManagement;

    /**
     * @var SaleslistFactory
     */
    protected $saleslistFactory;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var FileFactory
     */
    protected $fileFactory;
    /**
     * @var \Webkul\Marketplace\Model\Order\Pdf\Creditmemo
     */
    protected $creditmemoPdf;

    /**
     * @var \Webkul\Marketplace\Model\Order\Pdf\Invoice
     */
    protected $invoicePdf;
    
    /**
     * @var InvoiceCollection
     */
    protected $invoiceCollection;

    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $invoiceManagement;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;

    /**
     * @var MpSellerModel
     */
    protected $mpSellerModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @var CreditmemoFactory;
     */
    protected $_creditmemoFactory;

    /**
     * @var OrderManagementInterface
     */
    protected $_orderManagement;
    
    /**
     * Constructor
     *
     * @param \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper
     * @param \Webkul\Marketplace\Model\Saleslist $collection
     * @param \Webkul\Marketplace\Model\Orders $shippingColl
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoices
     * @param \Webkul\Marketplace\Model\Order\Pdf\Invoice $pdf
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param ShipmentFactory $shipmentFactory
     * @param Shipment $shipment
     * @param CreditmemoSender $creditmemoSender
     * @param CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param OrderRepositoryInterface $_orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param NotificationHelper $notificationHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @param SaleslistFactory $saleslistFactory
     * @param CustomerUrl $customerUrl
     * @param \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $creditmemoPdf
     * @param \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param MpOrdersModel $mpOrdersModel
     * @param InvoiceCollection $_invoiceCollection
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param MpSellerModel $mpSellerModel
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper,
        \Webkul\Marketplace\Model\Saleslist $collection,
        \Webkul\Marketplace\Model\Orders $shippingColl,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoices,
        \Webkul\Marketplace\Model\Order\Pdf\Invoice $pdf,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        Context $context,
        PageFactory $_resultPageFactory,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        CreditmemoRepositoryInterface $creditmemoRepository,
        ShipmentFactory $shipmentFactory,
        Shipment $shipment,
        CreditmemoSender $creditmemoSender,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        StockConfigurationInterface $stockConfiguration,
        OrderRepositoryInterface $_orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Registry $coreRegistry,
        NotificationHelper $notificationHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        SaleslistFactory $saleslistFactory,
        CustomerUrl $customerUrl,
        \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $creditmemoPdf,
        \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        MpOrdersModel $mpOrdersModel,
        InvoiceCollection $_invoiceCollection,
        \Magento\Catalog\Model\ProductFactory $productModel,
        MpSellerModel $mpSellerModel,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->sellerSubAccountHelper = $sellerSubAccountHelper;
        $this->helper = $helper;
        $this->invoices = $invoices;
        $this->collection = $collection;
        $this->shippingColl = $shippingColl;
        $this->date = $date;
        $this->pdf = $pdf;
        $this->fileFactory = $fileFactory;
        parent::__construct(
            $context,
            $_resultPageFactory,
            $invoiceSender,
            $shipmentSender,
            $shipmentFactory,
            $shipment,
            $creditmemoSender,
            $creditmemoRepository,
            $creditmemoFactory,
            $invoiceRepository,
            $stockConfiguration,
            $_orderRepository,
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
            $_invoiceCollection,
            $invoiceManagement,
            $productModel,
            $mpSellerModel,
            $logger
        );
    }
    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $_isPartner = $this->helper->isSeller();
        if ($_isPartner == 1) {
            $get = $this->getRequest()->getParams();
            $todate = date_create($get['special_to_date']);
            $_to = date_format($todate, 'Y-m-d H:i:s');
            $fromdate = date_create($get['special_from_date']);
            $_from = date_format($fromdate, 'Y-m-d H:i:s');

            $invoiceIds = [];
            try {
                $sellerId = $this->_customerSession->getCustomerId();
                $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
                if ($subAccount->getId()) {
                    $sellerId = $this->sellerSubAccountHelper->getSubAccountSellerId();
                }
                $collection = $this->collection
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )
                ->addFieldToFilter(
                    'created_at',
                    ['datetime' => true, 'gteq' => $_from, 'lteq' => $_to]
                )
                ->addFieldToSelect('order_id')
                ->distinct(true);
                foreach ($collection as $coll) {
                    $shippingColls = $this->shippingColl
                    ->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        $coll->getOrderId()
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    foreach ($shippingColls as $tracking) {
                        if ($tracking->getInvoiceId()) {
                            array_push($invoiceIds, $tracking->getInvoiceId());
                        }
                    }
                }
                if (!empty($invoiceIds)) {
                    $invoices = $this->invoiceCollection
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter(
                        'entity_id',
                        ['in' => $invoiceIds]
                    )
                    ->load();

                    if (!$invoices->getSize()) {
                        $this->messageManager->addError(
                            __('There are no printable documents related to selected date range.')
                        );

                        return $this->resultRedirectFactory->create()->setPath(
                            'marketplace/order/history',
                            [
                                '_secure' => $this->getRequest()->isSecure(),
                            ]
                        );
                    }
                    $_pdf = $this->invoicePdf->getPdf($invoices);
                    $_date = $this->date->date('Y-m-d_H-i-s');

                    return $this->fileFactory->create(
                        'invoiceslip'.$_date.'.pdf',
                        $_pdf->render(),
                        DirectoryList::VAR_DIR,
                        'application/pdf'
                    );
                } else {
                    $this->messageManager->addError(
                        __('There are no printable documents related to selected date range.')
                    );
                    $path='marketplace/order/history';
                    return $this->resultRedirectFactory->create()->setPath(
                        $path,
                        [
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $path='marketplace/order/history';
                return $this->resultRedirectFactory->create()->setPath(
                    $path,
                    [
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addError(
                    __('We can\'t print the invoice right now.')
                );
                $path='marketplace/order/history';
                return $this->resultRedirectFactory->create()->setPath(
                    $path,
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
}
