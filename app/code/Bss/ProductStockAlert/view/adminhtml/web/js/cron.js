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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define ([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function ($, confirmation) {
    'use strict';

    $('#addbutton_button').on('click', function (event) {
        event.preventDefault;
        let URL = $('#URL').text();
        confirmation({
            title: $.mage.__('Run Cron'),
            content: $.mage.__('Run cron will automatically send emails for subscribed customers of all in stock products. Do you want to continue?'),
            actions: {
                confirm: function () {
                    $(location).prop('href', URL);
                },
                cancel: function () {
                    return false;
                },
                always: function (){}
            }
        });
    });
});
