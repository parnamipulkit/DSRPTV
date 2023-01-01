<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}


class GF_Field_DSRPTV_Product extends GF_Field_Hidden {

	public $type = 'dsrptv_product';


	public function get_form_editor_field_title() {
		return esc_attr__( 'DSRPTV Product', 'dsrptv' );
	}



	/**
	 * Returns the field's form editor description.
	 *
	 * @since 2.5
	 *
	 * @return string
	 */
	public function get_form_editor_field_description() {
		return esc_attr__( 'Add DSRPTV Product ID, quantity & other options', 'gravityforms' );
	}


	public function is_value_submission_array() {
		return true;
	}


	public function get_field_input( $form, $value = array(), $entry = null ) {


		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		if( $is_form_editor ) return '';

		$id       = (int) $this->id;

		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

		$field_type         = $is_entry_detail || $is_form_editor ? 'text' : 'hidden';
		$class_attribute    = $is_entry_detail || $is_form_editor ? '' : "class='gform_hidden'";
		$required_attribute = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

		$keys = array(
			'id' 	=> $this->dsrptvProductID,
			'qty' 	=> $this->dsrptvProductQty
		);

		$input = '';


		foreach ( $keys as $key => $keyDefaultValue ) {

			if( $is_entry_detail || $is_form_editor ){
				$input .= '<label>'.$key.'</label>';
			}
			

			$input .= sprintf( "<input name='input_%d[%s]' type='%s' value='%s' %s/>",$id, $key, $field_type, $keyDefaultValue, $is_form_editor ? 'disabled' : ''  );

		}


		return sprintf( "<div class='ginput_container ginput_container_text'>%s</div>", $input );
	}


}

GF_Fields::register( new GF_Field_DSRPTV_Product() );
