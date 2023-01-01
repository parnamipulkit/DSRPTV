<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class DSRPTV_Order_Form extends DSRPTV_Form {

	protected static $_instance = null;

	public function __construct(){

		$this->type 	= 'order';
		$this->fields 	= include DSRPTV_PATH.'/forms/order.php';
		$this->url 		= 'https://dev.dsrptv.io/api/v1/orders/create';

		$this->hooks();

		parent::__construct();

	}


	/**
	 * Hooks
	*/
	public function hooks(){
		add_action( 'after_setup_theme', array( $this, 'create' ) );
		add_action( 'gform_form_settings_fields', array( $this, 'form_settings' ), 10, 2 );
		add_filter( 'gform_tooltips', array( $this, 'add_tooltip' ) );
		add_filter( 'gform_confirmation', array( $this, 'add_query_args_to_redirection' ), 10, 3 );
	}



	public function create(){

		if( !isset( $_GET['dsprtv_createform'] ) || $_GET['dsprtv_createform'] !== $this->type ) return;

		if( empty( dsrptv_forms()->get_forms( 'lead' ) ) ){
			_e( '<h1>Please create a lead form first</h1>', 'dsrptv' );
			die();
			return;
		}

		$form_meta = array(
			'title' 		=> __( 'DSRPTV Order', 'dsrptv' ),
			'description' 	=> __( 'Capture Orders', 'dsrptv' ),
			'is_active' 	=> true,
			'fields' 		=> $this->fields,
			'button' 		=> array(
				'type' 	=> 'text',
				'text' 	=> __( 'Place order', 'dsrptv' )
			),
			'dsrptv_type' 	=> 'order'
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


	public function form_settings( $fields, $form ){

		if( !isset( $form['dsrptv_type'] ) || $form['dsrptv_type'] !== 'order' ) return $fields;

		$selectLeadField = array(
			'type' 			=> 'select',
			'name' 			=> 'lead_form_id',
			'label' 		=> 'Lead Form',
			'required' 		=> true,
			'tooltip' 		=> gform_tooltip( 'leadFormID', '', true ),
		);


		$forms = dsrptv_forms()->get_forms( 'lead' );

		foreach ( $forms as $leadForm ) {

			$selectLeadField['choices'][] = array(
				'label' 	=> $leadForm['id']. ' : '. $leadForm['title'],
				'value' 	=> $leadForm['id']
			);

		}

		//Setting first lead form id as default value
		$selectLeadField['default_value'] = $selectLeadField['choices'][0]['value'];

		$fields['form_basics']['fields'][] = $selectLeadField;


		return $fields;
	}


	public function add_tooltip( $tooltips ){
		$tooltips['leadFormID'] = esc_html__( 'Link to Lead Form', 'dsrptv' );
		return $tooltips;
	}



	public function validate( $validation_result ){

		$form = $validation_result['form'];

		if( !isset( $form['dsrptv_type'] ) || $form['dsrptv_type'] !== $this->type ) return $validation_result;

		$leadID 	= dsrptv_lead_form()->get_session_data_property('id');
		$funnelID 	= dsrptv_lead_form()->get_session_data_property('funnel_id');

		if( !$leadID || !$funnelID ){
			//redirect to lead form if lead form not filled
			die('Please fill lead form first');
			return $validation_result;
		}


		$formFields = $form['fields'];

		$body = array(
			'key' 				=> dsrptv()->get_general_option('api-key'),
			'ip_address' 		=> dsrptv()->getIP(),
			'lead_id'			=> $leadID,
			'funnel_id' 		=> $funnelID,
			'payment_source' 	=> 'card'
		);


		// Loop through form fields and fetch field values from POST if available.
		foreach ( $formFields as $field ) {

			$fieldID = $field->id;

			if( !empty( $field->inputs ) ){


				foreach( $field->inputs as $subinput ) {

					$subInputID = $subinput['id'];

					//clipping month and year ID
					if( $clipPos = strpos( $subInputID, '_' ) ){
						$subInputFormFieldID = 'input_'. str_replace( '.', '_', substr( $subInputID , 0, $clipPos ) );
					}
					else{
						$subInputFormFieldID = 'input_'. str_replace( '.', '_',  $subInputID );
					}

					
					//Fetch month year value
					if( strpos( $subInputID , 'month' ) ){
						$value = (int) rgpost( $subInputFormFieldID )[0];
					}
					elseif ( strpos( $subInputID , 'year' ) ) {
						$value = (int) rgpost( $subInputFormFieldID )[1];
					}
					else{
						$value = esc_html( rgpost( $subInputFormFieldID ) );
					}
		
					$body[ $subinput['dsrptvAPIParam'] ] = $value;
					
				}

			}
			else{

				$value = rgpost( 'input_'. $fieldID );

				if( strpos( 'products', $field->dsrptvAPIParam ) >= 0){
					$body[ 'products' ][] = $value;
				}
				else{
					$body[ $field->dsrptvAPIParam ] = $value;
				}		
			}
		}


		//clean up data before passing to API
		$body['card_exp_month'] = sprintf( "%02d", $body['card_exp_month'] );
		$body['card_exp_year'] = substr( $body['card_exp_year'], -2 );


		$result = $this->post_data_curl( $body );

	    $validation_result['form'] = $form;

	    return $validation_result;

	}


	public function add_query_args_to_redirection( $confirmation, $form, $entry ){

		if ( ! is_array( $confirmation ) || empty( $confirmation['redirect'] ) ) {
			return $confirmation;
		}

		$confirmation['redirect'] = add_query_arg( array( 'your_param_name' => $values ), $confirmation['redirect'] );

		return $confirmation;

	}


}

function dsrptv_order_form(){
	return DSRPTV_Order_Form::get_instance();
}

dsrptv_order_form();


?>