<?php
namespace Ewave\ExtendedBundleProduct\Controller\Adminhtml\ExtendedBundleProduct;

use Ewave\ExtendedBundleProduct\Api\SelectionRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Controller\ResultFactory;

class ConfigurableOptions extends Action
{
    /**
     * @var SelectionRepositoryInterface
     */
    protected $selectionRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ConfigurableOptions constructor.
     * @param Action\Context $context
     * @param SelectionRepositoryInterface $selectionRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Action\Context $context,
        SelectionRepositoryInterface $selectionRepository,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->selectionRepository = $selectionRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultData = [];
        $selectionId = (int)$this->getRequest()->getParam('selectionId');
        $productId = (int)$this->getRequest()->getParam('productId');

        if ($productId) {
            /** @var ProductInterface|Product $product */
            $product = $this->productRepository->getById($productId);
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                if ($selectionId) {
                    $selectedOptions = $this->selectionRepository->getConfigurableOptions($selectionId);
                } else {
                    $selectedOptions = [];
                }
                $resultData = $this->selectionRepository->getSelectionConfigurableOptions(
                    $product,
                    $selectedOptions
                );
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($resultData);

        return $resultJson;
    }
}
