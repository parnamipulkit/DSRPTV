<?php

class DSRPTV_DB{

	protected static $_instance = null;

	public $sessions_table;
	
	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function hooks(){
		add_action( 'init', array( $this, 'create_table' ), 15 );
	}

	public function __construct(){

		global $wpdb;

		$this->sessions_table	= $wpdb->prefix . 'dsrptv_sessions';

		$this->hooks();
	}


	private function get_placeholder( $input ){

		$type = gettype( $input );

		if( $type === "integer" ){
			return '%d';
		}elseif( $type === "float" ){
			return '%f';
		}
		else{
			return '%s';
		}

	}


	public function get_rows( $args = array(), $output = OBJECT ){
		
		global $wpdb;

		$defaults = array(
			'limit' 		=> -1,
			'offset' 		=> 0,
			'where' 		=> array(),
			'meta_query' 	=> array(),
			'relation' 		=> 'AND'
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		$values = array();

		$query = "SELECT * FROM {$this->sessions_table}";

		$query .= " WHERE";

		if( !empty( $where ) ){
			
			$i = 0;
			foreach ( $where as $index => $whereData ) {
				if( $i > 0 ){
					$query .= " {$relation}";
				}
				$query .= " {$whereData['key']}";
				$compare = isset( $whereData['compare'] ) ? $whereData['compare'] : '=';
				$query .= " {$compare}";
				$query .= " {$this->get_placeholder( $whereData['value'] )}";
				$values[] = $whereData['value'];
				$i++;
			}
		}
		else{
			$query .= " 1 = %d";
			$values[] = 1;
		}


		if( $limit !== -1 ){

			$query .= " LIMIT {$this->get_placeholder( $limit )}";
			$values[] = $limit;

			if( $offset ){
				$query .= " OFFSET {$this->get_placeholder( $offset )}";
				$values[] = $offset;
			}

		}


		$results = $wpdb->get_results( 
			$wpdb->prepare(
				$query,
				$values
			),
			$output 
		);


		return $results;
		

	}



	public function get_rows_by_session_key( $session_key, $session_type = '', $args = array(), $output = OBJECT ){
		
		$defaults = array(
			'limit' 	=> -1,
			'offset' 	=> 0
		);

		$args = wp_parse_args( $args, $defaults );

		
		$args['where'][] = array(
			'key' 		=> 'session_key',
			'value' 	=> $session_key,
			'compare' 	=> '='
		);
		


		if( $session_type ){
			$args['where'][] = array(
				'key' 		=> 'session_type',
				'value' 	=> $session_type,
				'compare' 	=> '='
			);
		}


		$rows = $this->get_rows( $args, $output );
		
		return $rows;
	}


	public function get_row_by_session_type( $session_type = 'lead', $session_key = '', $args = array() ){
		
		$defaults = array(
			'limit' 	=> -1,
			'offset' 	=> 0
		);

		if( !$session_key ){
			$session_key = $this->get_current_user_session_key();
		}

		$args = wp_parse_args( $args, $defaults );


		$args['where'][] = array(
			'key' 		=> 'session_type',
			'value' 	=> $session_type,
			'compare' 	=> '='
		);

		$args['where'][] = array(
			'key' 		=> 'session_key',
			'value' 	=> $session_key,
			'compare' 	=> '='
		);
	
	

		$rows = $this->get_rows( $args );

		if( !empty( $rows ) ){
			return $rows[0];
		}
		
		return $rows;
	}





	/* Inserts new Row if does not exist
	   Updates if row duplication is not allowed
	*/
	public function update_row( $data = array(), $where= array() ){

		global $wpdb;

		$defaults = array(
			'session_key' 		=> $this->get_current_user_session_key(),
			'session_value' 	=> null,
			'session_type'  	=> 'lead',
			'session_expiry' 	=> strtotime('+30 days'),
		);

		$data = wp_parse_args( $data, $defaults );

		if( $data['session_key'] ){

			$session_key_exists =  $this->get_rows_by_session_key( $data['session_key'], $data['session_type'] );
			
		}

		if( !empty( $session_key_exists ) ){

			$session_id = $session_key_exists[0]->session_id;

			$action = $wpdb->update( $this->sessions_table, $data, array(
				'session_id' => $session_id
			) );
		}
		else{
			$action = $wpdb->insert( $this->sessions_table, $data );
			$session_id = $wpdb->insert_id;
		}

		if( false === $action ){
			return new WP_Error( $wpdb->last_error );
		}
		
		return true;

	}


	public function update_meta( $row_id, $meta_key, $meta_value ){

		update_metadata( 'x_people', $row_id, $meta_key, $meta_value );

	}


	public function get_meta( $row_id, $meta_key = '', $single = true ){
		$meta_value = get_metadata( 'x_people', $row_id, $meta_key, $single  );
		if( !$meta_key && is_array( $meta_value ) ){
			foreach ( $meta_value as $key => $value ) {
				$meta_value[ $key ] = maybe_unserialize( $value[0] );
			}
		}
		return $meta_value;
	}



	public function get_count( $type = 'client' ){

		global $wpdb;

		$query = "
		SELECT COUNT({$this->sessions_table}.ID) AS total FROM {$this->sessions_table} WHERE type = %s
		";

		$values = array(
			$type
		);


		$results = $wpdb->get_row(
			$wpdb->prepare( 
				$query,
				$values
			),
			ARRAY_A
		);

		return $results['total'];

	}


	public function create_table(){

		global $wpdb;

		$version_option = 'dsrptv-session-db-version';

		$db_version 	= get_option( $version_option );

		//if( version_compare( $db_version, '1.0', '=' ) ) return;

		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->sessions_table} (
			session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  			session_key char(32) NOT NULL,
			session_value longtext NOT NULL,
			session_type char(32),
			session_expiry BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY  (session_id)
			) $charset_collate;";

		dbDelta( $sql );

		update_option( $version_option, '1.0' );

	}


	public function get_current_user_session_key(){

		if( isset( $_COOKIE['dsrptv_user_key'] ) ){
			return sanitize_text_field( $_COOKIE['dsrptv_user_key'] );
		}

		//Create key
		$key = uniqid();
		setcookie( 'dsrptv_user_key', $key, 0 , '/' );

		return $key;

	}

}

function dsrptv_db(){
	return DSRPTV_DB::get_instance();
}

dsrptv_db();