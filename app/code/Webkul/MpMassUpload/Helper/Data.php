<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMassUpload\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Filesystem\Driver\File;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\CollectionFactory as AttributeProfile;
use Webkul\MpMassUpload\Api\AttributeProfileRepositoryInterface;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeMapping\CollectionFactory as AttributeMapping;
use Webkul\MpMassUpload\Api\AttributeMappingRepositoryInterface;
use Webkul\MpMassUpload\Api\ProfileRepositoryInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;
use Magento\Framework\Filesystem\Io\File as fileUpload;
use Magento\Framework\Serialize\SerializerInterface;
use \Magento\Store\Model\StoreRepository;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_entity;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * @var \Webkul\MpMassUpload\Model\ProfileFactory
     */
    protected $_profile;

    /**
     * @var \Webkul\Marketplace\Controller\Product\SaveProduct
     */
    protected $_saveProduct;

    /**
     * @var SellerCollection
     */
    protected $_sellerCollection;

    /**
     * @var CategoryCollection
     */
    protected $_categoryCollection;

    /**
     * @var AttributeCollection
     */
    protected $_attributeCollection;

    /**
     * @var CustomerCollection
     */
    protected $_customerCollection;

    /**
     * @var AttributeSetCollection
     */
    protected $_attributeSetCollection;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_fileDriver;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvReader;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;

    /**
     * @var \Webkul\MpMassUpload\Model\Zip
     */
    protected $_zip;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $_customerGroup;

    /**
     * @var Parser
     */
    protected $_parser;

    /**
     * @var File
     */
    protected $_file;

    /**
     * @var AttributeProfile
     */
    protected $_attributeProfile;

    /**
     * @var AttributeProfileRepositoryInterface
     */
    protected $_attributeProfileRepository;

    /**
     * @var AttributeMapping
     */
    protected $_attributeMapping;

    /**
     * @var AttributeMappingRepositoryInterface
     */
    protected $_attributeMappingRepository;

    /**
     * @var ProfileRepositoryInterface
     */
    protected $_profileRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * File helper downloadable product
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var ConfigurableProTypeModel
     */
    protected $_configurableProTypeModel;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $_serializerInterface;

    /**
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $_mpProduct;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var StoreRepository
     */
    protected $_storeRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\MpMassUpload\Model\ProfileFactory $profile
     * @param \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct
     * @param SellerCollection $sellerCollectionFactory
     * @param CategoryCollection $categoryCollectionFactory
     * @param AttributeCollection $attributeCollectionFactory
     * @param CustomerCollection $customerCollectionFactory
     * @param AttributeSetCollection $attributeSetCollectionFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\File\Csv $csvReader
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\MpMassUpload\Model\Zip $zip
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\Group $customerGroup
     * @param Parser $parser
     * @param File $file
     * @param AttributeProfile $attributeProfile
     * @param AttributeProfileRepositoryInterface $attributeProfileRepository
     * @param AttributeMapping $attributeMapping
     * @param AttributeMappingRepositoryInterface $attributeMappingRepository
     * @param ProfileRepositoryInterface $profileRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProduct
     * @param ConfigurableProTypeModel $configurableProTypeModel
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param fileUpload $fileUpload
     * @param DirectoryList $directoryList
     * @param SerializerInterface $serializerInterface
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Psr\Log\LoggerInterface $logger
     * @param StoreRepository $storeRepository
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Eav\Model\Entity $entity,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpMassUpload\Model\ProfileFactory $profile,
        \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct,
        SellerCollection $sellerCollectionFactory,
        CategoryCollection $categoryCollectionFactory,
        AttributeCollection $attributeCollectionFactory,
        CustomerCollection $customerCollectionFactory,
        AttributeSetCollection $attributeSetCollectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\File\Csv $csvReader,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\MpMassUpload\Model\Zip $zip,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Group $customerGroup,
        Parser $parser,
        File $file,
        AttributeProfile $attributeProfile,
        AttributeProfileRepositoryInterface $attributeProfileRepository,
        AttributeMapping $attributeMapping,
        AttributeMappingRepositoryInterface $attributeMappingRepository,
        ProfileRepositoryInterface $profileRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Downloadable\Helper\File $fileHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\Marketplace\Model\ProductFactory $mpProduct,
        ConfigurableProTypeModel $configurableProTypeModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        fileUpload $fileUpload,
        DirectoryList $directoryList,
        SerializerInterface $serializerInterface,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $logger,
        StoreRepository $storeRepository,
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel
    ) {
        $this->_request                     = $context->getRequest();
        $this->_moduleManager               = $context->getModuleManager();
        $this->_storeManager                = $storeManager;
        $this->_customerSession             = $customerSession;
        $this->_filesystem                  = $filesystem;
        $this->_entity                      = $entity;
        $this->_config                      = $config;
        $this->_formKey                     = $formKey;
        $this->_product                     = $productFactory;
        $this->_saveProduct                 = $saveProduct;
        $this->_sellerCollection            = $sellerCollectionFactory;
        $this->_categoryCollection          = $categoryCollectionFactory;
        $this->_attributeCollection         = $attributeCollectionFactory;
        $this->_customerCollection          = $customerCollectionFactory;
        $this->_attributeSetCollection      = $attributeSetCollectionFactory;
        $this->_fileDriver                  = $fileDriver;
        $this->_csvReader                   = $csvReader;
        $this->_fileUploader                = $fileUploaderFactory;
        $this->_zip                         = $zip;
        $this->_objectManager               = $objectManager;
        $this->_resource                    = $resource;
        $this->_customerGroup               = $customerGroup;
        $this->_parser                      = $parser;
        $this->_file                        = $file;
        $this->_attributeProfile            = $attributeProfile;
        $this->_attributeProfileRepository  = $attributeProfileRepository;
        $this->_attributeMapping            = $attributeMapping;
        $this->_attributeMappingRepository  = $attributeMappingRepository;
        $this->_profile                     = $profile;
        $this->_profileRepository           = $profileRepository;
        $this->_jsonHelper                  = $jsonHelper;
        $this->fileHelper                   = $fileHelper;
        $this->marketplaceHelper            = $marketplaceHelper;
        $this->_mpProduct                   = $mpProduct;
        $this->_configurableProTypeModel    = $configurableProTypeModel;
        $this->_timezoneInterface           = $timezoneInterface;
        $this->fileUpload                   = $fileUpload;
        $this->directoryList                = $directoryList;
        $this->_serializerInterface         = $serializerInterface;
        $this->_pricingHelper               = $pricingHelper;
        $this->logger                       = $logger;
        $this->_date                        = $date;
        $this->_storeRepository             = $storeRepository;
        $this->sellerModel                  = $sellerModel;
        $this->userContext                  = $userContext;
        parent::__construct($context);
    }

    /**
     * Get Csv Product Type
     *
     * @param array $uploadedFileRowData
     *
     * @return string
     */
    public function getProductType($uploadedFileRowData)
    {
        if ($this->getCount($uploadedFileRowData) > 0) {
            if (in_array('weight', $uploadedFileRowData[0])) {
                if (in_array('_super_attribute_code', $uploadedFileRowData[0])) {
                    return 'configurable';
                }
                return 'simple';
            } else {
                if (in_array('downloadable_link_file', $uploadedFileRowData[0])) {
                    return 'downloadable';
                } elseif (in_array('_super_attribute_code', $uploadedFileRowData[0])) {
                    return 'configurable';
                } else {
                    return 'virtual';
                }
            }
        }
        return '';
    }

    /**
     * Get Csv Type
     *
     * @param int $profileId
     *
     * @return string
     */
    public function getCsvType($profileId)
    {
        $profileData = $this->getProfileData($profileId);
        return $profileData->getProductType();
    }

    /**
     * Get Current Customer Id
     *
     * @return int
     */
    public function getCustomerId()
    {
        $customerId = 0;
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = (int) $this->marketplaceHelper->getCustomerId();
        }
        return $customerId;
    }

    /**
     * Check Customer is Logged In or Not
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return true;
        }
        return false;
    }

    /**
     * Get Media Path
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * Get Base Path
     *
     * @param int $profileId
     *
     * @return string
     */
    public function getBasePath($profileId)
    {
        $mediaPath = $this->getMediaPath();
        $basePath = $mediaPath . 'marketplace/massupload/' . $profileId . "/";
        return $basePath;
    }

    /**
     * Get Profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        $profiles = ['' => __('Select Profile')];
        $collection = $this->_profileRepository->getBySellerId($this->getCustomerId());
        foreach ($collection as $item) {
            $profiles[$item->getId()] = $item->getProfileName();
        }

        return $profiles;
    }

   /**
    * Get attribute profiles
    *
    * @param integer $flag
    * @return array
    */
    public function getAttributeProfiles($flag = 0)
    {
        $profiles = ['' => __('Select Dataflow Profile')];
        if ($this->getCustomerId()) {
            $sellerId = $this->getCustomerId();
            $collection = $this->_attributeProfileRepository->getBySellerId(
                $sellerId
            );
        } else {
            $sellerId = 0;
            $collection = $this->_attributeProfileRepository->getList();
        }
        if ($flag == 1) {
            return $collection;
        }
        foreach ($collection as $item) {
            $profiles[$item->getId()] = $item->getProfileName();
        }

        return $profiles;
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
        if ($data) {
            $type = $data->getProductType();
        }
        $isConfigurableAllowed = $this->isProductTypeAllowed('configurable');
        if ($type == 'configurable' && $isConfigurableAllowed) {
            $uploadedFileRowData = $this->getUploadedFileRowData($profileId);
            $count = $this->getCount($this->getConfigurableFormatCsv($uploadedFileRowData, 1));
        } else {
            $count = $this->getCount($this->getUploadedFileRowData($profileId));
            if ($count >= 1) {
                --$count;
            }
        }
        return $count;
    }

    /**
     * Get Csv Data
     *
     * @param int $profileId
     *
     * @return array
     */
    public function getUploadedFileRowData($profileId = 0)
    {
        try {
            $uploadedFileRowData = [];
            $data = $this->getProfileData($profileId);
            if ($data) {
                $uploadedFileRowData = $this->_serializerInterface->unserialize($data->getDataRow());
            }
            return $uploadedFileRowData;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Uploaded Profile Data
     *
     * @param int $profileId
     *
     * @return \Webkul\MpMassUpload\Api\ProfileRepositoryInterface
     */
    public function getProfileData($profileId = 0)
    {
        if ($profileId == 0) {
            $id = (int) $this->_request->getParam('id');
        } else {
            $id = $profileId;
        }
        $data = $this->_profileRepository->get($id);
        return $data;
    }

   /**
    * Read csv file
    *
    * @param string $csvFilePath
    * @param array $attributeMappedArr
    * @return array
    */
    public function readCsv($csvFilePath, $attributeMappedArr)
    {
        try {
            $uploadedFileRowData = $this->_csvReader->getData($csvFilePath);
            // Start: Coverting uploaded file data attributes into magento attributes
            foreach ($uploadedFileRowData[0] as $key => $productkey) {
                if (!empty($attributeMappedArr[$productkey])) {
                    $productkey = $attributeMappedArr[$productkey];
                    $uploadedFileRowData[0][$key] = $productkey;
                }
            }
            // End: Coverting uploaded file data attributes into magento attributes
        } catch (\Exception $e) {
            $uploadedFileRowData = [];
        }
        return $uploadedFileRowData;
    }

    /**
     * Validate csv data
     *
     * @param array $uploadedFileRowData
     * @return array
     */
    public function validateCsvData($uploadedFileRowData)
    {
        $productType = $this->getProductType($uploadedFileRowData);
        $result = ['error' => false, 'type' => $productType];
        if ($productType == '') {
            $msg = 'Something went wrong.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Get attribute set id
     *
     * @param int $profileId
     * @return int
     */
    public function getAttributeSetId($profileId)
    {
        $attributeSetId = 0;
        $data = $this->_profileRepository->get($profileId);
        if ($data) {
            $attributeSetId = $data->getAttributeSetId();
        }
        return $attributeSetId;
    }

    /**
     * Get Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }

    /**
     * Check for Valid Sku to Upload Product
     *
     * @param int|string $sku
     *
     * @return bool
     */
    public function isValidSku($sku)
    {
        if ($sku == '') {
            return false;
        }
        $productId = $this->_product->create()->getIdBySku($sku);
        if ($productId) {
            return false;
        }
        return true;
    }

    /**
     * Check for Valid Profile
     *
     * @return bool
     */
    public function isValidProfile()
    {
        $id = (int) $this->_request->getParam('id');
        $profileRowData = $this->getUploadedFileRowData($id);
        if (!empty($profileRowData)) {
            return true;
        }
        return false;
    }

    /**
     * Validate Product Data
     *
     * @param array $data
     * @param string $profileType
     * @param int $row
     *
     * @return array
     */
    public function validateFields($data, $profileType, $row)
    {
        $data = $this->prepareProductDataIfNotSet($data, $profileType);
        if (empty($data['product'])) {
            $result['error'] = 1;
            $result['data'] = $data;
            $result['msg'] = __('Skipped row %1. product data can not be empty.', $row);
            return $result;
        } else {
            $name = $data['product']['name'];
            $sku = $data['product']['sku'];
            $description = $data['product']['description'];
            $weight = $data['product']['weight'];
            if (strlen($name) <= 0) {
                $result['error'] = 1;
                $result['data'] = $data;
                $result['msg'] = __('Skipped row %1. product name can not be empty.', $row);
                return $result;
            }
            if (strlen($description) <= 0) {
                $result['error'] = 1;
                $result['data'] = $data;
                $result['msg'] = __('Skipped row %1. product description can not be empty.', $row);
                return $result;
            }
            if (($profileType != 'virtual') && ($profileType != 'downloadable') && strlen($weight) <= 0) {
                $result['error'] = 1;
                $result['data'] = $data;
                $result['msg'] = __('Skipped row %1. product weight can not be empty.', $row);
                return $result;
            }
            if (strlen($sku) <= 0) {
                $result['error'] = 1;
                $result['data'] = $data;
                $result['msg'] = __('Skipped row %1. product sku can not be empty.', $row);
                return $result;
            }
            $productId = $this->_product->create()->getIdBySku($sku);
            if ($productId) {
                $data = $this->existingProductDataMapping($data, $productId);
            }
        }
        return ['error' => 0, 'data' => $data];
    }

    /**
     * Maps product data if already existing
     *
     * @param array $data
     * @param int $productId
     * @return array
     */
    public function existingProductDataMapping($data, $productId)
    {
        $product = $this->_product->create()->load($productId);
        // Existing Product Data Mapping Start
        $productArray = $product->getData();
        foreach ($data['product'] as $key => $value) {
            if (empty($value)) {
                if ($key == 'stock') {
                    $data['product']['stock'] = $productArray['quantity_and_stock_status']['qty'];
                }
                if ($key == 'type') {
                    $data['product']['type'] = $productArray['type_id'];
                }
                if (array_key_exists($key, $productArray)) {
                    $data['product'][$key] = $productArray[$key];
                }
            }
        }
        //Existing Product Image Data Mapping Start
        if (!empty($data['product']['images']) && !empty($productArray['image'])) {
            $imageArray = [];
            if (substr_count($data['product']['images'], ',') >= 0) {
                $str = $data['product']['images'];
                $len = strlen($str);
                $count = substr_count($str, ',');
                $j = 0;
                $pos = strpos($str, ',', $j);
                if (substr_count($data['product']['images'], ',') == 0) {
                    $pos = $len;
                }
                for ($i = 1; $i <= ($count + 1); $i++) {
                    $posOfComma = strpos($str, ',', $j);
                    $imgName = substr($str, $j, $pos);
                    // Validate whether image contains url or not
                    if ((filter_var($imgName, FILTER_VALIDATE_URL)) || (strrpos($imgName, '/'))) {
                        $urlLength = strlen($imgName);
                        $posOfSlash = strrpos($imgName, '/');
                        $newImgName = substr($imgName, $posOfSlash + 1, $urlLength);
                        $imageArray[$i] = $newImgName;
                    } else {
                        $imageArray[$i] = $imgName;
                    }
                    $j = $posOfComma + 1;
                }
            }
            foreach ($productArray['media_gallery']['images'] as $imagesData) {
                $str = $imagesData['file'];
                $len = strlen($str);
                $pos = strrpos($str, '/');
                $imageName = substr($str, $pos + 1, $len);
                $imageArrayCount = count($imageArray);
                for ($i = 1; $i <= $imageArrayCount; $i++) {
                    if ($imageArray[$i] == $imageName) {
                        $imageArray[$i] = "";
                    }
                }
            }
            $result = array_filter($imageArray);
            $data['product']['images'] = implode(',', array_values($result));
        }
        $data['id'] = $productId;
        $data['product_id'] = $productId;
        $data['product']['website_ids'][] = $product->getStore()->getWebsiteId();

        return $data;
    }

    /**
     * Prepare Product Data If NotSet
     *
     * @param array $data
     * @param string $profileType
     *
     * @return array
     */
    public function prepareProductDataIfNotSet($data, $profileType)
    {
        $fields = [
            "product" => [
                "name" => "",
                "category" => "",
                "description" => "",
                "short_description" => "",
                "sku" => "",
                "stock" => "",
                "price" => "",
                "special_price" => "",
                "special_from_date" => "",
                "special_to_date" => "",
                "weight" => 0,
                "mp_product_cart_limit" => "",
                "visibility" => "",
                "tax_class_id" => "",
                "meta_title" => "",
                "meta_keyword" => "",
                "meta_description" => "",
                "images" => "",
                "downloadable_link_file" => "",
                "links_title" => "",
                "links_purchased_separately" => "",
                "samples_title" => "",
                "downloadable_link_file" => "",
                "downloadable_link_price" => "",
                "downloadable_link_title" => "",
                "downloadable_link_type" => "",
                "downloadable_link_sample" => "",
                "downloadable_link_is_sharable" => "",
                "downloadable_link_is_unlimited" => "",
                "downloadable_link_number_of_downloads" => "",
                "downloadable_link_sample_type" => "",
                "custom_option" => "",
                "_super_attribute_option" => "",
                "_super_attribute_option" => "",
                "seller_id" => "",
                "url_key" => ""
            ],
        ];
        $data = $this->setFieldsValue($data, $fields);

        return $data;
    }

    /**
     * Validate and Set Default Values for Fields
     *
     * @param array $persistentData
     * @param array $fields
     *
     * @return array
     */

    public function setFieldsValue(&$persistentData, $fields)
    {
        foreach ($fields['product'] as $key => $field) {
            if (empty($persistentData['product'][$key])) {
                $persistentData['product'][$key] = $field;
            }
        }
        return $persistentData;
    }

    /**
     * Prepare Product Data If NotSet
     *
     * @param array $childRow
     * @param array $data
     *
     * @return array
     */
    public function prepareAssociatedProductIfNotSet($childRow, $data)
    {
        if (empty($childRow['product']['name'])) {
            $childRow['product']['name'] = $data['product']['name'];
        }
        if (empty($childRow['product']['weight'])) {
            $childRow['product']['weight'] = $data['product']['weight'];
        }
        if (empty($childRow['product']['stock'])) {
            $childRow['product']['stock'] = $data['product']['stock'];
        }
        if (empty($childRow['product']['price'])) {
            $childRow['product']['price'] = $data['product']['price'];
        }
        return $childRow;
    }

    /**
     * Save Product
     *
     * @param int $profileId
     * @param int $row
     * @return array
     */
    public function getProductPostData($profileId, $row)
    {
        $sellerId = $this->getCustomerId();
        $profileType = $this->getCsvType($profileId);
        $productRowData = $this->calculateProductRowData(
            $sellerId,
            $profileId,
            $row,
            $profileType
        );
        return $productRowData;
    }

    /**
     * Calculate Product Row Data
     *
     * @param int $sellerId
     * @param int $profileId
     * @param int $row
     * @param string $profileType
     *
     * @return array
     */
    public function calculateProductRowData($sellerId, $profileId, $row, $profileType)
    {
        $uploadedFileRowData = $this->getUploadedFileRowData($profileId);
        $mainRow = $row;
        $isConfigurableAllowed = $this->isProductTypeAllowed('configurable');
        if ($profileType == 'configurable' && $isConfigurableAllowed) {
            $rowIndexArr = $this->getConfigurableFormatCsv($uploadedFileRowData, 1);
            if (!empty($rowIndexArr[$row])) {
                $row = $rowIndexArr[$row];
            }
            $childRowIndexArr = $this->getConfigurableFormatCsv($uploadedFileRowData, 0);
            if (!empty($childRowIndexArr[$mainRow])) {
                $childRowArr = $childRowIndexArr[$mainRow];
            } else {
                $childRowArr = [];
            }
        }
        if (!array_key_exists($row, $uploadedFileRowData)) {
            $wholeData['error'] = 1;
            $wholeData['msg'] = __('Product data for row %1 does not exist', $mainRow);
        }
        // Prepare product row data
        $i = 0;
        $j = 0;
        $data = [];
        if (!empty($uploadedFileRowData[$row])) {
            $data = $uploadedFileRowData[$row];
        }
        $customData = [];
        $customData['product'] = [];
        $csvAttributeList = [];
        foreach ($uploadedFileRowData[0] as $value) {
            $csvAttributeList[$value] = $value;
            if (!empty($data[$i])) {
                $customData['product'][$value] = $data[$i];
            } else {
                $customData['product'][$value] = '';
            }
            $i++;
        }
        $data = $customData;
        
        $validate = $this->validateFields(
            $data,
            $profileType,
            $mainRow
        );
        if ($validate['error']) {
            $wholeData['error'] = $validate['error'];
            $wholeData['msg'] = $validate['msg'];
        }
        $data = $validate['data'];
        /*Calculate product weight*/
        $hasWeight = 1;
        $isDownloadableAllowed = $this->isProductTypeAllowed('downloadable');
        $isVirtualAllowed = $this->isProductTypeAllowed('virtual');
        if (($profileType == 'virtual' && $isVirtualAllowed) ||
            ($profileType == 'downloadable' && $isDownloadableAllowed)
        ) {
            $weight = 0;
            $hasWeight = 0;
        } else {
            $weight = $data['product']['weight'];
        }
        /*Get Category ids by category name (set by comma seperated)*/
        $categoryIds = $this->getCategoryIds($data['product']['category']);
        /*Get $taxClassId by tax*/
        $taxClassId = $this->getAttributeOptionIdbyOptionText(
            "tax_class_id",
            trim($data['product']['tax_class_id'])
        );
        $isInStock = 1;
        if (!empty($data['product']['stock']) && !(int)$data['product']['stock']) {
            $isInStock = 0;
        } elseif (empty($data['product']['stock'])) {
            $data['product']['stock'] = '';
        }
        if (empty($data['product']['is_in_stock']) || $data['product']['is_in_stock'] == 'Out of Stock') {
            $isInStock = 0;
        }
        $attributeSetId = $this->getAttributeSetId($profileId);
        $wholeData['form_key'] = $this->getFormKey();
        $wholeData['type'] = $profileType;
        $wholeData['set'] = $attributeSetId;
        if (!empty($data['id'])) {
            $wholeData['id'] = $data['id'];
            $wholeData['product_id'] = $data['product_id'];
            $wholeData['product']['website_ids'] = $data['product']['website_ids'];
            if (!empty($data['product']['weight']) && $data['product']['weight'] != 0) {
                $weight = $data['product']['weight'];
                $hasWeight = 1;
            }
        }
        $wholeData['product']['category_ids'] = $categoryIds;
        $wholeData['product']['name'] = $data['product']['name'];
        $wholeData['product']['short_description'] = $data['product']['short_description'];
        $wholeData['product']['description'] = $data['product']['description'];
        $wholeData['product']['sku'] = $data['product']['sku'];
        $wholeData['product']['price'] = $data['product']['price'];
        $wholeData['product']['visibility'] = 4;
        $wholeData['product']['tax_class_id'] = $taxClassId;
        $wholeData['product']['product_has_weight'] = $hasWeight;
        $wholeData['product']['weight'] = $weight;
        $wholeData['product']['stock_data']['manage_stock'] = 1;
        $wholeData['product']['stock_data']['use_config_manage_stock'] = 1;
        $wholeData['product']['quantity_and_stock_status']['qty'] = $data['product']['stock'];
        $wholeData['product']['quantity_and_stock_status']['is_in_stock'] = $isInStock;
        $wholeData['product']['meta_title'] = $data['product']['meta_title'];
        $wholeData['product']['meta_keyword'] = $data['product']['meta_keyword'];
        $wholeData['product']['meta_description'] = $data['product']['meta_description'];
        $wholeData['product']['seller_id'] = $data['product']['seller_id'];
        $wholeData['product']['url_key'] = $data['product']['url_key'];
        /*START :: Set Special Price Info*/
        $wholeData = $this->processSpecialPriceData($wholeData, $data);
        /*Set Image Info*/
        $wholeData = $this->processImageData($wholeData, $data, $profileId);
        /*Set Downloadable Data*/
        $isDownloadableAllowed = $this->isProductTypeAllowed('downloadable');
        if ($profileType == 'downloadable' && $isDownloadableAllowed) {
            $wholeData = $this->processDownloadableData($wholeData, $data, $profileId);
        }
        /*Set Configurable Data*/
        $isConfigurableAllowed = $this->isProductTypeAllowed('configurable');
        if ($profileType == 'configurable' && $isConfigurableAllowed) {
            $wholeData = $this->processConfigurableData(
                $wholeData,
                $data,
                $mainRow,
                $childRowArr,
                $uploadedFileRowData,
                $profileId
            );
        }
        /*Set Custom Attributes Values*/
        if ($this->canSaveCustomAttribute()) {
            $wholeData = $this->validateCsvForRequiredCustomAttributes($csvAttributeList, $wholeData);
            if (!isset($wholeData['error'])) {
                $wholeData = $this->processCustomAttributeData($wholeData, $data, $mainRow);
            }
        }
        /*Set Custom Options Values*/
        if ($this->canSaveCustomOption()) {
            $wholeData = $this->processCustomOptionData($wholeData, $data);
        }
        /*Set product Status */
        if (!$this->getProductApprovalRequiredStatus()) {
            if (isset($data['product']['status'])) {
                $wholeData['status'] = $data['product']['status'];
            }
        }
        /*Set mapped category for Attribute Mapping */
        if ($this->isAttributeMappingEnabled()) {
            if (isset($data['product']['attribute_mapping_category'])) {
                $category = $data['product']['attribute_mapping_category'];
                if (strpos($category, ',') !== false) {
                    $wholeData['error'] = 1;
                    $wholeData['msg'] = __(
                        'Skipped row %1. Multiple categories are not allowed for attribute mapping',
                        $mainRow
                    );
                } else {
                    $categoryId = $this->getCategoryIds($category);
                    if (empty($categoryId) && empty($categoryId[0])) {
                        $wholeData['error'] = 1;
                        $wholeData['msg'] = __(
                            'Skipped row %1. The category name provided for mapping do not exist.',
                            $mainRow
                        );
                    } else {
                        $wholeData['product']['attribute_mapping_category'] = $categoryId[0];
                    }
                }
            } else {
                $wholeData['product']['attribute_mapping_category'] =  '';
            }
        }
        /*Set Cross-sell Up-sell and related product data */
        if (isset($data['product']['related_skus']) && !empty($data['product']['related_skus'])) {
            $wholeData = $this->setRelatedCrossUpSellProductData($wholeData, $data, 'related');
        }
        if (isset($data['product']['crosssell_skus']) && !empty($data['product']['crosssell_skus'])) {
            $wholeData = $this->setRelatedCrossUpSellProductData($wholeData, $data, 'crosssell');
        }
        if (isset($data['product']['upsell_skus']) && !empty($data['product']['upsell_skus'])) {
            $wholeData = $this->setRelatedCrossUpSellProductData($wholeData, $data, 'upsell');
        }
        $wholeData = $this->utf8Converter($wholeData);
        return $wholeData;
    }

    /**
     * Set related, cross-sell and up-sell
     *
     * @param array $wholeData
     * @param array $data
     * @param int $flag
     * @return array
     */
    public function setRelatedCrossUpSellProductData($wholeData, $data, $flag)
    {
        if ($flag == 'related') {
            $value = $data['product']['related_skus'];
        } elseif ($flag == 'crosssell') {
            $value = $data['product']['crosssell_skus'];
        } elseif ($flag == 'upsell') {
            $value = $data['product']['upsell_skus'];
        }
        $valueArray = explode(",", $value);
        $linkProductArray = [];
        foreach ($valueArray as $key => $value) {
            $productId = $this->_product->create()->getIdBySku($value);
            if ($productId) {
                $product = $this->_product->create()->load($productId);
                $productArray = $product->getData();
                $attributeSetCollection = $this->_attributeSetCollection->create()
                    ->addFieldToSelect('attribute_set_name')
                    ->addFieldToFilter('attribute_set_id', $productArray['attribute_set_id'])
                    ->getFirstItem()
                    ->toArray();
                if ($productArray['status'] == 1) {
                    $status = 'Enabled';
                } else {
                    $status = 'Disabled';
                }
                $formattedPrice = $this->_pricingHelper->currency($productArray['price'], true, false);
                $store = $this->_storeManager->getStore();
                $imageUrl = $store->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/product' . $product->getImage();
                $linkProductArray[] = [
                    'id' => $productArray['entity_id'],
                    'name' => $productArray['name'],
                    'status' => $status,
                    'attribute_set' => $attributeSetCollection['attribute_set_name'],
                    'sku' => $productArray['sku'],
                    'price' => $formattedPrice,
                    'thumbnail' => $imageUrl,
                    'record_id' => $productArray['entity_id']
                ];
                $wholeData['links'][$flag] = $linkProductArray;
            }
        }
        return $wholeData;
    }

    /**
     * Validate csv attribute list
     *
     * @param array $csvAttributeList
     * @param array $wholeData
     * @return array
     */
    public function validateCsvForRequiredCustomAttributes($csvAttributeList, $wholeData)
    {
        $result = ['error' => 0];
        $customAttributeList = $this->getCustomAttributeList();
        foreach ($customAttributeList as $code => $value) {
            $attributeId = $value;
            $attribute = $this->getAttributeDataById($attributeId);
            if ($attribute['is_required'] == 1) {
                if (!array_key_exists($attribute['attribute_code'], $csvAttributeList)) {
                    $wholeData['error'] = 1;
                    $wholeData['msg'] = __(
                        'Required "%1" Attribute column is missing in sheet',
                        $attribute['attribute_code']
                    );
                }
            }
        }
        return $wholeData;
    }

    /**
     * Is attribute mapping enabled
     *
     * @return boolean
     */
    public function isAttributeMappingEnabled()
    {
        if ($this->_moduleManager->isEnabled('Webkul_MpAttributeMapping')) {
            return true;
        }
        return false;
    }

    /**
     * Return product approval config
     *
     * @return bool
     */
    public function getProductApprovalRequiredStatus()
    {
        return $this->scopeConfig->getValue(
            'marketplace/product_settings/product_approval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return authorize sell status
     *
     * @param int $productId
     * @param string $sellerId
     * @return int
     */
    public function isRightSeller($productId, $sellerId = '')
    {
        if (!$sellerId) {
            $sellerId = $this->marketplaceHelper->getCustomerId();
        }
        $data = 0;
        $model = $this->_mpProduct->create()
            ->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $productId
            )->addFieldToFilter(
                'seller_id',
                $sellerId
            );
        foreach ($model as $value) {
            $data = 1;
        }

        return $data;
    }
    
    /**
     * Save Product
     *
     * @param int $sellerId
     * @param int $row
     * @param array $wholeData
     *
     * @return array
     */
    public function saveProduct($sellerId, $row, $wholeData)
    {
        $outputImportArr = [];
        $sheetRequest = false;
        $result = ['error' => 0, 'config_error' => 0, 'msg' => ''];
        try {
            /* Check if authorized seller */
            if (!empty($wholeData['is_multiple'])) {
                $isMultiple = true;
                $outputImportArr = $this->validateForMultiImport($wholeData, $row, $sellerId);
                $wholeData = $outputImportArr['wholeData'];
                $sellerId = isset($wholeData['product']['seller_id'])?$wholeData['product']['seller_id']:$sellerId;
            }
            if (!empty($wholeData['id']) && empty($wholeData['error'])) {
                $productId = $wholeData['id'];
                
                if (empty($sellerId)) {
                    $wholeData['msg'] = __(
                        'Skipped row %1. No seller has been assigned to this product.
                        Please update csv with seller id value',
                        $row
                    );
                    $wholeData['error'] = 1;
                }
                $rightseller = $this->isRightSeller($productId, $sellerId);
                if (!$rightseller) {
                    $wholeData['msg'] = __(
                        'Skipped row %1. Product is already assigned to other seller.',
                        $row
                    );
                    $wholeData['error'] = 1;
                }
            }
            if ($row == 1) {
                $this->_customerSession->setSuccesProductCount(0);
            }
            $uploadedPro = $wholeData['total_row_count'];
            $successProCount = (int) $this->_customerSession->getSuccesProductCount();
            
            /*Set Product Add Status According to seller Group*/
            if ($this->isSellerGroupEnable() && !$this->checkProductAllowedStatus($uploadedPro, $successProCount)) {
                $result['error'] = 1;
                
                if ($this->getAllowedProductQty()) {
                    // if ($row > $this->getAllowedProductQty()) {
                        $result['message'] =
                        __('You are not allowed to add more than %1 Product(s)', $this->getAllowedProductQty());
                    // }
                } else {
                    $result['message'] = __('YOUR GROUP PACK IS EXPIRED...');
                }
            } elseif ($this->isSellerMembershipEnable() &&
                ($this->getConfigFeeAppliedFor() == 0 && !$this->isMembershipFeePaid())
            ) {
                $erroFlag = 1;
                $data = $this->isMembershipFeePaid($erroFlag);
                if ($data['status']) {
                    $result['error'] = 1;
                    $result['message'] = __('Seller Membership : %1 ', $data['msg']);
                }
            } else {
                if (!empty($wholeData['error'])) {
                    $result['error'] = $wholeData['error'];
                    $result['msg'] = $wholeData['msg'];
                } else {
                    $prodQty = 0;
                    if ($wholeData['type'] == 'configurable') {
                        $prodQty = $wholeData['product']['quantity_and_stock_status']['qty'];
                        unset($wholeData['product']['quantity_and_stock_status']['qty']);
                    }
                    $result = $this->_saveProduct->saveProductData($sellerId, $wholeData);
                    $isInStock = 1;
                    if ($wholeData['type'] == 'configurable') {
                        $wholeData['product']['quantity_and_stock_status']['qty'] = $prodQty;
                    }
                    if (!(int)$wholeData['product']['quantity_and_stock_status']['qty']) {
                        $isInStock = 0;
                    }
                    $result['is_in_stock'] = $isInStock;
                    if (!empty($result['product_id'])) {
                        $productId = (int) $result['product_id'];
                        $successProCount = (int) $this->_customerSession->getSuccesProductCount();
                        $successProCount++;
                        $this->_customerSession->setSuccesProductCount($successProCount);
                    }
                }
            }
        } catch (\Exception $e) {
            $result['msg'] = __('Skipped row %1. %2', $row, $e->getMessage());
            $result['error'] = 1;
        }
        $result['total_row_count'] = $wholeData['total_row_count'];
        $result['row'] = $row;
        if ($wholeData['total_row_count'] != $row) {
            $nextRow = $row + 1;
            $result['next_row_data'] = $this->calculateProductRowData(
                $sellerId,
                $wholeData['profile_id'],
                $nextRow,
                $wholeData['type']
            );
            $result['next_row_data']['profile_id'] = $wholeData['profile_id'];
            $result['next_row_data']['row'] = $nextRow;
            $result['next_row_data']['total_row_count'] = $wholeData['total_row_count'];
            $result['next_row_data']['seller_id'] = $sellerId;
            if (!empty($wholeData['store_to_upload'])) {
                $result['next_row_data']['store_to_upload'] = $wholeData['store_to_upload'];
            }
            if (isset($isMultiple)) {
                $result['next_row_data']['is_multiple'] = $isMultiple;
            }
            
        }
        if ($result['error'] == 1) {
            if (!empty($result['message'])) {
                $result['msg'] = $result['message'];
            }
            return $result;
        } else {
            if (empty($result['product_id'])) {
                $result['product_id'] = 0;
            }
            $productId = (int) $result['product_id'];
        }
        if ($productId == 0) {
            $result['error'] = 1;
            $result['msg'] = __('Skipped row %1. error in importing product.', $row);
        }

        return $result;
    }

    /**
     * Get attribute option by Id
     *
     * @param string $attributeCode
     * @param string $optionText
     * @return int|string
     */
    public function getAttributeOptionIdbyOptionText($attributeCode, $optionText)
    {
        if ($optionText == "") {
            return $optionText;
        }
        $model = $this->_config;
        $attribute = $model->getAttribute('catalog_product', $attributeCode);
        if ($attribute) {
            $optionId = $attribute->getSource()->getOptionId(trim($optionText));
            return $optionId;
        } else {
            return "";
        }
    }

    /**
     * Get Sku Error Message
     *
     * @param string $sku
     * @param int $row
     *
     * @return string
     */
    public function getSkuErrorMessage($sku, $row)
    {
        $msg = "";
        if ($sku == '') {
            $msg = __('Skipped row %1. sku can not be empty.', $row);
        } else {
            $msg = __('Skipped row %1. sku %2 already exist.', $row, $sku);
        }
        return $msg;
    }

    /**
     * Get Category Ids From Name
     *
     * @param string $categories
     *
     * @return array
     */
    public function getCategoryIds($categories)
    {
        $categoryIds = [];
        $categoryList = $this->getCategotyList();
        if (strpos($categories, ',') !== false) {
            $categories = explode(',', $categories);
        } else {
            $categories = [$categories];
        }
        $categories = array_unique($categories);
        foreach ($categories as $category) {
            $parentId = 2;
            if (strpos($category, '>>') !== false) {
                $category = explode('>>', $category);
                foreach ($category as $ch) {
                    if ($ch != "Default Category") {
                        $parentId = $this->getChildId($ch, $parentId);
                    }
                }
                foreach ($categoryList as $key => $cat) {
                    if ($key == $parentId) {
                        $categoryIds[] = $key;
                    }
                }
            } else {
                $category = trim($category);
                if (in_array($category, $categoryList)) {
                    foreach ($categoryList as $key => $cat) {
                        if ($cat == $category) {
                            $categoryIds[] = $key;
                        }
                    }
                }
            }
        }
        return $categoryIds;
    }

    /**
     * Return parent cat id
     *
     * @param string $name
     * @return array
     */
    public function getParentCategoryId($name)
    {
        $categoryList = [];
        $collection = $this->_categoryCollection
            ->create()
            ->addAttributeToFilter('name', $name);
        foreach ($collection as $category) {
            $categoryList[$category->getEntityId()] = $category->getEntityId();
        }
        return $categoryList;
    }

    /**
     * Get Child Category Names By Parent Category Id
     *
     * @param int $parentId
     * @return array
     */
    public function getChildIdByParentId($parentId)
    {
        $categoryList = [];
        $collection = $this->_categoryCollection
            ->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('parent_id', $parentId);
        foreach ($collection as $category) {
            $categoryList[$category->getEntityId()] = $category->getName();
        }
        return $categoryList;
    }

    /**
     * Return child id
     *
     * @param string $childName
     * @param int $parentId
     * @return bool|int
     */
    public function getChildId($childName, $parentId = false)
    {
        if ($parentId) {
            $collection = $this->_categoryCollection
                ->create()
                ->addFieldToFilter('parent_id', $parentId)
                ->addFieldToFilter('name', $childName);
            foreach ($collection as $category) {
                return $category->getEntityId();
            }
        }
        return $parentId;
    }

    /**
     * Get Array Count
     *
     * @param array $array
     *
     * @return int
     */
    public function getCount($array)
    {
        return count($array);
    }

    /**
     * Check Downloadable Link File is Valid or Not
     *
     * @param string $file
     *
     * @return bool
     */
    public function isValidDownloadableFile($file)
    {
        $file = trim($file);
        $allowedExtension = $this->getAllowedExtensions();
        if (strpos($file, '.') !== false) {
            $file = explode('.', $file);
            $ext = strtolower(end($file));
            if (in_array($ext, $allowedExtension)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all allowed extensions
     *
     * @return array
     */
    protected function getAllowedExtensions()
    {
        $result = [];
        foreach (array_keys($this->fileHelper->getAllMineTypes()) as $option) {
            $result[] = substr($option, 1);
        }
        return $result;
    }

    /**
     * Get Current Profile Id
     *
     * @return int
     */
    public function getProfileId()
    {
        $id = (int) $this->_request->getParam('id');
        return $id;
    }

    /**
     * Get Seller Id
     *
     * @return int
     */
    public function getSellerId()
    {
        $id = (int) $this->_request->getParam('seller_id');
        return $id;
    }

    /**
     * Get All Categories
     *
     * @return array
     */
    public function getCategotyList()
    {
        $categoryList = [];
        $collection = $this->_categoryCollection
            ->create()
            ->addAttributeToSelect('name');
        foreach ($collection as $category) {
            $categoryList[$category->getEntityId()] = trim($category->getName());
        }
        return $categoryList;
    }

    /**
     * Get Csv Data in Format to Upload Configurable Product
     *
     * @param array $uploadedFileRowData
     * @param int $isParent
     *
     * @return array
     */
    public function getConfigurableFormatCsv($uploadedFileRowData, $isParent = 0)
    {
        $configData = [];
        $skipData = [];
        $parent = 0;
        $count = 0;
        $length = $this->getCount($uploadedFileRowData);
        for ($i = 1; $i < $length; ++$i) {
            if ($uploadedFileRowData[$i][1] == 'configurable') {
                $parent = $i;
                ++$count;
                if ($isParent == 1) {
                    $configData[$count] = $i;
                }
            }
            if ($parent > 0) {
                if ($uploadedFileRowData[$i][1] == 'simple') {
                    if ($isParent != 1) {
                        $configData[$count][] = $i;
                    }
                }
            }
        }
        return $configData;
    }

    /**
     * Return attribute sets
     *
     * @param integer $flag
     * @return array
     */
    public function getAttributeSets($flag = 0)
    {
        $result = [];
        $allowedAttributeSets = $this->marketplaceHelper->getAllowedAttributesetIds();
        $allowedAttributeSetIds = explode(',', $allowedAttributeSets);
        $entityTypeId = $this->_entity
            ->setType('catalog_product')
            ->getTypeId();
        $attributeSetCollection = $this->_attributeSetCollection
            ->create()
            ->addFieldToFilter(
                'attribute_set_id',
                ['in' => $allowedAttributeSetIds]
            )
            ->setEntityTypeFilter($entityTypeId);
        if ($flag == 1) {
            $result[0] = __("Select Attribute Set");
        }
        foreach ($attributeSetCollection as $set) {
            $result[$set->getAttributeSetId()] = $set->getAttributeSetName();
        }
        return $result;
    }

    /**
     * Check Attribute Code is VAlid or Not for Configurable Product
     *
     * @param string $attributeCode
     *
     * @return bool
     */
    public function isValidAttribute($attributeCode)
    {
        $collection = $this->_attributeCollection
            ->create()
            ->addFieldToFilter('attribute_code', $attributeCode)
            ->addFieldToFilter('frontend_input', 'select');
        foreach ($collection as $attribute) {
            if ($attribute->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get Attribute by Attribute Code
     *
     * @param string $attributeCode
     *
     * @return array
     */
    public function getAttributeByCode($attributeCode)
    {
        $attributeData = [];
        $collection = $this->_attributeCollection
            ->create()
            ->addFieldToFilter('attribute_code', $attributeCode)
            ->addFieldToFilter('frontend_input', 'select');
        foreach ($collection as $attribute) {
            $attributeData = $attribute->getData();
        }
        return $attributeData;
    }

    /**
     * Get Attribute Id by Attribute Code
     *
     * @param string $attributeCode
     *
     * @return int
     */
    public function getAttributeId($attributeCode)
    {
        $attributeId = 0;
        $collection = $this->_attributeCollection
            ->create()
            ->addFieldToFilter('attribute_code', $attributeCode)
            ->addFieldToFilter('frontend_input', 'select');
        foreach ($collection as $attribute) {
            $attributeId = $attribute->getId();
        }
        return $attributeId;
    }

    /**
     * Get Attribute Options
     *
     * @param string $attributeCode
     *
     * @return array
     */
    public function getAttributeOptions($attributeCode)
    {
        $result = [];
        $model = $this->_config;
        $attribute = $model->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions(false);
        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }

    /**
     * Get List of Seller Ids
     *
     * @return array
     */
    public function getSellerIdList()
    {
        $sellerIdList = [];
        $collection = $this->_sellerCollection
            ->create()
            ->addFieldToFilter('is_seller', 1);
        foreach ($collection as $item) {
            $sellerIdList[] = $item->getSellerId();
        }
        return $sellerIdList;
    }

    /**
     * Get List of Sellers
     *
     * @return array
     */
    public function getSellerList()
    {
        $sellerIdList = $this->getSellerIdList();
        $sellerList = ['' => __('Select Seller')];
        $collection = $this->_customerCollection
            ->create()
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addAttributeToSelect('email')
            ->addFieldToFilter('entity_id', ['in' => $sellerIdList]);
        foreach ($collection as $item) {
            $sellerList[$item->getId()] = $item->getFirstname() . ' ' .
             $item->getLastname() . ' (' . $item->getEmail() . ')';
        }
        return $sellerList;
    }

    /**
     * Get Sample Csv File Urls.
     *
     * @return array
     */
    public function getSampleCsv()
    {
        $result = [];
        $mediaDirectory = $this->_storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $url = $mediaDirectory . 'marketplace/massupload/samples/';
        $result[] = $url . 'simple.csv';
        $result[] = $url . 'downloadable.csv';
        $result[] = $url . 'config.csv';
        $result[] = $url . 'virtual.csv';
        return $result;
    }

    /**
     * Get Sample XML File Urls.
     *
     * @return array
     */
    public function getSampleXml()
    {
        $result = [];
        $mediaDirectory = $this->_storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $url = $mediaDirectory . 'marketplace/massupload/samples/';
        $result[] = $url . 'simple.xml';
        $result[] = $url . 'downloadable.xml';
        $result[] = $url . 'config.xml';
        $result[] = $url . 'virtual.xml';
        return $result;
    }

    /**
     * Get Sample XLS File Urls.
     *
     * @return array
     */
    public function getSampleXls()
    {
        $result = [];
        $mediaDirectory = $this->_storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $url = $mediaDirectory . 'marketplace/massupload/samples/';
        $result[] = $url . 'simple.xls';
        $result[] = $url . 'downloadable.xls';
        $result[] = $url . 'config.xls';
        $result[] = $url . 'virtual.xls';
        return $result;
    }

    /**
     * Get Seller Details
     *
     * @param int $sellerId
     *
     * @return \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    public function getSellerDetails($sellerId)
    {
        $seller = "";
        $collection = $this->_sellerCollection
            ->create()
            ->addFieldToFilter('seller_id', $sellerId);
        foreach ($collection as $seller) {
            return $seller;
        }
        return $seller;
    }

    /**
     * Check If Customer Is Seller Or Not
     *
     * @param int $sellerId [Optional]
     *
     * @return bool
     */
    public function isSeller($sellerId = '')
    {
        if ($sellerId == '') {
            $sellerId = $this->getCustomerId();
        }
        $seller = $this->getSellerDetails($sellerId);
        if (!is_object($seller)) {
            return false;
        }
        $isSeller = $seller->getIsSeller();
        if ($isSeller == 1) {
            return true;
        }
        return false;
    }

    /**
     * Rearrange Images of Product to upload
     *
     * @param string $path
     * @param string $originalPath
     * @param array  $result
     * @return void
     */
    public function arrangeFiles($path, $originalPath = '', $result = [])
    {
        if ($originalPath == '') {
            $originalPath = $path;
        }
        $entries = $this->_fileDriver->readDirectory($path);
        foreach ($entries as $file) {
            if ($this->_fileDriver->isDirectory($file)) {
                $result = $this->arrangeFiles($file, $originalPath, $result);
            } else {
                $tmp = explode("/", $file);
                $fileName = end($tmp);
                $sourcePath = $path . '/' . $fileName;
                $destinationPath = $originalPath . '/' . $fileName;
                if (!$this->_fileDriver->isExists($destinationPath)) {
                    $result[$sourcePath] = $destinationPath;
                    $this->_fileDriver->copy($sourcePath, $destinationPath);
                }
            }
        }
    }

    /**
     * Delte Extra Images and Folder
     *
     * @param string $path
     * @param bool $removeParent [optional]
     * @return void
     */
    public function flushFilesCache($path, $removeParent = false)
    {
        $entries = $this->_fileDriver->readDirectory($path);
        foreach ($entries as $entry) {
            if ($this->_fileDriver->isDirectory($entry)) {
                $this->removeDir($entry);
            }
        }
        if ($removeParent) {
            $this->removeDir($path);
        }
    }

    /**
     * Remove Folder and Its Content
     *
     * @param string $dir
     * @return void
     */
    public function removeDir($dir)
    {
        if ($this->_fileDriver->isDirectory($dir)) {
            $entries = $this->_fileDriver->readDirectory($dir);
            foreach ($entries as $entry) {
                if ($this->_fileDriver->isFile($entry)) {
                    $this->_fileDriver->deleteFile($entry);
                } else {
                    $this->removeDir($entry);
                }
            }
            $this->_fileDriver->deleteDirectory($dir);
        }
    }

    /**
     * Get Custom Attribute List
     *
     * @return array
     */
    public function getCustomAttributeList()
    {
        $attributeIds = [];
        if ($this->canSaveCustomAttribute()) {
            try {
                $collection = $this->_objectManager
                    ->create(\Webkul\Customattribute\Model\ResourceModel\Manageattribute\Collection::class)
                    ->addFieldToFilter("status", 1);
                foreach ($collection as $item) {
                    $attributeIds[] = $item->getAttributeId();
                }
            } catch (\Exception $e) {
                $attributeIds = [];
            }
        }
        return $attributeIds;
    }

    /**
     * Get MultiSelect Custom Attribute Codes
     *
     * @return array
     */
    public function getMultiSelectCustomAttribute()
    {
        $attributeCodes = [];
        $attributeIds = $this->getCustomAttributeList();
        foreach ($attributeIds as $attributeId) {
            $attribute = $this->getAttributeDataById($attributeId);
            if ($attribute["frontend_input"] == "multiselect") {
                $attributeCodes[$attribute['attribute_code']] = $attribute['attribute_code'];
            }
        }
        return $attributeCodes;
    }

    /**
     * Get All Allowed Custom Attribute Codes
     *
     * @return array
     */
    public function getAttributeCodes()
    {
        $attributeCodes = [];
        $attributeIds = $this->getCustomAttributeList();
        $collection = $this->_attributeCollection
            ->create()
            ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds]);
        foreach ($collection as $attribute) {
            if ($attribute->getId()) {
                $attributeCodes[] = $attribute->getAttributeCode();
            }
        }
        return $attributeCodes;
    }

    /**
     * Check Whether Can Save Custom Attribute or Not
     *
     * @return bool
     */
    public function canSaveCustomAttribute()
    {
        $validateCustomAttribute = $this->scopeConfig->getValue(
            'marketplace/massupload_customAttribute/validate_custom_attribute',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($this->_moduleManager->isEnabled('Webkul_Customattribute') && $validateCustomAttribute) {
            return true;
        }
        return false;
    }

    /**
     * Get Attribute Data by Code
     *
     * @param array $attributeCode
     *
     * @return array
     */
    public function getAttributeDataByCode($attributeCode)
    {
        $data = ['error' => 1];
        $collection = $this->_attributeCollection
            ->create()
            ->addFieldToFilter('attribute_code', $attributeCode);
        foreach ($collection as $attribute) {
            if ($attribute->getId()) {
                $data = $attribute->getData();
                $data['error'] = 0;
            }
        }
        return $data;
    }

    /**
     * Return attribute data by id
     *
     * @param int $attributeId
     * @return array
     */
    public function getAttributeDataById($attributeId)
    {
        $data = ['error' => 1];
        $collection = $this->_attributeCollection
            ->create()
            ->addFieldToFilter('main_table.attribute_id', $attributeId);
        foreach ($collection as $attribute) {
            if ($attribute->getId()) {
                $data = $attribute->getData();
                $data['error'] = 0;
            }
        }
        return $data;
    }

    /**
     * Get Array From String
     *
     * @param string $string
     * @param string $delimiter [optional]
     * @return array
     */
    public function getArrayFromString($string, $delimiter = ",")
    {
        if (strpos($string, $delimiter) !== false) {
            $data = explode($delimiter, $string);
        } else {
            $data = [$string];
        }
        return $data;
    }

    /**
     * Get All Customer Groups
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        $groups = [];
        $groupsCollection = $this->_customerGroup->getCollection();
        foreach ($groupsCollection as $group) {
            $groups[$group->getCustomerGroupId()] = $group->getCustomerGroupCode();
        }
        return $groups;
    }

    /**
     * Get All Websites
     *
     * @return array
     */
    public function getAllWebsites()
    {
        $websites = [];
        $allWebsites = $this->_storeManager->getWebsites();
        foreach ($allWebsites as $website) {
            $websites[$website->getWebsiteId()] = $website->getName();
        }
        return $websites;
    }

    /**
     * Check Whether Attribute is Allowed or Not
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function isAttributeAllowed($attribute)
    {
        if ($attribute['error']) {
            return false;
        }
        $allAttributeIds = $this->getCustomAttributeList();
        if (in_array($attribute['attribute_id'], $allAttributeIds)) {
            return true;
        }
        return false;
    }

    /**
     * Save Profile Data
     *
     * @param [type] $productType
     * @param [type] $fileName
     * @param [type] $fileData
     * @param [type] $extension
     * @return array
     */
    public function saveProfileData($productType, $fileName, $fileData, $extension)
    {
        
        $result = [];
        $time = time();
        if ($extension == 'xml') {
            $name = $time . ".xml";
        } else {
            $name = $time . ".csv";
        }
        $customerId = $this->getCustomerId();
        $attributeSet = $this->_request->getParam('attribute_set');
        $profileName = $time . "_" . $fileName;
        // Set uploaded file data in database
        $profile = $this->_profile->create();
        $profileData = [
            'customer_id' => $customerId,
            'profile_name' => $profileName,
            'product_type' => $productType,
            'attribute_set_id' => $attributeSet,
            'image_file' => 'images',
            'link_file' => 'links',
            'sample_file' => 'samples',
            'created_date' => $this->_date->gmtDate(),
            'data_row' => $this->_serializerInterface->serialize($fileData),
            'file_type' => $extension
        ];
        $profile->setData($profileData);
        $profile->save();
        $profileId = $profile->getId();
        $result = ['name' => $name, 'id' => $profileId];
        return $result;
    }

    /**
     * Undocumented function
     *
     * @param [type] $noValidate
     * @return array
     */
    public function validateUploadedFiles($noValidate)
    {
   
        $validateCsv = $this->validateCsv();
     
        if ($validateCsv['error']) {
            return $validateCsv;
        }
        $csvFile = $validateCsv['csv'];
        if (empty($noValidate)) {
            $validateZip = $this->validateZip();
            if ($validateZip['error']) {
                return $validateZip;
            }
        }
        // Start: Calculate Profile Mapped Attribute Data Array
        // for coverting uploaded file data attributes into magento attributes
        $atrrProfileId = $this->_request->getParam('attribute_profile_id');
       
        $attributeMappedData = $this->_attributeMappingRepository
            ->getByProfileId($atrrProfileId);
        $attributeMappedArr = [];
        foreach ($attributeMappedData as $key => $value) {
            if ($value['mage_attribute'] == 'image') {
                $attributeMappedArr[$value['file_attribute']] = 'images';
            } elseif ($value['mage_attribute'] == 'category_ids') {
                $attributeMappedArr[$value['file_attribute']] = 'category';
            } else {
                $attributeMappedArr[$value['file_attribute']] = $value['mage_attribute'];
            }
        }
       
        // End: Calculate Profile Mapped Attribute Data Array
        $csvFilePath = $validateCsv['path'];
        if ($validateCsv['extension'] == 'csv') {
            $uploadedFileRowData = $this->readCsv($csvFilePath, $attributeMappedArr);
        } elseif ($validateCsv['extension'] == 'xml') {
            $uploadedFileRowData = $this->_parser->load($csvFilePath)->xmlToArray();
            $dataKeyProductArray = [];
            $dataValueArray = [];
            $count = count($uploadedFileRowData);
            $flag = 1;
            foreach ($uploadedFileRowData['node']['product'] as $key => $value) {
                if (is_array($value) && is_numeric($key)) {
                    $flag = 0;
                    $dataValueProductArray = [];
                    foreach ($value as $productkey => $productValue) {
                        // Start: Coverting uploaded file data attributes into magento attributes
                        if (!empty($attributeMappedArr[$productkey])) {
                            $productkey = $attributeMappedArr[$productkey];
                        }
                        // End: Coverting uploaded file data attributes into magento attributes
                        $dataKeyProductArray[$productkey] = $productkey;
                        $dataValueProductArray[$productkey] = $productValue;
                    }
                    $dataValueArray[] = $dataValueProductArray;
                } else {
                    $dataKeyProductArray[$key] = $key;
                    $dataValueArray[] = $value;
                }
            }
            $i = 0;
            $dataKeyArray = [];
            foreach ($dataKeyProductArray as $key => $value) {
                $dataKeyArray[$i] = $value;
                if (!$flag) {
                    foreach ($dataValueArray as $productkey => $productvalue) {
                        if (empty($dataValueArray[$productkey][$value])) {
                            $dataValueArray[$productkey][$i] = '';
                            unset($dataValueArray[$productkey][$value]);
                        } else {
                            $dataValueArray[$productkey][$i] = $dataValueArray[$productkey][$value];
                            unset($dataValueArray[$productkey][$value]);
                        }
                    }
                }
                $i++;
            }
            $data[0] = $dataKeyArray;

            if (!$flag) {
                $i = 1;
                foreach ($dataValueArray as $key => $value) {
                    $data[$i] = $value;
                    $i++;
                }
            } else {
                $data[1] = $dataValueArray;
            }
            $uploadedFileRowData = $data;
            
        } else {
            $objPhpSpreadsheetReader = IOFactory::load($csvFilePath);

            $loadedSheetNames = $objPhpSpreadsheetReader->getSheetNames();

            $objWriter = IOFactory::createWriter($objPhpSpreadsheetReader, 'Csv');

            $csvXLSFilePath = $this->_filesystem->getDirectoryWrite(
                DirectoryList::MEDIA
            )->getAbsolutePath('/xlscoverted') . $csvFile . '.csv';
            foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $objWriter->setSheetIndex($sheetIndex);
                $this->saveObjectWriter($objWriter, $csvXLSFilePath);
            }
            $uploadedFileRowData = $this->readCsv($csvXLSFilePath, $attributeMappedArr);
        }
        $validateCsvData = $this->validateCsvData($uploadedFileRowData);
        
        if ($validateCsvData['error']) {
            return $validateCsvData;
        }
        $productType = $validateCsvData['type'];
        $isDownloadableAllowed = $this->isProductTypeAllowed('downloadable');
        if ($productType == 'downloadable' && $isDownloadableAllowed) {
            $validateLinkFiles = $this->validateLinkFiles();
            if ($validateLinkFiles['error']) {
                return $validateLinkFiles;
            }
            if ($this->_request->getParam('is_link_samples')) {
                $validateLinkSampleFiles = $this->validateLinkSampleFiles();
                if ($validateLinkSampleFiles['error']) {
                    return $validateLinkSampleFiles;
                }
            }
            if ($this->_request->getParam('is_samples')) {
                $validateSampleFiles = $this->validateSampleFiles();
                if ($validateSampleFiles['error']) {
                    return $validateSampleFiles;
                }
            }
        }
        $result = [
            'error' => false,
            'type' => $productType,
            'csv' => $csvFile,
            'csv_data' => $uploadedFileRowData,
            'extension' => $validateCsv['extension']
        ];
        return $result;
    }
    /**
     * Save object writer
     *
     * @param object $objWriter
     * @param object $csvXLSFilePath
     * @return void
     */
    public function saveObjectWriter($objWriter, $csvXLSFilePath)
    {
        $objWriter->save($csvXLSFilePath);
    }

    /**
     * Validate uploaded Csv File
     *
     * @return array
     */
    public function validateCsv()
    {
      
        try {
            $csvUploader = $this->_fileUploader->create(['fileId' => 'massupload_csv']);
            $csvUploader->setAllowedExtensions(['csv', 'xml', 'xls']);
            $validateData = $csvUploader->validateFile();
            $extension = $csvUploader->getFileExtension();
            $csvFilePath = $validateData['tmp_name'];
            $csvFile = $validateData['name'];
            $csvFile = $this->getValidName($csvFile);
            $result = [
                'error' => false,
                'path' => $csvFilePath,
                'csv' => $csvFile,
                'extension' => $extension
            ];
        } catch (\Exception $e) {
            
            $msg = 'There is some problem in uploading file.'.$e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Validate uploaded Images Zip File
     *
     * @return array
     */
    public function validateZip()
    {
        try {
            $imageUploader = $this->_fileUploader->create(['fileId' => 'massupload_image']);
            $imageUploader->setAllowedExtensions(['zip']);
            $validateData = $imageUploader->validateFile();
            $zipFilePath = $validateData['tmp_name'];
            $allowedImages = ['png', 'jpg', 'jpeg', 'gif'];
            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath) === true) {
                $index = 0;
                do {
                    $fileName = $zip->getNameIndex($index);
                    if (strpos($fileName, '.') !== false) {
                            $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
                        if (!in_array($ext, $allowedImages)) {
                            $msg = 'There are some files in zip which are not image.';
                            $result = ['error' => true, 'msg' => $msg];
                            return $result;
                        }
                    }
                    $index++;
                } while ($fileName !== false);
                $zip->close();
            }

            $result = ['error' => false];
        } catch (\Exception $e) {
            $msg = 'There is some problem in uploading image zip file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Validate uploaded Link Files
     *
     * @return array
     */
    public function validateLinkFiles()
    {
        try {
            $linkUploader = $this->_fileUploader->create(['fileId' => 'link_files']);
            $result = ['error' => false];
        } catch (\Exception $e) {
            $msg = 'There is some problem in uploading link files.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Validate uploaded Link Sample Files
     *
     * @return array
     */
    public function validateLinkSampleFiles()
    {
        try {
            $linkUploader = $this->_fileUploader->create(['fileId' => 'link_sample_files']);
            $result = ['error' => false];
        } catch (\Exception $e) {
            $msg = 'There is some problem in uploading link sample files.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Validate uploaded Sample Files
     *
     * @return array
     */
    public function validateSampleFiles()
    {
        try {
            $linkUploader = $this->_fileUploader->create(['fileId' => 'sample_files']);
            $result = ['error' => false];
        } catch (\Exception $e) {
            $msg = 'There is some problem in uploading sample files.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Csv File
     *
     * @param array $result
     * @param string $extension
     * @param string $csvFile
     *
     * @return array
     */
    public function uploadCsv($result, $extension, $csvFile)
    {
        $profileId = $result['id'];
        try {
            $csvUploadPath = $this->getBasePath($profileId);
            if ($extension == 'xls') {
                $data = $this->_file->createDirectory($csvUploadPath);
                $sourcePath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)
                    ->getAbsolutePath('/xlscoverted') . $csvFile . '.csv';
                $this->_file->copy($sourcePath, $csvUploadPath . '/' . $result['name']);
                $this->_file->deleteFile($sourcePath);
            } else {
                $csvUploader = $this->_fileUploader->create(['fileId' => 'massupload_csv']);
                $extension = $csvUploader->getFileExtension();
                $csvUploader->setAllowedExtensions(['csv', 'xml', 'xls']);
                $csvUploader->setAllowRenameFiles(true);
                $csvUploader->setFilesDispersion(false);
                $csvUploader->save($csvUploadPath, $result['name']);
            }
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading csv file.' . $e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Images Zip File
     *
     * @param array $result
     * @param array $fileData
     *
     * @return array
     */
    public function uploadZip($result, $fileData)
    {
        $profileId = $result['id'];
        try {
            $zipModel = $this->_zip;
            $basePath = $this->getBasePath($profileId);
            $imageUploadPath = $basePath . 'zip/';
            $imageUploader = $this->_fileUploader->create(['fileId' => 'massupload_image']);
            $validateData = $imageUploader->validateFile();
            $imageUploader->setAllowedExtensions(['zip']);
            $imageUploader->setAllowRenameFiles(true);
            $imageUploader->setFilesDispersion(false);
            $imageUploader->save($imageUploadPath);
            $fileName = $imageUploader->getUploadedFileName();
            $source = $imageUploadPath . $fileName;
            $filePath = $this->getMediaPath() . 'tmp/catalog/product/' . $profileId . '/';
            $destination =  $filePath . 'tempfiles/';
            $zipModel->unzipImages($source, $destination);
            $this->arrangeFiles($destination);
            $this->flushFilesCache($destination);
            $this->copyFilesToDestinationFolder($profileId, $fileData, $filePath, 'images');
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading image zip file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Link Files
     *
     * @param array $result
     * @param array $fileData
     *
     * @return array
     */
    public function uploadLinks($result, $fileData)
    {
        $profileId = $result['id'];
        try {
            $zipModel = $this->_zip;
            $basePath = $this->getBasePath($profileId);
            $linkUploadPath = $basePath . 'downloadable/';
            $linkUploader = $this->_fileUploader->create(['fileId' => 'link_files']);
            $linkUploader->setAllowRenameFiles(true);
            $linkUploader->setFilesDispersion(false);
            $linkUploader->save($linkUploadPath);
            $fileName = $linkUploader->getUploadedFileName();
            $source   =   $linkUploadPath . $fileName;
            $filePath =  $this->getMediaPath() . 'downloadable/tmp/links/' . $profileId . '/';
            $destination =  $filePath . 'tempfiles/';
            $zipModel->unzipLinks($source, $destination);
            $this->arrangeFiles($destination);
            $this->flushFilesCache($destination);
            $this->copyFilesToDestinationFolder(
                $profileId,
                $fileData,
                $filePath,
                'downloadable_link_file'
            );
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading link files.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Link Sample Files
     *
     * @param array $result
     * @param array $fileData
     *
     * @return array
     */
    public function uploadLinkSamples($result, $fileData)
    {
        $profileId = $result['id'];
        try {
            $zipModel = $this->_zip;
            $basePath = $this->getBasePath($profileId);
            $linkUploadPath = $basePath . 'downloadable/';
            $linkUploader = $this->_fileUploader->create(['fileId' => 'link_sample_files']);
            $linkUploader->setAllowRenameFiles(true);
            $linkUploader->setFilesDispersion(false);
            $linkUploader->save($linkUploadPath);
            $fileName = $linkUploader->getUploadedFileName();
            $source = $linkUploadPath . $fileName;
            $filePath =  $this->getMediaPath() . 'downloadable/tmp/link_samples/' . $profileId . '/';
            $destination =  $filePath . 'tempfiles/';
            $zipModel->unzipLinks($source, $destination);
            $this->arrangeFiles($destination);
            $this->flushFilesCache($destination);
            $this->copyFilesToDestinationFolder(
                $profileId,
                $fileData,
                $filePath,
                'downloadable_link_sample'
            );
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading link sample files.' . $e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Sample Files
     *
     * @param array $result
     * @param array $fileData
     *
     * @return array
     */
    public function uploadSamples($result, $fileData)
    {
        $profileId = $result['id'];
        try {
            $zipModel = $this->_zip;
            $basePath = $this->getBasePath($profileId);
            $linkUploadPath = $basePath . 'downloadable/';
            $linkUploader = $this->_fileUploader->create(['fileId' => 'sample_files']);
            $linkUploader->setAllowRenameFiles(true);
            $linkUploader->setFilesDispersion(false);
            $linkUploader->save($linkUploadPath);
            $fileName = $linkUploader->getUploadedFileName();
            $source   = $linkUploadPath . $fileName;
            $filePath =  $this->getMediaPath() . 'downloadable/tmp/samples/' . $profileId . '/';
            $destination =  $filePath . 'tempfiles/';
            $zipModel->unzipLinks($source, $destination);
            $this->arrangeFiles($destination);
            $this->flushFilesCache($destination);
            $this->copyFilesToDestinationFolder(
                $profileId,
                $fileData,
                $filePath,
                'downloadable_sample_file'
            );
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading sample files.' . $e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Sample Files
     *
     * @param int $profileId
     * @param array $fileData
     * @param string $filePath
     * @param string $fileType
     *
     * @return void
     */
    public function copyFilesToDestinationFolder($profileId, $fileData, $filePath, $fileType)
    {
        $totalRows = $this->getCount($fileData);
        $skuIndex = '';
        $fileIndex = '';
        foreach ($fileData[0] as $key => $value) {
            if ($value == 'sku') {
                $skuIndex = $key;
            }
            if ($value == $fileType) {
                $fileIndex = $key;
            }
        }
        $fileTempPath = $filePath . 'tempfiles/';
        for ($i = 1; $i < $totalRows; $i++) {
            if (!empty($fileData[$i][$skuIndex]) && !empty($fileData[$i][$fileIndex])) {
                $sku = $fileData[$i][$skuIndex];
                $destinationPath = $filePath . $sku;
                $isDestinationExist = 0;
                $files = explode(',', $fileData[$i][$fileIndex]);
                foreach ($files as $file) {
                    if (empty(trim($file))) {
                        continue;
                    }
                    $sourcefilePath = $fileTempPath . $file;
                    if ($this->_fileDriver->isExists($sourcefilePath)) {
                        if ($isDestinationExist == 0) {
                            $isDestinationExist = $this->createDirectoryAtDestination($destinationPath);
                        }
                        $this->_file->copy($sourcefilePath, $destinationPath . '/' . $file);
                    }
                    if ($this->_moduleManager->isEnabled('Webkul_S3amazon')) {
                        $fileUploadDestination = '';
                        $startPos = strpos($destinationPath, "tmp/catalog/product/");
                        $fileUploadDestination = substr($destinationPath, $startPos).'/'.$file;
                        $s3storageObj = $this->_objectManager->create(
                            \Webkul\S3amazon\Model\MediaStorage\File\Storage\S3storage::class
                        );
                        $s3storageObj->saveFile($fileUploadDestination);
                    }
                }
            }
        }
        $this->_file->deleteDirectory($fileTempPath);
    }

    /**
     * Create directory at destination
     *
     * @param string $destinationPath
     * @return int
     */
    public function createDirectoryAtDestination($destinationPath)
    {
        $isDestinationExist = 0;
        if (!$this->_fileDriver->isExists($destinationPath)) {
            $this->_file->createDirectory($destinationPath);
            $isDestinationExist = 1;
        }
        return $isDestinationExist;
    }

    /**
     * Flush Unwanted Data
     *
     * @param int $profileId
     * @return void
     */
    public function flushData($profileId)
    {
        $this->_profileRepository->get($profileId)->delete();
        $path = $this->getBasePath($profileId);
        $this->flushFilesCache($path, true);
    }

    /**
     * Get Super Attribute Codes
     *
     * @return array
     */
    public function getSuperAttributes()
    {
        $attributes = [];
        $collection = $this->_attributeCollection
            ->create()
            ->addFieldToFilter('frontend_input', 'select')
            ->addFieldToFilter("is_global", 1)
            ->addFieldToFilter("is_user_defined", 1);
        foreach ($collection as $item) {
            $code = $item->getAttributeCode();
            if ($code != "wk_marketplace_preorder") {
                $attributes[$code] = __($code);
            }
        }
        return $attributes;
    }

    /**
     * Get Attribute Info With Attribute Set Id
     *
     * @param boolean $group
     * @return array
     */
    public function getAttributeInfo($group = true)
    {
        $attributeSets = $this->getAttributeSets();
        $attributeSetIds = array_keys($attributeSets);
        $tableName = $this->_resource->getTableName('eav_entity_attribute');
        $attributeIds = $this->getCustomAttributeList();
        if ($group) {
            $collection = $this->_attributeCollection
                ->create()
                ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds]);

            $collection->join(
                ['entity_attribute' => $tableName],
                'entity_attribute.attribute_id = main_table.attribute_id',
                '*'
            );
            $collection->addFieldToFilter('entity_attribute.attribute_set_id', ['in' => $attributeSetIds]);

            $collection->getSelect()->reset('columns')
                ->columns('main_table.attribute_code')
                ->columns('entity_attribute.attribute_set_id')
                ->columns('entity_attribute.attribute_id')
                ->group('entity_attribute.attribute_id');
            return $collection;
        } else {
            $allCollections = [];
            foreach ($attributeSetIds as $attributeSetId) {
                $collection = $this->_attributeCollection
                    ->create()
                    ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds]);

                $collection->join(
                    ['entity_attribute' => $tableName],
                    'entity_attribute.attribute_id = main_table.attribute_id',
                    '*'
                );
                $collection->addFieldToFilter('entity_attribute.attribute_set_id', ['eq' => $attributeSetId]);

                $collection->getSelect()->reset('columns')
                    ->columns('main_table.attribute_code')
                    ->columns('entity_attribute.attribute_set_id')
                    ->columns('entity_attribute.attribute_id');

                $allCollections[] = $collection;
            }
            return $allCollections;
        }
    }

    /**
     * Get Attribute Set Info With Attribute Id
     *
     * @return array
     */
    public function getAttributeSetsInfo()
    {
        $result = [];
        $allCollections = $this->getAttributeInfo(false);
        foreach ($allCollections as $collection) {
            foreach ($collection as $attribute) {
                if ($attribute->getId()) {
                    $result[$attribute->getAttributeSetId()][] = $attribute->getAttributeId();
                }
            }
        }

        return $result;
    }

    /**
     * Get Attribute Codes With Attribute Id
     *
     * @return array
     */
    public function getAttributeDetails()
    {
        $result = [];
        $collection = $this->getAttributeInfo();
        foreach ($collection as $attribute) {
            if ($attribute->getId()) {
                $result[$attribute->getAttributeId()] = $attribute->getAttributeCode();
            }
        }
        return $result;
    }

    /**
     * Remove Special Characters From String
     *
     * @param string $string
     *
     * @return string
     */
    public function getValidName($string)
    {
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        return preg_replace('/-+/', '-', $string);
    }

    /**
     * Get Valid Date
     *
     * @param string $date
     *
     * @return string
     */
    public function getDate($date)
    {
        $year = date("Y", strtotime($date));
        if ($year <= 1970) {
            return "";
        }
        return date("Y-m-d", strtotime($date));
    }

    /**
     * Delete Profile
     *
     * @param int $profileId
     * @return void
     */
    public function deleteProfile($profileId)
    {
        $this->_profileRepository->get($profileId)->delete();
    }

    /**
     * Check Whether Can Save Custom Options or Not
     *
     * @return bool
     */
    public function canSaveCustomOption()
    {
        if ($this->_moduleManager->isEnabled('Webkul_Customoption')) {
            return true;
        }
        return false;
    }

    /**
     * Process Special Price Data
     *
     * @param array $wholeData
     * @param array $data
     * @param int $flag [optional]
     *
     * @return array
     */
    public function processSpecialPriceData($wholeData, $data, $flag = 0)
    {
        if ($flag == 1) {
            /*Configurable Case*/
            $price = (float) $data['product']['price'];
            $specialPrice = (float) $data['product']['special_price'];
            $specialFromDate = trim($data['product']['special_from_date']);
            $specialToDate = trim($data['product']['special_to_date']);
        } else {
            $price = (float) $data['product']['price'];
            $specialPrice = (float) $data['product']['special_price'];
            $specialFromDate = trim($data['product']['special_from_date']);
            $specialToDate = trim($data['product']['special_to_date']);
        }

        $specialFromDate = $this->getDate($specialFromDate);
        $specialToDate = $this->getDate($specialToDate);
        if ($specialFromDate != "" && $specialToDate != "") {
            $diff = strtotime($specialToDate) - strtotime($specialFromDate);
        } else {
            $diff = 1;
        }
        $specialFromDate = $this->_timezoneInterface
            ->formatDate(
                $specialFromDate,
                \IntlDateFormatter::SHORT,
                false
            );
        $specialToDate = $this->_timezoneInterface
            ->formatDate(
                $specialToDate,
                \IntlDateFormatter::SHORT,
                false
            );
        if ($diff > 0 && $specialPrice != "" && $specialPrice < $price) {
            $wholeData['product']['special_price'] = $specialPrice;
            $wholeData['product']['special_from_date'] = $specialFromDate;
            $wholeData['product']['special_to_date'] = $specialToDate;
        }

        return $wholeData;
    }

    /**
     * Process Tier Price Data
     *
     * @param object $tierPrice
     * @return array
     */
    public function processTierPrice($tierPrice)
    {
        try {
            return $this->_jsonHelper->jsonDecode($tierPrice);
        } catch (\Exception $e) {
            return $tierPrice;
        }
    }

    /**
     * Process Image Data
     *
     * @param array $wholeData
     * @param array $data
     * @param int $profileId
     *
     * @return array $wholeData
     */
    public function processImageData($wholeData, $data, $profileId)
    {
        if (!empty($data['product']['images'])) {
            $sku = $data['product']['sku'];
            $images = array_unique($this->getArrayFromString($data['product']['images']));
            $customOptionData = [];
            $i = 0;
            $j = 0;
            foreach ($images as $key => $value) {
                $imageName = '/' . $profileId . '/' . $sku . '/' . $value;
                $imagePath = $this->getMediaPath() . 'tmp/catalog/product' . $imageName;
                /** upload image with url */
                if (\strpos($value, 'http') !== false) {
                    $filePath        =  $this->getMediaPath() . 'tmp/catalog/product/' . $profileId . '/';
                    $destinationPath = $filePath . $sku;
                    $this->_file->createDirectory($destinationPath);
                    $fileInfo = $this->fileUpload->getPathInfo($value);
                    $basename = $fileInfo['basename'];
                    $imageName = '/' . $profileId . '/' . $sku . '/' . $basename;
                    $imagePath = $this->getMediaPath() . 'tmp/catalog/product' . $imageName;
                    if ($this->_fileDriver->isExists($imagePath)) {
                        $j++;
                        $imageName = '/' . $profileId . '/' . $sku . '/' . $basename . ' (' . $j . ')';
                        $imagePath = $this->getMediaPath() . 'tmp/catalog/product' . $imageName;
                    };
                    $tmpDirectory    = $this->getMediaDirTmpDir();
                    $this->fileUpload->checkAndCreateFolder($tmpDirectory);
                    $baseFileName = $tmpDirectory . '/catalog/product' . $imageName;
                    $result = $this->fileUpload->read(str_replace(" ", "%20", $value), $baseFileName);
                }
                if (!empty(trim($value)) && $this->_fileDriver->isExists($imagePath)) {
                    $i++;
                    $wholeData['product']['media_gallery']['images'][$key]['position'] = $i;
                    $wholeData['product']['media_gallery']['images'][$key]['media_type'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['video_provider'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['file'] = $imageName . '.tmp';
                    $wholeData['product']['media_gallery']['images'][$key]['value_id'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['label'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['disabled'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['removed'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['video_url'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['video_title'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['video_description'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['video_metadata'] = '';
                    $wholeData['product']['media_gallery']['images'][$key]['role'] = '';
                    if ($i == 1) {
                        $wholeData['product']['image'] = $imageName . '.tmp';
                        $wholeData['product']['small_image'] = $imageName . '.tmp';
                        $wholeData['product']['thumbnail'] = $imageName . '.tmp';
                    }
                }
            }
        }
        return $wholeData;
    }

    /**
     * Get Media Directory
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }

    /**
     * Process Configurable Data
     *
     * @param array $wholeData
     * @param array $data
     * @param array $row
     * @param array $childRowArr
     * @param array $uploadedFileRowData
     * @param int $profileId
     * @return array
     */
    public function processConfigurableData($wholeData, $data, $row, $childRowArr, $uploadedFileRowData, $profileId)
    {
        $attributeCodes = $data['product']['_super_attribute_code'];
        $error = 0;
        $attributeData = $this->processAttributeData($attributeCodes);
        $attributes = $attributeData['attributes'];
        $flag = $attributeData['flag'];
        if ($flag == 1) {
            $msg = __('Skipped row %1. Some of super attributes are not valid.', $row);
            $validate['msg'] = $msg;
            $validate['error'] = 1;
            if ($validate['error']) {
                $wholeData['error'] = $validate['error'];
                $wholeData['msg'] = $validate['msg'];
            }
        }
        foreach ($attributes as $attribute) {
            $attributeId = $attribute['attribute_id'];
            $wholeData['attributes'][] = $attributeId;
        }
        $attributeOptions = [];
        foreach ($childRowArr as $key => $childRow) {
            // Prepare Associated product row data
            $i = 0;
            $j = 0;
            $childRowData = $uploadedFileRowData[$childRow];
            $customData = [];
            foreach ($uploadedFileRowData[0] as $value) {
                $key = $i++;
                if (empty($childRowData[$key])) {
                    $customData['product'][$value] = '';
                } else {
                    $customData['product'][$value] = $childRowData[$key];
                }
                if ($value == 'description' && empty($customData['product'][$value])) {
                    $customData['product'][$value] = $wholeData['product']['description'];
                }
            }
            if (!empty($customData['product']['stock'])) {
                $customData['product']['stock'] = $customData['product']['stock'];
            } else {
                $customData['product']['stock'] = $data['product']['stock'];
            }
            $childRowData = $customData;
            $childRowData = $this->prepareAssociatedProductIfNotSet(
                $childRowData,
                $data
            );
            $superAttributeOptions = $this->getArrayFromString($childRowData['product']['_super_attribute_option']);
            $arributeCodeIndex = 0;
            foreach ($attributes as $attribute) {
                if (!empty($superAttributeOptions[$arributeCodeIndex])) {
                    $attributeId = $attribute['attribute_id'];
                    $attributeOptions[$attributeId][] = $superAttributeOptions[$arributeCodeIndex];
                    $arributeCodeIndex++;
                }
            }
            $wholeData['product']['configurable_attributes_data'] = [];
            $pos = 0;
            $allAttributeOptionsIdsArr = [];
            foreach ($attributes as $attribute) {
                $attributeId = $attribute['attribute_id'];
                $code = $attribute['attribute_code'];
                $wholeData['product']['configurable_attributes_data'][$attributeId]['attribute_id'] = $attributeId;
                $wholeData['product']['configurable_attributes_data'][$attributeId]['code'] = $code;
                $wholeData['product']['configurable_attributes_data'][$attributeId]['label'] =
                    $attribute['frontend_label'];
                $wholeData['product']['configurable_attributes_data'][$attributeId]['position'] = $pos;
                $wholeData['product']['configurable_attributes_data'][$attributeId]['values'] = [];
                if (empty($attributeOptions[$attributeId])) {
                    $attributeOptions[$attributeId] = [];
                }
                foreach ($attributeOptions[$attributeId] as $key => $option) {
                    $attributeOptionsId = '';
                    $attributeOptionsByCode = $this->getAttributeOptions($code);
                    if (!in_array($option, $attributeOptionsByCode)) {
                        $result = [
                            'msg' => __('Skipped row %1. Super attribute value is not valid.', $row),
                            'error' => 1
                        ];
                        $wholeData['error'] = $result['error'];
                        $wholeData['msg'] = $result['msg'];
                    } else {
                        $attributeOptionsId = array_search($option, $attributeOptionsByCode);
                        $allAttributeOptionsIdsArr[$option]['id'] = $attributeOptionsId;
                        $allAttributeOptionsIdsArr[$option]['code'] = $code;
                    }
                    $wholeData['product']['configurable_attributes_data'][$attributeId]
                    ['values'][$attributeOptionsId]['include'] = 1;
                    $wholeData['product']['configurable_attributes_data'][$attributeId]
                    ['values'][$attributeOptionsId]['value_index'] = $attributeOptionsId;
                }
                $pos++;
            }
            // prepare variation matrix
            $variationMatrixArr = [];
            $variationMatrixConfAttribute = [];
            foreach ($superAttributeOptions as $key => $value) {
                if (!empty($allAttributeOptionsIdsArr[$value])) {
                    $optionAttrCode = $allAttributeOptionsIdsArr[$value]['code'];
                    $optionId = $allAttributeOptionsIdsArr[$value]['id'];
                    array_push($variationMatrixArr, $optionId);
                    $variationMatrixConfAttribute[$optionAttrCode] = $optionId;
                }
            }
            $associatedProductIds = [];
            if (!empty($wholeData['product_id'])) {
                $associatedProductIds = $this->getAllAssociatedProductsIds(
                    $wholeData['product_id']
                );
            }

            $variationMatrixIndex = implode('-', $variationMatrixArr);
            $configurableAttribute = $this->_jsonHelper->jsonEncode($variationMatrixConfAttribute);
            $associatedProId = $this->_product->create()->getIdBySku(
                $childRowData['product']['sku']
            );
            $assoImageData = $this->processImageData($childRowData, $childRowData, $profileId);
            if ($associatedProId && in_array($associatedProId, $associatedProductIds)) {
                $variationMatrixIndex = $associatedProId;
                $wholeData['configurations'][$variationMatrixIndex]['image'] = '';
                $wholeData['associated_product_ids'][] = $associatedProId;
                if (!empty($assoImageData['product']['image'])) {
                    $wholeData['configurations'][$variationMatrixIndex]['image'] = $assoImageData['product']['image'];
                    $wholeData['configurations'][$variationMatrixIndex]['small_image'] =
                        $assoImageData['product']['small_image'];
                    $wholeData['configurations'][$variationMatrixIndex]['thumbnail'] =
                        $assoImageData['product']['thumbnail'];
                    $wholeData['configurations'][$variationMatrixIndex]['media_gallery'] =
                        $assoImageData['product']['media_gallery'];
                }

                $wholeData['configurations'][$variationMatrixIndex]['name'] = $childRowData['product']['name'];
                $wholeData['configurations'][$variationMatrixIndex]['configurable_attribute'] = $configurableAttribute;
                $wholeData['configurations'][$variationMatrixIndex]['status'] = 1;
                if (empty($childRowData['product']['sku'])) {
                    $childRowData['product']['sku'] =
                        $wholeData['product']['sku'] . '-' . implode('-', $superAttributeOptions);
                }
                $wholeData['configurations'][$variationMatrixIndex]['sku'] = $childRowData['product']['sku'];
                $wholeData['configurations'][$variationMatrixIndex]['price'] = $childRowData['product']['price'];
                $wholeData['configurations'][$variationMatrixIndex]['quantity_and_stock_status']['qty'] =
                    $childRowData['product']['stock'];
                $wholeData['configurations'][$variationMatrixIndex]['quantity_and_stock_status']['qty'] =
                    $childRowData['product']['stock'];
                $wholeData['configurations'][$variationMatrixIndex]['weight'] = $childRowData['product']['weight'];
                if (!empty($childRowData['product']['special_price'])) {
                    $wholeData['configurations'][$variationMatrixIndex]['special_price'] =
                        $childRowData['product']['special_price'];
                    $wholeData['configurations'][$variationMatrixIndex]['special_from_date'] =
                        $childRowData['product']['special_from_date'];
                    $wholeData['configurations'][$variationMatrixIndex]['special_to_date'] =
                        $childRowData['product']['special_to_date'];
                }

                /*Set Custom Attributes Values*/
                if ($this->canSaveCustomAttribute()) {
                    $wholeData['configurations'][$variationMatrixIndex] =
                        $this->processCustomAttributeDataForAssoProduct(
                            $wholeData['configurations'][$variationMatrixIndex],
                            $childRowData,
                            $row
                        );
                }
            } else {
                $wholeData['variations-matrix'][$variationMatrixIndex]['image'] = '';
                if (!empty($wholeData['product_id'])) {
                    $wholeData['associated_product_ids'][] = '';
                }
                if (!empty($assoImageData['product']['image'])) {
                    $wholeData['variations-matrix'][$variationMatrixIndex]['image'] =
                        $assoImageData['product']['image'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['small_image'] =
                        $assoImageData['product']['small_image'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['thumbnail'] =
                        $assoImageData['product']['thumbnail'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['media_gallery'] =
                        $assoImageData['product']['media_gallery'];
                }
                $wholeData['variations-matrix'][$variationMatrixIndex]['name'] = $childRowData['product']['name'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['configurable_attribute'] =
                    $configurableAttribute;
                $wholeData['variations-matrix'][$variationMatrixIndex]['status'] = 1;
                if (empty($childRowData['product']['sku'])) {
                    $childRowData['product']['sku'] =
                        $wholeData['product']['sku'] . '-' . implode('-', $superAttributeOptions);
                }
                $wholeData['variations-matrix'][$variationMatrixIndex]['sku'] = $childRowData['product']['sku'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['price'] = $childRowData['product']['price'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['quantity_and_stock_status']['qty'] =
                    $childRowData['product']['stock'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['weight'] = $childRowData['product']['weight'];
                if (!empty($childRowData['product']['special_price'])) {
                    $wholeData['variations-matrix'][$variationMatrixIndex]['special_price'] =
                        $childRowData['product']['special_price'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['special_from_date'] =
                        $childRowData['product']['special_from_date'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['special_to_date'] =
                        $childRowData['product']['special_to_date'];
                }

                /*Set Custom Attributes Values*/
                if ($this->canSaveCustomAttribute()) {
                    $wholeData['variations-matrix'][$variationMatrixIndex] =
                        $this->processCustomAttributeDataForAssoProduct(
                            $wholeData['variations-matrix'][$variationMatrixIndex],
                            $childRowData,
                            $row
                        );
                }
            }
        }
        $wholeData['affect_configurable_product_attributes'] = 1;
        return $wholeData;
    }

    /**
     * Process Custom Attribute Data For Associated Product
     *
     * @param array $wholeData
     * @param array $data
     * @param array $row
     * @return array $wholeData
     */
    public function processCustomAttributeDataForAssoProduct($wholeData, $data, $row)
    {
        foreach ($data['product'] as $code => $value) {
            $code = trim($code);
            $attribute = $this->getAttributeDataByCode($code);
            $notAllowedAttr = [
                'images',
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'attribute_set_id',
                'category_ids',
                'visibility',
                'tax_class_id',
                'product_has_weight',
                'weight'
            ];
            $wholeData = $this->checkAttributeDataForAsso($attribute, $code, $value, $notAllowedAttr, $wholeData, $row);
        }
        return $wholeData;
    }

   /**
    * Check attribute data for assoc
    *
    * @param string $attribute
    * @param string $code
    * @param int $value
    * @param int $notAllowedAttr
    * @param array $wholeData
    * @param array $row
    * @return array
    */
    public function checkAttributeDataForAsso($attribute, $code, $value, $notAllowedAttr, $wholeData, $row)
    {
        if ($this->isAttributeAllowed($attribute) && !in_array($code, $notAllowedAttr)) {
            if ($code == "tier_price") {
                $value = $this->processTierPrice($value);
                if (!empty($value)) {
                    foreach ($value as $key => $vl) {
                        if (empty($vl['website_id'])) {
                            $value[$key]['website_id'] = 0;
                        }
                    }
                    $wholeData[$code] = $value;
                }
            } else {
                $wholeData = $this->isRequiredAttributeEmpty($attribute, $value, $wholeData, $row);
                if (!isset($wholeData['error'])) {
                    if ($attribute["frontend_input"] == "multiselect") {
                        $valueArray = explode(",", $value);
                        $optionId = $this->getOptionIdByLabel($code, $valueArray);
                    } elseif ($attribute["frontend_input"] == "select") {
                        $value = explode(",", $value);
                        $optionId = $this->getOptionIdByLabelSelect($code, $value);
                    } else {
                        if ($attribute["frontend_input"] == "boolean" && (strcasecmp($value, 'yes') == 0)) {
                            $value = 1;
                        } elseif ($attribute["frontend_input"] == "boolean" && (strcasecmp($value, 'no') == 0)) {
                            $value = 0;
                        }
                        $optionId = $value;
                    }
                    $wholeData[$code] = $optionId;
                }
            }
        }
        return $wholeData;
    }

    /**
     * Get all associated product ids
     *
     * @param int $id
     *
     * @return array
     */
    public function getAllAssociatedProductsIds($id)
    {
        $childProductsIds = $this->_configurableProTypeModel->getChildrenIds($id);
        return $childProductsIds[0];
    }

    /**
     * Process Attribute Data
     *
     * @param array|string $attributeCodes
     *
     * @return array
     */
    public function processAttributeData($attributeCodes)
    {
        $result = ['flag' => 0];
        $attributes = [];
        if (strpos($attributeCodes, ',') !== false) {
            $attributeCodes = explode(',', $attributeCodes);
            foreach ($attributeCodes as $attributeCode) {
                $attributeCode = trim($attributeCode);
                if (!$this->isValidAttribute($attributeCode)) {
                    $result['flag'] = 1;
                    break;
                }
                $attributesResultData = $this->getAttributeByCode($attributeCode);
                if (!empty($attributesResultData)) {
                    $attributes[] = $attributesResultData;
                }
            }
        } else {
            $attributeCodes = trim($attributeCodes);
            if (!$this->isValidAttribute($attributeCodes)) {
                $result['flag'] = 1;
            }
            $attributesResultData = $this->getAttributeByCode($attributeCodes);
            if (!empty($attributesResultData)) {
                $attributes[] = $attributesResultData;
            }
        }
        $result['attributes'] = $attributes;
        return $result;
    }

    /**
     * Process Downloadable Data
     *
     * @param array $wholeData
     * @param array $data
     * @param int $profileId
     *
     * @return array $wholeData
     */
    public function processDownloadableData($wholeData, $data, $profileId)
    {
        if (!empty($data['product']['downloadable_link_file'])) {
            $wholeData = $this->prepareLinkData($wholeData, $data, $profileId);
            $wholeData = $this->prepareSampleData($wholeData, $data, $profileId);
            $wholeData['product']['links_title'] = $data['product']['links_title'];
            if ($data['product']['links_purchased_separately']) {
                $wholeData['product']['links_purchased_separately'] = 1;
            }
            $wholeData['product']['samples_title'] = $data['product']['samples_title'];
            $wholeData['is_downloadable'] = 1;
        }
        return $wholeData;
    }

    /**
     * Process Downloadable Link Data
     *
     * @param array $wholeData
     * @param array $data
     * @param int $profileId
     *
     * @return array $wholeData
     */
    public function prepareLinkData($wholeData, $data, $profileId)
    {
        $sku = $data['product']['sku'];
        $linkFiles = array_unique($this->getArrayFromString($data['product']['downloadable_link_file']));
        $linkFilePrice = $this->getArrayFromString($data['product']['downloadable_link_price']);
        $linkFileTitle = $this->getArrayFromString($data['product']['downloadable_link_title']);
        $linkFileType = $this->getArrayFromString($data['product']['downloadable_link_type']);
        $linkFileSample = $this->getArrayFromString($data['product']['downloadable_link_sample']);
        $linkFileIsSharableArray = $this->getArrayFromString(
            $data['product']['downloadable_link_is_sharable']
        );
        $linkFileIsSharable = [];
        foreach ($linkFileIsSharableArray as $value) {
            if (strcasecmp($value, 'yes') == 0) {
                $linkFileIsSharable[] = 1;
            } elseif (strcasecmp($value, 'no') == 0) {
                $linkFileIsSharable[] = 0;
            } elseif (strcasecmp($value, 'Use config') == 0) {
                $linkFileIsSharable[] = 2;
            }
        }
        $linkFileIsUnlimitedArray = $this->getArrayFromString(
            $data['product']['downloadable_link_is_unlimited']
        );
        $linkFileIsUnlimited = [];
        foreach ($linkFileIsUnlimitedArray as $value) {
            if (strcasecmp($value, 'yes') == 0) {
                $linkFileIsUnlimited[] = 1;
            } elseif (strcasecmp($value, 'no') == 0) {
                $linkFileIsUnlimited[] = 0;
            }
        }
        $linkNumberOfDownloads = $this->getArrayFromString(
            $data['product']['downloadable_link_number_of_downloads']
        );
        $linkFileSampleType = $this->getArrayFromString($data['product']['downloadable_link_sample_type']);
        // Prepare Downloadable Link Data
        $i = 0;
        foreach ($linkFiles as $key => $value) {
            if (empty($linkFilePrice[$key])) {
                $linkFilePrice[$key] = 0;
            }
            if (empty($linkFileTitle[$key])) {
                $linkFileTitle[$key] = '';
            }
            if (empty($linkFileType[$key])) {
                $linkFileType[$key] = '';
            }
            if (empty($linkFileSample[$key])) {
                $linkFileSample[$key] = '';
            }
            if (empty($linkFileIsSharable[$key])) {
                $linkFileIsSharable[$key] = '';
            }
            if (empty($linkFileIsUnlimited[$key])) {
                $linkFileIsUnlimited[$key] = '';
            }
            if (empty($linkNumberOfDownloads[$key])) {
                $linkNumberOfDownloads[$key] = '';
            }
            if (empty($linkFileSampleType[$key])) {
                $linkFileSampleType[$key] = '';
            }
            $linkFileInfo = '';
            $linkFileUrl = '';
            if ($linkFileType[$key] == 'file' && $this->isValidDownloadableFile($value)) {
                $linkName = '/' . $profileId . '/' . $sku . '/' . $value;
                $linkPath = $this->getMediaPath() . 'downloadable/tmp/links/' . $linkName;
                if ($this->_fileDriver->isExists($linkPath)) {
                    $i++;
                    $fileInfo = $this->fileUpload->getPathInfo($linkPath);
                    $linkFileInfo = [
                        'file' => $linkName,
                        'name' => $fileInfo['basename'],
                        'size' => $this->_fileDriver->stat($linkPath)['size'],
                        'status' => 'new'
                    ];
                    $linkFileInfo = $this->_jsonHelper->jsonEncode(
                        $linkFileInfo
                    );
                } else {
                    continue;
                }
            } else {
                $i++;
                $linkFileUrl = $value;
            }
            // For Link Sample Data
            $linkFileSampleUrl = '';
            $linkFileSampleFile = '';
            $linkFileSampleInfo = [];
            if ($linkFileSampleType[$key] == 'file' && $this->isValidDownloadableFile($linkFileSample[$key])) {
                $linkFileSampleName = '/' . $profileId . '/' . $sku . '/' . $linkFileSample[$key];
                $linkFileSamplePath = $this->getMediaPath() . 'downloadable/tmp/link_samples' . $linkFileSampleName;
                if ($this->_fileDriver->isExists($linkFileSamplePath)) {
                    $linkSampleInfo = $this->fileUpload->getPathInfo($linkFileSamplePath);
                    $linkFileSampleFile = [
                        'file' => $linkFileSampleName,
                        'name' => $linkSampleInfo['basename'],
                        'size' => $this->_fileDriver->stat($linkFileSamplePath)['size'],
                        'status' => 'new'
                    ];
                    $linkFileSampleFile = $this->_jsonHelper->jsonEncode(
                        $linkFileSampleFile
                    );
                }
            } else {
                $linkFileSampleUrl = $linkFileSample[$key];
            }
            if (!empty($linkFileType[$key]) && ($linkFileInfo || $linkFileUrl)) {
                if (trim($linkFileSampleUrl) || $linkFileSampleFile) {
                    $linkFileSampleInfo['type'] = $linkFileSampleType[$key];
                }
                $linkFileSampleInfo['url'] = $linkFileSampleUrl;
                $linkFileSampleInfo['file'] = "[" . $linkFileSampleFile . "]";
                $wholeData['downloadable']['link'][$key]['sort_order'] = $i;
                $wholeData['downloadable']['link'][$key]['is_delete'] = '';
                $wholeData['downloadable']['link'][$key]['link_id'] = 0;
                $wholeData['downloadable']['link'][$key]['title'] = $linkFileTitle[$key];
                $wholeData['downloadable']['link'][$key]['price'] = $linkFilePrice[$key];
                $wholeData['downloadable']['link'][$key]['type'] = $linkFileType[$key];
                $wholeData['downloadable']['link'][$key]['file'] = "[" . $linkFileInfo . "]";
                $wholeData['downloadable']['link'][$key]['link_url'] = $linkFileUrl;
                $wholeData['downloadable']['link'][$key]['sample'] = $linkFileSampleInfo;
                $wholeData['downloadable']['link'][$key]['is_shareable'] = $linkFileIsSharable[$key];
                if ($linkFileIsUnlimited[$key]) {
                    $wholeData['downloadable']['link'][$key]['is_unlimited'] = $linkFileIsUnlimited[$key];
                } else {
                    $wholeData['downloadable']['link'][$key]['number_of_downloads'] = $linkNumberOfDownloads[$key];
                }
            }
        }
        return $wholeData;
    }

    /**
     * Process Downloadable Sample Data
     *
     * @param array $wholeData
     * @param array $data
     * @param int $profileId
     *
     * @return array $wholeData
     */
    public function prepareSampleData($wholeData, $data, $profileId)
    {
        $sku = $data['product']['sku'];
        $sampleFiles = $this->getArrayFromString(
            $data['product']['downloadable_sample_file']
        );
        $sampleType = $this->getArrayFromString(
            $data['product']['downloadable_sample_type']
        );
        $sampleTitle = $this->getArrayFromString(
            $data['product']['downloadable_sample_title']
        );
        // Prepare Downloadable Sample Data
        $i = 0;
        foreach ($sampleFiles as $key => $value) {
            if (empty($sampleTitle[$key])) {
                $sampleTitle[$key] = '';
            }
            if (empty($sampleType[$key])) {
                $sampleType[$key] = '';
            }
            $sampleFileInfo = '';
            $sampleFileUrl = '';
            if ($sampleType[$key] == 'file' && $this->isValidDownloadableFile($value)) {
                $sampleName = '/' . $profileId . '/' . $sku . '/' . $value;
                $samplePath = $this->getMediaPath() . 'downloadable/tmp/samples/' . $sampleName;
                if ($this->_fileDriver->isExists($samplePath)) {
                    $i++;
                    $fileInfo = $this->fileUpload->getPathInfo($samplePath);
                    $sampleFileInfo = [
                        'file' => $sampleName,
                        'name' => $fileInfo['basename'],
                        'size' => $this->_fileDriver->stat($samplePath)['size'],
                        'status' => 'new'
                    ];
                    $sampleFileInfo = $this->_jsonHelper->jsonEncode(
                        $sampleFileInfo
                    );
                } else {
                    continue;
                }
            } else {
                $i++;
                $sampleFileUrl = $value;
            }
            if (!empty($sampleType[$key]) && ($sampleFileInfo || $sampleFileUrl)) {
                $wholeData['downloadable']['sample'][$key]['sort_order'] = $i;
                $wholeData['downloadable']['sample'][$key]['is_delete'] = '';
                $wholeData['downloadable']['sample'][$key]['sample_id'] = 0;
                $wholeData['downloadable']['sample'][$key]['title'] = $sampleTitle[$key];
                $wholeData['downloadable']['sample'][$key]['type'] = $sampleType[$key];
                $wholeData['downloadable']['sample'][$key]['file'] = "[" . $sampleFileInfo . "]";
                $wholeData['downloadable']['sample'][$key]['sample_url'] = $sampleFileUrl;
            }
        }
        return $wholeData;
    }

    /**
     * Process Custom attribute
     *
     * @param array $wholeData
     * @param array $data
     * @param array $mainRow
     * @return array
     */
    public function processCustomAttributeData($wholeData, $data, $mainRow)
    {
        foreach ($data['product'] as $code => $value) {
            $code = trim($code);
            $attribute = $this->getAttributeDataByCode($code);
            $notAllowedAttr = [
                'images',
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'attribute_set_id',
                'category_ids',
                'visibility',
                'tax_class_id',
                'product_has_weight',
                'weight'
            ];
            $wholeData = $this->checkAttributeData($attribute, $code, $value, $notAllowedAttr, $wholeData, $mainRow);
        }
        return $wholeData;
    }

   /**
    * Check attribute data
    *
    * @param string $attribute
    * @param string $code
    * @param int $value
    * @param string $notAllowedAttr
    * @param array $wholeData
    * @param array $row
    * @return array
    */
    public function checkAttributeData($attribute, $code, $value, $notAllowedAttr, $wholeData, $row)
    {
        if ($this->isAttributeAllowed($attribute) && !in_array($code, $notAllowedAttr)) {
            if ($code == "tier_price") {
                $value = $this->processTierPrice($value);
                if (!empty($value)) {
                    foreach ($value as $key => $vl) {
                        if (empty($vl['website_id'])) {
                            $value[$key]['website_id'] = 0;
                        }
                    }
                    $wholeData['product'][$code] = $value;
                }
            } else {
                $wholeData = $this->isRequiredAttributeEmpty($attribute, $value, $wholeData, $row);
                if (!isset($wholeData['error'])) {
                    if ((($attribute["frontend_input"] == "multiselect"))) {
                        $valueArray = explode(",", $value);
                        $optionId = $this->getOptionIdByLabel($code, $valueArray);
                    } elseif ($attribute["frontend_input"] == "select") {
                        $value = explode(",", $value);
                        $optionId = $this->getOptionIdByLabelSelect($code, $value);
                    } else {
                        if ($attribute["frontend_input"] == "boolean" && (strcasecmp($value, 'yes') == 0)) {
                            $value = 1;
                        } elseif ($attribute["frontend_input"] == "boolean" && (strcasecmp($value, 'no') == 0)) {
                            $value = 0;
                        }
                        $optionId = $value;
                    }
                    $wholeData['product'][$code] = $optionId;
                }
            }
        }
        return $wholeData;
    }

    /**
     * Check for required attribute
     *
     * @param string $attribute
     * @param string $value
     * @param string $wholeData
     * @param string $row
     * @return array
     */
    public function isRequiredAttributeEmpty($attribute, $value, $wholeData, $row)
    {
        if ($attribute['is_required'] == 1 && empty($value)) {
            $wholeData['error'] = 1;
            $wholeData['msg'] = __(
                "Skipped row %1. Product's Custom Attribute '%2' value can not be empty.",
                $row,
                $attribute['frontend_label']
            );
        }
        return $wholeData;
    }

    /**
     * Get Option id by Option Label
     *
     * @param string $attributeCode
     * @param array $optionLabelArray
     * @return array
     */
    public function getOptionIdByLabel($attributeCode, $optionLabelArray)
    {
        $optionIdArray = [];
        $index = 0;
        if (!is_array($optionLabelArray)) {
            $_product = $this->_product->create();
            $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
            $optionId = '';
            if ($isAttributeExist && $isAttributeExist->usesSource()) {
                $optionId = $isAttributeExist->getSource()->getOptionId($optionLabelArray);
            }
            return $optionId;
        }
        foreach ($optionLabelArray as $optionLabel) {
            $_product = $this->_product->create();
            $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
            $optionId = '';
            if ($isAttributeExist && $isAttributeExist->usesSource()) {
                $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
                $optionIdArray[$index] = $optionId;
                $index++;
            }
        }
        return $optionIdArray;
    }

    /**
     * Get option by label
     *
     * @param string $attributeCode
     * @param array $optionLabelArray
     * @return int|string
     */
    public function getOptionIdByLabelSelect($attributeCode, $optionLabelArray)
    {
        $optionIdArray = '';
        $index = 0;
        if (!is_array($optionLabelArray)) {
            $_product = $this->_product->create();
            $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
            $optionId = '';
            if ($isAttributeExist && $isAttributeExist->usesSource()) {
                $optionId = $isAttributeExist->getSource()->getOptionId($optionLabelArray);
            }
            return $optionId;
        }
        foreach ($optionLabelArray as $optionLabel) {
            $_product = $this->_product->create();
            $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
            $optionId = '';
            if ($isAttributeExist && $isAttributeExist->usesSource()) {
                $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
                $optionIdArray = $optionId;
                $index++;
            }
        }
        return $optionIdArray;
    }

    /**
     * Process Custom Options Data
     *
     * @param array $wholeData
     * @param array $data
     *
     * @return array $wholeData
     */
    public function processCustomOptionData($wholeData, $data)
    {
        try {
            if (!empty($data['product']['custom_option'])) {
                $customOptionData = $this->_jsonHelper->jsonDecode(
                    $data['product']['custom_option']
                );
                foreach ($customOptionData as $key => $value) {
                    $customOptionData[$key]['option_id'] = '';
                }
                $wholeData['affect_product_custom_options'] = 1;
                $wholeData['product']['options'] = $customOptionData;
            }
            return $wholeData;
        } catch (\Exception $e) {
            return $wholeData;
        }
    }

    /**
     * Check Whether Product Type is Allowed or Not
     *
     * @param string $type
     *
     * @return bool
     */
    public function isProductTypeAllowed($type)
    {
        $allowedProductTypes = explode(
            ',',
            $this->marketplaceHelper->getAllowedProductType()
        );
        if (in_array($type, $allowedProductTypes)) {
            return true;
        }
        return false;
    }

    /**
     * Check Whether Seller Group Addon is enabled or not
     *
     * @return bool
     */
    public function isSellerGroupEnable()
    {
        $isSellerGroupEnabled = $this->scopeConfig->getValue(
            'mpsellergroup/general_settings/status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($this->_moduleManager->isEnabled('Webkul_MpSellerGroup') && $isSellerGroupEnabled) {
            return true;
        }
        return false;
    }

    /**
     * Get Seller Group Data by seller Id
     *
     * @return \Webkul\MpSellerGroup\Api\SellerGroupTypeRepositoryInterface|array
     */
    public function getSellerGroupData()
    {
        $getSellerGroup = $this->_objectManager->create(
            \Webkul\MpSellerGroup\Api\SellerGroupTypeRepositoryInterface::class
        )->getBySellerId($this->getCustomerId());
        if (!empty($getSellerGroup->getData())) {
            return $getSellerGroup;
        } else {
            return [];
        }
    }

    /**
     * Undocumented function
     *
     * @return int|bool
     */
    public function getAllowedProductQty()
    {
        $sellerId = $this->getCustomerId();
        $sellerGroupHelper = $this->_objectManager->create(\Webkul\MpSellerGroup\Helper\Data::class);
        $getSellerTypeGroup = $this->getSellerGroupData();
        $groupExpireStatus = $sellerGroupHelper->getPermission();
        $sellerCount = $this->_objectManager->create(
            \Webkul\MpSellerGroup\Api\SellerGroupTypeRepositoryInterface::class
        )->getBySellerCount($sellerId);
        if (isset($groupExpireStatus['expire']) ? $groupExpireStatus['expire'] : false) {
            return false;
        }
        if (!empty($getSellerTypeGroup) && $getSellerTypeGroup->getNoOfProducts()) {
            return $getSellerTypeGroup->getNoOfProducts();
        } elseif (!$sellerCount) {
            $getDefaultGroupStatus = $sellerGroupHelper->getDefaultGroupStatus();
            if ($getDefaultGroupStatus) {
                return $sellerGroupHelper->getDefaultProductAllowed();
            }
        }
        return 0;
    }
    /**
     * Check Whether Product Quantity exceeds or not according to seller group
     *
     * @param [type] $rowCount
     * @param [type] $successProCount
     * @return bool
     */
    public function checkProductAllowedStatus($rowCount, $successProCount)
    {
        $sellerId = $this->getCustomerId();
        $sellerGroupHelper = $this->_objectManager->create(
            \Webkul\MpSellerGroup\Helper\Data::class
        );
        $groupExpireStatus = $sellerGroupHelper->getPermission();
       
        $getSellerTypeGroup = $this->getSellerGroupData();
       
        $sellerCount = $this->_objectManager->create(
            \Webkul\MpSellerGroup\Api\SellerGroupTypeRepositoryInterface::class
        )->getBySellerCount($sellerId);
        if (isset($groupExpireStatus['expire']) ? $groupExpireStatus['expire'] : false) {
            return false;
        } elseif (!empty($getSellerTypeGroup)) {
            
            if (($rowCount > $getSellerTypeGroup->getNoOfProducts()) &&
                ($successProCount == $getSellerTypeGroup->getNoOfProducts())
            ) {
                return false;
            } else {
                $sellerGroupTypeRepository = $this->_objectManager->create(
                    \Webkul\MpSellerGroup\Api\SellerGroupTypeRepositoryInterface::class
                );
                $getSellerGroup = $sellerGroupTypeRepository->getBySellerId($sellerId);
                if ($getSellerGroup) {
                  
                    $remainingProducts = $getSellerGroup->getRemainingProducts();
                    $allowedQty = $getSellerGroup->getNoOfProducts();
                    $products = $this->_mpProduct->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    if ($products->getSize() < $allowedQty) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } elseif (!$sellerCount) {
            $products = $this->_mpProduct->create()
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            $getDefaultGroupStatus = $sellerGroupHelper->getDefaultGroupStatus();
            if ($getDefaultGroupStatus) {
                $allowqty = $sellerGroupHelper->getDefaultProductAllowed();
                if (($rowCount > $allowqty) && ($successProCount == $allowqty)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check Whether Seller Membership Addon is enabled or not
     *
     * @return bool
     */
    public function isSellerMembershipEnable()
    {
        $isSellerMembershipEnabled = $this->scopeConfig->getValue(
            'marketplace/mpmembership_settings/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($this->_moduleManager->isEnabled('Webkul_Mpmembership') && $isSellerMembershipEnabled) {
            return true;
        }
        return false;
    }

    /**
     * GetConfigFeeAppliedFor used to get spitcart is enable or not
     *
     * @return int
     */
    public function getConfigFeeAppliedFor()
    {
        $sellerMembershipHelper = $this->_objectManager->create(
            \Webkul\Mpmembership\Helper\Data::class
        );
        return $membershipType = $sellerMembershipHelper->getConfigFeeAppliedFor();
    }

    /**
     * Return memmbbership status
     *
     * @param integer $erroFlag
     * @return boolean
     */
    public function isMembershipFeePaid($erroFlag = 0)
    {
        $sellerMembershipHelper = $this->_objectManager->create(
            \Webkul\Mpmembership\Helper\Data::class
        );
        $data = $sellerMembershipHelper->getPermission();
        $data = $sellerMembershipHelper->getValidTransaction($data);
        if ($data['status']) {
            if ($erroFlag) {
                return $data;
            }
            return false;
        }
        return true;
    }

    /**
     * UTF8 converter
     *
     * @param array $data
     * @return array
     */
    public function utf8Converter($data = [])
    {
        array_walk_recursive($data, function (&$item, $key) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });
        return $data;
    }

    /**
     * Return seller status
     *
     * @param int $sellerId
     * @return int
     */
    public function isSellerExistInMp($sellerId)
    {
        if (empty($sellerId)) {
            return 0;
        }
        $model = $this->marketplaceHelper->getSellerCollectionObj($sellerId);
        return $model->getSize();
    }

    /**
     * Validate multi import for csv
     *
     * @param array $wholeData
     * @param array $row
     * @param int $sellerId
     * @return array
     */
    public function validateForMultiImport($wholeData, $row, $sellerId)
    {
        $output = [];
        if (!empty($wholeData['product']['seller_id'])) {
            $sellerId = $wholeData['product']['seller_id'];
            if (empty($this->isSellerExistInMp($sellerId))) {
                $wholeData['msg'] = __(
                    'Skipped row %1. No seller exist with the provided seller id.',
                    $row
                );
                $wholeData['error'] = 1;
            }
        }
        return $output = ['wholeData' => $wholeData,
        'seller_id' => $sellerId];
    }
    /**
     * Return store list
     *
     * @return array
     */
    public function getAllStores()
    {
        $stores = $stores = $this->_storeRepository->getList();
        $storeList = [];
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            $storeName = $store["name"];
            $storeList[$storeId] = $storeName;
        }
        return $storeList;
    }

    /**
     * Check file is exist or not at specific location
     *
     * @param string $fileName
     * @return bool
     */
    public function checkFileExists($fileName)
    {
        if ($this->_fileDriver->isExists($fileName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Undocumented function
     *
     * @param object $requestData
     * @param array $fileData
     * @param string $extension
     * @return array
     */
    public function validateUploadedFile($requestData, $fileData, $extension)
    {
        $atrrProfileId = $requestData->getAttributeProfileId();
        $attributeMappedData = $this->_attributeMappingRepository
            ->getByProfileId($atrrProfileId);
        $attributeMappedArr = [];
        foreach ($attributeMappedData as $key => $value) {
            if ($value['mage_attribute'] == 'image') {
                $attributeMappedArr[$value['file_attribute']] = 'images';
            } elseif ($value['mage_attribute'] == 'category_ids') {
                $attributeMappedArr[$value['file_attribute']] = 'category';
            } else {
                $attributeMappedArr[$value['file_attribute']] = $value['mage_attribute'];
            }
        }
        // End: Calculate Profile Mapped Attribute Data Array
        $csvFilePath = $fileData['path'].'/'.$fileData['name'];
        $csvFile = $this->getValidName($fileData['name']);
        if ($extension == 'csv') {
            $uploadedFileRowData = $this->readCsv($csvFilePath, $attributeMappedArr);
        } elseif ($extension == 'xml') {
            $uploadedFileRowData = $this->_parser->load($csvFilePath)->xmlToArray();
            $dataKeyProductArray = [];
            $dataValueArray = [];
            $count = count($uploadedFileRowData);
            $flag = 1;
            foreach ($uploadedFileRowData['node']['product'] as $key => $value) {
                if (is_array($value) && is_numeric($key)) {
                    $flag = 0;
                    $dataValueProductArray = [];
                    foreach ($value as $productkey => $productValue) {
                        // Start: Coverting uploaded file data attributes into magento attributes
                        if (!empty($attributeMappedArr[$productkey])) {
                            $productkey = $attributeMappedArr[$productkey];
                        }
                        // End: Coverting uploaded file data attributes into magento attributes
                        $dataKeyProductArray[$productkey] = $productkey;
                        $dataValueProductArray[$productkey] = $productValue;
                    }
                    $dataValueArray[] = $dataValueProductArray;
                } else {
                    $dataKeyProductArray[$key] = $key;
                    $dataValueArray[] = $value;
                }
            }
            $i = 0;
            $dataKeyArray = [];
            foreach ($dataKeyProductArray as $key => $value) {
                $dataKeyArray[$i] = $value;
                if (!$flag) {
                    foreach ($dataValueArray as $productkey => $productvalue) {
                        if (empty($dataValueArray[$productkey][$value])) {
                            $dataValueArray[$productkey][$i] = '';
                            unset($dataValueArray[$productkey][$value]);
                        } else {
                            $dataValueArray[$productkey][$i] = $dataValueArray[$productkey][$value];
                            unset($dataValueArray[$productkey][$value]);
                        }
                    }
                }
                $i++;
            }
            $data[0] = $dataKeyArray;
            if (!$flag) {
                $i = 1;
                foreach ($dataValueArray as $key => $value) {
                    $data[$i] = $value;
                    $i++;
                }
            } else {
                $data[1] = $dataValueArray;
            }
            $uploadedFileRowData = $data;
        } else {
            $objPhpSpreadsheetReader = IOFactory::load($csvFilePath);

            $loadedSheetNames = $objPhpSpreadsheetReader->getSheetNames();

            $objWriter = IOFactory::createWriter($objPhpSpreadsheetReader, 'Csv');

            $csvXLSFilePath = $this->_filesystem->getDirectoryWrite(
                DirectoryList::MEDIA
            )->getAbsolutePath('/xlscoverted').$csvFile.'.csv';
            foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $objWriter->setSheetIndex($sheetIndex);
                $this->saveObjectWriter($objWriter, $csvXLSFilePath);
            }
            $uploadedFileRowData = $this->readCsv($csvXLSFilePath, $attributeMappedArr);
        }
        $validateCsvData = $this->validateCsvData($uploadedFileRowData);
        if ($validateCsvData['error']) {
            return $validateCsvData;
        }
        $productType = $validateCsvData['type'];
        $isDownloadableAllowed = $this->isProductTypeAllowed('downloadable');
        if ($productType == 'downloadable' && $isDownloadableAllowed) {
            $validateLinkFiles = $this->validateLinkFiles();
            if ($validateLinkFiles['error']) {
                return $validateLinkFiles;
            }
            if ($requestData->getIsLinkSamples()) {
                $validateLinkSampleFiles = $this->validateLinkSampleFiles();
                if ($validateLinkSampleFiles['error']) {
                    return $validateLinkSampleFiles;
                }
            }
            if ($requestData->getIsSamples()) {
                $validateSampleFiles = $this->validateSampleFiles();
                if ($validateSampleFiles['error']) {
                    return $validateSampleFiles;
                }
            }
        }
        $result = [
            'error' => false,
            'type' => $productType,
            'csv' => $csvFile,
            'csv_data' => $uploadedFileRowData,
            'extension' => $extension
        ];
        return $result;
    }

    /**
     * Upload Csv File
     *
     * @param array $result
     * @param string $extension
     * @param string $csvFile
     * @param string $filepath
     * @return array
     */
    public function uploadFile($result, $extension, $csvFile, $filepath)
    {
        $profileId = $result['id'];
        try {
            $csvUploadPath = $this->getBasePath($profileId);
            if ($extension == 'xls') {
                $data = $this->_file->createDirectory($csvUploadPath);
                $sourcePath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)
                            ->getAbsolutePath('/xlscoverted').$csvFile.'.csv';
                $this->_file->copy($sourcePath, $csvUploadPath.'/'.$result['name']);
                $this->_file->deleteFile($sourcePath);
            } else {
                $data = $this->_file->createDirectory($csvUploadPath);
                $sourcePath = $filepath;
                $this->_file->copy($sourcePath, $csvUploadPath.'/'.$result['name']);
                $this->_file->deleteFile($sourcePath);

            }
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading csv file.'.$e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Images Zip File
     *
     * @param array $result
     * @param array $fileData
     * @param string $filepath
     * @param string $fileName
     * @return array
     */
    public function uploadedZip($result, $fileData, $filepath, $fileName)
    {
        $profileId = $result['id'];
        try {

            /** Move Zip folder from Tmp folder */
            $basePath = $this->getBasePath($profileId);
            $data = $this->_file->createDirectory($basePath);
            $sourcePath = $filepath.'/'.$fileName;
            $this->_file->copy($sourcePath, $basePath.'/'.$fileName);
            $this->_file->deleteFile($sourcePath);
            /** Move Zip folder from Tmp folder */
            
            $zipModel = $this->_zip;
            $source = $basePath.$fileName;
            $filePath = $this->getMediaPath().'tmp/catalog/product/'.$profileId.'/';
            $destination =  $filePath.'tempfiles/';
            $zipModel->unzipImages($source, $destination);
            $this->arrangeFiles($destination);
            $this->flushFilesCache($destination);
            $this->copyFilesToDestinationFolder($profileId, $fileData, $filePath, 'images');
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading image zip file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Save Profile Data
     *
     * @param string $productType
     * @param string $fileName
     * @param array $fileData
     * @param string $extension
     * @param object $profileData
     * @return array
     */
    public function saveProfilesData($productType, $fileName, $fileData, $extension, $profileData)
    {
        $result = [];
        $time = time();
        if ($extension == 'xml') {
            $name = $time.".xml";
        } else {
            $name = $time.".csv";
        }
        $customerId = $this->getCustomerLoggedId();
        $attributeSet = $profileData->getAttributeSet();
        $profileName = $time."_".$fileName;
        // Set uploaded file data in database
        $profile = $this->_profile->create();
        $profileData = [
            'customer_id' => $customerId,
            'profile_name' => $profileName,
            'product_type' => $productType,
            'attribute_set_id' => $attributeSet,
            'image_file' => 'images',
            'link_file' => 'links',
            'sample_file' => 'samples',
            'data_row' => $this->_serializerInterface->serialize($fileData),
            'file_type' => $extension
        ];
        $profile->setData($profileData);
        $profile->save();
        $profileId = $profile->getId();
        $result = ['name' => $name, 'id' => $profileId];
        return $result;
    }

    /**
     * GetCustomerId function
     *
     * @return void
     */
    public function getCustomerLoggedId()
    {
        return $this->userContext->getUserId();
    }
    
    /**
     * Return the Customer seller status.
     *
     * @param int $id
     * @return bool|0|1
     */
    public function isSellerStatus($id)
    {
        $sellerId = '';
        $sellerStatus = 0;
        $model = $this->sellerModel->create()->getCollection()
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
     * Decodes the given $encodedValue string which is encoded in the JSON format
     *
     * @param string $encodedValue
     * @return mixed
     */
    public function getJsonDecode($encodedValue)
    {
        return $this->_jsonHelper->jsonDecode($encodedValue);
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @return string
     */
    public function getJsonEncode($valueToEncode)
    {
        return $this->_jsonHelper->jsonEncode($valueToEncode);
    }
}
