<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 *
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

class AssignSeller implements ObserverInterface
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var SellerProduct
     */
    protected $_sellerProductCollectionFactory;

    /**
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param Session $customerSession
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $sellerProductCollectionFactory
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        Session $customerSession,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $sellerProductCollectionFactory
    ) {
        $this->_assignHelper = $helper;
        $this->_customerSession = $customerSession;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $observer->getData();
        $ids = [];
        if (array_key_exists(0, $params)) {
            $sellerId = $this->_customerSession->getCustomerId();
            if (array_key_exists('id', $params[0])) {
                $productId = $params[0]['id'];
                if (!$this->_assignHelper->hasAssignedProducts($productId)) {
                    return;
                }
                $sellerProducts = $this->_sellerProductCollectionFactory
                                ->create()
                                ->addFieldToFilter(
                                    'mageproduct_id',
                                    $productId
                                )->addFieldToFilter(
                                    'seller_id',
                                    $sellerId
                                );
                if ($this->_customerSession->getAssignProductIds()) {
                    $ids = $this->_customerSession->getAssignProductIds();
                }
                if ($sellerProducts->getSize()) {
                    $ids[] = $productId;
                    $this->_customerSession->setAssignProductIds($ids);
                    $this->_assignHelper->assignSeller($productId);
                }
            }
        }
    }
}
