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

namespace Bss\ProductStockAlert\Observer\Frontend;

use Bss\ProductStockAlert\Helper\Data;
use Bss\ProductStockAlert\Model\StockNotifyCookie;
use Magento\Framework\Event\ObserverInterface;

class CustomerLogOutObserver implements ObserverInterface
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
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isStockAlertAllowed()) {
            $this->stockNotifyCookie->deleteAll();
        }
    }
}
