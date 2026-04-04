<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Ui\Component\Listing\Columns\Frontend;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Marketplace\Helper\Data as HelperData;

class AttributeAction extends Column
{
    public const URL_EDIT = 'marketplace/product_attribute/new';
    public const URL_DELETE = 'marketplace/product_attribute/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

   /**
    * @param ContextInterface $context
    * @param UiComponentFactory $uiComponentFactory
    * @param UrlInterface $urlBuilder
    * @param HelperData $helperData
    * @param array $components
    * @param array $data
    */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        HelperData $helperData,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helperData = $helperData;
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
        $mpHelper = $this->helperData;
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['attribute_id'])) {
                    $urlEntityParamName = $this->getData('config/urlEntityParamName')?:'attribute_id';
                     $displayStatus =  $mpHelper->checkIfSellerAttribute($item['attribute_id']);
                    if (!empty($displayStatus)) {
                        $item[$this->getData('name')] = [
                            'edit' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::URL_EDIT,
                                    [
                                        $urlEntityParamName => $item['attribute_id']
                                    ]
                                ),
                                'label' => __("Edit")
                            ],
                            'delete' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::URL_DELETE,
                                    [
                                        $urlEntityParamName => $item['attribute_id']
                                    ]
                                ),
                                'label' => __('Delete')
                            ]
                        ];
                    }
                   
                }
            }
        }

        return $dataSource;
    }
}
