<?php
namespace Ewave\ExtendedBundleProduct\Api;

use Magento\Catalog\Model\Product;

interface SelectionRepositoryInterface
{
    /**
     * @param int $selectionId
     * @return array
     */
    public function getConfigurableOptions($selectionId);

    /**
     * @param Product $product
     * @param array|null $selectedOptions
     * @return array
     */
    public function getSelectionConfigurableOptions(Product $product, $selectedOptions = null);

    /**
     * @param int $selectionId
     * @return mixed
     */
    public function getParentProductIdBySelectionId($selectionId);
}
