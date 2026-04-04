<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver;

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
class SellerReviewDetails implements ResolverInterface
{
    /**
     *
     * @param SellerManagement $sellerManagement
     */
    public function __construct(
        SellerManagement $sellerManagement
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
        if (!isset($args['id'])) {
            throw new GraphQlInputException(
                __("'id' input argument is required.")
            );
        }
        return $this->sellerManagement->getReview($args['id'])->__toArray();
    }
}
