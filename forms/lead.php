<?php

$fields = array(


	array(

		'id' 					=> 1,
		'type' 					=> 'text',
		'label' 				=> __( 'First Name', 'dsrptv' ),
		'isRequired' 			=> true,
		'layoutGridColumnSpan' 	=> 6,
		'size' 					=> 'large',
		'dsrptvAPIParam' 		=> 'first_name'
	),

	array(
		'id' 					=> 2,
		'type' 					=> 'text',
		'label' 				=> __( 'Last Name', 'dsrptv' ),
		'isRequired' 			=> true,
		'layoutGridColumnSpan' 	=> 6,
		'size' 					=> 'large',
		'dsrptvAPIParam' 		=> 'last_name'
	),


	array(
		'id' 					=> 4,
		'type' 					=> 'email',
		'label' 				=> __( 'Email', 'dsrptv' ),
		'isRequired' 			=> true,
		'layoutGridColumnSpan' 	=> 6,
		'size' 					=> 'large',
		'dsrptvAPIParam' 		=> 'email',
	),


	array(
		'id' 					=> 5,
		'type' 					=> 'phone',
		'label' 				=> __( 'Phone', 'dsrptv' ),
		'isRequired' 			=> true,		
		'layoutGridColumnSpan' 	=> 6,
		'size' 					=> 'large',
		'phoneFormat' 			=> 'international',
		'dsrptvAPIParam'  		=> 'phone',
	),


	array(
		'id' 					=> 3,
		'type' 					=> 'text',
		'label' 				=> __( 'Company name', 'dsrptv' ),
		'isRequired' 			=> false,
		'size' 					=> 'large',
		'dsrptvAPIParam'  		=> 'company_name',
	),



	array(
		'id' 				=> 6,
		'type' 				=> 'address',
		'label' 			=> __( 'Address', 'dsrptv' ),
		'addressType' 		=> 'us',
		'dsrptvAdType' 		=> 'billing',
		'inputs' 			=> array(
			array(
				'id' 				=> 6.1,
				'label' 			=> __( 'Street Address', 'dsrptv' ),
				'dsrptvAPIParam' 	=> 'address',
			),
			array(
				'id' 				=> 6.2,
				'label' 			=> __( 'Address Line 2', 'dsrptv' ),
				'dsrptvAPIParam' 	=> 'address2',
			),
			array(
				'id' 				=> 6.3,
				'label' 			=> __( 'City', 'dsrptv' ),
				'dsrptvAPIParam' 	=> 'city',
			),
			array(
				'id' 				=> 6.4,
				'label' 			=> __( 'State / Province / Region', 'dsrptv' ),
				'dsrptvAPIParam' 	=> 'state',
			),
			array(
				'id' 				=> 6.5,
				'label' 			=> __( 'ZIP / Postal Code', 'dsrptv' ),
				'dsrptvAPIParam' 	=> 'zipcode',
			),
			array(
				'id' 				=> 6.6,
				'label' 			=> __( 'Country', 'dsrptv' ),
				'dsrptvAPIParam' 	=> 'country',
			)
		)
	),


);

return $fields;