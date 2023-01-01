<?php
/**
* Plugin Name: DSRPTV
* Plugin URI: https://dsrptv.io/
* Author: dsrptv
* Version: 1.0
* Text Domain: dsrptv
* Domain Path: /languages
* Author URI: https://dsrptv.io/
* Description:
* Tags: dsrptv
*/


//Exit if accessed directly
if(!defined('ABSPATH')){
	return;
}

define("DSRPTV_PATH",plugin_dir_path(__FILE__)); // Plugin path
define("DSRPTV_URL",plugins_url('',__FILE__)); // plugin url
define("DSRPTV_PLUGIN_BASENAME",plugin_basename( __FILE__ ));
define("DSRPTV_VERSION","1.0"); //Plugin version





function dsrptv_init(){
	

	do_action('dsrptv_before_plugin_activation');

	if( !class_exists('GFAPI') ){
		return; // exit if gravity form is not active
	}

	if ( ! class_exists( 'DSRPTV' ) ) {
		require DSRPTV_PATH.'/includes/class-dsrptv.php';
	}

	dsrptv();

	
}
add_action( 'plugins_loaded','dsrptv_init', 20 );

function dsrptv(){
	return DSRPTV::get_instance();
}



add_action( 'gform_field_advanced_settings', 'my_advanced_settings', 10, 2 );
function my_advanced_settings( $position, $form_id ) {

   if ($position == 50) {  // right after Admin Field Label
		?>

		<li class="dsrptv_api_param_input_setting field_setting">
			<label for="field_admin_label">
				<?php _e('DSRPTV API Param', 'dsrptv'); ?>
				<?php gform_tooltip("form_field_dsrptv_api_param_input") ?>
			</label>
			<input type="text" id="field_dsrptv_api_param_input" onchange="SetFieldProperty('dsrptvAPIParam', this.value);" class="fieldwidth-3" />
		</li>

		<?php
	}	
}


add_action( 'gform_field_standard_settings', 'my_standard_settings', 10, 2 );
function my_standard_settings( $position, $form_id ) {
  
    //create settings on position 25 (right after Field Label)
    if ( $position == 25 ) {
        ?>
        <li class="dsrptv_product_id_input_setting field_setting">
			<label for="field_admin_label">
				<?php _e('Product ID', 'dsrptv'); ?>
				<?php gform_tooltip("form_field_dsrptv_product_id_input") ?>
			</label>
			<input type="number" id="field_dsrptv_product_id_input" onchange="SetFieldProperty('dsrptvProductID', this.value);" class="fieldwidth-3" />
		</li>

		 <li class="dsrptv_product_qty_input_setting field_setting">
			<label for="field_admin_label">
				<?php _e('Product Quantity', 'dsrptv'); ?>
				<?php gform_tooltip("form_field_dsrptv_product_qty_input") ?>
			</label>
			<input type="number" id="field_dsrptv_product_qty_input" onchange="SetFieldProperty('dsrptvProductQty', this.value);" class="fieldwidth-3" />
		</li>

        <?php
    }
}

add_action('gform_editor_js', function() {
	?>
	<script type="text/javascript">
		// Add our setting to these field types

		jQuery.each( fieldSettings, function( key , value){
			fieldSettings[ key ] = value + ', .dsrptv_api_param_input_setting';
		} );

		fieldSettings.dsrptv_product += ', .dsrptv_product_id_input_setting';
		fieldSettings.dsrptv_product += ', .dsrptv_product_qty_input_setting';
		
 
		// Make sure our field gets populated with its saved value
		jQuery(document).on("gform_load_field_settings", function(event, field, form) {
	        	jQuery("#field_dsrptv_api_param_input").val(field["dsrptvAPIParam"] || '' );
	        	jQuery("#field_dsrptv_product_id_input").val(field["dsrptvProductID"] || '' );
	        	jQuery("#field_dsrptv_product_qty_input").val(field["dsrptvProductQty"] );
	    	});
	</script>
	<?php
});

//Filter to add a new tooltip
add_filter('gform_tooltips', function($tooltips) {
	$tooltips['form_field_dsrptv_api_param_input'] = __('<h6>API Parameter</h6>This field is used to map with your DSRPTV field', 'txtdomain');
	return $tooltips;
});


add_action( 'gform_editor_js_set_default_values', function(){
	?>
		case 'dsrptv_product' :
		if ( ! field.label ) {
			field.label = '<?php _e( 'Product Settings', 'dsrptv' ); ?>';
		}
		if ( ! field.dsrptvAPIParam ) {
			field.dsrptvAPIParam = '<?php echo 'products'; ?>';
		}
		break;
	<?php

});


add_filter( 'gform_countries', function () {
    $countries = GF_Fields::get( 'address' )->get_default_countries();
    asort( $countries );
 
    return $countries;
} );



add_filter( 'gform_us_states', function( $states ){

	$new_states = array();
	foreach ( $states as $state ) {
		$new_states[ GF_Fields::get( 'address' )->get_us_state_code( $state ) ] = $state;
	}

	return $new_states;

} );


function dsrptv_get_canadian_state_code( $state_name ){

	$state_name = ucfirst( strtolower( $state_name ) );

	$canadian_states = array( 
	    "BC" => "British Columbia", 
	    "ON" => "Ontario", 
	    "NL" => "Newfoundland and Labrador", 
	    "NS" => "Nova Scotia", 
	    "PE" => "Prince Edward Island", 
	    "NB" => "New Brunswick", 
	    "QC" => "Quebec", 
	    "MB" => "Manitoba", 
	    "SK" => "Saskatchewan", 
	    "AB" => "Alberta", 
	    "NT" => "Northwest Territories", 
	    "NU" => "Nunavut",
	    "YT" => "Yukon Territory"
	);

	$byname = array_flip( $canadian_states );

	return isset( $byname[ $state_name ] ) ? $byname[ $state_name ] : '';

}


//Enable credit card field
add_filter( 'gform_enable_credit_card_field', '__return_true', 11 );