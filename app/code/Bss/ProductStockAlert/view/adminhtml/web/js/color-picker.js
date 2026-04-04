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
    'jquery',
    'JsColor',
    '!domReady'
], function ($) {
    'use strict';

    $.widget('mage.customColorPicker', {
        options: {
            inputClass: '.bss-colpicker'
        },
        _create: function () {
            this._bind();
            return this;
        },
        _bind: function () {
            $(this.options.inputClass).colpick({
                /**
                 * @param {String} hsb
                 * @param {String} hex
                 * @param {String} rgb
                 * @param {String} el
                 */
                onChange: function (hsb, hex, rgb, el) {
                    $(el).val('#' + hex);
                }
            });
        }
    });

    return $.mage.customColorPicker;
});
