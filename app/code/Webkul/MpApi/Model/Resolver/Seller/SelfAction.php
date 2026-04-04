<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver\Seller;

use Magento\Authorization\Model\UserContextInterface;
use Webkul\MpApi\Api\SellerManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\CustomerGraphQl\Model\Resolver\Customer as CustomerGraphQl;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;

/**
 * Book field resolver, used for GraphQL request processing
 */
class SelfAction extends CustomerGraphQl
{
    /**
     * @var SellerManagementInterface
     */
    protected $sellerManagement;

    /**
     * @param SellerManagementInterface $sellerManagement
     * @param GetCustomer $getCustomer
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        SellerManagementInterface $sellerManagement,
        GetCustomer $getCustomer,
        ExtractCustomerData $extractCustomerData
    ) {
        $this->sellerManagement = $sellerManagement;
        parent::__construct($getCustomer, $extractCustomerData);
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
        parent::resolve($field, $context, $info, $value, $args);
        $result = $this->sellerManagement->getSeller($context->getUserId());
        
        return $result->getTotalCount() > 0 ? $result->getItems()[0] : [];
    }
}
