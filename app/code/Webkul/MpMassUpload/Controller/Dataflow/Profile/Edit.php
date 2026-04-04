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
 * Webkul MassUpload Dataflow Profile Edit Controller.
 */
class Edit extends \Webkul\MpMassUpload\Controller\Dataflow\AbstractProfile
{
    /**
     * MassUpload Dataflow Profile Edit action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $sellerId = $this->marketplaceHelper->getCustomerId();
            $id = (int)$this->getRequest()->getParam('id');
            // Check If profile does not exists
            $attributeProfile = $this->_attributeProfileRepository->get($id);
            if ($attributeProfile->getId()) {
                if ($attributeProfile->getSellerId() == $sellerId) {
                    $resultPage = $this->_resultPageFactory->create();
                    if ($this->marketplaceHelper->getIsSeparatePanel()) {
                        $resultPage->addHandle('mpmassupload_layout2_dataflow_profile_edit');
                    }
                    $resultPage->getConfig()->getTitle()->set(
                        __('Edit Dataflow Profile')
                    );
                    return $resultPage;
                } else {
                    $this->messageManager->addError(
                        __('You are not authorized to update this profile.')
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Requested profile doesn\'t exist.')
                );
            }
            return $this->resultRedirectFactory->create()->setPath(
                'mpmassupload/dataflow/profile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                'mpmassupload/dataflow/profile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
