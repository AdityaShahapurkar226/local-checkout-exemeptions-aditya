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

namespace Webkul\MpMassUpload\Controller\Adminhtml\Dataflow\Profile;

/**
 * Webkul MassUpload Adminhtml Dataflow Profile Save Controller.
 */
class Save extends \Webkul\MpMassUpload\Controller\Adminhtml\Dataflow\AbstractProfile
{
    /**
     * MassUpload Dataflow Profile Save action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Validator\Exception|\Exception
     */
    public function execute()
    {
        $returnToEdit = false;
        $postData = $this->getRequest()->getPostValue();
        $id = isset($postData['id'])
            ? $postData['id']
            : null;
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        if ($postData) {
            try {
                $sellerId = 0;
                if (!empty($id)) {
                    // Check If profile does not exists
                    $attributeProfile = $this->_attributeProfileRepository->get($id);
                    if ($attributeProfile->getId()) {
                        $value = $this->_attributeProfile->load($id);
                        $value->setProfileName($postData['profile_name']);
                        $value->setAttributeSetId($postData['attribute_set_id']);
                        $value->save();
                        $this->saveProfileAttributeMapData($postData, $id);
                        $this->messageManager->addSuccess(
                            __('Profile was successfully saved.')
                        );
                    } else {
                        $this->messageManager->addError(
                            __('Dataflow profile does not exist.')
                        );
                    }
                } else {
                    $value = $this->_attributeProfile;
                    $value->setSellerId($sellerId);
                    $value->setProfileName($postData['profile_name']);
                    $value->setAttributeSetId($postData['attribute_set_id']);
                    $value->setCreatedDate($this->_date->gmtDate());
                    $id = $value->save()->getId();
                    $this->messageManager->addSuccess(
                        __('Profile was successfully created.')
                    );
                }
                return $this->resultRedirectFactory->create()->setPath(
                    'mpmassupload/dataflow_profile/edit',
                    ['id'=>$id, '_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    'mpmassupload/dataflow_profile/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'mpmassupload/dataflow_profile/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
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
                        $attributeMapping = $this->loadItem($attributeMappingId);
                    } else {
                        $attributeMapping = $this->_attributeMapping->create();
                    }
                    $attributeMapping->setProfileId($id);
                    $attributeMapping->setFileAttribute($postData['file_attribute'][$key]);
                    $attributeMapping->setMageAttribute($mageAttribute);
                    $this->saveItem($attributeMapping);
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
    }

    /**
     * Load item
     *
     * @param int $attributeMappingId
     * @return void
     */
    public function loadItem($attributeMappingId)
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
     * Saves item
     *
     * @param object $attributeMapping
     * @return void
     */
    public function saveItem($attributeMapping)
    {
        $attributeMapping->save();
    }
}
