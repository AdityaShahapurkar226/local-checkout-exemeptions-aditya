/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.sellerCreateConfigurable', {
        _create: function () {
            var self = this;
            var attributeOptionsData = this.options.attributeOptions;
            var defaultValue = this.options.defaultValue;
            var backUrl = this.options.backUrl;
            var storeOptions = this.options.storeOptions;
            var fcop=0;
            if(attributeOptionsData.totalRecords >0) {
                $(".admin__data-grid-outer-wrap").css('display','none');
                var headone=$('<div></div>').addClass("wk-mp-option-box")
                .append(
                    $('<ul></ul>').addClass("wk-mp-headcus ul_first")
                    .append($('<li></li>').text($t('Admin')))
                    .append($('<li></li>').text($t('Default Store View')))
                    .append($('<li></li>').text($t('Is Default')))
                    .append(
                        $('<li></li>').append(
                            $('<button></button>').attr({type:'button', value:$t('Add Option'),title:$t('Add Option'),class:"attroptions button"}).append(
                                "<span><span>"+$t('Add Option')+"</span></span>"
                            )
                        )
                    )
                );
                $('#cust').append(headone);
                $(".attroptions").trigger("click");
                $.each( attributeOptionsData.items, function( key, data ) {
                    var selectedStatus = false;var storeValue="";
                    if(defaultValue == data.option_id ) {
                        selectedStatus = true;
                    }
                    if($.inArray(data.option_id, Object.keys(storeOptions)) !== -1) {
                          storeValue = storeOptions[data.option_id];
                    }
                    var addcust = $('<ul></ul>').addClass('wk-mp-headcus')
                                .append($('<input>').attr({type:'hidden',class:"",name:'attroptions['+fcop+'][option_id]',value:data.option_id}))
                                .append($('<li></li>')
                                        .append($('<input>').attr({type:'text',class:"required-entry widthinput",name:'attroptions['+fcop+'][admin]',value:data.value})))
                                .append($('<li></li>')
                                        .append($('<input>').attr({type:'text',class:"widthinput",name:'attroptions['+fcop+'][store]',value:storeValue})))
                                .append($('<li></li>')
                                        .append($('<input>').attr({type:'radio',class:"widthinput",name:'default[]',checked:selectedStatus,value:data.option_id})))
                                .append($('<li></li>')
                                        .append($('<button></button>').attr({type:'button', value:" Delete Row",title:$t('Delete Row'),class:"deletecusopt button"}).append("<span><span>"+$t('Delete')+"</span></span>")));
                $('.wk-mp-option-box').append(addcust);
                fcop++;
                })
            }
            $("button#add_new_defined_option").click(function () {
                $('#cust').show();
            });
            $("button#save").click(function () {
                if ($('#apply_to').is(":visible")) {
                    $('#protype').attr('disabled', 'disabled');
                }
            });
            var attr_options=0,select=0;
            $("#frontend_input").click(function () {
                if (attr_options !== 0 && select !== 1) {
                    attr_options=$(".wk-mp-option-box").clone();
                }
            });
            
            $("#associate-product").delegate('.wk-mp-headcus input','focusout',function () {
                    $(this).attr('value',$(this).val());
            });
            
            $("#associate-product").delegate('.wk-mp-headcus input[type="checkbox"]','focusout',function () {
                if ($(this).is(":checked")) {
                    $(this).attr('checked','checked');
                } else {
                $(this).removeAttr("checked");
                }
            });
            $("#frontend_input").change(function () {
                $('.val_required').show();
                $(".wk-mp-option-box").remove();
                if ($("#frontend_input").val() == "multiselect" || $("#frontend_input").val() == "select") {
                    if (attr_options===0) {
                        var headone=$('<div></div>').addClass("wk-mp-option-box")
                        .append(
                            $('<ul></ul>').addClass("wk-mp-headcus ul_first")
                            .append($('<li></li>').text($t('Admin')))
                            .append($('<li></li>').text($t('Default Store View')))
                            .append($('<li></li>').text($t('Is Default')))
                            .append(
                                $('<li></li>').append(
                                    $('<button></button>').attr({type:'button', value:$t('Add Option'),title:$t('Add Option'),class:"attroptions button"}).append(
                                        "<span><span>"+$t('Add Option')+"</span></span>"
                                    )
                                )
                            )
                        );
                        $('#cust').append(headone);
                        $(".attroptions").trigger("click");
                        attr_options++;
                    } else {
                        $('#cust').append($('<div></div>').addClass("wk-mp-option-box").append(attr_options.html()));
                    }
                } else {
                    select=1;
                }
            });

            $("#associate-product").delegate(".deletecusopt","click",function () {
                $(this).parents(".wk-mp-headcus").remove();
            });

            $("#associate-product").delegate(".attroptions","click",function () {
                var addcust = $('<ul></ul>').addClass('wk-mp-headcus')
                                .append($('<li></li>')
                                        .append($('<input>').attr({type:'text',class:"required-entry widthinput",name:'attroptions['+fcop+'][admin]'})))
                                .append($('<li></li>')
                                        .append($('<input>').attr({type:'text',class:"widthinput",name:'attroptions['+fcop+'][store]'})))
                                .append($('<li></li>')
                                        .append($('<input>').attr({type:'radio',class:"widthinput",name:'default[]',value:fcop})))
                                .append($('<li></li>')
                                        .append($('<button></button>').attr({type:'button', value:" Delete Row",title:$t('Delete Row'),class:"deletecusopt button"}).append("<span><span>"+$t('Delete')+"</span></span>")));
                $('.wk-mp-option-box').append(addcust);
                fcop++;
            });
            $(".wk-cnfig-back").on('click',function(event) {
                event.preventDefault(); 
               var url = backUrl;
               location.replace(url);
            });
            $(document).on('change','.widthinput',function () {
                var validt = $(this).val();
                var regex = /(<([^>]+)>)/ig;
                var mainvald = validt .replace(regex, "");
                $(this).val(mainvald);
            });
        }
    });
    return $.mage.sellerCreateConfigurable;
});
