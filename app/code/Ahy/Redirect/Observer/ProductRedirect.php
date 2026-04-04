<?php

namespace Ahy\Redirect\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class ProductRedirect implements ObserverInterface
{
    protected RedirectInterface $redirect;
    protected ProductRepositoryInterface $productRepository;
    protected ActionFlag $actionFlag;
    protected RequestInterface $request;
    protected ResultFactory $resultFactory;
    protected LoggerInterface $logger;

    public function __construct(
        RedirectInterface $redirect,
        ProductRepositoryInterface $productRepository,
        ActionFlag $actionFlag,
        RequestInterface $request,
        ResultFactory $resultFactory,
        LoggerInterface $logger
    ) {
        $this->redirect = $redirect;
        $this->productRepository = $productRepository;
        $this->actionFlag = $actionFlag;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        // Get the product ID from the request
        $productId = (int) $this->request->getParam('id');

        try {
            // Load the product by ID
            $product = $this->productRepository->getById($productId);
            $sku = strtolower(string: $product->getSku());
            // Check if SKU starts with 'sms-'
            if (strpos(haystack: $sku, needle: 'sms-') === 0) {
                $this->logger->info("Redirecting product with SKU {$product->getSku()} to homepage.");

                // Prevent further dispatch
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);

                // Redirect to homepage
                $observer->getControllerAction()->getResponse()->setRedirect('/', 301);
            }
        } catch (\Exception $e) {
            // Log the exception with an error message
            $this->logger->error("Error while trying to redirect product with ID {$productId}: " . $e->getMessage());
        }
    }
}
