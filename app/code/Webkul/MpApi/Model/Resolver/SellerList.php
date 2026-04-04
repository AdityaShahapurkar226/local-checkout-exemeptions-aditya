<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver;

use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Webkul\MpApi\Api\SellerRepositoryInterface;

/**
 * Book field resolver, used for GraphQL request processing
 */
class SellerList implements ResolverInterface
{
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SellerRepositoryInterface $sellerRepo
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SellerRepositoryInterface $sellerRepo
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sellerRepo = $sellerRepo;
    }
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'filter' input argument is required.")
            );
        }
        $fieldName = key($args['filter']);
        $filterType = key($args['filter'][$fieldName]);
        $fieldValue = $args['filter'][$fieldName][$filterType];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter($fieldName, $fieldValue, $filterType)->create();
        return $this->sellerRepo->getList($searchCriteria)->__toArray();
    }
}
