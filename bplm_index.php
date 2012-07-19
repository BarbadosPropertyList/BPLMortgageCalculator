<?php
/*
Plugin Name: BPL Mortgage Calculator
Plugin URI:
Description: BPL Mortgage Calculator
Version: 0.1
Author: BarbadosPropertyList
Author URI:
*/

register_activation_hook(__FILE__, 'bplm_activation');

function bplm_activation() {
	
}

register_deactivation_hook(__FILE__, 'bplm_deactivation');
function bplm_deactivation() {

}

//Loads Javascript for users so AJAX magic can happen
add_action( 'template_redirect', 'bplm_add_js' );
function bplm_add_js() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js');
    wp_enqueue_script( 'jquery' );

	wp_enqueue_script( 'jquery_currency', plugins_url( 'js/jquery.currency.js' , __FILE__ ), array('jquery') );
	
	wp_enqueue_script( 'bplm_add_js', plugins_url( 'js/bplm.js' , __FILE__ ), array('jquery') );

	$protocol = isset( $_SERVER["HTTPS"] ) ? 'https://' : 'http://'; //This is used to set correct adress if secure protocol is used so ajax calls are working
	$params = array(
		'ajax_url' => admin_url( 'admin-ajax.php', $protocol )
	);
	wp_localize_script( 'bplm_add_js', 'bplm_add_js', $params );
}

// Shortcode magic:) sample usage:
// [bpl_currecy_converter amount="200" from="BBD" to="USD" show_time="no"].
// Defaults same as bplm_calculator_view function
add_shortcode( 'bpl_mortgage', 'bplm_calculator_shortcode_view' );
function bplm_calculator_shortcode_view($options) {
	if(!isset($options['purchase'])) {
		$options['purchase'] = '250000';
	}
	if(!isset($options['down'])) {
		$options['down'] = '25000';
	}
	if(!isset($options['interest'])) {
		$options['interest'] = '5';
	}
	if(!isset($options['tax'])) {
		$options['tax'] = '3125';
	}
	if(!isset($options['years'])) {
		$options['years'] = '30';
	}
	
	foreach ($options as $key => $value) {
		$options[$key] = preg_replace('/[^0-9.]*/', '', $value);
	}	
	
	$return = '<div class="bpl_mortgage_calculator_shortcode">';
	$return .= bplm_calculator_view( $options['purchase'], $options['down'], $options['interest'], $options['tax'], $options['years'], 0 );
	$return .= '</div>';
	
	return $return;
	
}

//function that displays and does all the processing
function bplm_calculator_view( $purchase = '250000', $down = '25000', $interest = '5.0', $tax = '3125', $years = '30', $echo = 1 ) {

	//sets up/gets values for later use
	
	$return = '
	<div class="bplm_calculator_holder">
		<form method="post" action="" class="bplm_calculator" name="bplm_calculator">
			<p class="bplm_options_purchase_holder bplm_options_holder">
				<label class="bplm_options_purchase_label" for="bplm_options_purchase">'.__('Purchase Price:', 'bplm_plugin').'</label>
				<input class="bplm_options_purchase form_numbers form_currency" name="bplm_options_purchase" value="'.$purchase.'" />
			</p>

			<p class="bplm_options_down_holder bplm_options_holder">
				<label class="bplm_down_label" for="bplm_options_down">'.__('Down Payment:', 'bplm_plugin').'</label>
				<input class="bplm_options_down form_numbers form_currency" name="bplm_options_down" value="'.$down.'" />
			</p>
			
			<p class="bplm_options_interest_holder bplm_options_holder">
				<label class="bplm_interest_label" for="bplm_options_interest">'.__('Interest Rate:', 'bplm_plugin').'</label>
				<input class="bplm_options_interest form_numbers form_procent" name="bplm_options_interest" value="'.$interest.'" />
			</p>

			<p class="bplm_options_tax_holder bplm_options_holder">
				<label class="bplm_tax_label" for="bplm_options_tax">'.__('Property Tax (Per Year):', 'bplm_plugin').'</label>
				<input class="bplm_options_tax form_numbers form_currency" name="bplm_options_tax" value="'.$tax.'" />
			</p>';
			
			//<p class="bplm_options_pmi_holder bplm_options_holder">
				//<label class="bplm_pmi_label" for="bplm_options_pmi">'.__('PMI:', 'bplm_plugin').'</label>
				//<input class="bplm_options_pmi form_numbers" name="bplm_options_pmi" value="'.$pmi.'" />
			//</p>
			
	$return .='
			<p class="bplm_options_years_holder bplm_options_holder">
				<label class="bplm_years_label" for="bplm_options_years">'.__('Term (years):', 'bplm_plugin').'</label>
				<input class="bplm_options_years form_numbers" name="bplm_options_years" value="'.$years.'" />
			</p>
			
			<p class="bplm_options_startdate_holder bplm_options_holder">
				<label class="bplm_option_startdate_label" for="bplm_options_startdate">'.__('Mortgage Start Date:', 'bplm_plugin').'</label>
				'.date_dropdown(10).'
			</p>
						
			<input class="bplm_submit" type="submit" value="Calculate" style="width: 200px; height: 35px;"/>
			
			<div class="bplm_results">
			</div>
			
		</form>

	</div>
	';
	
	if($echo != 1) {
		return $return;
	}
	else {
		echo $return;
	}
	
}

