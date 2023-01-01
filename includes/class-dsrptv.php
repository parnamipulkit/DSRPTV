<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class DSRPTV{

	protected static $_instance = null;

	public $updated = false;

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	
	public function __construct(){
		$this->includes();
		$this->hooks();
	}

	/**
	 * File Includes
	*/
	public function includes(){

		require_once DSRPTV_PATH.'includes/class-dsrptv-db.php';
		require_once DSRPTV_PATH.'includes/class-dsrptv-geolocation.php';
		require_once DSRPTV_PATH.'includes/functions.php';
		require_once DSRPTV_PATH.'/includes/class-dsrptv-gf-product.php';


		if($this->is_request('frontend')){

		}
		
		if($this->is_request('admin')) {
			require_once DSRPTV_PATH.'/admin/class-dsrptv-admin.php';
		}

		require_once DSRPTV_PATH.'includes/class-dsrptv-forms.php';
		require_once DSRPTV_PATH.'includes/class-dsrptv-form.php';
		require_once DSRPTV_PATH.'includes/class-dsrptv-lead-form.php';
		require_once DSRPTV_PATH.'includes/class-dsrptv-order-form.php';
		require_once DSRPTV_PATH.'includes/class-dsrptv-upsell-form.php';
	}


	/**
	 * Hooks
	*/
	public function hooks(){
		$this->on_install();
	}


	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}


	/**
	* On install
	*/
	public function on_install(){

		$version_option = 'dsrptv-version';
		$db_version 	= get_option( $version_option );

		//If first time installed
		if( !$db_version ){
			
		}

		if( version_compare( $db_version, DSRPTV_VERSION, '<') ){
			//Update to current version
			update_option( $version_option, DSRPTV_VERSION);
			$this->updated = true;
		}
	}


	public function get_option( $key, $subkey = '' ){
		$option = get_option( $key );
		if( $subkey ){
			if( !isset( $option[ $subkey ] ) ) return;
			return !is_array( $option[ $subkey ] ) ? esc_attr( $option[ $subkey ] ) : $option[ $subkey ];
		}
		else{
			return $option;
		}
	}


	public function get_general_option( $subkey = '' ){
		return $this->get_option( 'dsrptv-general-options', $subkey );
	}


	public function getIP(){
		return DSRPTV_Geolocation::get_ip_address();;  
	}

}

?>