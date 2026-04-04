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
namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MpMassUpload\Api\Data\AttributeProfileInterface;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\CollectionFactory;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile as ResourceModelAttributeProfile;
use Webkul\MpMassUpload\Api\AttributeProfileRepositoryInterface;

class AttributeProfileRepository implements AttributeProfileRepositoryInterface
{
    /**
     * @var AttributeProfileFactory
     */
    protected $_attributeProfileFactory;

    /**
     * @var AttributeProfile[]
     */
    protected $_instancesById = [];

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ResourceModelAttributeProfile
     */
    protected $_resourceModel;

    /**
     * @param AttributeProfileFactory       $attributeProfileFactory
     * @param CollectionFactory             $collectionFactory
     * @param ResourceModelAttributeProfile $resourceModel
     */
    public function __construct(
        AttributeProfileFactory $attributeProfileFactory,
        CollectionFactory $collectionFactory,
        ResourceModelAttributeProfile $resourceModel
    ) {
        $this->_attributeProfileFactory = $attributeProfileFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceModel = $resourceModel;
    }

    /**
     * Get attribute profile data by entity id
     *
     * @param int $entityId
     * @return AttributeProfileFactory
     */
    public function get($entityId)
    {
        $attributeProfileData = $this->_attributeProfileFactory->create();
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\Collection $attributeProfileData */
        $attributeProfileData->load($entityId);
        if (!$attributeProfileData->getId()) {
            $this->_instancesById[$entityId] = $attributeProfileData;
        }
        $this->_instancesById[$entityId] = $attributeProfileData;

        return $this->_instancesById[$entityId];
    }

    /**
     * Get attribute profile data by seller id
     *
     * @param int $sellerId
     * @return \Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\CollectionFactory
     */
    public function getBySellerId($sellerId)
    {
        $attributeProfileCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId);
        $attributeProfileCollection->load();

        return $attributeProfileCollection;
    }

    /**
     * Get attribute profile data by profile name
     *
     * @param int $sellerId
     * @param string $profileName
     * @return \Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\CollectionFactory
     */
    public function getByProfileName($sellerId, $profileName)
    {
        $attributeProfileCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('profile_name', $profileId);
        $attributeProfileCollection->load();

        return $attributeProfileCollection;
    }

    /**
     * GetList
     *
     * @return \Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\CollectionFactory
     */
    public function getList()
    {
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * Delete Attribute Profile
     *
     * @param AttributeProfileInterface $attributeProfile
     * @return boolean
     */
    public function delete(AttributeProfileInterface $attributeProfile)
    {
        $entityId = $attributeProfile->getId();
        try {
            $this->_resourceModel->delete($attributeProfile);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove temp profile data record with id %1', $entityId)
            );
        }
        unset($this->_instancesById[$entityId]);

        return true;
    }

    /**
     * Delete Attribute Profile By Id
     *
     * @param int $entityId
     * @return void
     */
    public function deleteById($entityId)
    {
        $attributeProfile = $this->get($entityId);

        return $this->delete($attributeProfile);
    }

    /**
     * Delete Attribute Profile By Seller Id
     *
     * @param int $sellerId
     * @return void
     */
    public function deleteBySellerId($sellerId)
    {
        $attributeProfile = $this->getBySellerId($sellerId);

        return $this->delete($attributeProfile);
    }

    /**
     * Delete Attribute Profile By Profile Name
     *
     * @param int $sellerId
     * @param string $profileName
     * @return void
     */
    public function deleteByProfileName($sellerId, $profileName)
    {
        $attributeProfile = $this->getByProfileName($sellerId, $profileName);

        return $this->delete($attributeProfile);
    }
}
