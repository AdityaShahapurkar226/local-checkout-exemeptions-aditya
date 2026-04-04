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
namespace Bss\ProductStockAlert\Block\Product\View\Type\Bundle\Option;

use Bss\ProductStockAlert\Model\Attribute\Source\Order;

class Radio extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio
{
    /**
     * @var string
     */
    protected $_template = 'Bss_ProductStockAlert::product/view/type/bundle/option/radio.phtml';

    /**
     * Helper instance
     *
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * Radio constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Bss\ProductStockAlert\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $jsonEncoder,
            $catalogData,
            $registry,
            $string,
            $mathRandom,
            $cartHelper,
            $taxData,
            $pricingHelper,
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
