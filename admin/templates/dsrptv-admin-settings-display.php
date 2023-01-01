<div class="xoo-tabs">
	<?php

	$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $tabs as $tab_key => $tab_caption ) {
		$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
		echo '<a class="nav-tab ' . $active . '" href="?page=dsrptv&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
	}
	echo '</h2>';

	
	$option_name = 'dsrptv-'.$current_tab.'-options';


	?>
</div>


<div class="xoo-container">

	<div class="xoo-main">

		<form method="post" action="options.php">
			<?php

			settings_fields( $option_name ); // Output nonces

			do_settings_sections( $option_name ); // Display Sections & settings

			submit_button( 'Save Settings' );	// Display Save Button
			?>			
			
		</form>


	</div>


</div>

