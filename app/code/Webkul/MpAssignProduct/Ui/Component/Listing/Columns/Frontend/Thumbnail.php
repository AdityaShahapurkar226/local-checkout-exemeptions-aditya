<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Ui\Component\Listing\Columns\Frontend;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Model\ProductFactory;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var ProductFactory
     */
    protected $productModel;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /** @var  */

    /**
     * Initialization
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductFactory $productModel
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductFactory $productModel,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->productModel = $productModel;
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
        $store = $this->storeManager->getStore(
            $this->context->getFilterParam(
                'store_id',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )
        );
        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                
                $product = $this->productModel->create()->load($item['product_id']);
                if ($product->getThumbnail()) {
                    $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail')
                    ->setImageFile($product->getThumbnail());
                    $imageUrl = $imageHelper->getUrl();
                } else {
                    $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail')
                    ->setImageFile($this->imageHelper->getDefaultPlaceholderUrl('thumbnail'));
                    $imageUrl = $this->imageHelper->getDefaultPlaceholderUrl('thumbnail');
                }
                
                $item[$fieldName.'_src'] = $imageUrl;
                $item[$fieldName.'_alt'] = $imageHelper->getLabel();
                $origImageHelper = $this->imageHelper->init(
                    $product,
                    'product_base_image'
                );
                $item[$fieldName.'_orig_src'] = $origImageHelper->getUrl();
                $item[$fieldName.'_name'] = $product->getName();
                if (!empty($product->getDescription())) {
                    $item[$fieldName.'_description'] = strip_tags(
                        htmlspecialchars_decode($product->getDescription())
                    );
                }

            }
        }

        return $dataSource;
    }

    /**
     * Get Image alt
     *
     * @param [type] $row
     * @return void
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;

        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
