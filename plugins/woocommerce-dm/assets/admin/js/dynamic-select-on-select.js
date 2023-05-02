
/*function disableUsedCheckboxes() {
	if(jQuery(this).is(':checked')) {
		jQuery('[type="checkbox"][value="'+jQuery(this).val()+'"]').prop('disabled', true);
		jQuery('[type="checkbox"][value="'+jQuery(this).val()+'"]').prop('checked', false);
		jQuery(this).prop('disabled', false);
		jQuery(this).prop('checked', true);
	} else {
		jQuery('[type="checkbox"][value="'+jQuery(this).val()+'"]').prop('disabled', false);
	}
}

jQuery(document).ready(function($){
	if (typeof acf == 'undefined') { return; }
	
	acf.add_filter('select2_ajax_results', function( json, params, instance ){
		
		var change_json = false;
		
		if(instance.$el.closest('[data-name]').data('name') == 'wc_dtime') {
			change_json = 'wc_dtime';
		} else if(instance.$el.closest('[data-name]').data('name') == 'wc_unit') {
			change_json = 'wc_unit';
		}
		
		if(change_json) {
			var njson = {
				limit: 20,
				more: false,
				results: []
			};
	
			var already_selected = [];
			jQuery('[data-name="' + change_json + '"] .select2-hidden-accessible').each(function(k) {
				if(jQuery(this).val() && instance.$el.val() != jQuery(this).val()) {
					already_selected.push(parseInt(jQuery(this).val()));		
				}
			});
			
		    jQuery.each(json.results, function(k, v) {
		    	if(already_selected.indexOf(v.id) == -1) {
		    		njson.results.push(v);
		    	}
		    });
		    
		    // return
		    return njson;
		}
		
		return json;

	});
	
	jQuery('[data-taxonomy="dm_units"] input[type="checkbox"]:checked').each(disableUsedCheckboxes);
	jQuery('[data-taxonomy="dm_units"] input[type="checkbox"]').change(disableUsedCheckboxes);
	jQuery('[data-taxonomy="dm_dtimes"] input[type="checkbox"]:checked').each(disableUsedCheckboxes);
	jQuery('[data-taxonomy="dm_dtimes"] input[type="checkbox"]').change(disableUsedCheckboxes);

	jQuery('select[data-name="dm_rate"]').focus(function() {
		
		var already_selected = [];
		jQuery('select[data-name="dm_rate"] option:selected').each(function(k) {
			if(jQuery(this).attr('value') && jQuery(this).attr('value') != '0') {
				already_selected.push(parseInt(jQuery(this).attr('value')));		
			}
		});
		
		jQuery(this).find('option').each(function() {
	    	if(already_selected.indexOf(parseInt(jQuery(this).attr('value'))) > -1 && !jQuery(this).is(':selected')) {
	    		jQuery(this).hide();
	    	} else {
	    		jQuery(this).show();
	    	}
	    });

	});
});*/

jQuery(document).ready(function($){
	var remove_radios = [
		'dm_dtimes',
		'dm_units'
	];
	for(var k = 0; k < remove_radios.length; k++) {
		var key = remove_radios[k];

		var i = 1;
		jQuery('[data-taxonomy="'+key+'"] ul').each(function() {
			jQuery(this).find('li:nth-child(' + i + ') input').prop('checked', true);
			i++;
		});
		jQuery('[data-taxonomy="'+key+'"] ul li input:not(:checked)').closest('li').remove();
		jQuery('[data-taxonomy="'+key+'"] ul li input').hide();
	}
});