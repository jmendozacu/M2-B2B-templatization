var config = {
	map: {
	    '*': {
	        	/*jqueryui_google: 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js',*/
                ui_autocomplete_html: 'Dckap_Shoppinglist/js/jquery.ui.autocomplete.html',
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
        
               
        require(['ui_autocomplete_html'],function($){              
            enableAutocomplete(jQuery('#shoppinglist-form-add'));        
        });           


		 
    }
);


function enableAutocomplete(pSelector) {       
    var cache = {};
    var ac_min_chars = 3;
    jQuery('#shoppinglist-form-add').find('input[name="product_name[]"]').autocomplete({
        minLength : ac_min_chars,
        delay:500,
        html: true,        
        source: function (request, response) {
            var searchKeyword = request.term;
            if(jQuery.trim(searchKeyword) != '') {
                if ( searchKeyword in cache ) {
                    response(cache[searchKeyword]);
                    return;
                }
                var ac_item = this.element;
               // ac_item.siblings('.item_loader').show(function(){
                    jQuery.ajax({
                        cache:true,
                        dataType:'json',
                        method: 'POST',
                        /*async: false,*/
                        url: Base_url +'shoppinglist/index/getproduct',
                        data:{query:searchKeyword},
                        crossDomain:false,
                        success:function(datasuggestions){
                            /*if(jQuery.trim(datasuggestions) == '') {
                                datasuggestions = [{"label":"No Matches Found","title":"No Matches Found","value":"no-matches"}];
                            }*/
                            cache[searchKeyword] = datasuggestions;
                            response(datasuggestions);  
                           // ac_item.siblings('.item_loader').hide();  
                        }
                    });
               // });

            }                              
        },
        /*search: function( event, ui ) {
            jQuery(this).siblings('.item_loader').show();            
        },
        response: function( event, ui ) {
            jQuery(this).siblings('.item_loader').hide();
        },*/
        focus: function( event, ui ) {
            if(jQuery.trim(ui.item.value) != 'no-matches') {          
                var itemTitle = jQuery('<textarea />').html(ui.item.label).text();
                jQuery(this).val(itemTitle);
            }    
            return false;
        },
        select: function( event, ui ) {
            if(ui.item.value == 'no-matches')
                return false;
            var ac_element = jQuery(this);
            ac_element.siblings('.item_loader').show();            
            var elmParent = jQuery(this).parents('tr');
            var parentTd = jQuery(this).parents('td');
            //jQuery('#quickorder_message_wrap,#quickorder_error_wrap').hide();
            var prodId = ui.item.value;
            var itemTitle = jQuery('<textarea />').html(ui.item.label).text();
            jQuery(this).val(itemTitle);
            jQuery(this).siblings('input:hidden').val( ui.item.productid );            
            elmParent.find('.txt-sku').html(ui.item.sku);
            elmParent.find('.pqty').html(ui.item.price);
            elmParent.find('.txt-qty').val(1);
            parentTd.find('.confBlock').remove();
           
                elmParent.addClass('simple');
                elmParent.find('.txt-qty').show();
                ac_element.siblings('.item_loader').hide();
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
function jsleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}
