<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductStockAlert
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\CustomerData;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Wishlist\Block\Customer\Sidebar;
use Magento\Wishlist\CustomerData\Wishlist as CoreWishList;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;

class Wishlist extends CoreWishList
{
    /**
     * @var ItemResolverInterface
     */
    protected $itemResolverInterface;

    /**
     * Wishlist constructor.
     * @param Data $wishlistHelper
     * @param Sidebar $block
     * @param ImageFactory $imageHelperFactory
     * @param ViewInterface $view
     * @param ItemResolverInterface $itemResolver
     */
    public function __construct(
        Data $wishlistHelper,
        Sidebar $block,
        ImageFactory $imageHelperFactory,
        ViewInterface $view,
        ItemResolverInterface $itemResolver
    ) {
        $this->itemResolverInterface = $itemResolver;
        parent::__construct($wishlistHelper, $block, $imageHelperFactory, $view, $itemResolver);
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $counter = $this->getCounter();
        //--Core Function--//
        $itemData = [
            'counter' => $counter,
            'items' => $counter ? $this->getItems() : [],
        ];
        //---Bss Logic---//
        if (!empty($itemData['items'])) {
            foreach ($itemData['items'] as $key => $item) {
                $productId = (int)$item['product_id'];
                if ($productId) {
                    if ($item['product_is_saleable_and_visible'] == true && !$item['is_available']) {
                        $itemData['items'][$key]['product_is_saleable_and_visible'] = false;
                    }
                }
            }
        }
        return $itemData;
    }

    /**
     * @inheritDoc
     */
    protected function getItemData(Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        return [
            'image' => $this->getImageData($this->itemResolverInterface->getFinalProduct($wishlistItem)),
            'product_sku' => $product->getSku(),
            'product_id' => $product->getId(),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_price' => $this->block->getProductPriceHtml(
                $product,
                'wishlist_configured_price',
                \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem),
            'is_available' => $product->isAvailable()
        ];
    }
}
