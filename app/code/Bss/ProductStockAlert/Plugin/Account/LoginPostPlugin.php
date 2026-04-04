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

namespace Bss\ProductStockAlert\Plugin\Account;

use Bss\ProductStockAlert\Helper\Data;
use Bss\ProductStockAlert\Model\StockNotifyCookie;
use Magento\Customer\Controller\Account\LoginPost;

class LoginPostPlugin
{
    /**
     * @var StockNotifyCookie
     */
    protected $stockNotifyCookie;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Construct.
     *
     * @param StockNotifyCookie $stockNotifyCookie
     * @param Data $helper
     */
    public function __construct(
        StockNotifyCookie $stockNotifyCookie,
        Data $helper
    ) {
        $this->stockNotifyCookie = $stockNotifyCookie;
        $this->helper = $helper;
    }

    /**
     * Change redirect after login to home instead of dashboard.
     *
     * @param LoginPost $subject
     * @param mixed $result
     * @return mixed
     * @throws \Exception
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result
    ) {
        if ($this->helper->isStockAlertAllowed()) {
            $this->stockNotifyCookie->deleteAll();

            //add btn cookie all product enable notify of customer
            $productNotify = $this->stockNotifyCookie->getAllStock();
            foreach ($productNotify as $product) {
                $this->stockNotifyCookie->setBtnCookie($product['product_id'], $product['parent_id']);
            }
        }

        return $result;
    }
}
