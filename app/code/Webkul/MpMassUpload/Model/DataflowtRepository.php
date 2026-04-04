<?php
/**
 * Webkul Software.
 *
 * @category    Webkul
 * @package     Webkul_MpMassUpload
 * @author      Webkul
 * @copyright   Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */


namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Controller\ResultFactory;
use Webkul\MpMassUpload\Api\AttributeProfileRepositoryInterface;
use Webkul\MpMassUpload\Model\AttributeProfile;
use Webkul\MpMassUpload\Api\AttributeMappingRepositoryInterface;
use Webkul\MpMassUpload\Model\AttributeMappingFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttributeGroup;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Framework\Filesystem\Io\File;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory as SearchResultFactory;
use Webkul\MpMassUpload\Api\DataflowRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Webkul\MpMassUpload\Api\Data\ResponseInterface;

/**
 * DataflowtRepository Class DataFlow
 */
class DataflowtRepository implements DataflowRepositoryInterface
{

    /**
     * @param \Webkul\MpMassUpload\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param ResultFactory $resultFactory
     * @param SearchCriteriaInterface $searchCriteria
     * @param AttributeSetRepositoryInterface $attributeSet
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeProfileRepositoryInterface $attributeProfileRepository
     * @param AttributeProfile $attributeProfile
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param AttributeMappingRepositoryInterface $attributeMappingRepository
     * @param AttributeMappingFactory $attributeMapping
     * @param AttributeGroup $attributeGroup
     * @param ProductAttribute $productAttributeCollection
     * @param EavAttribute $eavAttribute
     * @param ResponseInterface $responseInterface
     * @param File $file
     * @param \Webkul\Marketplace\Model\Product $mpProduct
     * @param \Webkul\MpMassUpload\Helper\Export $helperExport
     * @param AdapterCsv $writer
     * @param FileFactory $fileFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultFactory $searchResultsFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Webkul\MpMassUpload\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mphelper,
        ResultFactory $resultFactory,
        SearchCriteriaInterface $searchCriteria,
        AttributeSetRepositoryInterface $attributeSet,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeProfileRepositoryInterface $attributeProfileRepository,
        AttributeProfile $attributeProfile,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        AttributeMappingRepositoryInterface $attributeMappingRepository,
        AttributeMappingFactory $attributeMapping,
        AttributeGroup $attributeGroup,
        ProductAttribute $productAttributeCollection,
        EavAttribute $eavAttribute,
        ResponseInterface $responseInterface,
        File $file,
        \Webkul\Marketplace\Model\Product $mpProduct,
        \Webkul\MpMassUpload\Helper\Export $helperExport,
        AdapterCsv $writer,
        FileFactory $fileFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultFactory $searchResultsFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->helper                       = $helper;
        $this->mphelper                     = $mphelper;
        $this->resultFactory                = $resultFactory;
        $this->searchCriteria               = $searchCriteria;
        $this->attributeSet                 = $attributeSet;
        $this->searchCriteriaBuilder        = $searchCriteriaBuilder;
        $this->_attributeProfile            = $attributeProfile;
        $this->_attributeProfileRepository  = $attributeProfileRepository;
        $this->_date                        = $date;
        $this->_attributeMappingRepository  = $attributeMappingRepository;
        $this->_attributeMapping            = $attributeMapping;
        $this->_attributeGroup              = $attributeGroup;
        $this->_productAttributeCollection  = $productAttributeCollection;
        $this->_eavAttribute                = $eavAttribute;
        $this->responseInterface            = $responseInterface;
        $this->file                         = $file;
        $this->_mpProduct                   = $mpProduct;
        $this->_helperExport                = $helperExport;
        $this->_writer                      = $writer;
        $this->fileFactory                  = $fileFactory;
        $this->collectionProcessor          = $collectionProcessor;
        $this->searchResultsFactory         = $searchResultsFactory;
        $this->resource                     = $resource;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeSetList()
    {
        $customerId = $this->helper->getCustomerLoggedId();
        if (!$this->helper->isSellerStatus($customerId)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid seller')
            );
        }
        $searchCriteria = $this->searchCriteriaBuilder
        ->addFilter(
            'attribute_set_id',
            explode(',', $this->mphelper->getAllowedAttributesetIds()),
            'in'
        )->create();
        $attributeList = $this->attributeSet->getlist($searchCriteria);
        $attibuteSet = $this->setAttributeSetList($attributeList);
        return $attibuteSet;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeSetList($attribute)
    {
        return $attribute;
    }

    /**
     * Save Attribute Set Into Profile
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData
     * @return void
     */
    public function saveAttributeSetProfile(\Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData)
    {
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            $postData = $attributeSetData;
            $sellerId = $this->helper->getCustomerLoggedId();
            if (empty($postData->getProfileName()) || empty($postData->getAttributeSet())) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please fill the Required Fields')
                );
            }
            if (preg_match('/<script>/', $postData->getProfileName())) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please Enter valid values')
                );
            }
            
            if (!empty($postData->getEditId())) {
                $id = $postData->getEditId();
                // Check If profile does not exists
                $attributeProfile = $this->_attributeProfileRepository->get($id);
                if ($attributeProfile->getId()) {
                    if ($attributeProfile->getSellerId() == $sellerId) {
                        $value = $this->_attributeProfile->load($id);
                        $value->setProfileName($postData->getProfileName());
                        $value->setAttributeSetId($postData->getAttributeSet());
                        $value->save();
                        $this->saveProfileAttributeMapData($postData, $id);
                        $attributeSetData->setId($id);
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('You are not authorized to update this profile.')
                        );
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Dataflow profile does not exist.')
                    );
                }
            } else {
                $value = $this->_attributeProfile;
                $value->setSellerId($sellerId);
                $value->setProfileName($postData->getProfileName());
                $value->setAttributeSetId($postData->getAttributeSet());
                $value->setCreatedDate($this->_date->gmtDate());
                $id = $value->save()->getId();
                $attributeSetData->setId($id);
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }

        return $attributeSetData;
    }

    /**
     * Save Profile Attribute Data.
     *
     * @param array $postData
     * @param int $id
     */
    public function saveProfileAttributeMapData($postData, $id)
    {
        $existingMappingIds = [];
        if (!empty($postData['mage_attribute'])) {
            foreach ($postData['mage_attribute'] as $key => $mageAttribute) {
                if (!empty($mageAttribute)) {
                    // Check If profile does not exists
                    $attributeMappingData = $this->_attributeMappingRepository
                        ->getByMageAttribute($id, $mageAttribute);
                    $attributeMappingId = '';
                    foreach ($attributeMappingData as $value) {
                        $attributeMappingId = $value->getId();
                    }
                    if ($attributeMappingId) {
                        $attributeMapping = $this->loadDataItem($attributeMappingId);
                    } else {
                        $attributeMapping = $this->_attributeMapping->create();
                    }
                    $attributeMapping->setProfileId($id);
                    $attributeMapping->setFileAttribute($postData['file_attribute'][$key]);
                    $attributeMapping->setMageAttribute($mageAttribute);
                    $this->saveDataItem($attributeMapping);
                    array_push($existingMappingIds, $attributeMapping->getEntityId());
                }
            }
        }
        
        if (!empty($existingMappingIds)) {
            $allattribute = $this->_attributeMapping->create()
            ->getCollection()
            ->addFieldToFilter(
                'profile_id',
                $id
            );
            foreach ($allattribute as $attribute) {
                if (!in_array($attribute->getEntityId(), $existingMappingIds)) {
                    $this->deleteItems($attribute);
                }
            }
        }
        if (empty($postData['mage_attribute'])) {
            $allattribute = $this->_attributeMapping->create()
            ->getCollection()
            ->addFieldToFilter(
                'profile_id',
                $id
            );
            foreach ($allattribute as $attribute) {
                if (!in_array($attribute->getEntityId(), $existingMappingIds)) {
                    $this->deleteItems($attribute);
                }
            }
        }
    }

    /**
     * Load items
     *
     * @param int $attributeMappingId
     * @return void
     */
    public function loadDataItem($attributeMappingId)
    {
        $attributeMapping = $this->_attributeMapping->create()->load($attributeMappingId);
        return $attributeMapping;
    }

    /**
     * Delete items
     *
     * @param array $attribute
     * @return void
     */
    public function deleteItems($attribute)
    {
        $attribute->delete();
    }

    /**
     * Save data item
     *
     * @param array $attributeMapping
     * @return void
     */
    public function saveDataItem($attributeMapping)
    {
        $attributeMapping->save();
    }

    /**
     * Get Attribute List
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getAttributeList(\Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData)
    {
        $result['success'] = false;
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            if (!$attributeSetId = $attributeSetData->getAttributeSet()) {
                $result['success'] = false;
                $result['message'] = __('Please Enter Attribute Set Id');
                return $this->getJsonResponse($result);
            }
            $result['success'] = true;
            $attributecode = [];
            $groups = $this->_attributeGroup->create()
                ->setAttributeSetFilter($attributeSetId)
                ->setSortOrder()
                ->load();
            foreach ($groups as $node) {
                $nodeChildren = $this->loadData($node);
                if ($nodeChildren->getSize() > 0) {
                    foreach ($nodeChildren->getItems() as $child) {
                        $attributeData =  $this->getCatalogResourceEavAttribute($child->getAttributeId());
                            array_push($attributecode, $attributeData['attribute_code']);
                    }
                }
            }
            $result['attributecode'] = $attributecode;
            return $this->getJsonResponse($result);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Load data
     *
     * @param object $node
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    public function loadData($node)
    {
        $nodeChildren = [];
        $nodeChildren = $this->_productAttributeCollection->create()
                ->setAttributeGroupFilter($node->getId())
                ->addVisibleFilter()
                ->load();
        return $nodeChildren;
    }

    /**
     * Catalog resource data
     *
     * @param int $id
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getCatalogResourceEavAttribute($id)
    {
        return $this->_eavAttribute->load($id);
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
        $res = $this->responseInterface;
        $res->setItem($responseContent);
        return $res->getData();
    }

    /**
     * Map Attributes
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeMapData
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function mapAttributes(\Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeMapData)
    {
        $id = $attributeMapData->getId();
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            if (empty($attributeMapData->getMageAttribute()) || empty($attributeMapData->getFileAttribute())) {
                $attributeMapData->setEditId($id);
                $attributeMapData->setErrorStatus(1);
                $attributeMapData->setMessage(__('Please fill the Required Fields'));
            }
            $attributeProfile = $this->_attributeProfileRepository->get($id);
            if ($attributeProfile->getId()) {
                if ($attributeProfile->getSellerId() == $customerId) {
                    $value = $this->_attributeProfile->load($id);
                    $value->setProfileName($attributeMapData->getProfileName());
                    $value->setAttributeSetId($attributeMapData->getAttributeSet());
                    $value->save();
                    $attributeMapData->setId($id);
                }
            }
            $postData['mage_attribute'] = $this->helper->getJsonDecode(
                $attributeMapData->getMageAttribute()
            );
            $postData['file_attribute'] = $this->helper->getJsonDecode(
                $attributeMapData->getFileAttribute()
            );
 
            $this->saveProfileAttributeMapData($postData, $id);
            $attributeMapData->setEditId($id);
            $attributeMapData->setErrorStatus(0);
            $attributeMapData->setMessage(__('Profile was successfully saved'));
        } catch (\Exception $e) {
            $attributeMapData->setEditId($id);
            $attributeMapData->setErrorStatus(1);
            $attributeMapData->setMessage(__($e->getMessage()));
        }
        return $attributeMapData;
    }

    /**
     * Get Attribute Mapped Data
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeMapData
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMappedAttributeDetails(\Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeMapData)
    {
        $result = [];
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            if ($attributeMapData && $attributeMapData->getId()) {
                $attrProfileId =  $attributeMapData->getId();
                $attributeProfile = $this->_attributeProfileRepository->get($attrProfileId);
                $mapAttributes =  $this->_attributeMappingRepository->getByProfileId($attrProfileId);
                $result['error'] = false;
                $result['attributeProfile'] = $attributeProfile->getData();
                $result['mapAttributes'] = $mapAttributes->getData();
                return $this->getJsonResponse($result);
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Get Sample Files
     *
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getSampleFiles()
    {
        $sampleCsv = $this->getSampleFilePrepare($this->helper->getSampleCsv());
        $sampleXml = $this->getSampleFilePrepare($this->helper->getSampleXml());
        $sampleXls = $this->getSampleFilePrepare($this->helper->getSampleXls());
        $sampleFiles = array_merge($sampleCsv, $sampleXml);
        $sampleFiles = array_merge($sampleFiles, $sampleXls);
        return $this->getJsonResponse($sampleFiles);
    }

    /**
     * Prepare Sample Files
     *
     * @param array $sampleFiles
     * @return array
     */
    public function getSampleFilePrepare($sampleFiles)
    {
        $result =[];
        foreach ($sampleFiles as $sampleFile) {
            $fileData = $this->file->getPathInfo($sampleFile);
            switch ($fileData['filename']) {
                case 'simple':
                    $result[$fileData['extension']][$fileData['filename']]['label'] = __(
                        'Sample Simple Type '.ucfirst($fileData['extension']).' File'
                    );
                    $result[$fileData['extension']][$fileData['filename']]['path'] = $sampleFile;
                    break;
                case 'downloadable':
                    $result[$fileData['extension']][$fileData['filename']]['label'] = __(
                        'Sample Downloadable Type '.ucfirst($fileData['extension']).' File'
                    );
                    $result[$fileData['extension']][$fileData['filename']]['path']= $sampleFile;
                    break;
                case 'config':
                    $result[$fileData['extension']][$fileData['filename']]['label'] = __(
                        'Sample Configurable Type '.ucfirst($fileData['extension']).' File'
                    );
                    $result[$fileData['extension']][$fileData['filename']]['path'] = $sampleFile;
                    break;
                case 'virtual':
                    $result[$fileData['extension']][$fileData['filename']]['label'] = __(
                        'Sample Virtual Type '.ucfirst($fileData['extension']).' File'
                    );
                    $result[$fileData['extension']][$fileData['filename']]['path'] = $sampleFile;
                    break;
            }
            
        }
        return $result;
    }

    /**
     * Get Sample Files
     *
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getSuperAttributes()
    {
        $result = [];
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            $superAttributes = (array) $this->helper->getSuperAttributes();
            $result['sucess'] = true;
            $result['attributeInfo'] = implode(", ", $superAttributes);
            return $this->getJsonResponse($result);
        } catch (\Exception $e) {
            $result['sucess'] = false;
            $result['message'] = __($e->getMessage());
            return $this->getJsonResponse($result);
        }
    }

    /**
     * Get Super Atrribute Options
     *
     * @param string $attributeCode
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getSuperAttributeOption($attributeCode)
    {
        $result = [];
        try {
            $result['success'] = true;
            $result['options'] = $this->helper->getAttributeOptions($attributeCode);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = __($e->getMessage());
        }
        return $this->getJsonResponse($result);
    }

    /**
     * Get Product Export File
     *
     * @param string $productType
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getProductExport($productType)
    {
        $result = [];
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }

            if ($productType) {
                $allowedAttributes = [];
                $fileName = $productType.'_product.csv';
                $products = $this->_mpProduct
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $customerId
                )->addFieldToSelect(
                    ['mageproduct_id']
                );
                $productIds = $products->getAllIds();
                $productsRow = $this->_helperExport->exportProducts(
                    $productType,
                    $productIds,
                    $allowedAttributes
                );
                
                $this->setHeaderColumns($productsRow, $fileName, $productType);
            }

            return $this->getJsonResponse($result);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Set header columns
     *
     * @param array $productsRow
     * @param string $fileName
     * @param string $productType
     * @return void
     */
    public function setHeaderColumns($productsRow, $fileName, $productType)
    {
        if (!empty($productsRow)) {
            $writer = $this->_writer;
            $writer->setHeaderCols($productsRow[0]);
            foreach ($productsRow[1] as $dataRow) {
                if (!empty($dataRow)) {
                    $writer->writeRow($dataRow);
                }
            }
            $productsRow = $writer->getContents();
            $this->fileFactory->create(
                $fileName,
                $productsRow,
                DirectoryList::VAR_DIR,
                'text/csv'
            );
        }
    }

    /**
     * Delete Profile
     *
     * @param string $profileIds
     * @return void
     */
    public function deleteAttributeProfile($profileIds)
    {
        $result = [];
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }

            if ($profileIds) {
                $countRecord = 0;
                $profileIds = $this->helper->getJsonDecode($profileIds);
                if (count($profileIds)==0) {
                    $result['error'] = true;
                    $result['message'] = __('Please Select record');
                }
                foreach ($profileIds as $profileId) {
                    $attributeProfile = $this->_attributeProfileRepository->get($profileId);
                    if ($attributeProfile->getId()) {
                        if ($attributeProfile->getSellerId() == $customerId) {
                            $this->_attributeProfileRepository->deleteById($profileId);
                            $countRecord++;
                        }
                    }
                }
                if ($countRecord>0) {
                    $result['error'] = false;
                    $result['message'] = __('A total of %1 record(s) have been deleted.', $countRecord);
                } else {
                    $result['error'] = true;
                    $result['message'] = __('Selected Items not longer existing');
                }

                return $this->getJsonResponse($result);
            }
            return $this->getJsonResponse($result);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
    
    /**
     * Retrieve Attribute Profile List.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $sellerId
     * @return \Magento\Framework\Api\SearchResults
     */
    public function getAttributeProfileList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $sellerId)
    {
        $profileCollection = $this->_attributeProfile->getCollection()
        ->addFieldToFilter('seller_id', ['eq'=>$sellerId]);

        $eavAttributeSet = $this->resource->getTableName('eav_attribute_set');
        
        $profileCollection->getSelect()->join(
            $eavAttributeSet.' as eas',
            'main_table.attribute_set_id = eas.attribute_set_id',
            ["attribute_name" => "attribute_set_name"]
        );
        $profileCollection->setOrder('created_date', 'DESC');
        
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $filter->setField('main_table.'.$filter->getField());
                if ($filter->getField() === 'store_id') {
                    $profileCollection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $profileCollection->addFieldToFilter($fields, $conditions);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $profileCollection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $profileCollection->setCurPage($searchCriteria->getCurrentPage());
        $profileCollection->setPageSize($searchCriteria->getPageSize());
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($profileCollection->getSize());
        $searchResults->setItems($profileCollection->getData());
        return $searchResults;
    }
}
