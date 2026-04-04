<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../../../bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get(\Magento\Framework\App\State::class);
$state->setAreaCode('adminhtml');

$productRepository = $obj->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$csvFile = 'app/code/Ahy/UpdateProductsCSV/csv-files/categories/product_categories.csv';

if (!file_exists($csvFile)) {
    exit("CSV file not found.\n");
}

$rows = array_map('str_getcsv', file($csvFile));
$header = array_shift($rows);

foreach ($rows as $row) {
    $data = array_combine($header, $row);

    if (empty($data['sku'])) {
        echo "Missing SKU in a row.\n";
        continue;
    }

    $sku = trim($data['sku']);

    $categoryIds = [];
    foreach ($data as $key => $value) {
        if (!in_array($key, ['Category1', 'Category2'])) {
            continue; // Skip columns other than Category1 or Category2
        }
        $value = trim($value);
        if (is_numeric($value)) {
            $categoryIds[] = (int)$value;
        }
    }

    if (empty($categoryIds)) {
        echo "No valid category IDs for SKU: $sku\n";
        continue;
    }

    try {
    $product = $productRepository->get($sku);

    // Assign new category IDs
    $product->setCategoryIds($categoryIds);
    $product->setData('save_rewrites_history', false);
    $product->setData('url_key_create_redirect', false);

    try {
        $productRepository->save($product);
        echo "Assigned categories [" . implode(',', $categoryIds) . "] to SKU: $sku\n";
        
    } catch (\Magento\Framework\Exception\LocalizedException $e) {
        if (strpos($e->getMessage(), 'URL key for specified store already exists') !== false) {
            $fallbackUrlKey = strtolower(preg_replace('/[^a-z0-9]+/', '-', $sku));
            $fallbackUrlKey = trim($fallbackUrlKey, '-'); // remove leading/trailing dashes
            $product->setUrlKey($fallbackUrlKey);

            try {
                $productRepository->save($product);
                echo "Assigned categories and resolved URL key conflict for SKU: $sku (URL key set to: $fallbackUrlKey)\n";
            } catch (\Exception $e2) {
                echo "Failed second save for SKU $sku: " . $e2->getMessage() . "\n";
            }
        } else {
            echo "Skipped SKU $sku due to localized error: " . $e->getMessage() . "\n";
        }
    }
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        echo "Product not found for SKU: $sku\n";
    } catch (\Exception $e) {
        echo "Error updating SKU $sku: " . $e->getMessage() . "\n";
    }

}
