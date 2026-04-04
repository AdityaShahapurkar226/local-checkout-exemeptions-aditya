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
namespace Webkul\MpApi\Model\Seller;

use Webkul\MpApi\Api\FeedbackRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

class SellerManagement implements \Webkul\MpApi\Api\SellerManagementInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customerInterface;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Directory\Model\Country
     */
    protected $country;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\Marketplace\Api\Data\SellerInterfaceFactory
     */
    protected $sellerFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $mageOrderRepo;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var CreditmemoFactory;
     */
    protected $creditmemoFactory;

    /**
     * @var CreditmemoSender;
     */
    protected $creditmemoSender;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\ResourceConnectionn
     */
    protected $resourceConnection;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepoInterface;

    /**
     * @var \Webkul\Marketplace\Api\Data\ProductInterfaceFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory
     */
    protected $saleslistFactory;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Webkul\Marketplace\Api\Data\OrdersInterfaceFactory
     */
    protected $mpOrdersFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $invoiceManagementInterface;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;

    /**
     * @var \Webkul\Marketplace\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrl;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Webkul\Marketplace\Helper\Orders
     */
    protected $orderHelper;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManagementInterface;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepositoryInterface;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Webkul\MpApi\Api\Data\FeedbackInterfaceFactory
     */
    protected $feedbackFactory;

    /**
     * @var \Webkul\Marketplace\Api\Data\FeedbackcountInterfaceFactory
     */
    protected $feedbackcountFactory;

    /**
     * @var \Webkul\MpApi\Api\Data\ResponseInterface
     */
    protected $responseInterface;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * @var \Webkul\Marketplace\Controller\Product\SaveProduct
     */
    protected $saveProduct;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;
    
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $carrierFactory;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;
    
    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\MpApi\Api\SaleslistRepositoryInterface $salesListRepo
     * @param \Webkul\MpApi\Api\SellerRepositoryInterface $sellerRepo
     * @param \Webkul\MpApi\Api\OrdersRepositoryInterface $ordersRepo
     * @param FeedbackRepositoryInterface $feedbackRepo
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerInterface
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Directory\Model\CountryFactory $country
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\Marketplace\Api\Data\SellerInterfaceFactory $sellerFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param InvoiceSender $invoiceSender
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentSender $shipmentSender
     * @param CreditmemoSender $creditmemoSender
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepoInterface
     * @param \Webkul\Marketplace\Api\Data\ProductInterfaceFactory $mpProductFactory
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $saleslistFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Webkul\Marketplace\Api\Data\OrdersInterfaceFactory $mpOrdersFactory
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagementInterface
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     * @param \Webkul\Marketplace\Helper\Email $emailHelper
     * @param \Magento\Backend\Model\Url $backendUrl
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagementInterface
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepositoryInterface
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Webkul\MpApi\Api\Data\FeedbackInterfaceFactory $feedbackFactory
     * @param \Webkul\Marketplace\Api\Data\FeedbackcountInterfaceFactory $feedbackcountFactory
     * @param \Webkul\MpApi\Api\Data\ResponseInterface $responseInterface
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param \Magento\Framework\Api\FilterFactory $filter
     * @param \Magento\Framework\Api\Search\FilterGroupFactory $filterGroup
     * @param \Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor $mediaProcessor
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\MpApi\Api\SaleslistRepositoryInterface $salesListRepo,
        \Webkul\MpApi\Api\SellerRepositoryInterface $sellerRepo,
        \Webkul\MpApi\Api\OrdersRepositoryInterface $ordersRepo,
        FeedbackRepositoryInterface $feedbackRepo,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\Data\CustomerInterface $customerInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Directory\Model\CountryFactory $country,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\Marketplace\Api\Data\SellerInterfaceFactory $sellerFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoFactory $creditmemoFactory,
        InvoiceSender $invoiceSender,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ShipmentFactory $shipmentFactory,
        ShipmentSender $shipmentSender,
        CreditmemoSender $creditmemoSender,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepoInterface,
        \Webkul\Marketplace\Api\Data\ProductInterfaceFactory $mpProductFactory,
        \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $saleslistFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Webkul\Marketplace\Api\Data\OrdersInterfaceFactory $mpOrdersFactory,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagementInterface,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Webkul\Marketplace\Helper\Email $emailHelper,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagementInterface,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepositoryInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Webkul\MpApi\Api\Data\FeedbackInterfaceFactory $feedbackFactory,
        \Webkul\Marketplace\Api\Data\FeedbackcountInterfaceFactory $feedbackcountFactory,
        \Webkul\MpApi\Api\Data\ResponseInterface $responseInterface,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Filesystem\Io\File $file,
        \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Framework\Api\Search\FilterGroupFactory $filterGroup,
        \Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor $mediaProcessor,
        AccountManagementInterface $accountManagement
    ) {
        $this->customerFactory = $customerFactory;
        $this->salesListRepo = $salesListRepo;
        $this->sellerRepo = $sellerRepo;
        $this->ordersRepo = $ordersRepo;
        $this->feedbackRepo = $feedbackRepo;
        $this->customerSession = $customerSession;
        $this->customerInterface = $customerInterface;
        $this->customerRepository = $customerRepository;
        $this->country = $country;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->sellerFactory = $sellerFactory;
        $this->mpHelper = $marketplaceHelper;
        $this->mageOrderRepo = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->invoiceSender = $invoiceSender;
        $this->date = $date;
        $this->resourceConnection = $resourceConnection;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentSender = $shipmentSender;
        $this->creditmemoSender = $creditmemoSender;
        $this->eventManager = $eventManager;
        $this->productRepoInterface = $productRepoInterface;
        $this->mpProductFactory = $mpProductFactory;
        $this->saleslistFactory = $saleslistFactory;
        $this->directoryList = $directoryList;
        $this->mpOrdersFactory = $mpOrdersFactory;
        $this->invoiceManagementInterface = $invoiceManagementInterface;
        $this->dbTransaction = $dbTransaction;
        $this->emailHelper = $emailHelper;
        $this->backendUrl = $backendUrl;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlInterface = $urlInterface;
        $this->orderHelper = $orderHelper;
        $this->creditmemoManagementInterface = $creditmemoManagementInterface;
        $this->creditmemoRepositoryInterface = $creditmemoRepositoryInterface;
        $this->imageHelper = $imageHelper;
        $this->feedbackFactory = $feedbackFactory;
        $this->feedbackcountFactory = $feedbackcountFactory;
        $this->responseInterface = $responseInterface;
        $this->driverFile = $driverFile;
        $this->file = $file;
        $this->saveProduct = $saveProduct;
        $this->_shipmentRepository = $shipmentRepository;
        $this->carrierFactory = $carrierFactory;
        $this->jsonHelper = $jsonHelper;
        $this->filterGroup = $filterGroup;
        $this->filter = $filter;
        $this->searchCriteria = $searchCriteria;
        $this->mediaProcessor = $mediaProcessor;
        $this->accountManagement = $accountManagement;
    }

    /**
     * @inheritDoc
     */
    public function getSeller($id, $storeId = 0)
    {
        $filter1 = $this->filter->create();
        $filter2 = $this->filter->create();
        $filter3 = $this->filter->create();
        $filterGroup1 = $this->filterGroup->create();
        $filterGroup2 = $this->filterGroup->create();
        $filterGroup3 = $this->filterGroup->create();
        $filter1->setField("seller_id")
            ->setValue($id)
            ->setConditionType("eq");
        $filter2->setField("store_id")
            ->setValue($storeId)
            ->setConditionType("eq");
        $filter3->setField("is_seller")
            ->setValue(1)
            ->setConditionType("eq");
        $filterGroup1->setFilters([$filter1]);
        $filterGroup2->setFilters([$filter2]);
        $filterGroup3->setFilters([$filter3]);
        $criteria = $this->searchCriteria->setFilterGroups([
            $filterGroup1,$filterGroup2,$filterGroup3
        ]);
        return $this->sellerRepo->getList($criteria);
    }

    /**
     * @inheritDoc
     */
    public function getSellerList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->sellerRepo->getList($searchCriteria);
    }

    /**
     * Get order
     *
     * @param int $orderId
     *
     * @return Magento\Sales\Model\Order
     */
    private function getOrder($orderId)
    {
        $order = $this->mageOrderRepo->get($orderId);
        if ($order->getId()) {
            return $order;
        } else {
            throw \NoSuchEntityException::singleField('orderId', $orderId);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSellerProducts($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            $returnArray = [];
            $collection = $this->mpProductFactory->create()
                ->getCollection()
                ->addFieldToSelect('mageproduct_id')
                ->addFieldToFilter('seller_id', $id)
                ->addFieldToFilter('status', 1)
                ->setOrder('mageproduct_id');
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no result found')
                );
            } else {
                $items = [];
                foreach ($collection as $coll) {
                    $productId = $coll->getMageproductId();
                    $product = $this->productRepoInterface->getById($productId);
                    if ($product) {
                        $items[] = $this->arrayMerge([
                            'name' => $product->getName(),
                            'type' => $product->getTypeId(),
                            'sku' => $product->getSku()
                        ], $coll->getData());
                    }
                }
                $returnArray['status'] = self::SUCCESS;
                $returnArray['total_count'] = count($items);
                $returnArray['items'] = $items;
                return $this->getJsonResponse($returnArray);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['error'] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSellerSalesList($id)
    {
        $filter = $this->filter->create();
        $filterGroup = $this->filterGroup->create();
        $filter->setField("seller_id")
            ->setValue($id)
            ->setConditionType("eq");
        $filterGroups = $filterGroup->setFilters([$filter]);
        $criteria = $this->searchCriteria->setFilterGroups([$filterGroups]);
        $data = $this->salesListRepo->getList($criteria);
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getSellerSalesDetails($id)
    {
        $returnArray = [];
        $collectionOrders = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', $id)
            ->addFieldToFilter('magequantity', ['neq' => 0])
            ->addFieldToSelect('order_id')
            ->distinct(true);
        if ($collectionOrders->getSize() == 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Seller doesnot have any order')
            );
        }
        $filter = $this->filter->create();
        $filterGroup = $this->filterGroup->create();
        $orderIds = $collectionOrders->getData();

        $finalOrderIds = array_column($orderIds, 'order_id');
        $filter->setField("order_id")
            ->setValue(implode(',', $finalOrderIds))
            ->setConditionType("in");
        $filterGroups = $filterGroup->setFilters([$filter]);
        $criteria = $this->searchCriteria->setFilterGroups([$filterGroups]);
        $data = $this->ordersRepo->getLists($criteria);
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function createInvoice($id, $orderId)
    {
        $returnArray = [];
        try {
            $sellerId = $id;
            if (!$this->isSeller($sellerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }

            $order = $this->getOrder($orderId);

            $orderId = $order->getId();
            if ($order->canUnhold()) {
                $returnArray['message'] = _createInvoice_('Can not create invoice as order is in HOLD state');
                $returnArray['status'] = self::LOCAL_ERROR;
            } else {
                $data = [];
                $data['send_email'] = 1;
                $marketplaceOrder = $this->mpOrdersFactory->create();
                $model = $marketplaceOrder
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                $sellerId
                            )->addFieldToFilter(
                                'order_id',
                                $orderId
                            );
                foreach ($model as $tracking) {
                    $marketplaceOrder = $tracking;
                }

                $invoiceId = $marketplaceOrder->getInvoiceId();

                if (!$invoiceId) {
                    $items = [];
                    $itemsarray = [];
                    $shippingAmount = 0;
                    $codcharges = 0;
                    $paymentCode = '';
                    $paymentMethod = '';
                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    $trackingsdata = $this->mpOrdersFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                    foreach ($trackingsdata as $tracking) {
                        $shippingAmount = $tracking->getShippingCharges();
                        if ($paymentCode == 'mpcashondelivery') {
                            $codcharges = $tracking->getCodCharges();
                        }
                    }
                    $codCharges = 0;
                    $tax = 0;
                    $collection = $this->saleslistFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        );
                    if ($collection->getSize() == 0) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('you are not authorize to create invoice')
                        );
                    }
                    foreach ($collection as $saleproduct) {
                        if ($paymentCode == 'mpcashondelivery') {
                            $codCharges = $codCharges + $saleproduct->getCodCharges();
                        }
                        $tax = $tax + $saleproduct->getTotalTax();
                        array_push($items, $saleproduct['order_item_id']);
                    }

                    $itemsarray = $this->_getItemQtys($order, $items);

                    if (count($itemsarray) > 0 && $order->canInvoice()) {
                        $invoice = $this->invoiceManagementInterface->prepareInvoice($order, $itemsarray['data']);
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

                        $data['capture_case'] = "offline";
                        $invoice->setRequestedCaptureCase(
                            $data['capture_case']
                        );

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
                                    $tax
                        );
                        $invoice->setBaseGrandTotal(
                            $itemsarray['subtotal'] + $shippingAmount + $codcharges + $tax
                        );

                        $invoice->register();

                        $invoice->getOrder()->setCustomerNoteNotify(
                            !empty($data['send_email'])
                        );
                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = $this->dbTransaction->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        $transactionSave->save();

                        $invoiceId = $invoice->getId();

                        $this->invoiceSender->send($invoice);
                        $returnArray['invoice_id'] = $invoiceId;
                        $returnArray['message'] = __('Invoice has been created for this order.');
                        $returnArray['status'] = self::SUCCESS;
                    } else {
                        $returnArray['message'] = __('You cannot create invoice for this order.');
                        $returnArray['status'] = self::LOCAL_ERROR;
                    }
                    /** Update mpcod table records */
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
                            $this->setCollectCodStatusEnable($saleslistColl);
                        }

                        $trackingcol1 = $this->mpOrdersFactory->create()
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
                            $row->save();
                        }
                    }
                } else {
                    $returnArray['message'] = __('Cannot create Invoice for this order.');
                    $returnArray['status'] = self::LOCAL_ERROR;
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
        }
        return $this->getJsonResponse($returnArray);
    }

    /**
     * @inheritDoc
     */
    public function viewInvoice($id, $orderId, $invoiceId)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }
            $returnArray = [];

            $customerId = $id;
            $invoiceId = $invoiceId;
            $helper = $this->mpHelper;
            $order = $this->getOrder($orderId);
            $invoice = $this->invoiceRepository->get($invoiceId);
            $paymentCode = '';
            $payment_method = '';
            $orderId = $order->getId();
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
                $payment_method = $order->getPayment()->getConfigData('title');
            }
            $invoiceStatus = '';
            if ($invoice->getState() == 1) {
                $invoiceStatus = __('Pending');
            } elseif ($invoice->getState() == 2) {
                $invoiceStatus = __('Paid');
            } elseif ($invoice->getState() == 3) {
                $invoiceStatus = __('Canceled');
            }
            $marketplaceOrders = $this->mpOrdersFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $customerId);

            if (count($marketplaceOrders)) {
                $returnArray['mainHeading'] = __('View Invoice Details');
                $returnArray['sendmailAction'] = __('Send Email To Customer');
                $returnArray['sendmailWarning'] = __('Are you sure you want to send order email to customer?');
                $returnArray['subHeading'] = __(
                    'Invoice #%1 - %2 | %3',
                    $invoice->getIncrementId(),
                    $invoiceStatus,
                    $invoice->getCreatedAt()
                );
                $returnArray['orderData']['title'] = __('Order Information');
                $returnArray['orderData']['label'] = __('Order # %1', $order->getIncrementId());
                $returnArray['orderData']['statusLabel'] = __('Order Status');
                $returnArray['orderData']['statusValue'] = ucfirst($order->getStatus());
                $returnArray['orderData']['dateLabel'] = __('Order Date');
                $returnArray['orderData']['dateValue'] = $order->getCreatedAt();
                // Buyer Data
                $returnArray['buyerData']['title'] = __('Buyer Information');
                $returnArray['buyerData']['nameLabel'] = __('Customer Name').': ';
                $returnArray['buyerData']['nameValue'] = $order->getCustomerName();
                $returnArray['buyerData']['emailLabel'] = __('Email').': ';
                $returnArray['buyerData']['emailValue'] = $order->getCustomerEmail();
                // Shipping Address Data
                if (!$order->getIsVirtual()) {
                    $returnArray['shippingAddressData']['title'] = __('Shipping Address');
                    $shippingAddress = $order->getShippingAddress();
                    $shippingAddressData['name'] = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                    $shippingAddressData['street'] = $shippingAddress->getStreet()[0];
                    if (count($shippingAddress->getStreet()) > 1) {
                        if ($shippingAddress->getStreet()[1]) {
                            $shippingAddressData['street'] .= $shippingAddress->getStreet()[1];
                        }
                    }
                    $state = [
                        $shippingAddress->getCity(),
                        $shippingAddress->getRegion(),
                        $shippingAddress->getPostcode()
                    ];
                    $shippingAddressData['state'] = implode(', ', $state);
                    $shippingAddressData['country'] = $this->country->create()
                    ->load($shippingAddress->getCountryId())->getName();
                    $shippingAddressData['telephone'] = 'T: '.$shippingAddress->getTelephone();
                    $returnArray['shippingAddressData']['address'][] = $shippingAddressData;

                    // Shipping Method Data
                    $returnArray['shippingMethodData']['title'] = __('Shipping Information');
                    if ($order->getShippingDescription()) {
                        $returnArray['shippingMethodData']['method'] = strip_tags($order->getShippingDescription());
                    } else {
                        $returnArray['shippingMethodData']['method'] = __('No shipping information available');
                    }
                }

                // Billing Address Data
                $returnArray['billingAddressData']['title'] = __('Billing Address');
                $billingAddress = $order->getBillingAddress();
                $billingAddressData['name'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                $billingAddressData['street'] = $billingAddress->getStreet()[0];
                if (count($billingAddress->getStreet()) > 1) {
                    if ($billingAddress->getStreet()[1]) {
                        $billingAddressData['street'] .= $billingAddress->getStreet()[1];
                    }
                }
                $billState = [
                    $billingAddress->getCity(),
                    $billingAddress->getRegion(),
                    $billingAddress->getPostcode()
                ];
                $billingAddressData['state'] = implode(', ', $billState);
                $billingAddressData['country'] = $this->country->create()
                                                    ->load($billingAddress->getCountryId())
                                                    ->getName();
                $billingAddressData['telephone'] = 'T: '.$billingAddress->getTelephone();
                $returnArray['billingAddressData']['address'][] = $billingAddressData;

                // Payment Method Data
                $returnArray['paymentMethodData']['title'] = __('Payment Method');
                $returnArray['paymentMethodData']['method'] = $order->getPayment()->getMethodInstance()->getTitle();

                // Item List
                $itemCollection = $order->getAllVisibleItems();
                $_count = count($itemCollection);
                $subtotal = 0;
                $vendorSubtotal = 0;
                $totaltax = 0;
                $adminSubtotal = 0;
                $shippingamount = 0;
                $codchargesTotal = 0;

                foreach ($itemCollection as $_item) {
                    $eachItem = [];
                    $rowTotal = 0;
                    $availableSellerItem = 0;
                    $shippingcharges = 0;
                    $itemPrice = 0;
                    $sellerItemCost = 0;
                    $totaltaxPeritem = 0;
                    $codchargesPeritem = 0;
                    $sellerItemCommission = 0;
                    $sellerOrderslist = $this->saleslistFactory
                        ->create()->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('mageproduct_id', $_item->getProductId())
                        ->addFieldToFilter('order_item_id', $_item->getItemId())
                        ->setOrder('order_id', 'DESC');

                    foreach ($sellerOrderslist as $sellerItem) {
                        $availableSellerItem = 1;
                        $totalamount = $sellerItem->getTotalAmount();
                        $sellerItemCost = $sellerItem->getActualSellerAmount();
                        $sellerItemCommission = $sellerItem->getTotalCommision();
                        $shippingcharges = $sellerItem->getShippingCharges();
                        $itemPrice = $sellerItem->getMageproPrice();
                        $totaltaxPeritem = $sellerItem->getTotalTax();
                        $codchargesPeritem = $sellerItem->getCodCharges();
                    }
                    if ($availableSellerItem == 1) {
                        $sellerItemQty = $_item->getQtyOrdered();
                        $rowTotal = $itemPrice * $sellerItemQty;
                        $vendorSubtotal = $vendorSubtotal + $sellerItemCost;
                        $subtotal = $subtotal + $rowTotal;
                        $adminSubtotal = $adminSubtotal + $sellerItemCommission;
                        $totaltax = $totaltax + $totaltaxPeritem;
                        $codchargesTotal = $codchargesTotal + $codchargesPeritem;
                        $shippingamount = $shippingamount + $shippingcharges;
                        $result = [];
                        if ($options = $_item->getProductOptions()) {
                            $result = $this->setResultValue($options, $result);
                        }
                        $eachItem['productName'] = $_item->getName();
                        if ($options = $result) {
                            $eachItem = $this->setEachItemOption($options, $eachItem);
                        }
                        $eachItem['price'] = strip_tags($order->formatPrice($_item->getPrice()));
                        $eachItem['qty']['Ordered'] = $_item->getQtyOrdered() * 1;
                        $eachItem['qty']['Invoiced'] = $_item->getQtyInvoiced() * 1;
                        $eachItem['qty']['Shipped'] = $_item->getQtyShipped() * 1;
                        $eachItem['qty']['Canceled'] = $_item->getQtyCanceled() * 1;
                        $eachItem['qty']['Refunded'] = $_item->getQtyRefunded() * 1;
                        $eachItem['subTotal'] = strip_tags($order->formatPrice($rowTotal));
                        if ($paymentCode == 'mpcashondelivery') {
                            $eachItem['codCharges'] = strip_tags($order->formatPrice($codchargesPeritem));
                        }
                        $eachItem['adminComission'] = strip_tags($order->formatPrice($sellerItemCommission));
                        $eachItem['vendorTotal'] = strip_tags($order->formatPrice($sellerItemCost));
                        $returnArray['items'][] = $eachItem;
                    }
                }
                $returnArray['subtotal']['title'] = __('Subtotal');
                $returnArray['subtotal']['value'] = strip_tags($order->formatPrice($subtotal));
                $returnArray['shipping']['title'] = __('Shipping & Handling');
                $returnArray['shipping']['value'] = strip_tags($order->formatPrice($shippingamount));
                $returnArray['tax']['title'] = __('Total Tax');
                $returnArray['tax']['value'] = strip_tags($order->formatPrice($totaltax));
                $admintotaltax = 0;
                $vendortotaltax = 0;
                if (!$this->mpHelper->getConfigTaxManage()) {
                    $admintotaltax = $totaltax;
                } else {
                    $vendortotaltax = $totaltax;
                }
                if ($paymentCode == 'mpcashondelivery') {
                    $returnArray['cod']['title'] = __('Total COD Charges');
                    $returnArray['cod']['value'] = strip_tags($order->formatPrice($codchargesTotal));
                }
                $returnArray['totalOrderedAmount']['title'] = __('Total Ordered Amount');
                $returnArray['totalOrderedAmount']['value'] = strip_tags(
                    $order->formatPrice(
                        $subtotal + $shippingamount + $codchargesTotal + $totaltax
                    )
                );
                $returnArray['totalVendorAmount']['title'] = __('Total Vendor Amount');
                $returnArray['totalVendorAmount']['value'] = strip_tags(
                    $order->formatPrice(
                        $vendorSubtotal + $shippingamount + $codchargesTotal + $vendortotaltax
                    )
                );
                $returnArray['totalAdminComission']['title'] = __('Total Administration Fee');
                $returnArray['totalAdminComission']['value'] = strip_tags(
                    $order->formatPrice(
                        $adminSubtotal + $admintotaltax
                    )
                );
                $returnArray['status'] = self::SUCCESS;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Request')
                );
            }

            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid Request');

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder($id, $orderId)
    {
        $returnArray = [];
        $customerId = $id;
        $order = $this->getOrder($orderId);

        $orderId = $order->getId();
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }
            $orderHelper = $this->orderHelper;
            $flag = $orderHelper->cancelorder($order, $customerId);
            if ($flag) {
                $collection = $this->saleslistFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId);
                if ($collection->getSize() == 0) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Invalid Request')
                    );
                }
                foreach ($collection as $saleproduct) {
                    $saleproduct->setCpprostatus(2);
                    $saleproduct->setPaidStatus(2);
                    $saleproduct->save();
                    $trackingcoll = $this->mpOrdersFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter('order_id', $orderId)
                            ->addFieldToFilter('seller_id', $customerId);
                    foreach ($trackingcoll as $tracking) {
                        $tracking->setTrackingNumber('canceled');
                        $tracking->setCarrierName('canceled');
                        $tracking->setIsCanceled(1);
                        $tracking->save();
                    }
                }
                $returnArray['message'] = __('The order has been cancelled.');
                $returnArray['status'] = self::SUCCESS;
            } else {
                $returnArray['message'] = __('You are not permitted to cancel this order.');
                $returnArray['status'] = self::LOCAL_ERROR;
            }
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;

            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'] = __('The order has not been cancelled.');
            $returnArray['status'] = self::SEVERE_ERROR;

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function createCreditmemo($id, $invoiceId, $orderId, $creditMemo)
    {
        $sellerId = $id;
        $returnArray = [];
        if ($order = $this->_initOrder($orderId, $sellerId)) {
            try {
                if (!$this->isSeller($id)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Invalid Seller')
                    );
                }
                $creditMemo = $creditMemo->getData();
                $creditmemo = $this->_initOrderCreditmemo($creditMemo, $sellerId, $invoiceId, $order);
                if ($creditmemo) {
                    if (!$creditmemo->isValidGrandTotal()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The credit memo\'s total must be positive.')
                        );
                    }
                    $data = $creditMemo;

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
                        // do not allow online refund for Refund to Store Credit
                        if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Cannot create online refund for Refund to Store Credit.')
                            );
                        }
                    }
                    $creditmemo = $this->creditmemoManagementInterface->refund(
                        $creditmemo,
                        (bool) $data['do_offline'],
                        !empty($data['send_email'])
                    );

                    /** Update records */
                    $creditmemoIds = [];
                    $trackingcol1 = $this->mpOrdersFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('seller_id', $sellerId);
                    foreach ($trackingcol1 as $tracking) {
                        if ($tracking->getCreditmemoId()) {
                            $creditmemoIds = explode(',', $tracking->getCreditmemoId());
                        }
                        array_push($creditmemoIds, $creditmemo->getId());
                        $tracking->setCreditmemoId(implode(',', $creditmemoIds));
                        $tracking->save();
                    }

                    if (!empty($data['send_email'])) {
                        $this->creditmemoSender->send($creditmemo);
                    }
                    
                    $returnArray['id'] = $creditmemo->getId();
                    $returnArray['status'] = self::SUCCESS;
                    $returnArray['message'] = __('You created the credit memo.');
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $returnArray['status'] = self::LOCAL_ERROR;
                $returnArray['message'] = $e->getMessage();
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $this->createLog($e);
                $returnArray['status'] = self::SEVERE_ERROR;
                $returnArray['message'] = $e->getMessage();
                return $this->getJsonResponse($returnArray);
            }
        } else {
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid Request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function viewCreditmemo($id, $orderId, $creditmemoId)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }
            $returnArray = [];
            $customerId = $id;
            $creditmemoId = $creditmemoId;

            $helper = $this->mpHelper;
            $order = $this->getOrder($orderId);
            $paymentCode = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            $orderId = $order->getId();
            $creditmemo = $this->creditmemoRepositoryInterface->get($creditmemoId);

            $creditmemoStatus = '';
            if ($creditmemo->getState() == 1) {
                $creditmemoStatus = __('Pending');
            } elseif ($creditmemo->getState() == 2) {
                $creditmemoStatus = __('Refunded');
            } elseif ($creditmemo->getState() == 3) {
                $creditmemoStatus = __('Canceled');
            }
            $marketplaceOrders = $this->mpOrdersFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $customerId);
            $tracking = new \Magento\Framework\DataObject();
            foreach ($marketplaceOrders as $tracking) {
                $tracking = $tracking;
            }
            if (count($marketplaceOrders)) {
                $returnArray['sendmailAction'] = __('Send Email To Customer');
                $returnArray['sendmailWarning'] = __('Are you sure you want to send order email to customer?');
                $returnArray['mainHeading'] = __('Credit Memo Information');
                $returnArray['subHeading'] = __(
                    'Credit Memo #%1 - %2 | %3',
                    $creditmemo->getIncrementId(),
                    $creditmemoStatus,
                    $creditmemo->getCreatedAt()
                );
                $returnArray['orderData']['title'] = __('Order Information');
                $returnArray['orderData']['label'] = __('Order # %1', $order->getIncrementId());
                $returnArray['orderData']['statusLabel'] = __('Order Status');
                $returnArray['orderData']['statusValue'] = ucfirst($order->getStatus());
                $returnArray['orderData']['dateLabel'] = __('Order Date');
                $returnArray['orderData']['dateValue'] = $order->getCreatedAt();
                // Buyer Data
                $returnArray['buyerData']['title'] = __('Buyer Information');
                $returnArray['buyerData']['nameLabel'] = __('Customer Name').': ';
                $returnArray['buyerData']['nameValue'] = $order->getCustomerName();
                $returnArray['buyerData']['emailLabel'] = __('Email').': ';
                $returnArray['buyerData']['emailValue'] = $order->getCustomerEmail();
                // Shipping Address Data
                if (!$order->getIsVirtual()) {
                    $returnArray['shippingAddressData']['title'] = __('Shipping Address');
                    $shippingAddress = $order->getShippingAddress();
                    $shippingAddressData['name'] = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                    $shippingAddressData['street'] = $shippingAddress->getStreet()[0];
                    if (count($shippingAddress->getStreet()) > 1) {
                        if ($shippingAddress->getStreet()[1]) {
                            $shippingAddressData['street'] .= $shippingAddress->getStreet()[1];
                        }
                    }
                    $state = [
                        $shippingAddress->getCity(),
                        $shippingAddress->getRegion(),
                        $shippingAddress->getPostcode()
                    ];
                    $shippingAddressData['state'] = implode(', ', $state);
                    $shippingAddressData['country'] = $this->country->create()
                    ->load($shippingAddress->getCountryId())->getName();
                    $shippingAddressData['telephone'] = 'T: '.$shippingAddress->getTelephone();
                    $returnArray['shippingAddressData']['address'][] = $shippingAddressData;

                    // Shipping Method Data
                    $returnArray['shippingMethodData']['title'] = __('Shipping Information');
                    if ($order->getShippingDescription()) {
                        $returnArray['shippingMethodData']['method'] = strip_tags($order->getShippingDescription());
                    } else {
                        $returnArray['shippingMethodData']['method'] = __('No shipping information available');
                    }
                }

                // Billing Address Data
                $returnArray['billingAddressData']['title'] = __('Billing Address');
                $billingAddress = $order->getBillingAddress();
                $billingAddressData['name'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                $billingAddressData['street'] = $billingAddress->getStreet()[0];
                if (count($billingAddress->getStreet()) > 1) {
                    if ($billingAddress->getStreet()[1]) {
                        $billingAddressData['street'] .= $billingAddress->getStreet()[1];
                    }
                }
                $billState = [
                    $billingAddress->getCity(),
                    $billingAddress->getRegion(),
                    $billingAddress->getPostcode()
                ];
                $billingAddressData['state'] = implode(', ', $billState);
                $billingAddressData['country'] = $this->country->create()
                                                ->load($billingAddress->getCountryId())
                                                ->getName();
                $billingAddressData['telephone'] = 'T: '.$billingAddress->getTelephone();
                $returnArray['billingAddressData']['address'][] = $billingAddressData;

                // Payment Method Data
                $returnArray['paymentMethodData']['title'] = __('Payment Method');
                $returnArray['paymentMethodData']['method'] = $order->getPayment()->getMethodInstance()->getTitle();
 
                // Item List
                $itemCollection = $order->getAllVisibleItems();
                $_count = count($itemCollection);
                $subtotal = 0;
                $vendorSubtotal = 0;
                $totaltax = 0;
                $adminSubtotal = 0;
                $shippingamount = 0;
                $codchargesTotal = 0;
                foreach ($itemCollection as $_item) {
                    $eachItem = [];
                    $rowTotal = 0;
                    $availableSellerItem = 0;
                    $shippingcharges = 0;
                    $itemPrice = 0;
                    $sellerItemCost = 0;
                    $totaltaxPeritem = 0;
                    $codchargesPeritem = 0;
                    $sellerItemCommission = 0;
                    $sellerOrderslist = $this->saleslistFactory
                        ->create()->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('mageproduct_id', $_item->getProductId())
                        ->addFieldToFilter('order_item_id', $_item->getItemId())
                        ->setOrder('order_id', 'DESC');

                    foreach ($sellerOrderslist as $sellerItem) {
                        $availableSellerItem = 1;
                        $totalamount = $sellerItem->getTotalAmount();
                        $sellerItemCost = $sellerItem->getActualSellerAmount();
                        $sellerItemCommission = $sellerItem->getTotalCommision();
                        $shippingcharges = $sellerItem->getShippingCharges();
                        $itemPrice = $sellerItem->getMageproPrice();
                        $totaltaxPeritem = $sellerItem->getTotalTax();
                        $codchargesPeritem = $sellerItem->getCodCharges();
                    }
                    if ($availableSellerItem == 1) {
                        $sellerItemQty = $_item->getQtyOrdered();
                        $rowTotal = $itemPrice * $sellerItemQty;
                        $vendorSubtotal = $vendorSubtotal + $sellerItemCost;
                        $subtotal = $subtotal + $rowTotal;
                        $adminSubtotal = $adminSubtotal + $sellerItemCommission;
                        $totaltax = $totaltax + $totaltaxPeritem;
                        $codchargesTotal = $codchargesTotal + $codchargesPeritem;
                        $shippingamount = $shippingamount + $shippingcharges;
                        $result = [];
                        if ($options = $_item->getProductOptions()) {
                            $result = $this->setResultValue($options, $result);
                        }
                        $eachItem['productName'] = $_item->getName();
                        if ($options = $result) {
                            $eachItem = $this->setEachItemOption($options, $eachItem);
                        }
                        $eachItem['price'] = strip_tags($order->formatPrice($_item->getPrice()));
                        $eachItem['qty']['Ordered'] = $_item->getQtyOrdered() * 1;
                        $eachItem['qty']['Invoiced'] = $_item->getQtyInvoiced() * 1;
                        $eachItem['qty']['Shipped'] = $_item->getQtyShipped() * 1;
                        $eachItem['qty']['Canceled'] = $_item->getQtyCanceled() * 1;
                        $eachItem['qty']['Refunded'] = $_item->getQtyRefunded() * 1;
                        $eachItem['subTotal'] = strip_tags($order->formatPrice($rowTotal));
                        if ($paymentCode == 'mpcashondelivery') {
                            $eachItem['codCharges'] = strip_tags($order->formatPrice($codchargesPeritem));
                        }
                        $eachItem['adminComission'] = strip_tags($order->formatPrice($sellerItemCommission));
                        $eachItem['vendorTotal'] = strip_tags($order->formatPrice($sellerItemCost));
                        $returnArray['items'][] = $eachItem;
                    }
                }
                $returnArray['subtotal']['title'] = __('Subtotal');
                $returnArray['subtotal']['value'] = strip_tags($order->formatPrice($subtotal));
                $returnArray['shipping']['title'] = __('Shipping & Handling');
                $returnArray['shipping']['value'] = strip_tags($order->formatPrice($shippingamount));
                $returnArray['tax']['title'] = __('Total Tax');
                $returnArray['tax']['value'] = strip_tags($order->formatPrice($totaltax));
                $admintotaltax = 0;
                $vendortotaltax = 0;
                if (!$this->mpHelper->getConfigTaxManage()) {
                    $admintotaltax = $totaltax;
                } else {
                    $vendortotaltax = $totaltax;
                }
                if ($paymentCode == 'mpcashondelivery') {
                    $returnArray['cod']['title'] = __('Total COD Charges');
                    $returnArray['cod']['value'] = strip_tags($order->formatPrice($codchargesTotal));
                }
                $returnArray['totalOrderedAmount']['title'] = __('Total Ordered Amount');
                $returnArray['totalOrderedAmount']['value'] = strip_tags(
                    $order->formatPrice(
                        $subtotal + $shippingamount + $codchargesTotal + $totaltax
                    )
                );
                $returnArray['totalVendorAmount']['title'] = __('Total Vendor Amount');
                $returnArray['totalVendorAmount']['value'] = strip_tags(
                    $order->formatPrice(
                        $vendorSubtotal + $shippingamount + $codchargesTotal + $vendortotaltax
                    )
                );
                $returnArray['totalAdminComission']['title'] = __('Total Administration Fee');
                $returnArray['totalAdminComission']['value'] = strip_tags(
                    $order->formatPrice(
                        $adminSubtotal + $admintotaltax
                    )
                );
            }

            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function ship($id, $orderId, $trackingId, $carrier)
    {
        $status = 0;
        $returnArray = [];
        if (!$this->isSeller($id)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid Seller')
            );
        }
        $shipment = [];

        $message = '';
        if ($order = $this->_initOrder($orderId, $id)) {
            $sellerId = $id;
            $marketplaceOrder = $this->getOrderinfo($sellerId, $id);
            $trackingid = '';
            $carrier = '';
            $trackingData = [];
            try {
                if (!empty($trackingId)) {
                    $trackingid = $trackingId;
                    $trackingData[1]['number'] = $trackingId;
                    $trackingData[1]['carrier_code'] = 'custom';
                }
                if (!empty($carrier)) {
                    $trackingData[1]['title'] = $carrier;
                }

                if (!empty($shipment['api_shipment'])) {
                    $this->eventManager->dispatch(
                        'generate_api_shipment',
                        [
                            'api_shipment' => $shipment['api_shipment'],
                            'order_id' => $orderId,
                        ]
                    );
                    $shipmentData = $this->customerSession->getData('shipment_data');
                    $apiName = $shipmentData['api_name'];
                    $trackingid = $shipmentData['tracking_number'];
                    $trackingData[1]['number'] = $trackingid;
                    $trackingData[1]['carrier_code'] = 'custom';
                    $this->customerSession->unsetData('shipment_data');
                }
                if (empty($shipment['api_shipment']) || $trackingid != '') {
                    if ($order->canUnhold()) {
                        return $this->getJsonResponse([
                            'status' => $status,
                            'message' => 'Can not create shipment as order is in HOLD state'
                        ]);
                    } else {
                        $items = [];
                        $shippingAmount = 0;

                        $trackingsdata = $this->mpOrdersFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                $orderId
                            )->addFieldToFilter(
                                'seller_id',
                                $sellerId
                            );
                        foreach ($trackingsdata as $tracking) {
                            $shippingAmount = $tracking->getShippingCharges();
                        }

                        $collection = $this->saleslistFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                $orderId
                            )->addFieldToFilter(
                                'seller_id',
                                $sellerId
                            );
                        foreach ($collection as $saleproduct) {
                            array_push($items, $saleproduct['order_item_id']);
                        }

                        $itemsarray = $this->_getShippingItemQtys($order, $items);

                        if (count($itemsarray) > 0) {
                            $data = [
                                'send_email' => 1,
                                'create_shipping_label' => 1
                            ];
                            $shipment = false;
                            $shipmentId = 0;
                            $shipment = $this->setShipmentNew(
                                $shipment,
                                $shipmentId,
                                $orderId,
                                $order,
                                $status,
                                $itemsarray,
                                $trackingData
                            );
                            $message = $this->sendShippmentNew(
                                $shipment,
                                $data,
                                $orderId,
                                $sellerId,
                                $trackingid,
                                $carrier,
                                $message
                            );
                            $status = self::SUCCESS;
                        }
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $status = self::SEVERE_ERROR;
                $message = $e->getMessage();
            } catch (\Exception $e) {
                $status = self::LOCAL_ERROR;
                $message = $e->getMessage();
            }
            return $this->getJsonResponse(['status' => $status,'message' => $message]);
        } else {
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid Request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Prepare the shipment
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @param array $trackingData
     * @return void
     */
    public function _prepareShipment($order, $items, $trackingData)
    {
        $shipment = $this->shipmentFactory->create(
            $order,
            $items,
            $trackingData
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }

    /**
     * Get the item quantity of shipping
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @return array
     */
    public function _getShippingItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = (int)($item->getQtyOrdered() - $item->getQtyShipped());
                $_item = $item;

                // for bundle product
                $bundleitems = $this->arrayMerge([$_item], $_item->getChildrenItems());

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int)(
                                $_bundleitem->getQtyOrdered() - $item->getQtyShipped()
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
     * @inheritDoc
     */
    public function viewShipment($id, $orderId, $shipmentId)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }
            $returnArray = [];

            $customerId = $id;
            $helper = $this->mpHelper;
            $order = $this->getOrder($orderId);
            $shipment = $this->_shipmentRepository->get($shipmentId);
            $tracking = $this->getOrderinfo($customerId, $shipmentId);
            $paymentMethod = '';
            if ($order->getPayment()) {
                $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
            }
            $marketplaceOrders = $this->mpOrdersFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $customerId);

            if (count($marketplaceOrders)) {
                $returnArray['mainHeading'] = __('View Shipment Details');
                $returnArray['sendmailAction'] = __('Send Email To Customer');
                $returnArray['sendmailWarning'] = __('Are you sure you want to send shipment email to customer?');
                $returnArray['subHeading'] = __(
                    'Shipment #%1 | %2',
                    $shipment->getIncrementId(),
                    $shipment->getCreatedAt()
                );
                $returnArray['orderData']['title'] = __('Order Information');
                $returnArray['orderData']['label'] = __('Order # %1', $order->getIncrementId());
                $returnArray['orderData']['statusLabel'] = __('Order Status');
                $returnArray['orderData']['statusValue'] = ucfirst($order->getStatus());
                $returnArray['orderData']['dateLabel'] = __('Order Date');
                $returnArray['orderData']['dateValue'] = $order->getCreatedAt();
                // Buyer Data
                $returnArray['buyerData']['title'] = __('Buyer Information');
                $returnArray['buyerData']['nameLabel'] = __('Customer Name').': ';
                $returnArray['buyerData']['nameValue'] = $order->getCustomerName();
                $returnArray['buyerData']['emailLabel'] = __('Email').': ';
                $returnArray['buyerData']['emailValue'] = $order->getCustomerEmail();
                // Shipping Address Data
                if (!$order->getIsVirtual()) {
                    $returnArray['shippingAddressData']['title'] = __('Shipping Address');
                    $shippingAddress = $order->getShippingAddress();
                    $shippingAddressData['name'] = $shippingAddress->getFirstname().' '.
                        $shippingAddress->getLastname();
                    $shippingAddressData['street'] = $shippingAddress->getStreet()[0];
                    if (count($shippingAddress->getStreet()) > 1) {
                        if ($shippingAddress->getStreet()[1]) {
                            $shippingAddressData['street'] .= $shippingAddress->getStreet()[1];
                        }
                    }
                    $state = [
                        $shippingAddress->getCity(),
                        $shippingAddress->getRegion(),
                        $shippingAddress->getPostcode()
                    ];
                    $shippingAddressData['state'] = implode(', ', $state);
                    $shippingAddressData['country'] = $this->country->create()
                        ->load($shippingAddress->getCountryId())->getName();
                    $shippingAddressData['telephone'] = 'T: '.$shippingAddress->getTelephone();
                    $returnArray['shippingAddressData']['address'][] = $shippingAddressData;

                    // Shipping Method Data
                    $returnArray['shippingMethodData']['title'] = __('Shipping Information');
                    if ($order->getShippingDescription()) {
                        $returnArray['shippingMethodData']['method'] = strip_tags($order->getShippingDescription());
                    } else {
                        $returnArray['shippingMethodData']['method'] = __('No shipping information available');
                    }
                }

                // Billing Address Data
                $returnArray['billingAddressData']['title'] = __('Billing Address');
                $billingAddress = $order->getBillingAddress();
                $billingAddressData['name'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                $billingAddressData['street'] = $billingAddress->getStreet()[0];
                if (count($billingAddress->getStreet()) > 1) {
                    if ($billingAddress->getStreet()[1]) {
                        $billingAddressData['street'] .= $billingAddress->getStreet()[1];
                    }
                }
                $billState = [
                    $billingAddress->getCity(),
                    $billingAddress->getRegion(),
                    $billingAddress->getPostcode()
                ];
                $billingAddressData['state'] = implode(', ', $billState);
                $billingAddressData['country'] = $this->country->create()
                                                    ->load($billingAddress->getCountryId())
                                                    ->getName();
                $billingAddressData['telephone'] = 'T: '.$billingAddress->getTelephone();
                $returnArray['billingAddressData']['address'][] = $billingAddressData;

                // Payment Method Data
                $returnArray['paymentMethodData']['title'] = __('Payment Method');
                $returnArray['paymentMethodData']['method'] = $paymentMethod;
                
                // Shipping Carriers
                $shippingCarriers = [];
                foreach ($shipment->getTracks() as $track) {
                    $carrier = $this->carrierFactory->create($track->getCarrierCode());
                    $shippingCarriers[] = [
                        'carrier' => ($carrier) ? $carrier->getConfigData('title') : __('Custom Value'),
                        'title' => $track->getTitle(),
                        'number' => $track->getNumber()
                    ];
                }
                $returnArray['shippingCarriers'] = $shippingCarriers;
                
                // Item List
                $itemCollection = $order->getAllVisibleItems();
                $_count = count($itemCollection);
                
                foreach ($itemCollection as $_item) {
                    $eachItem = [];
                    $availableSellerItem = 0;
                    $sellerOrderslist = $this->saleslistFactory
                        ->create()->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('mageproduct_id', $_item->getProductId())
                        ->addFieldToFilter('order_item_id', $_item->getItemId())
                        ->setOrder('order_id', 'DESC');

                    foreach ($sellerOrderslist as $sellerItem) {
                        $availableSellerItem = 1;
                    }
                    if ($availableSellerItem == 1) {
                        $result = [];
                        if ($options = $_item->getProductOptions()) {
                            $result = $this->setResultValue($options, $result);
                        }
                        $eachItem['productName'] = $_item->getName();
                        $eachItem['sku'] = $_item->getSku();
                        if ($options = $result) {
                            $eachItem = $this->setEachItemOption($options, $eachItem);
                        }
                        $eachItem['qty'] = $_item->getQtyShipped() * 1;
                        $returnArray['items'][] = $eachItem;
                    }
                }
                $returnArray['status'] = self::SUCCESS;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Request')
                );
            }

            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid Request');

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function mailToAdmin($id, $subject, $query)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }
            $returnArray = [];
            $customerId = $id;

            $helper = $this->mpHelper;

            $seller = $this->customerFactory->create()->load($customerId);

            $sellerName = $seller->getName();
            $sellerEmail = $seller->getEmail();

            $adminStoremail = $helper->getAdminEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $emailTemplateVariables = [];
            $senderInfo = [];
            $receiverInfo = [];
            $emailTemplateVariables['myvar1'] = $adminUsername;
            $emailTemplateVariables['myvar2'] = $sellerName;
            $emailTemplateVariables['subject'] = $subject;
            $emailTemplateVariables['myvar3'] = $query;
            $senderInfo = [
                'name' => $sellerName,
                'email' => $sellerEmail,
            ];
            $receiverInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];

            $this->emailHelper
                ->askQueryAdminEmail($emailTemplateVariables, $senderInfo, $receiverInfo);

            $returnArray['message'] = __('The message has been sent.');
            $returnArray['status'] = self::SUCCESS;

            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid Request');

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function mailToSeller($id, $subject, $query, $productId, $customerEmail, $customerName)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }
            $returnArray = [];

            $sellerId = $id;

            $seller = $this->customerFactory->create()->load($sellerId);
            $sellerEmail = $seller->getEmail();
            $sellerName = $seller->getFirstname().' '.$seller->getLastname();

            $buyerEmail = $customerEmail;
            $buyerName = $customerName;

            if (strlen($buyerName) < 2) {
                $buyerName = 'Guest';
            }
            $emailTemplateVariables = [];
            $emailTemplateVariables['myvar1'] = $sellerName;
            if ($productId) {
                $emailTemplateVariables['myvar3'] = $this->_product->load(
                    $data['product-id']
                )->getName();
            }
            $emailTemplateVariables['myvar4'] = $query;
            $emailTemplateVariables['myvar5'] = $buyerEmail;
            $emailTemplateVariables['myvar6'] = $subject;
            $senderInfo = [
                'name' => $buyerName,
                'email' => $buyerEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $sellerEmail,
            ];
            $data['email'] = $customerEmail;
            $data['name'] = $customerName;
            $data['product-id'] = $productId;
            $data['ask'] = $query;
            $data['subject'] = $subject;
            $data['seller-id'] = $sellerId;

            $this->emailHelper->sendQuerypartnerEmail(
                $data,
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );

            $returnArray['status'] = self::SUCCESS;
            $returnArray['message'] = __('Mail sent successfully !!');
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid Request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function becomePartner($id, $shopUrl, $isSeller)
    {
        try {
            if ($this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('already seller')
                );
            }

            $shop_urlcount = $this->sellerFactory->create()->getCollection()
                ->addFieldToFilter(
                    'shop_url',
                    $shopUrl
                );
            if (!count($shop_urlcount)) {
                $sellerId = $id;
                $status = $this->mpHelper->getIsPartnerApproval() ? 0 : 1;
                $model = $this->sellerFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('shop_url', $shopUrl);
                if (!count($model)) {
                    if (isset($isSeller) && $isSeller) {
                        $autoId = 0;
                        $collection = $this->sellerFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId);
                        foreach ($collection as $value) {
                            $autoId = $value->getId();
                        }
                        $value = $this->sellerFactory->create()->load($autoId);
                        $value->setData('is_seller', $status);
                        $value->setData('shop_url', $shopUrl);
                        $value->setData('seller_id', $sellerId);
                        $value->setCreatedAt($this->date->gmtDate());
                        $value->setUpdatedAt($this->date->gmtDate());
                        $value->save();

                        $returnArray['message'] = __('Profile information was successfully saved');
                        $returnArray['status'] = self::SUCCESS;
                        return $this->getJsonResponse($returnArray);
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Please confirm that you want to become seller.')
                        );
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Shop URL already exist please set another.')
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Shop URL already exist please set another.')
                );
            }
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSellerReviews($id)
    {
        $filter1 = $this->filter->create();
        $filter2 = $this->filter->create();
        $filterGroup1 = $this->filterGroup->create();
        $filterGroup2 = $this->filterGroup->create();
        $filter1->setField("seller_id")
            ->setValue($id)
            ->setConditionType("eq");
        $filter2->setField("status")
            ->setValue(1)
            ->setConditionType("eq");
        $filterGroup1->setFilters([$filter1]);
        $filterGroup2->setFilters([$filter2]);
        $criteria = $this->searchCriteria->setFilterGroups([
            $filterGroup1,$filterGroup2
        ]);
        return $this->feedbackRepo->getList($criteria);
    }

    /**
     * @inheritDoc
     */
    public function makeSellerReview($id, \Webkul\MpApi\Api\Data\FeedbackInterface $feedback)
    {
        $data = $feedback->convertToArray();
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid Seller')
                );
            }
            $returnArray = [];
            $errors = $this->validateReviewPost($data);
            if (count($errors) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(implode(', ', $errors))
                );
            }
            $data['created_at'] = $this->date->gmtDate();

            $data['seller_id'] = $id;
            if (!isset($data['buyer_id']) || !$data['buyer_id']) {
                $data['buyer_id'] = null;
            }

            if (!isset($data['buyer_email']) && $data['buyer_id']) {
                $data['buyer_email'] = $this->customerFactory->create()->load($data['buyer_id'])->getEmail();
            }

            $customerId = $data['buyer_id'];
            $sellerId = $id;
            $feedbackcount = 0;
            $collectionfeed = $this->feedbackcountFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('buyer_id', $customerId)
                ->addFieldToFilter('seller_id', $sellerId);

            foreach ($collectionfeed as $value) {
                $feedcountid = $value->getEntityId();
                $ordercount = $value->getOrderCount();
                $feedbackcount = $value->getFeedbackCount();
                $value->setFeedbackCount($feedbackcount + 1);
                $value->save();
            }
            $reviewId = $this->feedbackFactory->create()->setData($data)->save()->getId();
            $returnArray['review_id'] = $reviewId;
            $returnArray['message'] = __('Your review successfully saved');
            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid Request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function getReview($reviewId)
    {
        $filter1 = $this->filter->create();
        $filter2 = $this->filter->create();
        $filterGroup1 = $this->filterGroup->create();
        $filterGroup2 = $this->filterGroup->create();
        $filter1->setField("entity_id")
            ->setValue($reviewId)
            ->setConditionType("eq");
        $filter2->setField("status")
            ->setValue(0)
            ->setConditionType("neq");
        $filterGroup1->setFilters([$filter1]);
        $filterGroup2->setFilters([$filter2]);
        $criteria = $this->searchCriteria->setFilterGroups([
            $filterGroup1,$filterGroup2
        ]);
        return $this->feedbackRepo->getList($criteria);
    }

    /**
     * Validate Review Post data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function validateReviewPost($data)
    {
        $errors = [];
        if (!is_array($data) || count($data) == 0) {
            return ['invalid post data'];
        }
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'feed_price':
                    if (($value % 20 !=0) || ($value > 100 || $value <= 0)) {
                        $errors[] = __('invalid feed price');
                    }
                    break;
                case 'feed_value':
                    if (($value % 20 !=0) || ($value > 100 || $value <= 0)) {
                        $errors[] = __('invalid feed value');
                    }
                    break;
                case 'feed_quality':
                    if (($value % 20 !=0) || ($value > 100 || $value <= 0)) {
                        $errors[] = __('invalid feed quality');
                    }
                    break;
                case 'buyer_email':
                    $emailValidator = new \Zend\Validator\EmailAddress();

                    if (!$emailValidator->isValid($value)) {
                        $errors[] = __('invalid email address');
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function getLandingPageData()
    {
        try {
            $width = 600;
            $returnArray = [];
            $height = $width / 2;
            $allSellers = [];
            $helper = $this->mpHelper;
            $pageLayout = $helper->getPageLayout();
            if ($pageLayout == '1') {
                $marketplacelabel1 = $helper->getMarketplacelabel1();
                $marketplacelabel2 = $helper->getMarketplacelabel2();
                $marketplacelabel3 = $helper->getMarketplacelabel3();
                $marketplacelabel4 = $helper->getMarketplacelabel4();
                $bannerDisplay = $helper->getDisplayBanner();
                $bannerImage = $helper->getBannerImage();
                $bannerContent = $helper->getBannerContent();
                $iconsDisplay = $helper->getDisplayIcon();
                $iconImage1 = $helper->getIconImage1();
                $iconImage1Label = $helper->getIconImageLabel1();
                $iconImage2 = $helper->getIconImage2();
                $iconImage2Label = $helper->getIconImageLabel2();
                $iconImage3 = $helper->getIconImage3();
                $iconImage3Label = $helper->getIconImageLabel3();
                $iconImage4 = $helper->getIconImage4();
                $iconImage4Label = $helper->getIconImageLabel4();
                $marketplacebutton = $helper->getMarketplacebutton();
                $marketplaceprofile = $helper->getMarketplaceprofile();
                if ($iconsDisplay) {
                    $returnArray['icons'][] = ['image' => $iconImage1, 'label' => $iconImage1Label];
                    $returnArray['icons'][] = ['image' => $iconImage2, 'label' => $iconImage2Label];
                    $returnArray['icons'][] = ['image' => $iconImage3, 'label' => $iconImage3Label];
                    $returnArray['icons'][] = ['image' => $iconImage4, 'label' => $iconImage4Label];
                }
                $returnArray['labels'][] = ['label' => $marketplacelabel1];
                $returnArray['labels'][] = ['label' => $marketplacelabel2];
                $returnArray['labels'][] = ['label' => $marketplacelabel3];
                $returnArray['labels'][] = ['label' => $marketplacelabel4];
                $returnArray['aboutImage'] = strip_tags($marketplaceprofile);
            } elseif ($pageLayout == '2') {
                $bannerDisplay = $helper->getDisplayBannerLayout2();
                $bannerImage = $helper->getBannerImageLayout2();
                $bannerContent = $helper->getBannerContentLayout2();
                $marketplacebutton = $helper->getBannerButtonLayout2();
            } elseif ($pageLayout == '3') {
                $bannerDisplay = $helper->getDisplayBannerLayout3();
                $bannerImage = $helper->getBannerImageLayout3();
                $bannerContent = $helper->getBannerContentLayout3();
                $marketplacebutton = $helper->getBannerButtonLayout3();
                $marketplacelabel1 = $helper->getMarketplacelabel1Layout3();
                $marketplacelabel2 = $helper->getMarketplacelabel2Layout3();
                $marketplacelabel3 = $helper->getMarketplacelabel3Layout3();
                $iconsDisplay = $helper->getDisplayIconLayout3();
                $iconImage1 = $helper->getIconImage1Layout3();
                $iconImage1Label = $helper->getIconImageLabel1Layout3();
                $iconImage2 = $helper->getIconImage2Layout3();
                $iconImage2Label = $helper->getIconImageLabel2Layout3();
                $iconImage3 = $helper->getIconImage3Layout3();
                $iconImage3Label = $helper->getIconImageLabel3Layout3();
                $iconImage4 = $helper->getIconImage4Layout3();
                $iconImage4Label = $helper->getIconImageLabel4Layout3();
                $iconImage5 = $helper->getIconImage5Layout3();
                $iconImage5Label = $helper->getIconImageLabel5Layout3();
                if ($iconsDisplay) {
                    $returnArray['icons'][] = ['image' => $iconImage1, 'label' => $iconImage1Label];
                    $returnArray['icons'][] = ['image' => $iconImage2, 'label' => $iconImage2Label];
                    $returnArray['icons'][] = ['image' => $iconImage3, 'label' => $iconImage3Label];
                    $returnArray['icons'][] = ['image' => $iconImage4, 'label' => $iconImage4Label];
                    $returnArray['icons'][] = ['image' => $iconImage5, 'label' => $iconImage5Label];
                }
                $returnArray['labels'][] = ['label' => $marketplacelabel1];
                $returnArray['labels'][] = ['label' => $marketplacelabel2];
                $returnArray['labels'][] = ['label' => $marketplacelabel3];
            }
            /** Order collection */
            $sellers_order = $this->mpOrdersFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter(
                    'invoice_id',
                    ['neq' => 0]
                )
                ->addFieldToSelect('seller_id');

            $sellers_order->getSelect()
                ->join(
                    ['ccp' => $this->resourceConnection->getTableName('marketplace_userdata')],
                    'ccp.seller_id = main_table.seller_id',
                    ['is_seller' => 'is_seller']
                )
                ->where('ccp.is_seller = 1');

            $sellers_order->getSelect()
                ->columns('COUNT(*) as countOrder')->group('seller_id');
            $sellerArr = [];
            foreach ($sellers_order as $value) {
                if ($helper->getSellerProCount($value['seller_id'])) {
                    $sellerArr[$value['seller_id']] = [];
                    $seller_products = $this->saleslistFactory
                        ->create()->getCollection()
                                        ->addFieldToFilter('main_table.seller_id', $value['seller_id'])
                                        ->addFieldToFilter('cpprostatus', 1)
                                        ->addFieldToSelect('mageproduct_id')
                                        ->addFieldToSelect('magequantity');
                    $seller_products->getSelect()
                                        ->columns('SUM(magequantity) as countOrderedProduct')
                                        ->group('main_table.mageproduct_id');
                    $seller_products->getSelect()
                                        ->joinLeft(
                                            ['ccp' => $this->resourceConnection->getTableName('marketplace_product')],
                                            'ccp.mageproduct_id = main_table.mageproduct_id',
                                            ['status' => 'status']
                                        )->where('ccp.status = 1');

                    $seller_products->setOrder('countOrderedProduct', 'DESC')->setPageSize(3);
                    foreach ($seller_products as $seller_product) {
                        array_push($sellerArr[$value['seller_id']], $seller_product['mageproduct_id']);
                    }
                }
            }
            if (count($sellerArr) != 4) {
                $i = count($sellerArr);
                $count_pro_arr = [];
                $sellerProductColl = $this->mpProductFactory
                        ->create()->getCollection()->addFieldToFilter('status', 1);
                $sellerProductColl
                    ->getSelect()->join(
                        ['ccp' => $this->resourceConnection->getTableName('marketplace_userdata')],
                        'ccp.seller_id = main_table.seller_id',
                        ['is_seller' => 'is_seller']
                    )
                    ->where('ccp.is_seller = 1');

                $sellerProductColl->getSelect()->columns('COUNT(*) as countOrder')->group('main_table.seller_id');

                foreach ($sellerProductColl as $value) {
                    if (!isset($count_pro_arr[$value['seller_id']])) {
                        $count_pro_arr[$value['seller_id']] = [];
                    }
                    $count_pro_arr[$value['seller_id']] = $value['countOrder'];
                }
                arsort($count_pro_arr);
                foreach ($count_pro_arr as $procountSellerId => $procount) {
                    if ($i <= 4) {
                        if ($helper->getSellerProCount($procountSellerId)) {
                            $sellerArr[$procountSellerId] = $this->checkProcountSetOrNot(
                                $sellerArr,
                                $procountSellerId
                            );
                            $sellerProductColl = $this->mpProductFactory
                                    ->create()->getCollection()
                                    ->addFieldToFilter('seller_id', $procountSellerId)
                                    ->addFieldToFilter('status', 1)
                                    ->setPageSize(3);
                            $sellerArr[$procountSellerId] = $this->setSellerProductId(
                                $sellerProductColl,
                                $sellerArr,
                                $procountSellerId
                            );
                        }
                    }
                    ++$i;
                }
            }
            if ($bannerDisplay) {
                $returnArray['bannerImage'] = $bannerImage;
                $returnArray['banner'][0]['label'] = $marketplacebutton;
                $returnArray['banner'][0]['content'] = strip_tags($bannerContent);
            }
            $i = 0;
            $count = count($sellerArr);
            $logowidth = $logoheight = $width / 4;
            foreach ($sellerArr as $seller_id => $products) {
                $products = array_unique($products);
                $eachSeller = [];
                ++$i;
                $seller = $this->customerFactory->create()->load($seller_id);
                $seller_product_count = 0;
                $profileurl = 0;
                $shoptitle = '';
                $logoImage = 'noimage.png';
                $seller_product_count = $helper->getSellerProCount($seller_id);
                $seller_data = $this->sellerFactory->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $seller_id
                    );
                foreach ($seller_data as $seller_data_result) {
                    $profileurl = $seller_data_result->getShopUrl();
                    $shoptitle = $seller_data_result->getShopTitle();
                    $logoImage = $seller_data_result->
                    getlogoPic() == '' ? 'noimage.png' : $seller_data_result->getLogoPic();
                }
                if (!$shoptitle) {
                    $shoptitle = $seller->getName();
                }
                $logoUrl = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).'avatar'.DS.$logoImage;

                if (!isset($products[0])) {
                    $products[0] = 0;
                    $seller_product_row = $this->mpProductFactory
                                    ->create()->getCollection()
                                        ->addFieldToFilter('seller_id', $seller_id)
                                        ->addFieldToFilter('status', 1);
                    if (isset($products[1])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[1]]);
                    }
                    if (isset($products[2])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[2]]);
                    }
                    $seller_product_row->getSelect()
                                        ->columns('COUNT(*) as countproducts')
                                        ->group('seller_id');
                    foreach ($seller_product_row as $seller_product_row_data) {
                        $products[0] = $seller_product_row_data['mageproduct_id'];
                    }
                }
                if (!isset($products[1])) {
                    $products[1] = 0;
                    $seller_product_row = $this->mpProductFactory
                                    ->create()->getCollection()
                                        ->addFieldToFilter('seller_id', $seller_id)
                                        ->addFieldToFilter('status', 1);
                    if (isset($products[0])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[0]]);
                    }
                    if (isset($products[2])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[2]]);
                    }
                    $seller_product_row->getSelect()
                                        ->columns('COUNT(*) as countproducts')
                                        ->group('seller_id');
                    foreach ($seller_product_row as $seller_product_row_data) {
                        $products[1] = $seller_product_row_data['mageproduct_id'];
                    }
                }
                if (!isset($products[2])) {
                    $products[2] = 0;
                    $seller_product_row = $this->mpProductFactory
                                        ->create()->getCollection()
                                        ->addFieldToFilter('seller_id', $seller_id)
                                        ->addFieldToFilter('status', 1);
                    if (isset($products[1])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[1]]);
                    }
                    if (isset($products[0])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[0]]);
                    }
                    $seller_product_row->getSelect()
                                        ->columns('COUNT(*) as countproducts')
                                        ->group('seller_id');
                    foreach ($seller_product_row as $seller_product_row_data) {
                        $products[2] = $seller_product_row_data['mageproduct_id'];
                    }
                }
                
                if ($products[0]) {
                    $product_1 = $this->productRepoInterface->getById($products[0]);
                    $eachSeller['products'][] = [
                        'id' => $product_1->getId(),
                        'name' => $product_1->getName(),
                        'type' => $product_1->getTypeId(),
                        'thumbnail' => $this->getImageUrl(
                            $product_1,
                            $width / 2.5,
                            'product_page_image_large'
                        )
                    ];
                }
                if ($products[1]) {
                    $product_2 = $this->productRepoInterface->getById($products[1]);
                    $eachSeller['products'][] = [
                        'id' => $product_2->getId(),
                        'name' => $product_2->getName(),
                        'type' => $product_2->getTypeId(),
                        'thumbnail' => $this->getImageUrl(
                            $product_2,
                            $width / 2.5,
                            'product_page_image_large'
                        )
                    ];
                }
                if ($products[2]) {
                    $product_3 = $this->productRepoInterface->getById($products[2]);
                    $eachSeller['products'][] = [
                        'id' => $product_3->getId(),
                        'name' => $product_3->getName(),
                        'type' => $product_3->getTypeId(),
                        'thumbnail' => $this->getImageUrl(
                            $product_3,
                            $width / 2.5,
                            'product_page_image_large'
                        )
                    ];
                }

                $eachSeller['shopTitle'] = $shoptitle;
                $eachSeller['profileurl'] = $profileurl;
                $eachSeller['sellerIcon'] = $logoUrl;
                $eachSeller['sellerProductCount'] = $seller_product_count;
                $allSellers[] = $eachSeller;
            }
            $returnArray['sellers'] = $allSellers;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Initialize order model instance.
     *
     * @param int $id
     * @param int $sellerId
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder($id, $sellerId)
    {
        try {
            if (!$id) {
                return false;
            }
            $order = $this->getOrder($id);
            $tracking = $this->getOrderinfo($sellerId, $id);
            if (!empty($tracking)) {
                return $order;
            }
            
            return false;
        } catch (\NoSuchEntityException $e) {
            return false;
        } catch (\InputException $e) {
            return false;
        }
    }

    /**
     * Get item quantities.
     *
     * @param object $order
     * @param array $items
     *
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
                $bundleitems = $this->arrayMerge([$_item], $_item->getChildrenItems());

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
     * Get requested item data.
     *
     * @param array $refundData
     * @param object $order
     * @param array $items
     *
     * @return array
     */
    protected function _getItemData($refundData, $order, $items)
    {
        $data['items'] = [];
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)
                && isset($refundData['items'][$item->getItemId()]->getData()['qty'])) {
                    $itemQty = (int)$refundData['items'][$item->getItemId()]->getData()['qty'];
                $data['items'][$item->getItemId()]['qty'] = $itemQty;
                $_item = $item;
                // for bundle product
                $bundleitems = $this->arrayMerge([$_item], $_item->getChildrenItems());
                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $itemQty = (int)$refundData['items'][$_bundleitem->getItemId()]->getData()['qty'];
                            $data['items'][$_bundleitem->getItemId()]['qty'] = $itemQty;
                        }
                    }
                }
            } else {
                if (!$item->getParentItemId()) {
                    $data['items'][$item->getItemId()]['qty'] = 0;
                }
            }
        }
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }

        return $qtys;
    }

    /**
     * Initialize creditmemo model instance.
     *
     * @param int $creditmemoId
     * @param int $orderId
     * @param int $sellerId
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    protected function _initCreditmemo($creditmemoId, $orderId, $sellerId)
    {
        $creditmemo = false;
        if (!$creditmemoId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->creditmemoRepositoryInterface->get($creditmemoId);
        if (!$creditmemoId) {
            return false;
        }
        try {
            $tracking = null;
            $marketplaceOrder = $this->mpOrdersFactory->create();
            $model = $marketplaceOrder
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )
                ->addFieldToFilter(
                    'order_id',
                    $orderId
                );
            foreach ($model as $tracking) {
                $marketplaceOrder = $tracking;
            }
            $tracking = $marketplaceOrder;

            if (count($tracking)) {
                $creditmemoArr = explode(',', $tracking->getCreditmemoId());
                if (in_array($creditmemoId, $creditmemoArr)) {
                    return $creditmemo;
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->createLog($e);
            return false;
        }
    }

    /**
     * Return the seller Order data.
     *
     * @param int $sellerId
     * @param int $orderId
     *
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function getOrderinfo($sellerId, $orderId = '')
    {
        $model = $this->mpOrdersFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            )
            ->addFieldToFilter(
                'order_id',
                $orderId
            )->getFirstItem();
        if ($model->getId()) {
            return $model;
        }

        return [];
    }

    /**
     * Return the Customer seller status.
     *
     * @param int $id
     *
     * @return bool|0|1
     */
    public function isSeller($id)
    {
        $sellerId = '';
        $sellerStatus = 0;
        $model = $this->sellerFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $id
            );
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }

    /**
     * Initialize creditmemo model for invoice.
     *
     * @param int $invoiceId
     * @param \Magento\Sales\Model\Order $order
     *
     * @return $this|bool
     */
    protected function _initCreditmemoInvoice($invoiceId, $order)
    {
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }

        return false;
    }

    /**
     * Initialize creditmemo model instance.
     *
     * @param array $refundData
     * @param int $sellerId
     * @param int $invoiceId
     * @param object $order
     *
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     */
    protected function _initOrderCreditmemo($refundData, $sellerId, $invoiceId, $order)
    {
        $creditmemo = false;

        $sellerId = $sellerId;
        $orderId = $order->getId();

        $invoice = $this->_initCreditmemoInvoice($invoiceId, $order);
        $items = [];
        $itemsarray = [];
        $shippingAmount = 0;
        $codcharges = 0;
        $paymentCode = '';
        $paymentMethod = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }
        $trackingsdata = $this->mpOrdersFactory->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('seller_id', $sellerId);
        foreach ($trackingsdata as $tracking) {
            $shippingAmount = $tracking->getShippingCharges();
            if ($paymentCode == 'mpcashondelivery') {
                $codcharges = $tracking->getCodCharges();
            }
        }
        $codCharges = 0;
        $tax = 0;
        $collection = $this->saleslistFactory->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('seller_id', $sellerId);
        foreach ($collection as $saleproduct) {
            if ($paymentCode == 'mpcashondelivery') {
                $codCharges = $codCharges + $saleproduct->getCodCharges();
            }
            $tax = $tax + $saleproduct->getTotalTax();
            array_push($items, $saleproduct['order_item_id']);
        }

        $savedData = $this->_getItemData($refundData, $order, $items);
        $qtys = [];
        foreach ($savedData as $orderItemId => $itemData) {
            if (isset($itemData['qty'])) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
            if (isset($refundData['items'][$orderItemId]->getData()['back_to_stock'])) {
                $backToStock[$orderItemId] = true;
            }
        }

        if (empty($refundData['shipping_amount'])) {
            $refundData['shipping_amount'] = 0;
        }
        if (empty($refundData['adjustment_positive'])) {
            $refundData['adjustment_positive'] = 0;
        }
        if (empty($refundData['adjustment_negative'])) {
            $refundData['adjustment_negative'] = 0;
        }
        if (!$shippingAmount >= $refundData['shipping_amount']) {
            $refundData['shipping_amount'] = 0;
        }
        $refundData['qtys'] = $qtys;
        if ($invoice) {
            $creditmemo = $this->creditmemoFactory->createByInvoice(
                $invoice,
                $refundData
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

        return $creditmemo;
    }

    /**
     * GetImageUrl get Product Image.
     *
     * @param Magento\Catalog\Model\product $_product
     * @param float                         $resize
     * @param string                        $imageType
     * @param bool                          $keepFrame
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getImageUrl(
        $_product,
        $resize,
        $imageType = 'product_page_image_large',
        $keepFrame = true
    ) {
        return $this->imageHelper
                ->init($_product, $imageType)
                ->keepFrame($keepFrame)
                ->resize($resize)
                ->getUrl();
    }

    /**
     * GetJsonResponse returns json response.
     *
     * @param array $responseContent
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    protected function getJsonResponse($responseContent = [])
    {
        $res = $this->responseInterface;
        $res->setItem($responseContent);
        
        return $res->getData();
    }

    /**
     * Create Log
     *
     * @param object $object
     * @param boolean $info
     */
    public function createLog($object, $info = false)
    {
        $myLogger = $this->logger;
        if ($info) {
            $myLogger->info($info);
        }
        $myLogger->debug($object);
    }

    /**
     * @inheritDoc
     */
    public function createAccount(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $password,
        $isSeller,
        $profileurl,
        $registered
    ) {
        $customerData = [];
        $returnArray = [];
        $customerData["email"] = $customer->getEmail();
        $customerData["firstname"] = $customer->getFirstname();
        $customerData["lastname"] = $customer->getLastname();
        $customerData["storeId"] = $customer->getStoreId();
        $customerData["websiteId"] = $customer->getWebsiteId();
        $email = $customerData['email'];
        if (isset($customerData['email']) &&
            isset($customerData['firstname']) &&
            isset($customerData['lastname']) &&
            isset($customerData['storeId']) &&
            isset($customerData['websiteId']) &&
            isset($isSeller) &&
            isset($profileurl) &&
            isset($password) &&
            isset($registered)
        ) {
            if ((int) $registered == 1) {
                $customer = $this->customerFactory->create();
                
                $customer->setWebsiteId($customerData['websiteId'])->loadByEmail($email);

                if ($customer->getId()) {
                    $sellerCreated = $this->createSeller($customer, $profileurl);
                    return $this->responseForCreateAccount($sellerCreated, $customer, $returnArray, $profileurl);
                } else {
                    $returnArray['status'] = self::SEVERE_ERROR;
                    $returnArray['message'] = __('Invalid Request');
                    return $this->getJsonResponse($returnArray);
                }
            } else {
                if (!empty($isSeller) && !empty($profileurl) && $isSeller == 1) {
                    $model = $this->sellerFactory->create()->getCollection()->addFieldToFilter(
                        'shop_url',
                        $profileurl
                    );
                    if ($model->getSize() == 0) {
                        try {
                            $customer = $this->accountManagement->createAccount($customer, $password, "");
                            $sellerCreated = $this->createSeller($customer, $profileurl);
                            return $this->responseForCreateAccount(
                                $sellerCreated,
                                $customer,
                                $returnArray,
                                $profileurl
                            );
                        } catch (\Exception $e) {
                            $returnArray['status'] = self::SEVERE_ERROR;
                            $returnArray['message'] = $e->getMessage();
                            return $this->getJsonResponse($returnArray);
                        }
                    } else {
                        $returnArray['status'] = self::SEVERE_ERROR;
                        $returnArray['message'] = __(
                            'Sorry! But this shop name is not available, please set another shop name.'
                        );
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray['status'] = self::SEVERE_ERROR;
                    $returnArray['message'] = __('Invalid params');
                    return $this->getJsonResponse($returnArray);
                }
            }
        } else {
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Invalid params');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Interface create seller account.
     *
     * @param object $customer
     * @param string $profileurl
     *
     * @return boolean
     */
    private function createSeller($customer, $profileurl)
    {
        $profileurlcount = $this->sellerFactory->create()->getCollection();
        $profileurlcount->addFieldToFilter(
            ['shop_url','seller_id'],
            [$profileurl,$customer->getId()]
        );
        if ($profileurlcount->getSize() == 0) {
            $status = $this->mpHelper->getIsPartnerApproval() ? 0 : 1;
            $customerid = $customer->getId();
            $model = $this->sellerFactory->create();
            $model->setData('is_seller', $status);
            $model->setData('shop_url', $profileurl);
            $model->setData('seller_id', $customerid);
            $model->setData('store_id', 0);
            $model->setCreatedAt($this->date->gmtDate());
            $model->setUpdatedAt($this->date->gmtDate());
            if ($status == 0) {
                $model->setAdminNotification(1);
            }
            $model->save();
            $loginUrl = $this->urlInterface->getUrl("marketplace/account/dashboard");
            $this->customerSession->setBeforeAuthUrl($loginUrl);
            $this->customerSession->setAfterAuthUrl($loginUrl);

            $helper = $this->mpHelper;
            if ($helper->getAutomaticUrlRewrite()) {
                $this->createSellerPublicUrls($profileurl);
            }
            $adminStoremail = $helper->getAdminEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';
            $senderInfo = [
                'name' => $customer->getFirstName().' '.$customer->getLastName(),
                'email' => $customer->getEmail(),
            ];
            $receiverInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $emailTemplateVariables['myvar1'] = $customer->getFirstName().' '.
            $customer->getLastName();
            $emailTemplateVariables['myvar2'] = $this->backendUrl->getUrl(
                'customer/index/edit',
                ['id' => $customer->getId()]
            );
            $emailTemplateVariables['myvar3'] = 'Admin';

            $this->emailHelper->sendNewSellerRequest(
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create seller public URLs.
     *
     * @param string $profileurl
     */
    private function createSellerPublicUrls($profileurl = '')
    {
        if ($profileurl) {
            $getCurrentStoreId = $this->mpHelper->getCurrentStoreId();

            /**
             * Set Seller Profile Url
             */
            $sourceProfileUrl = 'marketplace/seller/profile/shop/'.$profileurl;
            $requestProfileUrl = $profileurl;
            /**
             * Check if already exist in url rewrite model
             */
            $urlId = '';
            $profileRequestUrl = '';
            $urlCollectionData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceProfileUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $profileRequestUrl = $value->getRequestPath();
            }
            if ($profileRequestUrl != $requestProfileUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceProfileUrl)
                    ->setRequestPath($requestProfileUrl)
                    ->save();
            }

            /**
             * Set Seller Collection Url
             */
            $sourceCollectionUrl = 'marketplace/seller/collection/shop/'.$profileurl;
            $requestCollectionUrl = $profileurl.'/collection';
            /**
             * Check if already rexist in url rewrite model
             */
            $urlId = '';
            $collectionRequestUrl = '';
            $urlCollectionData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceCollectionUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $collectionRequestUrl = $value->getRequestPath();
            }
            if ($collectionRequestUrl != $requestCollectionUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceCollectionUrl)
                    ->setRequestPath($requestCollectionUrl)
                    ->save();
            }

            /**
             * Set Seller Feedback Url
             */
            $sourceFeedbackUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
            $requestFeedbackUrl = $profileurl.'/feedback';
            /**
             * Check if already rexist in url rewrite model
             */
            $urlId = '';
            $feedbackRequestUrl = '';
            $urlFeedbackData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceFeedbackUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlFeedbackData as $value) {
                $urlId = $value->getId();
                $feedbackRequestUrl = $value->getRequestPath();
            }
            if ($feedbackRequestUrl != $requestFeedbackUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceFeedbackUrl)
                    ->setRequestPath($requestFeedbackUrl)
                    ->save();
            }

            /**
             * Set Seller Location Url
             */
            $sourceLocationUrl = 'marketplace/seller/location/shop/'.$profileurl;
            $requestLocationUrl = $profileurl.'/location';
            /**
             * Check if already rexist in url rewrite model
             */
            $urlId = '';
            $locationRequestUrl = '';
            $urlLocationData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceLocationUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlLocationData as $value) {
                $urlId = $value->getId();
                $locationRequestUrl = $value->getRequestPath();
            }
            if ($locationRequestUrl != $requestLocationUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceLocationUrl)
                    ->setRequestPath($requestLocationUrl)
                    ->save();
            }

            /**
             * Set Seller Policy Url
             */
            $sourcePolicyUrl = 'marketplace/seller/policy/shop/'.$profileurl;
            $requestPolicyUrl = $profileurl.'/policy';
            /**
             * Check if already rexist in url rewrite model
             */
            $urlId = '';
            $policyRequestUrl = '';
            $urlPolicyData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourcePolicyUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlPolicyData as $value) {
                $urlId = $value->getId();
                $policyRequestUrl = $value->getRequestPath();
            }
            if ($policyRequestUrl != $requestPolicyUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourcePolicyUrl)
                    ->setRequestPath($requestPolicyUrl)
                    ->save();
            }
        }
    }

    /**
     * Merge two arrays.
     *
     * @param array $item
     * @param array $data
     *
     * @return array
     */
    private function arrayMerge($item, $data)
    {
        return array_merge($item, $data);
    }

    /**
     * Set collect cod status to enable.
     *
     * @param array $saleslistColl
     */
    public function setCollectCodStatusEnable($saleslistColl)
    {
        foreach ($saleslistColl as $saleslist) {
            $saleslist->setCollectCodStatus(1);
            $saleslist->save();
        }
    }

    /**
     * Set result value in product items.
     *
     * @param array $options
     * @param array $result
     *
     * @return array
     */
    public function setResultValue($options, $result)
    {
        if (isset($options['options'])) {
            $result = $this->arrayMerge($result, $options['options']);
        }
        if (isset($options['additional_options'])) {
            $result = $this->arrayMerge($result, $options['additional_options']);
        }
        if (isset($options['attributes_info'])) {
            $result = $this->arrayMerge($result, $options['attributes_info']);
        }
        return $result;
    }

    /**
     * Set option in each item.
     *
     * @param array $options
     * @param array $eachItem
     *
     * @return array
     */
    public function setEachItemOption($options, $eachItem)
    {
        foreach ($options as $_option) {
            $eachOption = [];
            $eachOption['label'] = strip_tags($_option['label']);
            $eachOption['value'] = $_option['value'];
            $eachItem['option'][] = $eachOption;
        }
        return $eachItem;
    }

    /**
     * Set seller collection data.
     *
     * @param object $sellerCollection
     * @param object $shipment
     * @param int $trackingid
     * @param string $carrier
     */
    public function setSellerCollectionData($sellerCollection, $shipment, $trackingid, $carrier)
    {
        foreach ($sellerCollection as $row) {
            if ($shipment->getId() != '') {
                $row->setShipmentId($shipment->getId());
                $row->setTrackingNumber($trackingid);
                $row->setCarrierName($carrier);
                $row->save();
            }
        }
    }

    /**
     * Response for create account.
     *
     * @param boolean $sellerCreated
     * @param object $customer
     * @param array $returnArray
     * @param string $profileurl
     *
     * @return object
     */
    public function responseForCreateAccount($sellerCreated, $customer, $returnArray, $profileurl)
    {
        if ($sellerCreated == true && $customer->getId()) {
            $returnArray['status'] = self::SUCCESS;
            $returnArray['message'] = __('Success');
            $returnArray['id'] = $customer->getId();
            $returnArray['group_id'] = $customer->getGroupId();
            $returnArray['default_billing'] = $customer->getDefaultBilling();
            $returnArray['default_shipping'] = $customer->getDefaultShipping();
            $returnArray['confirmation'] = $customer->getConfirmation();
            $returnArray['created_at'] = $customer->getCreatedAt();
            $returnArray['updated_at'] = $customer->getUpdatedAt();
            $returnArray['created_in'] = $customer->getCreatedIn();
            $returnArray['dob'] = $customer->getDob();
            $returnArray['email'] = $customer->getEmail();
            $returnArray['firstname'] = $customer->getFirstname();
            $returnArray['lastname'] = $customer->getLastname();
            $returnArray['middlename'] = $customer->getMiddlename();
            $returnArray['prefix'] = $customer->getPrefix();
            $returnArray['suffix'] = $customer->getSuffix();
            $returnArray['gender'] = $customer->getGender();
            $returnArray['store_id'] = $customer->getStoreId();
            $returnArray['taxvat'] = $customer->getTaxvat();
            $returnArray['website_id'] = $customer->getWebsiteId();
            $returnArray['disable_auto_group_change'] = $customer->getDisableAutoGroupChange();
            $returnArray['is_seller'] = 1;
            $returnArray['profileurl'] = $profileurl;
            return $this->getJsonResponse($returnArray);
        } else {
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('Sorry! But this shop name/email is already present');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Check if product count is set or not.
     *
     * @param array $sellerArr
     * @param int $procountSellerId
     *
     * @return array
     */
    public function checkProcountSetOrNot($sellerArr, $procountSellerId)
    {
        if (!isset($sellerArr[$procountSellerId])) {
            $sellerArr[$procountSellerId] = [];
        }
        return $sellerArr[$procountSellerId];
    }

    /**
     * Set seller product id.
     *
     * @param object $sellerProductColl
     * @param array $sellerArr
     * @param int $procountSellerId
     *
     * @return array
     */
    public function setSellerProductId($sellerProductColl, $sellerArr, $procountSellerId)
    {
        foreach ($sellerProductColl as $value) {
            array_push($sellerArr[$procountSellerId], $value['mageproduct_id']);
        }
        return $sellerArr[$procountSellerId];
    }

    /**
     * Set new shipment
     *
     * @param array $shipment
     * @param int $shipmentId
     * @param int $orderId
     * @param object $order
     * @param int $status
     * @param array $itemsarray
     * @param array $trackingData
     *
     * @return object
     */
    public function setShipmentNew(
        $shipment,
        $shipmentId,
        $orderId,
        $order,
        $status,
        $itemsarray,
        $trackingData
    ) {
        if (!empty($shipment['shipment_id'])) {
            $shipmentId = $shipment['shipment_id'];
        }
        if ($shipmentId) {
            $shipment = $this->shipmentFactory->create()->load($shipmentId);
        } elseif ($orderId) {
            if ($order->getForcedDoShipmentWithInvoice()) {
                return $this->getJsonResponse([
                    'status'=> $status,
                    'message' => 'Cannot do shipment for the order separately from invoice.'
                ]);
            }
            if (!$order->canShip()) {
                return $this->getJsonResponse([
                    'status' => $status,
                    'message' => 'Cannot do shipment for the order.'
                ]);
            }

            $shipment = $this->_prepareShipment(
                $order,
                $itemsarray['data'],
                $trackingData
            );
        }
        return $shipment;
    }

    /**
     * Send new shipment.
     *
     * @param array $shipment
     * @param array $data
     * @param int $orderId
     * @param int $sellerId
     * @param int $trackingid
     * @param string $carrier
     * @param string $message
     *
     * @return object
     */
    public function sendShippmentNew($shipment, $data, $orderId, $sellerId, $trackingid, $carrier, $message)
    {
        if ($shipment) {
            $comment = '';
            $shipment->getOrder()->setCustomerNoteNotify(
                !empty($data['send_email'])
            );
            $shippingLabel = '';
            if (!empty($data['create_shipping_label'])) {
                $shippingLabel = $data['create_shipping_label'];
            }
            $isNeedCreateLabel=!empty($shippingLabel) && $shippingLabel;
            $shipment->getOrder()->setIsInProcess(true);

            $transactionSave = $this->dbTransaction->addObject(
                $shipment
            )->addObject(
                $shipment->getOrder()
            );
            $transactionSave->save();

            $shipmentId = $shipment->getId();

            $courrier = 'custom';
            $sellerCollection = $this->mpOrdersFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
            $this->setSellerCollectionData($sellerCollection, $shipment, $trackingid, $carrier);

            $this->shipmentSender->send($shipment);

            $shipmentCreatedMessage = __('The shipment has been created.');
            $labelMessage = __('The shipping label has been created.');
            $message = $isNeedCreateLabel ? $shipmentCreatedMessage.' '.$labelMessage
                : $shipmentCreatedMessage;
            $status = 1;
            return $message;
        }
    }

    /**
     * @inheritDoc
     */
    public function saveProduct(\Magento\Catalog\Api\Data\ProductInterface $product, $id)
    {
        try {
            $productData['product'] = $product->convertToArray();

            $mediaGalleryData = isset($productData['product']["media_gallery"]) ?
             $productData['product']["media_gallery"] : false;
            unset($productData['product']["media_gallery"]);

            if (isset($productData['product']['id'])) {
                $productData['id'] = $productData['product']['id'];
                $product = $this->productRepoInterface->getById($productData['product']['id']);
                if ($product) {
                    $model = $this->mpProductFactory->create()->getCollection()
                        ->addFieldToFilter(
                            'mageproduct_id',
                            $product->getId()
                        )->addFieldToFilter(
                            'seller_id',
                            $id
                        );
                    if (count($model)) {
                        $productData['product']['sku'] = $product->getSku();
                        $productData['product'] = array_merge(
                            $product->getData(),
                            $productData['product']
                        );
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('You are not authorised to edit this product.')
                        );
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The Product does not exist for the provided Id.')
                    );
                }
            }
            
            if (isset($productData['product']['sku'])) {
                if (!$productData['product']['sku']) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('SKU is missing or empty.')
                    );
                }
                try {
                    $product = $this->productRepoInterface->get($productData['product']['sku']);
                    $model = $this->mpProductFactory->create()->getCollection()
                        ->addFieldToFilter(
                            'mageproduct_id',
                            $product->getId()
                        )->addFieldToFilter(
                            'seller_id',
                            $id
                        );

                    if (count($model)) {
                        $productData['id'] = $product->getId();
                        $productData['product'] = array_merge(
                            $product->getData(),
                            $productData['product']
                        );
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('SKU already exists.')
                        );
                    }
                } catch (\Exception $e) {
                    $error = $e;
                }
            }

            if (isset($productData['product']['store_id'])) {
                $productData['store'] = $productData['product']['store_id'];
            }

            if (!isset($productData['product']['set'])) {
                $productData['set'] = "";
            }

            if (isset($productData['product']['type'])) {
                $productData['type'] = '';
            }
            if (isset($productData['product']['weight'])) {
                $productData['product']['product_has_weight'] = true;
            }

            if (isset($productData['product']['attribute_set_id'])) {
                $productData['set'] = $productData['product']['attribute_set_id'];
            }

            if (isset($productData['product']['type_id'])) {
                $productData['type'] = $productData['product']['type_id'];
            }

            if (isset($productData['product']['status'])) {
                $productData['status'] = $productData['product']['status'];
            }
            if (isset($productData["product"]["extension_attributes"])) {
                $extAttr = $productData["product"]["extension_attributes"];
                $categories = $extAttr->getCategoryLinks();
                $productData["product"]['category_ids'] = [];
                foreach ($categories as $category) {
                    $productData["product"]['category_ids'][] = $category->getcategoryId();
                }
                $stockItem = $extAttr->getStockItem()->getData();
                $productData["product"]["stock_data"]["manage_stock"] =
                $stockItem["manage_stock"];
                $productData["product"]["stock_data"]["use_config_manage_stock"] =
                $stockItem["use_config_manage_stock"];
                $productData["product"]["quantity_and_stock_status"]["qty"] = $stockItem["qty"];
                $productData["product"]["quantity_and_stock_status"]["is_in_stock"] = $stockItem["is_in_stock"];
            }
            $result = $this->saveProduct->saveProductData($id, $productData);

            if ($mediaGalleryData) {
                $productId = $result['product_id'];
                $product = $this->productRepoInterface->getById($productId);
                $this->mediaProcessor->processMediaGallery($product, $mediaGalleryData["images"]);
                $product->save();
            }
            if (isset($result['error']) && $result['error'] == 1) {
                if (isset($result['message']) && $result['message'] != '') {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($result['message'])
                    );
                }
            }
            $returnArray['status'] = self::SUCCESS;
            $returnArray['message'] = __("Product saved successfully.");
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = $e->getMessage();
            return $this->getJsonResponse($returnArray);
        }
    }
}
