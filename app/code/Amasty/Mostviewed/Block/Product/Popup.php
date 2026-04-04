<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Product;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    private $products = [];

    /**
     * @var bool
     */
    private $hasRequiredOptions;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Mostviewed\Model\Pack\Cart\ProductRegistry
     */
    private $productRegistry;

    /**
     * @var int
     */
    private $isAjaxCartEnabled;

    /**
     * @var int
     */
    private $packId;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Mostviewed\Model\Pack\Cart\ProductRegistry $productRegistry,
        bool $isAjaxCartEnabled = false,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->productRegistry = $productRegistry;
        $this->isAjaxCartEnabled = (int) $isAjaxCartEnabled;
    }

    public function _construct()
    {
        $this->setTemplate('Amasty_Mostviewed::bundle/popup.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param array $products
     *
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    public function setHasRequiredOptions(bool $hasRequiredOptions): void
    {
        $this->hasRequiredOptions = $hasRequiredOptions;
    }

    /**
     * @return Phrase[]
     */
    public function getHeaders(): array
    {
        if ($this->hasRequiredOptions) {
            $result = [
                __('Some products of the bundle pack were not added to cart.'),
                __('Please choose the options to add them.')
            ];
        } else {
            $result = [
                __('The product has additional configurations you may be interested in.'),
                __('Please choose the options to add them.')
            ];
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode(
            [
                'url' => $this->getUrl('ammostviewed/cart/add'),
                'productsInCart' => $this->productRegistry->getProducts(),
                'isAjaxCartEnabled' => $this->isAjaxCartEnabled,
                'packId' => $this->getPackId()
            ]
        );
    }

    public function setPackId(int $packId): void
    {
        $this->packId = $packId;
    }

    public function getPackId(): int
    {
        return $this->packId;
    }
}
