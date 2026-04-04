<?php

declare(strict_types = 1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Helper;

use Amasty\Mostviewed\Model\ConfigProvider;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\DesignInterface;

class Config extends AbstractHelper
{
    public const MODULE_PATH = 'ammostviewed/';

    public const DEFAULT_GATHERED_PERIOD = 30;

    public const BUNDLE_PAGE_PATH = 'ammostviewed/bundle_packs/cms_page';

    public const IGNORE_ANCHOR_CATEGORIES = 'general/ignore_anchor_categories';

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Amasty\Mostviewed\Model\Rule\Condition\CombineFactory
     */
    private $combineFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Rule\Condition\SameAsCombineFactory
     */
    private $sameAsCombineFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Indexer\RuleProcessor
     */
    private $ruleProcessor;

    /**
     * inject objects for prevent fatal on cloud
     */
    public function __construct(
        \Amasty\Mostviewed\Model\Rule\Condition\CombineFactory $combineFactory,
        \Amasty\Mostviewed\Model\Rule\Condition\SameAsCombineFactory $sameAsCombineFactory,
        \Amasty\Mostviewed\Model\Indexer\RuleProcessor $ruleProcessor,
        \Magento\Framework\Filter\FilterManager $filterManager,
        Context $context
    ) {
        parent::__construct($context);
        $this->filterManager = $filterManager;
        $this->combineFactory = $combineFactory;
        $this->sameAsCombineFactory = $sameAsCombineFactory;
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_PATH . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool|null
     */
    public function isIgnoreAnchorCategories(): bool
    {
        return (bool)$this->getModuleConfig(self::IGNORE_ANCHOR_CATEGORIES);
    }

    /**
     * @deprecated
     * @see ConfigProvider::getGatheredPeriod
     */
    public function getGatheredPeriod(?int $storeId = null): int
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getGatheredPeriod($storeId);
    }

    /**
     * @deprecated
     * @see ConfigProvider::getOrderStatus
     */
    public function getOrderStatus(?int $storeId = null): array
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getOrderStatus($storeId);
    }

    /**
     * @return bool
     */
    public function isBlockInCartEnabled()
    {
        return (bool)$this->getModuleConfig('bundle_packs/display_cart_block');
    }

    /**
     * @return int
     */
    public function isTopMenuEnabled()
    {
        return $this->getModuleConfig('bundle_packs/top_menu_enabled');
    }

    /**
     * @return string
     */
    public function getBlockPosition()
    {
        return $this->getModuleConfig('bundle_packs/position');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->filterManager->stripTags(
            $this->getModuleConfig('bundle_packs/tab_title'),
            [
                'allowableTags' => null,
                'escape' => true
            ]
        );
    }

    /**
     * @param null|int $storeId
     *
     * @return int
     */
    public function getThemeForStore($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
