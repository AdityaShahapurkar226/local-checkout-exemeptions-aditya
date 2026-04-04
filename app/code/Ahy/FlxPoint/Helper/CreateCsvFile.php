<?php
/**
 * Copyright © Ahy Consulting All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\FlxPoint\Helper;
use Psr\Log\LoggerInterface;

use Magento\Framework\App\Helper\AbstractHelper;

class CreateCsvFile extends AbstractHelper
{
    protected LoggerInterface $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger

    ) {
        parent::__construct($context);
        $this->logger = $logger;

    }

    public function createCsvFile($folderPath, $filePath, $jsonFilePath, $lastUpdatedAt): array
    {
        try {
            $returnMsgArr = [];

            $csvArr     = ["sku", "flxpoint_sku", "store_view_code", "attribute_set_code", "product_type", "categories", "product_websites", "name", "description", "short_description", "weight", "product_online", "tax_class_name", "visibility", "price", "special_price", "special_price_from_date", "special_price_to_date", "url_key", "product_brand", "meta_title", "meta_keywords", "meta_description", "created_at", "updated_at", "new_from_date", "new_to_date", "display_product_options_in", "map_price", "msrp_price", "map_enabled", "gift_message_available", "custom_design", "custom_design_from", "custom_design_to", "custom_layout_update", "page_layout", "product_options_container", "msrp_display_actual_price_type", "additional_attributes", "qty", "out_of_stock_qty", "use_config_min_qty", "is_qty_decimal", "allow_backorders", "use_config_backorders", "min_cart_qty", "use_config_min_sale_qty", "max_cart_qty", "use_config_max_sale_qty", "is_in_stock", "notify_on_stock_below", "use_config_notify_stock_qty", "manage_stock", "use_config_manage_stock", "use_config_qty_increments", "qty_increments", "use_config_enable_qty_inc", "enable_qty_increments", "is_decimal_divided", "website_id", "deferred_stock_update", "use_config_deferred_stock_update", "related_skus", "crosssell_skus", "upsell_skus", "hide_from_product_page", "configurable_variation_labels", "bundle_price_type", "bundle_sku_type", "bundle_price_view", "bundle_weight_type", "bundle_values", "associated_skus", "configurable_variations", "base_image", "base_image_label", "small_image", "small_image_label", "thumbnail_image", "thumbnail_image_label", "additional_images", "additional_image_labels", "seller_id", "is_seller_active", "configurable_variation_labels_values", "image_path", "additional_image_path"];
            $csvArr2    = ["sku", "base_image", "additional_images", "seller_id"];
            
            // Read the contents of the JSON file
            $isJsonFileAvailable = file_exists(filename: $jsonFilePath);
            if (!$isJsonFileAvailable) {
                $returnMsgArr[] = "<info>Could not read json file: {$jsonFilePath}<info>";
                $returnMsgArr[] = "<info>Create the Json file using the following command: bin/magento ahy:flxpoint:import-product -j<info>";
            }
            else {
                $jsonFileContents       = file_get_contents(filename: $jsonFilePath);
                // Replace "][" with ","
                $jsonFileContents       = str_replace(search: '}][{', replace: '},{', subject: $jsonFileContents);

                // Decode the JSON contents into an associative array
                $productDetailsArr      = json_decode(json: $jsonFileContents, associative: true);

                // Check if decoding was successful
                if ($productDetailsArr === null && json_last_error() !== JSON_ERROR_NONE) {
                    // Handle JSON decoding error
                    throw new \Exception(message: 'Error decoding JSON file: ' . json_last_error_msg());
                }
                $productCsvFile         = fopen(filename: $filePath, mode: 'w');
                $productImageCsvFile    = fopen(filename: (string) $folderPath . '/product-image.csv', mode: 'w');
                // Write header
                fputcsv(stream: $productCsvFile, fields: $csvArr, separator: ",");
                fputcsv(stream: $productImageCsvFile, fields: $csvArr2, separator: ",");

                $imageCsvFilePath       = (string) $folderPath . "product_image_path.csv";
                // Check if the CSV file exists
                if (!file_exists(filename: $imageCsvFilePath)) {
                    // If the file doesn't exist, create it
                    $created            = touch(filename: $imageCsvFilePath);
                    
                    if (!$created) {
                        echo "Error: Failed to create the CSV file!";
                    }
                }
                // Open the image CSV file for reading
                $imageCsvFile           = fopen(filename: $imageCsvFilePath, mode: 'r');

                // Read the headers from the image CSV file
                $imageHeaders           = fgetcsv(stream: $imageCsvFile);
                
                $rowData = [];
                $excludeTheMainProduct      = [
                    'unimount', //simtek
                    'DDR-02-0001','SDS-01-0001','SDM-01-0001','CIR-01-0001','KIR-01-0001','PPR-02-0001','SPS-01-0001','SHR-01-0001','SHR-01-0002','SHR-01-0003','SHR-01-0004','SHR-01-0005','SHR-02-0001','SHR-02-0002','SHR-02-0003','SHR-02-0004','SHR-02-0005',//Freaks of Nature
                    'old-fashioned-elk-liver-crisps-organic-spices','elk-liver-crisps-100-grass-fed-finished', //Grazly
                    'routeins',
                    'mystery-pair', //Outway
                    'tungsten-shakey-jig-head', 'tungsten-propelor-jig','tungsten-bladed-swimbait-hooks','tungsten-nail-weight','flipping-weight','tungsten-worm-weight','teardrop-drop-shot-weight','tungsten-skinny-drop-shot-weight','tungsten-meister-nail-weight', //Grandbass
                    '3600-drift-2-latch-tray-green','mossy-oak-blades-timber-pack-waterfowl-series','trophy-cooler','2-gun-rolltop-waterproof-case','big-bait-bag-10','big-bait-bag-14','lure-wrap-2-pk-medium','bass-sack-medium','bass-sack-large','bass-sack-small','rigger-series-big-bait-bag','EVCT34745','evolution-rod-tube-54','lure-wrap-2-pk-large','54-hill-country-ii-rifle-case','ballistix-terminal-tray','smallmouth-3700-backpack','sportsmans-warehouse-backpack-tackle-bag','largemouth-3-0-3600-tackle-bag','largemouth-3-0-3600-tackle-bag','sportsmans-warehouse-3600-tackle-bag','ballistix-terminal-tray','largemouth-3-0-3700-tackle-bag','largemouth-3-0-24-boat-duffel','largemouth-3-0-backpack','ballistix-hangr-3-7d','largemouth-3-0-sling-pack','largemouth-3-0-18-boat-duffel','ballistix-hangr-3-7d-1','ballistix-3-7-tray','3600-2-latch-drift-tray-4-pk-gbrs','3700-drift-2-latch-tray-orange','3600 Drift 2-Latch Tray - Seafoam','3600-drift-2-latch-tray-orange','3700-drift-2-latch-tray-green','3700-drift-2-latch-tray-seafoam','largemouth-3-0-tackle-backpack-bps-exclusive','3700-drift-2-latch-tray-blue','3600-drift-2-latch-tray-blue','3600-drift-2-latch-tray-seafoam', //Evolution Outdoor
                    'patriot-promo-t','huntchef-ldseasoning','90001', //forloh 
                    'clyde-protection-plan-dca81823-d269-4ab0-8fc0-470280cdd5c1-1',  //Ice Barrel
                    'd-tech-4-0-liner-glove','2025-kelsey-short-1','avery-jacket', //DSG
                    'roller-bag', 'chasing-deer-childrens-book', '60-quart-ice-vault-roto-molded-cooler-with-wheels-custom-14d4297c-8d13-4a83-8800-e821e9d7b6ef', 'blue-seventy-two-pro-series-red-deluxe-3-day-emergency-kit-for-1-person', 'family-pack-blue-seventy-two-pro-series-deluxe-3-day-emergency-kit-for-1-person-1', 'paracord-charging-cable', 'klymit-everglow-light-tube-lg', 'spectre-accessories-kit', 'black-diamond-headlamp-spotlite-160', 'renegade-grey', 'drift-series-3700-tackle-backpack-1', 'the-sampler-wholesale', 'replacement-magnetic-antenna', 'braided-metal-antenna-extension', 'water-resistant-silicone-case', 'simtek-duo-smart-motion-sensor', 'protection-plan', 'ironman-performance-antenna', 'upgraded-antenna-fat-boy', 'extras-pak', '4-candy-craw', 'tote-bag', 'i-eat-ass', 'the-hammer', 'donkey-snacks-top-water', 'steak-bj-day', 'p-e-t-a', 'p-e-t-a-1', 'p-e-t-a-2', 'i-eat-ass-hat', 'huntchef-hjjerky-kit', 'huntchef-ham-kit', 'huntchef-cbsaussagekit', 'huntchef-saussagekit', 'huntchef-hwsausagekit', 'huntchef-mmsausagekit', 'huntchef-ccseasoning', 'huntchef-seasoning', 'huntchef-sobseasoning', 'huntchef-fdseasoning', 'huntchef-adseasoning', 'huntchef-soseasoning', 'huntchef-ttseasoning', 'huntchef-rdseasoning', 'womens-silverlux-leggings','6-curl-tail-worm', '2026-buzzbait-version-2-3' //MonsterBass
                ];
                $excludeProductVariantUpc   = [
                    850042372616,123123456781,111111122545,860011467175,111111122445,860011467168,123123456700,123123456701,123123456788,850026688221, 856124008626, 856124008688, 850026688085, 856124008022, 856124008619, 850026688016, 850026688528, 856124008138, 850026688245, 850026688078, 856124008701, 856124008091, 856124008015, 856124008374, 856124008121, 856124008312, 850026688009, 876716006427, 876716002924, 655295799353, 196852654635, 39339688542, 646816966873, 646816966866, 850054042101, 646816966842, 646816966859, 653981522056, 661646954562, 850054042088, 850054042095
                ];
                /**
                 * @include specific products
                 */
                $includeOnlySpecificMainProduct                 = [
                    '5149738753', '5149738754-1', '5149738754', '5149737711', '5149738754-1-1', '5149737710', '5149737709', //from sasquatch tea
                ];
                $importSpecificMainProductFromTheseSellerIds    = [
                    '985348',   // Sasquatch Tea Company
                    '994202',   // Surfside Supply
                    '975921',   //Grill Your Ass Off
                    '993820',   //Maniac Outdoors
                    '1015663',  //White Duck Outdoors
                ];
                $sellerToTakeProductUpcFromInventorySku         = [
                    993547, //Coyote Eyewear
                    993458, //DSG outerwear
                ];
                $sellerIdToGetCustomAttributeForSpecificSeller = [
                    993916, // Ice Barrel
                    1015663 //White Duck Outdoors
                ];
                $sourceIdArrToChangeTheUpcFromFloatToString     = [ 
                    994017, //Echo Water
                    989138, //Redi-Edge
                    992242, //Pontoon Boat Solutions
                    985348, //Sasquatch Tea Company
                    995581, //Squatch Survival Gear
                    1015663,//White Duck Outdoors
                ];
                $sellerIdsWithIncreasedQuantity                 = [
                    994891, //Hot Bento
                    992242, //pontoon boats solutions
                    985348, //Sasquatch Tea Company
                    993628, //Blue Ribbon Nets
                ];
                $specialDiscountPriceForSeller                  = [
                    // 993916, // Ice Barrel
                    // 993458, //DSG Outerwear
                    // 976092, //Forloh
                    // 975921, //Grill Your Ass Off
                ];
                $specialDiscountPriceForSellerFromDate          = [
                    // 993916 => '2024-11-20 00:00:00', // Ice Barrel
                    // 993458 => '2024-11-29 00:00:00', // DSG Outerwear
                    // 975921 => '2024-11-29 00:00:00', // Grill Your Ass Off
                    // 976092 => '2024-11-28 00:00:00', //Forloh
                ];
                $specialDiscountPriceForSellerToDate            = [
                    // 993916 => '2024-12-03 00:00:00', // Ice Barrel
                    // 993458 => '2024-12-02 00:00:00', // DSG Outerwear
                    // 975921 => '2024-12-02 00:00:00', // Grill Your Ass Off
                    // 976092 => '2024-12-02 00:00:00', // Forloh
                ];
                $specialPriceDiscountPercentageForSeller        = [
                    // 993916 => 15, // Ice Barrel
                    // 993458 => 20, // DSG Outerwear
                    // 975921 => 30, // Grill Your Ass Off
                    // 976092 => 30, //Forloh
                ];
                $importProductsFromSpecificAttributes            =[
                    'mens-spring-25','mens-fall-24', 'mens-resort-24', 'fishing', 'accessories', //Surfside Supply
                    'Everest', //Grill Your Ass Off
                ];
                $importSpecificVariantsSeller                   = [
                    994150, //Grazly,   
                    993457, //Oru Kayak                                           
                ];
                $specificVariants                               = [
                    'NoCoconutTallow - 1 Unit', 'OldFashionedBisonLiver - 2 Pack', 'OldFashionedElkHeart - 1 Pack', 'OldFashionedHeart -1 Pack', 'OldFashionedVrilDust - 2 Pack', 'PlainBisonHeart - 2 Pack', 'PlainBisonLiver - 2 Pack', 'PlainElkHeart - 2 Pack', 'PlainVrilDust - 2 Pack', 'SmokyBarbecueBisonLiver - 2 Pack', 'SmokyBarbecueElkLiver - 2 Pack', 'SmokyBisonHeart -1 Pack', 'SmokyVrilDust - 2 Pack', 'TallowUnscented - 1 Unit',
                    'OKY602-ORA-LK','OKY501-ORA-IN-01','OKY302-ORA-LT','OKY301-BLA-LTS-01','OKY102-ORA-ST','OKY203-ORA-XT','OPD101-WHI-00','OPK101-GRE-00','OFL101-GRE-00','OSP101-BLA-00','OSP100-BLA-00','OSW101-BLA-00','OPB101-BLA-00','OWB101-GRE-24','OCP101-BLA-00','OFP101-BLU-00','OPF102-GRE-XL','OPF102-GRE-SM','CFD101-ORA-L','CFD101-ORA-M','CFD101-ORA-XL','CFD101-ORA-S','CFD101-ORA-XS','OGS101-BLA-00','OSS101-GRE-00','ORE102-ORI-00','FCB101-BLA-00','PLG101-BLA-00','STB101-BLA-00','PK-CAMP-BE-FLIPPOP','PK-CAMP-BE-SWITCHFLIP','OFL101-GRE-01','OKY402-ORA-TT-01','OKY501-BLA-INS-01', //oru kayak
                ];  
                
                $changeProductBrandSellerIds = [
                    '994202' => 'Surfside Supply',  // Surfside Supply
                    '993628' => 'Blue Ribbon Nets',     // Blue Ribbon Nets
                    '995262' => 'Outway',               //Outway
                    '995579' => 'Solstice Watersports', //Solstice Watersports
                    '995581' => 'Squatch Survival Gear',//Squatch Survival Gear
                    '1015663' => 'White Duck Outdoors',  //White Duck Outdoors
                ];
                $upcArr = [];
                $addPreifxToSellersConvertedUPCtoSKU            =[
                    995262,     //Outway
                    995579,     //Solstice Watersports
                    995581,     //Squatch Survival Gear
                    1015663,    //White Duck Outdoors

                ];
                $productTitles = [
                    "Oru Inlet Sport", "Oru Beach","Oru Haven",
                ];
                $sellerShouldContainEverestTag = [  //Should also place the  sellerId in $importSpecificMainProductFromTheseSellerIds
                    993820,//Maniac Outdoors
                    1015663, //White Duck Outdoors 

                ];
            
                foreach ($productDetailsArr as $product) {
                    $countProductVariants   = count(value: $product['variants']);
                    $productSourceId        = $product['sourceId'];
                    $mainProductDescription = $product['description'];
                    $needToChangeTheUpcFromFloatToString            = in_array(needle: $productSourceId, haystack: $sourceIdArrToChangeTheUpcFromFloatToString);
                    $productVariant         = $product['variants'];
                    $productType            = ($countProductVariants !== 0) ? (($countProductVariants == 1) ? 'simple' : 'configurable') : false;
                    $customAttributes       = $product['customFields'];
                    // Retrieve custom attributes from the product
                    $customAttributes       = $product['customFields'];
                    $customAttributeToCheck = ['features', 'care_guide', 'materials', 'description_tag', 'specifications', 'details'];
                    // Initialize an associative array to hold attribute values
                    $attributeValues = array_fill_keys(keys: $customAttributeToCheck, value: null);
                    // Iterate through custom attributes and store relevant values
                    foreach ($customAttributes as $attribute) {
                        if (!empty($attribute['value']) && in_array(needle: $attribute['name'], haystack: $customAttributeToCheck)) {
                            $attributeValues[$attribute['name']] = $attribute['value'];
                        }
                    }

                    // Extract the values into individual variables
                    $features                       = $attributeValues['features'];
                    $careGuide                      = $attributeValues['care_guide'];
                    $materials                      = $attributeValues['materials'];
                    $descriptionTag                 = $attributeValues['description_tag'];
                    $additionalInfo                 = '';
                    $flxpointProductDetail          = null;
                    $flxpointProductSpecification   = null;

                    if(in_array(needle: $productSourceId, haystack: $sellerIdToGetCustomAttributeForSpecificSeller)){

                        if($productSourceId == 993916){
                            // Decode JSON
                            $details        = !empty($attributeValues['details']) ? json_decode(json: $attributeValues['details'], associative: true) : null;
                            $specifications = !empty($attributeValues['specifications']) ? json_decode(json: $attributeValues['specifications'], associative: true) : null;
                            
                            // Validate JSON and extract values if data exists
                            $detailsValues          = [];
                            $specificationsValues   = [];
        
                            if ($details !== null && isset($details['children']) && !empty($details['children'])) {
                                $detailsValues = $this->extractValues(node: $details);
                            }
        
                            if ($specifications !== null && isset($specifications['children']) && !empty($specifications['children'])) {
                                $specificationsValues = $this->extractValues(node: $specifications);
                            }
        
                            // Combine values into strings
                            $flxpointProductDetail          = !empty($detailsValues) ? implode(separator: ' ', array: $detailsValues) : null;
                            $flxpointProductSpecification   = !empty($specificationsValues) ? implode(separator: ' ', array: $specificationsValues) : null;
                        }
                        if($productSourceId == 1015663){
                            $detailsValues          = [];
                            $details      = !empty($attributeValues['tab_1_description']) ? json_decode(json: $attributeValues['tab_1_description'], associative: true) : null;
                            if ($details !== null && isset($details['children']) && !empty($details['children'])) {
                                $detailsValues = $this->extractValues(node: $details);
                            }
                            $flxpointProductDetail          = !empty($detailsValues) ? implode(separator: ' ', array: $detailsValues) : null;

                            $specificationsValues   = [];
                            $specifications = !empty($attributeValues['tab_2_description']) ? json_decode(json: $attributeValues['tab_2_description'], associative: true) : null;
                            if ($specifications !== null && isset($specifications['children']) && !empty($specifications['children'])) {
                                $specificationsValues = $this->extractValues(node: $specifications);
                            }
                            $flxpointProductSpecification   = !empty($specificationsValues) ? implode(separator: ' ', array: $specificationsValues) : null;
                        }
                    }

                    // Array of custom attributes to append to the description
                    $attributesToAppend = [
                        'Materials'     => $materials,
                        'Care Guide'    => $careGuide,
                        'Features'      => $features,
                        'Details'       => $flxpointProductDetail,
                        'Specifications'=> $flxpointProductSpecification,
                    ];

                    // Loop through each attribute and append if the value is not null
                    foreach ($attributesToAppend as $label => $value) {
                        if ($value) {
                            // Convert newlines to <br> for HTML output
                            $formattedValue = nl2br(string: $value);
                            $additionalInfo .= "<p><strong>$label:</strong> $formattedValue</p>";
                        }
                    }

                    // Concatenate the additional info to the main product description if not empty
                    if ($additionalInfo) {
                        $mainProductDescription .= $additionalInfo;
                    }

                    // Append description tag if it exists
                    if ($descriptionTag) {
                        $descriptionTag          = nl2br(string: $descriptionTag);
                        $mainProductDescription .= "<p><strong>$descriptionTag</strong></p>";
                    }

                    $productCategories          = $this->_extractProductCategories(product: $product, productSourceId: $productSourceId);
                    $containsSubscriptionOrGift = $this->_containsSubscriptionOrGift(productCategories: $productCategories, productSourceId: $productSourceId);
                    $productVariantSku          = [];
                    $sellerId                   = $product["sourceId"];
                    $productBrand               = $product["manufacturer"];
                    if (array_key_exists($sellerId, $changeProductBrandSellerIds)) {
                        $productBrand = $changeProductBrandSellerIds[$sellerId];
                    }
                    $configurableOptions        = [];
                    $configurableOptionLabels   = [];
                    $parentProductName          = $product["title"];
                    $parentProductNameLower     = strtolower(string: $parentProductName);
                    foreach ($product['options'] as $option) {
                        $configurableOptions[]  = $option['name'];
                    }
                    $mainProductSku             = $product["sku"];
                    $mainProductSkuInLowerCase  = strtolower(string: $mainProductSku);
                    $containsSubscription       = preg_match(pattern: '/\bsubscription\b/i', subject: $mainProductSkuInLowerCase);
                    $keywords                   = ['subscription', 'gift', 'waders'];
                    $containsKeywords           = preg_match(pattern: '/\b(' . implode(separator: '|', array: $keywords) . ')\b/i', subject: $mainProductSkuInLowerCase);
                    $attributesArray            = isset($product['attributes']) && is_array($product['attributes']) ? $product['attributes'] : [];

                    /**
                     * @include specific products
                     */
                    foreach ($attributesArray as $attribute) {
                        if($sellerId !== 975921 && !in_array($sellerId, $sellerShouldContainEverestTag) ){
                            if (!empty($attribute['value']) && in_array(needle: $attribute['value'], haystack: $importProductsFromSpecificAttributes) && $attribute['value'] !== 'Everest') {
                                $includeOnlySpecificMainProduct[] = $mainProductSku;
                            }

                        }
                        else if($sellerId == 975921){
                            if (!empty($attribute['value']) && in_array( needle: $sellerId, haystack: $importProductsFromSpecificAttributes) && $attribute['value'] === 'Everest') {
                                $includeOnlySpecificMainProduct[] = $mainProductSku;
                            }
                        }
                        else if(in_array($sellerId, $sellerShouldContainEverestTag)){
                            if (!empty($attribute['value']) && in_array( needle: $sellerId, haystack: $sellerShouldContainEverestTag) && $attribute['value'] == 'Everest') {
                                $includeOnlySpecificMainProduct[] = $mainProductSku;
                            }
                        }
                    }
                    $importOnlySpecificMainProduct  = in_array(needle: $sellerId, haystack: $importSpecificMainProductFromTheseSellerIds) && in_array(needle: $mainProductSku, haystack: $includeOnlySpecificMainProduct);
                    if($importOnlySpecificMainProduct){
                        if(!in_array(needle: $mainProductSku, haystack: $excludeTheMainProduct)){
                            if($countProductVariants && $productType && !$containsKeywords && !$containsSubscription && !$containsSubscriptionOrGift && !preg_match(pattern: '/\b(?:\w*test\w*|wholesale|eat ass|XCover Protection Plan|P.E.T.A.|waders|peta|waders|subscription)\b/i', subject: $parentProductNameLower)){
                                foreach ($productVariant as $variant) {
                                    $variantInventorySku = $variant["sku"];
                                    $needToGetUpcFromInventorySku = in_array(needle: $productSourceId, haystack: $sellerToTakeProductUpcFromInventorySku) && (is_numeric(value: $variantInventorySku) && empty($variant["upc"]));
                                    // $sku                = $needToChangeTheUpcFromFloatToString ? $variant["upc"] : (float)$variant["upc"];
                                    $sku                = (!$needToChangeTheUpcFromFloatToString && $needToGetUpcFromInventorySku)
                                                            ? (string) $variantInventorySku
                                                            : ($needToChangeTheUpcFromFloatToString ? (string) $variant["upc"] : (float) $variant["upc"]);
                                    if(in_array($sellerId , $importSpecificVariantsSeller)){
                                        if(!in_array( $variantInventorySku, $specificVariants)){
                                            continue;
                                        }
                                    }
                                    if($sellerId == 993820){
                                        $sku = $variantInventorySku; //Added becasuse does not satisfy condition above as the UPC is present for the product  
                                    }
                                    if (in_array($sellerId, $addPreifxToSellersConvertedUPCtoSKU)) {
                                            $prefix         =  strtolower(substr($productBrand, 0, 3));    
                                            $sku            = $prefix.'-'. $sku;
                                    }   
                                    if (strlen($sku) > 63) {
                                        $oldSku = $sku;
                                        $uniqueSuffix = (string)mt_rand(100000000000, 999999999999);
                                        $sku = substr($sku, 0, 50);
                                        $getSKUFromFile = $this->saveOldSKU($oldSku, $uniqueSuffix);
                                        if($getSKUFromFile){
                                            $sku = $getSKUFromFile;
                                        } else {
                                            $sku = $sku . '-' . $uniqueSuffix;
                                        }
                                    }
                                    $alreadyAddedUpc    = !in_array(needle: $sku, haystack: $upcArr);
                                    $updatedAt          = $variant["updatedAt"] ?? $variant["insertedAt"];
                                    $isLatest           = $updatedAt > $lastUpdatedAt;
                                    $productName        = ($product["title"] !== $variant["title"]) ? $product["title"] .' - '. $variant["title"] : $variant["title"] ;
                                    $variantProductName = strtolower(string: $productName);
                                    if($alreadyAddedUpc && !in_array(needle: $sku, haystack: $excludeProductVariantUpc) && $sku !== 0.0 && $isLatest && !preg_match(pattern: '/\b(?:\w*test\w*|wholesale|eat ass|XCover Protection Plan|P.E.T.A.|waders|peta|subscription)\b/i', subject: $variantProductName)){
                                        $productVariantSku[]= $sku;
                                        $configurableOptionsValue = [];
                                        foreach ($variant['options'] as $option) {
                                            if (!empty($option['name']) && !empty($option['value'])) {
                                                $configurableOptionsValue[] = $option['name'] . "=" . $option['value'];
                                            }
                                        }
                                        $imageRow                   = fgetcsv(stream: $imageCsvFile);
                                        $imageSku                   = (is_array(value: $imageRow) && array_key_exists(key: 0, array: $imageRow)) ? $imageRow[0] : '';
                                        $imageFilePath              = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 1, array: $imageRow))) ? $imageRow[1] : '';
                                        $additionalImageFilePath    = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 2, array: $imageRow))) ? $imageRow[2] : '';
                                        $configurableVariationLabelValues = implode(separator: '~', array: $configurableOptionsValue);
                                        $configurableOptionLabels[] = (string) $sku . ':' . $configurableVariationLabelValues; 
                                        $productName        = trim(string: preg_replace(pattern: '/\s+/', replacement: ' ', subject: $productName)); // Remove extra spaces and replace with a single space, then trim leading and trailing spaces;
                                        $productMapPrice    = $variant["map"];
                                        if ($sellerId === 993457 && in_array($product["title"], $productTitles)) {
                                            $productMapPrice = null;
                                        }                                      
                                        $productMsrpPrice   = $variant["msrp"];
                                        $flxpointSku        = $variant["sku"];
                                        $productSalePrice   = $variant["inventoryListPrice"];
                                        $special_price      = null;
                                        $special_price_from = null;
                                        $special_price_to   = null;
                                        if ($this->isValidPrice(price: $productSalePrice) && $this->isValidPrice(price: $productMsrpPrice)) {
                                            $special_price  = $productSalePrice;
                                            $productPrice   = $productMsrpPrice;
                                        } elseif ($this->isValidPrice(price: $productMapPrice) && $this->isValidPrice(price: $productMsrpPrice)) {
                                            $special_price  = $productMapPrice;
                                            $productPrice   = $productMsrpPrice;
                                        } else {
                                            // Default price selection based on available valid prices
                                            $productPrice   = $productSalePrice ?: ($productMapPrice ?: $productMsrpPrice ?: 0);
                                        }
                                        
                                        if(in_array(needle: $productSourceId, haystack: $specialDiscountPriceForSeller)){
                                            if($specialDiscountPriceForSeller[$productSourceId] == 993916){
                                                // $special_price will be $specialPriceDiscountPercentageForSeller of $productPrice
                                                $special_price      = $productPrice - ($productPrice * $specialPriceDiscountPercentageForSeller[$productSourceId] / 100);
                                                $special_price_from = $specialDiscountPriceForSellerFromDate[$productSourceId] ?? null;
                                                $special_price_to   = $specialDiscountPriceForSellerToDate[$productSourceId] ?? null;
                                            } else {
                                                if($this->isValidPrice(price: $productPrice)){
                                                    // $special_price will be $specialPriceDiscountPercentageForSeller of $productPrice
                                                    $special_price      = $productPrice - ($productPrice * $specialPriceDiscountPercentageForSeller[$productSourceId] / 100);
                                                    $special_price_from = $specialDiscountPriceForSellerFromDate[$productSourceId] ?? null;
                                                    $special_price_to   = $specialDiscountPriceForSellerToDate[$productSourceId] ?? null;
                                                }
                                            }
                                        }
                                        $productQty         = $this->_getProductQuantity(productSourceId: $productSourceId, variantQuantity: $variant["quantity"] ?? 0, sellerIdsWithIncreasedQuantity: $sellerIdsWithIncreasedQuantity);
                                        $productIsInStock   = ($productPrice == 0 || $productQty == 0 || $productQty < 0 || $productPrice == 0.00 || $productPrice == 0) ? 0 : 1;
                                        if ($productQty <= 5) {
                                            $productQty = 0;
                                            $productIsInStock = 0;
                                        }
                                        $productWeight      = $variant["weight"];
                                        $visibility         = ($productType === 'configurable') ? 'Not Visible Individually' : 'Catalog, Search';
                                        $urlKey             = $this->_generateProductUrlKey(productName: $productName, sku: $sku);
                                        $productName        = htmlentities(string: $productName);
                                        $imageUrls          = $this->_getSortedProductImageUrls(
                                            ImageArr: !empty($variant["images"]) ? $variant["images"] : $product["images"]
                                        );
                                        $isPriceZero        = $productPrice == 0 || $productPrice == 0.00;
                                        $isArchived         = $variant["archived"] ?? false;
                                        $isNonConfigurableWithNoImages = ($productType !== 'configurable') && empty($imageUrls);
                                        $productStatus      = ($isPriceZero || $isArchived || $isNonConfigurableWithNoImages) ? 2 : 1;
                                        $additionalImagesUrl= (count(value: $imageUrls) > 1) ? implode(separator: ' | ', array: array_slice(array: $imageUrls, offset: 1)) : ""; // If there are more than 1 URLs, use all except the first one (index 0) or else use the 0th position image URL
                                        $productSalePrice   = ''; //$variant["salePrice"];
                                        $productDescription = $variant["description"] ?? $mainProductDescription ?? '';
                                        $productDescription = preg_replace(pattern: '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', replacement: '', subject: $productDescription);;
                                        $productShortDescription = $productName;
                                        $rowData            = [$sku, $flxpointSku, '', 'More Products', 'simple', $productCategories, 'base', $productName, htmlentities(string: $productDescription), $productShortDescription, $productWeight, $productStatus, '', $visibility, $productPrice, $special_price, $special_price_from, $special_price_to, $urlKey, $productBrand, $productName, $productName, '', '', '', '', '', 'Block after Info Column', $productMapPrice, $productMsrpPrice, '', '', '', '', '', '', '', '', 'Use config', '', $productQty, 0, 1, 0, 0, 1, 1, 1, 10000, 1, $productIsInStock, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, '','','','','','','','','','','','', $imageUrls[0], $productName, $imageUrls[0], $productName, $imageUrls[0], $productName, $additionalImagesUrl, $productName, $sellerId, '', $configurableVariationLabelValues, $imageFilePath, $additionalImageFilePath];
                                        fputcsv(stream: $productCsvFile, fields: $rowData, separator: ",");
                                        $rowData2           = [$sku, $imageUrls[0], $additionalImagesUrl, $sellerId];
                                        fputcsv(stream: $productImageCsvFile, fields: $rowData2, separator: ",");
                                        $upcArr[] = $sku;
                                    }
                                }
                            }
                            if($productType === 'configurable'){
                                $sku                = $product["sku"];
                                $sku                = preg_replace(pattern: '/[^a-zA-Z0-9-]/', replacement: '-', subject: $sku);
                                $sku                = preg_replace(pattern: '/-+/', replacement: '-', subject: $sku); // Replace consecutive hyphens with a single hyphen
                                $sku                = ltrim(string: $sku, characters: '-'); // Remove hyphen from the start
                                $skuForFlxPoint = preg_replace('/-+/', '-', preg_replace('/[^a-zA-Z0-9-]/', '-', $product["sku"]));
                                if (strlen($sku) > 50) {
                                    $sku = substr($sku, 0, 50);
                                }
                                 if (strlen($sku) > 63) {
                                    $oldSku = $sku;
                                    $uniqueSuffix = (string)mt_rand(100000000000, 999999999999);
                                    $sku = substr($sku, 0, 50);
                                    $getSKUFromFile = $this->saveOldSKU($oldSku, $uniqueSuffix);
                                    if($getSKUFromFile){
                                        $sku = $getSKUFromFile;
                                    } else {
                                        $sku = $sku . '-' . $uniqueSuffix;
                                    }
                                }
                                $productName        = $product["title"];
                                $updatedAt          = $product["updatedAt"] ?? $product["insertedAt"];
                                $isLatest           = $updatedAt > $lastUpdatedAt;
                                $productName        = trim(string: preg_replace(pattern: '/\s+/', replacement: ' ', subject: $productName)); // Remove extra spaces and replace with a single space, then trim leading and trailing spaces;
                                $productPrice       = $productMapPrice = $productMsrpPrice = $productQty = 0;
                                $productIsInStock   = $productWeight = 1;
                                $flxpointSku        = $sku;
                                $visibility         = 'Catalog, Search';
                                $urlKey             = $this->_generateProductUrlKey(productName: $productName);
                                $productName        = htmlentities(string: $productName);
                                $imageUrls          = $this->_getSortedProductImageUrls(ImageArr: $product["images"]);
                                $productStatus      = $product["archived"] || empty($imageUrls) ? 2 : 1;
                                $additionalImagesUrl= (count(value: $imageUrls) > 1) ? implode(separator: ' | ', array: array_slice(array: $imageUrls, offset: 1)) : ""; // If there are more than 1 URLs, use all except the first one (index 0) or else use the 0th position image URL
                                $productSalePrice   = '';
                                $productDescription = $mainProductDescription ?? '';
                                $productDescription = preg_replace(pattern: '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', replacement: '', subject: $productDescription);
                                $productShortDescription = $productName;
                                $productVariations  = implode(separator: ' | ', array: array_unique(array: $productVariantSku));
                                $configurableOptions= implode(separator: ' | ', array: array_unique(array: $configurableOptions));
                                $configurableOptionLabels= implode(separator: ' | ', array: array_unique(array: $configurableOptionLabels));
                                $variationCount     = count(value: array_unique(array: $productVariantSku));
                                if($variationCount && $isLatest){
                                    $imageRow                   = fgetcsv(stream: $imageCsvFile);
                                    $imageSku                   = (is_array(value: $imageRow) && array_key_exists(key: 0, array: $imageRow)) ? $imageRow[0] : '';
                                    $imageFilePath              = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 1, array: $imageRow))) ? $imageRow[1] : '';
                                    $additionalImageFilePath    = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 2, array: $imageRow))) ? $imageRow[2] : '';
                                    $rowData                    = [$sku, $flxpointSku, '', 'More Products', $productType, $productCategories, 'base', $productName, htmlentities(string: $productDescription), $productShortDescription, $productWeight, $productStatus, '', $visibility, $productPrice, $productSalePrice, '', '', $urlKey, $productBrand, $productName, $productName, '', '', '', '', '', 'Block after Info Column', $productMapPrice, $productMsrpPrice, '', '', '', '', '', '', '', '', 'Use config', '', $productQty, 0, 1, 0, 0, 1, 1, 1, 10000, 1, $productIsInStock, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, '','','','',$configurableOptions,'','','','','','',$productVariations, $imageUrls[0], $productName, $imageUrls[0], $productName, $imageUrls[0], $productName, $additionalImagesUrl, $productName, $sellerId, '', $configurableOptionLabels, $imageFilePath, $additionalImageFilePath];
                                    fputcsv(stream: $productCsvFile, fields: $rowData, separator: ",");
                                    $rowData2                   = [$sku, $imageUrls[0], $additionalImagesUrl, $sellerId];
                                    fputcsv(stream: $productImageCsvFile, fields: $rowData2, separator: ",");
                                }
                            }
                        }
                    } else if (!in_array(needle: $sellerId, haystack: $importSpecificMainProductFromTheseSellerIds)) {
                        if(!in_array(needle: $mainProductSku, haystack: $excludeTheMainProduct)){
                            if($countProductVariants && $productType && !$containsSubscription && !$containsSubscriptionOrGift && !preg_match(pattern: '/\b(?:\w*test\w*|wholesale|eat ass|XCover Protection Plan|P.E.T.A.|waders|peta|subscription)\b/i', subject: $parentProductNameLower)){
                                foreach ($productVariant as $variant) {
                                    $variantInventorySku = $variant["sku"];
                                    if(in_array($sellerId , $importSpecificVariantsSeller)){
                                        if(!in_array( $variantInventorySku, $specificVariants)){
                                            continue;
                                        }
                                    }
                                    $needToGetUpcFromInventorySku = in_array(needle: $productSourceId, haystack: $sellerToTakeProductUpcFromInventorySku) && (is_numeric(value: $variantInventorySku) && empty($variant["upc"]));
                                    $sku                = (!$needToChangeTheUpcFromFloatToString && $needToGetUpcFromInventorySku)
                                                            ? (string) $variantInventorySku
                                                            : ($needToChangeTheUpcFromFloatToString ? (string) $variant["upc"] : (float) $variant["upc"]);
                                    if (in_array($sellerId, $addPreifxToSellersConvertedUPCtoSKU)) {
                                            $prefix         =  strtolower(substr($productBrand, 0, 3));    
                                            $sku            = $prefix.'-'. $sku;
                                    }   

                                    $updatedAt          = $variant["updatedAt"] ?? $variant["insertedAt"];
                                    $isLatest           = $updatedAt > $lastUpdatedAt;
                                    $productName        = ($product["title"] !== $variant["title"]) ? $product["title"] .' - '. $variant["title"] : $variant["title"] ;
                                    $variantProductName = strtolower(string: $productName);
                                    $alreadyAddedUpc    = !in_array(needle: $sku, haystack: $upcArr);
                                    if($alreadyAddedUpc && !in_array(needle: $sku, haystack: $excludeProductVariantUpc) && $sku !== 0.0 && $isLatest && !preg_match(pattern: '/\b(?:\w*test\w*|wholesale|eat ass|P.E.T.A.|XCover Protection Plan|waders|peta|subscription)\b/i', subject: $variantProductName)){
                                        $productVariantSku[]= $sku;
                                        $configurableOptionsValue = [];
                                        foreach ($variant['options'] as $option) {
                                            if (!empty($option['name']) && !empty($option['value'])) {
                                                $configurableOptionsValue[] = $option['name'] . "=" . $option['value'];
                                            }
                                        }
                                        $imageRow                   = fgetcsv(stream: $imageCsvFile);
                                        $imageSku                   = (is_array(value: $imageRow) && array_key_exists(key: 0, array: $imageRow)) ? $imageRow[0] : '';
                                        $imageFilePath              = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 1, array: $imageRow))) ? $imageRow[1] : '';
                                        $additionalImageFilePath    = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 2, array: $imageRow))) ? $imageRow[2] : '';
                                        $configurableVariationLabelValues = implode(separator: '~', array: $configurableOptionsValue);
                                        $configurableOptionLabels[] = (string) $sku . ':' . $configurableVariationLabelValues; 
                                        $productName        = trim(string: preg_replace(pattern: '/\s+/', replacement: ' ', subject: $productName)); // Remove extra spaces and replace with a single space, then trim leading and trailing spaces;
                                        $productMapPrice    = $variant["map"];
                                        if ($sellerId === 993457 && in_array($product["title"], $productTitles)) {
                                            $productMapPrice = null;
                                        } 
                                        $productMsrpPrice   = $variant["msrp"];
                                        $flxpointSku        = $variant["sku"];
                                        $productSalePrice   = $variant["inventoryListPrice"];
                                        $special_price      = null;
                                        $special_price_from = null;
                                        $special_price_to   = null;
                                        if ($this->isValidPrice(price: $productSalePrice) && $this->isValidPrice(price: $productMsrpPrice)) {
                                            $special_price  = $productSalePrice;
                                            $productPrice   = $productMsrpPrice;
                                        } elseif ($this->isValidPrice(price: $productMapPrice) && $this->isValidPrice(price: $productMsrpPrice)) {
                                            $special_price  = $productMapPrice;
                                            $productPrice   = $productMsrpPrice;
                                        } else {
                                            // Default price selection based on available valid prices
                                            $productPrice   = $productSalePrice ?: ($productMapPrice ?: $productMsrpPrice ?: 0);
                                        }
                                        if(in_array(needle: $productSourceId, haystack: $specialDiscountPriceForSeller) && $this->isValidPrice(price: $productPrice)){
                                            if(in_array(needle: 993916, haystack: $specialDiscountPriceForSeller)){
                                                // $special_price will be $specialPriceDiscountPercentageForSeller of $productPrice
                                                $special_price      = $productPrice - ($productPrice * $specialPriceDiscountPercentageForSeller[$productSourceId] / 100);
                                                $special_price_from = $specialDiscountPriceForSellerFromDate[$productSourceId] ?? null;
                                                $special_price_to   = $specialDiscountPriceForSellerToDate[$productSourceId] ?? null;
                                            } elseif ($productSourceId !== 993916 ){ 
                                                if($special_price == null ){
                                                    // $special_price will be $specialPriceDiscountPercentageForSeller of $productPrice
                                                    $special_price      = $productPrice - ($productPrice * $specialPriceDiscountPercentageForSeller[$productSourceId] / 100);
                                                    $special_price_from = $specialDiscountPriceForSellerFromDate[$productSourceId] ?? null;
                                                    $special_price_to   = $specialDiscountPriceForSellerToDate[$productSourceId] ?? null;
                                                } else {
                                                    // $special_price will be $specialPriceDiscountPercentageForSeller of $productPrice
                                                    $productPrice       = $special_price;
                                                    $special_price      = $special_price - ($special_price * $specialPriceDiscountPercentageForSeller[$productSourceId] / 100);
                                                    $special_price_from = $specialDiscountPriceForSellerFromDate[$productSourceId]?? null;
                                                    $special_price_to   = $specialDiscountPriceForSellerToDate[$productSourceId]?? null;
                                                }
                                            }
                                        }
                                        $productQty         = $this->_getProductQuantity(productSourceId: $productSourceId, variantQuantity: $variant["quantity"] ?? 0, sellerIdsWithIncreasedQuantity: $sellerIdsWithIncreasedQuantity);
                                        $productIsInStock   = ($productPrice == 0 || $productQty == 0 || $productQty < 0 || $productPrice == 0.00 || $productPrice == 0) ? 0 : 1;
                                        if ($productQty <= 5) {
                                            $productQty = 0;
                                            $productIsInStock = 0;
                                        }
                                        $productWeight      = $variant["weight"];
                                        $isPriceZero        = $productPrice == 0 || $productPrice == 0.00;
                                        $isArchived         = $variant["archived"] ?? false;
                                        $visibility         = ($productType === 'configurable') ? 'Not Visible Individually' : 'Catalog, Search';
                                        $urlKey             = $this->_generateProductUrlKey(productName: $productName, sku: $sku);
                                        $productName        = htmlentities(string: $productName);
                                        $imageUrls = $this->_getSortedProductImageUrls(
                                            !empty($variant["images"]) ? $variant["images"] : $product["images"]
                                        );
                                        $isNonConfigurableWithNoImages = ($productType !== 'configurable') && empty($imageUrls);
                                        $productStatus      = ($isPriceZero || $isArchived || $isNonConfigurableWithNoImages) ? 2 : 1;
                                        $additionalImagesUrl= (count(value: $imageUrls) > 1) ? implode(separator: ' | ', array: array_slice(array: $imageUrls, offset: 1)) : ""; // If there are more than 1 URLs, use all except the first one (index 0) or else use the 0th position image URL
                                        $productSalePrice   = ''; //$variant["salePrice"]; 
                                        $productDescription = $variant["description"] ?? $mainProductDescription ?? '';
                                        $productDescription = preg_replace(pattern: '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', replacement: '', subject: $productDescription);;
                                        $productShortDescription = $productName;
                                        $rowData            = [$sku, $flxpointSku, '', 'More Products', 'simple', $productCategories, 'base', $productName, htmlentities(string: $productDescription), $productShortDescription, $productWeight, $productStatus, '', $visibility, $productPrice, $special_price, $special_price_from, $special_price_to, $urlKey, $productBrand, $productName, $productName, '', '', '', '', '', 'Block after Info Column', $productMapPrice, $productMsrpPrice, '', '', '', '', '', '', '', '', 'Use config', '', $productQty, 0, 1, 0, 0, 1, 1, 1, 10000, 1, $productIsInStock, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, '','','','','','','','','','','','', $imageUrls[0], $productName, $imageUrls[0], $productName, $imageUrls[0], $productName, $additionalImagesUrl, $productName, $sellerId, '', $configurableVariationLabelValues, $imageFilePath, $additionalImageFilePath];
                                        fputcsv(stream: $productCsvFile, fields: $rowData, separator: ",");
                                        $rowData2           = [$sku, $imageUrls[0], $additionalImagesUrl, $sellerId];
                                        fputcsv(stream: $productImageCsvFile, fields: $rowData2, separator: ",");
                                        $upcArr[] = $sku;
                                    }
                                }
                            }
                            if($productType === 'configurable'){
                                $sku                = $product["sku"];
                                $sku                = preg_replace(pattern: '/[^a-zA-Z0-9-]/', replacement: '-', subject: $sku);
                                $sku                = preg_replace(pattern: '/-+/', replacement: '-', subject: $sku); // Replace consecutive hyphens with a single hyphen
                                $sku                = ltrim(string: $sku, characters: '-'); // Remove hyphen from the start
                                if (strlen($sku) > 50) {
                                    $sku = substr($sku, 0, 50);
                                }
                                $skuForFlxPoint = preg_replace('/-+/', '-', preg_replace('/[^a-zA-Z0-9-]/', '-', $product["sku"]));
                                $productName        = $product["title"];
                                $updatedAt          = $product["updatedAt"] ?? $product["insertedAt"];
                                $isLatest           = $updatedAt > $lastUpdatedAt;
                                $productName        = trim(string: preg_replace(pattern: '/\s+/', replacement: ' ', subject: $productName)); // Remove extra spaces and replace with a single space, then trim leading and trailing spaces;
                                $productPrice       = $productMapPrice = $productMsrpPrice = $productQty = 0;
                                $productIsInStock   = $productWeight = 1;
                                $flxpointSku        = $skuForFlxPoint;
                                $visibility         = 'Catalog, Search';
                                $urlKey             = $this->_generateProductUrlKey(productName: $productName);
                                $productName        = htmlentities(string: $productName);
                                $imageUrls          = $this->_getSortedProductImageUrls(ImageArr: $product["images"]);
                                $productStatus      = $product["archived"] || empty($imageUrls)  ? 2 : 1;
                                $additionalImagesUrl= (count(value: $imageUrls) > 1) ? implode(separator: ' | ', array: array_slice(array: $imageUrls, offset: 1)) : ""; // If there are more than 1 URLs, use all except the first one (index 0) or else use the 0th position image URL
                                $productSalePrice   = '';
                                $productDescription = $mainProductDescription ?? '';
                                $productDescription = preg_replace(pattern: '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', replacement: '', subject: $productDescription);
                                $productShortDescription = $productName;
                                $productVariations  = implode(separator: ' | ', array: array_unique(array: $productVariantSku));
                                $configurableOptions= implode(separator: ' | ', array: array_unique(array: $configurableOptions));
                                $configurableOptionLabels= implode(separator: ' | ', array: array_unique(array: $configurableOptionLabels));
                                $variationCount     = count(value: array_unique(array: $productVariantSku));
                                if($variationCount && $isLatest){
                                    $imageRow                   = fgetcsv(stream: $imageCsvFile);
                                    $imageSku                   = (is_array(value: $imageRow) && array_key_exists(key: 0, array: $imageRow)) ? $imageRow[0] : '';
                                    $imageFilePath              = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 1, array: $imageRow))) ? $imageRow[1] : '';
                                    $additionalImageFilePath    = ($sku == $imageSku && (is_array(value: $imageRow) && array_key_exists(key: 2, array: $imageRow))) ? $imageRow[2] : '';
                                    $rowData                    = [$sku, $flxpointSku, '', 'More Products', $productType, $productCategories, 'base', $productName, htmlentities(string: $productDescription), $productShortDescription, $productWeight, $productStatus, '', $visibility, $productPrice, $productSalePrice, '', '', $urlKey, $productBrand, $productName, $productName, '', '', '', '', '', 'Block after Info Column', $productMapPrice, $productMsrpPrice, '', '', '', '', '', '', '', '', 'Use config', '', $productQty, 0, 1, 0, 0, 1, 1, 1, 10000, 1, $productIsInStock, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, '','','','',$configurableOptions,'','','','','','',$productVariations, $imageUrls[0], $productName, $imageUrls[0], $productName, $imageUrls[0], $productName, $additionalImagesUrl, $productName, $sellerId, '', $configurableOptionLabels, $imageFilePath, $additionalImageFilePath];
                                    fputcsv(stream: $productCsvFile, fields: $rowData, separator: ",");
                                    $rowData2                   = [$sku, $imageUrls[0], $additionalImagesUrl, $sellerId];
                                    fputcsv(stream: $productImageCsvFile, fields: $rowData2, separator: ",");
                                }
                            }
                        }
                    }
                    $countProductVariants = 0;
                }
                
                fclose(stream: $imageCsvFile);
                fclose(stream: $productCsvFile);
                $returnMsgArr[] = '<info>File Created with product CSV Data Successfully<info>';
            }
            return $returnMsgArr;
        } catch (\Exception $e) {
            var_dump(value: (string) $e . ' vendor list file creating error ' );
            return [];
        }
    }


    private function _generateProductUrlKey($productName, $sku = null): string
    {
        $title  = trim(preg_replace(pattern: '/\s+/', replacement: ' ', subject: $productName)); // Remove extra spaces and replace with a single space, then trim leading and trailing spaces
        // Include SKU in title if it's not null
        $title .= ($sku !== null) ? '-' . $sku : '';
        $urlKey = preg_replace(pattern: '/[^a-zA-Z0-9-]/', replacement: '-', subject: $title); // Replace spaces and special characters with hyphens, then remove any remaining special characters
        $urlKey = preg_replace(pattern: '/-+/', replacement: '-', subject: $urlKey); // Replace consecutive hyphens with a single hyphen
        // $urlKey = $urlKey . '.html'; // add .html at the end of the url
        $urlKey = strtolower($urlKey); // Convert to lowercase
        $urlKey = rtrim(string: $urlKey, characters: '-');
        $urlKey = ltrim(string: $urlKey, characters: '-');
        return $urlKey;
    }

    private function _getSortedProductImageUrls($ImageArr): array
    {
        $productImageArr = $ImageArr;
        // Check if "images" key exists and is an array
        if (isset($productImageArr) && is_array(value: $productImageArr)) {
            // Check if there is more than one image
            if (count($productImageArr) > 1) {
                // Sort the images based on the "sortOrder" value
                usort(array: $productImageArr, callback: function ($a, $b): float|int {
                    return $a["sortOrder"] - $b["sortOrder"];
                });
                // Extract the URLs based on sortOrder after sorting
                $imageUrls = array_column(array: $productImageArr, column_key: "url");
                // Now $imageUrls contains the image URLs sorted by sortOrder
            } else {
                // Only one image, store its URL in the same variable
                $imageUrls = (!empty($productImageArr[0]) && isset($productImageArr[0]["url"]) && !empty($productImageArr[0]["url"])) ? [$productImageArr[0]["url"]] : [''];
            }

            // Filter out GIF URLs
            $imageUrls = array_filter(array: $imageUrls, callback: function ($url): bool {
                $path = parse_url(url: $url, component: PHP_URL_PATH);
                return pathinfo(path: $path, flags: PATHINFO_EXTENSION) !== 'gif';
            });

            return $imageUrls;
        }
        return [];
    }

    private function _extractProductCategories($product, $productSourceId): string
    {
        $categoryResults = [];

        foreach ($product as $key => $value) {
            if (strpos(haystack: $key, needle: 'category') !== false && is_array(value: $value) && isset($value['id']) && isset($value['name'])) {
                $categoryResults[] = [
                    'id'   => $value['id'],
                    'name' => $value['name'],
                ];
            }
        }
        $sellerId = [993458]; // seller id: DSG OuterWear
        
        // If no category results were found, add default categories 'Apparel' and 'Women' for DSG OuterWear
        if (empty($categoryResults) && in_array(needle: $productSourceId, haystack: $sellerId)) {
            $categoryResults[] = ['id' => null, 'name' => 'Apparel'];
            $categoryResults[] = ['id' => null, 'name' => 'Women'];
        }

        // Extract 'name' values from the associative array
        $categoryNames = array_column(array: $categoryResults, column_key: 'name');

        // Implode the names with '|'
        $productCategories = implode(separator: ' | ', array: array_unique(array: $categoryNames));
        return $productCategories;
    }

    private function _containsSubscriptionOrGift($productCategories, $productSourceId): bool
    {
        $allowedSourceIds = [992255, 992730]; //source id: Monsterbass, fav fishing
        
        // Check if productSourceId is in the allowed source IDs array
        if (in_array(needle: $productSourceId, haystack: $allowedSourceIds)) {
            // Regular expression pattern to check for 'subscription' or 'gift'
            $pattern = '/\b(subscription|gift)\b/i';
            
            // Check if the pattern matches the productCategories string
            return preg_match(pattern: $pattern, subject: $productCategories) === 1;
        } else {
            return false;
        }
    }
    private function isValidPrice($price) {
        return !empty($price) && !is_null($price) && $price !== 0 && $price !== 0.00;
    }
    /**
     * Get the product quantity based on the product source ID and seller IDs.
     *
     * @param int $productSourceId The product source ID to check.
     * @param int $variantQuantity The quantity from the variant.
     * @param array $sellerIdsWithIncreasedQuantity Array of seller IDs that should trigger an increased quantity.
     * @param int $increaseAmount The amount to increase the quantity by (default to 9999999).
     * @return int The calculated product quantity.
     */
    private function _getProductQuantity(int $productSourceId, int $variantQuantity, array $sellerIdsWithIncreasedQuantity, int $increaseAmount = 9999999): int
    {
        // Set default quantity if not provided
        $productQty = $variantQuantity ?? 0;

        // Increase the quantity if the product source ID is in the array
        if (in_array(needle: $productSourceId, haystack: $sellerIdsWithIncreasedQuantity)) {
            $productQty = $increaseAmount;
        }

        return $productQty;
    }

    // Recursive function to extract values
    private function extractValues(array $node, &$result = [])
    {
        // Check if node is valid
        if (!is_array($node) || empty($node)) {
            return $result;
        }

        // Check if 'value' exists and is not null
        if (isset($node['value']) && $node['value'] !== null) {
            $result[] = $node['value'];
        }

        // Process children if they exist
        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->extractValues($child, $result);
            }
        }

        return $result;

    }
    function saveOldSKU($oldSku, $newSku){
        $filePath = BP . '/var/sku_mapping.json';
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        $data = [];
        if (file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $data = json_decode($fileContent, true) ?: [];
        }
        if (isset($data[$oldSku])) {
            return $data[$oldSku];
        }
        $oldSkuTrimmed = substr($oldSku, 0, 50);

        $data[$oldSku] = $oldSkuTrimmed."-".$newSku;
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

        return null;
    }

}