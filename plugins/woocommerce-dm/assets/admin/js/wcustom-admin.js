jQuery(document).ready(function($){
	jQuery('.dm_ajax_call').click(function() {
		var t = jQuery(this).data('type');
		
		jQuery(this).replaceWith('<span id="' + t + '">Bitte warten....</span>');

		$.ajax({
			type: "POST",
			dataType: "json",
			url: adminVars.ajaxurl,
			data: {
				'action': 'woocommerce-dm-' + t
			},
			success: function( data ) {
				jQuery('#' + t).html(data.html);
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				console.log(errorThrown);
				jQuery('#' + t).html(textStatus);
			}
		});
		return false;
	});
	
	jQuery('#acf-group_5d7540559d0ce input, #acf-group_5d7540559d0ce textarea').prop('readonly', true);
	jQuery('#acf-group_5d7540559d0ce input, #acf-group_5d7540559d0ce textarea').prop('disabled', true);
});