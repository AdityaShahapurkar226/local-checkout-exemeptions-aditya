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
namespace Webkul\MpMassUpload\Block;

use Magento\Framework\UrlInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_entity;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_massUploadHelper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Webkul\MpMassUpload\Helper\Export
     */
    protected $_exportHelper;

    /**
     * @var CollectionFactory
     */
    protected $_setCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Webkul\MpMassUpload\Helper\Export $exportHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param CollectionFactory $setCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Eav\Model\Entity $entity,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Webkul\MpMassUpload\Helper\Export $exportHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        CollectionFactory $setCollection,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_entity = $entity;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_massUploadHelper = $massUploadHelper;
        $this->_exportHelper = $exportHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_setCollection = $setCollection;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Mass Upload'));
        }
        return parent::_prepareLayout();
    }

    /**
     * Get Attribute Set Collection.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    public function getAttributeSetCollection()
    {
        $allowedAttributeSets = $this->marketplaceHelper->getAllowedAttributesetIds();
        $allowedAttributeSetIds = explode(',', $allowedAttributeSets);
        $entityTypeId = $this->_entity
                            ->setType('catalog_product')
                            ->getTypeId();
        $attributeSetCollection = $this->_setCollection
                ->create()
                ->addFieldToFilter(
                    'attribute_set_id',
                    ['in' => $allowedAttributeSetIds]
                )
                ->setEntityTypeFilter($entityTypeId);
        return $attributeSetCollection;
    }

    /**
     * Check Whether Product Type is Allowed or Not
     *
     * @param string $type
     * @return bool
     */
    public function isProductTypeAllowed($type)
    {
        return $this->_massUploadHelper->isProductTypeAllowed($type);
    }

    /**
     * Get Attribute Profiles
     *
     * @param boolean $flag
     * @return array
     */
    public function getAttributeProfiles($flag = 0)
    {
        return $this->_massUploadHelper->getAttributeProfiles($flag);
    }

    /**
     * Get Profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->_massUploadHelper->getProfiles();
    }

    /**
     * Get Sample Csv File Urls.
     *
     * @return array
     */
    public function getSampleCsv()
    {
        return $this->_massUploadHelper->getSampleCsv();
    }

    /**
     * Get Sample XML File Urls.
     *
     * @return array
     */
    public function getSampleXml()
    {
        return $this->_massUploadHelper->getSampleXml();
    }

    /**
     * Get Sample XLS File Urls.
     *
     * @return array
     */
    public function getSampleXls()
    {
        return $this->_massUploadHelper->getSampleXls();
    }

    /**
     * Get All Allowed Custom Attribute Codes
     *
     * @return array
     */
    public function getAttributeCodes()
    {
        return $this->_massUploadHelper->getAttributeCodes();
    }

    /**
     * Get Attribute Set Info With Attribute Id
     *
     * @return array
     */
    public function getAttributeSetsInfo()
    {
        return $this->_massUploadHelper->getAttributeSetsInfo();
    }

    /**
     * Get Attribute Codes With Attribute Id
     *
     * @return array
     */
    public function getAttributeDetails()
    {
        return $this->_massUploadHelper->getAttributeDetails();
    }

    /**
     * Encodes data in json format
     *
     * @param array $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->_jsonHelper->jsonEncode($data);
    }

    /**
     * Get All Customer Groups
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        return $this->_massUploadHelper->getCustomerGroups();
    }

    /**
     * Get All Websites
     *
     * @return array
     */
    public function getAllWebsites()
    {
        return $this->_massUploadHelper->getAllWebsites();
    }

    /**
     * Get Super Attribute Codes
     *
     * @return array
     */
    public function getSuperAttributes()
    {
        return $this->_massUploadHelper->getSuperAttributes();
    }

    /**
     * Get MultiSelect Custom Attribute Codes
     *
     * @return array
     */
    public function getMultiSelectCustomAttribute()
    {
        return $this->_massUploadHelper->getMultiSelectCustomAttribute();
    }

    /**
     * Check Whether Can Save Custom Attribute or Not
     *
     * @return bool
     */
    public function canSaveCustomAttribute()
    {
        return $this->_massUploadHelper->canSaveCustomAttribute();
    }

    /**
     * Return the Customer seller status.
     *
     * @return bool|0|1
     */
    public function isSeller()
    {
        return $this->marketplaceHelper->isSeller();
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
        return $this->_massUploadHelper->getTotalCount($profileId);
    }

    /**
     * Get Current Profile Id
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->_massUploadHelper->getProfileId();
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
        return $this->_massUploadHelper->getProductPostData($profileId, $row);
    }

    /**
     * Prepare File Column Row
     *
     * @param string $productType
     * @param array $allowedAttributes
     * @return array
     */
    public function prepareFileColumnRow($productType, $allowedAttributes)
    {
        return $this->_exportHelper->prepareFileColumnRow($productType, $allowedAttributes);
    }
}
