<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Helper\Catalog\Product;

use Magento\Bundle\Helper\Catalog\Product\Configuration as Subject;
use Magento\Bundle\Model\Product\Price as PriceModel;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class ConfigurationPlugin
 */
class ConfigurationPlugin
{
    /**
     * Core data
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->escaper = $escaper;
    }

    /**
     * @param Subject $subject
     * @param \Closure $proceed
     * @param ItemInterface|\Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    public function aroundGetBundleOptions(
        Subject $subject,
        \Closure $proceed,
        ItemInterface $item
    ) {
        $children = $item->getChildren();
        if (!empty($children)) {
            $options = [];
            $product = $item->getProduct();

            /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
            $bundleOptionsIds = $optionsQuoteItemOption
                ? json_decode($optionsQuoteItemOption->getValue(), true)
                : [];

            if ($bundleOptionsIds) {
                /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
                $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);
                $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');
                $bundleSelectionIds = json_decode($selectionsQuoteItemOption->getValue(), true);

                if (!empty($bundleSelectionIds)) {
                    $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);
                    $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
                    foreach ($bundleOptions as $bundleOption) {
                        if ($bundleOption->getSelections()) {
                            $option = ['label' => $bundleOption->getTitle(), 'value' => []];

                            $bundleSelections = $bundleOption->getSelections();
                            foreach ($bundleSelections as $bundleSelection) {
                                $qty = $subject->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                                if ($qty) {
                                    $option['has_html'] = true;
                                    $optionValueAdded = false;
                                    foreach ($children as $child) {
                                        $childSelectionId = $child->getOptionByCode('selection_id')->getValue();
                                        if ($child->getProductType() == Configurable::TYPE_CODE
                                            && $childSelectionId == $bundleSelection->getSelectionId()
                                        ) {
                                            $childProduct = $child->getProduct();
                                            if ($product->getPriceType() == PriceModel::PRICE_TYPE_DYNAMIC) {
                                                $price = $childProduct->getPriceInfo()->getPrice('final_price');
                                                $priceValue = $price->getCustomAmount($child->getPrice())->getValue();
                                            } else {
                                                $priceValue = $subject->getSelectionFinalPrice($item, $bundleSelection);
                                            }

                                            $option['value'][] = '<span class="bundle_selection_name">' . $qty . ' x '
                                                . $this->escaper->escapeHtml($bundleSelection->getName())
                                                . '</span> '
                                                . $this->pricingHelper->currency($priceValue);

                                            $attributes = $childProduct->getTypeInstance()
                                                ->getSelectedAttributesInfo($childProduct);

                                            foreach ($attributes as $attribute) {
                                                $option['value'][] = '<span class="bundle_selection_attribute">'
                                                    . implode(': ', [
                                                        $attribute['label'],
                                                        $attribute['value']
                                                    ]) . '</span>';
                                            }

                                            $optionValueAdded = true;
                                        }
                                    }

                                    if (!$optionValueAdded) {
                                        $option['value'][] = $qty . ' x '
                                            . $this->escaper->escapeHtml($bundleSelection->getName())
                                            . ' '
                                            . $this->pricingHelper->currency(
                                                $subject->getSelectionFinalPrice($item, $bundleSelection)
                                            );
                                    }
                                }
                            }

                            if ($option['value']) {
                                $options[] = $option;
                            }
                        }
                    }
                }
            }
            return $options;
        }

        return $proceed($item);
    }
}
