<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Observer\Frontend;

use Amasty\Mostviewed\Model\Cart\AddProductsByIds;
use Amasty\Mostviewed\Model\Cart\ProductAddingProgressFlag;
use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\Pack\Cart\Discount\GetPacksForCartItem;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPack;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class TotalsCollectAfter implements ObserverInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetPacksForCartItem
     */
    private $getPacksForCartItem;

    /**
     * @var ProductAddingProgressFlag
     */
    private $productAddingProgressFlag;

    public function __construct(
        ConfigProvider $configProvider,
        GetPacksForCartItem $getPacksForCartItem,
        ProductAddingProgressFlag $productAddingProgressFlag
    ) {
        $this->configProvider = $configProvider;
        $this->getPacksForCartItem = $getPacksForCartItem;
        $this->productAddingProgressFlag = $productAddingProgressFlag;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        if ($quote
            && !$this->productAddingProgressFlag->get()
            && !$this->configProvider->isProductsCanBeAddedSeparately()
        ) {
            /** @var QuoteItem $quoteItem */
            foreach ($quote->getAllItems() as $quoteItem) {
                $bundlePackIdOption = $quoteItem->getOptionByCode(AddProductsByIds::BUNDLE_PACK_ID_OPTION);
                if ($bundlePackIdOption && empty($this->getPacksForCartItem->execute($quoteItem))) {
                    $quoteItem->removeOption(AddProductsByIds::BUNDLE_PACK_ID_OPTION);
                    $quoteItem->removeOption(AddProductsByIds::BUNDLE_PACK_OPTION_CODE);
                }
            }
        }
    }
}
