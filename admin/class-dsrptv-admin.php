<?php

class DSRPTV_Admin{

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


	public function includes(){
		require_once DSRPTV_PATH.'admin/class-dsrptv-admin-settings.php';
	}

	public function hooks(){
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	public function enqueue_scripts( $hook ){

		//Enqueue Styles only on plugin settings page
		if( $hook !== 'toplevel_page_gf_edit_forms' ){
			return;
		}


		wp_enqueue_style( 'dsrptv-admin-style', DSRPTV_URL . '/admin/assets/dsrptv-admin-style.css', array(), DSRPTV_VERSION, 'all' );
		

		wp_enqueue_script( 'dsrptv-admin-js', DSRPTV_URL . '/admin/assets/dsrptv-admin.js', array( 'jquery' ), DSRPTV_VERSION, false );

		wp_localize_script('dsrptv-admin-js','dsrptv_admin_localize',array(
			'adminurl'  => admin_url().'admin-ajax.php',
			'forms' 	=> array(
				'upsell' => array(
					'text' 	=> __( 'DSRPTV Add upsell form', 'xt' ),
					'url' 	=> add_query_arg( 'dsprtv_createform', 'upsell' ),
				),
				'order' => array(
					'text' 	=> __( 'DSRPTV Add order form', 'xt' ),
					'url' 	=> add_query_arg( 'dsprtv_createform', 'order' ),
				),
				'lead' => array(
					'text' 	=> __( 'DSRPTV Add lead form', 'xt' ),
					'url' 	=> add_query_arg( 'dsprtv_createform', 'lead' ),
				),
			)
		));
	}

}


function dsrptv_admin(){
	return DSRPTV_Admin::get_instance();
}

dsrptv_admin();