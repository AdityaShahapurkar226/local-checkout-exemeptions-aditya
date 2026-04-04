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
declare(strict_types=1);

namespace Bss\ProductStockAlert\Model;

use Bss\ProductStockAlert\Helper\Data;
use Bss\ProductStockAlert\Helper\ProductData;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;

class StockBtn
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
     * @var ProductData
     */
    protected $productDataBss;

    /**
     * Constructor.
     *
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param ProductData $productDataBss
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\ProductStockAlert\Helper\Data $helper,
        ProductData $productDataBss
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->productDataBss = $productDataBss;
    }

    /**
     * Get btn cancel.
     *
     * @param int $productId
     * @param int|null $parentProductId
     * @return string
     */
    public function getBtnAfterAddNotify($productId, $parentProductId)
    {
        $btnText = $this->helper->getStopButtonText();
        $btnUrl = $this->helper->getCancelPostAction($productId, $parentProductId);
        $btnTextColor = $this->helper->getButtonTextColor();
        $btnColor = $this->helper->getButtonColor();

        return '<a class="action primary btn-stock-cookie notification_me' . $productId . '" href="' . $btnUrl . '"
             title="' . $btnText . '" style="background-color: ' . $btnColor . '">
            <span style="color: ' . $btnTextColor . '">' . $btnText . '</span>
            </a>';
    }
}
