<?php

namespace AgeChecker\AgeChecker\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Model\Session;

class Loader extends \Magento\Framework\View\Element\Template {
	private $scopeConfig;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\View\Element\Template\Context $context
	) {
		parent::__construct($context);

		$this->scopeConfig     = $scopeConfig;
	}

	public function generateScript() {
		$enableOnAllPages = $this->getConfigValue('general/enable_on_all_pages');

		// Check if user is on a checkout page.
		$url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
		if (strpos($url, "checkout") === false && strpos($url, "onepage") === false && !$enableOnAllPages) {
			return;
		}

		$key                        = $this->getConfigValue('general/api_key');
		$element                    = $this->getConfigValue('general/element');
		$store_name                 = $this->getConfigValue('general/name');
		$enabled                    = $this->getConfigValue('general/enabled');
		$categories                 = $this->getConfigValue('general/categories');
		$config                     = $this->getConfigValue('general/client_config');
		$category_specific_api_keys = $this->getConfigValue('general/category_specific_api_keys');
		$before_load_script			= $this->getConfigValue('general/before_load_script');

		if (!$enabled) {
			return;
		}

		$objectManager = ObjectManager::getInstance();
		$checkoutSession = $objectManager->get(Session::class);

		if (!isset($config)) {
			$config = "";
		}

		$elements = "";
		// Separate elements by commas and make a JS string array.
		if($element) {
			$elements = explode(",", $element);
			for ($i = 0; $i < count($elements); ++ $i) {
				$elements[$i] = "\"" . trim($elements[$i]) . "\"";
			}
			$elements = implode(",", $elements);
		}

		$categoriesArr = [];

		if ($categories) {
			$categoriesArr = explode(",", $categories);
			for ($i = 0; $i < count($categoriesArr); ++$i) {
				// Trim IDs and cast to integer
				$categoriesArr[$i] = (int) trim($categoriesArr[$i]);
			}
		}

		$enabled = empty($categoriesArr) ? true : false; // Will be set to true if an item in cart is not in the category exclusion list

		if (!empty($categoriesArr)) {
			$quote = $checkoutSession->getQuote();
			$cartItems = $quote->getAllVisibleItems();
			
			foreach ($cartItems as $item) {
				$product = $item->getProduct();
				$categoryIds = $product->getCategoryIds();
				$isCategoryInArr = false; 

				// Check if atleast one category of this product is in $categoriesArr
				foreach ($categoryIds as $categoryId) {
					$inArrayResult = in_array($categoryId, $categoriesArr);

					if ($inArrayResult) {
						$isCategoryInArr = true;
                		break;
					}
				}

				if (!$isCategoryInArr) {
					$enabled = true;
					break;
				}
			}
		}

		// Category specific API key check
		if (!empty($category_specific_api_keys)) {
			$decoded_category_specific_api_keys = json_decode($category_specific_api_keys, true);

			if (gettype($decoded_category_specific_api_keys) === "array") { // Ensures setting value is an array
				$quote = $checkoutSession->getQuote();
				$cartItems = $quote->getAllVisibleItems();

				$keys = array_keys($decoded_category_specific_api_keys);
				$values = array_values($decoded_category_specific_api_keys);
				$priority = count($values); // Will use default $key value if $priority does not end up lower than count
				
				foreach ($cartItems as $item) {
					$product = $item->getProduct();
					$categoryIds = $product->getCategoryIds();
					
					// Products can have multiple category IDs
					foreach ($categoryIds as $categoryId) {
						// Search for this category ID in the "category specific API key" array
						$index = array_search($categoryId, $keys);
	
						if ($index !== false) {
							$enabled = true; // Make sure we prompt age verification if an item in a category specific API key is found
							// If the index of the found category is higher than the currently set priority
							// Update priority to the index of this category in the "category specific api keys" array
							if ($index < $priority) $priority = $index;
						}
					}
				}

				// If $priority ends up lower than the length of the array use the new API key
				if ($priority < count($values)) $key = $values[$priority];
			}
		}

		if ($enabled) {
			return '(function(w,d){'. $before_load_script .'var config={key:"' . $key . '",element:[' . $elements . '],name:"' . $store_name . '",platform_features:{magento2:{quote_module:true}},' . $config . '};w.AgeCheckerConfig=config;if(config.path&&(w.location.pathname+w.location.search).indexOf(config.path)) return;var h=d.getElementsByTagName("head")[0];var a=d.createElement("script");a.src="https://cdn.agechecker.net/static/popup/v1/popup.js";a.crossOrigin="anonymous";a.onerror=function(a){w.location.href="https://agechecker.net/loaderror";};h.insertBefore(a,h.firstChild);})(window, document);';
		}
	}

	private function getConfigValue($path) {
		return $this->scopeConfig->getValue('agechecker/' . $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
}