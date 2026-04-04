<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

class AdminProductDeleteAfter implements ObserverInterface
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /**
     * Initialization
     *
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_assignHelper = $helper;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
    }

    /**
     * Assign product to seller on admin product delete
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $productId = $product->getId();
            if (!$this->_assignHelper->assignToSeller() && !$this->_assignHelper->hasAssignedProducts($productId)) {
                return;
            }
            $this->_assignHelper->assignSellerToAdminProduct($productId);
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
}
