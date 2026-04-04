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
namespace Webkul\SellerSubAccount\Plugin\Controller\Product;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface as MessageManager;

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
     * @param HelperData        $helper
     * @param MarketplaceHelper $marketplaceHelper
     * @param PageFactory       $resultPageFactory
     * @param RedirectFactory   $resultRedirectFactory
     * @param Registry          $coreRegistry
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct     $sellerProductCollectionFactory
     * @param EventManager      $eventManager
     * @param MessageManager    $messageManager
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
        MessageManager $messageManager
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->_eventManager = $eventManager;
        $this->_messageManager = $messageManager;
    }

    /**
     * Around Execute.
     *
     * @param \Webkul\Marketplace\Controller\Product\Delete $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundExecute(
        \Webkul\Marketplace\Controller\Product\Delete $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $proceed();
        }

        $isPartner = $this->_marketplaceHelper->isSeller();
        if ($isPartner == 1) {
            try {
                $wholedata = $block->getRequest()->getParams();

                $this->_eventManager->dispatch(
                    'mp_delete_product',
                    [$wholedata]
                );

                $sellerId = $this->_helper->getSubAccountSellerId();
                $deleteFlag = 0;
                //set secure area
                $this->_coreRegistry->register('isSecureArea', 1);
                $deletedProductId = '';
                $sellerProducts = $this->_sellerProductCollectionFactory
                ->create()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $wholedata['id']
                )->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                foreach ($sellerProducts as $sellerProduct) {
                    $deletedProductId = $sellerProduct['mageproduct_id'];
                    $sellerProduct->delete();
                }

                $mageProducts = $this->_productCollectionFactory
                ->create()
                ->addFieldToFilter(
                    'entity_id',
                    $deletedProductId
                );
                foreach ($mageProducts as $mageProduct) {
                    $mageProduct->delete();
                    $deleteFlag = 1;
                }
                //unset secure area
                $this->_coreRegistry->unregister('isSecureArea');
                if ($deleteFlag) {
                    $this->_messageManager->addSuccess(
                        __('Product has been successfully deleted from your account.')
                    );
                } else {
                    $this->_messageManager->addError(
                        __('You are not authorize to delete this product.')
                    );
                }

                return $this->_resultRedirectFactory->create()->setPath(
                    '*/*/productlist',
                    ['_secure' => $block->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());

                return $this->_resultRedirectFactory->create()->setPath(
                    '*/*/productlist',
                    ['_secure' => $block->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->_resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $block->getRequest()->isSecure()]
            );
        }
    }
}
