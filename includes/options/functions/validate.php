<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Email validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_validate_email' ) ) {
	function ovic_validate_email( $value )
	{
		if ( !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			return esc_html__( 'Please write a valid email address!', 'ovic-addon-toolkit' );
		}

		return '';
	}
}
/**
 *
 * Numeric validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_validate_numeric' ) ) {
	function ovic_validate_numeric( $value )
	{
		if ( !is_numeric( $value ) ) {
			return esc_html__( 'Please write a numeric data!', 'ovic-addon-toolkit' );
		}

		return '';
	}
}
/**
 *
 * Required validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_validate_required' ) ) {
	function ovic_validate_required( $value )
	{
		if ( empty( $value ) ) {
			return esc_html__( 'Fatal Error! This field is required!', 'ovic-addon-toolkit' );
		}

		return '';
	}
}
/**
 *
 * URL validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_validate_url' ) ) {
	function ovic_validate_url( $value )
	{
		if ( !filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return esc_html__( 'Please write a valid url!', 'ovic-addon-toolkit' );
		}

		return '';
	}
}
/**
 *
 * Email validate for Customizer
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_customize_validate_email' ) ) {
	function ovic_customize_validate_email( $validity, $value, $wp_customize )
	{
		if ( !sanitize_email( $value ) ) {
			$validity->add( 'required', esc_html__( 'Please write a valid email address!', 'ovic-addon-toolkit' ) );
		}

		return $validity;
	}
}
/**
 *
 * Numeric validate for Customizer
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_customize_validate_numeric' ) ) {
	function ovic_customize_validate_numeric( $validity, $value, $wp_customize )
	{
		if ( !is_numeric( $value ) ) {
			$validity->add( 'required', esc_html__( 'Please write a numeric data!', 'ovic-addon-toolkit' ) );
		}

		return $validity;
	}
}
/**
 *
 * Required validate for Customizer
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_customize_validate_required' ) ) {
	function ovic_customize_validate_required( $validity, $value, $wp_customize )
	{
		if ( empty( $value ) ) {
			$validity->add( 'required', esc_html__( 'Error! This field is required!', 'ovic-addon-toolkit' ) );
		}

		return $validity;
	}
}
/**
 *
 * URL validate for Customizer
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_customize_validate_url' ) ) {
	function ovic_customize_validate_url( $validity, $value, $wp_customize )
	{
		if ( !filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$validity->add( 'required', esc_html__( 'Please write a valid url!', 'ovic-addon-toolkit' ) );
		}

		return $validity;
	}
}
