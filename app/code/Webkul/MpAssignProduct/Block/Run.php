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
namespace Webkul\MpAssignProduct\Block;

use Magento\Tax\Api\Data\TaxClassKeyInterface;

class Run extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;
   /**
    * Initialization
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
    * @param \Webkul\MpAssignProduct\Logger\Logger $logger
    * @param \Magento\Tax\Api\TaxClassManagementInterface $taxClassManagementInterface
    * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
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
        \Magento\Tax\Api\TaxClassManagementInterface $taxClassManagementInterface,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
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
        $this->taxClassManagementInterface = $taxClassManagementInterface;
        $this->taxClassKeyDataObjectFactory = $taxClassKeyDataObjectFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get CSV product data
     *
     * @param int $profileId
     * @return array
     */
    public function getCsvProductData($profileId)
    {
        $wholeData = [];
        $profileData = $this->getProfileData($profileId);
        $data = $this->jsonHelper->jsonDecode($profileData->getDataRow());
        $data = $this->processData($data);
        $header = $data[0];
        $sellerId = $profileData->getSellerId();
        $allowedProductType = ['simple', 'virtual','configurable'];
       
        foreach (array_slice($data, 1) as $key => $row) {
            $newRow = [];
            $assignProductData = $this->getKeyValue($row, $key, $header);
            $childProducts = [];
            if (isset($assignProductData['error'])) {
                $wholeData[]['error'] = __('Value(s) are empty for one or more required fields at line %1', $row);
            } else {
                try {
                    if ($assignProductData['Parent Product Sku'] != '') {
                        $product = $this->getProductBySku($assignProductData['Parent Product Sku']);
                        $childProducts = $this->getAllAssociatedProductOfParent(
                            array_slice($data, 1),
                            $header,
                            $assignProductData['Parent Product Sku']
                        );
                            
                    } else {
                        $product = $this->getProductBySku($assignProductData['Sku']);
                    }
                    
                    if ($product && $product->getId()) {
                        $productFromSameSeller = $this->checkIfProductFromSameSeller($product->getId(), $sellerId);
                        $productExisst = in_array($product->getId(), array_column($wholeData, 'product_id'));
                        if ($productFromSameSeller) {
                            $wholeData[]['error'] =
                            (__('Product from same seller can\'t be Assigned, 
                            Product Sku is %1', $assignProductData['Sku']));
                        } elseif (!in_array($product->getTypeId(), $allowedProductType)) {
                            $wholeData[]['error'] =
                            (__('Assign product is available for Simple, Configurable and Virtual type products.'));
                        } elseif (!$productExisst) {
                            $data1 = $this->prepareData($product, $assignProductData, $sellerId, $childProducts);
                            $wholeData[] = $data1;
                           
                        }
                    } else {
                        $wholeData[]['error'] =  __('Invalid Sku: " %1"', $assignProductData['Sku']);
                    }
                } catch (\Exception $e) {
                    $wholeData[]['error'] =  __('Something went wrong: " %1"', $assignProductData['Sku']);
                }
            }

        }
        return $wholeData;
    }

    /**
     * Process Data
     *
     * @param array $data
     * @return array
     */
    public function processData($data)
    {
        $returndata = [];
        if ($data) {
            foreach ($data as $key => $value) {
                if (!empty($value[0])) {
                    $returndata[$key] = $data[$key];
                }
            }
        }
        return $returndata;
    }

    /**
     * Get All Associated Product Row
     *
     * @param array $data
     * @param array $header
     * @param int $productId
     * @return array
     */
    public function getAllAssociatedProductOfParent($data, $header, $productId)
    {
        $childProducts = [];
        foreach ($data as $key => $row) {
            $assignProductData = $this->getKeyValue($row, $key, $header);
            if ($assignProductData['Parent Product Sku'] === $productId) {
                $childProducts[$key] = $assignProductData;
            }
        }
        return $childProducts;
    }
   /**
    * Prepare Csv row
    *
    * @param object $product
    * @param array $csvData
    * @param int $sellerId
    * @param array $childProducts
    * @return void
    */
    public function prepareData($product, $csvData, $sellerId, $childProducts = [])
    {
        try {
            $newRow = [];
            if ($product->getTypeId() == 'configurable') {
                
                $newRow['product_id'] = $product->getId();
                $newRow['description'] = $csvData['Description'];
                if ($csvData['Tax Class'] != '') {
                    $newRow['tax_class_id'] = $this->getTaxClassId($csvData['Tax Class']);
                } else {
                    $newRow['tax_class_id'] = 0;
                }
                $newRow['product_condition'] = $this->getProductCondition($csvData['Product Condition']);
                foreach ($childProducts as $key => $childProduct) {
                    $childProductData = $this->getProductBySku($childProduct['Sku']);
                    if ($childProductData && $childProductData->getId()) {
                        $newRow['products'][$childProductData->getId()]['id'] = 1;
                        $newRow['products'][$childProductData->getId()]['qty'] = $childProduct['Quantity'];
                        $newRow['products'][$childProductData->getId()]['price'] = $childProduct['Price'];
                    }
                    $assignProduct = $this->checkIfAssignProductExists($product->getId(), $sellerId);
                    if (isset($assignProduct['assign_id']) && $assignProduct['assign_id'] !='') {
                        $associatesData = $this->helper->getAssociatesData($assignProduct['assign_id']);
                        if ($childProductData && array_key_exists($childProductData->getId(), $associatesData)) {
                            $newRow['products'][$childProductData->getId()]['associate_id'] =
                            $associatesData[$childProductData->getId()]['id'];
                            $newRow['products'][$childProductData->getId()]['assign_product_id'] =
                            $associatesData[$childProductData->getId()]['assign_product_id'];
                        }
                    }
                }
                $newRow = array_merge($this->getImagesData($csvData), $newRow);
                
                $newRow = array_merge($assignProduct, $newRow);
            } else {
                $newRow['product_id'] = $product->getId();
                $newRow['qty'] = $csvData['Quantity'];
                $newRow['description'] = $csvData['Description'];
                $newRow['price'] = $csvData['Price'];
                if (isset($csvData['Tax Class']) && $csvData['Tax Class'] != '') {
                    $newRow['tax_class_id'] = $this->getTaxClassId($csvData['Tax Class']);
                } else {
                    $newRow['tax_class_id'] = 0;
                }
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

    /**
     * GetImagesData
     *
     * @param [type] $assignProductData
     * @return array
     */
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
     * Get product condition value
     *
     * @param string $productCondition
     * @return bool
     */
    public function getProductCondition($productCondition)
    {
        $value = 0;
        if ($productCondition == 'New') {
            $value = 1;
        } elseif ($productCondition == 'Used') {
            $value = 2;
        }
        return $value;
    }
    /**
     * Check if product from same seller
     *
     * @param [type] $productId
     * @param [type] $sellerId
     * @return bool
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
     * Prepare row data of csv row
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
            } elseif ($value == 'Product Images' || $value == 'Parent Product Sku' || $value == 'Tax Class') {
                $temp[$value] = $row[$key];
            } else {
                $temp['error'] = true;
            }
        }
        return $temp;
    }
    /**
     * Get product data by sku
     *
     * @param [type] $sku
     * @return ] array
     */
    public function getProductBySku($sku)
    {
        try {
            return $this->productRepository->get($sku);
        } catch (\Exception $e) {
            return false;
        }
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
        $count = 0;
        foreach ($data as $value) {
            if (!"" == $value[0]) {
                $count++;
            }
        }
        if ($count >= 1) {
            --$count;
        }
       
        return $count;
    }

    /**
     * Get profile data
     *
     * @param integer $profileId
     * @return object
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
     * Get profile id
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->getRequest()->getParam('profile');
    }
    /**
     * Get tax id by class name
     *
     * @param string $className
     * @return int
     */
    public function getTaxClassId($className)
    {
        $taxClassId = $this->taxClassManagementInterface->getTaxClassId(
            $this->taxClassKeyDataObjectFactory->create()
                ->setType(TaxClassKeyInterface::TYPE_NAME)
                ->setValue($className)
        );
        return $taxClassId;
    }
    /**
     * Encode json data
     *
     * @param [type] $data
     * @return void
     */
    public function jsonEncode($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }
}
