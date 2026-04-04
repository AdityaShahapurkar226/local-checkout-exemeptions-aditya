/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require([
    'jquery',
    'Magento_Ui/js/modal/modal'
    ], function ($, modal) {
        $(document).ready(function () {
            var productWiseShippingArray = window.productWiseShippingArray;
            var currencyCode = window.currencySymbol;
            var itemQuantityArray = window.itemQuantityArray;
            $('.customs-value-currency').html(currencyCode);
            for (i = 0; i < window.productWiseShippingArray.length; i++) {
                if (productWiseShippingArray[i]) {
                    var ele = $('#my-orders-table tbody')[i];
                    var elementId = $(ele).find("tr").attr('id');
                    rowId = elementId.substr(elementId.indexOf("w-") + 2, elementId.length);
                    $(ele).append('<p><input type="checkbox" class="generatePackage" id="'+rowId+'" value="'+rowId+'"> Create Shipping Label with '+itemQuantityArray[rowId]['item_shipping_method']+'</p>');
                    $('.wk-mp-list_table').hide();
                }
            }
        });
        
        $(".generatePackage").click(function () {
            if ($(this).prop("checked")) {
                var checkboxElement = this;
                var itemId = $(this).val();
                var itemQuantityArray = window.itemQuantityArray;
                var itemQty = itemQuantityArray[itemId]['qty'];
                $('#item_id').val(itemQty);
                $('#item_id').attr("name", "shipment[items]["+itemId+"]");
                $.ajax({
                    url: window.getMultiShipDataLink,
                    method: "post",
                    data: {itemId: itemId},
                    success: function (result) {
                        if (typeof(result)=="object") {
                            var fieldName = "packages[1][params][content_type]";
                            var html = "<select name="+fieldName+" class='admin__control-select multishipping-package-select'>";
                            $.each(result, function (i, j) {
                                html+="<option value="+i+">"+j+"</option>";
                            });
                            html+="</select>";
                            $('.package-extra-content').show();
                            $('.container-types').html(html);
                            /**
                             * add products to package
                             */
                            $('#package_item_qty').attr("name", "packages[1][items]["+itemId+"][qty]").val(itemQuantityArray[itemId]["qty"]);
                            $('#package_item_customs_value').attr("name", "packages[1][items]["+itemId+"][customs_value]").val(itemQuantityArray[itemId]["customs_value"]);
                            $('#package_item_price').attr("name", "packages[1][items]["+itemId+"][price]").val(itemQuantityArray[itemId]["price"]);
                            $('#package_item_name').attr("name", "packages[1][items]["+itemId+"][name]").val(itemQuantityArray[itemId]["name"]);
                            $('#package_item_weight').attr("name", "packages[1][items]["+itemId+"][weight]").val(itemQuantityArray[itemId]["weight"]);
                            $('#package_item_product_id').attr("name", "packages[1][items]["+itemId+"][product_id]").val(itemQuantityArray[itemId]["product_id"]);
                            $('#package_item_order_item_id').attr("name", "packages[1][items]["+itemId+"][order_item_id]").val(itemQuantityArray[itemId]["order_item_id"]);
                        }
                    }
                });

                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: [{
                        text: $.mage.__('Submit'),
                        class: '',
                        click: function () {
                            $('body').trigger('processStart');
                            this.closeModal();
                            $("#item-wise-packaging-form").submit();
                            $("#"+itemId).prop('checked', false);
                            setTimeout(function () {
                                location.reload()
                            }, 3000);
                        }
                    }]
                };
                var popup = modal(options, $('#popup-modal'));
                $("#popup-modal").modal("openModal");
            }
        });

        $("body").on('change','.multishipping-package-select',function () {
            if ($(this).val() == 'OTHER') {
                $('.multiship-reason').prop('disabled', false);
            } else {
                $('.multiship-reason').prop('disabled', true);
            }
        });
    });
