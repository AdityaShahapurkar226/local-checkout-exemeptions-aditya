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
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Plugin\Model\Product\Type;

class Configurable
{

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * Configurable constructor.
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUsedProductCollection($subject, $result, $product)
    {
        if ($this->helper->isStockAlertAllowed() && $this->helper->checkCustomer()) {
            $result->setFlag('has_stock_status_filter', true);
        }
        return $result;
    }
}
