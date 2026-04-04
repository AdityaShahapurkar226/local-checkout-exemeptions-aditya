<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Adminhtml\Product;

class InlineEdit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
        $this->_stockRegistry = $stockRegistry;
    }

    /**
     * Save grid inline changes
     *
     * @return $this
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (count($postItems)) {
            
            foreach ($postItems as $item) {
                
                try {
                    if ($item['mageproduct_id']) {
                        $product = $this->productRepository->getById($item['mageproduct_id']);
                        $stockItem = $this->_stockRegistry->getStockItem($item['mageproduct_id']);
                        $stockItem->setData('qty', $item['qty']);
                        if (isset($item['status'])) {
                            $product->setStatus($item['status']);
                        }
                        if (isset($item['product_price'])) {
                            $product->setPrice($item['product_price']);
                        }
                        
                        $product->save();
                    }
                } catch (\Exception $e) {
                    $messages[] = "[Error:]  {$e->getMessage()}";
                    $error = true;
                }
            }
            
        } else {
            $messages[] = __('Please correct the data sent.');
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
