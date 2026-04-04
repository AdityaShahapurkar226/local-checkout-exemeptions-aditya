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
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory as SubSellerCollectionFactory;

class Delete
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

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
     * @param MarketplaceHelper $marketplaceHelper
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Registry $coreRegistry
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct $sellerProductCollectionFactory
     * @param EventManager $eventManager
     * @param MessageManager $messageManager
     * @param SellerCollectionFactory $collectionFactory
     * @param SubSellerCollectionFactory $subSellerCollection
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory,
        Registry $coreRegistry,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        EventManager $eventManager,
        MessageManager $messageManager,
        SellerCollectionFactory $collectionFactory,
        SubSellerCollectionFactory $subSellerCollection
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->subSellerCollection=$subSellerCollection;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->_eventManager = $eventManager;
        $this->_messageManager = $messageManager;
    }

    /**
     * Around Execute
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\Delete $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Adminhtml\Index\Delete $block,
        \Closure $proceed
    ) {

        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addErrorMessage(__('Customer could not be deleted.'));
            return $resultRedirect->setPath('customer/index');
        }

        $customerId = $this->initCurrentCustomer();
        if (!empty($customerId)) {
            try {
                $subAccount=$this->getSubAccountsList($customerid);
                    
                if ($this->isSeller($customerId) && $subAccount):
                    foreach ($subAccount as $account):
                           $sCustomerId=$account->getCustomerId();
                           $subSellerCustomer = $this->_customerRepository->getById($sCustomerId);
                           $subSellerCustomer->setDisableAutoGroupChange(0);
                           $subSellerCustomer->setGroupId(1);
                        if ($subSellerCustomer):
                            $this->_customerRepository->save($subSellerCustomer);
                        endif;
                           $account->setStatus(0);
                           $account->delete();
                    endforeach;
                endif;

                $this->_customerRepository->deleteById($customerId);
                $this->messageManager->addSuccessMessage(__('You deleted the customer.'));
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('customer/index');
    }

    /**
     * Is Seller
     *
     * @param int $customerid
     *
     * @return int|boolean
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
