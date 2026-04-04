<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SaveAssignedProduct extends \Magento\Backend\App\Action
{
    /** @var \Magento\Customer\Model\Url */
    protected $url;

    /** @var \Magento\Customer\Model\Session */
    protected $_session;

    /** @var \Webkul\MpAssignProduct\Helper\Data */
    protected $_assignHelper;

    /** @var \Magento\Catalog\Model\Product\Copier */
    protected $productCopier;

    /** @var \Webkul\Marketplace\Helper\Data */
    protected $mpHelper;

    /** @var StockRegistryInterface */
    protected $stockRegistry;

    /** @var StockConfigurationInterface */
    protected $stockConfiguration;

    /** @var  ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductFactory */
    protected $productFactory;

    /** @var Processor */
    protected $imageProcessor;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Gallery */
    protected $productGallery;

    /** @var \Webkul\MpAssignProduct\Model\ItemsFactory */
    protected $assignItemsFactory;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var \Webkul\MpAssignProduct\Helper\Email */
    protected $email;

    /** @var $_template */
    protected $_template;

    /**
     * Construct
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $productGallery
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $assignItems
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Webkul\MpAssignProduct\Helper\Email $email
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $productGallery,
        \Webkul\MpAssignProduct\Model\ItemsFactory $assignItems,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\MpAssignProduct\Helper\Email $email
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_assignHelper = $helper;
        $this->productCopier = $productCopier;
        $this->mpHelper = $mpHelper;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->productFactory   = $productFactory;
        $this->imageProcessor = $imageProcessor;
        $this->productGallery = $productGallery;
        $this->assignItemsFactory = $assignItems;
        $this->customerRepository = $customerRepository;
        $this->email = $email;
        parent::__construct($context);
    }
    /**
     * Save Assigned product
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_assignHelper;
        $currentStoreId = $helper->getStoreId();
        $data = $this->getRequest()->getParams();
       
        $data['image'] = '';
        if (!array_key_exists('product_id', $data)) {
            $this->messageManager->addError(__('Something went wrong.'));
            return $this->resultRedirectFactory->create()->setPath('*/product/add', ['id' => $data['product_id']]);
        }
        if (!array_key_exists('seller_id', $data) || $data['seller_id']  == 0) {
            $this->messageManager->addError(__('Seller is required field.'));
            return $this->resultRedirectFactory->create()->setPath('*/product/add', ['id' => $data['product_id']]);
        }
        $productId = $data['product_id'];
        $newProductId = 0;
        $product = $helper->getProduct($productId);
        $productType = $product->getTypeId();
        $assignProduct = $this->checkIfAssignProductExists($data['product_id'], $data['seller_id']);
        if (isset($assignProduct['assign_id']) && $assignProduct['assign_id'] !=''
          && !array_key_exists('assign_id', $data)) {
            $this->messageManager->addError(__('The product is already assigned to the seller.'));
            return $this->resultRedirectFactory->create()->setPath('*/product/add', ['id' => $data['product_id']]);
        }
        $result = $helper->validateData($data, $productType);
       
        if ($result['error']) {
            $this->messageManager->addError(__($result['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/product/add', ['id' => $data['product_id']]);
        }
        
        if (array_key_exists('assign_id', $data) && array_key_exists('assign_product_id', $data)) {
            $flag = 1;
            $newProductId = $data['assign_product_id'];
        } else {
            $flag = 0;
            $data['del'] = 0;
        }
        if (!$flag) {
            $newProduct = $this->productCopier->copy($product);
            $newProductId = $newProduct->getId();
            $data['assign_product_id'] = $newProductId;
            $this->removeImages($newProductId);
        }
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $attributData = [
          'status' => $status,
          'description' => $data['description'],
          'tax_class_id' => $data['tax_class_id']
        ];
        if ($productType != "configurable") {
            $attributData['price'] = $data['price'];
        }
        $helper->updateProductData([$newProductId], $attributData, $currentStoreId);
        $duplicateProduct = $helper->getProduct($newProductId);
        $sku = $duplicateProduct->getSku();
        if (!$flag) {
            $duplicateProduct->setSpecialPrice(null);
            $duplicateProduct->save();
        }
       
        if ($productType != "configurable") {
            $this->mpHelper->reIndexData();
            $this->updateStockData($sku, $data['qty'], 1);
        } else {
            $associateProducts = [];
            $updatedProducts = $this->addAssociatedProducts($newProductId, $data);
            $this->mpHelper->reIndexData();
            $data['products'] = $updatedProducts;
            foreach ($updatedProducts as $exProductId => $updatedData) {
                $associateProducts[] = $updatedData['new_product_id'];
                $this->updateStockData($updatedData['sku'], $updatedData['qty'], 1);
            }
            $duplicateProduct->setStatus($status);
            $duplicateProduct->setDescription($data['description']);
            $duplicateProduct->setTaxClassId($data['tax_class_id']);
            $duplicateProduct->setAssociatedProductIds($associateProducts);
            $duplicateProduct->setCanSaveConfigurableAttributes(true);
            $duplicateProduct->save();
        }
       
        $result = $helper->processAssignProduct($data, $productType, $flag);
        if ($result['assign_id'] > 0) {
            $this->adminStoreMediaImages($newProductId, $data, $currentStoreId);
            
            $this->sendAssignProductEmail($data, $flag);
            $this->messageManager->addSuccess(__('Product is saved successfully.'));
            return $this->resultRedirectFactory->create()->setPath('*/product/index');
        } else {
            $this->messageManager->addError(__('There was some error while processing your request.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', ['id' => $data['product_id']]);
        }
    }

    /**
     * Send assign product email
     *
     * @param array $data
     * @param bool $flag
     * @return void
     */
    public function sendAssignProductEmail($data, $flag)
    {
        try {
            $customerId = $data['seller_id'];
            $adminEmail = $this->_assignHelper->getAdminEmail();
            $adminName = $this->_assignHelper->getAdminName();
            if ($adminEmail != '') {
                if (!($seller = $this->_assignHelper->getSellerDetails($customerId))) {
                    return;
                }
                $shopTitle = $seller->getShopTitle();
                if (!$shopTitle) {
                    $shopTitle = $seller->getShopUrl();
                }
                // $store = $this->_storeManager->getStore()->getId();
                $product = $this->_assignHelper->getProduct($data['product_id']);
                $productName = $product->getName();
                $customer = $this->customerRepository->getById($data['seller_id']);
                $customerEmail = $customer->getEmail();
                $customerName = $customer->getFirstName();
                $this->_template = "product_template";
                if ($flag) {
                    $msg = $this->_assignHelper->getEditProductMessage();
                    $subject = __("Update Product");
                } else {
                    $msg = __('Admin has assigned %1 product to you', $productName);
                    $subject = __("Assigned Product");
                }
                $condition = $data['product_condition'];
                if ($condition == 1) {
                    $condition = __("New");
                } else {
                    $condition = __("Used");
                }
                $templateVars = [
                                    'subject' => $subject,
                                    'message' => $msg,
                                    'admin_name' => $adminName,
                                    'seller_name' => $shopTitle,
                                    'product_name' => $productName,
                                    'product_condition' => $condition,
                                    'msg' => $msg,
                                ];
                $senderInfo = ['email' => $adminEmail, 'name' => $adminName];
                $receiverInfo = [
                    'name' => $customerName,
                    'email' => $customerEmail,
                ];
                $this->email->sendAssignProductEmail($templateVars, $senderInfo, $receiverInfo);
            }
        } catch (\Excecption $e) {
            $error = $e->getMessage();
        }
    }
    /**
     * Check if product is already assigned
     *
     * @param [type] $productId
     * @param [type] $sellerId
     * @return array
     */
    public function checkIfAssignProductExists($productId, $sellerId)
    {
        $data = [];
        $collection = $this->assignItemsFactory->create()->getCollection()
        ->addFieldToFilter('product_id', $productId)
        ->addFieldToFilter('seller_id', $sellerId)->getFirstItem();
        if (!empty($collection->getData())) {
            $data['assign_id'] = $collection->getId();
            $data['assign_product_id'] = $collection->getAssignProductId();
            $data['del'] = 0;
        }
        return $data;
    }
    /**
     * Update Stock Data of Product
     *
     * @param string $sku
     * @param integer $qty
     * @param integer $isInStock
     */
    public function updateStockData($sku, $qty = 0, $isInStock = 0)
    {
        try {
            $socpeConfiguration = $this->stockConfiguration;
            $scopeId = $socpeConfiguration->getDefaultScopeId();
            $stockRegistry = $this->stockRegistry;
            $stockItem = $stockRegistry->getStockItemBySku($sku, $scopeId);
            $stockItem->setData('is_in_stock', $isInStock);
            $stockItem->setData('qty', $qty);
            $stockItem->setData('manage_stock', 1);
            $stockItem->setData('use_config_notify_stock_qty', 1);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage));
        }
    }
    /**
     * Save assigned productfrom csv
     *
     * @param array $data
     * @return void
     */
    public function saveAssignedProduct($data)
    {
       
        $helper = $this->_assignHelper;
        $currentStoreId = $helper->getStoreId();
        $data['image'] = '';
        $productId = $data['product_id'];
        $newProductId = 0;
        $product = $helper->getProduct($productId);
        $productType = $product->getTypeId();
        $result = $helper->validateData($data, $productType);
        
        if ($result['error']) {
            $result['error'] = $result['msg'];
        }
        
        if (array_key_exists('assign_id', $data) && array_key_exists('assign_product_id', $data)) {
            $flag = 1;
            $newProductId = $data['assign_product_id'];
        } else {
            $flag = 0;
            $data['del'] = 0;
        }
        if (!$flag) {
            $newProduct = $this->productCopier->copy($product);
            $newProductId = $newProduct->getId();
            $data['assign_product_id'] = $newProductId;
            $this->removeImages($newProductId);
        }
       
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $attributData = [
          'status' => $status,
          'description' => $data['description'],
        //   'tax_class_id' => $data['tax_class_id']
        ];
        if ($productType != "configurable") {
            $attributData['price'] = $data['price'];
        }
        $helper->updateProductData([$newProductId], $attributData, $currentStoreId);
        $duplicateProduct = $helper->getProduct($newProductId);
        $sku = $duplicateProduct->getSku();
        if (!$flag) {
            $duplicateProduct->setSpecialPrice(null);
            $duplicateProduct->save();
        }
        if ($productType != "configurable") {
            $this->mpHelper->reIndexData();
            $this->updateStockData($sku, $data['qty'], 1);
        } else {
            $associateProducts = [];
            $updatedProducts = $this->addAssociatedProducts($newProductId, $data);
            $this->mpHelper->reIndexData();
            $data['products'] = $updatedProducts;
            foreach ($updatedProducts as $exProductId => $updatedData) {
                $associateProducts[] = $updatedData['new_product_id'];
                $this->updateStockData($updatedData['sku'], $updatedData['qty'], 1);
            }
            $duplicateProduct->setStatus($status);
            $duplicateProduct->setDescription($data['description']);
            $duplicateProduct->setAssociatedProductIds($associateProducts);
            $duplicateProduct->setCanSaveConfigurableAttributes(true);
            $duplicateProduct->save();
        }
        $result = $helper->processAssignProduct($data, $productType, $flag);
        if ($result['assign_id'] > 0) {
            $this->adminStoreMediaImages($newProductId, $data, $currentStoreId);
            $this->sendAssignProductEmail($data, $flag);
        } else {
            $result['error'] = 1;
        }
        return $result;
    }

    /**
     * AddAssociatedProducts for the configurable Products
     *
     * @param int $productId [Product id of configurable Product]
     * @param mixed $data
     */
    public function addAssociatedProducts($productId, $data)
    {
        $helper = $this->_assignHelper;
        $storeId = $helper->getStoreId();
        $updatedProducts = [];
        if (isset($data['products'])) {
            foreach ($data['products'] as $existingProductId => $associatedProductData) {
                if (isset($associatedProductData['assign_product_id']) && $associatedProductData['assign_product_id']) {
                    $associatedProductData['new_product_id'] = $associatedProductData['assign_product_id'];
                    $product = $helper->getProduct($associatedProductData['assign_product_id']);
                    $associatedProductData['sku'] = $product->getSku();
                    if ($product->getPrice() != $associatedProductData['price']) {
                        $attributData = [
                        'price' => $associatedProductData['price']
                        ];
                        $helper->updateProductData([$product->getId()], $attributData, $storeId);
                    }
                    $updatedProducts[$existingProductId] = $associatedProductData;
                } else {
                    if ($associatedProductData['qty'] && $associatedProductData['price']) {
                        $product = $helper->getProduct($existingProductId);
                        $urlKey = explode("_", $product->getUrlKey());
                        if (count($urlKey) > 1) {
                            $new_url_string = str_replace($urlKey[1], "", trim($product->getUrlKey()));
                        } else {
                            $new_url_string = $product->getUrlKey();
                        }
                        $new_url_key = $new_url_string . '_' . random_int(10, 99);
                        $attributDatas = [
                            'url_key' => $new_url_key
                        ];
                        $helper->updateProductData([$product->getId()], $attributDatas, $storeId);

                        $newProduct = $this->productCopier->copy($product);
                        $attributData = [
                        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                        'price' => $associatedProductData['price'],
                        'special_price' => null
                        ];
                        $helper->updateProductData([$newProduct->getId()], $attributData, $storeId);
                        $associatedProductData['new_product_id'] = $newProduct->getId();
                        $associatedProductData['sku'] = $newProduct->getSku();
                        $updatedProducts[$existingProductId] = $associatedProductData;
                    }
                }
            }
        }
        return $updatedProducts;
    }
   
    /**
     * RemoveImages function is used to remove images of the assigned Product.
     *
     * @param [type] $productId
     * @param integer $storeId
     * @return void
     */
    protected function removeImages($productId, $storeId = 0)
    {
        try {
            $product = $this->productRepository->getById(
                $productId,
                true
            );
            $images = $product->getMediaGalleryImages();
            foreach ($images as $child) {
                $this->imageProcessor->removeImage($product, $child->getFile());
                $this->productGallery->deleteGallery($child->getValueId());
            }
            $product->save();
          
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * AdminStoreMediaImages function is used to store the images of the assigned Product
     *
     * @param [type] $productId
     * @param [type] $wholedata
     * @param integer $storeId
     * @return void
     */
    protected function adminStoreMediaImages($productId, $wholedata, $storeId = 0)
    {
        if (!empty($wholedata['product']['media_gallery'])) {
            $catalogProduct = $this->productFactory->create()->load(
                $productId
            );
            $catalogProduct->addData($wholedata['product'])->save();
        }
    }
}
