<?php
namespace Ewave\ExtendedBundleProduct\Ui\DataProvider\Product\Form\Modifier;

use Ewave\ExtendedBundleProduct\Helper\Data;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Element\MultiSelect;

/**
 * Class ConfigurablePanel
 * @package Ewave\ExtendedBundleProduct\Ui\DataProvider\Product\Form\Modifier
 */
class ConfigurablePanel extends AbstractModifier
{
    const CODE_BUNDLE_DATA = 'bundle-items';
    const CODE_BUNDLE_OPTIONS = 'bundle_options';
    const CODE_IS_SEPARATE_CART_ITEMS = 'is_separate_cart_items';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $path = $this->arrayManager->findPath(
            static::CODE_BUNDLE_DATA,
            $meta,
            null,
            'children'
        );

        $meta = $this->arrayManager->merge(
            $path,
            $meta,
            [
                'children' => [
                    self::CODE_BUNDLE_OPTIONS => $this->getBundleOptions()
                ]
            ]
        );

        $meta = $this->modifyConfiguration($meta, static::CODE_IS_SEPARATE_CART_ITEMS);
        $meta = $this->modifyConfiguration($meta, Data::CODE_ATTRIBUTE_BUNDLE_IS_COUNT_ITEMS_SEPARATE);

        return $meta;
    }

    /**
     * Modify Is Separate Cart Items configuration
     *
     * @param array $meta
     * @param string $codeAttribute
     * @return array
     */
    protected function modifyConfiguration(array $meta, $codeAttribute)
    {
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                $codeAttribute,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'dataScope' => 'data.product.' . $codeAttribute,
            ]
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Get Bundle Options structure
     *
     * @return array
     */
    protected function getBundleOptions()
    {
        return [
            'children' => [
                'record' => [
                    'children' => [
                        'product_bundle_container' => [
                            'children' => [
                                'bundle_selections' => [
                                    'children' => [
                                        'record' => $this->getBundleSelections(),
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get bundle selections structure
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getBundleSelections()
    {
        return [
            'children' => [
                'configurable_options' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Ewave_ExtendedBundleProduct/js/components/configurable-multiselect',
                                'formElement' => MultiSelect::NAME,
                                'componentType' => Form\Field::NAME,
                                'label' => __('Configurable Options'),
                                'dataScope' => 'configurable_options',
                                'sortOrder' => 280,
                                'visible' => true,
                                'url' => $this->urlBuilder->getUrl(
                                    'ewave_extendedbundleproduct/extendedbundleproduct/configurableOptions'
                                ),
                                'imports' => [
                                    'productId' => '${ $.provider }:${ $.parentScope }.product_id',
                                    'selectionId' => '${ $.provider }:${ $.parentScope }.selection_id',
                                    '__disableTmpl' => ['productId' => false, 'selectionId' => false],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
