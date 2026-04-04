<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMultiShipping\Controller\Labels;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;

class GetData extends Action
{
    /**
     * @var \Webkul\MarketplaceBaseShipping\Block\Order\Packaging
     */
    private $baseShippingOrdersBlock;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $_resultJsonFactory;

    /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory
     */
    private $saleslistFactory;

    /**
     * @param Context $context
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Webkul\MarketplaceBaseShipping\Block\Order\Packaging $baseShippingOrdersBlock
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Webkul\MarketplaceBaseShipping\Block\Order\Packaging $baseShippingOrdersBlock,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->baseShippingOrdersBlock = $baseShippingOrdersBlock;
        $this->carrierFactory = $carrierFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->saleslistFactory = $saleslistFactory;
        parent::__construct($context);
    }

    /**
     * action get data
     *
     * @return json
     */
    public function execute()
    {
        $params = $this->getrequest()->getParams();
        $itemId = $params['itemId'];
        $result = $this->_resultJsonFactory->create();
        $orderItem = $this->saleslistFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('order_item_id', $itemId)
                                            ->getFirstitem();
        if ($orderItem->getShippingMethod()) {
            $shippingMethodCode = $orderItem->getShippingMethod();
            $shippingMethodCode = substr($shippingMethodCode, 0, strpos($shippingMethodCode, "_"));

            $params = new \Magento\Framework\DataObject(
                [
                    'method' => $shippingMethodCode
                ]
            );
            $carrierModel = $this->carrierFactory->createIfActive($shippingMethodCode);

            if (is_array($carrierModel->getContentTypes($params))) {
                return $result->setData($carrierModel->getContentTypes($params));
            }
            return $result->setData("");
        }
    }
}
