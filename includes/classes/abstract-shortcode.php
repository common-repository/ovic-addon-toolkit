<?php
/**
 * Ovic Addon Shortcode
 *
 * @author   KHANH
 * @category shortcode
 * @package  Ovic_Addon_Shortcode
 * @since    1.0.0
 */

use Elementor\Plugin;

if ( !class_exists( 'Ovic_Addon_Shortcode' ) ) {
    class Ovic_Addon_Shortcode
    {
        /**
         * List class name.
         *
         * @var  array
         */
        public $classes        = array(
            'shortcode' => '', // Class shortcode   : "Shortcode_{$shortcode}"
            'elementor' => '', // Class elementor   : "Elementor_{$shortcode}"
            'wpbakery'  => '', // Class wpbakery    : "Wpbakery_{$shortcode}"
            'widget'    => '', // Class widget      : "Widget_{$shortcode}"
            'editor'    => '', // Class editor      : "Editor_{$shortcode}"
        );
        public $rtl            = false;
        public $enqueue        = false;
        public $shortcode      = '';            // Shortcode name
        public $default        = array();       // Default param shortcode
        public $path_templates = 'shortcode';   // Name path template
        public $path_assets    = '';            // Name path assets lib
        public $is_woocommerce = false;         // Shortcode run with WooCommerce
        public $builder        = '';            // Class builder

        /**
         * Meta key.
         *
         * @var  string
         */
        protected $meta_key = '_Ovic_VC_Shortcode_Custom_Css';

        /**
         * Shortcode name.
         *
         * @return string
         */
        public function shortcode_name()
        {
            return $this->shortcode;
        }

        /**
         * Generate custom style.
         *
         * @param array $atts parameters.
         *
         * @return  string
         */
        public function style_generate( $atts )
        {
            return '';
        }

        /**
         * Generate content.
         *
         * @param array $atts parameters.
         * @param null $content
         *
         * @return  string
         */
        public function content( $atts, $content = null )
        {
            return '';
        }

        /**
         * Shortcode path.
         *
         * @return string
         */
        public function get_path()
        {
            if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $this->template_name() ) ) {
                return trailingslashit( get_stylesheet_directory_uri() ) . $this->template_name();
            }

            return trailingslashit( get_template_directory() ) . $this->template_name();
        }

        /**
         * Shortcode directory.
         *
         * @param $shortcode
         * @param string $folder
         * @param string $basename
         *
         * @return string
         */
        public function get_dir( $shortcode, $folder = '', $basename = '' )
        {
            $path = $shortcode . '/' . $shortcode . '.php';

            if ( $folder != '' ) {
                $path = $shortcode . '/' . $folder;
                if ( $basename != '' ) {
                    $path .= '/' . $basename;
                }
            }

            if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $this->template_name() . '/' . $path ) ) {
                return trailingslashit( get_stylesheet_directory() ) . $this->template_name() . '/' . $path;
            }

            return trailingslashit( get_template_directory() ) . $this->template_name() . '/' . $path;
        }

        /**
         * Shortcode uri.
         *
         * @param $shortcode
         * @param string $folder
         * @param string $basename
         *
         * @return string
         */
        public function get_uri( $shortcode, $folder = '', $basename = '' )
        {
            $path = $shortcode . '/' . $shortcode . '.php';

            if ( $folder != '' ) {
                $path = $shortcode . '/' . $folder;
                if ( $basename != '' ) {
                    $path .= '/' . $basename;
                }
            }

            if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $this->template_name() . '/' . $path ) ) {
                return trailingslashit( get_stylesheet_directory_uri() ) . $this->template_name() . '/' . $path;
            }

            return trailingslashit( get_template_directory_uri() ) . $this->template_name() . '/' . $path;
        }

        /**
         * Shortcode check WooCommerce active.
         *
         * @return string
         */
        public function is_woocommerce()
        {
            if ( $this->is_woocommerce ) {
                /**
                 * Detect plugin. For use on Front End only.
                 */
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

                // check for plugin using plugin name
                if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                    return false;
                }
            }

            return true;
        }

        /**
         * Install shortcode.
         */
        public function __construct()
        {
            $parent_class = get_parent_class( $this );

            if ( empty( $parent_class ) ) {

                $parent_class = get_class( $this );

                // Include shortcode
                $this->classes = $this->include_shortcode( $parent_class );

                if ( !empty( $this->classes ) ) {

                    // Save inline styles
                    add_action( 'save_post', array( $this, 'update_post' ) );

                    // Add inline styles
                    add_action( 'wp_enqueue_scripts', array( $this, 'inline_styles' ), 999 );

                }

                // Add Post Editor
                add_action( 'plugins_loaded', array( $this, 'add_post_editor' ) );

            }
        }

        /**
         * Add functionality to widgets, elementor, visual composer and so on
         *
         * @return void
         */
        public function add_post_editor()
        {
            // Register Elementor
            add_action( 'elementor/init', array( $this, 'install_Elementor' ) );

            // Register Widgets
            add_action( 'widgets_init', array( $this, 'install_Widgets' ) );

            // Register Editor
            add_action( 'init', array( $this, 'install_Editor' ) );

            // Register Visual Composer
            add_action( 'vc_before_init', array( $this, 'install_WPBakery' ) );
        }

        /**
         * Register shortcode.
         *
         * @param $class_name
         * @param $shortcode
         *
         * @return void
         */
        public function install( $class_name, $shortcode )
        {
            if ( empty( $this->shortcode_name() ) ) {
                return;
            }

            // Create shortcode
            add_shortcode( "{$shortcode}", "{$class_name}::output_html" );

            // Register scripts
            add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 999 );
        }

        /**
         * Register WPBakery.
         *
         * @return void
         */
        public function install_WPBakery()
        {
            foreach ( $this->classes as $shortcode => $classes ) {
                if ( !empty( $classes['wpbakery'] ) ) {
                    $file_path = $this->get_dir( $shortcode, 'config', 'wpbakery.php' );
                    if ( !file_exists( $file_path ) ) {
                        continue;
                    }
                    // Include Widget files
                    require_once( $file_path );
                    // Register shortcode
                    if ( class_exists( $classes['wpbakery'] ) ) {
                        $classes['wpbakery']::visual_composer_include();
                    }
                }
            }
        }

        /**
         * Register Editor.
         *
         * @return void
         */
        public function install_Editor()
        {
            $shortcodes = array();
            $settings   = array(
                'id'           => 'ovic_addon_shortcode',
                'title'        => 'Ovic Shortcode',
                'button_title' => 'shortcode',
            );

            foreach ( $this->classes as $shortcode => $classes ) {
                if ( !empty( $classes['editor'] ) ) {
                    $file_path = $this->get_dir( $shortcode, 'config', 'editor.php' );
                    if ( !file_exists( $file_path ) ) {
                        continue;
                    }
                    // Include Widget files
                    require_once( $file_path );
                    // Register widget
                    if ( class_exists( $classes['editor'] ) ) {
                        $shortcodes[] = $classes['editor']::shortcode_config();
                    }
                }
            }

            if ( !empty( $shortcodes ) ) {
                OVIC_Shortcode::instance( $settings, array(
                    array(
                        'shortcodes' => $shortcodes
                    )
                ) );
            }
        }

        /**
         * Register Widgets.
         *
         * @return void
         */
        public function install_Widgets()
        {
            foreach ( $this->classes as $shortcode => $classes ) {
                if ( !empty( $classes['widget'] ) ) {
                    $file_path = $this->get_dir( $shortcode, 'config', 'widget.php' );
                    if ( !file_exists( $file_path ) ) {
                        continue;
                    }
                    // Include Widget files
                    require_once( $file_path );
                    // Register widget
                    if ( class_exists( $classes['widget'] ) ) {
                        register_widget( $classes['widget'] );
                    }
                }
            }
        }

        /**
         * Register Elementor.
         *
         * @return void
         */
        public function install_Elementor()
        {
            $min_elementor_version = '2.0.0';
            $min_php_version       = '7.0';

            // Check if Elementor installed and activated
            if ( !did_action( 'elementor/loaded' ) ) {
                return;
            }

            // Check for required Elementor version
            if ( !version_compare( ELEMENTOR_VERSION, $min_elementor_version, '>=' ) ) {
                return;
            }

            // Check for required PHP version
            if ( version_compare( PHP_VERSION, $min_php_version, '<' ) ) {
                return;
            }

            // Load remote source
            if ( OVIC_CORE()->get_config( 'remote_source' ) ) {
                $this->register_source();
            }

            // Add control section
            add_action( 'elementor/element/after_section_start', array( $this, 'after_section_start' ), 10, 3 );
            add_action( 'elementor/element/after_section_end', array( $this, 'after_section_end' ), 10, 3 );
            add_action( 'elementor/element/after_add_attributes', array( $this, 'after_add_attributes' ) );
            add_action( 'elementor/widget/before_render_content', array( $this, 'before_render_content' ) );
            add_action( 'elementor/column/print_template', array( $this, 'column_template' ), 10, 2 );
            add_action( 'elementor/section/print_template', array( $this, 'section_template' ), 10, 2 );
            // Add categories
            add_action( 'elementor/elements/categories_registered', array( $this, 'elementor_categories' ) );

            // Add Plugin actions
            add_action( 'elementor/widgets/register', array( $this, 'elementor_widgets' ) );
        }

        /**
         * Register our custom source.
         */
        private function register_source()
        {
            // include remote class
            include_once OVIC_PLUGIN_DIR . 'includes/classes/class-remote-source.php';
            $manager = Elementor\Plugin::instance()->templates_manager;

            // Unregister source with closure binding.
            $unregister_source = function ( $id, $manager ) {
                unset( $manager->_registered_sources[ $id ] );
            };

            $unregister_source->call( $manager, 'remote', $manager );
            $manager->register_source( 'Elementor\TemplateLibrary\Ovic_Source_Remote' );
        }

        /**
         * Render column output in the editor.
         *
         * Used to generate the live preview, using a Backbone JavaScript template.
         *
         * @param $template
         * @param $element
         *
         * @return false|string
         * @since 2.9.0
         * @access protected
         */
        public function column_template( $template, $element )
        {
            ob_start();

            ovic_get_template( 'elementor/column.php', array(
                'shortcode' => $this,
                'template'  => $template,
                'element'   => $element,
            ) );

            return ob_get_clean();
        }

        /**
         * Render section output in the editor.
         *
         * Used to generate the live preview, using a Backbone JavaScript template.
         *
         * @param $template
         * @param $element
         *
         * @return false|string
         * @since 2.9.0
         * @access protected
         */
        public function section_template( $template, $element )
        {
            ob_start();

            ovic_get_template( 'elementor/section.php', array(
                'shortcode' => $this,
                'template'  => $template,
                'element'   => $element,
            ) );

            return ob_get_clean();
        }

        /**
         * Add control before render content.
         *
         * @param $element
         *
         * @return void
         */
        public function before_render_content( $element )
        {
            $settings = $element->get_settings_for_display();

            if ( 'image' === $element->get_name() && $settings['link_to'] !== 'none' ) {
                if ( !OVIC_CORE()->is_elementor_editor() ) {
                    $element->add_render_attribute( 'link', [
                        'class' => $settings['background_effect'],
                    ] );
                }
            }
        }

        /**
         * Add control after add attributes.
         *
         * @param $element
         *
         * @return void
         */
        public function after_add_attributes( $element )
        {
            $settings     = $element->get_settings_for_display();
            $owl_settings = $this->generate_carousel( $settings, 'slides_', false );
            $rows_space   = !empty( $settings['slides_rows_space'] ) ? $settings['slides_rows_space'] : '';

            $is_dom_optimization_active = true;
            if ( version_compare( ELEMENTOR_VERSION, '3.1.0', '>=' ) ) {
                $is_dom_optimization_active = Elementor\Plugin::instance()->experiments->is_feature_active( 'e_dom_optimization' );
            }
            if ( 'image' === $element->get_name() && $settings['link_to'] !== 'none' ) {
                $element->remove_render_attribute( '_wrapper', 'class', $settings['background_effect'] );
            }
            if ( 'column' === $element->get_name() ) {
                if ( !empty( $settings['_use_slide'] ) && $settings['_use_slide'] == 'yes' ) {
                    $element->add_render_attribute( '_widget_wrapper', [
                        'class'      => 'owl-slick ' . $rows_space,
                        'data-slick' => json_encode( $owl_settings ),
                        'style'      => $this->generate_style( $settings, 'slides_' ),
                    ] );
                }
            }
            if ( 'section' === $element->get_name() ) {
                if ( !empty( $settings['_use_slide'] ) && $settings['_use_slide'] == 'yes' ) {
                    $element->add_render_attribute( '_wrapper', [
                        'class'      => 'elementor-section-slide ' . $rows_space,
                        'data-slick' => json_encode( $owl_settings ),
                        'style'      => $this->generate_style( $settings, 'slides_' ),
                    ] );
                }
                if ( $is_dom_optimization_active ) {
                    $element->add_render_attribute( '_wrapper', [
                        'class' => 'elementor-container-wrap',
                    ] );
                }
                if ( !empty( $settings['content_width']['size'] ) ) {
                    $element->add_render_attribute( '_wrapper', 'class', 'elementor-has-width' );
                }
            }
        }

        /**
         * Add control before section end.
         *
         * @param $element
         * @param $section_id
         * @param $args
         *
         * @return void
         */
        public function after_section_end( $element, $section_id, $args )
        {
            /**
             * @var Elementor\Element_Base $element
             * https://code.elementor.com/php-hooks/#elementorelementsection_namesection_idbefore_section_end
             */
            if ( ( 'column' === $element->get_name() && 'layout' === $section_id ) || ( 'section' === $element->get_name() && 'section_layout' === $section_id ) ) {
                if ( OVIC_CORE()->get_config( 'elementor_grid' ) ) {
                    // columns settings
                    $element->start_controls_section(
                        'screen_responsive',
                        [
                            'tab'       => \Elementor\Controls_Manager::TAB_LAYOUT,
                            'label'     => esc_html__( 'Screen Responsive', 'ovic-addon-toolkit' ),
                            'condition' => [
                                '_advanced_width' => 'yes',
                            ],
                        ]
                    );

                    $element->add_control(
                        'screen_laptop',
                        [
                            'label'        => esc_html__( 'Laptop', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SELECT,
                            'default'      => '',
                            'options'      => [
                                ''          => 'inherit',
                                'col-lg-1'  => '1/12',
                                'col-lg-2'  => '2/12',
                                'col-lg-3'  => '3/12',
                                'col-lg-4'  => '4/12',
                                'col-lg-5'  => '5/12',
                                'col-lg-6'  => '6/12',
                                'col-lg-7'  => '7/12',
                                'col-lg-8'  => '8/12',
                                'col-lg-9'  => '9/12',
                                'col-lg-10' => '10/12',
                                'col-lg-11' => '11/12',
                                'col-lg-12' => '12/12',
                                'col-lg-15' => '1/5',
                                'col-lg-25' => '2/5',
                                'col-lg-35' => '3/5',
                                'col-lg-45' => '4/5',
                            ],
                            'prefix_class' => '',
                        ]
                    );

                    $element->add_control(
                        'hidden_laptop',
                        [
                            'label'        => esc_html__( 'Hidden', 'ovic-addon-toolkit' ),
                            'description'  => esc_html__( 'screen resolution < 1500px', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SWITCHER,
                            'default'      => '',
                            'prefix_class' => '',
                            'return_value' => 'elementor-hidden-lg',
                        ]
                    );

                    $element->add_control(
                        'screen_ipad',
                        [
                            'label'        => esc_html__( 'Ipad', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SELECT,
                            'default'      => '',
                            'options'      => [
                                ''          => 'inherit',
                                'col-md-1'  => '1/12',
                                'col-md-2'  => '2/12',
                                'col-md-3'  => '3/12',
                                'col-md-4'  => '4/12',
                                'col-md-5'  => '5/12',
                                'col-md-6'  => '6/12',
                                'col-md-7'  => '7/12',
                                'col-md-8'  => '8/12',
                                'col-md-9'  => '9/12',
                                'col-md-10' => '10/12',
                                'col-md-11' => '11/12',
                                'col-md-12' => '12/12',
                                'col-md-15' => '1/5',
                                'col-md-25' => '2/5',
                                'col-md-35' => '3/5',
                                'col-md-45' => '4/5',
                            ],
                            'prefix_class' => '',
                        ]
                    );

                    $element->add_control(
                        'hidden_ipad',
                        [
                            'label'        => esc_html__( 'Hidden', 'ovic-addon-toolkit' ),
                            'description'  => esc_html__( 'screen resolution < 1200px', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SWITCHER,
                            'default'      => '',
                            'prefix_class' => '',
                            'return_value' => 'elementor-hidden-md',
                        ]
                    );

                    $element->add_control(
                        'screen_landscape',
                        [
                            'label'        => esc_html__( 'landscape Tablet', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SELECT,
                            'default'      => '',
                            'options'      => [
                                ''          => 'inherit',
                                'col-sm-1'  => '1/12',
                                'col-sm-2'  => '2/12',
                                'col-sm-3'  => '3/12',
                                'col-sm-4'  => '4/12',
                                'col-sm-5'  => '5/12',
                                'col-sm-6'  => '6/12',
                                'col-sm-7'  => '7/12',
                                'col-sm-8'  => '8/12',
                                'col-sm-9'  => '9/12',
                                'col-sm-10' => '10/12',
                                'col-sm-11' => '11/12',
                                'col-sm-12' => '12/12',
                                'col-sm-15' => '1/5',
                                'col-sm-25' => '2/5',
                                'col-sm-35' => '3/5',
                                'col-sm-45' => '4/5',
                            ],
                            'prefix_class' => '',
                        ]
                    );

                    $element->add_control(
                        'hidden_landscape',
                        [
                            'label'        => esc_html__( 'Hidden', 'ovic-addon-toolkit' ),
                            'description'  => esc_html__( 'screen resolution < 992px', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SWITCHER,
                            'default'      => '',
                            'prefix_class' => '',
                            'return_value' => 'elementor-hidden-sm',
                        ]
                    );

                    $element->add_control(
                        'screen_portrait',
                        [
                            'label'        => esc_html__( 'Portrait Tablet', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SELECT,
                            'default'      => '',
                            'options'      => [
                                ''          => 'inherit',
                                'col-xs-1'  => '1/12',
                                'col-xs-2'  => '2/12',
                                'col-xs-3'  => '3/12',
                                'col-xs-4'  => '4/12',
                                'col-xs-5'  => '5/12',
                                'col-xs-6'  => '6/12',
                                'col-xs-7'  => '7/12',
                                'col-xs-8'  => '8/12',
                                'col-xs-9'  => '9/12',
                                'col-xs-10' => '10/12',
                                'col-xs-11' => '11/12',
                                'col-xs-12' => '12/12',
                                'col-xs-15' => '1/5',
                                'col-xs-25' => '2/5',
                                'col-xs-35' => '3/5',
                                'col-xs-45' => '4/5',
                            ],
                            'prefix_class' => '',
                        ]
                    );

                    $element->add_control(
                        'hidden_portrait',
                        [
                            'label'        => esc_html__( 'Hidden', 'ovic-addon-toolkit' ),
                            'description'  => esc_html__( 'screen resolution < 768px', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SWITCHER,
                            'default'      => '',
                            'prefix_class' => '',
                            'return_value' => 'elementor-hidden-xs',
                        ]
                    );

                    $element->add_control(
                        'screen_mobile',
                        [
                            'label'        => esc_html__( 'Mobile', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SELECT,
                            'default'      => '',
                            'options'      => [
                                ''          => 'inherit',
                                'col-ts-1'  => '1/12',
                                'col-ts-2'  => '2/12',
                                'col-ts-3'  => '3/12',
                                'col-ts-4'  => '4/12',
                                'col-ts-5'  => '5/12',
                                'col-ts-6'  => '6/12',
                                'col-ts-7'  => '7/12',
                                'col-ts-8'  => '8/12',
                                'col-ts-9'  => '9/12',
                                'col-ts-10' => '10/12',
                                'col-ts-11' => '11/12',
                                'col-ts-12' => '12/12',
                                'col-ts-15' => '1/5',
                                'col-ts-25' => '2/5',
                                'col-ts-35' => '3/5',
                                'col-ts-45' => '4/5',
                            ],
                            'prefix_class' => '',
                        ]
                    );

                    $element->add_control(
                        'hidden_mobile',
                        [
                            'label'        => esc_html__( 'Hidden', 'ovic-addon-toolkit' ),
                            'description'  => esc_html__( 'screen resolution < 480px', 'ovic-addon-toolkit' ),
                            'type'         => \Elementor\Controls_Manager::SWITCHER,
                            'default'      => '',
                            'prefix_class' => '',
                            'return_value' => 'elementor-hidden-ts',
                        ]
                    );

                    $element->end_controls_section();
                }

                // Slide settings
                $element->start_controls_section(
                    'carousel_settings',
                    [
                        'tab'       => \Elementor\Controls_Manager::TAB_LAYOUT,
                        'label'     => esc_html__( 'Carousel Settings', 'ovic-addon-toolkit' ),
                        'condition' => [
                            '_use_slide' => 'yes',
                        ],
                    ]
                );

                $element->start_controls_tabs( 'tabs_slide' );

                $element->start_controls_tab(
                    'tab_slide_settings',
                    [
                        'label' => esc_html__( 'Settings', 'ovic-addon-toolkit' ),
                    ]
                );

                $slides_to_show = range( 1, 10 );
                $slides_to_show = array_combine( $slides_to_show, $slides_to_show );

                $element->add_control(
                    'slides_rows_space',
                    [
                        'label'   => esc_html__( 'Rows space', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
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

                $element->add_control(
                    'slides_rows',
                    [
                        'label'   => esc_html__( 'Rows', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
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

                $element->add_control(
                    'slides_margin',
                    [
                        'label'       => esc_html__( 'Margin', 'ovic-addon-toolkit' ),
                        'type'        => \Elementor\Controls_Manager::NUMBER,
                        'min'         => 0,
                        'placeholder' => '30',
                        'default'     => '30',
                    ]
                );

                $element->add_control(
                    'slides_to_show',
                    [
                        'label'   => esc_html__( 'Slides to Show', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'options' => [
                                '' => esc_html__( 'Default', 'ovic-addon-toolkit' ),
                            ] + $slides_to_show,
                        'default' => '4',
                    ]
                );

                $element->add_control(
                    'slides_navigation',
                    [
                        'label'   => esc_html__( 'Navigation', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'both',
                        'options' => [
                            'both'   => esc_html__( 'Arrows and Dots', 'ovic-addon-toolkit' ),
                            'arrows' => esc_html__( 'Arrows', 'ovic-addon-toolkit' ),
                            'dots'   => esc_html__( 'Dots', 'ovic-addon-toolkit' ),
                            'none'   => esc_html__( 'None', 'ovic-addon-toolkit' ),
                        ],
                    ]
                );

                $element->add_control(
                    'slides_vertical',
                    [
                        'label'   => esc_html__( 'Vertical', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'options' => [
                            'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                            'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                        ],
                        'default' => 'no',
                    ]
                );

                $element->add_control(
                    'slides_autoplay',
                    [
                        'label'   => esc_html__( 'Autoplay', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'no',
                        'options' => [
                            'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                            'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                        ],
                    ]
                );

                $element->add_control(
                    'slides_autoplay_speed',
                    [
                        'label'     => esc_html__( 'Autoplay Speed', 'ovic-addon-toolkit' ),
                        'type'      => \Elementor\Controls_Manager::NUMBER,
                        'min'       => 0,
                        'default'   => 1000,
                        'condition' => [
                            'slides_autoplay' => 'yes',
                        ],
                    ]
                );

                $element->add_control(
                    'slides_infinite',
                    [
                        'label'   => esc_html__( 'Infinite Loop', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'no',
                        'options' => [
                            'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                            'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                        ],
                    ]
                );

                $element->add_control(
                    'slides_speed',
                    [
                        'label'   => esc_html__( 'Animation Speed', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::NUMBER,
                        'min'     => 0,
                        'default' => 500,
                    ]
                );

                $element->add_control(
                    'slides_direction',
                    [
                        'label'   => esc_html__( 'Direction', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'ltr',
                        'options' => [
                            'ltr' => esc_html__( 'Left', 'ovic-addon-toolkit' ),
                            'rtl' => esc_html__( 'Right', 'ovic-addon-toolkit' ),
                        ],
                    ]
                );

                $element->end_controls_tab();

                $element->start_controls_tab(
                    'tab_slide_responsive',
                    [
                        'label' => esc_html__( 'Responsive', 'ovic-addon-toolkit' ),
                    ]
                );

                $repeater = new \Elementor\Repeater();

                $repeater->add_control(
                    'screen',
                    [
                        'label'   => esc_html__( 'Screen', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::TEXT,
                        'default' => 1500,
                    ]
                );

                $repeater->add_control(
                    'show',
                    [
                        'label'   => esc_html__( 'Slides to Show', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
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
                        'type'    => \Elementor\Controls_Manager::SELECT,
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

                $repeater->add_control(
                    'margin',
                    [
                        'label'       => esc_html__( 'Margin', 'ovic-addon-toolkit' ),
                        'type'        => \Elementor\Controls_Manager::NUMBER,
                        'min'         => 0,
                        'placeholder' => '30',
                        'default'     => '30',
                    ]
                );

                $repeater->add_control(
                    'vertical',
                    [
                        'label'   => esc_html__( 'Vertical', 'ovic-addon-toolkit' ),
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'options' => [
                            'yes' => esc_html__( 'Yes', 'ovic-addon-toolkit' ),
                            'no'  => esc_html__( 'No', 'ovic-addon-toolkit' ),
                        ],
                        'default' => 'no',
                    ]
                );

                $element->add_control(
                    'slides_responsive',
                    [
                        'type'          => \Elementor\Controls_Manager::REPEATER,
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

                $element->end_controls_tab();

                $element->end_controls_tabs();

                $element->end_controls_section();
            }
        }

        /**
         * Add control before section start.
         *
         * @param $element
         * @param $section_id
         * @param $args
         *
         * @return void
         */
        public function after_section_start( $element, $section_id, $args )
        {
            /**
             * @var Elementor\Element_Base $element
             * https://code.elementor.com/php-hooks/#elementorelementsection_namesection_idbefore_section_end
             */
            if ( ( 'column' === $element->get_name() && 'layout' === $section_id ) || ( 'section' === $element->get_name() && 'section_layout' === $section_id ) ) {
                $element->add_control(
                    '_use_slide',
                    [
                        'label' => esc_html__( 'Enable Slider', 'ovic-addon-toolkit' ),
                        'type'  => Elementor\Controls_Manager::SWITCHER,
                    ]
                );
                if ( OVIC_CORE()->get_config( 'elementor_grid' ) ) {
                    $element->add_control(
                        '_advanced_width',
                        [
                            'label' => esc_html__( 'Advanced Width', 'ovic-addon-toolkit' ),
                            'type'  => Elementor\Controls_Manager::SWITCHER,
                        ]
                    );
                }
                $element->add_responsive_control(
                    '_custom_width',
                    [
                        'label_block' => true,
                        'type'        => \Elementor\Controls_Manager::TEXT,
                        'label'       => esc_html__( 'Custom Width', 'ovic-addon-toolkit' ),
                        'description' => esc_html__( 'Eg: 100px, 20%, calc( 100% - 30px )', 'ovic-addon-toolkit' ),
                        'selectors'   => [
                            '{{WRAPPER}}' => 'width: {{VALUE}}',
                        ],
                    ]
                );
            }
            if ( 'container' === $element->get_name() && 'section_layout_container' === $section_id ) {
                $element->add_responsive_control(
                    '_custom_width',
                    [
                        'label_block' => true,
                        'type'        => \Elementor\Controls_Manager::TEXT,
                        'label'       => esc_html__( 'Custom Width', 'ovic-addon-toolkit' ),
                        'description' => esc_html__( 'Eg: 100px, 20%, calc( 100% - 30px )', 'ovic-addon-toolkit' ),
                        'selectors'   => [
                            '{{WRAPPER}}' => 'width: {{VALUE}}',
                        ],
                    ]
                );
            }
            if ( 'section_effects' === $section_id ) {
                $config = [
                    'type'         => Elementor\Controls_Manager::SELECT,
                    'label'        => esc_html__( 'Effect', 'ovic-addon-toolkit' ),
                    'options'      => apply_filters( 'ovic_elementor_background_effect', [
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
                    'default'      => 'none',
                    'prefix_class' => '',
                ];
                $element->add_control( 'background_effect', $config );
            }
            if ( 'column' === $element->get_name() && 'section_advanced' === $section_id ) {
                $element->add_responsive_control(
                    '_order',
                    [
                        'label'     => __( 'Order', 'ovic-addon-toolkit' ),
                        'type'      => Elementor\Controls_Manager::NUMBER,
                        'min'       => 0,
                        'selectors' => [
                            '{{WRAPPER}}' => 'order: {{VALUE}};',
                        ],
                    ]
                );
            }
            if ( '_section_position' == $section_id ) {
                $element->add_control(
                    '_position_overflow',
                    [
                        'label'     => esc_html__( 'Overflow', 'ovic-addon-toolkit' ),
                        'type'      => Elementor\Controls_Manager::SWITCHER,
                        'selectors' => [
                            '{{WRAPPER}}' => 'overflow: hidden;',
                        ],
                    ]
                );
                $element->add_responsive_control(
                    '_position_size',
                    [
                        'label'      => esc_html__( 'Position size', 'ovic-addon-toolkit' ),
                        'type'       => Elementor\Controls_Manager::DIMENSIONS,
                        'size_units' => [ 'px', '%' ],
                        'selectors'  => [
                            'body {{WRAPPER}}' => 'top: {{TOP}}{{UNIT}}!important;right: {{RIGHT}}{{UNIT}}!important;bottom: {{BOTTOM}}{{UNIT}}!important;left: {{LEFT}}{{UNIT}}!important;',
                        ],
                        'condition'  => [
                            '_position!' => '',
                        ],
                    ]
                );
            }
        }

        /**
         * Register Elementor categories.
         *
         * @return void
         * @throws Exception
         */
        public function elementor_categories()
        {
            $elementsManager = \Elementor\Plugin::instance()->elements_manager;
            $elementsManager->add_category( 'ovic',
                [
                    'title'  => esc_html__( 'Kutethemes', 'ovic-addon-toolkit' ),
                    'icon'   => 'eicon-wordpress',
                    'active' => true,
                ]
            );
        }

        /**
         * Register Elementor widgets.
         *
         * @return void
         * @throws Exception
         */
        public function elementor_widgets( $widgets_manager )
        {
            include_once 'abstract-elementor.php';

            foreach ( $this->classes as $shortcode => $classes ) {
                if ( !empty( $classes['elementor'] ) ) {
                    $file_path = $this->get_dir( $shortcode, 'config', 'elementor.php' );
                    if ( !file_exists( $file_path ) ) {
                        continue;
                    }
                    // Include Widget files
                    require_once( $file_path );
                    // Register widget
                    if ( class_exists( $classes['elementor'] ) ) {
                        $widgets_manager->register( new $classes['elementor']() );
                    }
                }
            }
        }

        /**
         * Shortcode enqueue name.
         *
         * @return string
         */
        public function enqueue_name()
        {
            return apply_filters( 'ovic_enqueue_templates_shortcode',
                "shortcode_enqueue_{$this->shortcode_name()}",
                $this
            );
        }

        /**
         * Shortcode template.
         *
         * @return string
         */
        public function template_name()
        {
            return apply_filters(
                'ovic_addon_templates_shortcode', $this->path_templates, $this->shortcode_name()
            );
        }

        /**
         * Shortcode get template.
         *
         * @param $template_name
         * @param array $args
         * @param string $template_path
         * @param string $default_path
         *
         * @return void
         */
        public function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' )
        {
            $default_path  = "{$this->get_path()}/";
            $template_name = "{$this->shortcode_name()}/{$template_name}";
            $template_path = "{$this->template_name()}";

            ovic_get_template( $template_name, $args, $template_path, $default_path );
        }

        /**
         * Add inline styles.
         *
         * @return  void
         */
        public function inline_styles()
        {
            $css     = '';
            $page_id = 0;

            if ( is_front_page() || is_home() ) {
                $page_id = get_queried_object_id();
            } elseif ( is_singular() ) {
                if ( !$page_id ) {
                    $page_id = get_the_ID();
                }
            } elseif ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
                $page_id = get_option( 'woocommerce_shop_page_id' );
            }

            if ( $page_id != 0 && !OVIC_CORE()->is_elementor( $page_id ) ) {
                $css .= get_post_meta( $page_id, $this->meta_key, true );
            }

            if ( !empty( $css ) ) {
                wp_add_inline_style( 'ovic-core', $css );
            }
        }

        /**
         * Get uri assets.
         *
         * @param $extension
         *
         * @return  string
         */
        public function get_asset_url( $extension )
        {
            $shortcode   = $this->shortcode_name();
            $path_assets = !empty( $this->path_assets ) ? "{$this->path_assets}/{$shortcode}" : "{$shortcode}";
            $file_path   = $this->get_dir( $shortcode, $path_assets . ".{$extension}" );
            $file_uri    = $this->get_uri( $shortcode, $path_assets . ".{$extension}" );

            if ( is_file( $file_path ) ) {
                $min       = '';
                $file_path = str_replace( ".{$extension}", '', $file_path );
                $file_uri  = str_replace( ".{$extension}", '', $file_uri );

                if ( is_file( $file_path . ".min.{$extension}" ) ) {
                    $min = '.min';
                }
                if ( is_rtl() && $this->rtl == true ) {
                    return $file_uri . "-rtl{$min}.{$extension}";
                }

                return $file_uri . "{$min}.{$extension}";
            }

            return '';
        }

        /**
         * Register enqueue assets shortcode.
         *
         * @return  void
         */
        public function scripts()
        {
            $style = $this->get_asset_url( 'css' );
            if ( !empty( $style ) ) {
                wp_register_style( $this->enqueue_name(), esc_url( $style ), array(), OVIC_VERSION );
                if ( $this->enqueue == true || OVIC_CORE()->is_elementor_editor() ) {
                    wp_enqueue_style( $this->enqueue_name() );
                }
            }
            $script = $this->get_asset_url( 'js' );
            if ( !empty( $script ) ) {
                wp_register_script( $this->enqueue_name(), esc_url( $script ), array(), OVIC_VERSION, true );
                if ( $this->enqueue == true || OVIC_CORE()->is_elementor_editor() ) {
                    wp_enqueue_script( $this->enqueue_name() );
                }
            }
        }

        /**
         * Include shortcode.
         *
         * @param $parent_class
         *
         * @return array
         */
        public function include_shortcode( $parent_class )
        {
            $classes = array();
            $parent  = glob( trailingslashit( get_template_directory() ) . $this->template_name() . '/*/*.php' );
            $child   = glob( trailingslashit( get_stylesheet_directory() ) . $this->template_name() . '/*/*.php' );
            $path    = array_merge( $parent, $child );

            foreach ( $path as $shortcode ) {
                $filename   = wp_basename( dirname( $shortcode ) );
                $sc_name    = implode( '_', array_map( 'ucfirst', explode( '_', $filename ) ) );
                $class_name = "Shortcode_{$sc_name}";
                $config     = dirname( $shortcode ) . '/config';
                $shortcode  = $this->get_dir( $filename );

                if ( !class_exists( $class_name ) ) {

                    // Include shortcode
                    include_once $shortcode;

                    // Install shortcode
                    if ( class_exists( $class_name ) ) {

                        $instance = new $class_name();

                        if ( $instance->is_woocommerce() && is_subclass_of( $instance, $parent_class ) ) {
                            if ( method_exists( $class_name, 'install' ) ) {
                                $instance->install( $class_name, $filename );
                            }
                            unset( $instance );
                            // Push class name
                            $classes[ $filename ]['shortcode'] = $class_name;
                            // Push config
                            foreach ( glob( $config . '/*.php' ) as $config ) {
                                $config_name = str_replace( '.php', '', wp_basename( $config ) );
                                $class_name  = ucfirst( $config_name );
                                $class_name  = "{$class_name}_{$sc_name}";
                                // Push config to class
                                $classes[ $filename ][ $config_name ] = $class_name;
                            }
                        }

                    }

                }

            }

            return $classes;
        }

        /**
         * Replace and save custom css to post meta.
         *
         * @param int $post_id
         *
         * @return  void
         */
        public function update_post( $post_id )
        {
            if ( !wp_is_post_revision( $post_id ) && !OVIC_CORE()->is_elementor( $post_id ) ) {
                // Set and replace content.
                $post = $this->replace_post( $post_id );
                if ( $post ) {
                    // Generate custom CSS.
                    $css = $this->build_custom_css( $post->post_content );
                    // Update post to post meta.
                    $this->save_post( $post );
                    // Update save CSS to post meta.
                    $this->save_css_postmeta( $post_id, $css );

                    do_action( 'ovic_addon_save_post', $post_id );
                } else {
                    $this->save_css_postmeta( $post_id, '' );
                }
            }
        }

        /**
         * Change shortcode id.
         *
         * @param $post
         *
         * @return  void
         */
        public function save_post( $post )
        {
            // Update post content.
            global $wpdb;

            $wpdb->update(
                $wpdb->posts,
                array(
                    'post_content' => $post->post_content,    // string
                ),
                array(
                    'ID' => $post->ID,
                ),
                array( '%s' ),
                array( '%d' )
            );
            // Update post cache.
            wp_cache_replace( $post->ID, $post, 'posts' );
        }

        /**
         * Replace shortcode used in a post with real content.
         *
         * @param int $post_id Post ID.
         *
         * @return  WP_Post object or null.
         */
        public function replace_post( $post_id )
        {
            // Get post.
            $post = get_post( $post_id );
            if ( $post ) {
                $post->post_content = preg_replace_callback(
                    '/(ovic_vc_custom_id)="[^"]+"/',
                    array( $this, 'replace_post_callback' ),
                    $post->post_content
                );
            }

            return $post;
        }

        /**
         * Replace shortcode id.
         *
         * @param $matches
         *
         * @return string
         */
        function replace_post_callback( $matches )
        {
            // Generate a random string to use as element ID.
            $id = 'ovic_vc_custom_' . uniqid();

            return $matches[1] . '="' . $id . '"';
        }

        /**
         * Update extra post meta.
         *
         * @param int $post_id Post ID.
         * @param string $css Custom CSS.
         *
         * @return  void
         */
        public function save_css_postmeta( $post_id, $css )
        {
            if ( $post_id && $this->meta_key ) {
                if ( !$css ) {
                    delete_post_meta( $post_id, $this->meta_key );
                } else {
                    update_post_meta( $post_id, $this->meta_key, preg_replace( '/[\t\r\n]/', '', $css ) );
                }
            }
        }

        /**
         * Parse shortcode custom css string.
         *
         * @param string $content
         *
         * @return  string
         */
        public function build_custom_css( $content )
        {
            $css = '';

            if ( class_exists( 'WPBMap' ) ) {
                WPBMap::addAllMappedShortcodes();
            }
            if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes ) ) {
                foreach ( $shortcodes[2] as $index => $tag ) {
                    $classes   = $this->classes;
                    $list_tags = array_keys( $classes );
                    if ( in_array( $tag, $list_tags ) ) {
                        $class_name = $classes[ $tag ]['shortcode'];
                        $atts       = shortcode_parse_atts( trim( $shortcodes[3][ $index ] ) );
                        // Get inline style
                        $instance = new $class_name();

                        if ( method_exists( $class_name, 'style_generate' ) ) {
                            $css .= $instance->style_generate( $atts );
                        }

                        unset( $instance );
                    }
                }
                if ( !empty( $shortcodes[5] ) ) {
                    foreach ( $shortcodes[5] as $shortcode_content ) {
                        $css .= $this->build_custom_css( $shortcode_content );
                    }
                }
            }

            return $css;
        }

        /**
         * Generate classes.
         *
         * @param array $atts parameters.
         * @param array $classes
         *
         * @return  string
         */
        public function main_class( $atts, $classes = array() )
        {
            if ( defined( 'VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG' ) ) {
                $classes[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', $this->shortcode_name(), $atts );
            }

            if ( !empty( $atts['el_class'] ) ) {
                $classes[] = $atts['el_class'];
            }

            if ( !empty( $atts['ovic_vc_custom_id'] ) ) {
                $classes[] = $atts['ovic_vc_custom_id'];
            }

            $classes = !empty( $classes ) ? implode( ' ', $classes ) : '';

            return apply_filters( 'ovic_shortcode_main_class', $classes, $this->shortcode_name(), $atts );
        }

        public function is_boolean( $string )
        {
            $string = strtolower( $string );

            return ( in_array( $string, array( "true", "false", "1", "0", "yes", "no" ), true ) );
        }

        public function preg_replace_callback( $matches )
        {
            $name = str_replace( array( '"', ':' ), array( '', '' ), $matches[0] );

            if ( is_numeric( $name ) || $this->is_boolean( $name ) === true ) {
                return ":{$name}";
            }

            return $matches[0];
        }

        /**
         * Generate carousel.
         *
         * @param array $atts
         * @param string $prefix
         * @param bool $echo
         *
         * @return array|string
         */
        public function generate_carousel( $atts, $prefix = 'slides_', $echo = true )
        {
            if ( !empty( $atts['carousel'] ) && is_array( $atts['carousel'] ) ) {
                $data_slick = preg_replace_callback(
                    '/:"[^"]+"/',
                    array( $this, 'preg_replace_callback' ),
                    json_encode( $atts['carousel'] )
                );
                if ( $echo == false ) {
                    return json_decode( $data_slick, true );
                }

                return htmlspecialchars( ' data-slick=' . $data_slick . ' style=' . $this->generate_style( $atts['carousel'], '' ) . ' ' );
            }

            $responsive = array();
            $settings   = array(
                'slidesToShow' => 4,
                'infinite'     => false,
                'arrows'       => false,
            );
            if ( isset( $atts["{$prefix}rows"] ) ) {
                $settings['rows'] = (int)$atts["{$prefix}rows"];
            }
            if ( isset( $atts["{$prefix}to_show"] ) ) {
                $settings['slidesToShow'] = (int)$atts["{$prefix}to_show"];
            }
            if ( isset( $atts["{$prefix}margin"] ) ) {
                $settings['slidesMargin'] = (int)$atts["{$prefix}margin"];
            }
            if ( isset( $atts["{$prefix}autoplay_speed"] ) ) {
                $settings['autoplaySpeed'] = (int)$atts["{$prefix}autoplay_speed"];
            }
            if ( isset( $atts["{$prefix}speed"] ) ) {
                $settings['speed'] = (int)$atts["{$prefix}speed"];
            }
            if ( isset( $atts["{$prefix}autoplay"] ) && $atts["{$prefix}autoplay"] == 'yes' ) {
                $settings['autoplay'] = true;
            }
            if ( isset( $atts["{$prefix}infinite"] ) && $atts["{$prefix}infinite"] == 'yes' ) {
                $settings['infinite'] = true;
            }
            if ( isset( $atts["{$prefix}vertical"] ) && $atts["{$prefix}vertical"] == 'yes' ) {
                $settings['vertical'] = true;
            }
            if ( isset( $atts["{$prefix}direction"] ) && $atts["{$prefix}direction"] == 'rtl' ) {
                $settings['rtl'] = true;
            }
            if ( !empty( $atts["{$prefix}navigation"] ) ) {
                if ( $atts["{$prefix}navigation"] == 'both' || $atts["{$prefix}navigation"] == 'arrows' ) {
                    $settings['arrows'] = true;
                }
                if ( $atts["{$prefix}navigation"] == 'both' || $atts["{$prefix}navigation"] == 'dots' ) {
                    $settings['dots'] = true;
                }
            }
            if ( !empty( $atts["{$prefix}responsive"] ) ) {
                foreach ( $atts["{$prefix}responsive"] as $tab ) {
                    $vertical           = false;
                    $data               = array();
                    $data['breakpoint'] = (int)$tab['screen'];
                    $data['settings']   = array();

                    if ( !empty( $tab['show'] ) ) {
                        $data['settings']['slidesToShow'] = (int)$tab['show'];
                    }
                    if ( !empty( $tab['margin'] ) ) {
                        $data['settings']['slidesMargin'] = (int)$tab['margin'];
                    }
                    if ( !empty( $tab['rows'] ) ) {
                        $data['settings']['rows'] = (int)$tab['rows'];
                    }
                    if ( !empty( $tab['vertical'] ) && $tab['vertical'] == 'yes' ) {
                        $vertical = true;
                    }
                    if ( !empty( $tab['navigation'] ) ) {
                        if ( $tab['navigation'] == 'arrows' ) {
                            $data['settings']['arrows'] = true;
                            $data['settings']['dots']   = false;
                        }
                        if ( $tab['navigation'] == 'dots' ) {
                            $data['settings']['dots']   = true;
                            $data['settings']['arrows'] = false;
                        }
                        if ( $tab['navigation'] == 'both' ) {
                            $data['settings']['dots']   = true;
                            $data['settings']['arrows'] = true;
                        }
                        if ( $tab['navigation'] == 'none' ) {
                            $data['settings']['dots']   = false;
                            $data['settings']['arrows'] = false;
                        }
                    }
                    $data['settings']['vertical'] = $vertical;

                    $responsive[] = $data;
                }
            }

            $data_slick = array_merge( $settings, array(
                    'responsive' => array_values( $responsive )
                )
            );

            if ( $echo == false ) {
                $data_slick = preg_replace_callback(
                    '/:"[^"]+"/',
                    array( $this, 'preg_replace_callback' ),
                    json_encode( $data_slick )
                );

                return json_decode( $data_slick, true );
            }

            return htmlspecialchars( ' data-slick=' . json_encode( $data_slick ) . ' style=' . $this->generate_style( $atts, $prefix ) . ' ' );
        }

        /**
         * Generate style.
         *
         * @param array $atts
         *
         * @param string $prefix
         *
         * @return string
         */
        public function generate_style( $atts, $prefix = 'slides_' )
        {
            $style = '';

            if ( isset( $atts["{$prefix}to_show"] ) ) {
                $style .= '--show:' . (int)$atts["{$prefix}to_show"] . ';';
            }
            if ( isset( $atts["{$prefix}margin"] ) ) {
                $style .= '--margin:' . (int)$atts["{$prefix}margin"] . 'px;';
            }
            if ( isset( $atts["slidesToShow"] ) ) {
                $style .= '--show:' . (int)$atts["slidesToShow"] . ';';
            }
            if ( isset( $atts["slidesMargin"] ) ) {
                $style .= '--margin:' . (int)$atts["slidesMargin"] . 'px;';
            }

            if ( !empty( $atts["{$prefix}responsive"] ) ) {
                foreach ( $atts["{$prefix}responsive"] as $tab ) {
                    $breakpoint = !empty( $tab['breakpoint'] ) ? (int)$tab['breakpoint'] : (int)$tab['screen'];
                    $show       = !empty( $tab['settings']['slidesToShow'] ) ? (int)$tab['settings']['slidesToShow'] : (int)@$tab['show'];
                    $margin     = !empty( $tab['settings']['slidesMargin'] ) ? (int)$tab['settings']['slidesMargin'] : (int)@$tab['margin'];

                    if ( $breakpoint <= 1500 && $breakpoint > 1200 ) {
                        $style .= '--show-laptop:' . $show . ';--margin-laptop:' . $margin . 'px;';
                    } elseif ( $breakpoint <= 1200 && $breakpoint > 992 ) {
                        $style .= '--show-ipad:' . $show . ';--margin-ipad:' . $margin . 'px;';
                    } elseif ( $breakpoint <= 992 && $breakpoint > 768 ) {
                        $style .= '--show-landscape:' . $show . ';--margin-landscape:' . $margin . 'px;';
                    } elseif ( $breakpoint <= 768 && $breakpoint > 480 ) {
                        $style .= '--show-portrait:' . $show . ';--margin-portrait:' . $margin . 'px;';
                    } elseif ( $breakpoint <= 480 ) {
                        $style .= '--show-mobile:' . $show . ';--margin-mobile:' . $margin . 'px;';
                    }
                }
            }

            return $style;
        }

        /**
         * Generate boostrap.
         *
         * @param array $atts
         *
         * @param string $prefix
         *
         * @return string
         */
        public function generate_boostrap( $atts, $prefix = 'grid_' )
        {
            $classes = array();

            if ( !empty( $atts[ $prefix . 'rows_space' ] ) ) {
                $classes[] = $atts[ $prefix . 'rows_space' ];
            }
            if ( !empty( $atts[ $prefix . 'desktop' ] ) ) {
                $classes[] = $atts[ $prefix . 'desktop' ];
            }
            if ( !empty( $atts[ $prefix . 'laptop' ] ) ) {
                $classes[] = $atts[ $prefix . 'laptop' ];
            }
            if ( !empty( $atts[ $prefix . 'ipad' ] ) ) {
                $classes[] = $atts[ $prefix . 'ipad' ];
            }
            if ( !empty( $atts[ $prefix . 'landscape' ] ) ) {
                $classes[] = $atts[ $prefix . 'landscape' ];
            }
            if ( !empty( $atts[ $prefix . 'portrait' ] ) ) {
                $classes[] = $atts[ $prefix . 'portrait' ];
            }
            if ( !empty( $atts[ $prefix . 'mobile' ] ) ) {
                $classes[] = $atts[ $prefix . 'mobile' ];
            }

            return implode( ' ', $classes );
        }

        /**
         * Add link render attributes.
         *
         * Used to add link tag attributes to a specific HTML element.
         *
         * The HTML link tag is represented by the element parameter. The `url_control` parameter
         * needs to be an array of link settings in the same format they are set by Elementor's URL control.
         *
         * Example usage:
         *
         * `$this->add_link_attributes( 'button', $settings['link'] );`
         *
         * @param array $url_control Array of link settings.
         *
         * @param bool $string
         *
         * @return array|mixed|string
         * @since 2.8.0
         * @access public
         */
        public function add_link_attributes( $url_control, $string = false )
        {
            $attribute  = '';
            $attributes = [];

            if ( !empty( $url_control['url'] ) ) {
                $attributes['href'] = $url_control['url'];
            } else {
                $attributes['href'] = '#';
            }

            if ( !empty( $url_control['is_external'] ) ) {
                $attributes['target'] = '_blank';
            }

            if ( !empty( $url_control['nofollow'] ) ) {
                $attributes['rel'] = 'nofollow';
            }

            if ( !empty( $url_control['class'] ) ) {
                $attributes['class'] = $url_control['class'];
            }

            if ( !empty( $url_control['custom_attributes'] ) && class_exists( 'Elementor\Utils' ) ) {
                // Custom URL attributes should come as a string of comma-delimited key|value pairs
                $attributes = array_merge( $attributes, Elementor\Utils::parse_custom_attributes( $url_control['custom_attributes'] ) );
            }

            if ( $string == true && !empty( $attributes ) ) {

                foreach ( $attributes as $key => $value ) {
                    if ( $key == 'href' ) {
                        $value = esc_url( $value );
                    } else {
                        $value = str_replace( ' ', '&#x20;', $value );
                    }
                    $attribute .= " {$key}={$value} ";
                }

                return $attribute;

            }

            return $attributes;
        }

        /**
         * Generate content.
         *
         * @param array $atts parameters.
         * @param null $content
         *
         * @return  string
         */
        public static function output_html( $atts, $content = null )
        {
            $classname = static::class;
            $instance  = new $classname();

            if ( method_exists( $classname, 'content' ) ) {
                if ( !empty( $instance->default ) ) {
                    $atts = wp_parse_args( $atts, $instance->default );
                }
                $content = $instance->content( $atts, $content );
            }

            unset( $instance );

            return apply_filters( $classname, $content, $atts );
        }

        /* == Visual composer. == */

        /**
         * @param $css_animation
         *
         * @return string
         */
        public function getCSSAnimation( $css_animation )
        {
            $output = '';
            if ( '' !== $css_animation && 'none' !== $css_animation ) {
                wp_enqueue_script( 'vc_waypoints' );
                wp_enqueue_style( 'vc_animate-css' );
                $output = ' wpb_animate_when_almost_visible wpb_' . $css_animation . ' ' . $css_animation;
            }

            return $output;
        }

        /* do_action( 'vc_enqueue_font_icon_element', $font ); // hook to custom do enqueue style */
        public function constructIcon( $section )
        {
            $class = 'vc_tta-icon';

            if ( function_exists( 'vc_icon_element_fonts_enqueue' ) ) {
                vc_icon_element_fonts_enqueue( $section['i_type'] );
                if ( isset( $section[ 'i_icon_' . $section['i_type'] ] ) ) {
                    $class .= ' ' . $section[ 'i_icon_' . $section['i_type'] ];
                } else {
                    $class .= ' fa fa-adjust';
                }
            }

            return '<i class="' . $class . '"></i>';
        }

        public static function convertAttributesToNewProgressBar( $atts )
        {
            if ( isset( $atts['values'] ) && strlen( $atts['values'] ) > 0 && function_exists( 'vc_param_group_parse_atts' ) ) {
                $values = vc_param_group_parse_atts( $atts['values'] );
                if ( !is_array( $values ) ) {
                    $temp        = explode( ',', $atts['values'] );
                    $paramValues = array();
                    foreach ( $temp as $value ) {
                        $data               = explode( '|', $value );
                        $colorIndex         = 2;
                        $newLine            = array();
                        $newLine['percent'] = isset( $data[0] ) ? $data[0] : 0;
                        $newLine['title']   = isset( $data[1] ) ? $data[1] : '';
                        if ( isset( $data[1] ) && preg_match( '/^\d{1,3}\%$/', $data[1] ) ) {
                            $colorIndex         += 1;
                            $newLine['percent'] = (float)str_replace( '%', '', $data[1] );
                            $newLine['title']   = isset( $data[2] ) ? $data[2] : '';
                        }
                        if ( isset( $data[ $colorIndex ] ) ) {
                            $newLine['customcolor'] = $data[ $colorIndex ];
                        }
                        $paramValues[] = $newLine;
                    }
                    $atts['values'] = urlencode( json_encode( $paramValues ) );
                }
            }

            return $atts;
        }

        function get_all_attributes( $tag, $text )
        {
            preg_match_all( '/' . get_shortcode_regex() . '/s', $text, $matches );
            $out               = array();
            $shortcode_content = array();
            if ( isset( $matches[5] ) ) {
                $shortcode_content = $matches[5];
            }
            if ( isset( $matches[2] ) ) {
                $i = 0;
                foreach ( (array)$matches[2] as $key => $value ) {
                    if ( $tag === $value ) {
                        $out[ $i ]            = shortcode_parse_atts( $matches[3][ $key ] );
                        $out[ $i ]['content'] = $matches[5][ $key ];
                    }
                    $i++;
                }
            }

            return $out;
        }
    }

    new Ovic_Addon_Shortcode();
}