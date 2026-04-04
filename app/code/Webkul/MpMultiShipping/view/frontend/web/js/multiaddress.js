/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require(['jquery',],function ($) {
    $(function(){
        let shippingRadios = document.querySelectorAll("input.radio");
        $(shippingRadios).on("click",function(){
            if ($(this).hasClass("multiaddress-method")) {
                $('input[name="shipping_method['+$(this).data('addressid')+']"]').prop('checked', false);
            }
        });

        let submitButton = document.querySelector("button.action.primary.continue");
        let multiAddressMethods = document.querySelectorAll("input.multiaddress-method");
        $(submitButton).on("click",function(){
            let shipFlag = [];
            let shipCost = [];
            let shippingMode = window.shippingMode;
            $(multiAddressMethods).each(function(){
                let addressId = $(this).data('addressid');
                let sellerId = $(this).data('sellerid');
                if (shippingMode  == 2) {
                    var itemId = $(this).data('itemid');
                }
                let radioElement;
                if (shippingMode  == 1) {
                    radioElement = $("input[name='multi_address_shipping["+addressId+"]["+sellerId+"]']:checked");
                } else if (shippingMode  == 2) {
                    radioElement = $("input[name='multi_address_shipping["+addressId+"]["+itemId+"]']:checked");
                }
                
                
                if(radioElement.val() == undefined) {
                    shipFlag[addressId] = false;
                } else {
                    if(!shipFlag.hasOwnProperty(addressId)) {
                        shipFlag[addressId] = true;
                    }
                    if (shippingMode  == 1) {
                        shipCost.push({
                            customAddressId : radioElement.data('customaddressid'),
                            addressId : addressId,
                            sellerId : sellerId,
                            cost : radioElement.data('cost')
                        });
                    } else if (shippingMode  == 2) {
                        shipCost.push({
                            customAddressId : radioElement.data('customaddressid'),
                            addressId : addressId,
                            sellerId : sellerId,
                            itemId : itemId,
                            cost : radioElement.data('cost')
                        });
                    }
                }
            });
            for (let addressId in shipFlag) {
                if (shipFlag[addressId] == true) {
                    $("input[name='shipping_method["+addressId+"]'][value='mpmultishipping_mpmultishipping']").prop('checked', true);
                }
            }
            $("input[name='multi_address_cost']").val(JSON.stringify(shipCost));
            // return false;
        });
    });
});