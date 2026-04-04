<?php
declare(strict_types=1);

namespace Ahy\ThemeCustomization\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class HoverMapSection implements SectionSourceInterface
{
    private ResourceConnection $resource;
    private StoreManagerInterface $storeManager;
    private CheckoutSession $checkoutSession;

    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession
    ) {
        $this->resource       = $resource;
        $this->storeManager   = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    public function getSectionData(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $logger = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('HoverMap quoteId=' . $quote->getId() . ' itemCount=' . count($quote->getAllVisibleItems()));
                if (!$quote) return ['map' => []];

        $connection = $this->resource->getConnection();
        $mediaUrl   = $this->storeManager->getStore()
                          ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $tableMg = $this->resource->getTableName('catalog_product_entity_media_gallery');

        $galleryMap = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $itemId   = (int) $item->getId();
            $parentId = (int) $item->getProductId();

            $simpleId  = null;
            $simpleOpt = $item->getOptionByCode('simple_product');
            if ($simpleOpt) {
                $simpleId = (int) $simpleOpt->getValue();
            }

            $queryId = $simpleId ?? $parentId;
            $urls    = $this->getProductImageUrls($connection, $tableMg, $mediaUrl, $queryId);

            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)
                ->debug("HoverMap urls: itemId={$itemId} queryId={$queryId} count=" . count($urls));

            if (count($urls) < 2 && $simpleId !== null) {
                $colorSlugs = $this->getColorSlug($connection, $item);
                $urls = $this->getParentImageUrlsByColor(
                    $connection, $tableMg, $mediaUrl, $parentId, $colorSlugs
                );
            }

            if (count($urls) < 2) continue;

            $galleryMap[$itemId] = [
                'main'  => $urls[0],
                'hover' => $urls[1],
            ];
        }

        return ['map' => $galleryMap];
    }

    private function getProductImageUrls(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        string $tableMg,
        string $mediaUrl,
        int $productId
    ): array {
        $tableMgv = $this->resource->getTableName('catalog_product_entity_media_gallery_value');

        $select = $connection->select()
            ->from(['mg' => $tableMg], ['value'])
            ->join(['mgv' => $tableMgv], 'mg.value_id = mgv.value_id', [])
            ->where('mgv.entity_id = ?', $productId)
            ->where('mgv.disabled = ?', 0)
            ->where('mg.media_type = ?', 'image')
            ->order('mgv.position ASC');

        $values = $connection->fetchCol($select);

        return array_map(
            fn($v) => $mediaUrl . 'catalog/product' . $v,
            $values
        );
    }

private function getColorSlug(
    \Magento\Framework\DB\Adapter\AdapterInterface $connection,
    \Magento\Quote\Model\Quote\Item $item
): array {  // now returns array of slugs
    $buyRequest = $item->getBuyRequest();
    $superAttr  = $buyRequest ? $buyRequest->getSuperAttribute() : [];
    $colorAttributeId = 93;

    if (empty($superAttr) || !isset($superAttr[$colorAttributeId])) {
        return [];
    }

    $optionValueId = (int) $superAttr[$colorAttributeId];
    $label = $connection->fetchOne(
        "SELECT value FROM eav_attribute_option_value WHERE option_id = ? AND store_id = 0",
        [$optionValueId]
    );

    if (!$label) return [];

    $normalized = strtolower(trim($label));
    return [
        '_' . str_replace([' ', '-'], '_', $normalized) . '_',  // _carbon_grey_
        '-' . str_replace([' ', '_'], '-', $normalized) . '-',  // -carbon-grey-
    ];
}

private function getParentImageUrlsByColor(
    \Magento\Framework\DB\Adapter\AdapterInterface $connection,
    string $tableMg,
    string $mediaUrl,
    int $parentId,
    array $colorSlugs  // changed from string to array
): array {
    if (empty($colorSlugs)) return [];

    $tableMgv = $this->resource->getTableName('catalog_product_entity_media_gallery_value');

    // Build OR conditions for each slug variant
    $conditions = [];
    foreach ($colorSlugs as $slug) {
        $conditions[] = $connection->quoteInto('mg.value LIKE ?', '%' . $slug . '%');
    }
    $whereColor = implode(' OR ', $conditions);

    $select = $connection->select()
        ->from(['mg' => $tableMg], ['value'])
        ->join(['mgv' => $tableMgv], 'mg.value_id = mgv.value_id', [])
        ->where('mgv.entity_id = ?', $parentId)
        ->where('mgv.disabled = ?', 0)
        ->where('mg.media_type = ?', 'image')
        ->where($whereColor)
        ->order('mgv.position ASC')
        ->limit(2);

    $values = $connection->fetchCol($select);

    return array_map(
        fn($v) => $mediaUrl . 'catalog/product' . $v,
        $values
    );
}
}