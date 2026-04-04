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
namespace Bss\ProductStockAlert\Block\Catalog\Product\View\Type;

class Bundle extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Bundle constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Bss\ProductStockAlert\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string
     */
    public function getFormDataActionUrl()
    {
        return $this->_urlBuilder->getUrl(
            'productstockalert/ajax/formData',
            ['product_id' => $this->getProduct()->getId()]
        );
    }

    /**
     * @return array|string
     */
    public function getProductType()
    {
        return $this->getProduct()->getTypeId();
    }

    /**
     * @return string
     */
    public function getActionController()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product|boolean
     */
    protected function getProduct()
    {
        $product = $this->coreRegistry->registry('current_product');
        if ($product && $product->getId()) {
            return $product;
        }
        return false;
    }
}
