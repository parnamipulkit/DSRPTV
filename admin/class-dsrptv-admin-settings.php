<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class DSRPTV_Admin_Settings{

	protected static $_instance = null;

	public static $callbacks;

	public $all_options = array();

	public $tabs = array();

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){

		self::$callbacks = include DSRPTV_PATH.'admin/includes/dsrptv-settings-callbacks.php';

		$this->set_tabs(); // Set tabs

		add_action( 'admin_init', array( $this, 'set_default_options' ) );

		add_action('admin_menu',array($this,'add_menu_page'));

		add_action('admin_enqueue_scripts',array($this,'enqueue_scripts'));

		add_action('admin_init',array($this,'display_all_settings'));

		add_filter( 'plugin_action_links_' . DSRPTV_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );


	}


	public function set_tabs(){

		if( !empty( $this->tabs ) ){
			return $this->tabs;
		}

		$this->tabs = array(
			'general' 	=> __( 'General','dsrptv' ),
		);

	}


	public function set_default_options(){

		$default_options = $this->get_all_options();
		if( empty( $default_options ) ) return;

		foreach ($default_options as $option_name => $settings ) {

			//Return current option value from the database
			$option_value = (array) get_option($option_name) ;

			foreach ($settings as $setting) {	
				if( $setting['type'] === 'setting' && isset( $setting['default'] ) && isset( $setting['id'] ) && !isset( $option_value[$setting['id']]) ){
					$option_value[$setting['id']] = $setting['default'];
				}
			}



			update_option( $option_name, $option_value );
			
		}

	}


	public function get_all_options(){

		if( !empty( $this->all_options ) ){
			return $this->all_options;
		}

		foreach ($this->tabs as $key => $title) {

			$path = DSRPTV_PATH.'admin/includes/options/'.$key.'.php'; 

			if( file_exists( $path ) ){
				$this->all_options[ 'dsrptv-'.$key.'-options' ] = include $path;
			}
		}

		return $this->all_options;
	}


	public function enqueue_scripts($hook) {

		//Enqueue Styles only on plugin settings page
		if( $hook !== 'toplevel_page_dsrptv' ){
			return;
		}
		
		/*wp_enqueue_media(); // media gallery
		wp_enqueue_style('wp-color-picker');

		wp_enqueue_style( 'xoo-ml-admin-style', XOO_ML_URL . '/admin/assets/css/xoo-ml-admin-style.css', array(), XOO_ML_VERSION, 'all' );

		wp_enqueue_script( 'xoo-ml-admin-js', XOO_ML_URL . '/admin/assets/js/xoo-ml-admin-js.js', array( 'jquery','wp-color-picker'), XOO_ML_VERSION, false );

		wp_localize_script('xoo-ml-admin-js','xoo_ml_admin_localize',array(
			'adminurl'  => admin_url().'admin-ajax.php',
		));*/

	}


	public function add_menu_page(){

	
		add_submenu_page( 
			'options-general.php',
			'Settings', //Page Title
			'DSRPTV', // Menu Titlle
			'manage_options',// capability
			'dsrptv', // Menu Slug
			array($this,'menu_page_callback') // callback
		);
		
	}

	public function menu_page_callback(){

		$args = array(
			'tabs' 					=> $this->tabs,
		);

		extract($args);

		include DSRPTV_PATH.'/admin/templates/dsrptv-admin-settings-display.php';

	}


	public function display_all_settings(){

		$default_options = $this->get_all_options();

		foreach ( $default_options as $option_name => $settings ) {
			$this->generate_settings( $settings, $option_name, $option_name, $option_name);
		}
	}


	public function generate_settings( $setting_fields, $page, $group, $option_name ){

		if(empty($setting_fields)){
			return;
		}

		foreach ($setting_fields as $field) {

			//Arguments for add_settings_field
			$args = $field;

			if( !isset($field['id']) || !isset($field['type']) || !isset($field['callback']) ) {
				continue;
			}

			//Check for callback functions
			if( is_callable( array( self::$callbacks, $field['callback'] ) ) ){
				$callback = array( self::$callbacks, $field['callback'] );
			}
			elseif ( is_callable( $field['callback'] ) ) {
				$callback = $field['callback'];
			}
			else{
				continue;
			}

			$title = isset($field['title']) ? $field['title'] : null;

			//Add a section
			if( $field['type'] === 'section' ){

				$section_args = array(
					'id' 		=> $field['id'],
					'title' 	=> $title,
					'callback' 	=> $callback,
					'page' 		=>$page
				);

				$section_args = apply_filters( 'dsrptv_section_args', $section_args );
				call_user_func_array( 'add_settings_section', array_values( $section_args ) );

			}

			//Add a setting field
			elseif( $field['type'] === 'setting' ){

				$setting_args = array(
					'id' 		=> $field['id'],
					'title' 	=> $title,
					'callback' 	=> $callback,
					'page' 		=> $page,
					'section' 	=> $field['section'],
					'args' 		=> $args
				);

				$setting_args = apply_filters( 'dsrptv_setting_args', $setting_args );
				
				call_user_func_array( 'add_settings_field', array_values( $setting_args ) );

			}

		}

		register_setting( $group, $option_name);

	}


	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=dsrptv' ) . '" target="_blank">' . __('Settings', 'dsrptv' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}


}

function dsrptv_admin_settings(){
	return DSRPTV_Admin_Settings::get_instance();
}

dsrptv_admin_settings();

?>