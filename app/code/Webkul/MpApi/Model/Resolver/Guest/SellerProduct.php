<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver\Guest;

use Magento\Authorization\Model\UserContextInterface;
use Webkul\MpApi\Api\SellerManagementInterface;
use Webkul\Marketplace\Helper\Data;
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
class SellerProduct implements ResolverInterface
{
    /**
     * @var SellerManagementInterface
     */
    protected $sellerManagement;

    /**
     * @param SellerManagementInterface $sellerManagement
     * @param Data $mpHelper
     */
    public function __construct(
        SellerManagementInterface $sellerManagement,
        Data $mpHelper
    ) {
        $this->sellerManagement = $sellerManagement;
        $this->mpHelper = $mpHelper;
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
        if (!isset($args['id'])) {
            throw new GraphQlInputException(
                __("'id' input argument is required.")
            );
        }
        if (!$this->mpHelper->getSellerStatus($args['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid Seller')
            );
        }
        
        $result = $this->sellerManagement->getSellerProducts($args['id']);
        if ($result['item']['status'] == 2) {
            throw new GraphQlAuthorizationException(
                __(
                    $result['item']['error']
                )
            );
        }
        return $result['item'];
    }
}
