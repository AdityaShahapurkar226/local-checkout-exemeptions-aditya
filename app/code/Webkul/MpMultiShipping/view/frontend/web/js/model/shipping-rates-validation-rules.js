/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*global define*/
define(
    [],
    function () {
        'use strict';
        return {
            getRules: function () {
                return {
                    'country_id': {
                        'required': true
                    },
                    'postcode': {
                        'required': false
                    }
                };
            }
        };
    }
);
