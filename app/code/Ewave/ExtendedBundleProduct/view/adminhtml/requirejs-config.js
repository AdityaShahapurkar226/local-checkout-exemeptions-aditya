/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'bundleConfigurable': 'Ewave_ExtendedBundleProduct/js/type/configurable'
        }
    },
    config: {
        mixins: {
            'Magento_Bundle/js/components/bundle-dynamic-rows-grid': {
                'Ewave_ExtendedBundleProduct/js/components/bundle-dynamic-rows-extend': true
            }
        }
    }
};
