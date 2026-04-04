<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\DataProvider\Pack\Form\Modifier;

use Amasty\Mostviewed\Model\Backend\Pack\Registry;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ConditionalDiscounts implements ModifierInterface
{
    public const FIELD_ID = 'id';
    public const FIELD_NUMBER_ITEMS = 'number_items';
    public const FIELD_DISCOUNT = 'discount_amount';
    public const FIELD_SORT_ORDER_NAME = 'sort_order';
    public const FIELD_IS_DELETE = 'is_delete';

    public const GRID_CONDITIONAL = 'conditional';
    public const BUTTON_ADD = 'button_add';

    /**
     * @var string
     */
    private $containerName;

    /**
     * @var string
     */
    private $tabName;

    /**
     * @var string
     */
    private $dataScope;

    /**
     * @var Registry
     */
    private $packRegistry;

    public function __construct(
        Registry $packRegistry,
        string $tabName = 'conditional_discounts',
        string $containerName = 'conditional_discounts',
        string $dataScope = ''
    ) {
        $this->packRegistry = $packRegistry;
        $this->containerName = $containerName;
        $this->tabName = $tabName;
        $this->dataScope = $dataScope;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $pack = $this->packRegistry->get();
        if (isset($data[$pack->getPackId()]) && $pack->getExtensionAttributes()->getConditionalDiscounts()) {
            foreach ($pack->getExtensionAttributes()->getConditionalDiscounts() as $conditionalDiscount) {
                $data[$pack->getPackId()][static::GRID_CONDITIONAL][] = $conditionalDiscount->getData();
            }
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                $this->tabName => [
                    'children' => [
                        $this->containerName => [
                            'children' => [
                                static::GRID_CONDITIONAL => $this->getRuleGridConfig(10),
                                static::BUTTON_ADD => $this->getButtonConfig(15)
                            ]
                        ]
                    ]
                ]
            ]
        );

        return $meta;
    }

    protected function getButtonConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'title' => __('Add Discount Option'),
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/button',
                        'sortOrder' => $sortOrder,
                        'actions' => [
                            [
                                'targetName' => sprintf(
                                    '${ $.ns }.${ $.ns }.%s.%s.%s',
                                    $this->tabName,
                                    $this->containerName,
                                    static::GRID_CONDITIONAL
                                ),
                                'actionName' => 'processingAddChild',
                                '__disableTmpl' =>  false
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    protected function getRuleGridConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Discount Option'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'template' => 'ui/dynamic-rows/templates/default',
                        'additionalClasses' => 'admin__field-wide',
                        'dataScope' => $this->dataScope,
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => true,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' =>  false
                        ],
                        'dataProvider' => '${ $.provider}',
                        'dndConfig' => ['enabled' => false],
                        'map' => ['id' => 'id'],
                        'update' => true
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('Add Discount Option'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true
                            ],
                        ],
                    ],
                    'children' => $this->getGridColumns()
                ]
            ]
        ];
    }

    private function getGridColumns(): array
    {
        return [
            static::FIELD_NUMBER_ITEMS => $this->getRangeFieldConfig([
                'sortOrder' => 10,
                'label' => __('Number of Individual Bundle Items')
            ], 0, 127),
            static::FIELD_DISCOUNT => $this->getRangeFieldConfig([
                'sortOrder' => 20,
                'label' => __('Bundle Discount Amount, %'),
            ], 0, 100),
            static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(997),
            static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(998),
            static::FIELD_ID => $this->getOptionIdFieldConfig(999),
        ];
    }

    protected function getPositionFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Hidden::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    protected function getOptionIdFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Hidden::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false
                    ],
                ],
            ],
        ];
    }

    protected function getRangeFieldConfig(array $config, int $min = 0, int $max = 2147483647): array
    {
        return array_merge_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => Input::NAME,
                            'componentType' => Field::NAME,
                            'dataType' => Number::NAME,
                            'visible' => true,
                            'showIfConditional' => 1,
                            'validation' => [
                                'required-entry' => true,
                                'validate-number' => true,
                                'greater-than-equals-to' => $min,
                                'less-than-equals-to' => $max
                            ]
                        ]
                    ]
                ]
            ],
            [
                'arguments' => [
                    'data' => [
                        'config' => $config
                    ]
                ]
            ]
        );
    }

    protected function getIsDeleteFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }
}
