<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MpAssignProduct\Plugin\Catalog;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\MassDelete
 */
class MassDelete extends \Magento\Catalog\Controller\Adminhtml\Product\MassDelete
{
    /**
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var \Webkul\MpAssignProduct\Helper\Data */
    private $helper;

    /**
     * Initialization
     *
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        \Webkul\MpAssignProduct\Helper\Data $helper
    ) {
        $this->_assignHelper = $helper;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository ?:
            ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        $this->logger = $logger ?:
            ObjectManager::getInstance()->create(LoggerInterface::class);
        parent::__construct($context, $productBuilder, $filter, $collectionFactory, $productRepository, $logger);
    }

    /**
     * Mass Delete Action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collection->addMediaGalleryData();

        $productDeleted = 0;
        $productDeletedError = 0;
       
        foreach ($collection->getItems() as $key => $product) {
        
            $assignToSeller = $this->_assignHelper->assignToSeller();
            $productId = $product->getId();
            
            if ($assignToSeller || ($this->_assignHelper->hasAssignedProducts($productId)
            && $this->_assignHelper->checkIsAssignProduct($productId))) {
                $sortingInfo = $this->_assignHelper->getSortingOrderInfo();
                $sortBy = $sortingInfo['sort_by'];
                $orderType = $sortingInfo['order_type'];
                $assignProducts = $this->_assignHelper->getTotalProducts($productId, 1, $sortBy, $orderType);
                
                if (count($assignProducts) > 0) {
                    foreach ($assignProducts as $key => $assignproduct) {
                        $proId = $assignproduct['id'];
                        break;
                    }
                    if ($proId) {
                        $associatedPro = $this->_assignHelper->getAssignedAssociatedProduct($productId, $proId);
                        foreach ($associatedPro as $associatedProduct) {
                            $collection->removeItemByKey($associatedProduct->getProductId());
                        }
                    }
                }
                
            }
            
        }
       
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            try {
                if ($product) {
                    $assignToSeller = $this->_assignHelper->assignToSeller();
                    $productId = $product->getId();
                   
                    if (!$assignToSeller || ( !$this->_assignHelper->hasAssignedProducts($productId)
                    && !$this->_assignHelper->checkIsAssignProduct($productId))) {
                        $this->productRepository->delete($product);
                        $productDeleted++;
                    } else {
                        $this->_assignHelper->assignSellerToAdminProduct($productId);
                        $productDeleted++;
                    }
                }
                 
            } catch (LocalizedException $exception) {
                $this->_assignHelper->logDataInLogger($exception->getLogMessage());
                $productDeletedError++;
            }
        }

        if ($productDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $productDeleted)
            );
        }

        if ($productDeletedError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been deleted. Please see server logs for more details.',
                    $productDeletedError
                )
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }
}
