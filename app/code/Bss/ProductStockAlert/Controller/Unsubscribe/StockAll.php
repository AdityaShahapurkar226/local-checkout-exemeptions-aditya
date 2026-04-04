<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Controller\Unsubscribe;

use Bss\ProductStockAlert\Controller\Unsubscribe as UnsubscribeController;
use Bss\ProductStockAlert\Model\Stock;
use Bss\ProductStockAlert\Model\StockNotifyCookie;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class StockAll extends UnsubscribeController
{
    /**
     * @var \Bss\ProductStockAlert\Model\Stock
     */
    protected $modelStock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store;

    /**
     * @var \Bss\ProductStockAlert\Model\StockNotifyCookie
     */
    protected $stockNotifyCookie;

    /**
     * StockAll constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Stock $modelStock
     * @param StoreManagerInterface $store
     * @param StockNotifyCookie $stockNotifyCookie
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Bss\ProductStockAlert\Model\Stock $modelStock,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Bss\ProductStockAlert\Model\StockNotifyCookie $stockNotifyCookie
    ) {
        $this->modelStock = $modelStock;
        $this->store = $store;
        $this->stockNotifyCookie = $stockNotifyCookie;
        parent::__construct($context, $customerSession);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->modelStock
                ->deleteCustomer(
                    $this->customerSession->getCustomerId(),
                    $this->store
                        ->getStore()
                        ->getWebsiteId()
                );

            //Delete all btn cookie
            $this->stockNotifyCookie->deleteAll();

            $this->messageManager->addSuccessMessage(__('You will no longer receive stock alerts.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update the alert subscription.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_url->getUrl("productstockalert"));
    }
}
