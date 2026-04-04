var config = {
    map: {
        '*': {
            amRelatedAnalytics: 'Amasty_Mostviewed/js/mostviewed_analytics'
        }
    },
    config: {
        mixins: {
            'Amasty_Conf/js/swatch-renderer': {
                'Amasty_Mostviewed/js/swatch-renderer': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Amasty_Mostviewed/js/swatch-renderer': true
            }
        }
    },

    shim: {
        'Magento_SalesRule/js/view/payment/discount': {
            deps: ['Amasty_Mostviewed/js/coupon/action/add-message']
        }
    }
};
