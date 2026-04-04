<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\Component;

use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Ui\DataProvider\Pack\Listing\GetConditionalDiscountsData;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class PackDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Amasty\Mostviewed\Model\OptionSource\DiscountType
     */
    private $discountType;

    /**
     * @var \Amasty\Mostviewed\Model\ResourceModel\Pack
     */
    private $packResource;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var GetConditionalDiscountsData
     */
    private $getConditionalDiscountsData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Amasty\Mostviewed\Model\OptionSource\DiscountType $discountType,
        \Amasty\Mostviewed\Model\ResourceModel\Pack $packResource,
        ProductCollectionFactory $productCollectionFactory,
        GetConditionalDiscountsData $getConditionalDiscountsData,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->discountType = $discountType;
        $this->packResource = $packResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->getConditionalDiscountsData = $getConditionalDiscountsData;
    }

    /**
     * @param SearchResultInterface $searchResult
     *
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $data = $item->getData();
            if (isset($data['discount_type'])) {
                if ($data['discount_type'] == DiscountType::CONDITIONAL) {
                    $data['conditional'] = $this->getConditionalDiscountsData->execute((int) $data['pack_id']);
                }
                $data['discount_type'] = $this->discountType->getLabelByValue($data['discount_type']);
            }
            if (isset($data['store_id']) && $data['store_id'] == '0') {
                $data['store_id'] = ['0'];
            }
            $data['parent_ids'] = $this->packResource->getParentIdsByPack($data['pack_id']);
            $this->convertProductIdsToNames($data);
            $arrItems['items'][] = $data;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    /**
     * @param array $data
     */
    private function convertProductIdsToNames(&$data)
    {
        if (isset($data['product_ids']) && $data['product_ids']) {
            $data['product_ids'] = $this->convertIds($data['product_ids']);
        }
        if (isset($data['parent_ids']) && $data['parent_ids']) {
            $data['parent_ids'] = $this->convertIds($data['parent_ids']);
        }
    }

    /**
     * @param array|string $productIds
     */
    private function convertIds($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($productIds)
            ->addAttributeToSelect(['name'], 'left');

        $result = [];
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($productCollection->getItems() as $product) {
            $result[] = $product->getName();
        }

        return implode(', ', $result);
    }
}
