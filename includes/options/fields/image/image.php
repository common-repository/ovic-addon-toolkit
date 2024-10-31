<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Image
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_Image' ) ) {
	class OVIC_Field_Image extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function render()
		{
			echo $this->field_before();
			$preview = '';
			$add     = ( !empty( $this->field['add_title'] ) ) ? $this->field['add_title'] : esc_html__( 'Add Image', 'ovic-addon-toolkit' );
			$hidden  = ( empty( $this->value ) ) ? ' hidden' : '';
			if ( !empty( $this->value ) ) {
				$attachment = wp_get_attachment_image_src( $this->value, 'thumbnail' );
				$preview    = $attachment[0];
			}
			echo '<div class="ovic-image-preview' . $hidden . '">';
			echo '<div class="ovic-image-inner"><i class="fa fa-times ovic-image-remove"></i><img src="' . $preview . '" alt="preview" /></div>';
			echo '</div>';
			echo '<a href="#" class="button button-primary ovic-button">' . $add . '</a>';
			echo '<input type="text" name="' . $this->field_name() . '" value="' . $this->value . '"' . $this->field_class() . $this->field_attributes() . '/>';
			echo $this->field_after();
		}
	}
}
