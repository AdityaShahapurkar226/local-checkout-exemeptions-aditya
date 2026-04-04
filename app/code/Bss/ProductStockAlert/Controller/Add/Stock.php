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
namespace Bss\ProductStockAlert\Controller\Add;

use Bss\ProductStockAlert\Controller\Add as AddController;
use Bss\ProductStockAlert\Model\StockFactory;
use Bss\ProductStockAlert\Model\StockNotifyCookie;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Stock extends AddController implements HttpPostActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\ProductStockAlert\Model\StockFactory
     */
    protected $modelStockFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var StockNotifyCookie
     */
    protected $stockNotifyCookie;

    /**
     * Construct.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param StockFactory $modelStockFactory
     * @param StoreManagerInterface $store
     * @param Customer $customer
     * @param ProductRepositoryInterface $productRepository
     * @param StockNotifyCookie $stockNotifyCookie
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Bss\ProductStockAlert\Model\StockFactory $modelStockFactory,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Customer\Model\Customer $customer,
        ProductRepositoryInterface $productRepository,
        \Bss\ProductStockAlert\Model\StockNotifyCookie $stockNotifyCookie
    ) {
        $this->productRepository = $productRepository;
        $this->modelStockFactory = $modelStockFactory;
        $this->store = $store;
        $this->customer = $customer;
        $this->stockNotifyCookie = $stockNotifyCookie;
        parent::__construct($context, $customerSession);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);
        $backProductUrl = $backUrl;
        $productId = (int)$this->getRequest()->getParam('product_id');
        $customerEmail = $this->getRequest()->getParam('stockalert_email');
        $parentIdParam = (int)$this->getRequest()->getParam('parent_id');
        $parentId = $this->getParentId($parentIdParam, $productId);

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->checkResultRedirect($backUrl, $productId, $customerEmail)) {
            $this->messageManager->addErrorMessage(__('Invalid value input. Please try again.'));
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
            return $resultRedirect;
        }

        try {
            $product = $this->productRepository->getById($productId);
            $customerId = $this->customerSession->getCustomerId() ? $this->customerSession->getCustomerId() : 0;
            $customerData = $this->customer->load($customerId);
            $customerName = $customerId ? $customerData->getFirstname() . " " . $customerData->getLastname() : "Guest";

            $backProductUrl = $product->getUrlInStore();
            if ($parentId) {
                $parent = $this->productRepository->getById($parentId);
                $backProductUrl = $parent->getUrlInStore();
            }

            if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $this->messageManager->addErrorMessage(__('Please correct this email address: %1', $customerEmail));
                $resultRedirect->setUrl($backProductUrl);
                return $resultRedirect;
            }

            $model = $this->modelStockFactory->create()
                ->setCustomerId($customerId)
                ->setCustomerEmail($customerEmail)
                ->setCustomerName($customerName)
                ->setProductSku($product->getSku())
                ->setProductId($product->getId())
                ->setWebsiteId(
                    $this->store->getStore()->getWebsiteId()
                )
                ->setStoreId(
                    $this->store->getStore()->getId()
                )
                ->setParentId($parentId);
            $model->save();
            //set Notify
            $this->setNotifySubscription($customerId, $customerEmail, $product);

            //Add btn cookie after add notify.
            $this->stockNotifyCookie->setBtnCookie($product->getId(), $parentId);

            $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addErrorMessage(__('There are not enough parameters.'));
        } catch (AlreadyExistsException $alreadyExistsException) {
            $this->messageManager->addErrorMessage(__('This email has been subscribed.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the alert subscription right now.'));
        }
        $resultRedirect->setUrl($backProductUrl);
        return $resultRedirect;
    }

    /**
     * @param int $parentIdParam
     * @param int $productId
     * @return int|null
     */
    private function getParentId($parentIdParam, $productId)
    {
        if ($parentIdParam && $parentIdParam != $productId) {
            return $parentIdParam;
        }
        return null;
    }

    /**
     * @param string $customerId
     * @param string $customerEmail
     * @param Product $product
     * @return $this
     * @throws NoSuchEntityException
     */
    private function setNotifySubscription($customerId, $customerEmail, $product)
    {
        if ($customerId == 0) {
            $notify = $this->customerSession->getNotifySubscription();
            if ($notify && !empty($notify)) {
                $notify[$product->getId()] = [
                    "email" => $customerEmail,
                    "website" => $this->store->getStore()->getWebsiteId()
                ];
            } else {
                $notify = [];
                $notify[$product->getId()] = [
                    "email" => $customerEmail,
                    "website" => $this->store->getStore()->getWebsiteId()
                ];
            }
            $this->customerSession->setNotifySubscription($notify);
        }
        return $this;
    }

    /**
     * @param string $backUrl
     * @param string $productId
     * @param string $customerEmail
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     */
    private function checkResultRedirect($backUrl, $productId, $customerEmail)
    {
        return !$backUrl || !$productId || !$customerEmail;
    }
}
