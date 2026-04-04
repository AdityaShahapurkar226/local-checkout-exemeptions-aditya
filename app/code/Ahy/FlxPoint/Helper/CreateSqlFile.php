<?php
/**
 * Copyright © Ahy Consulting All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\FlxPoint\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;
use Ahy\FlxPoint\Logger\Logger as FlxPointApiLogger;

class CreateSqlFile extends AbstractHelper
{

    protected $resourceConnection;
    /**
     * @var FlxPointApiLogger
     */
    private $_flxPointApiLogger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        FlxPointApiLogger   $flxPointApiLogger,
        ResourceConnection  $resourceConnection
    ) {
        parent::__construct($context);
        $this->_flxPointApiLogger   = $flxPointApiLogger;
        $this->resourceConnection   = $resourceConnection;
    }

    Public function createSqlFile($folderPath, $filePath): array
    {
        $returnMsgArr = [];
         // Check if the file exists
        if (!file_exists(filename: $filePath)) {
            $returnMsgArr[] = "<error>$filePath file does not exist.<error>";
            return $returnMsgArr;
        }
        
        try {
            $returnMsgArr[]     = "<info> Found the CSV file<info>";
            $processedCsvData   = $this->_processCsvDataToCreateSqlFile(filePath: $filePath);
            $returnMsgArr[]     = $processedCsvData['returnMsgArr'];
            $sqlFileContent     = $processedCsvData['sqlFileContent'];
            file_put_contents(filename: (string) $folderPath . 'flxpoint_product_custom_import.sql', data: $sqlFileContent);
            $returnMsgArr[]     = "<info> SQL file created <info>";
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();

            // Log the error message with additional information
            $errorLogMessage    = "Error in Ahy\FlxPoint\Helper\CreateSqlFile. Error Message: $errorMessage. Error in $errorFile on line $errorLine.";
            $returnMsgArr[]     = (string) "<error>An error occurred: " . $errorLogMessage . "<error>";
            $this->_flxPointApiLogger->info($errorLogMessage);
        }

        return $returnMsgArr;
    }

    private function _processCsvDataToCreateSqlFile($filePath): array 
    {
        $sqlFileContent  = "";
        $returnMsgArr = [];

        try {
            if (($handle = fopen(filename: $filePath, mode: "r")) !== FALSE) {
                $returnMsgArr[] = "<info> Processing the CSV file<info>";
                // Read the header row
                $header = fgetcsv(stream: $handle, length: 0, separator: ",");
                // Set array keys for the header array
                $headerArray = array_combine(keys: $header, values: $header);
                $returnMsgArr[] = "<info> Creating the SQL file<info>";
                $sqlFileContent = "";
                $sqlFileContent .= $this->_getCategoryIdByCategoryNameProcedure();
                $sqlFileContent .= $this->_insertProductOptionAttributeProcedure();
                $sqlFileContent .= $this->_createProcedureToUpsertSpecialPrice();
                $sqlFileContent .= $this->_insertProductSuperAttributeProcedure();
                $sqlFileContent .= $this->_generateSqlQueriesToCreateSkuForAssignedProductProcedure();

                // Read the data rows
                while (($data = fgetcsv(stream: $handle, length: 0, separator: ",")) !== FALSE) {
                    // Process each row data
                    $sqlFileContent .= "\nSTART TRANSACTION;\n";
                    $rowData = array_combine(keys: $headerArray, values: $data);
                    $productType = $rowData['product_type'];
                    $sqlFileContent .= $this->_generateSqlQueriesToCreateBaseProductIfNotAvailableForAssignedProductProcedure(data: $rowData);
                    $sqlFileContent .= $this->_generateSqlQueries(data: $rowData);
                    // End the transaction
                    $sqlFileContent .= "\nCOMMIT;\n\n";
                    // $sqlFileContent .= "ROLLBACK;\n\n";
                }
                $sqlFileContent .= "INSERT INTO flxpoint_delta (entity_id, last_update_at) VALUES (1, DATE_FORMAT(UTC_TIMESTAMP(), '%Y-%m-%dT%H:%i:%s.%fZ')) ON DUPLICATE KEY UPDATE last_update_at = VALUES(last_update_at);";
                fclose(stream: $handle);
                $returnMsgArr[] = "<info> CSV file Processed <info>";
                
            } else {
                $returnMsgArr[] = "<error>Failed to open CSV file for reading.<error>";
            }
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();

            // Log the error message with additional information
            $errorLogMessage    = "Error in Ahy\FlxPoint\Helper\CreateSqlFile. Error Message: $errorMessage. Error in $errorFile on line $errorLine.";
            $returnMsgArr[]     = (string) "<error>An error occurred: " . $errorLogMessage . "<error>";
            $this->_flxPointApiLogger->info($errorLogMessage);
        }

        return [
            'sqlFileContent' => $sqlFileContent,
            'returnMsgArr' => $returnMsgArr
        ];
    }

    private function _generateSqlQueries($data): string 
    {

        $productType    = $data["product_type"];
        switch ($productType) {
            case 'simple':
                // $sqlQueries  = $this->_generateSqlQueriesForSimpleProduct($data);
                $sqlQueries  = $this->_generateSqlQueriesForAssignedProduct(data: $data);
                break;
            case 'configurable':
                $sqlQueries  = $this->_generateSqlQueriesForConfigurableProduct(data: $data);
                break;
            default:
                # code...
                break;
        }
        return $sqlQueries;
    }

    private function _generateSqlQueriesForSimpleProduct($data, $isBaseProduct = false): string 
    {
        try {
            $name               = $this->_cleanSqlValue(value: $data['name']);
            $attributeSetCode   = $this->_cleanSqlValue(value: $data['attribute_set_code']);
            $brandName          = $this->_cleanSqlValue(value: $data['product_brand']);
            $shortDescription   = $this->_cleanSqlValue(value: $data['short_description']);
            $sku                = $this->_cleanSqlValue(value: $data['sku']);
            $flxpointSku        = $this->_cleanSqlValue(value: $data['flxpoint_sku']);
            $productCategory    = $this->_cleanSqlValue(value: $data['categories']);
            $description        = $this->_cleanSqlValue(value: $data['description']);
            $description        = ($description !== "''") ? $description : "'description'";
            $productStatus      = $data["product_online"];
            $productType        = $data["product_type"];
            $urlKey             = $isBaseProduct ? $this->_cleanSqlValue($data['url_key']) : '@new_sku';
            $urlRewrite         = $isBaseProduct ? ("'" . $data['url_key'] . ".html'") : 'CONCAT(@new_sku, ".html")';
            $price              = $isBaseProduct ? ((int)$data['price'] + 99999) : $data['price'];
            $specialPrice       = $isBaseProduct
                                    ? 'null'
                                    : (!empty($data['special_price']) ? $data['special_price'] : null);
            $specialPriceFrom   = $isBaseProduct
                                    ? 'null'
                                    : (!empty($data['special_price_from_date']) ? $this->_cleanSqlValue($data['special_price_from_date']) : null);
            $specialPriceTo     = $isBaseProduct
                                    ? 'null'
                                    : (!empty($data['special_price_to_date']) ? $this->_cleanSqlValue($data['special_price_to_date']) : null);

            $updateSpecialPrice = $specialPrice ? sprintf("CALL upsert_special_price(@new_entity_id, %s, %s, %s);\n", $specialPrice, $specialPriceFrom !== null ? $specialPriceFrom : 'null', $specialPriceTo !== null ? $specialPriceTo : 'null') : "";

            $skuType            = $isBaseProduct ? '@sku' : '@new_sku';
            $qty                = $data['qty'];
            $stockStatus        = $data['is_in_stock'];
            $configurableOptionsAttributeValue = "";
            if(!empty($data['configurable_variation_labels_values'])){
                $configurableOptions = explode(separator: '~', string: $data['configurable_variation_labels_values']);
                foreach ($configurableOptions as $option) {
                    // Split the option on '=' to separate attribute name and value
                    $parts = explode(separator: '=', string: $option);
                    if (count(value: $parts) < 2) {
                        $this->_flxPointApiLogger->info("Error processing configurable options: '$option'. Skipping this option.");
                        continue; // Skip this option if it doesn't contain '='
                    }
                    // The attribute name is the first part of the split
                    $attributeName = $this->_cleanSqlValue(value: trim(string: $parts[0]));
                    $attributeCode = $this->_cleanSqlValue(value: trim(string: str_replace(search: ' ', replace: '_', subject: $parts[0])));
                    $attributeOption = $this->_cleanSqlValue(value: trim(string: $parts[1]));
                    // $configurableOptionsAttributeValue .= "INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) VALUES ((SELECT attribute_id FROM eav_attribute WHERE attribute_code = $attributeName), 0, @new_entity_id, (SELECT option_id FROM eav_attribute_option_value WHERE value = $attributeOption)) ON DUPLICATE KEY UPDATE value = VALUES(value);\n";
                    $configurableOptionsAttributeValue .= "CALL InsertProductOptionAttributeProcedure($attributeCode, 0, @new_entity_id, $attributeName, $attributeOption);\n";
                }
            }

            switch($data["visibility"]) {
                case "Not Visible Individually":
                    $visibility = 1;
                    break;
                case "Catalog":
                    $visibility = 2;
                    break;
                case "Search":
                    $visibility = 3;
                    break;
                case "Catalog, Search":
                    $visibility = 4;
                    break;
                default:
                    // Default case if the visibility value doesn't match any of the above
                    $visibility = 1; // Set to "Not Visible Individually" by default
                    break;
            }

            $sqlQueries = "SET @new_entity_id    = NULL;\n";

            $sqlQueries .= "SET @attribute_set_id = (SELECT attribute_set_id FROM eav_attribute_set WHERE attribute_set_name = $attributeSetCode);\n";
            
            $sqlQueries .= "INSERT INTO catalog_product_entity (attribute_set_id, type_id, sku, has_options, required_options, created_at, updated_at) VALUES (@attribute_set_id, '$productType', $skuType, '0', '0', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE attribute_set_id = VALUES(attribute_set_id), type_id = VALUES(type_id), sku = VALUES(sku), has_options = VALUES(has_options), required_options = VALUES(required_options), created_at = VALUES(created_at), updated_at = VALUES(updated_at);\n";
            // Use the retrieved value to generate a new entity_id
            $sqlQueries .= "SET @new_entity_id    = COALESCE(@new_entity_id, LAST_INSERT_ID());\n";

            // Set Product Name and Url
            $sqlQueries .= "INSERT INTO catalog_product_entity_varchar (attribute_id, store_id, entity_id, value) VALUES ('73', '0', @new_entity_id, $name), ('123', '0', @new_entity_id, $urlKey);\n";

            // Set status of new product using entity_id and product visibility;
            $sqlQueries .= "INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) VALUES ('97', '0', @new_entity_id, '$productStatus'), ('99', '0', @new_entity_id, $visibility), ('136', '0', @new_entity_id, '2');\n";
            
            // Insert data into catalog_product_entity_text table for short description and Long description
            $sqlQueries .= "INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (76, 0, @new_entity_id, $shortDescription), (75, 0, @new_entity_id, $description);\n";
            
            $targetPath = "catalog/product/view/id/@new_entity_id";
            
            $sqlQueries .= "SET @target_path = CONCAT('catalog/product/view/id/', @new_entity_id);\n";

            $sqlQueries .= "CALL InsertProductOptionAttributeProcedure('product_brand', 0, @new_entity_id, 'Product Brand', $brandName);\n";

            // Insert product URL key into URL rewrite table
            $sqlQueries .= "INSERT INTO url_rewrite (entity_type, entity_id, store_id, request_path, target_path, is_autogenerated) VALUES ('product', @new_entity_id, 1, $urlRewrite, @target_path, 1) ON DUPLICATE KEY UPDATE target_path = @target_path, `request_path` = VALUES(`request_path`);\n";
            
            $sqlQueries .= "INSERT INTO catalog_product_entity_int (entity_id, attribute_id, value) VALUES (@new_entity_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'tax_class_id'), 2) ON DUPLICATE KEY UPDATE value = VALUES(value);\n";

            // Insert data into cataloginventory_stock_item table for product stock
            // $sqlQueries .= $configurableOptionsAttributeValue;
            $sqlQueries .= "INSERT INTO cataloginventory_stock_item (product_id, stock_id, qty, is_in_stock) VALUES (@new_entity_id, 1, $qty, $stockStatus);\n";

            // Insert data into cataloginventory_stock_status table for product stock status
            $sqlQueries .= "INSERT INTO cataloginventory_stock_status (product_id, website_id, stock_id, qty, stock_status) VALUES (@new_entity_id, 0, 1, $qty, $stockStatus) ON DUPLICATE KEY UPDATE `qty` = VALUES(`qty`), `stock_status` = VALUES(`stock_status`);\n";

            // Set websites
            $sqlQueries .= "INSERT INTO catalog_product_website (product_id, website_id) VALUES (@new_entity_id, 1);\n";

            // Set filter_price_range of product
            $sqlQueries .= "INSERT INTO catalog_product_entity_decimal (attribute_id, store_id, entity_id, value) VALUES ('77', '0', @new_entity_id, '$price');\n";

            $sqlQueries .= $updateSpecialPrice;

            $sqlQueries .= "CALL GetCategoryIdByName($productCategory, @categoryId);\n";

            $sqlQueries .= "INSERT INTO `catalog_product_entity_varchar` (`entity_id`, `attribute_id`, `store_id`, `value`) VALUES (@new_entity_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'flxpoint_sku'), '0', $flxpointSku) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);";
            
            // Insert data into catalog_category_product table to associate the product with the first category
            $sqlQueries .= "INSERT INTO catalog_category_product (category_id, product_id, position) VALUES (@categoryId, @new_entity_id, 1);\n";
            
            $sqlQueries .= $this->_generateSqlQueriesToAssignProductImage(data: $data);

            return $sqlQueries;
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();

            // Log the error message with additional information
            $errorLogMessage = "Error in Ahy\FlxPoint\Helper\CreateSqlFile -> _generateSqlQueriesForSimpleProduct(). Error Message: $errorMessage. Error in $errorFile on line $errorLine. for: " . $data['name'];
            
            $this->_flxPointApiLogger->info($errorLogMessage);
            return '';
        }
    }

    private function _generateSqlQueriesForConfigurableProduct($data, $isBaseProduct = false): string 
    {
        try {
            $name               = $this->_cleanSqlValue(value: $data['name']);
            $attributeSetCode   = $this->_cleanSqlValue(value: $data['attribute_set_code']);
            $brandName          = $this->_cleanSqlValue(value: $data['product_brand']);
            $shortDescription   = $this->_cleanSqlValue(value: $data['short_description']);
            $description        = $this->_cleanSqlValue(value: $data['description']);
            $description        = ($description !== "''") ? $description : "'description'";
            $sku                = $this->_cleanSqlValue(value: $data['sku']);
            $flxpointSku        = $this->_cleanSqlValue(value: $data['flxpoint_sku']);
            $productStatus      = $data["product_online"];
            $productCategory    = $this->_cleanSqlValue(value: $data['categories']);
            $productType        = $this->_cleanSqlValue(value: $data["product_type"]);
            $urlKey             = $isBaseProduct ? $this->_cleanSqlValue(value: $data['url_key']) : '@new_sku';
            $urlRewrite         = $isBaseProduct ? ("'" . $data['url_key'] . ".html'") : 'CONCAT(@new_sku, ".html")';
            $price              = 0 /* $isBaseProduct ? ((int)$data['price'] + 9999) : $data['price'] */;
            $specialPrice       = $isBaseProduct ? 'null' : (($data['special_price'] !== null && $data['special_price'] !== '') ? $data['special_price'] : null);
            $specialPriceFrom   = $isBaseProduct ? 'null' : (($data['special_price_from_date'] !== null && $data['special_price_from_date'] !== '') ? $this->_cleanSqlValue($data['special_price_from_date']) : null);
            $specialPriceTo     = $isBaseProduct ? 'null' : (($data['special_price_to_date'] !== null && $data['special_price_to_date'] !== '') ? $this->_cleanSqlValue($data['special_price_to_date']) : null);
            $skuType            = $isBaseProduct ? '@sku' : '@new_sku';
            $qty                = $data['qty'];
            $stockStatus        = $data['is_in_stock'];
            $configurable_variations = explode(' | ', $data['configurable_variations']);
            $sellerId           = $data["seller_id"];
            $associate_configurable_simple_products = '';
            $link_configurable_simple_products = '';
            $marketplace_assignproduct_associated_products = "";
            $updatedDescription = ($description !== "''") ? $description : "'description'";
            $configurableOptions = explode(' | ', $data['configurable_variation_labels']);
            $configurable_variation_labels_values = explode(' | ', $data['configurable_variation_labels_values']);
            $configurableOptionsLabels = "\n";
            $configurableOptionsAttribute = '';
            $configurableSuperAttribute = '';
            $attributeValue = $data['additional_attributes'];
            $attributeValue = ($attributeValue !== "''") ? $attributeValue : null;
            $attributeCodeCustom = 'more_specs';
            $attributeValue = str_replace("'", "&#39;", $attributeValue);

            foreach ($configurable_variation_labels_values as $option) {
                $optionLabelValues = explode(':', $option);
                $simpleSku = $optionLabelValues[0];
                
                // Check if the second element of the array exists
                if (isset($optionLabelValues[1])) {
                    $optionValues = $optionLabelValues[1];
                    $superAttribute = explode('~', $optionValues);
                    
                    foreach ($superAttribute as $attribute) {
                        $parts = explode('=', $attribute);
                        if (count($parts) < 2) {
                            $this->_flxPointApiLogger->info("Error processing configurable options: '$option'. Skipping this option.");
                            continue; // Skip this option if it doesn't contain '='
                        }
                        $attributeCode = $this->_cleanSqlValue(trim(str_replace(' ', '_', $parts[0])));
                        $attributeName = $this->_cleanSqlValue(trim($parts[0]));
                        $attributeOptionValue = $this->_cleanSqlValue(trim($parts[1]));
                        $configurableOptionsLabels .= $isBaseProduct
                            ? "SET @simpleProductId := (SELECT entity_id FROM catalog_product_entity WHERE sku = '$simpleSku');\n"
                            : "SET @simpleProductId := (SELECT assign_product_id FROM marketplace_assignproduct_items WHERE seller_id = @seller_id AND product_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = '$simpleSku'));\n";
                        $configurableOptionsLabels .= "CALL InsertProductOptionAttributeProcedure($attributeCode, 0, @simpleProductId, $attributeName, $attributeOptionValue);\n";
                    }
                } else {
                    // Handle the case where the second element doesn't exist
                    // You can either skip this iteration or log an error message
                    continue;
                }
            }

            
            foreach ($configurableOptions as $option) {
                $option = $this->_cleanSqlValue(str_replace(' ', '_',$option));
                $configurableSuperAttribute .= "CALL InsertProductSuperAttributeProcedure($option, @new_entity_id);\n";
            }
            $simpleProductOption = "";
            foreach ($configurable_variations as $variation) {
                // Assuming $new_entity_id is a variable containing the new entity id
                if($isBaseProduct){
                    $associate_configurable_simple_products .= "(@new_entity_id, (SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation')), ";
                    $link_configurable_simple_products      .= "((SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation'), @new_entity_id), ";
                    
                } else {
                    $associate_configurable_simple_products .= "(@new_entity_id, (SELECT assign_product_id FROM marketplace_assignproduct_items WHERE seller_id = @seller_id AND product_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation'))), ";
                    $link_configurable_simple_products      .= "((SELECT assign_product_id FROM marketplace_assignproduct_items WHERE seller_id = @seller_id AND product_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation')), @new_entity_id), ";
                }
                
                $marketplace_assignproduct_associated_products .= "INSERT INTO `marketplace_assignproduct_associated_products` (`product_id`, `parent_id`, `parent_product_id`, `qty`, `price`, `options`, `assign_product_id`) VALUES 
    ((SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation'), 
    (SELECT id FROM marketplace_assignproduct_items WHERE product_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = $sku)), 
    (SELECT entity_id FROM catalog_product_entity WHERE sku = $sku), 
    (SELECT qty FROM cataloginventory_stock_item where product_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation')), 
    (SELECT value FROM catalog_product_entity_decimal WHERE attribute_id = 77 AND entity_id = (SELECT assign_product_id FROM marketplace_assignproduct_items WHERE seller_id = @seller_id AND product_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation'))), 
    '',
    (SELECT assign_product_id FROM marketplace_assignproduct_items WHERE seller_id = @seller_id AND product_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = '$variation')));\n";
            }

            // Remove the trailing comma and space
            $associate_configurable_simple_products = rtrim($associate_configurable_simple_products, ', ');
            $link_configurable_simple_products      = rtrim($link_configurable_simple_products, ', ');
            switch($data["visibility"]) {
                    case "Not Visible Individually":
                        $visibility = 1;
                        break;
                    case "Catalog":
                        $visibility = 2;
                        break;
                    case "Search":
                        $visibility = 3;
                        break;
                    case "Catalog, Search":
                        $visibility = 4;
                        break;
                    default:
                        // Default case if the visibility value doesn't match any of the above
                        $visibility = 1; // Set to "Not Visible Individually" by default
                        break;
                }
                $sqlQueries= '';
                
        $sqlQueries =  $isBaseProduct ? '' : "
SET @sku = $sku;\n
CALL CreateBaseProductIfNotAvailableForAssignedProduct(@sku);\n
SET @entity_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = @sku);\n
CALL CreateSkuForAssignedProduct(@sku);
DROP PROCEDURE IF EXISTS UpsertMarketplaceProduct;
DELIMITER //

CREATE PROCEDURE UpsertMarketplaceProduct(IN p_seller_id INT, IN p_product_upc VARCHAR(255))
BEGIN
    DECLARE v_mageproduct_id INT;
    DECLARE v_new_sku VARCHAR(255);

    -- Check if the record exists
    SELECT mageproduct_id INTO v_mageproduct_id FROM marketplace_product WHERE seller_id = p_seller_id AND product_upc = p_product_upc;

    -- If the result set is not empty, update the updated_at column
    IF v_mageproduct_id IS NOT NULL THEN
        SET @product_status = 'product available. Updating the product details';
        SET @mageproduct_id = v_mageproduct_id;
        SELECT sku INTO v_new_sku FROM catalog_product_entity WHERE entity_id = @mageproduct_id;
        SET @new_sku = v_new_sku;
        SELECT @product_status, @mageproduct_id;

        -- Set status of new product using entity_id
        INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) VALUES ('97', '0', @mageproduct_id, '$productStatus'), ('136', '0', @mageproduct_id, '2') ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Set Product Name of product 
        -- INSERT INTO catalog_product_entity_varchar (attribute_id, store_id, entity_id, value) VALUES ('73', '0', @mageproduct_id, $name) ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Insert data into catalog_product_entity_text table for short description
        -- INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (76, 0, @mageproduct_id, $shortDescription) ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Insert data into catalog_product_entity_text table for long description
        -- INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (75, 0, @mageproduct_id, $description) ON DUPLICATE KEY UPDATE value = VALUES(value);
    
        -- Insert data into cataloginventory_stock_item table for product stock
        INSERT INTO cataloginventory_stock_item (product_id, stock_id, qty, is_in_stock) VALUES (@mageproduct_id, 1, $qty, $stockStatus) ON DUPLICATE KEY UPDATE `qty` = VALUES(`qty`), `is_in_stock` = VALUES(`is_in_stock`);

        -- Insert data into cataloginventory_stock_status table for product stock status
        INSERT INTO cataloginventory_stock_status (product_id, website_id, stock_id, qty, stock_status) VALUES (@mageproduct_id, 0, 1, $qty, $stockStatus) ON DUPLICATE KEY UPDATE `qty` = VALUES(`qty`), `stock_status` = VALUES(`stock_status`);

        CALL InsertProductOptionAttributeProcedure('product_brand', 0, @mageproduct_id, 'Product Brand', $brandName);
        
        INSERT INTO `catalog_product_entity_varchar` (`entity_id`, `attribute_id`, `store_id`, `value`) VALUES (@mageproduct_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'flxpoint_sku'), '0', $flxpointSku) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

        -- Insert tax
        INSERT INTO catalog_product_entity_int (entity_id, attribute_id, value) VALUES (@mageproduct_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'tax_class_id'), 2) ON DUPLICATE KEY UPDATE value = VALUES(value);
        
        -- Insert data into catalog_product_entity_decimal table for product price
        INSERT INTO catalog_product_entity_decimal (attribute_id, store_id, entity_id, value) VALUES ('77', '0', @mageproduct_id, '$price') ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Assuming the correct route for your Magento version
        SET @target_path = CONCAT('catalog/product/view/id/', @mageproduct_id);
        -- Insert product URL key into URL rewrite table
        -- INSERT INTO url_rewrite (entity_type, entity_id, store_id, request_path, target_path, is_autogenerated) VALUES ('product', @mageproduct_id, 1, $urlRewrite, @target_path, 1) ON DUPLICATE KEY UPDATE target_path = @target_path, `request_path` = VALUES(`request_path`);
                
    -- If the result set is empty, insert a new record
    ELSE
        SET @product_status = 'product not available. Adding the products';
        SELECT @product_status;
        ";       
 

        $sqlQueries  .= "SET @new_entity_id = NULL;\n";

        $sqlQueries  .= "SET @attribute_set_id = (SELECT attribute_set_id FROM eav_attribute_set WHERE attribute_set_name = $attributeSetCode);
    
