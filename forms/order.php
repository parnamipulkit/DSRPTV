<?php

$fields = array(

    array(
        'id'        => 1,
        'type'      => 'creditcard',
        'label'     => __( 'Credit Card', 'dsrptv' ),
        'inputs'    => array(
                array(
                'id'                => '1.1',
                'label'             => 'Card Number',
                'customLabel'       => 'Number',
                'placeholder'       => __( 'Enter your credit card number', 'dsrptv' ),
                'dsrptvAPIParam'    => 'card_number',
            ),
            array(
                'id'                => '1.2_month',
                'label'             => __( 'Expiration Month', 'dsrptv' ),
                'defaultLabel'      => __( 'Expiration Date', 'dsrptv' ),
                'customLabel'       => 'Expiry',
                'placeholder'       => __( 'Enter the expiration month', 'dsrptv' ),
                'dsrptvAPIParam'    => 'card_exp_month',
            ),
            array(
                'id'                => '1.2_year',
                'label'             => 'Expiration Year',
                'placeholder'       => 'Enter the expiration year',
                'dsrptvAPIParam'    => 'card_exp_year',
            ),
            array(
                'id'                => '1.3',
                'label'             => 'Security Code',
                'customLabel'       => 'CVV',
                'placeholder'       => 'Enter your CVV',
                'dsrptvAPIParam'    => 'card_cvv',
            ),
            array(
                'id'                => '1.4',
                'label'             => 'Card Type',
                'dsrptvAPIParam'    => 'card_type',
            ),
            array(
                'id'                => '1.5',
                'label'             => 'Cardholder Name',
                'customLabel'       => 'Name',
                'placeholder'       => 'Enter your name as it appears on the card',
                'dsrptvAPIParam'    => 'card_name',
            ),
        )

	),


    array(
        'id'                    => 2,
        'type'                  => 'dsrptv_product',
        'label'                 => __( 'Product Settings', 'dsrptv' ),
        'dsrptvAPIParam'        => 'products.'.rand(1,9999),
    ),


);

return $fields;