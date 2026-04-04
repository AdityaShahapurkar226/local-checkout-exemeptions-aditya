<?php
declare(strict_types=1);

namespace Ahy\ThemeCustomization\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class CartCarousel extends Template
{
    private ResourceConnection $resource;
    private StoreManagerInterface $storeManager;
    private CheckoutSession $checkoutSession;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->resource        = $resource;
        $this->storeManager    = $storeManager;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    public function getGalleryMap(): array
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote) return [];

        $connection = $this->resource->getConnection();
        $mediaUrl   = $this->storeManager->getStore()
                          ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $tableMg    = $this->resource->getTableName('catalog_product_entity_media_gallery');
        $tableMgvte = $this->resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');

        $galleryMap = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $itemId   = (int) $item->getId();
            $parentId = (int) $item->getProductId();

            // Get simple child ID from quote item option — most reliable method
            $simpleId  = null;
            $simpleOpt = $item->getOptionByCode('simple_product');
            if ($simpleOpt) {
                $simpleId = (int) $simpleOpt->getValue();
            }

            // Query images — prefer simple child, fall back to parent configurable
            $queryId = $simpleId ?? $parentId;
            $urls    = $this->getProductImageUrls($connection, $tableMg, $tableMgvte, $mediaUrl, $queryId);

            // If simple child had no images, fall back to parent
           if (count($urls) < 2 && $simpleId !== null) {
                $urls = $this->getProductImageUrls($connection, $tableMg, $tableMgvte, $mediaUrl, $parentId);
            }

            // Need at least 2 images for a hover effect
            if (count($urls) < 2) continue;

            // Key by item_id — unique per cart row, matches localStorage item.item_id
            $galleryMap[$itemId] = [
                'main'  => $urls[0],
                'hover' => $urls[1],
            ];
        }

        return $galleryMap;
    }

    /**
     * Fetch image URLs for a product ordered by value_id ASC.
     */
    private function getProductImageUrls(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        string $tableMg,
        string $tableMgvte,
        string $mediaUrl,
        int $productId
    ): array {
        $select = $connection->select()
            ->from(['mg' => $tableMg], ['value'])
            ->join(['mgvte' => $tableMgvte], 'mg.value_id = mgvte.value_id', [])
            ->where('mgvte.entity_id = ?', $productId)
            ->where('mg.media_type = ?', 'image')
            ->order('mg.value_id ASC');

        $values = $connection->fetchCol($select);

        return array_map(
            fn($v) => $mediaUrl . 'catalog/product' . $v,
            $values
        );
    }
}