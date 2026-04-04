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
 * @copyright Copyright (c) 2016-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Plugin\Pricing\Render;

class FinalPriceBox
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * CategoryCheck constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\ProductStockAlert\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->helper=$helper;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param mixed $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterToHtml($subject, $result)
    {
        $product = $subject->getSaleableItem();
        $btnText = $this->helper->getButtonText();
        $btnTextColor = $this->helper->getButtonTextColor();
        $btnColor = $this->helper->getButtonColor();
        if ($this->doRenderStock($product)) {
            $button = '<div class="notify_me_bss">
            <a data-product-id-bss="' . $product->getId() . '" class="action primary notification_me' . $product->getId() . '" href="' . $product->getProductUrl() . '"
             title="' . $btnText . '" style="background-color: ' . $btnColor . '">
            <span style="color: ' . $btnTextColor . '">' . $btnText . '</span>
            </a>
            </div>
            <script type="text/x-magento-init">
                {
                    ".notification_me' . $product->getId() . '": {
                        "Bss_ProductStockAlert/js/stock-notice-config": {}
                    }
                }
            </script>';
            return $result . $button;
        }
        return $result;
    }

    /**
     * If render stock notify input
     * && Compatible with PreOrder
     *
     * @param $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function doRenderStock($product)
    {
        $page = $this->request->getFullActionName();
        $isInStock = $product->isAvailable();
        if (!$this->helper->isEnabledPreOrder()) {
            return $this->isSingleStockNotice(
                $product->getProductStockAlert(),
                $isInStock,
                $page
            );
        }

        $store = $this->storeManager->getStore()->getId();
        $preOrder = $product->getResource()->getAttributeRawValue($product->getId(), 'preorder', $store);
        return $this->isWorkingPreOrder(
            $product->getProductStockAlert(),
            $preOrder,
            $isInStock,
            $page
        );
    }

    /**
     * @param $isEnableStockNotice
     * @param $preOrder
     * @param $isInStock
     * @param $page
     * @return bool
     */
    private function isWorkingPreOrder(
        $isEnableStockNotice,
        $preOrder,
        $isInStock,
        $page
    ) {
        return $this->helper->isStockAlertAllowed() &&
            ($isEnableStockNotice == 1) &&
            !(($preOrder == 1 || ($preOrder == 2 && !$isInStock))) &&
            $page != "catalog_product_view";
    }

    /**
     * @param $isEnableStockNotice
     * @param $isInStock
     * @param $page
     * @return bool
     */
    private function isSingleStockNotice(
        $isEnableStockNotice,
        $isInStock,
        $page
    ) {
        return $this->helper->isStockAlertAllowed() &&
            ($isEnableStockNotice == 1 || !$isEnableStockNotice) &&
            !$isInStock &&
            $page != "catalog_product_view" &&
            $page != "wishlist_index_configure";
    }
}
