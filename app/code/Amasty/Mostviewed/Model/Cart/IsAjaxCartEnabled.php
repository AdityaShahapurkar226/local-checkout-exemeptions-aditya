<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Cart;

use Amasty\Mostviewed\Model\ConfigProvider;
use Magento\Framework\Module\Manager as ModuleManager;

class IsAjaxCartEnabled
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ModuleManager $moduleManager,
        ConfigProvider $configProvider
    ) {
        $this->moduleManager = $moduleManager;
        $this->configProvider = $configProvider;
    }

    public function execute(string $action): bool
    {
        return $this->moduleManager->isEnabled('Amasty_Cart')
            && (!$this->isProductPage($action) || $this->configProvider->isCartEnabledOnProductPage());
    }

    private function isProductPage(string $action): bool
    {
        return $action === 'catalog_product_view';
    }
}
