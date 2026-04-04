<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../../../bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get(\Magento\Framework\App\State::class);
$state->setAreaCode('adminhtml');

$resource = $obj->get(\Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();

$fromId = 310316;
$toId = 311368;

// Disable foreign key checks
$connection->query('SET FOREIGN_KEY_CHECKS=0;');

$tablesWithEntityId = [
    'catalog_product_entity',
    'catalog_product_entity_datetime',
    'catalog_product_entity_decimal',
    'catalog_product_entity_int',
    'catalog_product_entity_text',
    'catalog_product_entity_varchar',
];

$tablesWithProductId = [
    'catalog_category_product',
    'cataloginventory_stock_item',
    'cataloginventory_stock_status',
    'catalog_product_website',
];

$tablesWithParentChild = [
    'catalog_product_relation' => ['child_id', 'parent_id'],
    'catalog_product_super_link' => ['product_id', 'parent_id'],
];

$tablesWithProductIdOnly = [
    'catalog_product_super_attribute',
];

// Delete from entity_id based tables
foreach ($tablesWithEntityId as $table) {
    $tableName = $resource->getTableName($table);
    $connection->query("DELETE FROM $tableName WHERE entity_id BETWEEN $fromId AND $toId");
}

// Delete from product_id based tables
foreach ($tablesWithProductId as $table) {
    $tableName = $resource->getTableName($table);
    $connection->query("DELETE FROM $tableName WHERE product_id BETWEEN $fromId AND $toId");
}

// Delete from parent/child relationship tables
foreach ($tablesWithParentChild as $table => $columns) {
    $tableName = $resource->getTableName($table);
    foreach ($columns as $col) {
        $connection->query("DELETE FROM $tableName WHERE $col BETWEEN $fromId AND $toId");
    }
}

// Delete from product_id only tables
foreach ($tablesWithProductIdOnly as $table) {
    $tableName = $resource->getTableName($table);
    $connection->query("DELETE FROM $tableName WHERE product_id BETWEEN $fromId AND $toId");
}

// Re-enable foreign key checks
$connection->query('SET FOREIGN_KEY_CHECKS=1;');

echo "All products between entity_id $fromId and $toId have been updated.\n";
