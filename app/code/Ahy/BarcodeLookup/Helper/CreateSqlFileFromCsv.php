<?php

declare(strict_types=1);

namespace Ahy\BarcodeLookup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Ahy\BarcodeLookup\Logger\Logger as BarcodeLookupApiLogger;

use Ahy\BarcodeLookup\Config\BarcodeAttributeConfig;

class CreateSqlFileFromCsv extends AbstractHelper
{
    private $_barcodeLookupApiLogger;
    private $resourceConnection;

    public function __construct(
        Context $context,
        BarcodeLookupApiLogger $barcodeLookupApiLogger,
        ResourceConnection $resourceConnection
    ) {
        $this->_barcodeLookupApiLogger = $barcodeLookupApiLogger;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function createSqlFileFromCsv(string $csvFile, string $sqlFile)
    {
        try {
            if (!file_exists($csvFile) || filesize($csvFile) === 0) {
                throw new \Exception('CSV file is missing or empty: ' . $csvFile);
            }

            $handle = fopen($csvFile, 'r');
            if (!$handle) {
                throw new \Exception('Unable to open the CSV file for converting to SQL.');
            }

            $sqlHandle = fopen($sqlFile, 'w');
            if (!$sqlHandle) {
                throw new \Exception('Unable to open the SQL file for writing.');
            }

            $header = fgetcsv($handle);
            if (!$header) {
                throw new \Exception('Invalid CSV file format.');
            }

            // Write the SQL procedure creation statement
            $procedure = $this->createUpsertProcedure();
            fwrite($sqlHandle, $procedure . "\n");

            $paramMap = BarcodeAttributeConfig::BARCODE_ATTRIBUTES;

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) !== count($paramMap)) {
                    $this->logError('CSV row does not match expected column count. Skipping row: ' . json_encode($data));
                    continue;
                }

                // Sanitize and prepare values

                $sanitizedData = array_map(function ($value, $key) use ($paramMap) {
                    $sanitizedValue = $value;

                    if ($paramMap[$key] === 'barcode_number') {
                        // $value = trim($value, '"'); // Remove the trailing "
                        // $value = str_replace('="', '', $value); // Remove the leading ="

                        $sanitizedValue = "'" . addslashes($value) . "'";   // Leading zero matters with UPC, along with the extra spaces.
                    } else {
                        $sanitizedValue = is_numeric($value) ? (float) $value : "'" . addslashes(trim($value)) . "'";
                        $sanitizedValue = $this->cleanSqlValue($value);
                    }

                    return $sanitizedValue;
                }, $data, array_keys($data));

                // Construct the SQL statement
                $sql = "CALL upsert_product_attributes(" . implode(", ", $sanitizedData) . ");\n";
                
                // Write the SQL statement to the SQL file
                fwrite($sqlHandle, $sql);
            }

