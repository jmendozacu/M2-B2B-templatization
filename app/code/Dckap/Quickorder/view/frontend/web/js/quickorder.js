require(['jquery', 'Magento_Ui/js/modal/modal'],
        function ($, modal) {

            function hideMessage()
            {
                setTimeout(function () {
                    if ($('#quickorder_message_wrap').css('display') == 'block')
                    {
                        jQuery('#quickorder_message_wrap').hide();
                    }
                }, 3000);
            }
            jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();

            require(['ui_autocomplete_html'], function ($) {
                enableAutocomplete(jQuery('#quickorder-form'));
            });

            jQuery('#quickorder-form').on('click', 'button.btn-addcart', function () {
                jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();
                var parentEl = jQuery(this).parents('tr');
                var productId = jQuery.trim(parentEl.find('input[name="pid[]"]').val());
                if (jQuery.trim(productId) == '' || jQuery.trim(productId) <= 0) {
                    jQuery('#quickorder_error').text("Please select any product to add to cart");
                    jQuery('#quickorder_error_wrap').show();
                    return false;
                }
                if (parentEl.hasClass('configurable')) {
                    var confAttrData = parentEl.find('input[name="confAttrData[]"]').map(function () {
                        return $(this).val();
                    }).get();
                    var confAttrQty = parentEl.find('input[name="confAttrQty[]"]').map(function () {
                        return $(this).val();
                    }).get();
                    var params = {};
                    params['configurable'] = new Array();
                    jQuery('#quickorder_loader').show(function () {
                        //console.log('====confAttrData.length====>'+ confAttrData.length);
                        if (confAttrData.length == 0) {
                            jQuery('#quickorder_loader').hide();
                            jQuery('#quickorder_error').text('Select any product using the configurable attributes');
                            jQuery('#quickorder_error_wrap').show();
                        }
                        for (var k = 0; k < confAttrData.length; k++) {
                            params['configurable'][0] = {'product': productId, qty: confAttrQty[k], superAttribute: confAttrData[k]};
                            jQuery.ajax({
                                method: 'post',
                                async: false,
                                url: jQuery('#sbu').val() + "quickorder/index/addproduct",
                                data: params,
                                success: function (response) {
                                    if (k == confAttrData.length * 1 - 1) {
                                        jQuery('#quickorder_loader').hide();
                                        var result = jQuery.parseJSON(response);
                                        if (jQuery.trim(result.status) == 'success') {
                                            jQuery('#quickorder_message').text(result.message);
                                            jQuery('#quickorder_message_wrap').show();
                                            parentEl.find('.clear-row').click();
                                        } else if (jQuery.trim(result.status) == 'failure') {
                                            jQuery('#quickorder_error').text(result.message);
                                            jQuery('#quickorder_error_wrap').show();
                                        }
                                    }
                                }
                            });
                        }
                    });
                } else if (parentEl.hasClass('simple')) {
                    var productQty = jQuery.trim(jQuery(this).parents('tr').find('input[name="qty[]"]').val());
                    if (productId > 0) {
                        var jparams = {};
                        jparams['simple'] = {'pids': productId, 'pqty': productQty};
                        jQuery('#quickorder_loader').show();
                        jQuery.post(jQuery('#sbu').val() + "quickorder/index/addproduct", jparams, function (response) {
                            jQuery('#quickorder_loader').hide();
                            var result = jQuery.parseJSON(response);
                            if (jQuery.trim(result.status) == 'success') {
                                jQuery('#quickorder_message').text(result.message);
                                jQuery('#quickorder_message_wrap').show();
                                parentEl.find('.clear-row').click();
                            } else if (jQuery.trim(result.status) == 'failure') {
                                jQuery('#quickorder_error').text(result.message);
                                jQuery('#quickorder_error_wrap').show();
                            }
                        });
                    }
                } else {
                    jQuery('#quickorder_error').text('Select any product');
                    jQuery('#quickorder_error_wrap').show();
                }
                return false;
            });

            jQuery('#quickorder-form').on('click', 'button.clear-row', function () {
                var parentEl = jQuery(this).parents('tr');
                parentEl.removeClass('simple').removeClass('configurable');
                parentEl.find('input').val('');
                parentEl.find('.pqty,.txt-sku').text(''); //.txt-sku,.pname,
                parentEl.find('.txt-qty').show();
                parentEl.find('.confBlock').remove();
                return false;
            });

            jQuery('#quickorder-form').on('click', 'img.deleteConfProduct', function () {
                jQuery(this).parents('span').remove();
                return false;
            });


            jQuery('button.add-all-product').click(function () {
                jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();

                var productSelected = 0;
                jQuery('#quick-order tbody').find('input[name="pid[]"]').each(function () {
                    if (jQuery(this).val() > 0)
                        productSelected = 1;
                });
                if (!productSelected) {
                    jQuery('#quickorder_error').text("Please select any product to add to cart");
                    jQuery('#quickorder_error_wrap').show();
                    return false;
                }

                jQuery('#quickorder_loader').show();
                var pidsArray = new Array();
                var pqtyArray = new Array();
                var addedToCart = true;
             //   setTimeout(function () {
                    jQuery('#quick-order tbody tr').each(function () {
                        var parentTr = jQuery(this);
                        if (parentTr.hasClass('configurable')) {
                            var productId = jQuery.trim(jQuery(this).find('input[name="pid[]"]').val());
                            if (productId > 0) {
                                var confAttrData = jQuery(this).find('input[name="confAttrData[]"]').map(function () {
                                    return $(this).val();
                                }).get();
                                var confAttrQty = jQuery(this).find('input[name="confAttrQty[]"]').map(function () {
                                    return $(this).val();
                                }).get();
                                var params = {};
                                params['configurable'] = new Array();
                                //alert('=all=confAttrData.length======>'+ confAttrData.length);
                                if (confAttrData.length == 0) {
                                    addedToCart = false;
                                    jQuery('#quickorder_loader').hide();
                                    jQuery('#quickorder_error').text('Select any product using the configurable attributes');
                                    jQuery('#quickorder_error_wrap').show();
                                    return false;
                                }
                                for (var k = 0; k < confAttrData.length; k++) {
                                    params['configurable'][0] = {'product': productId, qty: confAttrQty[k], superAttribute: confAttrData[k]};
                                    jQuery.ajax({
                                        method: 'POST',
                                        async: false,
                                        url: jQuery('#sbu').val() + "quickorder/index/addproduct",
                                        data: params,
                                        success: function (response) {
                                            var result = jQuery.parseJSON(response);
                                            if (jQuery.trim(result.status) == 'success') {
                                                parentTr.find('button.clear-row').click();
                                            } else if (jQuery.trim(result.status) == 'failure') {
                                                addedToCart = false;
                                                jQuery('#quickorder_error').text(result.message);
                                                jQuery('#quickorder_error_wrap').show();
                                            }
                                        }
                                    });
                                }
                            }
                        } else if (parentTr.hasClass('simple')) {
                            pidsArray.push(jQuery(this).find('input[name="pid[]"]').val());
                            pqtyArray.push(jQuery(this).find('input[name="qty[]"]').val());
                        }
                    });

                    //console.log('adding simple');
                    /*if(pidsArray.length > 0) {
                     var pids    =  pidsArray.join(',');
                     var pqty    =  pqtyArray.join(',');
                     for (var p = 0; p < pidsArray.length; p++) {
                     var jparams = {};
                     jparams['simple'] = {'pids': pidsArray[p],'pqty': pqtyArray[p] };
                     //jparams['simple'] = {'pids':jQuery.trim(pids),'pqty': jQuery.trim(pqty) };
                     jQuery.ajax({
                     method: 'POST',
                     async: false,
                     url:jQuery('#sbu').val()+"quickorder/index/addproduct",
                     data: jparams,
                     success:function(response){
                     //console.log('====response====>'+ response);
                     var result = jQuery.parseJSON(response);
                     if(jQuery.trim(result.status) == 'failure') {
                     addedToCart = false;    
                     jQuery('#quickorder_error').text(result.message);
                     jQuery('#quickorder_error_wrap').show();
                     } 
                     }
                     });
                     }
                     }*/

                    if (pidsArray.length > 0) {
                        var pids = pidsArray.join(',');
                        var pqty = pqtyArray.join(',');
                        //for (var p = 0; p < pidsArray.length; p++) {
                        var jparams = {};
                        //jparams['simple'] = {'pids': pidsArray[p],'pqty': pqtyArray[p] };
                        jparams['simple'] = {'pids': jQuery.trim(pids), 'pqty': jQuery.trim(pqty)};
                        jQuery.ajax({
                            method: 'POST',
                            async: false,
                            url: jQuery('#sbu').val() + "quickorder/index/addproduct",
                            data: jparams,
                            success: function (response) {
                                //console.log('====response====>'+ response);
                                var result = jQuery.parseJSON(response);
                                if (jQuery.trim(result.status) == 'failure') {
                                    addedToCart = false;
                                    jQuery('#quickorder_error').text(result.message);
                                    jQuery('#quickorder_error_wrap').show();
                                }
                            }
                        });
                        //}
                    }

                    jQuery('#quickorder_loader').hide();
                    if (addedToCart == true) {
                        jQuery('#quickorder_message').text("Products were added to cart successfully.");
                        jQuery('#quickorder_message_wrap').show();
                        hideMessage();
                        jQuery('#quickorder-form button.clear-row').click();
                        return false;
                    }
               // }, 100);
                return false;
            });


            jQuery('button.add-all-sku').click(function () {
                jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();
                jQuery('#quickorder_loader').show();
                var psku = jQuery.trim(jQuery('#prod_skus').val());
                var errorInDataList = false;
                if (jQuery.trim(psku) != '') {
                    var prodList = jQuery.trim(psku).split(',');
                    for (var prod in prodList) {
                        var validated = prodList[prod].indexOf(jQuery.trim(jQuery('#separator').val()));
                        if (validated == -1) {
                            errorInDataList = true;
                        }
                        var skudata = prodList[prod].split(jQuery.trim(jQuery('#separator').val()));
                        //console.log('===skudata[1]*1=====>'+ skudata[0] +'===='+ skudata[1]+'==='+ isNaN(skudata[1]) );
                        if (isNaN(skudata[1])) {
                            errorInDataList = true;
                        }
                    }
                } else {
                    jQuery('#quickorder_loader').hide();
                    jQuery('#quickorder_error').text("Please enter any sku to proceed");
                    jQuery('#quickorder_error_wrap').show();
                    return false;
                }
                if (errorInDataList) {
                    jQuery('#quickorder_error').text("Please enter the correct SKU's and Quantity, separated by comma");
                    jQuery('#quickorder_error_wrap').show();
                    jQuery('#quickorder_loader').hide();
                    return false;
                }
                var jparams = {};
                jparams['simple'] = {'psku': psku};
                jQuery.post(jQuery('#sbu').val() + "quickorder/index/addproduct", jparams, function (response) {
                    jQuery('#quickorder_loader').hide();
                    var result = jQuery.parseJSON(response);
                    if (jQuery.trim(result.status) == 'success') {
                        jQuery('#quickorder_message').text(result.message);
                        jQuery('#quickorder_message_wrap').show();
                        jQuery('#quickorder-form button.clear-sku-list').click();
                    } else if (jQuery.trim(result.status) == 'failure') {
                        jQuery('#quickorder_error').html(result.message.split(',').join('<br/>'));
                        jQuery('#quickorder_error_wrap').show();
                    }
                });
                return false;
            });

            jQuery('button.clear-sku-list').click(function () {
                jQuery('#prod_skus').val('');
                return false;
            });

            jQuery('button.add-item').click(function () {
                var newDynamicRow = jQuery('<tr>' +
                        '<td class="td-pcode">' +
                        '<input class="txt-pcode" type="text" name="piden[]" value="" />' +
                        '<div class="item_loader" style="display:none;width:25px;height:25px;"><img width="25" height="25" src="' + jQuery('#img_path').val() + '/loading.gif"></div>' +
                        '<input type="hidden" name="pid[]" value="" />' +
                        '</td>' +
                        '<td class="td-qty"><input class="txt-qty" type="text" name="qty[]" /></td>' +
                        '<td class="td-action" >' +
                        '<button title="Clear" class="btn-remove-cart button clear-row"><span><span>Clear</span></span></button>' +
                        '</td>' +
                        '</tr>');
                newDynamicRow.insertBefore('.quick-order-body tr:last');
                enableAutocomplete(newDynamicRow);
                return false;
            });

            jQuery('button.remove-item').click(function () {
                jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();
                if (jQuery('.quick-order-body').children('tr').length > 2) {
                    jQuery('.quick-order-body').children('tr:last').prev().remove();
                } else {
                    jQuery('#quickorder_error').text("First row can't be deleted");
                    jQuery('#quickorder_error_wrap').show();
                }
                return false;
            });
        }
);


