<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubAccount\Observer;

use Magento\Framework\Event\ObserverInterface;

use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory as SubSellerCollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Webkul Marketplace SellerStatusChange Observer.
 */

class SellerStatusChange implements ObserverInterface
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    public $_fileUploaderFactory;

    /**
     * @var ObjectManagerInterface
     */
    public $_objectManager;

    /**
     * @var CollectionFactory
     */
    public $_collectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $_messageManager;
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    public $_jsonDecoder;

     /**
      * @var \Webkul\Marketplace\Model\SellerFactory
      */
    public $_sellerModel;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerModel
     * @param SubSellerCollectionFactory $subSellerCollection
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel,
        SubSellerCollectionFactory $subSellerCollection
    ) {
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_collectionFactory = $collectionFactory;
        $this->subSellerCollection=$subSellerCollection;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_sellerModel = $sellerModel;
    }

    /**
     * Admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $seller = $observer->getSeller();
         $customerid = $seller->getId();
        if ($this->isSeller($customerid)!="1") {
            $subAccount=$this->getSubAccountsList($customerid);
            if ($subAccount) {
                foreach ($subAccount as $account) {
                    $account->setStatus(0);
                    $account->save();
                }
            }
        }
    }
    /**
     * IsSeller function returns customer is seller or not
     *
     * @param int $customerid
     * @return boolean
     */
    public function isSeller($customerid)
    {
        $sellerStatus = 0;
        $model = $this->_collectionFactory->create()
        ->addFieldToFilter('seller_id', $customerid)
        ->addFieldToFilter('store_id', 0);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }
        return $sellerStatus;
    }
    
    /**
     * Get sub accounts list
     *
     * @param int $sellerId
     * @return void
     */
    public function getSubAccountsList($sellerId)
    {
        $collection = $this->subSellerCollection->create()
        ->addFieldToFilter('seller_id', $sellerId);
        if ($collection->getSize()) {
            if ($collection) {
                return $collection;
            } else {
                return false;
            }
            
        }
    }
}
