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
use Magento\Catalog\Model\Product;

/**
 * Class Productlink for the grid on the admin side.
 */
class Productlink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Product
     */
    protected $product;

    /**
     * Contstructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ProductFactory $product,
        array $components = [],
        array $data = []
    ) {
        $this->product = $product;
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
                if (isset($item['product_id']) && $item['product_id']!=0) {
                    $product = $this->loadProduct($item);
                    if ($product->getEntityId()) {
                        $item[$fieldName] = "<a href='".$this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $item['product_id']]
                        )."' target='blank' title='".__(
                            'View Product'
                        )."'>".$product->getName().'</a>';
                    } else {
                        $item[$fieldName] = $item['product_name'];
                    }
                } else {
                    $item[$fieldName] = __('None');
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
    public function loadProduct($item)
    {
        return $this->product->create()->load($item['product_id']);
    }
}
