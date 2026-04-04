<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver\Seller;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MpApi\Api\SellerManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpApi\Api\Data\CreditMemoInterface;
use Webkul\MpApi\Api\Data\CreditMemoInterfaceFactory;

/**
 * Book field resolver, used for GraphQL request processing
 */
class CreateCreditmemo implements ResolverInterface
{
    /**
     * @var SellerManagementInterface
     */
    protected $sellerManagement;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param SellerManagementInterface $sellerManagement
     * @param CreditMemoInterfaceFactory $creditMemoInterfaceFactory
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        SellerManagementInterface $sellerManagement,
        CreditMemoInterfaceFactory $creditMemoInterfaceFactory,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sellerManagement = $sellerManagement;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->creditMemoInterfaceFactory = $creditMemoInterfaceFactory;
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
        if (!isset($args['orderId'])) {
            throw new GraphQlInputException(
                __("'orderId' input argument is required.")
            );
        }
        $creditmemoDataObject = $this->createObject($args["creditmemo"]);
        $items = [];
        foreach ($creditmemoDataObject->getItems() as $key => $item) {
            $items[$item->getItemId()] = $item;
        }
        $creditmemoDataObject->setItems($items);
        $result = $this->sellerManagement->createCreditmemo(
            $context->getUserId(),
            $args['invoiceId'],
            $args['orderId'],
            $creditmemoDataObject
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

    /**
     * Create Credit Memo data object
     *
     * @param array $data
     * @return CreditMemoInterface
     * @throws LocalizedException
     */
    private function createObject(array $data): CreditMemoInterface
    {
        $creditmemoDataObject = $this->creditMemoInterfaceFactory->create();
        /**
         * Add required attributes for credit memo entity
         */
        $requiredDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $creditmemoDataObject,
            CreditMemoInterface::class
        );
        $data = array_merge($requiredDataAttributes, $data);
        $this->dataObjectHelper->populateWithArray(
            $creditmemoDataObject,
            $data,
            CreditMemoInterface::class
        );

        return $creditmemoDataObject;
    }
}
