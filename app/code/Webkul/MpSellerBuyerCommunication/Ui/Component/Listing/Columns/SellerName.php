<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Model\Customer;

/**
 * Class SellerName for the grid on the admin side.
 */
class SellerName extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * Contstructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Customer $customer
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Customer $customer,
        array $components = [],
        array $data = []
    ) {
        $this->customer = $customer;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['seller_id']) && $item['seller_id']!=0) {
                    $item[$fieldName] = "<a href='".$this->urlBuilder->getUrl(
                        'customer/index/edit',
                        ['id' => $item['seller_id']]
                    )."' target='blank' title='".__(
                        'View Customer'
                    )."'>".$this->loadCustomer($item).'</a>';
                }
            }
        }

        return $dataSource;
    }

    /**
     * Load Customer Name
     *
     * @param object $item
     * @return void
     */
    public function loadCustomer($item)
    {
        return $this->customer->load($item['seller_id'])->getName();
    }
}
