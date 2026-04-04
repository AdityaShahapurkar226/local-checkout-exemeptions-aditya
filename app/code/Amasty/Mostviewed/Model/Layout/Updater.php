<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Layout;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Block\Widget\Wrapper;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Mostviewed\Model\OptionSource\RuleType;
use Magento\Framework\App\Area;
use Magento\Theme\Model\View\Design;
use Magento\Widget\Model\Layout\Update;
use Magento\Widget\Model\Layout\UpdateFactory;
use Magento\Widget\Model\ResourceModel\Layout\Link\Collection as LinkCollection;
use Magento\Widget\Model\ResourceModel\Layout\Link\CollectionFactory as LinksFactory;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\ThemeFactory;

class Updater
{
    public const CONTENT_TEMPLATE = 'Amasty_Mostviewed::content/grid.phtml';

    public const SIDEBAR_TEMPLATE = 'Amasty_Mostviewed::sidebar/list.phtml';

    public const RELATED_NAME = 'catalog.product.related';

    public const UPSELL_NAME = 'product.info.upsell';

    public const CROSSEL_NAME = 'checkout.cart.crosssell';

    public const TAB_NAME = 'product.info.details';

    /**
     * @var UpdateFactory
     */
    private $layoutUpdateFactory;

    /**
     * @var BlockPosition
     */
    private $positionDefiner;

    /**
     * @var Design
     */
    private $themeConfig;

    /**
     * @var LinksFactory
     */
    private $linksFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ThemeFactory
     */
    private $themeFactory;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme
     */
    private $themeResource;

    public function __construct(
        UpdateFactory $layoutUpdateFactory,
        BlockPosition $positionDefiner,
        Design $themeConfig,
        LinksFactory $linksFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Theme\Model\ResourceModel\Theme $themeResource,
        ThemeFactory $themeFactory
    ) {
        $this->layoutUpdateFactory = $layoutUpdateFactory;
        $this->positionDefiner = $positionDefiner;
        $this->themeConfig = $themeConfig;
        $this->linksFactory = $linksFactory;
        $this->storeManager = $storeManager;
        $this->themeFactory = $themeFactory;
        $this->themeResource = $themeResource;
    }

    /**
     * @param GroupInterface $group
     */
    public function execute($group)
    {
        $position = $group->getBlockPosition();
        $layoutUpdateId = $group->getLayoutUpdateId();
        /** @var Update $layoutUpdate */
        $layoutUpdate = $this->getLayoutUpdate($layoutUpdateId);
        $stores = explode(',', $group->getStores());
        if (in_array(0, $stores)) {
            $allStores = $this->storeManager->getStores();
            $stores = [];
            foreach ($allStores as $store) {
                $stores[] = $store->getId();
            }
        }
        foreach ($stores as $storeId) {
            if ($layoutUpdateId) {
                $this->removeLinks($layoutUpdateId, $storeId);
            }
            $themeId = $this->getThemeId($storeId);

            $xml = $this->generateLayoutUpdateXml($position);

            $layoutUpdate
                ->setHandle($this->getHandleByPosition($position))
                ->setXml($xml)
                ->setThemeId($themeId)
                ->setStoreId($storeId);
            $layoutUpdate->save();
        }
        $group->setLayoutUpdateId($layoutUpdate->getId());
    }

    /**
     * @param int|null $layoutUpdateId
     */
    public function delete($layoutUpdateId)
    {
        /** @var Update $layoutUpdate */
        $layoutUpdate = $this->getLayoutUpdate($layoutUpdateId);
        if ($layoutUpdate->getId()) {
            $layoutUpdate->delete();
        }
    }

    /**
     * @param int|null $layoutUpdateId
     *
     * @return Update
     */
    public function getLayoutUpdate($layoutUpdateId)
    {
        /** @var Update $layoutUpdate */
        $layoutUpdate = $this->layoutUpdateFactory->create();
        if ($layoutUpdateId) {
            $layoutUpdate->load($layoutUpdateId);
        }

        return $layoutUpdate;
    }

