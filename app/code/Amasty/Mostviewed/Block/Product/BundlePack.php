<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Product;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Cart\IsAjaxCartEnabled;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\OptionSource\ApplyCondition;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\ImageFactory as HelperFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Math\FloatComparator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class BundlePack extends \Magento\Catalog\Block\Product\AbstractProduct implements IdentityInterface
{
    public const RELATED_IMAGE_ID = 'related_products_content';
    public const ZERO_DISCOUNT = 0.0;

    /**
     * @var array
     */
    private $bundles = [];

    /**
     * @var string
     */
    // protected $_template = 'Amasty_Mostviewed::bundle/pack.phtml';
    protected $_template = 'Amasty_Mostviewed::bundle/pack.phtml';

    /**
     * @var \Amasty\Mostviewed\Helper\Config
     */
    protected $config;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var HelperFactory
     */
    private $helperFactory;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var GroupValidator
     */
    private $groupValidator;

    /**
     * @var IsAjaxCartEnabled
     */
    private $isAjaxCartEnabled;

    /**
     * @var FloatComparator
     */
    private $floatComparator;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Amasty\Mostviewed\Helper\Config $config,
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        PriceCurrencyInterface $priceCurrency,
        HelperFactory $helperFactory,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        GroupValidator $groupValidator,
        IsAjaxCartEnabled $isAjaxCartEnabled,
        FloatComparator $floatComparator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->packRepository = $packRepository;
        $this->collectionFactory = $collectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->jsonEncoder = $jsonEncoder;
        $this->priceCurrency = $priceCurrency;
        $this->helperFactory = $helperFactory;
        $this->stockHelper = $stockHelper;
        $this->request = $request;
        $this->localeFormat = $localeFormat;
        $this->groupValidator = $groupValidator;
        $this->isAjaxCartEnabled = $isAjaxCartEnabled;
        $this->floatComparator = $floatComparator;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $name = $this->getNameInLayout();
        $position = $this->config->getBlockPosition();

        if (strpos($name, $position) !== false) {
            if ($this->isBundlePacksExists()) {
                return $this->getParentHtml();
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isCheckoutPage()
    {
        return $this->request->getFullActionName() === 'checkout_cart_index';
    }

    /**
     * @return string
     */
    public function getParentHtml()
    {
        return parent::toHtml();
    }

    /**
     * @return bool
     */
    public function isBundlePacksExists()
    {
        $product = $this->getProduct();

        if (!$product || !$product->isSaleable()) {
            return false;
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $bundles = $this->packRepository->getPacksByParentProductsAndStore(
            [$product->getId()],
            $storeId
        );

        if ($bundles) {
            /** @var PackInterface $pack */
            foreach ($bundles as $key => $pack) {
                if (!$this->groupValidator->validate($pack)) {
                    unset($bundles[$key]);
                }
            }

            if ($bundles) {
                $this->bundles = $bundles;

                return true;
            }

        }

        return false;
    }

    public function getImageModel(Product $product): Image
    {
        return $this->helperFactory->create()
            ->init($product, self::RELATED_IMAGE_ID);
    }

    /**
     * @param string|array $childIds
     *
     * @return array
     */
    public function getProductItems($childIds)
    {
        if (!is_array($childIds)) {
            $childIds = explode(',', $childIds);
        }

        /** @var ProductCollection $products */
        $collection = $this->collectionFactory->create()
            ->addIdFilter($childIds)
            ->addFieldToFilter('type_id', ['nin' => [Grouped::TYPE_CODE, 'giftcard']])
            ->addIdFilter($this->getProduct()->getId(), true);

        $collection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        $this->_addProductAttributesAndPrices($collection);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $this->stockHelper->addIsInStockFilterToCollection($collection);
        $productItems = [];

        foreach ($collection as $product) {
            $product->setDoNotUseCategoryId(true);
            $productItems[$product->getId()] = $product;
        }

        /* set correct sort order*/
        foreach ($childIds as $key => $childId) {
            if (isset($productItems[$childId])) {
                $childIds[$key] = $productItems[$childId];
            } else {
                unset($childIds[$key]);
            }
        }

        if (!empty($childIds)) {
            array_unshift($childIds, $this->getProduct());//add main product as first
        }

        return $childIds;
    }

    public function isPackVisible(PackInterface $pack, array $productItems): bool
    {
        $isPackVisible = !empty($productItems);

        if ($pack->getApplyCondition() === ApplyCondition::ALL_PRODUCTS) {
            $childIds = explode(',', $pack->getProductIds());
            $actualCountProducts = count($productItems) - 1; // -1 - remove main product for get count childs
            $expectedCountProducts = count($childIds);
            $isPackVisible = $isPackVisible && $actualCountProducts === $expectedCountProducts;
        }

        return $isPackVisible;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode(
            [
                'url' => $this->getUrl('ammostviewed/cart/add'),
                'isAjaxCartEnabled' => (int) $this->isAjaxCartEnabled->execute($this->getRequest()->getFullActionName())
            ]
        );
    }

    /**
     * @param PackInterface $pack
     * @param array|ProductCollection $items
     *
     * @return array
     */
    public function getPackJsonConfig(PackInterface $pack, $items)
    {
        $data = [
            'product_id' => (int)$this->getProduct()->getId(),
            'discount_amount' => (float)$pack->getDiscountAmount(),
            'parent_info' => [
                'price' => (float)$this->getProduct()->getPriceInfo()
                    ->getPrice('final_price')->getAmount()->getValue(),
                'qty' => 1
            ],
            'discount_type' => (int)$pack->getDiscountType(),
            'apply_for_parent' => (bool)$pack->getApplyForParent(),
            'apply_only_for_all' => $pack->getApplyCondition() === ApplyCondition::ALL_PRODUCTS,
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'products' => [],
            'conditional_discounts' => []
        ];

        foreach ($items as $key => $item) {
            if ($key === 0) {
                continue; //skip parent product
            }

            $data['products'][$item->getId()] = [
                'price' => (float) $item->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                'old_price' => (float) $item->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),
                'qty' => $pack->getChildProductQty($item->getId()),
                'discount_amount' => $pack->getChildProductDiscount((int) $item->getId())
            ];
        }

        $conditionalDiscounts = $pack->getExtensionAttributes()->getConditionalDiscounts() ?: [];
        foreach ($conditionalDiscounts as $conditionalDiscount) {
            $data['conditional_discounts'][$conditionalDiscount->getNumberItems()]
                = $conditionalDiscount->getDiscountAmount();
        }

        return $data;
    }

    public function getProductDiscount(array $config, int $itemId, bool $isParent): string
    {
        if ($isParent) {
            $productInfo = $config['parent_info'] ?? null;
        } else {
            $productInfo = $config['products'][$itemId] ?? null;
        }

        if ($productInfo === null) {
            return '';
        }

        $discountAmount = $this->getDiscountAmount($productInfo, $config);

        if ($this->floatComparator->equal($discountAmount, self::ZERO_DISCOUNT)) {
            return '';
        }

        if ($config['discount_type'] == DiscountType::FIXED) {
            $result = '-' . $this->priceOutput($discountAmount);
        } else {
            $result = $discountAmount . '%';
        }

        if ($isParent && !$config['apply_for_parent']) {
            $result = '';
        }

        return $result;
    }

    /**
     * @param $price
     *
     * @return string
     */
    public function priceOutput($price)
    {
        return $this->priceCurrency->format($price);
    }

    /**
     * @param $config
     *
     * @return array
     */
    public function getDiscountResult($config)
    {
        $parentInfo = $config['parent_info'];
        $oldPrice = $parentInfo['price'];
        $newPrice = $config['apply_for_parent'] ? $this->applyDiscount($parentInfo, $config) : $oldPrice;
        foreach ($config['products'] as $productInfo) {
            $oldPrice += $productInfo['price'] * $productInfo['qty'];
            $newPrice += $this->applyDiscount($productInfo, $config);
        }

        return [
            'final_price' => $newPrice,
            'discount' => $oldPrice - $newPrice
        ];
    }

    /**
     * @param $productInfo
     * @param $config
     *
     * @return float|int
     */
    private function applyDiscount($productInfo, $config)
    {
        $price = $productInfo['price'];
        $discountAmount = $this->getDiscountAmount($productInfo, $config);
        if ($config['discount_type'] == DiscountType::FIXED) {
            $price = ($price >= $discountAmount) ? $price - $discountAmount : 0;
        } else {
            $price = $price - $this->priceCurrency->round($price * $discountAmount / 100);
        }
        $price *= $productInfo['qty'];

        return $price;
    }

    private function getDiscountAmount(array $productInfo, array $config): float
    {
        if ($config['discount_type'] === DiscountType::CONDITIONAL) {
            $discountAmount = 0;
            $itemsQty = count($config['products']) + 1; // 1 - is main product
            foreach ($config['conditional_discounts'] as $numberItems => $conditionalDiscount) {
                if ($itemsQty < $numberItems) {
                    break;
                }
                $discountAmount = $conditionalDiscount;
            }
        } else {
            $discountAmount = $productInfo['discount_amount'] ?? $config['discount_amount'];
        }

        return $discountAmount;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function encode($data)
    {
        return $this->jsonEncoder->encode($data);
    }

    /**
     * @return array
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = [];

        if ($this->isBundlePacksExists()) {
            foreach ($this->bundles as $bundle) {
                $identities[] = Pack::CACHE_TAG . '_' . $bundle->getPackId();
                foreach (explode(',', $bundle->getProductIds()) as $productId) {
                    $identities[] = Product::CACHE_TAG . '_' . $productId;
                }
            }
        }

        return $identities;
    }

    /**
     * @param $bundles
     *
     * @return $this
     */
    public function setBundles($bundles)
    {
        $this->bundles = $bundles;

        return $this;
    }

    /**
     * @param $product
     *
     * @return $this
     */
    public function setProduct($product)
    {
        $this->setData('product', $product);

        return $this;
    }
}
