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
namespace Webkul\SellerSubAccount\Plugin\Controller\Transaction;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\Marketplace\Model\Sellertransaction;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;

class View
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
     * @var Sellertransaction
     */
    public $_sellertransaction;

    /**
     * @var PageFactory
     */
    public $_resultPageFactory;

    /**
     * @var RedirectFactory
     */
    public $_resultRedirectFactory;

    /**
     * @param HelperData        $helper
     * @param MarketplaceHelper $marketplaceHelper
     * @param Sellertransaction $sellertransaction
     * @param PageFactory       $resultPageFactory
     * @param RedirectFactory   $resultRedirectFactory
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        Sellertransaction $sellertransaction,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_sellertransaction = $sellertransaction;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Around Execute
     *
     * @param \Webkul\Marketplace\Controller\Transaction\View $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundExecute(
        \Webkul\Marketplace\Controller\Transaction\View $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $proceed();
        }

        $sellerId = $this->_helper->getSubAccountSellerId();

        $id = 0;
        $paramData = $block->getRequest()->getParams();
        if (!empty($paramData['id'])) {
            $id = $paramData['id'];
        }
        $collection = $this->_sellertransaction
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        )
        ->addFieldToFilter(
            'entity_id',
            $id
        );
        if ($collection->getSize()) {
            $isPartner = $this->_marketplaceHelper->isSeller();
            $isNotifyView = $block->getRequest()->getParam('n')?true:false;
            if ($isPartner == 1) {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->_resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(
                    __('Marketplace Seller Transaction View')
                );
                if ($isNotifyView) {
                    foreach ($collection as $value) {
                        $isNotification = $value->getSellerPendingNotification();
                        if ($isNotification) {
                            $value->setSellerPendingNotification(0);
                            $value->setId($value->getEntityId())->save();
                        }
                    }
                }
                return $resultPage;
            } else {
                return $this->_resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $block->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->_resultRedirectFactory->create()->setPath(
                'marketplace/transaction/history',
                ['_secure' => $block->getRequest()->isSecure()]
            );
        }
    }
}
