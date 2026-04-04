/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

var config = {
    map: {
        '*': {
            productStockalert: 'Bss_ProductStockAlert/js/product-stockalert',
            bssStockAlertEventHandle: 'Bss_ProductStockAlert/js/event-handle',
            bssProductStockAlertProcessor: 'Bss_ProductStockAlert/js/components/stock-notice',
            bss_configurable_control: 'Bss_ProductStockAlert/js/configurable_control'
        }
    },
    config: {
        mixins: {
            "Magento_Swatches/js/swatch-renderer": {
                "Bss_ProductStockAlert/js/swatch-renderer": true
            },
            "Magento_ConfigurableProduct/js/configurable": {
                "Bss_ProductStockAlert/js/configurable": true
            },
            "Bss_ConfiguableGridView/js/swatch": {
                "Bss_ProductStockAlert/js/swatch/swatch-renderer": true
            },
            "Magento_Bundle/js/slide": {
                "Bss_ProductStockAlert/js/bundle-slide-mixin": true
            },
            "Magento_Bundle/js/price-bundle": {
                "Bss_ProductStockAlert/js/price-bundle-mixin": true
            },
            "Magento_Theme/js/view/messages": {
                "Bss_ProductStockAlert/js/view/messages-mixin": true
            }
        }
    }
};
