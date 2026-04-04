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
namespace Bss\ProductStockAlert\Plugin\Block\Product\View\Type;

use Magento\Framework\App\RequestInterface;

class Configurable
{
    /**
     * @var \Bss\ProductStockAlert\Helper\ProductData
     */
    private $linkData;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Bss\ProductStockAlert\Helper\ProductData $linkData
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Bss\ProductStockAlert\Helper\ProductData $linkData,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        RequestInterface $request
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->linkData = $linkData;
        $this->helper = $helper;
        $this->catalogProduct = $catalogProduct;
        $this->request = $request;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfig($subject, $result)
    {
        if ($this->helper->isStockAlertAllowed() && $this->helper->checkCustomer()) {
            $childProduct = $this->linkData->getAllData($subject);
            $config = $this->jsonDecoder->decode($result);
            $config["productStockAlert"] = $childProduct;
            $config["productStockAlert"]["buttonDesign"] = [
                "btnText" => $this->helper->getButtonText(),
                "btnTextColor" => $this->helper->getButtonTextColor(),
                "btnColor" => $this->helper->getButtonColor()
            ];
            $config["productStockAlert"]["controllerActionName"] = $this->request->getFullActionName();
            return $this->jsonEncoder->encode($config);
        }
        return $result;
    }
}
