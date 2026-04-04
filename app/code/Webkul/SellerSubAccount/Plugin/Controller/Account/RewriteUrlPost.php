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
namespace Webkul\SellerSubAccount\Plugin\Controller\Account;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Model\Seller;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class RewriteUrlPost
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
     * @var RedirectFactory
     */
    public $_resultRedirectFactory;

    /**
     * @var FormKeyValidator
     */
    public $_formKeyValidator;

    /**
     * @var Seller
     */
    public $_seller;

    /**
     * @var UrlRewrite
     */
    public $_urlRewrite;

    /**
     * @var MessageManager
     */
    public $_messageManager;

    /**
     * @param HelperData        $helper
     * @param MarketplaceHelper $marketplaceHelper
     * @param RedirectFactory   $resultRedirectFactory
     * @param FormKeyValidator  $formKeyValidator
     * @param Seller            $seller
     * @param UrlRewrite        $urlRewrite
     * @param MessageManager    $messageManager
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        RedirectFactory $resultRedirectFactory,
        FormKeyValidator $formKeyValidator,
        Seller $seller,
        UrlRewrite $urlRewrite,
        MessageManager $messageManager
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_seller = $seller;
        $this->_urlRewrite = $urlRewrite;
        $this->_messageManager = $messageManager;
    }

    /**
     * Around Execute
     *
     * @param \Webkul\Marketplace\Controller\Account\RewriteUrlPost $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundExecute(
        \Webkul\Marketplace\Controller\Account\RewriteUrlPost $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $proceed();
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        if ($block->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($block->getRequest())) {
                    return $this->_resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $block->getRequest()->isSecure()]
                    );
                }
                $fields = $block->getRequest()->getParams();
                $collection = $this->_seller
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                foreach ($collection as $value) {
                    $profileurl = $value->getShopUrl();
                }

                $getCurrentStoreId = $this->_marketplaceHelper->getCurrentStoreId();

                if ($fields['profile_request_url']) {
                    $sourceUrl = 'marketplace/seller/profile/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = '';
                    $profileRequestUrl = '';
                    $urlCollectionData = $this->_urlRewrite
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $profileRequestUrl = $value->getRequestPath();
                    }
                    if ($profileRequestUrl != $fields['profile_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_urlRewrite
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['profile_request_url'])
                        ->save();
                    }
                }
                if ($fields['collection_request_url']) {
                    $sourceUrl = 'marketplace/seller/collection/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = '';
                    $collectionRequestUrl = '';
                    $urlCollectionData = $this->_urlRewrite
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $collectionRequestUrl = $value->getRequestPath();
                    }
                    if ($collectionRequestUrl != $fields['collection_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_urlRewrite
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['collection_request_url'])
                        ->save();
                    }
                }
                if ($fields['review_request_url']) {
                    $sourceUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = '';
                    $reviewRequestUrl = '';
                    $urlCollectionData = $this->_urlRewrite
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $reviewRequestUrl = $value->getRequestPath();
                    }
                    if ($reviewRequestUrl != $fields['review_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_urlRewrite
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['review_request_url'])
                        ->save();
                    }
                }
                if ($fields['location_request_url']) {
                    $sourceUrl = 'marketplace/seller/location/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = '';
                    $locationRequestUrl = '';
                    $urlCollectionData = $this->_urlRewrite
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $locationRequestUrl = $value->getRequestPath();
                    }
                    if ($locationRequestUrl != $fields['location_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_urlRewrite
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['location_request_url'])
                        ->save();
                    }
                }
                $this->_messageManager->addSuccess(__('The URL Rewrite has been saved.'));

                return $this->_resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $block->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());

                return $this->_resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $block->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->_resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $block->getRequest()->isSecure()]
            );
        }
    }
}
