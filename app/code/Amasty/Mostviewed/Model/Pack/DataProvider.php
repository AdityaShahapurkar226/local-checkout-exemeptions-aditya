<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack;

use Amasty\Base\Model\Serializer;
use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Controller\Adminhtml\Pack\Edit;
use Amasty\Mostviewed\Model\Backend\Pack\Registry;
use Amasty\Mostviewed\Model\Pack;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    private $packRegistry;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var \Amasty\Mostviewed\Helper\Price
     */
    private $priceModifier;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var PoolInterface
     */
    private $pool;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\UrlInterface $urlBuilder,
        CollectionFactory $collectionFactory,
        Registry $packRegistry,
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        \Amasty\Mostviewed\Helper\Price $priceModifier,
        Serializer $serializer,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->urlBuilder = $urlBuilder;
        $this->packRegistry = $packRegistry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->priceModifier = $priceModifier;
        $this->serializer = $serializer;
        $this->pool = $pool;
    }

    /**

     * @return array
     */
    public function getData()
    {
        $result = parent::getData();

        if ($data = $this->getSavedPack()) {
            /** @var Pack $pack */
            $pack = $this->collection->getNewEmptyItem();
            $pack->setData($data);
            $data = $this->convertProductsData($pack);
            $result[$pack->getId()] = $data;
            $this->dataPersistor->clear(Pack::PERSISTENT_NAME);
        } else {
            $current = $this->getCurrentPack();
            $data = $this->convertProductsData($current);
            $result[$current->getPackId()] = $data;
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $result = $modifier->modifyData($result);
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getCurrentPack()
    {
        return $this->packRegistry->get();
    }

    /**
     * @return mixed
     */
    private function getSavedPack()
    {
        return $this->dataPersistor->get(Pack::PERSISTENT_NAME);
    }

    private function convertProductsData(Pack $pack): array
    {
        $data = $pack->getData() ?? [];

        if (isset($data['product_ids'])
            && $data['product_ids']
            && !isset($data['product_ids']['child_products_container'])
        ) {
            $sortedData = explode(',', $data['product_ids']);
            $productsData = $this->getProductsData(
                $sortedData,
                $this->serializer->unserialize($data[PackInterface::PRODUCTS_INFO] ?? [])
            );
            /* save correct sort*/
            foreach ($sortedData as $key => $productId) {
                if (isset($productsData[$productId])) {
                    $sortedData[$key] = $productsData[$productId];
                } else {
                    unset($sortedData[$key]);
                }
            }

            $data['product_ids'] = [
                'child_products_container' => $sortedData
            ];
        }

        if (isset($data['parent_ids'])
            && $data['parent_ids']
            && !isset($data['parent_ids']['parent_products_container'])
        ) {
            $data['parent_ids'] = [
                'parent_products_container' => array_values($this->getProductsData($data['parent_ids']))
            ];
        }

        if ($pack->getExtensionAttributes()->getStores()) {
            $data['stores'] = implode(',', $pack->getExtensionAttributes()->getStores());
        }

        return $data;
    }

    /**
     * @param array|string $productIds
     * @param array $additionalData
     * @return array
     */
    private function getProductsData($productIds, $additionalData = [])
    {
        if (!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($productIds)
            ->addAttributeToSelect(['status', 'thumbnail', 'name', 'price'], 'left');

        $result = [];
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($productCollection->getItems() as $product) {
            $productId = $product->getId();
            $result[$productId] = $this->fillData($product);
            if (isset($additionalData[$productId])) {
                $result[$productId] = $additionalData[$productId] + $result[$productId];
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    private function fillData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return [
            'entity_id' => $product->getId(),
            'thumbnail' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
            'name'      => $product->getName(),
            'status'    => $product->getStatus(),
            'type_id'   => $product->getTypeId(),
            'sku'       => $product->getSku(),
            'price'     => $product->getPrice() ? $this->priceModifier->toDefaultCurrency($product->getPrice()) : '-'
        ];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
