/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'bundleConfigurable': 'Ewave_ExtendedBundleProduct/js/type/configurable'
        }
    },
    config: {
        mixins: {
            'Magento_Bundle/js/price-bundle': {
                'Ewave_ExtendedBundleProduct/js/extends/price-bundle': true
            },
            'Magento_Catalog/js/validate-product': {
                'Ewave_ExtendedBundleProduct/js/extends/validate-product': true
            },
            'Magento_Wishlist/js/add-to-wishlist': {
                'Ewave_ExtendedBundleProduct/js/extends/add-to-wishlist': true
            }
        }
    }
};
