<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver\Seller;

use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Webkul\MpApi\Api\SellerManagementInterface;

/**
 * Book field resolver, used for GraphQL request processing
 */
class OrderList implements ResolverInterface
{
    /**
     *
     * @param SellerManagement $sellerManagement
     */
    public function __construct(
        SellerManagementInterface $sellerManagement
    ) {
        $this->sellerManagement = $sellerManagement;
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        
        return $this->sellerManagement->getSellerSalesList($context->getUserId())->__toArray();
    }
}
