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
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Book field resolver, used for GraphQL request processing
 */
class CreateShipment implements ResolverInterface
{
    /**
     * @var SellerManagementInterface
     */
    protected $sellerManagement;

    /**
     * @param SellerManagementInterface $sellerManagement
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (!isset($args['orderId']) || !isset($args['trackingId']) || !isset($args['carrier'])) {
            throw new GraphQlInputException(
                __("'orderId' & 'trackingId' & 'carrier' input arguments are required.")
            );
        }
        $result = $this->sellerManagement->ship(
            $context->getUserId(),
            $args['orderId'],
            $args['trackingId'],
            $args['carrier']
        );
        if ($result['item']['status'] == 2) {
            throw new GraphQlAuthorizationException(
                __(
                    $result['item']['message']
                )
            );
        }
        return $result['item'];
    }
}