    /**
     * @param string $position
     *
     * @return string
     */
    public function generateLayoutUpdateXml($position)
    {
        $nameInLayout = 'amrelated_' . $position;
        $xml = '<body><referenceContainer name="' . $this->getContainerByPosition($position) . '">';
        $xml .= '<block class="' . Wrapper::class .
            '" name="' . $nameInLayout .
            '" template="' . $this->getTemplateByPosition($position) . '" ';
        if ($positionAttribute = $this->getPositionAttribute($position)) {
            $xml .= $positionAttribute;
        }
        $xml .= '>';
        // @codingStandardsIgnoreStart
        $xml .= '<action method="setData">' .
            '<argument name="name" xsi:type="string">position</argument>' .
            '<argument name="value" xsi:type="string">' . $position . '</argument></action>';
        $xml .= '</block></referenceContainer>';
        // @codingStandardsIgnoreEnd
        if ($move = $this->getMoveElement($position, $nameInLayout)) {
            $xml .= $move;
        }
        $xml .= '</body>';

        return $xml;
    }

    /**
     * @param int $layoutUpdateId
     * @param int $storeId
     */
    private function removeLinks($layoutUpdateId, $storeId)
    {
        /** @var LinkCollection $linkCollection */
        $linkCollection = $this->linksFactory->create();
        $linkCollection
            ->addFieldToFilter(GroupInterface::LAYOUT_UPDATE_ID, $layoutUpdateId)
            ->addFieldToFilter('store_id', $storeId);
        foreach ($linkCollection as $link) {
            $link->delete();
        }
    }

    /**
     * @param string $position
     *
     * @return string
     */
    private function getHandleByPosition($position)
    {
        $handle = '';
        $ruleType = $this->positionDefiner->getTypeByValue($position);
        switch ($ruleType['value']) {
            case RuleType::PRODUCT:
                $handle = 'catalog_product_view';
                break;
            case RuleType::CATEGORY:
                $handle = 'catalog_category_view';
                break;
            case RuleType::CART:
                $handle = 'checkout_cart_index';
                break;
        }

        return $handle;
    }

    /**
     * @param $position
     *
     * @return string
     */
    private function getTemplateByPosition($position)
    {
        switch ($position) {
            case BlockPosition::CATEGORY_SIDEBAR_BOTTOM:
            case BlockPosition::CATEGORY_SIDEBAR_TOP:
            case BlockPosition::PRODUCT_SIDEBAR_BOTTOM:
            case BlockPosition::PRODUCT_SIDEBAR_TOP:
                $template = self::SIDEBAR_TEMPLATE;
                break;
            default:
                $template = self::CONTENT_TEMPLATE;
        }

        return $template;
    }

    /**
     * @param $position
     *
     * @return string
     */
    private function getContainerByPosition($position)
    {
        switch ($position) {
            case BlockPosition::PRODUCT_CONTENT_TOP:
            case BlockPosition::CART_CONTENT_TOP:
            case BlockPosition::CATEGORY_CONTENT_TOP:
                $container = 'content.top';
                break;
            case BlockPosition::CART_CONTENT_BOTTOM:
            case BlockPosition::CATEGORY_CONTENT_BOTTOM:
            case BlockPosition::PRODUCT_CONTENT_BOTTOM:
                $container = 'content.bottom';
                break;
            case BlockPosition::CATEGORY_SIDEBAR_BOTTOM:
            case BlockPosition::PRODUCT_SIDEBAR_BOTTOM:
                $container = 'sidebar.additional';
                break;
            case BlockPosition::CATEGORY_SIDEBAR_TOP:
            case BlockPosition::PRODUCT_SIDEBAR_TOP:
                $container = 'sidebar.main';
                break;
            case BlockPosition::PRODUCT_AFTER_RELATED:
            case BlockPosition::PRODUCT_BEFORE_RELATED:
            case BlockPosition::PRODUCT_AFTER_UPSELL:
            case BlockPosition::PRODUCT_BEFORE_UPSELL:
                $container = 'content.aside';
                break;
            case BlockPosition::CART_AFTER_CROSSSEL:
            case BlockPosition::CART_BEFORE_CROSSSEL:
            case BlockPosition::PRODUCT_BEFORE_TAB:
                $container = 'content';
                break;
            case BlockPosition::PRODUCT_CONTENT_TAB:
                $container = self::TAB_NAME;
                break;
            default:
                $container = '';
        }

        return $container;
    }

