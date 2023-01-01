<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class DSRPTV_Form{

	public $type, $url, $fields, $sessionRow, $sessionData, $validation_result;

	public $errors = array();

	public static function get_instance(){
		if ( is_null( static::$_instance ) ) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}


	public function __construct(){
		add_filter( 'gform_validation_message', array( $this, 'display_form_errors' ), 10, 2 );
		add_filter( 'gform_validation', array( $this, 'validation' ), 10, 1 );
		add_filter( 'gform_submit_button', array( $this, 'form_token' ), 10, 2 );
	}


	public function form_token( $html, $form ){
		if( !$this->is_my_type( $form ) ) return $html;
		$form_token = isset( $_POST['dsrptv_form_token'] ) ? (int) $_POST['dsrptv_form_token'] : rand(1,999);
		$html .= '<input type="hidden" name="dsrptv_form_token" value="'.$form_token.'">';
		return $html;
	}


	public function is_my_type( $form ){

		return ( isset( $form['dsrptv_type'] ) && $form['dsrptv_type'] === $this->type );
	}


	public function validation( $validation_result ){


		$form = $validation_result['form'];

		if( !$this->is_my_type( $form ) ) return $validation_result;

		$validation_result = $this->validate( $validation_result );


		if( !empty( $this->errors ) ){
			$validation_result['is_valid'] 	= false;
	    	$validation_result['form'] 		= $this->add_errors_to_form( $form );
		}

		return $validation_result;

	}


	public function display_form_errors( $message, $form ){

		if( $form['dsrptv_type'] !== $this->type || empty( $this->errors ) ) return $message;

		foreach ( $this->errors as $index => $error ) {
			if( is_array( $error ) ){
				$this->errors[$index] = implode( '<br>' , $error );
			}
		}



		$message .= implode( '<br>' , $this->errors );

		return $message;

	}


	public function update_session_value( $value, $form_id = '' ){

		$value = is_array( $value ) || is_object( $value ) ? json_encode( $value, true ) : $value;

		dsrptv_db()->update_row(

			array(
				'session_value' => $value,
				'session_type' 	=> $this->type,
				'form_id' 		=> $form_id
			)

		);

	}


	public function get_session_data(){

		if( isset( $this->sessionData ) ){
			return $this->sessionData;
		}

		if( !isset( $this->sessionRow ) ){
			$this->sessionRow = dsrptv_db()->get_row_by_session_type( $this->type );
		}

		$this->sessionData = !empty( $this->sessionRow ) ? json_decode( $this->sessionRow->session_value ) : array();

		return $this->sessionData;

	}


	public function get_session_data_property( $property ){
		return isset( $this->get_session_data()->{$property} ) ? $this->get_session_data()->{$property} : '';
	}


	public function post_data_curl( $body, $form_id = '' ){

		print_r($body);
		die();

		$postText = wp_json_encode( $body );

		$curl = curl_init();

	    curl_setopt($curl, CURLOPT_URL, $this->url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $postText);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl,CURLOPT_HTTPHEADER , array(
	        "accept: application/json",
	        "content-type: application/json",
	    ));

	    $result = json_decode( curl_exec($curl), true );


	    if( isset( $result['errors'] ) ){
	    	$this->errors = $result['errors'];
	    }

	    if( isset( $result['reason'] ) ){
	    	$this->errors[] = $result['reason'];
	    }

	    if( isset( $result['client_id'] ) || ( isset( $result['status'] ) && $result['status'] === 'complete' ) ){
	    	$this->update_session_value( $result, $form_id );
	    }


	    return $result;

	}


	public function add_errors_to_form( $form ){

		if( empty( $this->errors ) ) return;

		$errors = $this->errors;

    	foreach ( $form['fields'] as $fieldObj ) {

    		$apiParam 	= $fieldObj->dsrptvAPIParam;
    		$fieldID 	= $fieldObj->id;
    		$notices 	= '';
    			
    		if( isset( $errors[ $apiParam ] ) ){
    			$notices = implode('<br>', $errors[ $apiParam ] );
    			unset($errors[ $apiParam ]);
    		}

    		if( isset( $fieldObj->inputs ) && !empty( $fieldObj->inputs ) ){

    			foreach ( $fieldObj->inputs as $subinput ) {

    				$subApiParam 	= $subinput['dsrptvAPIParam'];
    				$subFieldID 	= $subinput['id'];

    				if( isset( $errors[ $subApiParam ] ) ){
		    			$notices  = implode('<br>', $errors[ $subApiParam ] );
		    			unset($errors[ $subApiParam ]);
		    		}
    			}

    		}

    		if( $notices ){
    			$fieldObj->failed_validation = true;
    			$fieldObj->validation_message = $notices;
    		}	

    	}

    	$this->errors = $errors;

    	return $form;
	}


}