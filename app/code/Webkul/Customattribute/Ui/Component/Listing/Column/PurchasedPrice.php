<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Customattribute\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;

class PurchasedPrice extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param CollectionFactory $orderCollection
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Weee\Helper\Data $weeData
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $currencyInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        CollectionFactory $orderCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Weee\Helper\Data $weeData,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $currencyInterface,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->orderCollection = $orderCollection;
        $this->orderRepository = $orderRepository;
        $this->weeeData = $weeData;
        $this->currencyFactory = $currencyFactory;
        $this->_priceHelper = $priceHelper;
        $this->currencyInterface = $currencyInterface;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            
            foreach ($dataSource['data']['items'] as &$item) {
                $rewardAmountData = $this->orderCollection->create()
                ->addFieldToFilter(
                    'main_table.order_id',
                    $item['order_id']
                )->addFieldToFilter(
                    'main_table.seller_id',
                    $item['seller_id']
                );
                $salesCreditmemoItem = $this->orderCollection->create()->getTable('sales_order_item');
                $rewardAmountData->getSelect()->join(
                    $salesCreditmemoItem.' as creditmemo_item',
                    'creditmemo_item.item_id = main_table.order_item_id'
                )->where('creditmemo_item.order_id = '.$item['order_id']);
                $order = $this->orderRepository->get($item['order_id']);
                $store = $order->getStore();
                $weeeTotal = $this->weeeData->getTotalAmounts($rewardAmountData, $store);
                $weeeBaseTotal = $this->weeeData->getBaseTotalAmounts($rewardAmountData, $store);
                
                if (isset($item['tax_to_seller']) && $item['tax_to_seller']==1 &&
                $weeeTotal && $item['purchased_actual_seller_amount']>0) {
                    $price = $item['purchased_actual_seller_amount'] + $weeeTotal;
                    
                    $item[$fieldName] = $this->currencyInterface->format(
                        $price,
                        false,
                        2,
                        null,
                        $item['order_currency_code']
                    );
                   
                } else {
                    $price = $item['purchased_actual_seller_amount'] ;
                    $item[$fieldName] = $this->currencyInterface->format(
                        $price,
                        false,
                        2,
                        null,
                        $item['order_currency_code']
                    );
                }
            }
        }

        return $dataSource;
    }
}
