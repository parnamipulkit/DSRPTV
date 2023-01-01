<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class DSRPTV_Forms{

	protected static $_instance = null;


	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	
	public function __construct(){

		$this->hooks();

	}


	/**
	 * Hooks
	*/
	public function hooks(){
		
	}


	public function get_forms( $form_type = '', $form_args = array() ){

		$forms = call_user_func_array( array( 'GFAPI', 'get_forms' ) , $form_args );

		if( $form_type ){

			$formsWithType = array();

			foreach ( $forms as $form ) {
				if( isset( $form['dsrptv_type'] ) && $form['dsrptv_type'] === $form_type  ){
					$formsWithType[] = $form;
				}
			}

			return $formsWithType;
		}

		return $forms;

	}



	public function get_field_by_api_param( $param ){

		foreach ( $this->fields as $field ) {


			if( isset( $field['apiparam'] ) && $field['apiparam'] === $param ){
				return $field;
			}

			//If has sub inputs
			if( isset( $field['inputs'] ) ){
				foreach ( $field['inputs'] as $subinput ) {
					if( isset( $subinput['apiparam'] ) && $subinput['apiparam'] === $param ){
						return $subinput;
					}
				}
			}

		}

	}



}

function dsrptv_forms(){
	return DSRPTV_Forms::get_instance();
}

dsrptv_forms();

?>