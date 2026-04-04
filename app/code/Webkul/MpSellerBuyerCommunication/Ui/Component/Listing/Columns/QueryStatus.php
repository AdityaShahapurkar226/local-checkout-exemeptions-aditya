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

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class QueryStatus extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var storemanager
     */
    protected $_storeManager;

    /**
     * @param \Webkul\MpSellerBuyerCommunication\Helper\Data $dataHelper
     * @param ContextInterface                               $context
     * @param UiComponentFactory                             $uiComponentFactory
     * @param StoreManagerInterface                          $storemanager
     * @param array                                          $components
     * @param array                                          $data
     */
    public function __construct(
        \Webkul\MpSellerBuyerCommunication\Helper\Data $dataHelper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storemanager,
        array $components = [],
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_storeManager = $storemanager;
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
                $item[$fieldName] = $this->_dataHelper->getQueryStatusname($item['query_status']);
            }
        }
        return $dataSource;
    }
}
