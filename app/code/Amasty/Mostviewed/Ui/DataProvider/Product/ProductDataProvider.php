<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\DataProvider\Product;

class ProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public const PRODUCTS_KEY = 'mostviewed_conditions_applied';

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->registry = $registry;
    }

    /**
     * @param array $productIds
     */
    public function updateCollection($productIds)
    {
        /** @var \Magento\Catalog\Ui\DataProvider\Product\ProductCollection $collection */
        $collection = parent::getCollection();
        $collection->addIdFilter($productIds);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $appliedProducts = $this->registry->registry(self::PRODUCTS_KEY);
        if (!$this->getCollection()->isLoaded()) {
            if (is_array($appliedProducts)) {
                $this->updateCollection(array_keys($appliedProducts));
            }
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        if ($appliedProducts) {
            foreach ($items as &$item) {
                if (isset($appliedProducts[$item['entity_id']])) {
                    $stores = $appliedProducts[$item['entity_id']];
                    if (in_array('0', $stores)) {
                        $item['stores'] = ['0'];
                    } else {
                        $item['stores'] = $stores;
                    }
                }
            }
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'amasty_mostviewed_product_listing_' . $this->getRequest()->getParam('relation') . '_data_source' => [
                'arguments' => [
                    'data' => [
                        'js_config' => [
                            'group_id' => $this->getRequest()->getParam('group_id')
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    private function getRequest()
    {
        return $this->data['config']['request'];
    }
}
