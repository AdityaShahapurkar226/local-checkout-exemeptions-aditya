<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver;

use Webkul\MpApi\Api\Data\FeedbackInterfaceFactory;
use Webkul\Marketplace\Helper\Data;
use Webkul\MpApi\Model\Seller\SellerManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Book field resolver, used for GraphQL request processing
 */
class SellerReviews implements ResolverInterface
{
    /**
     * @param SellerManagement $sellerManagement
     * @param Data $mpHelper
     */
    public function __construct(
        SellerManagement $sellerManagement,
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
        
        return $this->sellerManagement->getSellerReviews($args['id'])->__toArray();
    }
}