-- insert new product
INSERT INTO catalog_product_entity (attribute_set_id, type_id, sku, has_options, required_options, created_at, updated_at) VALUES (@attribute_set_id, $productType, $skuType, '1', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE attribute_set_id = VALUES(attribute_set_id), type_id = VALUES(type_id), sku = VALUES(sku), has_options = VALUES(has_options), required_options = VALUES(required_options), created_at = VALUES(created_at), updated_at = VALUES(updated_at);

-- Use the retrieved value to generate a new entity_id
SET @new_entity_id = COALESCE(@new_entity_id, LAST_INSERT_ID());

-- set Product Name and url of product \n";

$sqlQueries .= "INSERT INTO catalog_product_entity_varchar (attribute_id, store_id, entity_id, value) VALUES ('73', '0', @new_entity_id, $name), ('123', '0', @new_entity_id, $urlKey) ON DUPLICATE KEY UPDATE value = VALUES(value);\n";

$sqlQueries .= "CALL GetCategoryIdByName($productCategory, @categoryId);\n";
            
// Insert data into catalog_category_product table to associate the product with the first category
$sqlQueries .= "INSERT INTO catalog_category_product (category_id, product_id, position) VALUES (@categoryId, @new_entity_id, 1);\n";

$sqlQueries  .= "-- Set status of new product using entity_id
INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) VALUES ('97', '0', @new_entity_id, '$productStatus'), ('136', '0', @new_entity_id, '2');

