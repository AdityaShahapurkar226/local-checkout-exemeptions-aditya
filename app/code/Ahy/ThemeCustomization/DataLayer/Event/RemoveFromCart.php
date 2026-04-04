<?php

declare(strict_types=1);

namespace Ahy\ThemeCustomization\DataLayer\Event;

use Yireo\GoogleTagManager2\DataLayer\Event\RemoveFromCart as OriginalRemoveFromCart;
use Magento\Quote\Api\Data\CartItemInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CartItemDataMapper;

class RemoveFromCart extends OriginalRemoveFromCart
{
    private CartItemDataMapper $cartItemDataMapper;
    private CartItemInterface $cartItem;

    /**
     * @param CartItemDataMapper $cartItemDataMapper
     */
    public function __construct(CartItemDataMapper $cartItemDataMapper)
    {
        $this->cartItemDataMapper = $cartItemDataMapper;
    }


    /**
     * @return array
     */
    public function get(): array
    {
        // Check if the SKU includes 'FREE'
        if (strpos($this->cartItem->getSku(), 'FREE') !== false) {
            // If the SKU contains 'FREE', return an empty array to ignore it
            return [];
        }

        $cartItemData = $this->cartItemDataMapper->mapByCartItem($this->cartItem);

        return [
            'event' => 'remove_from_cart',
            'ecommerce' => [
                'items' => [$cartItemData]
            ]
        ];
    }

    /**
     * @param CartItemInterface $cartItem
     * @return RemoveFromCart
     */
    public function setCartItem(CartItemInterface $cartItem): RemoveFromCart
    {
        $this->cartItem = $cartItem;
        return $this;
    }
}
