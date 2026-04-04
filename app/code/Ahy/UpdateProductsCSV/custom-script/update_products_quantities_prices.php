<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../../../bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get(\Magento\Framework\App\State::class);
$state->setAreaCode('adminhtml');

$productRepository = $obj->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$stockRegistry = $obj->get(\Magento\CatalogInventory\Api\StockRegistryInterface::class);

$csvFile = 'app/code/Ahy/UpdateProductsCSV/csv-files/quantities/product-quantities.csv';

if (!file_exists($csvFile)) {
    exit("CSV file not found.\n");
}

$rows = array_map('str_getcsv', file($csvFile));
$header = array_shift($rows);

foreach ($rows as $row) {
    $data = array_combine($header, $row);

    // Skip rows without a valid SKU
    if (empty($data['sku'])) {
        continue;
    }

    $sku = trim($data['sku']);
    $price = isset($data['price']) ? (float) $data['price'] : 0;
    $qty = isset($data['quantity']) ? (float) $data['quantity'] : 0;

    try {
        $product = $productRepository->get($sku);
        // $product->setPrice($price);
        $productRepository->save($product);

        $stockItem = $stockRegistry->getStockItemBySku($sku);

        if ($stockItem) {
            $stockItem->setManageStock(true);
            $stockItem->setUseConfigManageStock(false);
            $stockItem->setQty($qty);
            $stockItem->setIsInStock($qty > 0 ? 1 : 0);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);
        }

        echo "Updated SKU: $sku\n";
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        echo "SKU not found: $sku\n";
    } catch (Exception $e) {
        echo "Error updating SKU: $sku — " . $e->getMessage() . "\n";
    }
}