-- Set visibility of product using entity_id
INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) VALUES ('99', '0', @new_entity_id, $visibility);

-- Insert data into catalog_product_entity_text table for short description
INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (76, 0, @new_entity_id, $shortDescription);

-- Insert data into catalog_product_entity_text table for long description
INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (75, 0, @new_entity_id, $description);

CALL InsertProductOptionAttributeProcedure('product_brand', 0, @new_entity_id, 'Product Brand', $brandName);
-- Insert for Configurable product
INSERT INTO `catalog_product_entity_varchar` (`entity_id`, `attribute_id`, `store_id`, `value`) VALUES (@new_entity_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'flxpoint_sku'), '0', $flxpointSku) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);


-- Assuming the correct route for your Magento version
SET @target_path = CONCAT('catalog/product/view/id/', @new_entity_id);

-- Insert product URL key into URL rewrite table
INSERT INTO url_rewrite (entity_type, entity_id, store_id, request_path, target_path, is_autogenerated) VALUES ('product', @new_entity_id, 1, $urlRewrite, @target_path, 1) ON DUPLICATE KEY UPDATE target_path = @target_path, `request_path` = VALUES(`request_path`);

INSERT INTO catalog_product_website (product_id, website_id) VALUES (@new_entity_id, 1);

$configurableSuperAttribute

