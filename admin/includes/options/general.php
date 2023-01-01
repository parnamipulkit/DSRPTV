<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$option_name = 'dsrptv-general-options';

$settings = array(

	array(
		'type' 			=> 'section',
		'callback' 		=> 'section',
		'id' 			=> 'main-section',
		'title' 		=> '',
	),

	array(
		'type' 			=> 'setting',
		'callback' 		=> 'text',
		'section' 		=> 'main-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'api-key',
		'title' 		=> 'API key',
	)

);

return $settings;

?>
