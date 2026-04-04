<?php

namespace Ahy\ThemeCustomization\Block\Widget;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Widget\Block\BlockInterface;


class MoreItemsToExploreProductCard extends AbstractProduct implements BlockInterface
{
    protected $_template = 'Ahy_ThemeCustomization::More_Items_To_Explore_Product_Card.phtml';

    protected $productRepository;

    public function __construct(
        ProductContext $context,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
    }

    public function getProduct()
    {
        // $productId = $this->getData('id');
        // return $this->productRepository->getById($productId);
        return 'hello from block';
    }
    
}
?>