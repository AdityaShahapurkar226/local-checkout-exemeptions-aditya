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

namespace Webkul\MpMassUpload\Controller\Dataflow\Profile;

/**
 * Webkul MassUpload Dataflow Profile Save Controller.
 */
class Save extends \Webkul\MpMassUpload\Controller\Dataflow\AbstractProfile
{
    /**
     * MassUpload Dataflow Profile Save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpmassupload/dataflow/profile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $postData = $this->getRequest()->getParams();
                $sellerId = $this->marketplaceHelper->getCustomerId();
                if (empty($postData['profile_name']) || empty($postData['attribute_set_id'])) {
                    $this->messageManager->addError(
                        __('Please fill the Required Fields')
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpmassupload/dataflow/profile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                if (preg_match('/<script>/', $postData['profile_name'])) {
                    $this->messageManager->addError(
                        __('Please Enter valid values')
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpmassupload/dataflow/profile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $id = isset($postData['id'])
                    ? $postData['id']
                    : null;
                if (!empty($id)) {
                    // Check If profile does not exists
                    $attributeProfile = $this->_attributeProfileRepository->get($id);
                    if ($attributeProfile->getId()) {
                        if ($attributeProfile->getSellerId() == $sellerId) {
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
                                __('You are not authorized to update this profile.')
                            );
                        }
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
                    'mpmassupload/dataflow/profile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'mpmassupload/dataflow/profile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Save Profile Attribute Data.
     *
     * @param array $postData
     * @param int $id
     * @return void
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
    }

    /**
     * Load items
     *
     * @param array $attributeMappingId
     * @return \Webkul\MpMassUpload\Model\AttributeMappingFactory
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
}
