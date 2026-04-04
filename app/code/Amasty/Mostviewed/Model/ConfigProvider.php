<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\Mostviewed\Model\OptionSource\DisplayOptions;

class ConfigProvider extends ConfigProviderAbstract
{
    public const DISPLAY_OPTIONS_PATH = 'bundle_packs/display_options';
    public const CONFIRMATION_TITLE_PATH = 'bundle_packs/confirmation_title';
    public const ANALYTIC_ORDER_STATUS_PATH = 'bundle_packs/analytics/order_status';
    public const ANALYTIC_PERIOD_PATH = 'bundle_packs/analytics/period';
    public const ANALYTIC_BOUGHT_ORDER_STATUS_PATH = 'bundle_packs/analytics/order_status_bought';
    public const APPLY_SEPARATELY = 'bundle_packs/apply_for_separately';
    public const APPLY_CART_RULE = 'bundle_packs/apply_cart_rule';
    public const DISPLAY_CART_MESSAGE = 'bundle_packs/display_cart_message';
    public const GATHERED_PERIOD_PATH = 'general/period';
    public const ORDER_STATUS_PATH = 'general/order_status';

    public const DEFAULT_GATHERED_PERIOD = 30;

    /**
     * @var string
     */
    protected $pathPrefix = 'ammostviewed/';

    public function isShowAllOptions(): bool
    {
        return $this->getValue(self::DISPLAY_OPTIONS_PATH) == DisplayOptions::ALL_OPTIONS;
    }

    public function getConfirmationTitle(): string
    {
        return $this->getValue(self::CONFIRMATION_TITLE_PATH);
    }

    public function getPackAnalyticOrderStatuses(): array
    {
        return $this->getValue(self::ANALYTIC_ORDER_STATUS_PATH)
            ? explode(',', $this->getValue(self::ANALYTIC_ORDER_STATUS_PATH))
            : [];
    }

    public function getPackAnalyticPeriod(): int
    {
        return (int) $this->getValue(self::ANALYTIC_PERIOD_PATH);
    }

    public function getPackAnalyticBoughtOrderStatuses(): array
    {
        return $this->getValue(self::ANALYTIC_BOUGHT_ORDER_STATUS_PATH)
            ? explode(',', $this->getValue(self::ANALYTIC_BOUGHT_ORDER_STATUS_PATH))
            : [];
    }

    /**
     * Check is Ajax Cart module Product Page config.
     * @return bool
     */
    public function isCartEnabledOnProductPage(): bool
    {
        return $this->scopeConfig->isSetFlag('amasty_cart/confirm_popup/use_on_product_page');
    }

    /**
     * Ajax Cart module: Image Display for Configurable Products config.
     * @return bool
     */
    public function isChildImageForConfigurable(): bool
    {
        return $this->scopeConfig->isSetFlag('amasty_cart/confirm_display/configurable_image');
    }

    public function isProductsCanBeAddedSeparately(): bool
    {
        return $this->isSetFlag(self::APPLY_SEPARATELY);
    }

    /**
     * In case true, for item must be applied max discount (cart price rule discount or bundle pack discount).
     * @return bool
     */
    public function isApplyCartRule(): bool
    {
        return $this->isSetFlag(self::APPLY_CART_RULE);
    }

    public function isMessageInCartEnabled(): bool
    {
        return $this->isSetFlag(self::DISPLAY_CART_MESSAGE);
    }

    public function getGatheredPeriod(?int $storeId = null): int
    {
        $period = $this->getValue(self::GATHERED_PERIOD_PATH, $storeId);
        if (!$period) {
            $period = self::DEFAULT_GATHERED_PERIOD;
        }

        return (int) $period;
    }

    public function getOrderStatus(?int $storeId = null): array
    {
        $value = $this->getValue(self::ORDER_STATUS_PATH, $storeId);
        return $value ? explode(',', $value) : [];
    }
}
