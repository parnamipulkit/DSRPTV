jQuery(document).ready(function($){

	$.each( dsrptv_admin_localize.forms, function( key, form ){
		$('<a href="'+form.url+'"><button class="primary dsrptv-add-btn dsrptv-add-'+key+'form">'+form.text+'</button></a>').insertAfter('button.gform-add-new-form');
	} )

});