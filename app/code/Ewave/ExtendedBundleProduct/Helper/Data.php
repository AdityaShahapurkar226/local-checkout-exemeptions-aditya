<?php
namespace Ewave\ExtendedBundleProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Bundle\Model\Selection;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Ewave\ExtendedBundleProduct\Helper
 */
class Data extends AbstractHelper
{
    const XML_CONFIG_PATH_IS_COUNT_BUNDLE_ITEMS_SEPARATELY =
        'extended_bundle_product/general/count_bundle_items_separately';

    const XML_SHOW_BUNDLE_IMAGES =
        'extended_bundle_product/general/show_bundle_images';

    const CODE_ATTRIBUTE_BUNDLE_IS_COUNT_ITEMS_SEPARATE = 'is_count_bundle_items_separate';

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param Selection|Product $selection
     * @return array
     */
    public function getConfigurableOptions($selection)
    {
        if ($options = $selection->getConfigurableOptions()) {
            return json_decode($options, true);
        }
        return [];
    }

    /**
     * @param Selection $selection
     * @param array $options
     * @return void
     */
    public function setConfigurableOptions(Selection $selection, array $options)
    {
        $selection->setConfigurableOptions(json_encode($options));
    }

    /**
     * @return bool
     */
    public function isCountBundleItemsSeparately()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_CONFIG_PATH_IS_COUNT_BUNDLE_ITEMS_SEPARATELY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return bool
     */
    public function showImages()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_SHOW_BUNDLE_IMAGES,
            ScopeInterface::SCOPE_STORE
        );
    }
}
