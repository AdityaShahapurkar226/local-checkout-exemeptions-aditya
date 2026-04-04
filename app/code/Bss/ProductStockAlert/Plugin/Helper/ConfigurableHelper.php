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
 * @category  BSS
 * @package   Bss_ProductStockAlert
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Plugin\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data;

class ConfigurableHelper
{
    protected $helper;

    /**
     * ConfigurableHelper constructor.
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Data $configurableHelper
     * @param $options
     * @param $currentProduct
     * @param $allowedProducts
     * @return array
     */
    public function afterGetOptions(
        Data $configurableHelper,
        $options,
        $currentProduct,
        $allowedProducts
    ) {
        $allowAttributes = $configurableHelper->getAllowAttributes($currentProduct);
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if (!$product->isSalable() &&
                    $this->isStockAlertAllowed($product)) {
                    // If options are not available
                    // Or options are available but not in array
                    // Then add it to array
                    if (!isset($options[$productAttributeId][$attributeValue]) ||
                        (isset($options[$productAttributeId][$attributeValue]) &&
                            !in_array($productId, $options[$productAttributeId][$attributeValue]))) {
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                }
            }
        }
        return $options;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isStockAlertAllowed($product)
    {
        return $this->helper->isStockAlertAllowed() &&
            ($product->getProductStockAlert() != \Bss\ProductStockAlert\Model\Attribute\Source\Order::DISABLE) &&
            $this->helper->checkCustomer();
    }
}