SET @position = 0;
UPDATE catalog_product_super_attribute SET position = (@position := @position + 1) WHERE product_id = @new_entity_id;

-- Insert data into cataloginventory_stock_item table for product stock
INSERT INTO cataloginventory_stock_item (product_id, stock_id, qty, is_in_stock) VALUES (@new_entity_id, 1, $qty, $stockStatus);

-- Insert tax
INSERT INTO catalog_product_entity_int (entity_id, attribute_id, value) VALUES (@new_entity_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'tax_class_id'), 2) ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Insert data into cataloginventory_stock_status table for product stock status
INSERT INTO cataloginventory_stock_status (product_id, website_id, stock_id, qty, stock_status) VALUES (@new_entity_id, 0, 1, $qty, $stockStatus);

$configurableOptionsLabels

-- Associate the first simple product with the configurable product
INSERT INTO catalog_product_relation (parent_id, child_id) VALUES $associate_configurable_simple_products;

-- Link the first simple product to the configurable product
INSERT INTO catalog_product_super_link (product_id, parent_id) VALUES $link_configurable_simple_products;


";

    if ($attributeCodeCustom !== null ) {
        $sqlQueries .= "-- INSERTING NEW CUSTOM ATTRIBUTES FOR TENT \n
        SET @custom_attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '$attributeCodeCustom');\n
        INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (@custom_attribute_id, 0, @new_entity_id, '$attributeValue');\n
        ";
    }

$sqlQueries .= $this->_generateSqlQueriesToAssignProductImage($data);
$sqlQueries .= $isBaseProduct ? '' : $this->_assignProductQuery($data);
$sqlQueries .= $isBaseProduct ? '' : $marketplace_assignproduct_associated_products;
$sqlQueries .= $isBaseProduct ? '' : "END IF;
END //

DELIMITER ;
SET @seller_id = (SELECT seller_id FROM marketplace_userdata WHERE flxPoint_seller_id = $sellerId);

