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
namespace Webkul\SellerSubAccount\Plugin\Controller\Seller;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Registry;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory as SubSellerCollectionFactory;

class MassDelete
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var PageFactory
     */
    public $_resultPageFactory;

    /**
     * @var RedirectFactory
     */
    public $_resultRedirectFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    public $_productCollectionFactory;

    /**
     * @var SellerProduct
     */
    public $_sellerProductCollectionFactory;

    /**
     * @var EventManager
     */
    public $_eventManager;

    /**
     * @var MessageManager
     */
    public $_messageManager;

    /**
     * @param HelperData $helper
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Registry $coreRegistry
     * @param SellerProduct $sellerProductCollectionFactory
     * @param EventManager $eventManager
     * @param MessageManager $messageManager
     * @param SellerCollectionFactory $collectionFactory
     * @param SubSellerCollectionFactory $subSellerCollection
     */
    public function __construct(
        HelperData $helper,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory,
        Registry $coreRegistry,
        SellerProduct $sellerProductCollectionFactory,
        EventManager $eventManager,
        MessageManager $messageManager,
        SellerCollectionFactory $collectionFactory,
        SubSellerCollectionFactory $subSellerCollection
    ) {
        $this->_helper = $helper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->subSellerCollection=$subSellerCollection;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->_eventManager = $eventManager;
        $this->_messageManager = $messageManager;
    }

    /**
     * Around Execute
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\MassDelete $block
     * @param \Closure $proceed
     * @param AbstractCollection $collection
     *
     * @return int
     */
    public function aroundMassAction(
        \Magento\Customer\Controller\Adminhtml\Index\MassDelete $block,
        \Closure $proceed,
        $collection
    ) {
        $customersDeleted = 0;
        foreach ($collection->getAllIds() as $customerId) {
            if ($this->isSeller($customerId)) {
                $subAccount=$this->getSubAccountsList($customerid);
                if ($subAccount) {
                    foreach ($subAccount as $account) {
                        $sCustomerId=$account->getCustomerId();
                        $subSellerCustomer = $this->_customerRepository->getById($sCustomerId);
                        if ($subSellerCustomer) {
                            $subSellerCustomer->setGroupId(1);
                            $subSellerCustomer->setDisableAutoGroupChange(0);
                            $this->_customerRepository->save($subSellerCustomer);
                        }
                        $account->setStatus(0);
                        $account->delete();
                    }
                }
            }
            $this->customerRepository->deleteById($customerId);
            $customersDeleted++;
        }

        if ($customersDeleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $customersDeleted));
        }
        /** @var \Magen`to\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * Is Seller
     *
     * @param int $customerid
     *
     * @return boolean|int
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
     * Get Sub Accounts List
     *
     * @param int $sellerId
     *
     * @return boolean|object
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