            fclose($handle);
            fclose($sqlHandle);
            return ['status' => 'success', 'message' => 'SQL file created successfully from CSV'];
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError('Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            $returnMessageArray[] = ['status' => 'error', 'message' => 'Error when creating SQL file from CSV.'];
        }
    }

    private function createUpsertProcedure(): string
    {
        return "
DROP PROCEDURE IF EXISTS upsert_product_attributes;   -- Drop the existing stored procedure

DELIMITER //
CREATE PROCEDURE upsert_product_attributes(
    IN p_upc_number VARCHAR(255),
    IN p_mpn VARCHAR(255),
    IN p_model VARCHAR(255),
    IN p_asin VARCHAR(255),
    IN p_title TEXT,
    IN p_manufacturer TEXT,
    IN p_brand TEXT,
    IN p_age_group VARCHAR(255),
    IN p_color VARCHAR(255),
    IN p_gender VARCHAR(255),
    IN p_material TEXT,
    IN p_pattern TEXT,
    IN p_format VARCHAR(255),
    IN p_size VARCHAR(255),
    IN p_length VARCHAR(255),
    IN p_width VARCHAR(255),
    IN p_height VARCHAR(255),
    IN p_weight VARCHAR(255),
    IN p_description TEXT,
    IN p_images TEXT,
    IN p_barcode_last_update DATETIME
)
BEGIN
    DECLARE v_product_id INT;
    DECLARE v_attr_mpn INT;
    DECLARE v_attr_model INT;
    DECLARE v_attr_asin INT;
    DECLARE v_attr_title INT;
    DECLARE v_attr_manufacturer INT;
    DECLARE v_attr_brand INT;
    DECLARE v_attr_age_group INT;
    DECLARE v_attr_color INT;
    DECLARE v_attr_gender INT;
    DECLARE v_attr_material INT;
    DECLARE v_attr_pattern INT;
    DECLARE v_attr_format INT;
    DECLARE v_attr_size INT;
    DECLARE v_attr_length INT;
    DECLARE v_attr_width INT;
    DECLARE v_attr_height INT;
    DECLARE v_attr_weight INT;
    DECLARE v_attr_description INT;
    DECLARE v_attr_images INT;
    DECLARE v_attr_barcode_last_update INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    -- Fetch product_id using UPC Number
    SELECT entity_id INTO v_product_id
    FROM catalog_product_entity_varchar
    WHERE attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'upc_number')
    AND value = p_upc_number
    LIMIT 1;

    IF v_product_id IS NOT NULL THEN
        -- Cache attribute IDs to avoid repeated queries
        SELECT attribute_id INTO v_attr_mpn FROM eav_attribute WHERE attribute_code = 'barcode_mpn';
        SELECT attribute_id INTO v_attr_model FROM eav_attribute WHERE attribute_code = 'barcode_model';
        SELECT attribute_id INTO v_attr_asin FROM eav_attribute WHERE attribute_code = 'barcode_asin';
        SELECT attribute_id INTO v_attr_title FROM eav_attribute WHERE attribute_code = 'barcode_title';
        SELECT attribute_id INTO v_attr_manufacturer FROM eav_attribute WHERE attribute_code = 'barcode_manufacturer';
        SELECT attribute_id INTO v_attr_brand FROM eav_attribute WHERE attribute_code = 'barcode_brand';
        SELECT attribute_id INTO v_attr_age_group FROM eav_attribute WHERE attribute_code = 'barcode_age_group';
        SELECT attribute_id INTO v_attr_color FROM eav_attribute WHERE attribute_code = 'barcode_color';
        SELECT attribute_id INTO v_attr_gender FROM eav_attribute WHERE attribute_code = 'barcode_gender';
        SELECT attribute_id INTO v_attr_material FROM eav_attribute WHERE attribute_code = 'barcode_material';
        SELECT attribute_id INTO v_attr_pattern FROM eav_attribute WHERE attribute_code = 'barcode_pattern';
        SELECT attribute_id INTO v_attr_format FROM eav_attribute WHERE attribute_code = 'barcode_format';
        SELECT attribute_id INTO v_attr_size FROM eav_attribute WHERE attribute_code = 'barcode_size';
        SELECT attribute_id INTO v_attr_length FROM eav_attribute WHERE attribute_code = 'barcode_length';
        SELECT attribute_id INTO v_attr_width FROM eav_attribute WHERE attribute_code = 'barcode_width';
        SELECT attribute_id INTO v_attr_height FROM eav_attribute WHERE attribute_code = 'barcode_height';
        SELECT attribute_id INTO v_attr_weight FROM eav_attribute WHERE attribute_code = 'barcode_weight';
        SELECT attribute_id INTO v_attr_description FROM eav_attribute WHERE attribute_code = 'barcode_description';
        SELECT attribute_id INTO v_attr_barcode_last_update FROM eav_attribute WHERE attribute_code = 'barcode_last_update';

        -- Note: The p_barcode_last_update parameter value is ignored.

        -- Update VARCHAR attributes (only if the value is not empty)
        IF p_mpn IS NOT NULL AND p_mpn != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_mpn, p_mpn)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_model IS NOT NULL AND p_model != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_model, p_model)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_asin IS NOT NULL AND p_asin != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_asin, p_asin)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_age_group IS NOT NULL AND p_age_group != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_age_group, p_age_group)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_color IS NOT NULL AND p_color != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_color, p_color)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_gender IS NOT NULL AND p_gender != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_gender, p_gender)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_format IS NOT NULL AND p_format != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_format, p_format)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_size IS NOT NULL AND p_size != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_size, p_size)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_length IS NOT NULL AND p_length != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_length, p_length)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_width IS NOT NULL AND p_width != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_width, p_width)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_height IS NOT NULL AND p_height != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_height, p_height)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_weight IS NOT NULL AND p_weight != '' THEN
            INSERT INTO catalog_product_entity_varchar (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_weight, p_weight)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        -- Update TEXT attributes (only if the value is not empty)
        IF p_title IS NOT NULL AND p_title != '' THEN
            INSERT INTO catalog_product_entity_text (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_title, p_title)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_manufacturer IS NOT NULL AND p_manufacturer != '' THEN
            INSERT INTO catalog_product_entity_text (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_manufacturer, p_manufacturer)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_brand IS NOT NULL AND p_brand != '' THEN
            INSERT INTO catalog_product_entity_text (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_brand, p_brand)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_material IS NOT NULL AND p_material != '' THEN
            INSERT INTO catalog_product_entity_text (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_material, p_material)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_pattern IS NOT NULL AND p_pattern != '' THEN
            INSERT INTO catalog_product_entity_text (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_pattern, p_pattern)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        IF p_description IS NOT NULL AND p_description != '' THEN
            INSERT INTO catalog_product_entity_text (entity_id, attribute_id, value)
            VALUES (v_product_id, v_attr_description, p_description)
            ON DUPLICATE KEY UPDATE value = VALUES(value);
        END IF;

        INSERT INTO catalog_product_entity_datetime (entity_id, attribute_id, value)
        VALUES (v_product_id, v_attr_barcode_last_update, NOW())
        ON DUPLICATE KEY UPDATE value = NOW();

    END IF;

    COMMIT;
END //
DELIMITER ;
        ";
    }

    public function logError(string $message): void
    {
        $this->_barcodeLookupApiLogger->error($message);
    }

    /**
     * The logInfo function logs an informational message using the barcode lookup API logger.
     * 
     * @param string message The `logInfo` function takes a string parameter named ``, which is
     * used as the message to be logged by the `_barcodeLookupApiLogger`.
     */

     public function logInfo(string $message): void
     {
        $this->_barcodeLookupApiLogger->info($message);
     }

    private function removeUnsafeUtf8(string $text): string {
        // Keep only ASCII chars: hex 0x20 (space) to 0x7E (~)
        return preg_replace('/[^\x20-\x7E]/u', '', $text);
    }

    private function cleanSqlValue($value) {
        // Decode JSON-style unicode sequences like \u2265
        if (is_string($value)) {
            $decoded = json_decode('"' . $value . '"');
            if ($decoded !== null) {
                $value = $decoded;
            }
        }

        // Remove unsafe characters (non-ASCII)
        $value = $this->removeUnsafeUtf8($value);

        // Sanitize for SQL
        return is_numeric($value)
            ? (float) $value
            : "'" . addslashes(trim($value)) . "'";
    }

}
