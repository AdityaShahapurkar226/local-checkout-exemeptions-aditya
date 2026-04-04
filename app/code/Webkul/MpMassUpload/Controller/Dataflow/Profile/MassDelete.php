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
 * Webkul MassUpload Dataflow Profile MassDelete Controller.
 */
class MassDelete extends \Webkul\MpMassUpload\Controller\Dataflow\AbstractProfile
{
    /**
     * MassUpload Dataflow Profile MassDelete action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $sellerId = $this->marketplaceHelper->getCustomerId();
            $wholedata = $this->getRequest()->getParams();
            $ids = $this->getRequest()->getParam('profile_mass_delete');
            foreach ($ids as $key => $id) {
                // Check If profile does not exists
                $attributeProfile = $this->_attributeProfileRepository->get($id);
                if ($attributeProfile->getId()) {
                    if ($attributeProfile->getSellerId() == $sellerId) {
                        $this->_attributeProfileRepository->deleteById($id);
                        $this->messageManager->addSuccess(
                            __('Profile " %1 " was successfully deleted.', $attributeProfile->getProfileName())
                        );
                    } else {
                        $this->messageManager->addError(
                            __('You are not authorized to delete profile " %1 ".', $attributeProfile->getProfileName())
                        );
                    }
                } else {
                    $this->messageManager->addError(
                        __('Requested profile " %1 " doesn\'t exist', $attributeProfile->getProfileName())
                    );
                }
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
