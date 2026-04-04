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
namespace Bss\ProductStockAlert\Block\Product\View\Type;

use Bss\ProductStockAlert\Model\Attribute\Source\Order;

class Grouped extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped
{
    /**
     * Helper instance
     *
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * Grouped constructor.
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }

    /**
     * Retrieve form action
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->helper->getAjaxUrl();
    }

    /**
     * @return bool
     */
    public function checkCustomer()
    {
        return $this->helper->checkCustomer();
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->helper->getCustomerEmail();
    }

    /**
     * @param string $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasEmail($productId)
    {
        return $this->helper->hasEmail($productId);
    }

    /**
     * @param string $product_id
     * @return string
     */
    public function getPostAction($product_id)
    {
        return $this->helper->getPostAction($product_id);
    }

    /**
     * @return bool
     */
    public function isStockAlertAllowed()
    {
        return $this->helper->isStockAlertAllowed() && !($this->getProduct()->getProductStockAlert() == Order::DISABLE);
    }
}
