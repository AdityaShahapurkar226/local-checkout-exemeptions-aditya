<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Plugin\Elasticsearch7\SearchAdapter;

use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;

class Adapter
{

    /**
     *
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;
    /**
     *
     * @var Mapper
     */
    private $mapper;

    /**
     * @var QueryContainerFactory
     */
    private $queryContainerFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /** @var MpHelper */
    protected $mpHelper;

    /**
     *
     *
     * @var array
     */
    private static $emptyRawResponse = [
        "hits" =>
            [
                "hits" => []
            ],
        "aggregations" =>
            [
                "price_bucket" => [],
                "category_bucket" =>
                    [
                        "buckets" => []

                    ]
            ]
    ];

    /**
     * Initialization
     *
     * @param ConnectionManager $connectionManager
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param Mapper $mapper
     * @param QueryContainerFactory $queryContainerFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\Collection $collection
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
     * @param MpHelper $mpHelper
     */
    public function __construct(
        ConnectionManager $connectionManager,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        Mapper $mapper,
        QueryContainerFactory $queryContainerFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Marketplace\Model\ResourceModel\Product\Collection $collection,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory,
        MpHelper $mpHelper
    ) {
        $this->connectionManager = $connectionManager;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->mapper = $mapper;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->request = $request;
        $this->collection = $collection;
        $this->helper = $helper;
        $this->associatesFactory = $associatesFactory;
        $this->mpHelper = $mpHelper;
    }

    /**
     * Hide assigned prodcuts from collection
     *
     * @param \Magento\Elasticsearch7\SearchAdapter\Adapter $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return void
     */
    public function aroundQuery(
        \Magento\Elasticsearch7\SearchAdapter\Adapter $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        $assignProductsIds = $this->helper->getAssignProductCategoryCollection()->getAllIds();
        $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
        $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
        $actionName = $this->request->getFullActionName();
        if (!empty($assignProductsIds) && $actionName != 'marketplace_seller_collection') {
            $client = $this->connectionManager->getConnection();
            $aggregationBuilder = $this->aggregationBuilder;
            $updatedQuery = $this->mapper->buildQuery($request);
            $updatedQuery['body']['query']['bool']['mustNot'] = ['ids' => ['values' => $assignProductsIds]];
            $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $updatedQuery]));
            try {
                $rawResponse = $client->query($updatedQuery);
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Elasticsearch7_SearchAdapter_Adapter aroundQuery ".$e->getMessage());
                // return empty search result in case an exception is thrown from Elasticsearch
                $rawResponse = self::$emptyRawResponse;
            }

            $rawDocuments = $rawResponse['hits']['hits'] ?? [];
            $queryResponse = $this->responseFactory->create(
                [
                    'documents' => $rawDocuments,
                    'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                    'total' => $rawResponse['hits']['total']['value'] ?? 0
                ]
            );
            return $queryResponse;
        } elseif ($actionName == 'marketplace_seller_collection') {
            $partner = $this->mpHelper->getProfileDetail(MpHelper::URL_TYPE_COLLECTION);
            $sellerId = $partner->getSellerId();
            $productIds = $this->mpHelper->getSellerProducts($sellerId);
            $assignProductsIds = $this->helper->checkIfSellerHasAssignedProduct($sellerId);            
            if ($assignProductsIds) {

                $assignProductsIds = $assignProductsIds->addFieldToSelect('assign_product_id')->getData();
                $assignProductsIds = array_column($assignProductsIds, 'assign_product_id');

                $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
                $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
                $assignProductsIds = array_merge($assignProductsIds, $productIds);
                $client = $this->connectionManager->getConnection();
                $aggregationBuilder = $this->aggregationBuilder;
                $updatedQuery = $this->mapper->buildQuery($request);
                $updatedQuery['body']['query']['bool']['filter'] = ['ids' => ['values' => $assignProductsIds]];
                
                $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $updatedQuery]));
                try {
                    $rawResponse = $client->query($updatedQuery);
                } catch (\Exception $e) {
                    $this->helper
                    ->logDataInLogger("Elasticsearch7_SearchAdapter_Adapter aroundQuery ".$e->getMessage());
                    // return empty search result in case an exception is thrown from Elasticsearch
                    $rawResponse = self::$emptyRawResponse;
                }
                $rawDocuments = $rawResponse['hits']['hits'] ?? [];
                $queryResponse = $this->responseFactory->create(
                    [
                        'documents' => $rawDocuments,
                        'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                        'total' => $rawResponse['hits']['total']['value'] ?? 0
                    ]
                );
                return $queryResponse;
            }
            
        }
        return $proceed($request);
    }
}
