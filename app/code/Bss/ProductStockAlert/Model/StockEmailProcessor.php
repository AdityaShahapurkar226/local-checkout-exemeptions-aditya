<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model;

use Bss\ProductStockAlert\Model\ResourceModel\Stock as StockResource;
use Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

class StockEmailProcessor
{
    /**
     * Const
     * Run every 2000
     */
    const BATCH_SIZE = 2000;

    /**
     * @var array
     */
    private $productRelation = [];

    /**
     * @var array
     */
    private $stockItems = [];

    /**
     * @var array
     */
    private $productItems = [];

    /**
     * Warning (exception) errors array
     *
     * @var array
     */
    protected $errors = [];

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Bss\ProductStockAlert\Model\EmailFactory
     */
    protected $emailFactory;

    /**
     * @var Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Bss\ProductStockAlert\Model\Stock
     */
    protected $modelstock;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    private $stockNotiCollection;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockResource
     */
    private $stockResource;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Bss\ProductStockAlert\Helper\MultiSourceInventory
     */
    private $multiSourceInventory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ProductLinkManagementInterface
     */
    protected $productLinkManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * StockEmailProcessor constructor.
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param StockFactory $modelstock
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param EmailFactory $emailFactory
     * @param \Bss\ProductStockAlert\Model\Form\FormKey $formKey
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockResource $stockResource
     * @param ProductCollectionFactory $productCollectionFactory
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventory
     * @param ResourceConnection $resourceConnection
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Bss\ProductStockAlert\Model\StockFactory $modelstock,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Bss\ProductStockAlert\Model\EmailFactory $emailFactory,
        \Bss\ProductStockAlert\Model\Form\FormKey $formKey,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockResource $stockResource,
        ProductCollectionFactory $productCollectionFactory,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventory,
        ResourceConnection $resourceConnection,
        ProductLinkManagementInterface $productLinkManagement,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->emailFactory = $emailFactory;
        $this->formKey = $formKey;
        $this->modelstock = $modelstock;
        $this->inlineTranslation = $inlineTranslation;
        $this->stockItemCriteriaFactory = $stockItemCriteriaInterfaceFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockResource = $stockResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->multiSourceInventory = $multiSourceInventory;
        $this->resourceConnection = $resourceConnection;
        $this->productLinkManagement = $productLinkManagement;
        $this->storeManager = $storeManager;
    }

    /**
     * Run process send product alerts
     *
     * @param null|\Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     */
    public function process($collection = null)
    {
        /* @var $email \Bss\ProductStockAlert\Model\Email */
        $email = $this->emailFactory->create();
        $this->_processStock($email, $collection);
        $this->_sendErrorEmail();
        return $this;
    }

    /**
     * Process stock emails
     *
     * @param \Bss\ProductStockAlert\Model\Email $email
     * @param \Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection $collection
     * @return $this
     */
    protected function _processStock(
        \Bss\ProductStockAlert\Model\Email $email,
        $collection
    ) {
        // Get alert collection
        $email->setType('stock');
        $storeIds = $this->retrieveStoreIds();
        $this->getCollection($collection, $storeIds);
        $this->stockNotiCollection->joinRelationTable();

        // Get product ids (included child of configurable)
        // Get alert data as array
        $affectedData = $this->retrieveAffectedData();
        $dataStock = $affectedData['dataStock'];
        $affectedProductIds = array_unique($affectedData['affectedProductIds']);
        $affectedSkuWebsiteCode = $affectedData['affectedSkuWebsiteCode'];

        // Build product list
        // --- User product repository make slow load ---
        // Use collection instead of
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $productCollection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $productCollection->addAttributeToSelect(['status', 'type_id'])->addFieldToSelect('entity_id');
        $productCollection->addFieldToFilter('entity_id', ['in' => $affectedProductIds]);
        $productCollection->load();

        $this->createBulkProductItems($productCollection);

        // Build stock list for all product ids
        if (!$this->multiSourceInventory->isEnabledMsi()) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($affectedProductIds);
            $stockItemsCollection = $this->stockItemRepository->getList($criteria);
            $stockItems = $stockItemsCollection->getItems();
        } else {
            $stockResolver = $this->multiSourceInventory->getStockResolverObject();
            $salableQty = $this->multiSourceInventory->getSalableQtyObject();
            $stockItems = [];

            $stockIdCheckedWs = [];
            foreach ($affectedSkuWebsiteCode as $sku => $pidAndWebsite) {
                try {
                    $wsCode = $pidAndWebsite['website_code'];
                    $pid = $pidAndWebsite['product_id'];
                    $product = $this->getProduct($pid);
                    if (!in_array($wsCode, array_values($stockIdCheckedWs))) {
                        $stockId = $stockResolver->execute('website', $wsCode)->getStockId();
                        // This website already checked, does not need call execute any more
                        $stockIdCheckedWs[$wsCode] = $stockId;
                    } else {
                        $stockId = $stockIdCheckedWs[$wsCode];
                    }

                    if ($product) {
                        if (in_array($product->getTypeId(), ['bundle', 'configurable', 'grouped'])) {
                            switch ($product->getTypeId()) {
                                case "configurable":
                                    $childs = $product->getTypeInstance()->getUsedProducts($product);
                                    break;
                                case "grouped":
                                    $childs = $product->getTypeInstance()->getAssociatedProducts($product);
                                    break;
                                case "bundle":
                                    $childs = $this->productLinkManagement->getChildren($product->getSku());
                                    break;
                            }
                            $qty = 0;
                            $parentStock = $this->getStockStatus($sku, $stockId);
                            $childStock = false;
                            foreach ($childs as $child) {
                                if ($this->getStockStatus($child->getSku(), $stockId)) {
                                    $qty += $salableQty->execute($child->getSku(), $stockId);
                                    $childStock = true;
                                }
                            }
                            if ($childStock && $parentStock) {
                                $stockItems[$pid] = ['qty' => $qty, 'status' => true];
                            } else {
                                $stockItems[$pid] = ['qty' => 0, 'status' => false];
                            }
                        } else {
                            $status = $this->getStockStatus($sku, $stockId);
                            $qty = $salableQty->execute($sku, $stockId);
                            $stockItems[$pid] = ['qty' => $qty, 'status' => $status];
                        }
                    } else {
                        continue;
                    }
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
        $this->createBulkStockItems($stockItems);
        // Retrieve list of product ids that does not exist
        // That means, user subscribed a product, the identity saved to db.
        // With unexpected reasons, admin remove that product after,
        // Now product id is still alive in stock record but its already removed from catalog entity
        // We will take all of them and send a message to admin to notice the issue
        $noticeIdsShouldBeRemove = $this->retrievePidsListNotExist($affectedProductIds, $affectedData);
        // End

        // Process stock and get list mail that will be send
        $after = $this->validateStockData($dataStock);

        $listEmailProduct = $after['listEmailProduct'];
        $customerNames = $after['customerNames'];

        if (empty($listEmailProduct)) {
            // Remove notice records that have invalid product id
            $this->removeInvalidNotice($noticeIdsShouldBeRemove);
            // Create new form key to prevent frontend-user access
            $this->formKey->renewData();
            return $this;
        }

        // Do send email
        foreach ($listEmailProduct as $websiteId => $listEmails) {
            if (empty($listEmails)) {
                continue;
            }
            foreach ($listEmails as $emailSend => $products) {
                if (!filter_var($emailSend, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                $this->listEmails(
                    $products,
                    $email,
                    $customerNames,
                    $emailSend,
                    $websiteId
                );
            }
        }

        // Remove notice records that have invalid product id
        $this->removeInvalidNotice($noticeIdsShouldBeRemove);
        // Create new form key to prevent frontend-user access
        $this->formKey->renewData();
        return $this;
    }

    /**
     * @param array $noticeIdsShouldBeRemove
     */
    private function removeInvalidNotice($noticeIdsShouldBeRemove)
    {
        if (!empty($noticeIdsShouldBeRemove)) {
            $this->stockResource->removeNoiticeIds($noticeIdsShouldBeRemove);
        }
    }

    /**
     * @return array
     */
    private function retrieveAffectedData()
    {
        $collection = $this->stockNotiCollection;
        $dataStock = [];
        $affectedProductIds = [];
        $affectedSkuWebsiteCode = [];
        $totalLoop = ceil($collection->getSize() / self::BATCH_SIZE);

        for ($page = 1; $page <= $totalLoop; $page ++) {
            $currentPageData = $collection->setPageSize(self::BATCH_SIZE)->setCurPage($page)->getData();
            foreach ($currentPageData as $stockModel) {
                $storeId = $stockModel['store_id'];
                if ($this->helper->getLimitCount($storeId)
                    && $this->helper->getLimitCount($storeId) <= $stockModel['send_count']) {
                    continue;
                }
                if ($this->isChildProductStock($stockModel)) {
                    if (!in_array($stockModel['product_id'], $affectedProductIds)) {
                        $affectedProductIds[] = $stockModel['product_id'];
                        $dataStock[$stockModel['alert_stock_id']] = $this->createSkeletonStockAlert($stockModel);
                    }
                    $affectedProductIds[] = $stockModel['child_id'];
                    $this->productRelation[$stockModel['product_id']][] = $stockModel['child_id'];
                    $affectedSkuWebsiteCode[$stockModel['product_sku']] = [
                        'website_code' => $stockModel['website_code'],
                        'product_id' => $stockModel['child_id']
                    ];
                } else {
                    $affectedProductIds[] = $stockModel['product_id'];
                    $dataStock[$stockModel['alert_stock_id']] = $this->createSkeletonStockAlert($stockModel);
                    $affectedSkuWebsiteCode[$stockModel['product_sku']] = [
                        'website_code' => $stockModel['website_code'],
                        'product_id' => $stockModel['product_id']
                    ];
                }
            }
        }

        return [
            'dataStock' => $dataStock,
            'affectedProductIds' => $affectedProductIds,
            'affectedSkuWebsiteCode' => $affectedSkuWebsiteCode
        ];
    }

    /**
     * @param $dataStock
     * @return array
     */
    private function validateStockData($dataStock)
    {
        $arrayProduct = $listEmailProduct = $customerNames = [];
        foreach ($dataStock as $stockItem) {
            try {
                $productId = $stockItem['product_id'];
                $storeId = $stockItem['store_id'];

                $stock = $this->getStockItem($productId);
                $product = $this->getProduct($productId);

                if ($stock && $product !== null) {
                    $isInStock = $stock['stock_status'];
                    $qty = $stock['qty'];
                    $emailSendBasedQty = $this->helper->getEmailSendBasedQty($storeId);

                    if (!in_array($product->getTypeId(), ['bundle', 'configurable', 'grouped']) && $isInStock) {
                        if ($this->checkQtySendMail($this->helper->getQtySendMail($storeId), $qty)) {
                            continue;
                        }
                        $sentCounter = isset($arrayProduct[$productId]) ? $arrayProduct[$productId] + 1 : 1;
                        $arrayProduct[$productId] = $sentCounter;
                        if ($this->checkBaseQty($emailSendBasedQty, $arrayProduct[$productId], $qty)) {
                            continue;
                        }
                    }
                    $this->saveModel(
                        $isInStock,
                        $product,
                        $stockItem,
                        $storeId,
                        $listEmailProduct,
                        $customerNames
                    );
                }
            } catch (\Exception $e) {
                $this->setMessageErrors($e->getMessage(), $productId);
            }
        }

        return [
            'listEmailProduct'  => $listEmailProduct,
            'customerNames' => $customerNames,
        ];
    }

    /**
     * @param int $limitSend
     * @param int $qty
     * @return bool
     */
    private function checkQtySendMail($limitSend, $qty)
    {
        return ($limitSend && $limitSend > $qty);
    }

    /**
     * @param int $emailSendBasedQty
     * @param int $qtyNext
     * @param int $qty
     * @return bool
     */
    private function checkBaseQty($emailSendBasedQty, $qtyNext, $qty)
    {
        return ($emailSendBasedQty && $qtyNext > $qty);
    }

    /**
     * @param bool $isInStock
     * @param Product $product
     * @param array $stockData
     * @param int $storeId
     * @param array $listEmailProduct
     * @param array $customerNames
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function saveModel(
        $isInStock,
        $product,
        $stockData,
        $storeId,
        &$listEmailProduct,
        &$customerNames
    ) {
        $status = $stockData['status'];
        $stockId = $stockData['alert_stock_id'];
        $customerId = $stockData['customer_id'];
        $customerEmail = $stockData['customer_email'];
        $parent = $stockData['parent_id'];

        $productId = $product->getId();
        $productStatus = $product->getStatus();

        $checkModelSave = $this->checkModelSave(
            $isInStock,
            $productStatus,
            $status,
            $storeId,
            $stockData['send_count']
        );

        if ($checkModelSave == 1) {
            $customerNames[$stockId] = $stockData['customer_name'];

            if ($this->isConfigurableAndHasChild($product->getTypeId(), $productId)) {
                // Child of Configurable
                foreach ($this->productRelation[$productId] as $childProductId) {
                    $childProductStock = $this->getStockItem($childProductId);
                    $childIsInStock = $childProductStock['stock_status'];
                    $childQty = $childProductStock['qty'];
                    $qtyToSend = $this->helper->getQtySendMail($storeId);
                    if ($this->isReadyToMail($childIsInStock, $qtyToSend, $childQty)) {
                        $listEmailProduct[$storeId][$customerEmail][] = [
                            'is_salable' => $product->isSalable(),
                            'product_id' => $productId,
                            'parent_id' => $productId,
                            'child_id' => $childProductId,
                            'stockId' => $stockId,
                            'has_child' => true
                        ];
                        $this->stockResource->updateStock($stockId, ['status' => 0]);
                        break;
                    }
                }
            } else {
                // Simple and Parent Configurable Product
                $isSalable = $product->isSalable();
                if ($product->getTypeId() == 'configurable') {
                    $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                    foreach ($childProducts as $childProduct) {
                        if ($childProduct->isSalable()) {
                            $isSalable = true;
                            break;
                        }
                    }
                }
                $listEmailProduct[$storeId][$customerEmail][] = [
                    'is_salable' => $isSalable,
                    'product_id' => $productId,
                    'parent_id' => $parent ? $parent : $productId,
                    'child_id' => $productId,
                    'stockId' => $stockId,
                    'has_child' => $parent ? true : false
                ];
            }
        } elseif ($checkModelSave == 2) {
            $this->stockResource->updateStock($stockId, ['status' => 0]);
        }
    }

    /**
     * @param bool $isInStock
     * @param int $productStatus
     * @param int $status
     * @param int $storeId
     * @param int $sentCount
     * @return int
     */
    private function checkModelSave($isInStock, $productStatus, $status, $storeId, $sentCount)
    {
        if ($isInStock && $productStatus && $status == 0
            || ($this->helper->getLimitCount($storeId) >= $sentCount && $isInStock && $productStatus && $status == 1)
            || ($this->helper->getLimitCount($storeId) >= $sentCount && $isInStock && $productStatus && $status == 2)
        ) {
            return 1;
        } elseif ((!$isInStock || $productStatus == 2) && ($status == 1 || $status == 2)) {
            return 2;
        }
        return 3;
    }

    /**
     * @param object $products
     * @param \Bss\ProductStockAlert\Model\Email $email
     * @param array $customerNames
     * @param string $emailSend
     * @param int $websiteId
     */
    protected function listEmails($products, $email, $customerNames, $emailSend, $websiteId)
    {
        try {
            $productIds = [];
            foreach ($products as $productData) {
                if (isset($productData['is_salable']) &&
                    $productData['is_salable']) {
                    $email->addStockProduct($productData);
                    $email->setCustomerName($customerNames[$productData['stockId']]);
                    $productIds[] = $productData['product_id'];
                }
            }
            if (!empty($productIds)) {
                $email->setStoreId($websiteId);
                $email->setCustomerEmail($emailSend);
                $email->send();
                $email->clean();

                $subscribeCollection = $this->stockResource->getStockNotice(
                    [
                        'customer_email' => $emailSend,
                        'product_id' => $productIds,
                        'store_id' => $websiteId
                    ],
                    [
                        'send_count',
                        'alert_stock_id'
                    ]
                );

                foreach ($subscribeCollection as $subscribeItem) {
                    $storeId = $this->storeManager->getStore()->getId();
                    $send_cont = (int)($subscribeItem['send_count'] + 1);
                    if ((int)$this->helper->getLimitCount($storeId) == $send_cont) {
                        $data = [
                            'send_date' => $this->helper->getGmtDate(),
                            'send_count' => $send_cont,
                            'status' => \Bss\ProductStockAlert\Model\Config\Source\Status::STATUS_SENTLIMIT
                        ];
                    } else {
                        $data = [
                            'send_date' => $this->helper->getGmtDate(),
                            'send_count' => $send_cont,
                            'status' => \Bss\ProductStockAlert\Model\Config\Source\Status::STATUS_SENT
                        ];
                    }
                    $this->stockResource->updateStock(
                        $subscribeItem['alert_stock_id'],
                        $data
                    );
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * Send email to administrator if error
     *
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendErrorEmail()
    {
        $count = count($this->errors);
        if ($count) {
            if (!$this->helper->getEmailErrorTemplate()) {
                return $this;
            }

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $this->helper->getEmailErrorTemplate()
            )->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $this->errors)]
            )->setFrom(
                $this->helper->getEmailErrorIdentity()
            )->addTo(
                $this->helper->getEmailErrorRecipient()
            )->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
            $this->errors[] = [];
        }
        return $this;
    }

    /**
     * @param \Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection|null $oriCollection
     * @param int[] $storeIds
     * @return $this
     */
    private function getCollection($oriCollection, $storeIds)
    {
        if (!$this->stockNotiCollection) {
            try {
                if (!$oriCollection ||
                    !($oriCollection instanceof \Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection)) {
                    /** @var Collection $oriCollection */
                    $oriCollection = $this->modelstock->create()->getCollection();
                    $oriCollection->addFieldToFilter(
                        "store_id",
                        [
                            "in" => $storeIds
                        ]
                    );
                }
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
                return $this;
            }
            $this->stockNotiCollection = $oriCollection;
        }
        return $this;
    }

    /**
     * @return array
     */
    private function retrieveStoreIds()
    {
        try {
            $ids = [];
            foreach ($this->helper->getStores() as $store) {
                $storeId = $store->getId();
                if ($this->helper->isStockAlertAllowed($storeId)) {
                    $ids[] = $store->getId();
                }
            }

            return $ids;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return [];
        }
    }

    /**
     * @param array $affectedProductIds
     * @param array $affectedData
     * @return array
     */
    private function retrievePidsListNotExist($affectedProductIds, $affectedData)
    {
        $existProductIds = array_keys($this->productItems);
        $notExistProductIds = array_diff($affectedProductIds, $existProductIds);
        $affectedNotices = $affectedData['dataStock'];
        $affectedNoticeIds = [];
        foreach ($affectedNotices as $affectedNotice) {
            if (isset($affectedNotice['product_id']) &&
                in_array($affectedNotice['product_id'], $notExistProductIds)) {
                $affectedNoticeIds[] = $affectedNotice['alert_stock_id'];
            }
        }
        if (!empty($notExistProductIds)) {
            $this->errors[] =
                __(
                    "We found list of product ids that does not exist. Invalid product ids: %1",
                    implode(',', $notExistProductIds)
                );
        }
        return $affectedNoticeIds;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return array
     */
    private function createBulkProductItems($productCollection)
    {
        if (!$this->productItems) {
            $items = [];
            $productCollection->setPageSize(self::BATCH_SIZE);
            $pages = $productCollection->getLastPageNumber();
            for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $productCollection->setCurPage($currentPage);
                foreach ($productCollection as $productModel) {
                    $items[$productModel->getId()] = $productModel;
                }
                $productCollection->clear();
            }
            $this->productItems = $items;
        }
        return $this->productItems;
    }

    /**
     * @param int $productId
     * @return Product|null
     */
    private function getProduct($productId)
    {
        if (!$this->productItems) {
            return null;
        }
        return isset($this->productItems[$productId]) ? $this->productItems[$productId] : null;
    }

    /**
     * @param StockItemInterface[] $stockItems
     * @return array
     */
    private function createBulkStockItems($stockItems)
    {
        if (!$this->stockItems) {
            $items = [];
            /** @var StockItemInterface|float|int|string $stockItem */
            foreach ($stockItems as $idx => $stockItem) {
                if (is_array($stockItem)) {
                    $items[$idx] = [
                        'stock_status' => $stockItem['status'],
                        'qty' => $stockItem['qty']
                    ];
                } elseif (is_object($stockItem) && $stockItem instanceof StockItemInterface) {
                    $items[$stockItem->getProductId()] = [
                        'stock_status' => $stockItem->getIsInStock(),
                        'qty' => $stockItem->getQty()
                    ];
                }
            }
            $this->stockItems = $items;
        }
        return $this->stockItems;
    }

    /**
     * @param int $productId
     * @return Product|null
     */
    private function getStockItem($productId)
    {
        if (!$this->stockItems) {
            return null;
        }
        return isset($this->stockItems[$productId]) ? $this->stockItems[$productId] : null;
    }

    /**
     * @param array $stock
     * @return bool
     */
    private function isChildProductStock($stock)
    {
        return (isset($stock['child_id']) &&
            $stock['child_id'] != "" &&
            $stock['child_id'] &&
            isset($stock['parent_id']) &&
            $stock['child_id'] != $stock['parent_id']);
    }

    /**
     * @param bool $isInStock
     * @param string|int $qtyToSend
     * @param string|int $qty
     * @return bool
     */
    private function isReadyToMail($isInStock, $qtyToSend, $qty)
    {
        return ($isInStock &&
            $qtyToSend &&
            $qtyToSend <= $qty);
    }

    /**
     * @param int $productId
     * @param string $productType
     * @return bool
     */
    private function isConfigurableAndHasChild($productId, $productType)
    {
        return ($productType == "configurable" &&
            isset($this->productRelation[$productId]) &&
            !empty($this->productRelation[$productId]));
    }

    /**
     * @param int $customerId
     * @param string $customerEmail
     * @return string
     */
    private function getCustomerName($customerId, $customerEmail)
    {
        if ($customerId == 0) {
            return "Guest";
        }
        return $customerEmail;
    }

    /**
     * @param string $message
     * @param int $alertProductId
     */
    private function setMessageErrors($message, $alertProductId)
    {
        if (!isset($this->errors[$alertProductId])) {
            if ($message == __("The product that was requested doesn't exist. Verify the product and try again.")) {
                $messageError = __("- The product ID ") . $alertProductId .
                    __(" that was requested does not exist. Verify the product and try again.");
                if (!in_array($messageError, $this->errors)) {
                    $this->errors[$alertProductId] = $messageError;
                }
            } else {
                $this->errors[$alertProductId] = $message;
            }
        }
    }

    /**
     * @param array $stockModel
     * @return array
     */
    private function createSkeletonStockAlert($stockModel)
    {
        return [
            'alert_stock_id' => $stockModel['alert_stock_id'],
            'customer_id' => $stockModel['customer_id'],
            'customer_email' => $stockModel['customer_email'],
            'customer_name' => $stockModel['customer_name'],
            'product_id' => $stockModel['product_id'],
            'website_id' => $stockModel['website_id'],
            'website_code' => $stockModel['website_code'],
            'store_id' => $stockModel['store_id'],
            'status' => $stockModel['status'],
            'parent_id' => $stockModel['parent_id'],
            'send_count' => $stockModel['send_count'],
            'child_id' => $stockModel['child_id']
        ];
    }

    /**
     * @param $productSku
     * @param $stockId
     * @return int
     */
    public function getStockStatus($productSku, $stockId)
    {
        $productSku = (string) $productSku;
        if ($this->helper->isMagento24x()) {
            $areProductsSalable = $this->multiSourceInventory->getAreProductsSalableObject();
            $result = $areProductsSalable->execute([$productSku], $stockId);
            $result = current($result);
            return (int)$result->isSalable();
        } else {
            $isProductSalable = $this->multiSourceInventory->getIsProductSalableObject();
            return $isProductSalable->execute($productSku, $stockId);
        }
    }
}