//handle all the AJAX counting - TO DO!!!!!!!!!!!!!!!!!!!!!!!!!
add_action( 'wp_ajax_bplm_count_ajax', 'bplm_count_ajax' );
add_action('wp_ajax_nopriv_bplm_count_ajax', 'bplm_count_ajax');
function bplm_count_ajax() {
	
	foreach ($_POST as $key => $value) {
		$_POST[$key] = preg_replace('/[^0-9.]*/', '', $value);
	}
var_dump($_POST);
	
	if( is_numeric($_POST['purchase']) && is_numeric($_POST['down']) && is_numeric($_POST['interest']) && is_numeric($_POST['tax']) && is_numeric($_POST['years']) && is_numeric($_POST['startdate_month']) && is_numeric($_POST['startdate_year']) ) {
		
		$loan = $_POST['purchase'] - $_POST['down'];
		$interest = $_POST['interest'] / 100;
		$tax = $_POST['tax'];
		$years = $_POST['years'];
		$startdate_month = $_POST['startdate_month'];
		$startdate_year = $_POST['startdate_year'];
		
		$months = $_POST['years']*12;
		$tax_month = $tax/12;
		
		$monthly = (($interest/12) * $loan ) / (1 - ( pow( (1 + ($interest/12)), (-$years*12)) )  );
		$monthly_with_taxes = $monthly + $tax_month ;

		$total = $monthly_with_taxes * $months;
		
		$total_interest = $monthly * $months - $loan;
		
		$payoff_year = $startdate_year+$years;
		$payoff_month = strtotime('01-'.$startdate_month.'-1970');
		$payoff_date = date("M.", $payoff_month).' '.$payoff_year;
		
	?>
		
		<div class="bplm_result_loan_holder bplm_results_holder">
			<label class="bplm_result_loan_label bplm_results_label">
				<?php _e('Loan Amount:', 'bplm_plugin'); ?>
			</label>
			<span class="bplm_result_loan bplm_results">
				<strong>&#36;<?php echo number_format($loan , 0, '.', ','); ?></strong>
			</span>
		</div>
		<div class="bplm_result_monthly_with_taxes_holder bplm_results_holder">
			<label class="bplm_result_monthly_with_taxes_label bplm_results_label">
				<?php _e('Monthly payment:', 'bplm_plugin'); ?>
			</label>
			<span class="bplm_result_monthly_with_taxes bplm_results">
				<strong>&#36;<?php echo number_format($monthly_with_taxes , 2, '.', ','); ?></strong>
			</span>
		</div>
		<div class="bplm_result_total_holder bplm_results_holder">
			<label class="bplm_result_total_label bplm_results_label">
				<?php _e('Over ', 'bplm_plugin'); echo $months; _e(' payments:', 'bplm_plugin'); ?>
			</label>
			<span class="bplm_result_total bplm_results">				
				<strong>&#36;<?php echo number_format($total , 2, '.', ','); ?></strong>
			</span>
		</div>
		<div class="bplm_result_total_interest_holder bplm_results_holder">
			<label class="bplm_result_total_interest_label bplm_results_label">
				<?php _e('Total Interest:', 'bplm_plugin'); ?>
			</label>
			<span class="bplm_result_total_interest bplm_results">
				<strong>&#36;<?php echo number_format($total_interest , 2, '.', ','); ?></strong>
			</span>
		</div>
		<div class="bplm_result_payoff_date_holder bplm_results_holder">
			<label class="bplm_result_payoff_date_label bplm_results_label">
				<?php _e('Pay-off Date:', 'bplm_plugin'); ?>
			</label>
			<span class="bplm_result_payoff_date bplm_results">
				<strong><?php echo $payoff_date; ?></strong>
			</span>
		</div>
	
	<?php
	}
	else {
		echo 'Error';
	}

	//echo number_format($result , 2, '.', ',');

	die();

}

