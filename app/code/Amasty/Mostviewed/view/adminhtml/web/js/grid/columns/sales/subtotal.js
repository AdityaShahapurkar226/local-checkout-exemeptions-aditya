define([
    'Magento_Ui/js/grid/columns/column'
], function (column) {
    'use strict';

    return column.extend({
        getLabel: function (record) {
            return parseFloat(record[this.index].replace( /^\D+/g, '')) ? record[this.index] : '-';
        }
    });
});
