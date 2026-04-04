
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var config = {
    "map": {
        "*": {
            wkmultiaddress: 'Webkul_MpMultiShipping/js/multiaddress',
            'Magento_Checkout/js/view/cart/shipping-rates': 'Webkul_MpMultiShipping/js/view/cart/shipping-rates'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Webkul_MpMultiShipping/js/model/shipping-save-processor/payload-extender-mixin': true
            }
        }
    }
};