//Creates widget for BPL Converter
add_action( 'widgets_init', 'bplm_load_widget' );
function bplm_load_widget() {
	register_widget( 'bplm_widget' );
}

class bplm_widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function bplm_widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'bplm_widget', 'description' => __('Barbados Property List Mortgage Calculator.', 'bplm_widget') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'bplm-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'bplm-widget', __('BPL Mortgage Calculator', 'bplm_widget'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$purchase = $instance['purchase'];
		$down = $instance['down'];
		$interest = $instance['interest'];
		$tax = $instance['tax'];
		//$pmi = $instance['pmi'];
		$years = $instance['years'];
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		//Function that is doing all the magic
		bplm_calculator_view($purchase, $down, $interest, $tax, $years);

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['purchase'] = preg_replace('/[^0-9.]*/', '', $new_instance['purchase']);
		$instance['down'] = preg_replace('/[^0-9.]*/', '', $new_instance['down']);
		$instance['interest'] = preg_replace('/[^0-9.]*/', '', $new_instance['interest']);
		$instance['tax'] = preg_replace('/[^0-9.]*/', '', $new_instance['tax']);
		//$instance['pmi'] = strip_tags( $new_instance['pmi'] );
		$instance['years'] = preg_replace('/[^0-9.]*/', '', $new_instance['years']);
		//$instance['startdate'] = strip_tags( $new_instance['startdate'] );
		
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 
			'title' => __('BPL Mortgage Calculator', 'bplm_plugin'),
			'purchase' => '250000', 
			'down' => '25000', 
			'interest' => '5.0', 
			'tax' => '3125', 
			'pmi' => '0.5', 
			'years' => '30'
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget Title:', 'bplm_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Default Amount: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'purchase' ); ?>"><?php _e('Default Purchase Price:', 'bplm_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'purchase' ); ?>" name="<?php echo $this->get_field_name( 'purchase' ); ?>" value="<?php echo $instance['purchase']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'down' ); ?>"><?php _e('Default Down Payment:', 'bplm_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'down' ); ?>" name="<?php echo $this->get_field_name( 'down' ); ?>" value="<?php echo $instance['down']; ?>" style="width:100%;" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'interest' ); ?>"><?php _e('Default Interest Rate:', 'bplm_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'interest' ); ?>" name="<?php echo $this->get_field_name( 'interest' ); ?>" value="<?php echo $instance['interest']; ?>" style="width:100%;" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'tax' ); ?>"><?php _e('Default Property Tax (Per Year):', 'bplm_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'tax' ); ?>" name="<?php echo $this->get_field_name( 'tax' ); ?>" value="<?php echo $instance['tax']; ?>" style="width:100%;" />
		</p>
		
		<p style="display:none;">
			<label for="<?php echo $this->get_field_id( 'pmi' ); ?>"><?php _e('Default PMI:', 'bplm_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'pmi' ); ?>" name="<?php echo $this->get_field_name( 'pmi' ); ?>" value="<?php echo $instance['pmi']; ?>" style="width:100%;" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'years' ); ?>"><?php _e('Default Term (years):', 'bplm_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'years' ); ?>" name="<?php echo $this->get_field_name( 'years' ); ?>" value="<?php echo $instance['years']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}


//HELPERS

function date_dropdown($year_limit = 0){
	
    $html_output = '';

    /*months*/
    $html_output .= '<select name="bplm_options_startdate_month" class="bplm_options_startdate_month" >';
    $months = array("","January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        for ($month = 1; $month <= 12; $month++) {
        	if(date('n') == $month) { $selected = ' selected="selected"'; } else { $selected = '';}
            $html_output .= '<option value="'.$month.'"'.$selected.'>'.$months[$month].'</option>';
        }
    $html_output .= '</select>';

    /*years*/
    $html_output .= '<select name="bplm_options_startdate_year" class="bplm_options_startdate_year">';
        for ($year = date("Y"); $year <= (date("Y") + $year_limit); $year++) {
            $html_output .= '<option>'.$year.'</option>';
        }
    $html_output .= '</select>';

    return $html_output;
}

