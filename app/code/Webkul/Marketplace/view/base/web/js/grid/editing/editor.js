define(
    [
        'Magento_Ui/js/grid/editing/editor'
    ], function(Component){
        'use strict';
        return Component.extend({
            defaults:{
                templates: {
                    record: {  
                        component: 'Webkul_Marketplace/js/grid/editing/record', 
                    }
                }
            }
        })
    }
)