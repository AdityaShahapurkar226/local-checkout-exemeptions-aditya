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
     * @var CollectionFactory
     */
    public $_productCollectionFactory;

    /**
     * @var SellerProduct
     */
    public $_sellerProductCollectionFactory;

    /**
     * @var RedirectFactory
     */
    public $_resultRedirectFactory;
    
    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;

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
     * @param Registry $coreRegistry
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct $sellerProductCollectionFactory
     * @param MarketplaceHelper $marketplaceHelper
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param EventManager $eventManager
     * @param MessageManager $messageManager
     */
    public function __construct(
        HelperData $helper,
        Registry $coreRegistry,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        MarketplaceHelper $marketplaceHelper,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory,
        EventManager $eventManager,
        MessageManager $messageManager
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_messageManager = $messageManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_eventManager = $eventManager;
    }

    /**
     * Around Execute
     *
     * @param \Webkul\Marketplace\Controller\Product\MassDelete $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundExecute(
        \Webkul\Marketplace\Controller\Product\MassDelete $block,
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

                $ids = $block->getRequest()->getParam('product_mass_delete');

                $this->_eventManager->dispatch(
                    'mp_delete_product',
                    [$wholedata]
                );

                $sellerId = $this->_helper->getSubAccountSellerId();
                $this->_coreRegistry->register('isSecureArea', 1);
                $deletedIdsArr = [];
                $sellerProducts = $this->_sellerProductCollectionFactory
                ->create()
                ->addFieldToFilter(
                    'mageproduct_id',
                    ['in' => $ids]
                )->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                foreach ($sellerProducts as $sellerProduct) {
                    array_push($deletedIdsArr, $sellerProduct['mageproduct_id']);
                    $sellerProduct->delete();
                }

                $mageProducts = $this->_productCollectionFactory
                ->create()
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $deletedIdsArr]
                );
                foreach ($mageProducts as $mageProduct) {
                    $mageProduct->delete();
                }
                $unauthIds = array_diff($ids, $deletedIdsArr);
                $this->_coreRegistry->unregister('isSecureArea');
                if (count($unauthIds)) {
                    $this->_messageManager->addError(
                        __(
                            'You are not authorize to delete products with id(s) %1 .',
                            implode(',', $unauthIds)
                        )
                    );
                } else {
                    $this->_messageManager->addSuccess(
                        __('Products are successfully deleted from your account.')
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
