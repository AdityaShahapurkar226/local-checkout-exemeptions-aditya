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

namespace Webkul\SellerSubAccount\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;

// use Webkul\Marketplace\Model\SellerFactory;

class Printpdfinfo extends \Webkul\Marketplace\Controller\Order\Printpdfinfo
{
    /**
     * Order Print PDF Header Infomation Save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Data::class
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            if ($this->getRequest()->isPost()) {
                try {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/shipping',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $fields = $this->getRequest()->getParams();
                    
                    $sellerSubAccountHelper = $this->_objectManager->create(
                        \Webkul\SellerSubAccount\Helper\Data::class
                    );
                    $sellerId;
                    $subAccount = $sellerSubAccountHelper->getCurrentSubAccount();
                    if ($subAccount->getId()) {
                        $sellerId = $sellerSubAccountHelper->getSubAccountSellerId();
                    }
                    if (!isset($sellerId)) {
                        $sellerId = $this->_getSession()->getCustomerId();
                    }
                    $storeId = $helper->getCurrentStoreId();

                    $collection = $this->_objectManager->create(
                        \Webkul\Marketplace\Model\Seller::class
                    )->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    )
                    ->addFieldToFilter(
                        'store_id',
                        $storeId
                    );
                    $autoId;
                    foreach ($collection as $sellerFactory) {
                        $autoId = $sellerFactory->getId();
                    }
                    if (isset($autoId)) {
                       
                        $value = $this->_objectManager->create(
                            \Webkul\Marketplace\Model\Seller::class
                        )->load($autoId)
                        ->setOthersInfo($fields['others_info'])
                        ->setStoreId($storeId)->save();
                        $this->messageManager->addSuccess(
                            __('Information was successfully saved')
                        );
                    } else {
                       
                        $collection = $this->_objectManager->create(
                            \Webkul\Marketplace\Model\SellerFactory::class
                        )->create()
                        ->addData([
                            "is_seller" => 1,
                            "seller_id" => $sellerId,
                            "store_id" => $storeId,
                            "others_info" => $fields['others_info']
                            ]);
                            $saveData = $collection->save();
                            $this->messageManager->addSuccess(
                                __('Information was successfully saved')
                            );
                            
                    }
                    $autoId = '';
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/shipping',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/shipping',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/shipping',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
