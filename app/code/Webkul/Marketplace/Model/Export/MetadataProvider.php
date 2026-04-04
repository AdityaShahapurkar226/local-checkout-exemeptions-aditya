<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory;
use Webkul\Marketplace\Helper\Orders as HelperOrders;

class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var HelperOrders
     */
    protected $helperOrders;
   
    /**
     * Constructor
     *
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param HelperData $helper
     * @param CollectionFactory $collectionFactory
     * @param HelperOrders $helperOrders
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        HelperData $helper,
        CollectionFactory $collectionFactory,
        HelperOrders $helperOrders,
        $dateFormat = 'M j, Y h:i:s A',
        array $data = []
    ) {
        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
        $this->_helper = $helper;
        $this->_collectionFactory = $collectionFactory;
        $this->helperOrders = $helperOrders;
    }

    /**
     * Function to get Row Data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
       
        $totalShipping = array_search('total_shipping', $fields);
        $commission = array_search('total_commission', $fields);
        $actualSellerAmount = array_search('actual_seller_amount', $fields);
        $marketplaceOrders = $this->_collectionFactory->create()
                ->addFieldToFilter('order_id', $document['order_id'])
                ->addFieldToFilter('seller_id', $document['seller_id']);
        $taxToSeller = $this->_helper->getConfigTaxManage();
        $totalshipping = 0;
        $resultData = $this->getOrderItemTaxShipping(
            $marketplaceOrders,
            $document,
            $taxToSeller,
            $totalshipping
        );
        $taxToSeller = $resultData['taxToSeller'];
        $totalshipping = $resultData['totalshipping'];
        $item = $resultData['item'];
    
        foreach ($fields as $column) {
            if (isset($options[$column])) {
               
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } else {
                $row[] = $document->getCustomAttribute($column)->getValue();
                if ($column == 'total_shipping') {
                    $row[$totalShipping] = $item['total_shipping'];
                   
                } elseif ($column == 'total_commission') {
                    $taxAmount = $item['total_tax'];
                    $vendorTaxAmount = 0;
                    $adminTaxAmount = 0;
                    if ($taxToSeller) {
                        $vendorTaxAmount = $taxAmount;
                    } else {
                        $adminTaxAmount = $taxAmount;
                    }
                    if ($item['total_commission'] * 1) {
                        $row[$commission] = $item['total_commission'] + $adminTaxAmount;
                    }
                    
                } elseif ($column == 'actual_seller_amount') {
                    $taxAmount = $item['total_tax'];
                    $vendorTaxAmount = 0;
                    $adminTaxAmount = 0;
                    if ($taxToSeller) {
                        $vendorTaxAmount = $taxAmount;
                    } else {
                        $adminTaxAmount = $taxAmount;
                    }
    
                    if ($item['actual_seller_amount'] * 1) {
                        $taxShippingTotal = $vendorTaxAmount + $totalshipping;
                        $row[$actualSellerAmount] = $item['actual_seller_amount'] + $taxShippingTotal;
                    } else {
                        if ($totalshipping * 1) {
                            $row[$actualSellerAmount] = $totalshipping;
                        }
                    }
                }
            }
        }

        return $row;
    }
    /**
     * Get order item shipping tax
     *
     * @param object $marketplaceOrders
     * @param array $item
     * @param bool $taxToSeller
     * @param float $totalshipping
     * @return array
     */
    public function getOrderItemTaxShipping($marketplaceOrders, $item, $taxToSeller, $totalshipping)
    {
        $marketplaceOrdersData = [];
        foreach ($marketplaceOrders as $tracking) {
            $marketplaceOrdersData = $tracking->getData();
            $taxToSeller = $tracking['tax_to_seller'];
        }
        if ($item['is_shipping'] == 1) {
            foreach ($marketplaceOrders as $tracking) {
                $shippingamount = $marketplaceOrdersData['shipping_charges'];
                $refundedShippingAmount = $marketplaceOrdersData['refunded_shipping_charges'];
                $totalshipping = $shippingamount - $refundedShippingAmount;
                if ($totalshipping * 1) {
                    $item['total_shipping'] = $totalshipping;
                }
            }
        }
        return [
            'taxToSeller' => $taxToSeller,
            'totalshipping' => $totalshipping,
            'item' => $item
        ];
    }
}
