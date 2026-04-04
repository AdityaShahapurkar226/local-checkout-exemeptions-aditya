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
namespace Bss\ProductStockAlert\Ui\Component\MassAction;

use Bss\ProductStockAlert\Helper\Data as StockAlertHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Control\Action;

class NotifyStock extends Action
{
    /**
     * @var StockAlertHelper
     */
    protected $stockAlertHelper;

    /**
     * NotifyStock constructor.
     *
     * @param ContextInterface $context
     * @param StockAlertHelper $stockAlertHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StockAlertHelper $stockAlertHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $components,
            $data
        );
        $this->stockAlertHelper = $stockAlertHelper;
    }

    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        $config['url'] = $this->stockAlertHelper->getCronUrl();
        $this->setData('config', (array)$config);
        parent::prepare();
    }
}
