<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class DSRPTV_Lead_Form extends DSRPTV_Form{

	protected static $_instance = null;

	public function __construct(){

		$this->type 	= 'lead';
		$this->fields 	= include DSRPTV_PATH.'/forms/lead.php';
		$this->url 		= 'https://dev.dsrptv.io/api/v1/leads/create';

		$this->hooks();

		parent::__construct();

	}


	/**
	 * Hooks
	*/
	public function hooks(){
		add_action( 'after_setup_theme', array( $this, 'create' ) );
		add_action( 'gform_form_settings_fields', array( $this, 'form_settings' ), 10, 2 );
		add_action( 'gform_pre_render', array( $this, 'autofill_form' ) );
	}



	public function create(){

		if( !isset( $_GET['dsprtv_createform'] ) || $_GET['dsprtv_createform'] !== $this->type ) return;

		$form_meta = array(
			'title' 		=> __( 'DSRPTV Lead', 'dsrptv' ),
			'description' 	=> __( 'Capture leads', 'dsrptv' ),
			'is_active' 	=> true,
			'fields' 		=> $this->fields,
			'button' 		=> array(
				'type' 	=> 'text',
				'text' 	=> __( 'Proceed to checkout', 'dsrptv' )
			),
			'dsrptv_type' 	=> 'lead'
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

		if( !isset( $form['dsrptv_type'] ) || $form['dsrptv_type'] !== 'lead' ) return $fields;

		$fields['form_basics']['fields'][] = array(
			'type' 			=> 'text',
			'name' 			=> 'funnel_id',
			'label' 		=> 'Funnel ID',
			'required' 		=> true,
			'description' 	=> __( 'The indentifier of the funnel to use', 'dsrptv' )
		);
		return $fields;
	}


	public function validate( $validation_result ){

	
		$form = $validation_result['form'];

		if( !isset( $form['dsrptv_type'] ) || $form['dsrptv_type'] !== $this->type ) return $validation_result;

		if( !isset( $form['funnel_id'] ) || !$form['funnel_id'] ){
			die('No funnel ID found, please add funnel id in form settings');
		}

		$formFields = $form['fields'];

		 $body = array(
			'key' 			=> dsrptv()->get_general_option('api-key'),
			'funnel_id' 	=> $form['funnel_id'],
			'ip_address' 	=> dsrptv()->getIP()
		);


		// Loop through form fields and fetch field values from POST if available.
		foreach ( $this->fields as $field ) {

			$fieldID = $field['id'];

			$formFieldID = 'input_' . $fieldID;

			if( isset( $_POST[ $formFieldID ] ) ){
				$body[ $field['dsrptvAPIParam'] ] = rgpost( $formFieldID );
			}

			if( isset( $field['inputs'] ) ){


				foreach( $field['inputs'] as $subinput ) {

					$subInputFieldID = $subinput['id'];

					$subInputFormFieldID = 'input_' . str_replace('.', '_', $subInputFieldID );

					if( isset( $_POST[ $subInputFormFieldID ] ) ){
						$body[ $subinput['dsrptvAPIParam'] ] = rgpost( $subInputFormFieldID );
					}
				}

			}

		}



		//Do certain checks and follow API data type before sending
		if( isset( $body['country'] ) ){

			$countryCode = esc_html( GF_Fields::get( 'address' )->get_country_code($body['country']) );

			//if country name is passed, convert to country code
			$body['country'] = $countryCode ? $countryCode : $body['country'];

			//converting state name to state code
			if( isset( $body['state'] ) ){

				/*if( $body['country'] === 'US' ){
					$body['state'] = GF_Fields::get( 'address' )->get_us_state_code( $body['state'] );
				}*/
				if( $body['country'] === 'CA' ){
					$body['state'] = dsrptv_get_canadian_state_code( $body['state'] );
				}

			}

		}


		$result = $this->post_data_curl( $body );

	    $validation_result['form'] = $form;

	    return $validation_result;
	}


	/* If user already has created lead, fetch the values from db and prefill form */
	public function autofill_form( $form ){

		if( !isset( $form['dsrptv_type'] ) || $form['dsrptv_type'] !== $this->type ) return $form;

		$leadData = $this->get_session_data();


		//Loop form fields
		foreach ( $form['fields'] as $field ) {

			$apiParam = $field->dsrptvAPIParam;

			//If saved lead has this form field, assign value to field
			if( isset(  $leadData->{$apiParam} ) ){
				$field->defaultValue = esc_html(  $leadData->{$apiParam} );
			}

			//Assign address values
			if( $field->type === 'address' ){

				$addressType = isset( $field->dsrptvAdType ) ? esc_html( $field->dsrptvAdType ) : 'billing';

				if( isset( $leadData->addresses ) ){

					//Loop addresses ( billing/shipping )
					foreach ( $leadData->addresses as $savedAddress ) {

						if( $savedAddress->type !== $addressType ) continue;

						//Assign address value
						foreach ( $field->inputs as $index => $input ) {

							$apiParam = $input['dsrptvAPIParam'];

							//Do not prefill country/state
							if( $apiParam === 'country' ) continue;

							if( isset( $savedAddress->{$apiParam} ) ){
								($field->inputs)[ $index ]['defaultValue'] = esc_html( $savedAddress->{$apiParam} );
							}
				
						}

					}

				}
			}


		}


		return $form;
	}

}

function dsrptv_lead_form(){
	return DSRPTV_Lead_Form::get_instance();
}

dsrptv_lead_form();


?>