    /**
     * @param string $position
     *
     * @return string
     */
    private function getPositionAttribute($position)
    {
        switch ($position) {
            case BlockPosition::PRODUCT_AFTER_UPSELL:
                $positionAttribute = 'after="' . self::UPSELL_NAME . '"';
                break;
            case BlockPosition::PRODUCT_AFTER_RELATED:
                $positionAttribute = 'after="' . self::RELATED_NAME . '"';
                break;
            case BlockPosition::CART_AFTER_CROSSSEL:
            case BlockPosition::CART_BEFORE_CROSSSEL:
                $positionAttribute = '';
                break;
            case BlockPosition::PRODUCT_BEFORE_RELATED:
                $positionAttribute = 'before="' . self::RELATED_NAME . '"';
                break;
            case BlockPosition::PRODUCT_BEFORE_TAB:
                $positionAttribute = 'before="' . self::TAB_NAME . '"';
                break;
            case BlockPosition::PRODUCT_CONTENT_TAB:
                $positionAttribute = 'group="detailed_info"';
                break;
            case BlockPosition::PRODUCT_BEFORE_UPSELL:
                $positionAttribute = 'before="' . self::UPSELL_NAME . '"';
                break;
            case BlockPosition::CATEGORY_SIDEBAR_BOTTOM:
            case BlockPosition::PRODUCT_SIDEBAR_BOTTOM:
            case BlockPosition::PRODUCT_CONTENT_BOTTOM:
            case BlockPosition::CATEGORY_CONTENT_BOTTOM:
            case BlockPosition::CART_CONTENT_BOTTOM:
                $positionAttribute = 'after="-"';
                break;
            case BlockPosition::CART_CONTENT_TOP:
            case BlockPosition::CATEGORY_CONTENT_TOP:
            case BlockPosition::CATEGORY_SIDEBAR_TOP:
            case BlockPosition::PRODUCT_SIDEBAR_TOP:
            case BlockPosition::PRODUCT_CONTENT_TOP:
                $positionAttribute = 'before="-"';
                break;
            default:
                $positionAttribute = '';
        }

        return $positionAttribute;
    }

    /**
     * @param string $position
     * @param string $name
     *
     * @return string
     */
    private function getMoveElement($position, $name)
    {
        switch ($position) {
            case BlockPosition::CART_AFTER_CROSSSEL:
                $move = ' <move element="' . $name
                    . '" destination="checkout.cart.container" after="' . self::CROSSEL_NAME . '"/>';
                break;
            case BlockPosition::CART_BEFORE_CROSSSEL:
                $move = ' <move element="' . $name
                    . '" destination="checkout.cart.container" before="' . self::CROSSEL_NAME . '"/>';
                break;
            default:
                $move = '';
        }

        return $move;
    }

    /**
     * @param int|string $storeId
     * @return int|string
     */
    private function getThemeId($storeId)
    {
        $themeId = $this->themeConfig->getConfigurationDesignTheme(
            Area::AREA_FRONTEND,
            [
                'store' => $storeId
            ]
        );

        if (!is_numeric($themeId)) {
            /** @var Theme $theme */
            $theme = $this->themeFactory->create();
            $this->themeResource->load($theme, $themeId, 'theme_path');
            $themeId = $theme->getId();
        }

        return $themeId;
    }
}