CALL UpsertMarketplaceProduct(@seller_id, $sku);";

            return $sqlQueries;
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();

            // Log the error message with additional information
            $errorLogMessage = "Error in Ahy\FlxPoint\Helper\CreateSqlFile. Error Message: $errorMessage. Error in $errorFile on line $errorLine.";
            
            $this->_flxPointApiLogger->info($errorLogMessage);
            return '';
        }
    }

    private function _generateSqlQueriesForAssignedProduct($data): string {
        try {
        $name               = $this->_cleanSqlValue($data['name']);
        $shortDescription   = $this->_cleanSqlValue($data['short_description']);
        $description        = $this->_cleanSqlValue($data['description']);
        $sku                = $this->_cleanSqlValue($data['sku']);
        $productStatus      = $data["product_online"];
        $productType        = $this->_cleanSqlValue($data["product_type"]);
        $attributeSetCode   = $data['attribute_set_code'];
        $urlKey             = $data['url_key'];
        $price              = $data['price'];
        $qty                = $data['qty'];
        $stockStatus        = $data['is_in_stock'];
        $sellerId           = $data["seller_id"];

        $updatedDescription = ($description !== "''") ? $description : "'description'";
        $sqlQueries  = "SET @sku = $sku;\n
CALL CreateBaseProductIfNotAvailableForAssignedProduct(@sku);\n
SET @entity_id = (SELECT entity_id FROM catalog_product_entity WHERE sku = @sku);\n
CALL CreateSkuForAssignedProduct(@sku);\n";

        $sqlQueries .= $this->_createProcedureToUpsertProduct($data);

        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();

            // Log the error message with additional information
            $errorLogMessage = "Error in Ahy\FlxPoint\Helper\CreateSqlFile. Error Message: $errorMessage. Error in $errorFile on line $errorLine.";
            
            $this->_flxPointApiLogger->info($errorLogMessage);
        }
        return $sqlQueries;
    }

    private function _generateSqlQueriesToAssignProductImage($data) {
        
        $imagePath = $this->_cleanSqlValue($data['image_path']);
        $sqlQueries = "";
        // var_dump($imagePath . 'before');
        if($imagePath !== '' && $imagePath !== "''"){

            $sqlQueries .= "SET @image_url = $imagePath;\n";
            
            $sqlQueries .= "SET @image_attr_id = 87;\nSET @small_image_attr_id = 88;\nSET @thumbnail_attr_id = 89;\nSET @swatch_attr_id = 135;\n";
            
            $sqlQueries .= "INSERT INTO catalog_product_entity_media_gallery (attribute_id, value, media_type, disabled) VALUES ((SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'media_gallery' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product') LIMIT 1), @image_url, 'image', 0) ON DUPLICATE KEY UPDATE value = VALUES(value), media_type = VALUES(media_type), disabled = VALUES(disabled);\n";
            
            $sqlQueries .= "SET @last_media_gallery_id = LAST_INSERT_ID();\n";
            
            $sqlQueries .= "INSERT INTO catalog_product_entity_media_gallery_value_to_entity (value_id, entity_id) VALUES (@last_media_gallery_id, @new_entity_id) ON DUPLICATE KEY UPDATE value_id = VALUES(value_id), entity_id = VALUES(entity_id);\n";
            
            $sqlQueries .= "INSERT INTO catalog_product_entity_media_gallery_value (value_id, store_id, label, position, disabled, entity_id) VALUES (@last_media_gallery_id, 0, NULL, 1, 0, @new_entity_id) ON DUPLICATE KEY UPDATE value_id = VALUES(value_id), store_id = VALUES(store_id), label = VALUES(label), position = VALUES(position), disabled = VALUES(disabled), entity_id = VALUES(entity_id);\n";
            
            $sqlQueries .= "INSERT INTO catalog_product_entity_varchar (attribute_id, store_id, entity_id, value) VALUES (@image_attr_id, 0, @new_entity_id, @image_url), (@swatch_attr_id, 0, @new_entity_id, @image_url), (@small_image_attr_id, 0, @new_entity_id, @image_url), (@thumbnail_attr_id, 0, @new_entity_id, @image_url) ON DUPLICATE KEY UPDATE value = VALUES(value);\n";
        }
        $additionalImagePath = $data['additional_image_path'];
        
        if($additionalImagePath !== '' && $additionalImagePath !== "''"){
            
            $additionalImages = explode(' | ', $additionalImagePath);
            
            foreach ($additionalImages as $additionalImage) {
                $additionalImage = $this->_cleanSqlValue(trim($additionalImage));

                $sqlQueries .= "SET @image_url = $additionalImage;\n";

                // $sqlQueries .= "SET @image_attr_id = 87;\nSET @small_image_attr_id = 88;\nSET @thumbnail_attr_id = 89;\n";
                
                $sqlQueries .= "INSERT INTO catalog_product_entity_media_gallery (attribute_id, value, media_type, disabled) VALUES ((SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'media_gallery' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product') LIMIT 1), @image_url, 'image', 0) ON DUPLICATE KEY UPDATE value = VALUES(value), media_type = VALUES(media_type), disabled = VALUES(disabled);\n";
                
                $sqlQueries .= "SET @last_media_gallery_id = LAST_INSERT_ID();\n";
                
                $sqlQueries .= "INSERT INTO catalog_product_entity_media_gallery_value_to_entity (value_id, entity_id) VALUES (@last_media_gallery_id, @new_entity_id) ON DUPLICATE KEY UPDATE value_id = VALUES(value_id), entity_id = VALUES(entity_id);\n";
                
                $sqlQueries .= "INSERT INTO catalog_product_entity_media_gallery_value (value_id, store_id, label, position, disabled, entity_id) VALUES (@last_media_gallery_id, 0, NULL, NULL, 0, @new_entity_id) ON DUPLICATE KEY UPDATE value_id = VALUES(value_id), store_id = VALUES(store_id), label = VALUES(label), position = VALUES(position), disabled = VALUES(disabled), entity_id = VALUES(entity_id);\n";

                // $sqlQueries .= "INSERT INTO catalog_product_entity_varchar (attribute_id, store_id, entity_id, value) VALUES (@image_attr_id, 0, @new_entity_id, @image_url), (@small_image_attr_id, 0, @new_entity_id, @image_url), (@thumbnail_attr_id, 0, @new_entity_id, @image_url) ON DUPLICATE KEY UPDATE value = VALUES(value);\n";

            }
        }
        
        return $sqlQueries;
    }
    
    private function _generateSqlQueriesToCreateSkuForAssignedProductProcedure() {
        $sqlQueries  = "\n-- Drop the existing stored procedure\n
DROP PROCEDURE IF EXISTS CreateSkuForAssignedProduct;

DELIMITER //
    -- Recreate the stored procedure with updated logic
    CREATE PROCEDURE CreateSkuForAssignedProduct(IN input_sku VARCHAR(255))
    BEGIN
        DECLARE sku_count INT;

        -- Start a transaction
        START TRANSACTION;
        -- Check if the first query has any value
        SELECT COUNT(*)
        INTO sku_count
        FROM `catalog_product_entity`
        WHERE sku LIKE CONCAT(input_sku, '-%')
        ORDER BY `sku` DESC
        LIMIT 1;

        IF sku_count > 0 THEN
            -- If the first query has a value, run the second query
            SET @sku = input_sku;
            SET @new_sku := NULL;

            SELECT
                `catalog_product_entity`.`sku`,
                SUBSTRING(`catalog_product_entity`.`sku`, LENGTH(@sku) + 2) AS rest,
                @new_sku := CONCAT(@sku, CAST(CAST(SUBSTRING(`catalog_product_entity`.`sku`, LENGTH(@sku) + 1) AS SIGNED) - 1 AS CHAR)) AS new_sku
            FROM `catalog_product_entity`
            WHERE `catalog_product_entity`.`sku` LIKE CONCAT(@sku, '-%')
            ORDER BY `catalog_product_entity`.`sku` DESC
            LIMIT 1;
            -- Commit the transaction
            COMMIT;
        ELSE
            -- If the first query doesn't have a value, set @new_sku to a default value
            SET @new_sku := CONCAT(input_sku, '-1');
            SELECT @new_sku;
            
            -- Rollback the transaction
            ROLLBACK;
        END IF;
    END //
DELIMITER ;";

        return $sqlQueries;
    }

    private function _insertProductOptionAttributeProcedure() {
        $sqlQueries = "
DROP PROCEDURE IF EXISTS InsertProductOptionAttributeProcedure;
DELIMITER //

CREATE PROCEDURE InsertProductOptionAttributeProcedure(
    IN p_attribute_code VARCHAR(255),
    IN p_store_id INT,
    IN p_entity_id VARCHAR(255),
    IN p_attribute_name VARCHAR(255),
    IN p_option_value VARCHAR(255)
)
BEGIN
    DECLARE v_attribute_id INT;
    DECLARE v_option_id INT;

    -- Check if the attribute_code exists
    SELECT attribute_id INTO v_attribute_id FROM eav_attribute WHERE attribute_code = p_attribute_code;

    -- If attribute does not exist, create one
    IF v_attribute_id IS NULL THEN
        SET @error_message = CONCAT('Provided attribute_code: ', p_attribute_code, ' is not available. Creating the attribute code.');
        SELECT @error_message;

        -- Create the attribute
        INSERT INTO eav_attribute
            (`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `is_unique`)
        VALUES
            (4, p_attribute_code,  NULL, 'int', 'select', p_attribute_name, 'Magento\\\Eav\\\Model\\\Entity\\\Attribute\\\Source\\\Table', '0', '1', '0');

        -- Get the ID of the created attribute
        SET @attributeId = LAST_INSERT_ID();

        -- Insert into catalog_eav_attribute using the obtained attributeId
        INSERT INTO `catalog_eav_attribute` (`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`, `is_used_in_grid`, `is_visible_in_grid`, `is_filterable_in_grid`, `is_pagebuilder_enabled`, `additional_data`, `visible_to_seller`) 
        VALUES (@attributeId, '1', '1', '1', '1', '1', '1', '0', '0', '0', NULL, '0', '0', '1', '1', '1', '0', '{\"swatch_input_type\":\"text\",\"update_product_preview_image\":\"0\",\"use_product_image_for_swatch\":0}', '0');

        SELECT attribute_id INTO v_attribute_id FROM eav_attribute WHERE attribute_code = p_attribute_code;
    END IF;

    -- Check if option value exists
    SELECT eov.option_id INTO v_option_id 
    FROM eav_attribute ea
    JOIN eav_attribute_option eao ON ea.attribute_id = eao.attribute_id
    JOIN eav_attribute_option_value eov ON eao.option_id = eov.option_id
    WHERE ea.attribute_code = p_attribute_code AND eov.value = p_option_value
    LIMIT 1;

    -- If attribute value does not exist, create one
    IF v_option_id IS NULL THEN
        SET @error_message = CONCAT('Attribute option value: ', p_option_value, ' not found for the provided attribute_code: ', p_attribute_code, '. Creating the option value.');
        SELECT @error_message;

        -- Insert attribute values
        INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (v_attribute_id, '0');
        SET @optionId = LAST_INSERT_ID();

        -- Insert into eav_attribute_option_swatch
        INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES (@optionId, '0', p_option_value);
        INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES (@optionId, '1', p_option_value);

        INSERT INTO `eav_attribute_option_swatch` (`option_id`, `store_id`, `type`, `value`) VALUES (@optionId, '0', '0', p_option_value);
        INSERT INTO `eav_attribute_option_swatch` (`option_id`, `store_id`, `type`, `value`) VALUES (@optionId, '1', '0', p_option_value);

        SELECT eov.option_id INTO v_option_id 
        FROM eav_attribute ea
        JOIN eav_attribute_option eao ON ea.attribute_id = eao.attribute_id
        JOIN eav_attribute_option_value eov ON eao.option_id = eov.option_id
        WHERE ea.attribute_code = p_attribute_code AND eov.value = p_option_value
        LIMIT 1;
    END IF;

    -- Record exists, insert the query
    INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) 
    VALUES (v_attribute_id, p_store_id, p_entity_id, v_option_id) 
    ON DUPLICATE KEY UPDATE value = v_option_id;

END //

DELIMITER ;
";
        return $sqlQueries;
    }
    
    private function _insertProductSuperAttributeProcedure() {
        $sqlQueries = "DROP PROCEDURE IF EXISTS InsertProductSuperAttributeProcedure;
DELIMITER //

CREATE PROCEDURE InsertProductSuperAttributeProcedure(IN p_attribute_code VARCHAR(255), IN p_entity_id INT)
BEGIN
    DECLARE v_attribute_id INT;

    -- Check if the attribute_code exists
    SELECT attribute_id INTO v_attribute_id FROM eav_attribute WHERE attribute_code = p_attribute_code;
    -- SELECT attribute_id INTO v_attribute_id FROM eav_attribute WHERE attribute_code IN (p_attribute_code) AND attribute_id NOT IN ( SELECT attribute_id FROM catalog_product_super_attribute  WHERE product_id =  p_entity_id);
    
    -- If attribute does not exist, exit the procedure
    IF v_attribute_id IS NULL THEN
        SET @error_message = CONCAT('Super attribute not found for the provided product: ', p_attribute_code, ' product Id: ', p_entity_id);
        SELECT @error_message;

        INSERT INTO eav_attribute
            (`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `is_unique`)
        VALUES
            (4, p_attribute_code,  NULL, 'int', 'select', p_attribute_code, 'Magento\\\Eav\\\Model\\\Entity\\\Attribute\\\Source\\\Table', '0', '1', '0');

        -- Get the ID of the created attribute
        SET @attributeId = LAST_INSERT_ID();

        -- Insert into catalog_eav_attribute using the obtained attributeId
        INSERT INTO `catalog_eav_attribute` (`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`, `is_used_in_grid`, `is_visible_in_grid`, `is_filterable_in_grid`, `is_pagebuilder_enabled`, `additional_data`, `visible_to_seller`) 
        VALUES (@attributeId, '1', '1', '1', '1', '1', '1', '0', '0', '0', NULL, '0', '0', '1', '1', '1', '0', '{\"swatch_input_type\":\"text\",\"update_product_preview_image\":\"0\",\"use_product_image_for_swatch\":0}', '0');
        SELECT attribute_id INTO v_attribute_id FROM eav_attribute WHERE attribute_code = p_attribute_code;
    END IF;
    
    INSERT INTO catalog_product_super_attribute (product_id, attribute_id) VALUES (p_entity_id, v_attribute_id);
    INSERT INTO catalog_product_super_attribute_label (product_super_attribute_id, store_id, use_default, value) VALUES 
    (
        (SELECT product_super_attribute_id FROM catalog_product_super_attribute WHERE product_id = p_entity_id AND attribute_id = v_attribute_id),
        0, -- Replace with the appropriate store_id
        0, -- Set to 1 if you want to use the default label, 0 otherwise
        p_attribute_code
    ) ON DUPLICATE KEY UPDATE value = p_attribute_code;
    
END //

DELIMITER ;
";
        return $sqlQueries;
    }
    private function _getCategoryIdByCategoryNameProcedure(): string {
        $sqlQueries = "DROP PROCEDURE IF EXISTS GetCategoryIdByName;
DELIMITER //

CREATE PROCEDURE GetCategoryIdByName(IN categoryName VARCHAR(255), OUT categoryId INT)
BEGIN
    SELECT 
        COALESCE(cce.entity_id, 3196) INTO categoryId
    FROM 
        catalog_category_entity_varchar ccev
    LEFT JOIN 
        catalog_category_entity cce ON cce.entity_id = ccev.entity_id
    WHERE 
        ccev.attribute_id = (
            SELECT attribute_id
            FROM eav_attribute
            WHERE attribute_code = 'name'
            AND entity_type_id = (
                SELECT entity_type_id
                FROM eav_entity_type
                WHERE entity_type_code = 'catalog_category'
            )
        )
    AND 
        ccev.value = categoryName
    LIMIT 1;
    
    -- If categoryId is NULL (not found), set it to the default value 43
    IF categoryId IS NULL THEN
        SET categoryId = 3196;
    END IF;
END //

DELIMITER ;\n
";

        return $sqlQueries;

    }
    private function _generateSqlQueriesToCreateBaseProductIfNotAvailableForAssignedProductProcedure($data) {
        try {    
            $productType        = $this->_cleanSqlValue($data["product_type"]);
            // if($productType == 'simple'){
                $sqlQueries  = "\n-- Drop the existing stored procedure\n
DROP PROCEDURE IF EXISTS CreateBaseProductIfNotAvailableForAssignedProduct;
DELIMITER //
    CREATE PROCEDURE CreateBaseProductIfNotAvailableForAssignedProduct(IN input_sku VARCHAR(255))
    BEGIN
        DECLARE sku_count INT;
        -- Start a transaction
        START TRANSACTION;
        -- Check if the first query has any value
        SELECT COUNT(*)
        INTO sku_count
        FROM `catalog_product_entity`
        WHERE sku = input_sku;

        IF sku_count < 1 THEN
            -- If the first query has a value, run the second query
            SET @sku = input_sku;
            SET @product_status = 'Base product not available. Creating the base product.';
            SELECT @sku, @product_status;\n";

            $sqlQueries  .= ($data["product_type"] !== 'simple' ) ? $this->_generateSqlQueriesForConfigurableProduct($data, true) : $this->_generateSqlQueriesForSimpleProduct($data, true);
            $sqlQueries  .= "
        COMMIT;
    ELSE
            SET @product_status = 'base product already available';
            -- SELECT entity_id, sku FROM catalog_product_entity WHERE sku = @sku;
            SELECT entity_id, sku, @product_status AS product_status FROM catalog_product_entity WHERE sku = @sku;
            -- Rollback the transaction
            ROLLBACK;
        END IF;
    END //\n";
                $sqlQueries  .= "DELIMITER ;\n";
                
                return $sqlQueries;
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();

            // Log the error message with additional information
            $errorLogMessage = "Error in Ahy\FlxPoint\Helper\CreateSqlFile. Error Message: $errorMessage. Error in $errorFile on line $errorLine.";
            
            $this->_flxPointApiLogger->info($errorLogMessage);
        }
        // }
        // return $sqlQueries = '';
    }

    private function _cleanSqlValue($value) {
        // Get the database connection
        $connection = $this->resourceConnection->getConnection();

        // Use the quote method to properly escape and quote the value
        $cleanedValue = $connection->quote($value);

        return $cleanedValue;
    }

    private function _createProcedureToUpsertSpecialPrice(): string 
    {
        $sqlQueries = "
        DROP PROCEDURE IF EXISTS upsert_special_price;
        DELIMITER //

        CREATE PROCEDURE upsert_special_price (
            IN p_entity_id INT,
            IN p_value DECIMAL(10,2),
            IN p_special_from_date DATETIME,
            IN p_special_to_date DATETIME
        )
        BEGIN
            -- Attribute IDs for special_price, special_from_date, and special_to_date
            DECLARE special_price_attribute_id INT DEFAULT 78;
            DECLARE special_from_date_attribute_id INT DEFAULT 79;
            DECLARE special_to_date_attribute_id INT DEFAULT 80;

            -- Handle NULL value for special_price
            IF p_value IS NULL THEN
                DELETE FROM catalog_product_entity_decimal 
                WHERE entity_id = p_entity_id 
                AND attribute_id = special_price_attribute_id 
                AND store_id = 0;
                
                DELETE FROM catalog_product_entity_datetime
                WHERE entity_id = p_entity_id 
                AND attribute_id IN (special_from_date_attribute_id, special_to_date_attribute_id)
                AND store_id = 0;

                SELECT CONCAT('Special price and associated dates deleted for entity ID ', p_entity_id) AS output_message;
            ELSE
                -- Handle special_price
                INSERT INTO catalog_product_entity_decimal (entity_id, attribute_id, store_id, value)
                VALUES (p_entity_id, special_price_attribute_id, 0, p_value)
                ON DUPLICATE KEY UPDATE value = p_value;

                SELECT CONCAT('Special price updated for entity ID ', p_entity_id) AS output_message;

                -- Handle special_from_date
                IF p_special_from_date IS NOT NULL THEN
                    INSERT INTO catalog_product_entity_datetime (entity_id, attribute_id, store_id, value)
                    VALUES (p_entity_id, special_from_date_attribute_id, 0, p_special_from_date)
                    ON DUPLICATE KEY UPDATE value = p_special_from_date;

                    SELECT CONCAT('Special from date updated for entity ID ', p_entity_id) AS output_message;
                ELSE
                    DELETE FROM catalog_product_entity_datetime
                    WHERE entity_id = p_entity_id
                    AND attribute_id = special_from_date_attribute_id
                    AND store_id = 0;

                    SELECT CONCAT('Special from date deleted for entity ID ', p_entity_id) AS output_message;
                END IF;

                -- Handle special_to_date
                IF p_special_to_date IS NOT NULL THEN
                    INSERT INTO catalog_product_entity_datetime (entity_id, attribute_id, store_id, value)
                    VALUES (p_entity_id, special_to_date_attribute_id, 0, p_special_to_date)
                    ON DUPLICATE KEY UPDATE value = p_special_to_date;

                    SELECT CONCAT('Special to date updated for entity ID ', p_entity_id) AS output_message;
                ELSE
                    DELETE FROM catalog_product_entity_datetime
                    WHERE entity_id = p_entity_id
                    AND attribute_id = special_to_date_attribute_id
                    AND store_id = 0;

                    SELECT CONCAT('Special to date deleted for entity ID ', p_entity_id) AS output_message;
                END IF;
            END IF;
        END //

        DELIMITER ;

        ";

        return $sqlQueries;
    }


    private function _createProcedureToUpsertProduct($data){
        $name               = $this->_cleanSqlValue($data['name']);
        $shortDescription   = $this->_cleanSqlValue($data['short_description']);
        $description        = $this->_cleanSqlValue($data['description']);
        $brandName          = $this->_cleanSqlValue($data['product_brand']);
        $sku                = $this->_cleanSqlValue($data['sku']);
        $flxpointSku        = $this->_cleanSqlValue($data['flxpoint_sku']);
        $productStatus      = $data["product_online"];
        $productType        = $this->_cleanSqlValue($data["product_type"]);
        $attributeSetCode   = $data['attribute_set_code'];
        $urlKey             = $data['url_key'];
        $price              = $data['price'];
        $specialPrice       = !empty($data['special_price']) ? $data['special_price'] : null;
        $specialPriceFrom   = !empty($data['special_price_from_date']) ? $this->_cleanSqlValue($data['special_price_from_date']) : null;
        $specialPriceTo     = !empty($data['special_price_to_date']) ? $this->_cleanSqlValue($data['special_price_to_date']) : null;
        $updateSpecialPrice = $specialPrice ? sprintf( "CALL upsert_special_price(@mageproduct_id, %s, %s, %s);", $specialPrice, $specialPriceFrom !== null ? $specialPriceFrom : 'null', $specialPriceTo !== null ? $specialPriceTo : 'null' ) : "";
        $qty                = $data['qty'];
        $stockStatus        = $data['is_in_stock'];
        $sellerId           = $data["seller_id"];
        $urlRewrite         = 'CONCAT(@new_sku, ".html")';
        // $urlRewrite         = $isBaseProduct ? ("'" . $data['url_key'] . ".html'") : 'CONCAT(@new_sku, ".html")';

        $sqlQueries  = "
DROP PROCEDURE IF EXISTS UpsertMarketplaceProduct;
DELIMITER //

CREATE PROCEDURE UpsertMarketplaceProduct(IN p_seller_id INT, IN p_product_upc VARCHAR(255))
BEGIN
    DECLARE v_mageproduct_id INT;
    DECLARE v_new_sku VARCHAR(255);

    -- Check if the record exists
    SELECT mageproduct_id INTO v_mageproduct_id FROM marketplace_product WHERE seller_id = p_seller_id AND product_upc = p_product_upc limit 1;

    -- If the result set is not empty, update the updated_at column
    IF v_mageproduct_id IS NOT NULL THEN
        SET @product_status = 'product available. Updating the product details';
        SET @mageproduct_id = v_mageproduct_id;
        SELECT sku INTO v_new_sku FROM catalog_product_entity WHERE entity_id = @mageproduct_id;
        SET @new_sku = v_new_sku;
        SELECT @product_status, @mageproduct_id;

        -- Set status of new product using entity_id
        INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) VALUES ('97', '0', @mageproduct_id, '$productStatus'), ('136', '0', @mageproduct_id, '2') ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Set Product Name of product 
        -- INSERT INTO catalog_product_entity_varchar (attribute_id, store_id, entity_id, value) VALUES ('73', '0', @mageproduct_id, $name) ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Insert data into catalog_product_entity_text table for short description
        -- INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (76, 0, @mageproduct_id, $shortDescription) ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Insert data into catalog_product_entity_text table for long description
        -- INSERT INTO catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (75, 0, @mageproduct_id, $description) ON DUPLICATE KEY UPDATE value = VALUES(value);

        -- Insert data into cataloginventory_stock_item table for product stock
        INSERT INTO cataloginventory_stock_item (product_id, stock_id, qty, is_in_stock) VALUES (@mageproduct_id, 1, $qty, $stockStatus) ON DUPLICATE KEY UPDATE `qty` = VALUES(`qty`), `is_in_stock` = VALUES(`is_in_stock`);

        -- Insert data into cataloginventory_stock_status table for product stock status
        INSERT INTO cataloginventory_stock_status (product_id, website_id, stock_id, qty, stock_status) VALUES (@mageproduct_id, 0, 1, $qty, $stockStatus) ON DUPLICATE KEY UPDATE `qty` = VALUES(`qty`), `stock_status` = VALUES(`stock_status`);

        -- Insert data into catalog_product_entity_decimal table for product price
        INSERT INTO catalog_product_entity_decimal (attribute_id, store_id, entity_id, value) VALUES ('77', '0', @mageproduct_id, '$price') ON DUPLICATE KEY UPDATE value = VALUES(value);
        
        CALL InsertProductOptionAttributeProcedure('product_brand', 0, @mageproduct_id, 'Product Brand', $brandName);
        
        -- Insert tax
        INSERT INTO catalog_product_entity_int (entity_id, attribute_id, value) VALUES (@mageproduct_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'tax_class_id'), 2) ON DUPLICATE KEY UPDATE value = VALUES(value);

        INSERT INTO `catalog_product_entity_varchar` (`entity_id`, `attribute_id`, `store_id`, `value`) VALUES (@mageproduct_id, (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'flxpoint_sku'), '0', $flxpointSku) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

        $updateSpecialPrice

        -- Assuming the correct route for your Magento version
        SET @target_path = CONCAT('catalog/product/view/id/', @mageproduct_id);
        -- Insert product URL key into URL rewrite table
        -- INSERT INTO url_rewrite (entity_type, entity_id, store_id, request_path, target_path, is_autogenerated) VALUES ('product', @mageproduct_id, 1, $urlRewrite, @target_path, 1) ON DUPLICATE KEY UPDATE target_path = @target_path, `request_path` = VALUES(`request_path`);

    -- If the result set is empty, insert a new record
    ELSE
        SET @product_status = 'product not available. Adding the products';
        SELECT @product_status;
        ";
        $sqlQueries .= ($data["product_type"] == 'configurable') ? $this->_generateSqlQueriesForConfigurableProduct($data) : $this->_generateSqlQueriesForSimpleProduct($data);
        $sqlQueries .= $this->_assignProductQuery($data);
        $sqlQueries  .= "END IF;
