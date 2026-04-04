<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Block;

class Run extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;
    /**
     * initialization
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Webkul\MpAssignProduct\Model\ProfileFactory $profileFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Webkul\MpAssignProduct\Controller\Product\Save $saveConstroller
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $assignItems
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpAssignProduct\Model\ProfileFactory $profileFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Webkul\MpAssignProduct\Controller\Product\Save $saveConstroller,
        \Webkul\MpAssignProduct\Model\ItemsFactory $assignItems,
        \Webkul\MpAssignProduct\Logger\Logger $logger,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_url = $url;
        $this->_session = $session;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->profileFactory  = $profileFactory;
        $this->jsonHelper = $jsonHelper;
        $this->productRepository = $productRepository;
        $this->mpProductFactory = $mpProductFactory;
        $this->saveController = $saveConstroller;
        $this->assignItemsFactory = $assignItems;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getCsvProductData($profileId)
    {
        $wholeData = [];
        $profileData = $this->getProfileData($profileId);
        $data = $this->jsonHelper->jsonDecode($profileData->getDataRow());
        $header = $data[0];
        $sellerId = $profileData->getSellerId();
        $allowedProductType = ['simple', 'virtual','configurable'];

        foreach (array_slice($data, 1) as $key => $row) {
            $newRow = [];
            $assignProductData = $this->getKeyValue($row, $key, $header);
            if (isset($assignProductData['error'])) {
                $wholeData[]['error'] = 'Value(s) are empty for one or more required fields at line %1';
            } else {
                try {
                    if ($assignProductData['Parent Product Sku'] != '') {
                        $product = $this->getProductBySku($assignProductData['Parent Product Sku']);
                    } else {
                        $product = $this->getProductBySku($assignProductData['Sku']);
                    }
                    if ($product->getId()) {
                        $productFromSameSeller = $this->checkIfProductFromSameSeller($product->getId(), $sellerId);
                        if ($productFromSameSeller) {
                            $wholeData[]['error'] =
                            (__('Product from same seller can\'t be Assigned, 
                            Product Sku is %1', $assignProductData['Sku']));
                        } elseif (!in_array($product->getTypeId(), $allowedProductType)) {
                            $wholeData[]['error'] =
                            (__('Assign product is available for Simple, Configurable and Virtual type products.'));
                        } else {
                            $data1 = $this->prepareData($product, $assignProductData, $sellerId);
                            $wholeData[] = $data1;
                        }
                    }
                } catch (\Exception $e) {
                    $wholeData[]['error'] =  __('Invalid Sku: " %1"', $assignProductData['Sku']);
                }
            }
            
        }
        return $wholeData;
    }

    /**
     * prepare data for assign
     *
     * @param [type] $product
     * @param [type] $csvData
     * @param [type] $sellerId
     * @return void
     */
    public function prepareData($product, $csvData, $sellerId)
    {
        try {
            $newRow = [];
            if ($product->getTypeId() == 'configurable') {
                $newRow['product_id'] = $product->getId();
                $newRow['description'] = $csvData['Description'];
                $newRow['product_condition'] = $this->getProductCondition($csvData['Product Condition']);
                $childProduct = $this->getProductBySku($csvData['Sku']);
                $newRow['products'][$childProduct->getId()]['id'] = 1;
                $newRow['products'][$childProduct->getId()]['qty'] = $csvData['Quantity'];
                $newRow['products'][$childProduct->getId()]['price'] = $csvData['Price'];
                $newRow = array_merge($this->getImagesData($csvData), $newRow);
                $assignProduct = $this->checkIfAssignProductExists($product->getId(), $sellerId);
                if (isset($assignProduct['assign_id']) && $assignProduct['assign_id'] !='') {
                    $associatesData = $this->helper->getAssociatesData($assignProduct['assign_id']);
                    if (array_key_exists($childProduct->getId(), $associatesData)) {
                        $newRow['products'][$childProduct->getId()]['associate_id'] =
                        $associatesData[$childProduct->getId()]['id'];
                        $newRow['products'][$childProduct->getId()]['assign_product_id'] =
                        $associatesData[$childProduct->getId()]['assign_product_id'];
                    }
                }
                
                $newRow = array_merge($assignProduct, $newRow);
            } else {
                $newRow['product_id'] = $product->getId();
                $newRow['qty'] = $csvData['Quantity'];
                $newRow['description'] = $csvData['Description'];
                $newRow['price'] = $csvData['Price'];
                $newRow['product_condition'] = $this->getProductCondition($csvData['Product Condition']);
                $newRow = array_merge($this->getImagesData($csvData), $newRow);
                $assignProduct = $this->checkIfAssignProductExists($product->getId(), $sellerId);
                $newRow = array_merge($assignProduct, $newRow);
            }
        } catch (\Exception $e) {
            $wholedata[]['error'] = __('Something went wrong.');
        }
        
        return $newRow;
    }

    public function getImagesData($assignProductData)
    {
        try {
            $wholedata = [];
            $data=[];
            $data['product']['sku'] = $assignProductData['Sku'];
            $data['product']['images'] = $assignProductData['Product Images'];
            $profileId = $this->getProfileId();
            $wholedata = $this->helper->processImageData($wholedata, $data, $profileId);
            return $wholedata;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * check if product is already assigned
     *
     * @param [type] $productId
     * @param [type] $sellerId
     * @return void
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
     * get product condition value
     *
     * @param [type] $productCondition
     * @return void
     */
    public function getProductCondition($productCondition)
    {
        $value = 0;
        if ($productCondition == 'New') {
            $value = 1;
        }
        return $value;
    }
    /**
     * check if product from same seller
     *
     * @param [type] $productId
     * @param [type] $sellerId
     * @return void
     */
    public function checkIfProductFromSameSeller($productId, $sellerId)
    {
        $productExists = false;
        $productData = $this->mpProductFactory->create()->getCollection()
        ->addFieldToFilter('mageproduct_id', $productId)
        ->addFieldToFilter('seller_id', $sellerId)->getFirstItem();
        if (!empty($productData->getData())) {
            $productExists = true;
        }
        return $productExists;
    }
    /**
     * prepare row data of csv row
     *
     * @param [type] $row
     * @param [type] $rowKey
     * @param [type] $tagsArray
     * @return void
     */
    protected function getKeyValue($row, $rowKey, $tagsArray)
    {
        $temp = [];
        foreach ($tagsArray as $key => $value) {
            if ($value != 'Product Images' && $row[$key] != '') {
                $temp[$value] = $row[$key];
            } elseif ($value == 'Product Images' || $value == 'Parent Product Sku') {
                $temp[$value] = $row[$key];
            } else {
                $temp['error'] = true;
            }
        }
        return $temp;
    }
    /**
     * get product data by sku
     *
     * @param [type] $sku
     * @return ] array
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }
    /**
     * Get Total Product to Upload
     *
     * @param int $profileId
     *
     * @return int
     */
    public function getTotalCount($profileId = 0)
    {
        $type = '';
        $data = $this->getProfileData($profileId);
        $data = $this->jsonHelper->jsonDecode($data->getDataRow());
        $count = count($data);
        if ($count >= 1) {
            --$count;
        }
       
        return $count;
    }

    /**
     * get profile data
     *
     * @param integer $profileId
     * @return array
     */
    public function getProfileData($profileId = 0)
    {
        if ($profileId == 0) {
            $id = (int) $this->getRequest()->getParam('id');
        } else {
            $id = $profileId;
        }
        $profileData = $this->profileFactory->create()->getCollection()
        ->addFieldToFilter('entity_id', $profileId)->getFirstItem();
    
        return $profileData;
    }
    /**
     * get profile id
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->getRequest()->getParam('profile');
    }
    /**
     * encode json data
     *
     * @param [type] $data
     * @return void
     */
    public function jsonEncode($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }
}
