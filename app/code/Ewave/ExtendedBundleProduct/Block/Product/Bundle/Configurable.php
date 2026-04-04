<?php

namespace Ewave\ExtendedBundleProduct\Block\Product\Bundle;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableBlock;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Configurable extends ConfigurableBlock
{
    /**
     * @var Format
     */
    private $localeFormat;

    /**
     * @var array
     */
    private $allowProductsCache = [];

    /**
     * Configurable constructor.
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
     * @param ConfigurableProduct\Variations\Prices|null $variationPrices
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
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices = null
    ) {
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(Format::class);
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
     * Retrieve selection product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $this->unsAllowProducts();
        return $this->getSelection();
    }

    /**
     * {@inheritdoc}
     */
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
     * @return array
     */
    protected function getOptionPrices()
    {
        $prices = parent::getOptionPrices();
        foreach ($this->getAllowProducts() as $product) {
            if (isset($prices[$product->getId()])) {
                // reformat tier prices
                $tierPrices = $product->getPriceInfo()
                    ->getPrice(\Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE)
                    ->getTierPriceList();
                foreach ($tierPrices as &$tierPriceInfo) {
                    /** @var \Magento\Framework\Pricing\Amount\Base $price */
                    $price = $tierPriceInfo['price'];

                    $priceBaseAmount = $price->getBaseAmount();
                    $priceValue = $price->getValue();

                    $tierPriceInfo['prices'] = [
                        'oldPrice' => [
                            'amount' => $this->localeFormat ? $this->localeFormat->getNumber($priceBaseAmount) : $priceBaseAmount
                        ],
                        'basePrice' => [
                            'amount' => $this->localeFormat ? $this->localeFormat->getNumber($priceBaseAmount) : $priceBaseAmount
                        ],
                        'finalPrice' => [
                            'amount' => $this->localeFormat ? $this->localeFormat->getNumber($priceValue) : $priceValue
                        ]
                    ];
                }
                $prices[$product->getId()]['tierPrices'] = $tierPrices;
            }
        }
        return $prices;
    }

    /**
     * @param array $array
     * @return string
     */
    public function getJson(array $array)
    {
        return $this->jsonEncoder->encode($array);
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
}
