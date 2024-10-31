<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.

//
// Shortcode Settings
//
$settings = array(
	'id'           => 'ovic_shortcode',
	'title'        => 'Ovic Shortcode',
	'button_title' => 'shortcode',
);

//
// Shortcode Options
//
$options = array(
	array(
		'title'      => 'Basic Shortcode Examples',
		'shortcodes' => array(

			array(
				'name'   => 'ovic_products',
				'title'  => 'Products',
				'fields' => array(
					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Title',
					),
					array(
						'id'      => 'productsliststyle',
						'type'    => 'select',
						'options' => array(
							'none' => 'None',
							'grid' => 'Grid Bootstrap',
							'owl'  => 'Owl Carousel',
						),
						'default' => 'none',
						'title'   => 'Product list style',
					),
					array(
						'id'          => 'product_style',
						'type'        => 'select_preview',
						'options'     => array(
							'style-01' => array(
								'title'   => 'Style 01',
								'preview' => ''
							),
							'style-02' => array(
								'title'   => 'Style 02',
								'preview' => ''
							),
						),
						'default'     => 'none',
						'title'       => 'Product style',
						'description' => 'Select a style for product item',
					),
					array(
						'id'      => 'product_custom_thumb_width',
						'type'    => 'number',
						'default' => '300',
						'unit'    => 'px',
						'title'   => 'Width',
					),
					array(
						'id'      => 'product_custom_thumb_height',
						'type'    => 'number',
						'default' => '300',
						'unit'    => 'px',
						'title'   => 'Height',
					),
				),
			),

			array(
				'name'   => 'ovic_shortcode_1',
				'title'  => 'Basic Shortcode 1',
				'fields' => array(

					array(
						'id'    => 'icon',
						'type'  => 'icon',
						'title' => 'Icon',
					),

					array(
						'id'    => 'image',
						'type'  => 'image',
						'title' => 'Image',
					),

					array(
						'id'    => 'gallery',
						'type'  => 'gallery',
						'title' => 'Gallery',
					),

					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Title',
					),

					array(
						'id'          => 'select_sortable',
						'type'        => 'select',
						'title'       => 'Select with multiple Chosen and Sortable',
						'chosen'      => true,
						'multiple'    => true,
						'sortable'    => true,
						'placeholder' => 'Select an option',
						'options'     => array(
							'opt-1' => 'Option 1',
							'opt-2' => 'Option 2',
							'opt-3' => 'Option 3',
							'opt-4' => 'Option 4',
							'opt-5' => 'Option 5',
							'opt-6' => 'Option 6',
						),
						'default'     => array( 'opt-1', 'opt-2', 'opt-3' )
					),

					array(
						'id'          => 'select_ajax',
						'type'        => 'select',
						'title'       => 'Select with multiple AJAX search Pages',
						'chosen'      => true,
						'multiple'    => true,
						'sortable'    => true,
						'ajax'        => true,
						'options'     => 'pages',
						'placeholder' => 'Select pages',
					),

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
						'help'  => 'Lorem Ipsum Dollar.',
					),

				),
			),

			array(
				'name'   => 'ovic_shortcode_2',
				'title'  => 'Basic Shortcode 2',
				'fields' => array(

					array(
						'id'    => 'option_1',
						'type'  => 'text',
						'title' => 'Option 1',
						'help'  => 'Lorem Ipsum Dollar.',
					),

					array(
						'id'    => 'option_2',
						'type'  => 'text',
						'title' => 'Option 2',
					),

					array(
						'id'    => 'option_3',
						'type'  => 'text',
						'title' => 'Option 3',
					),

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
					)

				),
			),

			array(
				'name'   => 'ovic_shortcode_3',
				'title'  => 'Basic Shortcode 3',
				'fields' => array(

					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Title',
					),

					array(
						'id'    => 'active',
						'type'  => 'switcher',
						'title' => 'Active',
						'label' => 'You you want to it ?',
					),

					array(
						'id'         => 'car',
						'type'       => 'select',
						'title'      => 'Your car',
						'options'    => array(
							'bmw'      => 'BMW',
							'mercedes' => 'Mercedes',
							'opel'     => 'Opel',
							'ferrari'  => 'Ferrari'
						),
						'dependency' => array( 'active', '!=', '' )
					),

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
					)

				),
			),

			array(
				'name'   => 'ovic_shortcode_4',
				'title'  => 'Basic Shortcode 4',
				'fields' => array(

					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Title',
					),

					array(
						'id'      => 'active',
						'type'    => 'radio',
						'title'   => 'Active',
						'options' => array(
							'yes' => 'Yes, Please.',
							'no'  => 'No, Thank you.',
						)
					),

					array(
						'id'      => 'cars',
						'type'    => 'checkbox',
						'title'   => 'Select your cars',
						'options' => array(
							'bmw'      => 'BMW',
							'mercedes' => 'Mercedes',
							'open'     => 'Opel',
							'ferrari'  => 'Ferrari'
						)
					),

					array(
						'id'    => 'avatar',
						'type'  => 'upload',
						'title' => 'Avatar',
					),

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
					)

				),
			),

			array(
				'name'   => 'ovic_shortcode_5',
				'title'  => 'Basic Shortcode 5',
				'fields' => array(

					array(
						'id'      => 'layout',
						'title'   => 'Layout',
						'type'    => 'image_select',
						'options' => array(
							'layout-1' => 'http://codestarframework.com/assets/images/placeholder/65x65-2ecc71.gif',
							'layout-2' => 'http://codestarframework.com/assets/images/placeholder/65x65-e74c3c.gif',
							'layout-3' => 'http://codestarframework.com/assets/images/placeholder/65x65-3498db.gif',
						),
					),

					array(
						'id'         => 'cars',
						'type'       => 'select',
						'title'      => 'Select your cars',
						'options'    => array(
							'bmw'      => 'BMW',
							'mercedes' => 'Mercedes',
							'open'     => 'Opel',
							'ferrari'  => 'Ferrari',
							'jaguar'   => 'Jaguar',
							'seat'     => 'Seat',
						),
						'attributes' => array(
							'multiple' => 'only-key',
							'style'    => 'width: 125px; height: 100px;',
						)
					),

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
					)

				),
			),

			array(
				'name'   => 'ovic_shortcode_6',
				'title'  => 'Basic Shortcode 6',
				'fields' => array(

					array(
						'id'          => 'select_sortable',
						'type'        => 'select',
						'title'       => 'Select with multiple Chosen and Sortable',
						'chosen'      => true,
						'multiple'    => true,
						'sortable'    => true,
						'placeholder' => 'Select an option',
						'options'     => array(
							'opt-1' => 'Option 1',
							'opt-2' => 'Option 2',
							'opt-3' => 'Option 3',
							'opt-4' => 'Option 4',
							'opt-5' => 'Option 5',
							'opt-6' => 'Option 6',
						),
						'default'     => array( 'opt-1', 'opt-2', 'opt-3' )
					),

					array(
						'id'          => 'select_ajax',
						'type'        => 'select',
						'title'       => 'Select with multiple AJAX search Pages',
						'chosen'      => true,
						'multiple'    => true,
						'sortable'    => true,
						'ajax'        => true,
						'options'     => 'pages',
						'placeholder' => 'Select pages',
					),

				)
			)

		)
	),
	array(
		'title'      => 'Simple Shortcode Examples',
		'shortcodes' => array(

			array(
				'name'   => 'ovic_simple_1',
				'title'  => 'Simple Shortcode 1',
				'fields' => array(

					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Title',
					),

				),
			),

			array(
				'name'   => 'ovic_simple_2',
				'title'  => 'Simple Shortcode 2',
				'fields' => array(

					array(
						'id'    => 'option_1',
						'type'  => 'text',
						'title' => 'Option 1',
					),

					array(
						'id'    => 'option_2',
						'type'  => 'text',
						'title' => 'Option 2',
					),

					array(
						'id'    => 'option_3',
						'type'  => 'text',
						'title' => 'Option 3',
					),

				),
			),

			array(
				'name'   => 'ovic_simple_3',
				'title'  => 'Simple Shortcode 3',
				'fields' => array(

					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Title',
					),

					array(
						'id'    => 'active',
						'type'  => 'switcher',
						'title' => 'Active',
						'label' => 'You you want to it ?',
					),

					array(
						'id'      => 'car',
						'type'    => 'select',
						'title'   => 'Your car',
						'options' => array(
							'bmw'      => 'BMW',
							'mercedes' => 'Mercedes',
							'opel'     => 'Opel',
							'ferrari'  => 'Ferrari'
						)
					),

				),
			),

		)
	),
	array(
		'title'      => 'Single Shortcode Examples',
		'shortcodes' => array(

			array(
				'name'   => 'ovic_single_1',
				'title'  => 'Single Shortcode 1',
				'fields' => array(

					array(
						'type'    => 'content',
						'content' => 'Just click to "Insert Shortcode, this is adding a single shortcode',
					),

				),
			),

			array(
				'name'   => 'ovic_single_2',
				'title'  => 'Single Shortcode 2',
				'fields' => array(

					array(
						'type'    => 'content',
						'content' => 'Just click to "Insert Shortcode, this is adding a single shortcode',
					),

				),
			),

			array(
				'name'   => 'ovic_single_3',
				'title'  => 'Single Shortcode 3',
				'fields' => array(

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
						'help'  => 'This is a single shortcode and there is only content.',
					)

				),
			),

		)
	),
	array(
		'title'      => 'Advanced Shortcode Examples',
		'shortcodes' => array(

			array(
				'name'         => 'ovic_advanced_1',
				'title'        => 'Repeater Shortcode',
				'view'         => 'repeater',
				'clone_title'  => 'Add New',
				'clone_fields' => array(

					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Title',
					),

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
					),

				)
			),

			array(
				'name'         => 'ovic_advanced_3',
				'title'        => 'Group Shortcode',
				'view'         => 'group',
				'clone_id'     => 'ovic_advanced_3_sub',
				'clone_title'  => 'Add New',
				'fields'       => array(

					array(
						'id'    => 'title_1',
						'type'  => 'textarea',
						'title' => 'Content 1',
					),

					array(
						'id'    => 'content_2',
						'type'  => 'textarea',
						'title' => 'Content 2',
					)

				),
				'clone_fields' => array(

					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => 'Tab Title',
					),

					array(
						'id'    => 'content',
						'type'  => 'textarea',
						'title' => 'Content',
					),
				)
			),

			array(
				'name'   => 'ovic_advanced_4',
				'title'  => 'Contents Shortcode',
				'view'   => 'contents',
				'fields' => array(

					array(
						'id'    => 'content_1',
						'type'  => 'textarea',
						'title' => 'Content 1',
					),

					array(
						'id'    => 'content_2',
						'type'  => 'textarea',
						'title' => 'Content 2',
					)

				),
			),

		)
	)
);

OVIC_Shortcode::instance( $settings, $options );