function enableAutocomplete(pSelector) {
    var cache = {};
    var ac_min_chars = (jQuery('#ac_min_chars').val() * 1 > 0) ? jQuery('#ac_min_chars').val() * 1 : 3;
    jQuery('#quickorder-form').find('input[name="piden[]"]').autocomplete({
        minLength: ac_min_chars,
        delay: 500,
        html: true,
        source: function (request, response) {
            var searchKeyword = request.term;
            if (jQuery.trim(searchKeyword) != '') {
                if (searchKeyword in cache) {
                    response(cache[searchKeyword]);
                    return;
                }
                var ac_item = this.element;
                ac_item.siblings('.item_loader').show(function () {
                    jQuery.ajax({
                        cache: true,
                        dataType: 'json',
                        method: 'POST',
                        /*async: false,*/
                        url: jQuery('#sbu').val() + 'quickorder',
                        data: {query: searchKeyword},
                        crossDomain: false,
                        success: function (datasuggestions) {
                            /*if(jQuery.trim(datasuggestions) == '') {
                             datasuggestions = [{"label":"No Matches Found","title":"No Matches Found","value":"no-matches"}];
                             }*/
                            cache[searchKeyword] = datasuggestions;
                            response(datasuggestions);
                            ac_item.siblings('.item_loader').hide();
                        }
                    });
                });

            }
        },
        /*search: function( event, ui ) {
         jQuery(this).siblings('.item_loader').show();            
         },
         response: function( event, ui ) {
         jQuery(this).siblings('.item_loader').hide();
         },*/
        focus: function (event, ui) {
            if (jQuery.trim(ui.item.value) != 'no-matches') {
                var itemTitle = jQuery('<textarea />').html(ui.item.label).text();
                jQuery(this).val(itemTitle);
            }
            return false;
        },
        select: function (event, ui) {
            if (ui.item.value == 'no-matches')
                return false;
            var ac_element = jQuery(this);
            ac_element.siblings('.item_loader').show();
            var elmParent = jQuery(this).parents('tr');
            var parentTd = jQuery(this).parents('td');
            jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();
            var prodId = ui.item.value;
            var itemTitle = jQuery('<textarea />').html(ui.item.label).text();
            jQuery(this).val(itemTitle);
            jQuery(this).siblings('input:hidden').val(ui.item.value);
            elmParent.find('.txt-sku').html(ui.item.sku);
            elmParent.find('.pqty').html(ui.item.price);
            elmParent.find('.txt-qty').val(1);
            parentTd.find('.confBlock').remove();
            if (jQuery.trim(ui.item.ptype) == 'configurable' && (ui.item.value * 1) > 0) {
                elmParent.removeClass('simple').addClass('configurable');
                elmParent.find('.txt-qty').hide();
                elmParent.find('.pqty').html('');
                var $confBlock = jQuery("<div>", {class: 'confBlock'});
                parentTd.append($confBlock);
                jQuery.ajax({
                    cache: true,
                    method: 'POST',
                    url: jQuery('#sbu').val() + "quickorder/index/getproductinfo",
                    data: {ptype: jQuery.trim(ui.item.ptype), pid: ui.item.value},
                    crossDomain: false,
                    success: function (confResponse) {
                        if (confResponse != '') {
                            var cresults = jQuery.parseJSON(confResponse);
                            if (jQuery.trim(cresults.status) == 'success') {
                                var confOptions = optionwidth = optionMargin = '';
                                var attCounter = 0;
                                var attIds = new Array();
                                jQuery.each(cresults.cdata.data, function (cindex, cvalue) {
                                    attCounter++;
                                    var attParts = cindex.split('_');
                                    attIds[attCounter] = attParts[1] + '$$$' + cvalue.attrLabel + '$$$' + attParts[0];
                                    if (jQuery.trim(attParts[0]) == 'size') {
                                        optionwidth = '100px';
                                        optionMargin = 'margin-left:10px;';
                                    } else {
                                        optionwidth = '125px';
                                        optionMargin = '';
                                    }
                                    var $selectBox = jQuery("<select>", {id: cindex + '_' + ui.item.value, title: jQuery.trim(attParts[0]), class: 'confAttr_' + attCounter, name: jQuery.trim(attParts[0]) + '[]', style: optionMargin + 'width:' + optionwidth + ';'});
                                    $confBlock.append($selectBox);
                                    jQuery.each(cvalue, function (oid, ovalue) {
                                        if (jQuery.trim(oid) != 'attrLabel') {
                                            var $coption = jQuery("<option>", {value: oid});
                                            $coption.html(ovalue);
                                            $selectBox.append($coption);
                                        }
                                    });
                                });
                                var $cProduct = jQuery("<input>", {type: 'hidden', id: 'confProd', name: 'confProd[]'});
                                $confBlock.append($cProduct);
                                var $cquantityBox = jQuery("<input>", {type: 'text', id: 'confQty', value: '1', title: 'quantity', class: 'confQtyClass', style: 'margin-left:10px;width:50px;'});
                                $cProduct.after($cquantityBox);
                                var $cAddToListBtn = jQuery("<input>", {type: 'button', title: 'Add to list', id: 'confAddtoList', value: '+', class: 'confAddtoListClass'});
                                $cquantityBox.after($cAddToListBtn);
                                $confBlock.find("select[class='confAttr_1']").change(function () {
                                    $confBlock.find("select[class^='confAttr_']:not(select.confAttr_1)").each(function () {
                                        jQuery(this).find('option:eq(0)').prop('selected', 'true');
                                    });
                                });
                                var $selectedConfProductsBlock = jQuery('<div>', {id: 'confProdBlock', name: 'confProdBlock[]'});
                                $cAddToListBtn.after($selectedConfProductsBlock);
                                $cAddToListBtn.click(function () {
                                    var $selectedConfProduct = jQuery('<span>'); //,{ style:'margin-left:10px;margin-top:10px;' }
                                    $selectedConfProductsBlock.append($selectedConfProduct); //.append('<br/>')
                                    var mappingKeyParts = new Array();
                                    var priceMappingKeyParts = new Array();
                                    var super_attribute = attInfo = '';
                                    for (var attIndex = 1; attIndex <= attCounter; attIndex++) {
                                        attInfo = attIds[attIndex].split('$$$');
                                        attrField = jQuery(this).siblings('select.confAttr_' + attIndex);
                                        $selectedConfProduct.append("<strong>" + attInfo[1] + " : </strong>" + attrField.find('option:selected').text() + '&nbsp;&nbsp;');
                                        mappingKeyParts.push(attInfo[0] + '=' + attrField.val());
                                        priceMappingKeyParts.push(attInfo[2] + '_' + attrField.val());
                                    }
                                    $selectedConfProduct.append("<strong>Qty : </strong>" + attrField.siblings('.confQtyClass').val() + '&nbsp;&nbsp;');
                                    super_attribute = mappingKeyParts.join(',');
                                    var $superAttributeData = jQuery('<input>', {value: super_attribute, id: 'confAttrData', type: 'hidden', name: 'confAttrData[]'});
                                    $selectedConfProduct.append($superAttributeData);
                                    var $simpleProdQty = jQuery('<input>', {value: $cquantityBox.val(), id: 'confAttrQty', type: 'hidden', name: 'confAttrQty[]'});
                                    $selectedConfProduct.append($simpleProdQty);
                                    prodPriceKey = priceMappingKeyParts.join('-');
                                    jQuery.each(cresults.cdata.mapping, function (mindex, mvalues) {
                                        if (mindex == prodPriceKey) {
                                            $selectedConfProduct.append("<strong>Price : </strong>" + mvalues.price + '&nbsp;&nbsp;');
                                            return false;
                                        }
                                    });
                                    var $deleteConfProduct = jQuery('<img>', {class: 'deleteConfProduct', title: 'Delete', src: jQuery('#img_path').val() + '/delete.png', width: 16, height: 16});
                                    $selectedConfProduct.append($deleteConfProduct);
                                });
                                ac_element.siblings('.item_loader').hide();
                            }
                        }
                    }
                });
            } else {
                elmParent.removeClass('configurable').addClass('simple');
                elmParent.find('.txt-qty').show();
                ac_element.siblings('.item_loader').hide();
            }
            return false;
        }/*,
         messages: {
         noResults: '',
         results: function() {}
         }*/
    }).click(function () {
        jQuery(this).autocomplete('search');
    });
    return false;
}
function jsleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds) {
            break;
        }
    }
}