END //

DELIMITER ;

SET @seller_id = (SELECT seller_id FROM marketplace_userdata WHERE flxPoint_seller_id = $sellerId);

CALL UpsertMarketplaceProduct(@seller_id, $sku);

        ";
        
        return $sqlQueries;
    }

    private function _assignProductQuery($data){
        $name               = $this->_cleanSqlValue($data['name']);
        $shortDescription   = $this->_cleanSqlValue($data['short_description']);
        $description        = $this->_cleanSqlValue($data['description']);
        $sku                = $this->_cleanSqlValue($data['sku']);
        $productStatus      = $data["product_online"];
        $productType        = $this->_cleanSqlValue($data["product_type"]);
        $attributeSetCode   = $data['attribute_set_code'];
        $urlKey             = $data['url_key'];
        $price              = $data['price'];
        $qty                = $data['qty'];
        $stockStatus        = $data['is_in_stock'];
        $sellerId           = $data["seller_id"];
        $updatedDescription = ($description !== "''") ? $description : "'description'";

        $sqlQueries = "\n-- seller assignment
SET @seller_id = (SELECT seller_id FROM marketplace_userdata WHERE flxPoint_seller_id = $sellerId);

INSERT INTO marketplace_assignproduct_items (product_id, owner_id, seller_id, qty, price, description, options, image, `condition`, tax_class, type, created_at, status, shipping_country_charge, assign_product_id) VALUES (@entity_id, 0, @seller_id, $qty, $price, $updatedDescription, '', '', 1, 2, $productType, CURRENT_TIMESTAMP, 1, NULL, @new_entity_id);\n
INSERT INTO marketplace_product (`mageproduct_id`, `adminassign`, `seller_id`, `status`, `created_at`, `updated_at`, `is_approved`, `product_upc`) VALUES (@new_entity_id, '1', @seller_id, '$productStatus', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '1', $sku) ON DUPLICATE KEY UPDATE `adminassign` = VALUES(`adminassign`), `seller_id` = VALUES(`seller_id`), `status` = VALUES(`status`), `created_at` = VALUES(`created_at`), `is_approved` = VALUES(`is_approved`), `product_upc` = VALUES(`product_upc`), `updated_at` = VALUES(`updated_at`);
INSERT  INTO `catalog_product_entity_int` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('97', '0', @new_entity_id, '$productStatus') ON DUPLICATE KEY UPDATE `attribute_id` = VALUES(`attribute_id`), `store_id` = VALUES(`store_id`), `entity_id` = VALUES(`entity_id`), `value` = VALUES(`value`);\n
INSERT  INTO `catalog_product_entity_text` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('75', '0', @new_entity_id, $updatedDescription) ON DUPLICATE KEY UPDATE `attribute_id` = VALUES(`attribute_id`), `store_id` = VALUES(`store_id`), `entity_id` = VALUES(`entity_id`), `value` = VALUES(`value`);\n
INSERT  INTO `catalog_product_entity_int` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('136', '0', @new_entity_id, '0') ON DUPLICATE KEY UPDATE `attribute_id` = VALUES(`attribute_id`), `store_id` = VALUES(`store_id`), `entity_id` = VALUES(`entity_id`), `value` = VALUES(`value`);\n
INSERT  INTO `catalog_product_entity_decimal` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('77', '0', @new_entity_id, $price) ON DUPLICATE KEY UPDATE `attribute_id` = VALUES(`attribute_id`), `store_id` = VALUES(`store_id`), `entity_id` = VALUES(`entity_id`), `value` = VALUES(`value`);\n
INSERT INTO `catalog_product_entity` (`entity_id`, `sku`, `updated_at`) VALUES (@new_entity_id, @new_sku, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE `entity_id` = VALUES(`entity_id`), `updated_at` = VALUES(`updated_at`), `sku` = VALUES(`sku`);\n
";
    return $sqlQueries;
    }

    private function _upsertAttributeAndTheirValues(){
        $sqlQueries = "";

        return $sqlQueries;
    }

    private function _commonProcedures(){
        $sqlFileContent  = $this->_getCategoryIdByCategoryNameProcedure();
        $sqlFileContent .= $this->_upsertAttributeAndTheirValues();
        $sqlFileContent .= $this->_insertProductOptionAttributeProcedure();
        $sqlFileContent .= $this->_insertProductSuperAttributeProcedure();
        
        return $sqlFileContent;
    }
}