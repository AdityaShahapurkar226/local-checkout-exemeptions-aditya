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
namespace Webkul\SellerSubAccount\Controller\Adminhtml\Account;

class Manage extends \Webkul\SellerSubAccount\Controller\Adminhtml\AbstractSubAccount
{

    /**
     * Sub Account List page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $sellerId = (int)$this->getRequest()->getParam('seller_id');
        if ($sellerId) {
            $this->_coreRegistry->register(
                'seller_id',
                $sellerId
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/seller/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Marketplace::menu');
        $resultPage->getConfig()
                    ->getTitle()
                    ->prepend(__('Seller Sub Accounts'));

        return $resultPage;
    }
}
