<?php

namespace Ewave\ExtendedBundleProduct\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset\Configurable as ConfigurableBlock;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\ObjectManager;

class Configurable extends ConfigurableBlock
{
    /**
     * @var array
     */
    private $allowProductsCache = [];

    /**
     * @var Quote
     */
    protected $sessionQuote;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\ConfigurableProduct\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param array $data
     * @param Format|null $localeFormat
     * @param Session|null $customerSession
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices|null $variationPrices
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        array $data = [],
        Format $localeFormat = null,
        Session $customerSession = null,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices = null,
        Quote $sessionQuote = null
    ) {
        $data['productHelper'] = $catalogProduct;
        $this->sessionQuote = $sessionQuote ?: ObjectManager::getInstance()->get(Quote::class);
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $data,
            $localeFormat,
            $customerSession,
            $variationPrices
        );
    }

    /**
     * @return bool
     */
    public function hasBundleConfigurableProducts()
    {
        $product = $this->getProduct();
        if ($product->getTypeId() == Bundle::TYPE_CODE) {
            /** @var Bundle $bundleProduct */
            $bundleProduct = $product->getTypeInstance();
            $selections = $bundleProduct->getSelectionsCollection(
                $bundleProduct->getOptionsIds($product),
                $product
            );

            foreach ($selections as $selection) {
                if ($selection->getTypeId() == ConfigurableType::TYPE_CODE) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getProduct()
    {
        $this->unsAllowProducts();
        return $this->getSelection();
    }

    public function getAllowProducts()
    {
        $product = $this->getProduct();
        if (!array_key_exists($product->getId(), $this->allowProductsCache)) {
            $this->allowProductsCache[$product->getId()] = [];
            $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
            $allowedProducts = parent::getAllowProducts();
            foreach ($allowedProducts as $subProduct) {
                if ($subProduct->isDisabled()) {
                    continue;
                }

                if ($skipSaleableCheck || $this->stockRegistry->getStockItem($subProduct->getId())->getIsInStock()) {
                    $this->allowProductsCache[$product->getId()][] = $subProduct;
                }
            }
        }

        return $this->allowProductsCache[$product->getId()];
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getProduct()->getTypeId() == ConfigurableProduct::TYPE_CODE) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return mixed|null
     */
    public function getOptionJsonData()
    {
        $quoteItemOptionId = $this->getRequest()->getParams();
        if (isset($quoteItemOptionId['id']) && 'configureQuoteItems' === $this->getRequest()->getActionName()) {
            $quoteItem = $this->sessionQuote->getQuote()->getItemById((int)$quoteItemOptionId['id']);
            if ($quoteItem) {
                $infoBuyRequestQuoteItemOption = $quoteItem->getOptionByCode('info_buyRequest');
                if ($infoBuyRequestQuoteItemOption) {
                    return $infoBuyRequestQuoteItemOption->getData()['value'];
                }
            }
        }

        return null;
    }

    /**
     * @param array $array
     * @return string
     */
    public function getJson(array $array)
    {
        return $this->jsonEncoder->encode($array);
    }
}
