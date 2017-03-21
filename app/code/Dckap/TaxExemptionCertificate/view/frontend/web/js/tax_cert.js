require([ 'jquery'], function(){
	jQuery(document).ready(function(){
		jQuery('#taxcert').change(function () {
			var current_selected_value = this.value;
			jQuery('div#'+this.value).show();
			jQuery( "div.taxcerts" ).each(function() {
			if(current_selected_value != jQuery(this).attr('id'))
			 	{
			 	 	jQuery(this).hide();
			 	}
			});
		})
	});
	//jQuery('.sk-cube-grid').hide();
	jQuery('#sortpicture').on('click', function() {
	//jQuery('.sk-cube-grid').hide();
    jQuery("div#success-alert").hide();
    });

	
	jQuery('#upload').on('click', function() {
		//jQuery("div#loading_upload").attr('style','display: block');
		jQuery('div.sk-cube-grid').show();
    	var file_data = jQuery('#fileToUpload').prop('files')[0];   
   		var form_data = new FormData();                  
    	form_data.append('file', file_data);
 		jQuery.ajax({
            url: jQuery("#tax_cert_url").val(), 
            dataType: 'text', 
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,                         
            type: 'post',
            success: function(data){
                jQuery("#uploaded_files").append('<a href="pub_media_url'+data+'">'+data+'</a>');
                jQuery("div#success-alert").show();
                jQuery("div#loading_upload").attr('style','display: none')
            }
     	});
	});
});
	
