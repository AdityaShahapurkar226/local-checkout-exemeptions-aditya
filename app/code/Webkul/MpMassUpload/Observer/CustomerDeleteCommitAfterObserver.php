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

namespace Webkul\MpMassUpload\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpMassUpload\Model\AttributeProfileFactory;

/**
 * Webkul MpMassUpload CustomerDeleteCommitAfterObserver Observer Model.
 */
class CustomerDeleteCommitAfterObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpMassUpload\Model\AttributeProfileFactory
     */
    protected $attributeProfile;

    /**
     * @param AttributeProfileFactory $attributeProfile
     */
    public function __construct(
        AttributeProfileFactory $attributeProfile
    ) {
        $this->attributeProfile = $attributeProfile;
    }

    /**
     * Customer Delete After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $customer = $observer->getCustomer();
            $customerId = $customer->getId();
            $attributeProfile = $this->attributeProfile->create()->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $customerId]);
            if (!empty($attributeProfile)) {
                foreach ($attributeProfile as $profiles) {
                    $profiles->delete();
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
