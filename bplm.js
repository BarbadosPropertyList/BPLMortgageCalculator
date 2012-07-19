jQuery(document).ready(function($) {
	
	jQuery(".bplm_submit").click(function(event){ // function launched when submiting form
		
		event.preventDefault(); //disable default behavior
		
		var parent_element = jQuery(this).parent('.bplm_calculator');
		
		parent_element.find('.bplm_results').html('Loading...');
		
		var data = { //looks for and sets all variables used for export
			action: 'bplm_count_ajax',
			purchase: parent_element.find('input[name="bplm_options_purchase"]').val(),
			down: parent_element.find('input[name="bplm_options_down"]').val(),
			interest: parent_element.find('input[name="bplm_options_interest"]').val(),
			tax: parent_element.find('input[name="bplm_options_tax"]').val(),
			pmi: parent_element.find('input[name="bplm_options_pmi"]').val(),
			years: parent_element.find('input[name="bplm_options_years"]').val(),
			startdate_year: parent_element.find('select[name="bplm_options_startdate_year"]').val(),
			startdate_month: parent_element.find('select[name="bplm_options_startdate_month"]').val()
		};
		
		jQuery.post(bplm_add_js.ajax_url, data, function(data){ //post data to specified action trough special WP ajax page
			parent_element.find('.bplm_results').html(data);
		});

	});
	

	jQuery('.form_procent').currency({ region: 'PRC', thousands: '', decimal: '.', decimals: 2 });
	
	jQuery('.form_procent').focusout(function () {
		jQuery(this).currency({ region: 'PRC', thousands: '', decimal: '.', decimals: 2 });
	});
	
	jQuery('.form_currency').currency({ decimals: 0 });
		
	jQuery('.form_currency').focusout(function () { 
		jQuery(this).currency({ decimals: 0 });
	});
	
	jQuery('.form_numbers').keyup(function () { 
		this.value = this.value.replace(/[^0-9\.]/g,'');
	});
	
	jQuery('.form_numbers').focusin(function () { 
		this.value = this.value.replace(/[^0-9\.]/g,'');
	});

});