var config = {
    map: {
        '*': {
                /*jqueryui_google: 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js',*/
                ui_autocomplete_quote_html: 'Dckap_Requestquote/js/jquery.ui.autocomplete.quote.html',
                /*"jquery/ui": 'DCKAP_Quickorder/js/jquery-ui'*/
                "jquery/ui": 'Dckap_Quickorder/js/jquery-ui'                
              } 
        },
    paths: {
        /*"jquery/ui": 'DCKAP_Quickorder/js/jquery-ui'*/
        "jquery/ui": 'Dckap_Quickorder/js/jquery-ui'
    }  
};

require(['jquery','Magento_Ui/js/modal/modal'],
    
    function($,modal) {
        
               
        require(['ui_autocomplete_quote_html'],function($){              
            enableAutocomplete(jQuery('#requestquote_form'));        
        });           


         
    }
);

function enableAutocomplete(pSelector) {  
    var cache = {};
    jQuery('#requestquote_form').find('input[name="req_product[]"]').autocomplete({
        minLength : 4,
        delay:500,
        html: true,        
        source: function (request, response) {
            var searchKeyword = request.term;
            //alert(searchKeyword);
            if(jQuery.trim(searchKeyword) != '') {
                if ( searchKeyword in cache ) {
                    response(cache[searchKeyword]);
                    return;
                }
                var ac_item = this.element;
                ac_item.siblings('.item_loader').show(function(){
                    jQuery.ajax({
                        cache:true,
                        dataType:'json',
                        method: 'POST',
                        /*async: false,*/
                        url:jQuery('#sbu').val()+'quickorder/index/quote',
                        data:{query:searchKeyword},
                        crossDomain:false,
                        success:function(datasuggestions){
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
        
        select: function( event, ui ) {
            if(ui.item.value == 'no-matches')
                return false;
            var ac_element = jQuery(this);
            ac_element.siblings('.item_loader').show();            
            var elmParent = jQuery(this).parents('div');
            //var parentTd = jQuery(this).parents('td');
            //var parentTd = jQuery(this).parents('td');
            //jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();
            var prodId = ui.item.value;
            var itemTitle = jQuery('<textarea />').html(ui.item.label).text();
            jQuery(this).val(itemTitle);
            jQuery(this).siblings('input:hidden').val( ui.item.value );            
            //elmParent.find('.txt-sku').html(ui.item.sku);
            //elmParent.find('.pqty').html(ui.item.price);
            //elmParent.find('.txt_qty').val(1);
            //ac_element.parent('#pdescriptionf').text(ui.item.pdesc);
            console.log(jQuery(this).parent().parent().find( "div[id^='description_']" ))
            jQuery(this).parent().parent().find( "div[id^='description_']" ).html(ui.item.label);
            //parentTd.find('.confBlock').remove();
            if(jQuery.trim(ui.item.ptype) == 'configurable' && (ui.item.value*1) > 0) {                                
                elmParent.removeClass('simple').addClass('configurable');
                elmParent.find('.txt-qty').hide();
                elmParent.find('.pqty').html('');
                var $confBlock = jQuery("<div>",{ class:'confBlock'});                                
                //parentTd.append($confBlock);   
                jQuery.ajax({
                    cache:true,
                    method: 'POST',
                    url:jQuery('#sbu').val()+"quickorder/index/getproductinfo",
                    data:{ptype:jQuery.trim(ui.item.ptype),pid:ui.item.value},
                    crossDomain:false,
                    success:function(confResponse){
                        if(confResponse != '') {
                            var cresults = jQuery.parseJSON(confResponse);
                            if(jQuery.trim(cresults.status) == 'success') {
                                var confOptions = optionwidth = optionMargin = '';                            
                                var attCounter = 0;
                                var attIds = new Array();
                                jQuery.each(cresults.cdata.data, function (cindex,cvalue) {
                                    attCounter++;
                                    var attParts = cindex.split('_');
                                    attIds[attCounter] = attParts[1]+'$$$'+cvalue.attrLabel+'$$$'+attParts[0];
                                    if(jQuery.trim(attParts[0]) == 'size') {
                                        optionwidth = '100px';optionMargin = 'margin-left:10px;';
                                    }
                                    else {
                                        optionwidth = '125px';optionMargin = '';
                                    }
                                    var $selectBox = jQuery("<select>",{ id:cindex+'_'+ui.item.value, title: jQuery.trim(attParts[0]), class:'confAttr_'+attCounter, name: jQuery.trim(attParts[0])+'[]', style: optionMargin+'width:'+optionwidth+';'});                                
                                    $confBlock.append($selectBox);
                                    jQuery.each(cvalue, function (oid,ovalue) {
                                        if(jQuery.trim(oid) != 'attrLabel') {
                                            var $coption = jQuery("<option>",{value:oid});
                                            $coption.html(ovalue);
                                            $selectBox.append($coption);    
                                        }                                                                       
                                    });
                                });                                
                                var $cProduct       = jQuery("<input>" , { type: 'hidden', id:'confProd' , name:'confProd[]' });
                                $confBlock.append($cProduct);                                
                                var $cquantityBox   = jQuery("<input>" , { type: 'text', id:'confQty' , value:'1', title: 'quantity', class:'confQtyClass', style:'margin-left:10px;width:50px;' });
                                $cProduct.after($cquantityBox);                                
                                var $cAddToListBtn = jQuery("<input>" , { type: 'button', title: 'Add to list', id:'confAddtoList', value:'+', class:'confAddtoListClass'});
                                $cquantityBox.after($cAddToListBtn);
                                $confBlock.find("select[class='confAttr_1']").change(function(){
                                    $confBlock.find("select[class^='confAttr_']:not(select.confAttr_1)").each(function(){
                                        jQuery(this).find('option:eq(0)').prop('selected','true');
                                    });    
                                });                                
                                var $selectedConfProductsBlock = jQuery('<div>',{ id:'confProdBlock' , name:'confProdBlock[]' }); 
                                $cAddToListBtn.after($selectedConfProductsBlock);
                                $cAddToListBtn.click(function(){                                    
                                    var $selectedConfProduct = jQuery('<span>'); //,{ style:'margin-left:10px;margin-top:10px;' }
                                    $selectedConfProductsBlock.append($selectedConfProduct); //.append('<br/>')
                                    var mappingKeyParts = new Array();
                                    var priceMappingKeyParts = new Array();
                                    var super_attribute = attInfo = '';
                                    for (var attIndex = 1; attIndex <= attCounter; attIndex++) {
                                        attInfo = attIds[attIndex].split('$$$');
                                        attrField = jQuery(this).siblings('select.confAttr_'+attIndex);
                                        $selectedConfProduct.append("<strong>"+attInfo[1]+" : </strong>"+attrField.find('option:selected').text()+'&nbsp;&nbsp;');
                                        mappingKeyParts.push(attInfo[0]+'='+attrField.val());
                                        priceMappingKeyParts.push(attInfo[2]+'_'+attrField.val());
                                    }
                                    $selectedConfProduct.append("<strong>Qty : </strong>"+attrField.siblings('.confQtyClass').val()+'&nbsp;&nbsp;');                                    
                                    super_attribute = mappingKeyParts.join(',');
                                    var $superAttributeData = jQuery('<input>',{ value: super_attribute , id:'confAttrData', type:'hidden' , name:'confAttrData[]'});
                                    $selectedConfProduct.append($superAttributeData);
                                    var $simpleProdQty = jQuery('<input>',{ value:  $cquantityBox.val(), id:'confAttrQty', type:'hidden' , name:'confAttrQty[]'});
                                    $selectedConfProduct.append($simpleProdQty);                                    
                                    prodPriceKey = priceMappingKeyParts.join('-');
                                    jQuery.each(cresults.cdata.mapping, function (mindex,mvalues) {
                                        if(mindex == prodPriceKey) {
                                            $selectedConfProduct.append("<strong>Price : </strong>"+mvalues.price+'&nbsp;&nbsp;');
                                            return false;
                                        }
                                    });
                                    var $deleteConfProduct = jQuery('<img>',{ class: 'deleteConfProduct', title: 'Delete', src: jQuery('#img_path').val()+'/delete.png' , width:16, height:16});
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
    }).click(function(){
            jQuery(this).autocomplete('search');        
    });  
    return false;
}