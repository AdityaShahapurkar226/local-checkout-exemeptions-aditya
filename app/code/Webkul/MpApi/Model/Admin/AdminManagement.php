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

namespace Webkul\MpApi\Model\Admin;

class AdminManagement implements \Webkul\MpApi\Api\AdminManagementInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productFactory;

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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $mageOrderRepo;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Webkul\Marketplace\Api\Data\ProductInterfaceFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory
     */
    protected $saleslistFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepoInterface;
    
    /**
     * @var \Webkul\Marketplace\Api\Data\OrdersInterfaceFactory
     */
    protected $mpOrdersFactory;

    /**
     * @var \Webkul\Marketplace\Model\SaleperpartnerFactory
     */
    protected $salesPerPartnerFactory;

    /**
     * @var \Webkul\Marketplace\Model\SellertransactionFactory
     */
    protected $sellerTransactionsFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Webkul\MpApi\Api\Data\ResponseInterface
     */
    protected $responseApi;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;
    
    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Webkul\MpApi\Api\SaleslistRepositoryInterface $salesListRepo
     * @param \Webkul\MpApi\Api\SellerRepositoryInterface $sellerRepo
     * @param \Webkul\MpApi\Api\OrdersRepositoryInterface $ordersRepo
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\Marketplace\Api\Data\SellerInterfaceFactory $sellerFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Webkul\Marketplace\Api\Data\ProductInterfaceFactory $mpProductFactory
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $saleslistFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepoInterface
     * @param \Webkul\Marketplace\Api\Data\OrdersInterfaceFactory $mpOrdersFactory
     * @param \Webkul\Marketplace\Model\SaleperpartnerFactory $salesPerPartnerFactory
     * @param \Webkul\Marketplace\Model\SellertransactionFactory $sellerTransactionsFactory
     * @param \Webkul\Marketplace\Helper\Email $emailHelper
     * @param \Webkul\MpApi\Api\Data\ResponseInterface $responseApi
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param \Magento\Framework\Api\Filter $filter
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\MpApi\Api\SaleslistRepositoryInterface $salesListRepo,
        \Webkul\MpApi\Api\SellerRepositoryInterface $sellerRepo,
        \Webkul\MpApi\Api\OrdersRepositoryInterface $ordersRepo,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\Marketplace\Api\Data\SellerInterfaceFactory $sellerFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Event\Manager $eventManager,
        \Webkul\Marketplace\Api\Data\ProductInterfaceFactory $mpProductFactory,
        \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $saleslistFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepoInterface,
        \Webkul\Marketplace\Api\Data\OrdersInterfaceFactory $mpOrdersFactory,
        \Webkul\Marketplace\Model\SaleperpartnerFactory $salesPerPartnerFactory,
        \Webkul\Marketplace\Model\SellertransactionFactory $sellerTransactionsFactory,
        \Webkul\Marketplace\Helper\Email $emailHelper,
        \Webkul\MpApi\Api\Data\ResponseInterface $responseApi,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \Magento\Framework\Api\Filter $filter,
        \Magento\Framework\Api\Search\FilterGroup $filterGroup
    ) {
        $this->customerRepository = $customerRepository;
        $this->salesListRepo = $salesListRepo;
        $this->sellerRepo = $sellerRepo;
        $this->ordersRepo = $ordersRepo;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->sellerFactory = $sellerFactory;
        $this->mpHelper = $marketplaceHelper;
        $this->mageOrderRepo = $orderRepository;
        $this->date = $date;
        $this->eventManager = $eventManager;
        $this->mpProductFactory = $mpProductFactory;
        $this->saleslistFactory = $saleslistFactory;
        $this->productRepoInterface = $productRepoInterface;
        $this->mpOrdersFactory = $mpOrdersFactory;
        $this->salesPerPartnerFactory = $salesPerPartnerFactory;
        $this->sellerTransactionsFactory = $sellerTransactionsFactory;
        $this->emailHelper = $emailHelper;
        $this->responseApi = $responseApi;
        $this->filterGroup = $filterGroup;
        $this->filter = $filter;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * @inheritDoc
     */
    public function getSellerListForAdmin(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->sellerRepo->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getSellerForAdmin($id)
    {
        $filter = $this->filter;
        $filterGroup = $this->filterGroup;
        $filter->setField("seller_id")
            ->setValue($id)
            ->setConditionType("eq");
        $filterGroups = $filterGroup->setFilters([$filter]);
        $criteria = $this->searchCriteria->setFilterGroups([$filterGroups]);
        return $this->sellerRepo->getList($criteria);
    }

    /**
     * @inheritDoc
     */
    public function getSellerProducts($id)
    {
        $returnArray = [];
        $collection = $this->mpProductFactory->create()
            ->getCollection()
            ->addFieldToSelect('mageproduct_id')
            ->addFieldToFilter('seller_id', $id)
            ->setOrder('mageproduct_id');
        if ($collection->getSize() == 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Seller doesnot have any product')
            );
        } else {
            $mageProIds = $collection->getData();

            $finalProIds = array_column($mageProIds, 'mageproduct_id');

            $filter = $this->filter;
            $filterGroup = $this->filterGroup;
            $filter
                ->setField("entity_id")
                ->setValue(implode(',', $finalProIds))
                ->setConditionType("in");
            $filterGroups = $filterGroup->setFilters([$filter]);
            $criteria = $this->searchCriteria->setFilterGroups([$filterGroups]);
            $data = $this->productRepoInterface->getList($criteria);
            return $data;
        }
    }

    /**
     * @inheritDoc
     */
    public function getSellerSalesList($id)
    {
        $filter = $this->filter;
        $filterGroup = $this->filterGroup;
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
        $filter = $this->filter;
        $filterGroup = $this->filterGroup;
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
    public function payToSeller($sellerId, $sellerPayReason, $entityId)
    {
        try {
            $returnArray = [];
            $actparterprocost = 0;
            $totalamount = 0;
            $helper = $this->mpHelper;
            $taxToSeller = $helper->getConfigTaxManage();
            $orderinfo = '';
            $collection = $this->saleslistFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $entityId)
                ->addFieldToFilter('order_id', ['neq' => 0])
                ->addFieldToFilter('paid_status', 0)
                ->addFieldToFilter('cpprostatus', ['neq' => 0]);
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot pay to seller')
                );
            }
            foreach ($collection as $row) {
                $sellerId = $row->getSellerId();
                $order = $this->mageOrderRepo->get($row['order_id']);
                $taxAmount = $row['total_tax'];
                $marketplaceOrders = $this->mpOrdersFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', $row['order_id'])
                    ->addFieldToFilter('seller_id', $sellerId);
                foreach ($marketplaceOrders as $tracking) {
                    $taxToSeller = $tracking['tax_to_seller'];
                }
                $vendorTaxAmount = 0;
                if ($taxToSeller) {
                    $vendorTaxAmount = $taxAmount;
                }
                $codCharges = 0;
                $shippingCharges = 0;
                if (!empty($row['cod_charges'])) {
                    $codCharges = $row->getCodCharges();
                }
                if ($row->getIsShipping() == 1) {
                    foreach ($marketplaceOrders as $tracking) {
                        $shippingamount = $tracking->getShippingCharges();
                        $refundedShippingAmount = $tracking->getRefundedShippingCharges();
                        $shippingCharges = $shippingamount - $refundedShippingAmount;
                    }
                }
                $actparterprocost = $actparterprocost +
                    $row->getActualSellerAmount() +
                    $vendorTaxAmount +
                    $codCharges +
                    $shippingCharges;
                $totalamount = $totalamount +
                    $row->getTotalAmount() +
                    $taxAmount +
                    $codCharges +
                    $shippingCharges;
                $orderinfo = $orderinfo."<tr>
                    <td class='item-info'>".$row['magerealorder_id']."</td>
                    <td class='item-info'>".$row['magepro_name']."</td>
                    <td class='item-qty'>".$row['magequantity']."</td>
                    <td class='item-price'>".$order->formatPrice($row['magepro_price'])."</td>
                    <td class='item-price'>".$order->formatPrice($row['total_commission'])."</td>
                    <td class='item-price'>".$order->formatPrice($row['actual_seller_amount']).'</td>
                </tr>';
            }
            if ($actparterprocost) {
                $collectionverifyread = $this->salesPerPartnerFactory->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                if (count($collectionverifyread) >= 1) {
                    $id = 0;
                    $totalremain = 0;
                    $amountpaid = 0;
                    foreach ($collectionverifyread as $verifyrow) {
                        $id = $verifyrow->getId();
                        if ($verifyrow->getAmountRemain() >= $actparterprocost) {
                            $totalremain = $verifyrow->getAmountRemain() - $actparterprocost;
                        }
                        $amountpaid = $verifyrow->getAmountReceived();
                    }
                    $verifyrow = $this->salesPerPartnerFactory->create()->load($id);
                    $totalrecived = $actparterprocost + $amountpaid;
                    $verifyrow->setLastAmountPaid($actparterprocost);
                    $verifyrow->setAmountReceived($totalrecived);
                    $verifyrow->setAmountRemain($totalremain);
                    $verifyrow->setUpdatedAt($this->date->gmtDate());
                    $verifyrow->save();
                } else {
                    $percent = $helper->getConfigCommissionRate();
                    $collectionf = $this->salesPerPartner->create();
                    $collectionf->setSellerId($sellerId);
                    $collectionf->setTotalSale($totalamount);
                    $collectionf->setLastAmountPaid($actparterprocost);
                    $collectionf->setAmountReceived($actparterprocost);
                    $collectionf->setAmountRemain(0);
                    $collectionf->setCommissionRate($percent);
                    $collectionf->setTotalCommission($totalamount - $actparterprocost);
                    $collectionf->setCreatedAt($this->date->gmtDate());
                    $collectionf->setUpdatedAt($this->date->gmtDate());
                    $collectionf->save();
                }

                $uniqueId = $this->checktransid();
                $transid = '';
                $transactionNumber = '';
                if ($uniqueId != '') {
                    $sellerTrans = $this->sellerTransactionsFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'transaction_id',
                            $uniqueId
                        );
                    if (count($sellerTrans)) {
                        $id = 0;
                        foreach ($sellerTrans as $value) {
                            $id = $value->getId();
                        }
                        if ($id) {
                            $this->sellerTransactionsFactory->create()->load($id)->delete();
                        }
                    }
                    $sellerTrans = $this->sellerTransactionsFactory->create();
                    $sellerTrans->setTransactionId($uniqueId);
                    $sellerTrans->setTransactionAmount($actparterprocost);
                    $sellerTrans->setType('Manual');
                    $sellerTrans->setMethod('Manual');
                    $sellerTrans->setSellerId($sellerId);
                    $sellerTrans->setCustomNote($sellerPayReason);
                    $sellerTrans->setCreatedAt($this->date->gmtDate());
                    $sellerTrans->setUpdatedAt($this->date->gmtDate());
                    $sellerTrans = $sellerTrans->save();
                    $transid = $sellerTrans->getId();
                    $transactionNumber = $sellerTrans->getTransactionId();
                }

                $collection = $this->saleslistFactory->create()->load($entityId);

                $cpprostatus = $collection->getCpprostatus();
                $paidStatus = $collection->getPaidStatus();
                $orderId = $collection->getOrderId();

                if ($cpprostatus == 1 && $paidStatus == 0 && $orderId != 0) {
                    $collection->setPaidStatus(1);
                    $collection->setTransId($transid)->save();
                    $data['id'] = $collection->getOrderId();
                    $data['seller_id'] = $collection->getSellerId();
                    $this->eventManager->dispatch(
                        'mp_pay_seller',
                        [$data]
                    );
                }

                $seller = $this->customerRepository->getById($sellerId);

                $emailTempVariables = [];
                $adminStoreEmail = $helper->getAdminEmailId();
                $adminEmail = $adminStoreEmail ? $adminStoreEmail : $helper->getDefaultTransEmailId();
                $adminUsername = 'Admin';

                $senderInfo = [];
                $receiverInfo = [];

                $receiverInfo = [
                    'name' => $seller->getFirstName().' '.$seller->getLastName(),
                    'email' => $seller->getEmail(),
                ];
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];

                $emailTempVariables['myvar1'] = $seller->getFirstName().' '.$seller->getLastName();
                $emailTempVariables['myvar2'] = $transactionNumber;
                $emailTempVariables['myvar3'] = $this->date->gmtDate();
                $emailTempVariables['myvar4'] = $actparterprocost;
                $emailTempVariables['myvar5'] = $orderinfo;
                $emailTempVariables['myvar6'] = $sellerPayReason;

                $this->emailHelper->sendSellerPaymentEmail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );

                $returnArray['message'] = __('Payment has been successfully done for this seller');
                $returnArray['status'] = self::SUCCESS;
                return $this->getJsonResponse($returnArray);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignProduct($sellerId, $productIds)
    {
        try {
            $returnArray = [];
            if ($productIds == "") {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Product id(s) cannot be empty.')
                );
            }
            $collection = $this->sellerFactory->create()
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('is_seller', 1);
            if ($collection->getSize() <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Seller not found')
                );
            }
            // set product status to 1 to assign selected products from seller
            $productCollection = $this->productFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $productIds]);
            if ($productCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Product(s) are not found')
                );
            }
            $prdAssignTosellerCount = 0;
            foreach ($productCollection as $product) {
                $proid = $product->getId();
                $userid = '';
                $collection = $this->mpProductFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('mageproduct_id', $proid);
                $flag = 1;
                foreach ($collection as $coll) {
                    $flag = 0;
                    if ($sellerId != $coll['seller_id']) {
                        $returnArray['message'][] = __(
                            'The product with id %1 is already assigned to other seller.',
                            $proid
                        );
                    } else {
                        $returnArray['message'][] = __(
                            'The product with id %1 is already assigned to the seller.',
                            $proid
                        );
                        $coll->setAdminassign(1)->save();
                    }
                }
                if ($flag) {
                    $prdAssignTosellerCount++;
                    $collection1 = $this->mpProductFactory->create();
                    $collection1->setMageproductId($proid);
                    $collection1->setSellerId($sellerId);
                    $collection1->setStatus($product->getStatus());
                    $collection1->setAdminassign(1);
                    $collection1->setCreatedAt($this->date->gmtDate());
                    $collection1->setUpdatedAt($this->date->gmtDate());
                    $collection1->save();
                }
            }
            $returnArray['message'][] = __(
                '%1 Product(s) has been successfully assigned to seller',
                $prdAssignTosellerCount
            );
            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'][] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'][] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function unassignProduct($sellerId, $productIds)
    {
        try {
            $returnArray = [];
            if ($productIds == "") {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Product id(s) cannot be empty.')
                );
            }
            $collection = $this->sellerFactory->create()
                ->getCollection()
                ->addFieldToSelect(
                    '*'
                )->addFieldToFilter(
                    'seller_id',
                    ['eq' => $sellerId]
                )->addFieldToFilter(
                    'is_seller',
                    ['eq' => 1]
                );
            if ($collection->getSize() <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot found seller with id %1', $sellerId)
                );
            }
            $productCollection = $this->productFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $productIds]);
            if ($productCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot found any product(s)')
                );
            }
            $prdUnassignTosellerCount = 0;
            foreach ($productCollection as $product) {
                $proid = $product->getId();
                $userid = '';
                $collection = $this->mpProductFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('mageproduct_id', $proid);
                $flag = 1;
                foreach ($collection as $coll) {
                    $flag = 0;
                    if ($sellerId != $coll['seller_id']) {
                        $returnArray['message'][] = __(
                            'The product with id %1 is already assigned to other seller.',
                            $proid
                        );
                    } else {
                        $coll->delete();
                        $prdUnassignTosellerCount++;
                    }
                }
                if ($flag) {
                    $returnArray['message'][] = __(
                        'The product with id %1 is not assigned to the seller.',
                        $proid
                    );
                }
            }
            $returnArray['message'][] = __(
                '%1 Product(s) has been successfully unassigned from seller',
                $prdUnassignTosellerCount
            );
            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'][] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'][] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * GetJsonResponse returns json response.
     *
     * @param array $responseContent
     *
     * @return JSON
     */
    protected function getJsonResponse($responseContent = [])
    {
        $res = $this->responseApi;
        $res->setItem($responseContent);
        return $res->getData();
    }

    /**
     * Create log.
     *
     * @param object $object
     * @param string $info
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
     * Generate a random string.
     *
     * @param int $length
     * @param string $charset
     *
     * @return string
     */
    public function randString(
        $length,
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
    ) {
        $str = 'tr-';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[random_int(0, $count - 1)];
        }

        return $str;
    }
    
    /**
     * Check the transaction ID.
     */
    public function checktransid()
    {
        $uniqueId = $this->randString(11);
        $collection = $this->sellerTransactionsFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'transaction_id',
                $uniqueId
            );
        $i = 0;
        foreach ($collection as $value) {
            ++$i;
        }
        if ($i != 0) {
            $this->checktransid();
        } else {
            return $uniqueId;
        }
    }
}
