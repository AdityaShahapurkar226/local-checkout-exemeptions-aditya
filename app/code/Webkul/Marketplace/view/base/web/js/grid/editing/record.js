define(
    [
        'Magento_Ui/js/grid/editing/record'
    ], function(Component){
        'use strict';
        return Component.extend({
            defaults:{
                templates: {
                    fields: {
                        price: {
                            component: 'Webkul_Marketplace/js/form/element/price',
                            template: 'ui/form/element/input'
                        }
                    }
                }
            }
        })
    }
)