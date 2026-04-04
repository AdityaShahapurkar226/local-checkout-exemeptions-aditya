<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Plugin\Catalog\Helper\Product;

use Magento\Framework\View\Result\Page as ResultPage;

class View
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;

    /**
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Plugin for prepareAndRender
     *
     * @param \Magento\Catalog\Helper\Product\View $subject
     * @param ResultPage $resultPage
     * @param $productId
     * @param $controller
     * @param $params
     */
    public function beforePrepareAndRender(
        \Magento\Catalog\Helper\Product\View $subject,
        ResultPage $resultPage,
        $productId,
        $controller,
        $params = null
    ) {
        $newproductId = $this->helper->getMinimumPriceProducts($productId);
        if ($newproductId) {
            $productId = $newproductId;
        }
        return [$resultPage, $productId, $controller, $params];
    }
}
