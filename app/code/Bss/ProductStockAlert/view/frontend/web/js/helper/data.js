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
define([
    'underscore'
], function (_) {
    'use strict';

    return {
        /**
         * Merge two object
         * @param $obj1
         * @param $obj2
         * @returns {*}
         */
        mergeObject: function ($obj1, $obj2) {
            // ES6 can use Object.assign(obj1, obj2);
            // We do manually merge for all compatible version
            _.forEach($obj2, function ($val, $key) {
                $obj1[$key] = $val;
            });
            return _.clone($obj1);
        }
    };
});
