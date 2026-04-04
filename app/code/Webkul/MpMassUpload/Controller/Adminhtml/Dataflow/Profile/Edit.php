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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul MassUpload Dataflow Profile Edit Controller.
 */
class Edit extends \Webkul\MpMassUpload\Controller\Adminhtml\Dataflow\AbstractProfile
{
    /**
     * MassUpload Dataflow Profile Edit action.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $attributeProfileId = $this->initCurrentAttributeProfile();
        $isExistingAttributeProfile = (bool)$attributeProfileId;
        if ($isExistingAttributeProfile) {
            try {
                $attributeProfileData = [];
                $attributeProfileData['mpmassupload_dataflow_profile'] = [];
                $attributeProfile = null;
                $attributeProfile = $this->_attributeProfileRepository->get(
                    $attributeProfileId
                );
                $result = $attributeProfile->getData();
                if (!empty($result)) {
                    $attributeProfileData['mpmassupload_dataflow_profile'] = $result;
                    $this->_getSession()->setAttributeProfileFormData($attributeProfileData);
                } else {
                    $this->messageManager->addError(
                        __('Requested dataflow profile doesn\'t exist')
                    );
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('mpmassupload/dataflow/profile');
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while editing the dataflow profile.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('mpmassupload/dataflow/profile');
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $content = $resultPage->getLayout()->createBlock(
            \Webkul\MpMassUpload\Block\Adminhtml\Dataflow\Profile::class
        );
        $resultPage->addContent($content);
        $resultPage->setActiveMenu('Webkul_MpMassUpload::dataflow_profile');
        $this->prepareDefaultTitle($resultPage);
        if ($isExistingAttributeProfile) {
            $resultPage->getConfig()->getTitle()->prepend(
                __('Edit Dataflow Profile with id %1', $attributeProfileId)
            );
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Dataflow Profile'));
        }
        return $resultPage;
    }

    /**
     * Attribute Profile initialization
     *
     * @return int
     */
    protected function initCurrentAttributeProfile()
    {
        $attributeProfileId = (int)$this->getRequest()->getParam('id');

        if ($attributeProfileId) {
            $this->_coreRegistry->register(
                'mpmassupload_dataflow_profile',
                $attributeProfileId
            );
        }

        return $attributeProfileId;
    }

    /**
     * Prepare Dataflow Profile default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultTitle(
        \Magento\Backend\Model\View\Result\Page $resultPage
    ) {
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Dataflow Profile'));
    }
}
