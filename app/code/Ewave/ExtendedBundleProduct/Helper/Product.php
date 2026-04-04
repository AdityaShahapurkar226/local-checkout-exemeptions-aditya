<?php
namespace Ewave\ExtendedBundleProduct\Helper;

use Ewave\ExtendedBundleProduct\Api\SelectionRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Product extends AbstractHelper
{
    /**
     * @var SelectionRepositoryInterface
     */
    protected $selectionRepository;

    /**
     * @param Context $context
     * @param SelectionRepositoryInterface $selectionRepository
     */
    public function __construct(
        Context $context,
        SelectionRepositoryInterface $selectionRepository
    ) {
        parent::__construct($context);
        $this->selectionRepository = $selectionRepository;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getSelectionConfigurableOptions(\Magento\Catalog\Model\Product $product)
    {
        return $this->selectionRepository->getSelectionConfigurableOptions($product);
    }
}
