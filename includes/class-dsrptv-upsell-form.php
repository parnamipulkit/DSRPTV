<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class DSRPTV_Upsell_Form extends DSRPTV_Form{

	protected static $_instance = null;

	public function __construct(){

		$this->type 	= 'upsell';
		$this->fields 	= include DSRPTV_PATH.'/forms/upsell.php';
		$this->url 		= 'https://dev.dsrptv.io/api/v1/upsells/create';

		$this->hooks();

		parent::__construct();

	}


	/**
	 * Hooks
	*/
	public function hooks(){
		add_action( 'after_setup_theme', array( $this, 'create' ) );
	}



	public function create(){

		if( !isset( $_GET['dsprtv_createform'] ) || $_GET['dsprtv_createform'] !== $this->type ) return;

		$form_meta = array(
			'title' 		=> __( 'DSRPTV Upsell', 'dsrptv' ),
			'description' 	=> __( 'One click upsell', 'dsrptv' ),
			'is_active' 	=> true,
			'fields' 		=> $this->fields,
			'button' 		=> array(
				'type' 	=> 'text',
				'text' 	=> __( 'I want this', 'dsrptv' )
			),
			'dsrptv_type' 	=> 'upsell'
		);

		$form_id = GFAPI::add_form($form_meta);
		

		wp_safe_redirect(

			add_query_arg( array(
				'page' 	=> 'gf_edit_forms',
				'view' 	=> 'settings',
				'id' 	=> $form_id
			), get_admin_url() )

		);
		
	}


	public function validate( $validation_result ){

	
		$form = $validation_result['form'];

		if( !isset( $form['dsrptv_type'] ) || $form['dsrptv_type'] !== $this->type ) return $validation_result;

		$formFields = $form['fields'];

		$order_id = isset( $_GET['id'] ) ? $_GET['id'] : dsrptv_order_form()->get_session_data_property( 'id' );

		if( !$order_id ){
			die('no order id found');
		}

		$body = array(
			'key' 		=> dsrptv()->get_general_option('api-key'),
			'order_id' 	=> $order_id
		);



		// Loop through form fields and fetch field values from POST if available.
		foreach ( $formFields as $field ) {

			if( !$field->dsrptvAPIParam ) continue;

			$fieldID = $field->id;

			$value = rgpost( 'input_'. $fieldID );

			if( strpos( 'product', $field->dsrptvAPIParam ) >= 0 ){
				$body[ 'product_id' ] = $value['id'];
				$body[ 'product_qty' ] = (int) $value['qty'];
			}
			else{
				$body[ $field->dsrptvAPIParam ] = $value;
			}		
		
		}



		$result = $this->post_data_curl( $body );

	    $validation_result['form'] = $form;

	    return $validation_result;
	}

}


function dsrptv_upsell_form(){
	return DSRPTV_Upsell_Form::get_instance();
}

dsrptv_upsell_form();


?>