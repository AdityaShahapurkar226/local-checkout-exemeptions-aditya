<?php
// File: app/code/Ahy/BuyBox/Plugin/View.php

namespace Ahy\BuyBox\Plugin\Catalog\Helper\Product;

use Magento\Framework\View\Result\Page as ResultPage;
use Ahy\BuyBox\Helper\Data;

class View
{
    /**
     * @var Ahy\BuyBox\Helper\Data
     */
    protected $helper;

    /**
     * @param Ahy\BuyBox\Helper\Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * Plugin for prepareAndRender
     *
     * @param \Magento\Catalog\Helper\Product\View $subject
     * @param ResultPage $resultPage
     * @param int $productId
     * @param mixed $controller
     * @param array $params
     */
    public function beforePrepareAndRender(
        \Magento\Catalog\Helper\Product\View $subject,
        ResultPage $resultPage,
        $productId,
        $controller,
        $params = null
    ) {
        $newProductId = $this->helper->ahyBuyBox($productId);
        if ($newProductId) {
            $productId = $newProductId;
        }
        return [$resultPage, $productId, $controller, $params];
    }
}
