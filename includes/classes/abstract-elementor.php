<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager as Controls_Manager;

/**
 * Elementor shortcode widget.
 *
 * Elementor widget that insert any shortcodes into the page.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'Ovic_Widget_Elementor' ) ) {
    class Ovic_Widget_Elementor extends Elementor\Widget_Base
    {
        /**
         * Get widget name.
         *
         * Retrieve shortcode widget name.
         *
         * @return string Widget name.
         * @since 1.0.0
         * @access public
         *
         */
        public function get_name()
        {
            return 'ovic';
        }

        /**
         * Get widget title.
         *
         * Retrieve shortcode widget title.
         *
         * @return string Widget title.
         * @since 1.0.0
         * @access public
         *
         */
        public function get_title()
        {
            return esc_html__( 'Shortcode', 'ovic-addon-toolkit' );
        }

        /**
         * Get widget icon.
         *
         * Retrieve shortcode widget icon.
         *
         * @return string Widget icon.
         * @since 1.0.0
         * @access public
         *
         */
        public function get_icon()
        {
            return 'eicon-shortcode';
        }

        /**
         * Get widget keywords.
         *
         * Retrieve the list of keywords the widget belongs to.
         *
         * @return array Widget keywords.
         * @since 2.1.0
         * @access public
         *
         */
        public function get_keywords()
        {
            return [
                'ovic',
                'kutethemes',
                str_replace( '_', ' ', $this->get_name() )
            ];
        }

        /**
         * Get widget categories.
         *
         * Retrieve the list of categories the image widget belongs to.
         *
         * Used to determine where to display the widget in the editor.
         *
         * @return array Widget categories.
         * @since 2.0.0
         * @access public
         *
         */
        public function get_categories()
        {
            return array( 'ovic' );
        }

        /**
         * Whether the reload preview is required or not.
         *
         * Used to determine whether the reload preview is required.
         *
         * @return bool Whether the reload preview is required.
         * @since 1.0.0
         * @access public
         *
         */
        public function is_reload_preview_required()
        {
            return true;
        }

        /**
         * Initialize controls.
         *
         * Register the all controls added by `register_controls()`.
         *
         * @since 2.0.0
         * @access protected
         */
        protected function init_controls()
        {
            if ( $this->has_own_method( '_register_controls', self::class ) ) {
                $this->_register_controls();
            } else {
                $this->register_controls();
            }
        }

        /**
         * Register shortcode widget controls.
         *
         * Adds different input fields to allow the user to change and customize the widget settings.
         *
         * @since 1.0.0
         * @access protected
         */
        protected function _register_controls()
        {
            $this->register_controls();
        }

        /**
         * Register shortcode widget controls.
         *
         * Adds different input fields to allow the user to change and customize the widget settings.
         *
         * @since 3.1.0
         * @access protected
         */
        protected function register_controls()
        {
            $this->start_controls_section(
                'section_shortcode',
                [
                    'label' => esc_html__( 'Shortcode', 'ovic-addon-toolkit' ),
                ]
            );

            $this->add_control(
                'shortcode',
                [
                    'label'       => esc_html__( 'Enter your shortcode', 'ovic-addon-toolkit' ),
                    'type'        => Controls_Manager::TEXTAREA,
                    'dynamic'     => [
                        'active' => true,
                    ],
                    'placeholder' => '[gallery id="123" size="medium"]',
                    'default'     => '',
                ]
            );

            $this->end_controls_section();
        }

        /**
         * Render shortcode widget output on the frontend.
         *
         * Written in PHP and used to generate the final HTML.
         *
         * @since 1.0.0
         * @access protected
         */
        protected function render()
        {
            echo ovic_do_shortcode(
                $this->get_name(),
                $this->get_settings_for_display()
            );
        }

        /**
         * Render shortcode widget as plain content.
         *
         * Override the default behavior by printing the shortcode instead of rendering it.
         *
         * @since 1.0.0
         * @access public
         */
        public function render_plain_content()
        {
            // In plain mode, render without shortcode
            echo $this->get_settings( 'shortcode' );
        }

        /**
         * Render shortcode widget output in the editor.
         *
         * Written as a Backbone JavaScript template and used to generate the live preview.
         *
         * @since 2.9.0
         * @access protected
         */
        protected function content_template()
        {
        }

        public function getCategoryChildsFull( $parent_id, $array, $level, $return_id, &$dropdown )
        {
            $keys = array_keys( $array );
            $i    = 0;
            while ( $i < count( $array ) ) {
                $key  = $keys[ $i ];
                $item = $array[ $key ];
                $i++;
                if ( $item->category_parent == $parent_id ) {
                    $name               = str_repeat( '- ', $level ) . $item->name;
                    $value              = $return_id ? $item->term_id : $item->slug;
                    $dropdown[ $value ] = $name . '(' . $item->count . ')';
                    unset( $array[ $key ] );
                    $array = $this->getCategoryChildsFull( $item->term_id, $array, $level + 1, $return_id, $dropdown );
                    $keys  = array_keys( $array );
                    $i     = 0;
                }
            }

            return $array;
        }

        public function get_taxonomy( $settings = array() )
        {
            $args                = array(
                'type'         => 'post',
                'child_of'     => 0,
                'parent'       => '',
                'orderby'      => 'name',
                'order'        => 'ASC',
                'hide_empty'   => false,
                'hierarchical' => 1,
                'exclude'      => '',
                'include'      => '',
                'number'       => '',
                'taxonomy'     => 'category',
                'pad_counts'   => false,
                'return_id'    => false,
            );
            $args                = wp_parse_args( $settings, $args );
            $categories          = get_categories( $args );
            $categories_dropdown = array(
                '' => esc_html__( 'None', 'ovic-addon-toolkit' ),
            );
            if ( !empty( $categories ) ) {
                $this->getCategoryChildsFull( 0, $categories, 0, $args['return_id'], $categories_dropdown );
            }

            return $categories_dropdown;
        }

        public function effect_field( $id = 'css_effect', $class = false )
        {
            $elementor = $class !== false ? $class : $this;
            $elementor->add_control( $id,
                [
                    'type'    => Elementor\Controls_Manager::SELECT,
                    'label'   => esc_html__( 'Effect', 'ovic-addon-toolkit' ),
                    'options' => apply_filters( 'ovic_elementor_background_effect', [
                        'none'                          => esc_html__( 'None', 'ovic-addon-toolkit' ),
                        'effect normal-effect'          => esc_html__( 'Normal Effect', 'ovic-addon-toolkit' ),
                        'effect normal-effect dark-bg'  => esc_html__( 'Normal Effect Dark', 'ovic-addon-toolkit' ),
                        'effect background-zoom'        => esc_html__( 'Background Zoom', 'ovic-addon-toolkit' ),
                        'effect background-slide'       => esc_html__( 'Background Slide', 'ovic-addon-toolkit' ),
                        'effect rotate-in rotate-left'  => esc_html__( 'Rotate Left In', 'ovic-addon-toolkit' ),
                        'effect rotate-in rotate-right' => esc_html__( 'Rotate Right In', 'ovic-addon-toolkit' ),
                        'effect plus-zoom'              => esc_html__( 'Plus Zoom', 'ovic-addon-toolkit' ),
                        'effect border-zoom'            => esc_html__( 'Border Zoom', 'ovic-addon-toolkit' ),
                        'effect border-scale'           => esc_html__( 'Border ScaleUp', 'ovic-addon-toolkit' ),
                        'effect border-plus'            => esc_html__( 'Border Plus', 'ovic-addon-toolkit' ),
                        'effect overlay-plus'           => esc_html__( 'Overlay Plus', 'ovic-addon-toolkit' ),
                        'effect overlay-cross'          => esc_html__( 'Overlay Cross', 'ovic-addon-toolkit' ),
                        'effect overlay-horizontal'     => esc_html__( 'Overlay Horizontal', 'ovic-addon-toolkit' ),
                        'effect overlay-vertical'       => esc_html__( 'Overlay Vertical', 'ovic-addon-toolkit' ),
                        'effect flashlight'             => esc_html__( 'Flashlight', 'ovic-addon-toolkit' ),
                        'effect faded-in'               => esc_html__( 'Faded In', 'ovic-addon-toolkit' ),
                        'effect bounce-in'              => esc_html__( 'Bounce In', 'ovic-addon-toolkit' ),
                        'effect gray-filter'            => esc_html__( 'Gray Filter', 'ovic-addon-toolkit' ),
                    ] ),
                    'default' => 'none',
                ]
            );
        }

        public function product_size_field( $class = false )
        {
            $elementor = $class !== false ? $class : $this;
            // CUSTOM PRODUCT SIZE
            $product_size_width_list = array();
            $width                   = 300;
            $height                  = 300;
            if ( function_exists( 'wc_get_image_size' ) ) {
                $size   = wc_get_image_size( 'thumbnail' );
                $width  = isset( $size['width'] ) ? $size['width'] : $width;
                $height = isset( $size['height'] ) ? $size['height'] : $height;
            }
            for ( $i = 100; $i < $width; $i = $i + 10 ) {
                array_push( $product_size_width_list, $i );
            }
            $product_size_list                           = array();
            $product_size_list[ $width . 'x' . $height ] = $width . 'x' . $height;
            foreach ( $product_size_width_list as $k => $w ) {
                $w      = intval( $w );
                $width  = intval( $width );
                $height = intval( $height );
                if ( isset( $width ) && $width > 0 ) {
                    $h = round( $height * $w / $width );
                } else {
                    $h = $w;
                }
                $product_size_list[ $w . 'x' . $h ] = $w . 'x' . $h;
            }
            $product_size_list['custom'] = 'Custom';

            $elementor->add_control(
                'product_image_size',
                [
                    'type'    => Elementor\Controls_Manager::SELECT,
                    'label'   => esc_html__( 'Image size', 'ovic-addon-toolkit' ),
                    'options' => $product_size_list,
                    'default' => $width . 'x' . $height,
                ]
            );

            $elementor->add_control(
                'product_custom_thumb_width',
                array(
                    'type'        => Controls_Manager::NUMBER,
                    'label'       => esc_html__( 'Width', 'ovic-addon-toolkit' ),
                    'default'     => $width,
                    'placeholder' => $width,
                    'condition'   => [
                        'product_image_size' => 'custom'
                    ],
                )
            );

            $elementor->add_control(
                'product_custom_thumb_height',
                array(
                    'type'        => Controls_Manager::NUMBER,
                    'label'       => esc_html__( 'Height', 'ovic-addon-toolkit' ),
                    'default'     => $height,
                    'placeholder' => $height,
                    'condition'   => [
                        'product_image_size' => 'custom'
                    ],
                )
            );
        }

        public function bootstrap_settings( $settings = true, $prefix = 'grid_' )
        {
            $default = [
                'tab'   => Controls_Manager::TAB_SETTINGS,
                'label' => esc_html__( 'Bootstrap', 'ovic-addon-toolkit' ),
            ];

            $section = is_array( $settings ) ? $settings : $default;

            if ( $settings != false ) {
                $this->start_controls_section(
                    $prefix . 'bootstrap_section',
                    $section
                );
            }

            $this->add_control(
                $prefix . 'rows_space',
                [
                    'label'   => esc_html__( 'Rows space', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'rows-space-30',
                    'options' => [
                        'rows-space-0'  => 'Default',
                        'rows-space-5'  => '5px',
                        'rows-space-10' => '10px',
                        'rows-space-15' => '15px',
                        'rows-space-20' => '20px',
                        'rows-space-25' => '25px',
                        'rows-space-30' => '30px',
                        'rows-space-35' => '35px',
                        'rows-space-40' => '40px',
                        'rows-space-45' => '45px',
                        'rows-space-50' => '50px',
                        'rows-space-60' => '60px',
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'desktop',
                array(
                    'type'        => Controls_Manager::SELECT,
                    'label'       => esc_html__( 'Desktop', 'ovic-addon-toolkit' ),
                    'description' => esc_html__( 'screen resolution >= 1500px', 'ovic-addon-toolkit' ),
                    'default'     => 'col-bg-3',
                    'options'     => [
                        'col-bg-12' => '1 item',
                        'col-bg-6'  => '2 items',
                        'col-bg-4'  => '3 items',
                        'col-bg-3'  => '4 items',
                        'col-bg-15' => '5 items',
                        'col-bg-2'  => '6 items',
                    ],
                )
            );

            $this->add_control(
                $prefix . 'laptop',
                array(
                    'type'        => Controls_Manager::SELECT,
                    'label'       => esc_html__( 'Laptop', 'ovic-addon-toolkit' ),
                    'description' => esc_html__( 'screen resolution >= 1200px and < 1500px', 'ovic-addon-toolkit' ),
                    'default'     => 'col-lg-3',
                    'options'     => [
                        'col-lg-12' => '1 item',
                        'col-lg-6'  => '2 items',
                        'col-lg-4'  => '3 items',
                        'col-lg-3'  => '4 items',
                        'col-lg-15' => '5 items',
                        'col-lg-2'  => '6 items',
                    ],
                )
            );

            $this->add_control(
                $prefix . 'ipad',
                array(
                    'type'        => Controls_Manager::SELECT,
                    'label'       => esc_html__( 'Ipad', 'ovic-addon-toolkit' ),
                    'description' => esc_html__( 'screen resolution >= 992px and < 1200px', 'ovic-addon-toolkit' ),
                    'default'     => 'col-md-4',
                    'options'     => [
                        'col-md-12' => '1 item',
                        'col-md-6'  => '2 items',
                        'col-md-4'  => '3 items',
                        'col-md-3'  => '4 items',
                        'col-md-15' => '5 items',
                        'col-md-2'  => '6 items',
                    ],
                )
            );

            $this->add_control(
                $prefix . 'landscape',
                array(
                    'type'        => Controls_Manager::SELECT,
                    'label'       => esc_html__( 'landscape Tablet', 'ovic-addon-toolkit' ),
                    'description' => esc_html__( 'screen resolution >= 768px and < 992px', 'ovic-addon-toolkit' ),
                    'default'     => 'col-sm-6',
                    'options'     => [
                        'col-sm-12' => '1 item',
                        'col-sm-6'  => '2 items',
                        'col-sm-4'  => '3 items',
                        'col-sm-3'  => '4 items',
                        'col-sm-15' => '5 items',
                        'col-sm-2'  => '6 items',
                    ],
                )
            );

            $this->add_control(
                $prefix . 'portrait',
                array(
                    'type'        => Controls_Manager::SELECT,
                    'label'       => esc_html__( 'Portrait Tablet', 'ovic-addon-toolkit' ),
                    'description' => esc_html__( 'screen resolution >= 480px  add < 768px', 'ovic-addon-toolkit' ),
                    'default'     => 'col-xs-6',
                    'options'     => [
                        'col-xs-12' => '1 item',
                        'col-xs-6'  => '2 items',
                        'col-xs-4'  => '3 items',
                        'col-xs-3'  => '4 items',
                        'col-xs-15' => '5 items',
                        'col-xs-2'  => '6 items',
                    ],
                )
            );

            $this->add_control(
                $prefix . 'mobile',
                array(
                    'type'        => Controls_Manager::SELECT,
                    'label'       => esc_html__( 'Mobile', 'ovic-addon-toolkit' ),
                    'description' => esc_html__( 'screen resolution < 480px', 'ovic-addon-toolkit' ),
                    'default'     => 'col-ts-6',
                    'options'     => [
                        'col-ts-12' => '1 item',
                        'col-ts-6'  => '2 items',
                        'col-ts-4'  => '3 items',
                        'col-ts-3'  => '4 items',
                        'col-ts-15' => '5 items',
                        'col-ts-2'  => '6 items',
                    ],
                )
            );

            if ( !empty( $settings != false ) ) {
                $this->end_controls_section();
            }
        }

        public function carousel_settings( $settings = true, $prefix = 'slides_' )
        {
            $default = [
                'tab'   => Controls_Manager::TAB_SETTINGS,
                'label' => esc_html__( 'Carousel Settings', 'ovic-addon-toolkit' ),
            ];

            $section        = is_array( $settings ) ? $settings : $default;
            $slides_to_show = range( 1, 10 );
            $slides_to_show = array_combine( $slides_to_show, $slides_to_show );

            if ( $settings != false ) {
                $this->start_controls_section(
                    $prefix . 'carousel_settings',
                    $section
                );
            }

            $this->start_controls_tabs( $prefix . 'tabs_carousel' );

            $this->start_controls_tab(
                $prefix . 'tab_settings',
                [
                    'label' => esc_html__( 'Settings', 'ovic-addon-toolkit' ),
                ]
            );

            $this->add_control(
                $prefix . 'rows_space',
                [
                    'label'   => esc_html__( 'Rows space', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'rows-space-0',
                    'options' => [
                        'rows-space-0'  => 'Default',
                        'rows-space-5'  => '5px',
                        'rows-space-10' => '10px',
                        'rows-space-15' => '15px',
                        'rows-space-20' => '20px',
                        'rows-space-25' => '25px',
                        'rows-space-30' => '30px',
                        'rows-space-35' => '35px',
                        'rows-space-40' => '40px',
                        'rows-space-45' => '45px',
                        'rows-space-50' => '50px',
                        'rows-space-60' => '60px',
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'rows',
                [
                    'label'   => esc_html__( 'Rows', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                    ],
                    'default' => 1,
                ]
            );

            $this->add_control(
                $prefix . 'margin',
                [
                    'label'       => esc_html__( 'Margin', 'ovic-addon-toolkit' ),
                    'type'        => Controls_Manager::NUMBER,
                    'min'         => 0,
                    'placeholder' => '30',
                    'default'     => '30',
                ]
            );

            $this->add_control(
                $prefix . 'to_show',
                [
                    'label'   => esc_html__( 'Slides to Show', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                            '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        ] + $slides_to_show,
                    'default' => 4,
                ]
            );

            $this->add_control(
                $prefix . 'navigation',
                [
                    'label'   => esc_html__( 'Navigation', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'both',
                    'options' => [
                        'both'   => esc_html__( 'Arrows and Dots', 'ovic-addon-toolkit' ),
                        'arrows' => esc_html__( 'Arrows', 'ovic-addon-toolkit' ),
                        'dots'   => esc_html__( 'Dots', 'ovic-addon-toolkit' ),
                        'none'   => esc_html__( 'None', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'vertical',
                [
                    'label'   => esc_html__( 'Vertical', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                    'default' => 'no',
                ]
            );

            $this->add_control(
                $prefix . 'autoplay',
                [
                    'label'   => esc_html__( 'Autoplay', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'no',
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'autoplay_speed',
                [
                    'label'     => esc_html__( 'Autoplay Speed', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::NUMBER,
                    'min'       => 0,
                    'default'   => 1000,
                    'condition' => [
                        $prefix . 'autoplay' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'infinite',
                [
                    'label'   => esc_html__( 'Infinite Loop', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'no',
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'speed',
                [
                    'label'   => esc_html__( 'Animation Speed', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => 0,
                    'default' => 500,
                ]
            );

            $this->add_control(
                $prefix . 'direction',
                [
                    'label'   => esc_html__( 'Direction', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'ltr',
                    'options' => [
                        'ltr' => esc_html__( 'Left', 'ovic-addon-toolkit' ),
                        'rtl' => esc_html__( 'Right', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                $prefix . 'tab_responsive',
                [
                    'label' => esc_html__( 'Responsive', 'ovic-addon-toolkit' ),
                ]
            );

            $repeater = new \Elementor\Repeater();

            $repeater->add_control(
                'screen',
                [
                    'label'   => esc_html__( 'Screen', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::TEXT,
                    'default' => 1500,
                ]
            );

            $repeater->add_control(
                'show',
                [
                    'label'   => esc_html__( 'Slides to Show', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                            '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        ] + $slides_to_show,
                    'default' => 4,
                ]
            );

            $repeater->add_control(
                'navigation',
                [
                    'label'   => esc_html__( 'Navigation', 'ovic-addon-toolkit' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'default' => '',
                    'options' => [
                        'both'   => esc_html__( 'Arrows and Dots', 'ovic-addon-toolkit' ),
                        'arrows' => esc_html__( 'Arrows', 'ovic-addon-toolkit' ),
                        'dots'   => esc_html__( 'Dots', 'ovic-addon-toolkit' ),
                        'none'   => esc_html__( 'None', 'ovic-addon-toolkit' ),
                        ''       => esc_html__( 'Inherit', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $repeater->add_control(
                'rows',
                [
                    'label'   => esc_html__( 'Rows', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                    ],
                    'default' => '1',
                ]
            );

            $repeater->add_control(
                'margin',
                [
                    'label'       => esc_html__( 'Margin', 'ovic-addon-toolkit' ),
                    'type'        => Controls_Manager::NUMBER,
                    'min'         => 0,
                    'placeholder' => '30',
                    'default'     => '30',
                ]
            );

            $repeater->add_control(
                'vertical',
                [
                    'label'   => esc_html__( 'Vertical', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                    'default' => 'no',
                ]
            );

            $this->add_control(
                $prefix . 'responsive',
                [
                    'type'          => Controls_Manager::REPEATER,
                    'fields'        => $repeater->get_controls(),
                    'title_field'   => esc_html__( 'Screen: ', 'ovic-addon-toolkit' ) . '{{{ screen }}}px',
                    'prevent_empty' => false,
                    'default'       => [
                        [
                            'screen'     => 1500,
                            'show'       => 4,
                            'margin'     => 30,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 1200,
                            'show'       => 4,
                            'margin'     => 30,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 992,
                            'show'       => 3,
                            'margin'     => 20,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 768,
                            'show'       => 2,
                            'margin'     => 20,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 480,
                            'show'       => 2,
                            'margin'     => 10,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            if ( $settings != false ) {
                $this->end_controls_section();
            }
        }

        public function grid_settings_new( $settings = true, $prefix = 'grid_' )
        {
            $default = [
                'tab'   => Controls_Manager::TAB_SETTINGS,
                'label' => esc_html__( 'Grid Settings', 'ovic-addon-toolkit' ),
            ];

            $section = is_array( $settings ) ? $settings : $default;

            if ( $settings != false ) {
                $this->start_controls_section(
                    $prefix . 'grid_settings',
                    $section
                );
            }

            $this->add_responsive_control(
                $prefix . 'rows_space',
                [
                    'label'     => esc_html__( 'Rows Space', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::NUMBER,
                    'min'       => 0,
                    'selectors' => [
                        '{{WRAPPER}}' => '--rows-space: {{VALUE}}px;',
                    ],
                ]
            );

            $this->add_responsive_control(
                $prefix . 'cols',
                [
                    'label'     => esc_html__( 'Columns', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::NUMBER,
                    'min'       => 0,
                    'selectors' => [
                        '{{WRAPPER}}' => '--cols-number: {{VALUE}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                $prefix . 'cols_space',
                [
                    'label'     => esc_html__( 'Columns Space', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::NUMBER,
                    'min'       => 0,
                    'selectors' => [
                        '{{WRAPPER}}' => '--cols-space: {{VALUE}}px;',
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'justify_content',
                [
                    'label'     => esc_html__( 'Justify Content', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::SELECT,
                    'options'   => [
                        ''              => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        'center'        => esc_html__( 'Center', 'ovic-addon-toolkit' ),
                        'flex-start'    => esc_html__( 'Flex start', 'ovic-addon-toolkit' ),
                        'flex-end'      => esc_html__( 'Flex end', 'ovic-addon-toolkit' ),
                        'space-around'  => esc_html__( 'Space Around', 'ovic-addon-toolkit' ),
                        'space-between' => esc_html__( 'Space Between', 'ovic-addon-toolkit' ),
                        'space-evenly'  => esc_html__( 'Space Evenly', 'ovic-addon-toolkit' ),
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--justify-content: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'align_items',
                [
                    'label'     => esc_html__( 'Align Items', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::SELECT,
                    'options'   => [
                        ''           => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        'center'     => esc_html__( 'Center', 'ovic-addon-toolkit' ),
                        'flex-start' => esc_html__( 'Flex start', 'ovic-addon-toolkit' ),
                        'flex-end'   => esc_html__( 'Flex end', 'ovic-addon-toolkit' ),
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--align-items: {{VALUE}};',
                    ],
                ]
            );

            if ( !empty( $settings != false ) ) {
                $this->end_controls_section();
            }
        }

        public function carousel_settings_new( $settings = true, $prefix = 'slides_' )
        {
            $default = [
                'tab'   => Controls_Manager::TAB_SETTINGS,
                'label' => esc_html__( 'Carousel Settings', 'ovic-addon-toolkit' ),
            ];

            $section        = is_array( $settings ) ? $settings : $default;
            $slides_to_show = range( 1, 10 );
            $slides_to_show = array_combine( $slides_to_show, $slides_to_show );

            if ( $settings != false ) {
                $this->start_controls_section(
                    $prefix . 'carousel_settings',
                    $section
                );
            }

            $this->start_controls_tabs( $prefix . 'tabs_carousel' );

            $this->start_controls_tab(
                $prefix . 'tab_settings',
                [
                    'label' => esc_html__( 'Settings', 'ovic-addon-toolkit' ),
                ]
            );

            $this->add_control(
                $prefix . 'rows',
                [
                    'label'   => esc_html__( 'Rows', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                            '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        ] + $slides_to_show,
                    'default' => 1,
                ]
            );

            $this->add_responsive_control(
                $prefix . 'rows_space',
                [
                    'label'     => esc_html__( 'Rows Space', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::NUMBER,
                    'min'       => 0,
                    'selectors' => [
                        '{{WRAPPER}}' => '--rows-space: {{VALUE}}px;',
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'to_show',
                [
                    'label'   => esc_html__( 'Columns', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                            '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        ] + $slides_to_show,
                    'default' => 4,
                ]
            );

            $this->add_control(
                $prefix . 'margin',
                [
                    'label'   => esc_html__( 'Columns Space', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => 0,
                    'default' => 30,
                ]
            );

            $this->add_control(
                'overflow_visible',
                [
                    'label'        => esc_html__( 'Items overflow', 'ovic-addon-toolkit' ),
                    'type'         => Controls_Manager::SWITCHER,
                    'prefix_class' => 'overflow-visible-',
                ]
            );

            $this->add_control(
                $prefix . 'navigation',
                [
                    'label'   => esc_html__( 'Navigation', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'both',
                    'options' => [
                        'both'   => esc_html__( 'Arrows and Dots', 'ovic-addon-toolkit' ),
                        'arrows' => esc_html__( 'Arrows', 'ovic-addon-toolkit' ),
                        'dots'   => esc_html__( 'Dots', 'ovic-addon-toolkit' ),
                        'none'   => esc_html__( 'None', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'arrows',
                [
                    'label'        => esc_html__( 'Arrows style', 'ovic-addon-toolkit' ),
                    'type'         => Controls_Manager::SELECT,
                    'options'      => apply_filters( 'ovic_elementor_carousel_settings_arrows', [
                        '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                    ] ),
                    'default'      => '',
                    'prefix_class' => '',
                ]
            );

            $this->add_control(
                $prefix . 'dots',
                [
                    'label'        => esc_html__( 'Dots style', 'ovic-addon-toolkit' ),
                    'type'         => Controls_Manager::SELECT,
                    'options'      => apply_filters( 'ovic_elementor_carousel_settings_dots', [
                        '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                    ] ),
                    'default'      => '',
                    'prefix_class' => '',
                ]
            );

            $this->add_control(
                $prefix . 'vertical',
                [
                    'label'   => esc_html__( 'Vertical', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                    'default' => 'no',
                ]
            );

            $this->add_control(
                $prefix . 'autoplay',
                [
                    'label'   => esc_html__( 'Autoplay', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'no',
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'autoplay_speed',
                [
                    'label'     => esc_html__( 'Autoplay Speed', 'ovic-addon-toolkit' ),
                    'type'      => Controls_Manager::NUMBER,
                    'min'       => 0,
                    'default'   => 1000,
                    'condition' => [
                        $prefix . 'autoplay' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'infinite',
                [
                    'label'   => esc_html__( 'Infinite Loop', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'no',
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->add_control(
                $prefix . 'speed',
                [
                    'label'   => esc_html__( 'Animation Speed', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => 0,
                    'default' => 500,
                ]
            );

            $this->add_control(
                $prefix . 'direction',
                [
                    'label'   => esc_html__( 'Direction', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'ltr',
                    'options' => [
                        'ltr' => esc_html__( 'Left', 'ovic-addon-toolkit' ),
                        'rtl' => esc_html__( 'Right', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                $prefix . 'tab_responsive',
                [
                    'label' => esc_html__( 'Responsive', 'ovic-addon-toolkit' ),
                ]
            );

            $repeater = new \Elementor\Repeater();

            $repeater->add_control(
                'screen',
                [
                    'label'   => esc_html__( 'Screen', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::TEXT,
                    'default' => 1500,
                ]
            );

            $repeater->add_control(
                'rows',
                [
                    'label'   => esc_html__( 'Rows', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                            '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        ] + $slides_to_show,
                    'default' => 1,
                ]
            );

            $repeater->add_control(
                'show',
                [
                    'label'   => esc_html__( 'Columns', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                            '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                        ] + $slides_to_show,
                    'default' => 4,
                ]
            );

            $repeater->add_control(
                'margin',
                [
                    'label'   => esc_html__( 'Columns Space', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => 0,
                    'default' => 30,
                ]
            );

            $repeater->add_control(
                'navigation',
                [
                    'label'   => esc_html__( 'Navigation', 'ovic-addon-toolkit' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'default' => '',
                    'options' => [
                        'both'   => esc_html__( 'Arrows and Dots', 'ovic-addon-toolkit' ),
                        'arrows' => esc_html__( 'Arrows', 'ovic-addon-toolkit' ),
                        'dots'   => esc_html__( 'Dots', 'ovic-addon-toolkit' ),
                        'none'   => esc_html__( 'None', 'ovic-addon-toolkit' ),
                        ''       => esc_html__( 'Inherit', 'ovic-addon-toolkit' ),
                    ],
                ]
            );

            $repeater->add_control(
                'vertical',
                [
                    'label'   => esc_html__( 'Vertical', 'ovic-addon-toolkit' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => [
                        'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                        'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                    ],
                    'default' => 'no',
                ]
            );

            $this->add_control(
                $prefix . 'responsive',
                [
                    'type'          => Controls_Manager::REPEATER,
                    'fields'        => $repeater->get_controls(),
                    'title_field'   => esc_html__( 'Screen: ', 'ovic-addon-toolkit' ) . '{{{ screen }}}px',
                    'prevent_empty' => false,
                    'default'       => [
                        [
                            'screen'     => 1500,
                            'show'       => 4,
                            'margin'     => 30,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 1200,
                            'show'       => 4,
                            'margin'     => 20,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 992,
                            'show'       => 3,
                            'margin'     => 20,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 768,
                            'show'       => 2,
                            'margin'     => 16,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                        [
                            'screen'     => 480,
                            'show'       => 2,
                            'margin'     => 10,
                            'rows'       => 1,
                            'vertical'   => 'no',
                            'navigation' => '',
                        ],
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            if ( $settings != false ) {
                $this->end_controls_section();
            }
        }
    }
}
