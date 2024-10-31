<?php
/**
 *
 * Setup Framework Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC')) {
    class OVIC
    {
        /**
         *
         * instance
         * @access private
         * @var OVIC
         */
        private static $instance = null;

        /**
         * constants
         */
        public static $version = '2.1.6';
        public static $file    = '';
        public static $dir     = null;
        public static $url     = null;
        public static $min     = null;

        // instance
        public static function instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

            /* check for developer mode */
            self::$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

            // set constants
            self::constants();

            // include files
            self::includes();

            return self::$instance;
        }

        // Initialize
        public function __construct($file = __FILE__)
        {
            // Set file constant
            self::$file = $file;

            // init action
            do_action('ovic_init');

            // enqueue scripts
            add_action('admin_enqueue_scripts', array($this, 'register_scripts'), 20);

            // enqueue scripts
            add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'), 20);

            // enqueue scripts elementor
            add_action('elementor/editor/before_enqueue_scripts', array($this, 'register_scripts'));
            add_action('elementor/editor/after_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        public static function include_plugin_file($file, $load = true)
        {
            $path     = '';
            $file     = ltrim($file, '/');
            $override = apply_filters('ovic_override_framework', 'options-override');
            if (file_exists(get_parent_theme_file_path($override.'/'.$file))) {
                $path = get_parent_theme_file_path($override.'/'.$file);
            } elseif (file_exists(get_theme_file_path($override.'/'.$file))) {
                $path = get_theme_file_path($override.'/'.$file);
            } elseif (file_exists(self::$dir.'/'.$override.'/'.$file)) {
                $path = self::$dir.'/'.$override.'/'.$file;
            } elseif (file_exists(self::$dir.'/'.$file)) {
                $path = self::$dir.'/'.$file;
            }
            if (!empty($path) && !empty($file) && $load) {
                global $wp_query;
                if (is_object($wp_query) && function_exists('load_template')) {
                    load_template($path, true);
                } else {
                    require_once($path);
                }
            } else {
                return self::$dir.'/'.$file;
            }

            return self::$dir;
        }

        // Sanitize dirname
        public static function sanitize_dirname($dirname)
        {
            return preg_replace('/[^A-Za-z]/', '', $dirname);
        }

        // Set plugin url
        public static function include_plugin_url($file)
        {
            return self::$url.'/'.ltrim($file, '/');
        }

        // Define constants
        public static function constants()
        {
            /// We need this path-finder code for set URL of framework
            $dirname        = str_replace('//', '/', wp_normalize_path(dirname(dirname(self::$file))));
            $theme_dir      = str_replace('//', '/', wp_normalize_path(get_parent_theme_file_path()));
            $plugin_dir     = str_replace('//', '/', wp_normalize_path(WP_PLUGIN_DIR));
            $plugin_dir     = str_replace('/opt/bitnami', '/bitnami', $plugin_dir);
            $located_plugin = (preg_match('#'.self::sanitize_dirname($plugin_dir).'#', self::sanitize_dirname($dirname))) ? true : false;
            $directory      = ($located_plugin) ? $plugin_dir : $theme_dir;
            $directory_uri  = ($located_plugin) ? WP_PLUGIN_URL : get_parent_theme_file_uri();
            $foldername     = str_replace($directory, '', $dirname);
            $protocol_uri   = (is_ssl()) ? 'https' : 'http';
            $directory_uri  = set_url_scheme($directory_uri, $protocol_uri);

            self::$dir = $dirname;
            self::$url = $directory_uri.$foldername;
        }

        // Includes options files
        public static function includes()
        {
            // includes helpers
            self::include_plugin_file('functions/helpers.php');
            self::include_plugin_file('functions/deprecated.php');
            self::include_plugin_file('functions/fallback.php');
            self::include_plugin_file('functions/actions.php');
            self::include_plugin_file('functions/sanitize.php');
            self::include_plugin_file('functions/validate.php');

            // includes abstract
            self::include_plugin_file('classes/abstract.class.php');
            self::include_plugin_file('classes/fields.class.php');

            // includes classes
            self::include_plugin_file('classes/options.class.php');
            self::include_plugin_file('classes/metabox.class.php');
            self::include_plugin_file('classes/taxonomy.class.php');
            self::include_plugin_file('classes/profile.class.php');
            self::include_plugin_file('classes/shortcode.class.php');
            self::include_plugin_file('classes/customize.class.php');

            // includes classes
            do_action('ovic_options_includes');
        }

        //
        // Enqueue frontend scripts.
        public function frontend_scripts()
        {
            if (OVIC::get_config('fontawesome') == 'fa4') {
                wp_enqueue_style('font-awesome', OVIC::include_plugin_url('assets/lib/font-awesome/css/font-awesome-4.7.0.min.css'), null, '4.7.0');
            } else {
                wp_enqueue_style('ovic-fa5', OVIC::include_plugin_url('assets/lib/font-awesome/css/font-awesome-5.15.3.min.css'), null, '5.15.3');
                if (OVIC::get_config('fa4_support') == 1) {
                    wp_enqueue_style('ovic-fa5-v4-shims', OVIC::include_plugin_url('assets/lib/font-awesome/css/v4-shims.min.css'), null, '5.15.3');
                }
            }
        }

        /**
         * Get the config.
         *
         * @param  $key
         * @param  bool  $default
         *
         * @return string
         */
        public static function get_config($key = '', $default = false)
        {
            $args   = array(
                'fa4_support' => false,
                'fontawesome' => 'fa4',
            );
            $key    = trim($key);
            $config = get_option('ovic_addon_settings');
            $config = wp_parse_args($config, $args);

            if (empty($key)) {
                return $config;
            }

            if (!empty($config[$key])) {
                return $config[$key];
            }

            return $default;
        }

        public static function disable_scripts()
        {
            $exclude = [
                'dokan',
                'revslider',
                'revslider_navigation',
            ];

            if (in_array(ovic_get_var('page'), $exclude)) {
                return true;
            }

            return false;
        }

        /**
         * is Elementor editor?
         *
         * @return bool
         */
        public static function is_elementor_editor()
        {
            if (class_exists('Elementor\Plugin')) {
                if (Elementor\Plugin::$instance->preview->is_preview_mode() || Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    return true;
                }
            }

            return false;
        }

        //
        // Register admin scripts.
        public static function enqueue_scripts()
        {
            if (OVIC::disable_scripts()) {
                return;
            }

            /* admin utilities */
            wp_enqueue_media();

            /* wp color picker */
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');

            /* Font Awesome */
            wp_enqueue_style('ovic-fa5', OVIC::include_plugin_url('assets/lib/font-awesome/css/font-awesome-5.15.3.min.css'), null, '5.15.3');
            wp_enqueue_style('ovic-fa5-v4-shims', OVIC::include_plugin_url('assets/lib/font-awesome/css/v4-shims.min.css'), null, '5.15.3');

            /* Main style */
            wp_enqueue_style('ovic-options');
            wp_enqueue_style('ovic-options-custom');

            /* Main RTL styles */
            if (is_rtl()) {
                wp_enqueue_style('ovic-options-rtl');
            }

            /* Main scripts */
            wp_enqueue_script('ovic-options');

            /* Main variables */
            wp_localize_script('ovic-options', 'ovic_vars', array(
                    'color_palette' => apply_filters('ovic_color_palette', array()),
                    'i18n'          => array(
                        // global localize
                        'confirm'             => esc_html__('Are you sure?', 'ovic-addon-toolkit'),
                        'reset_notification'  => esc_html__('Restoring options.', 'ovic-addon-toolkit'),
                        'import_notification' => esc_html__('Importing options.', 'ovic-addon-toolkit'),

                        // chosen localize
                        'typing_text'         => esc_html__('Please enter %s or more characters', 'ovic-addon-toolkit'),
                        'searching_text'      => esc_html__('Searching...', 'ovic-addon-toolkit'),
                        'no_results_text'     => esc_html__('No results match', 'ovic-addon-toolkit'),
                    ),
                    'is_preview'    => (bool) OVIC::is_elementor_editor(),
                )
            );

            // Icon modal
            add_action('admin_footer', 'ovic_set_icons');
            add_action('elementor/editor/footer', 'ovic_set_icons');
            add_action('customize_controls_print_footer_scripts', 'ovic_set_icons');

            do_action('ovic_options_enqueue');

        }

        //
        // Enqueue admin scripts.
        public function register_scripts()
        {
            /* Main style */
            wp_register_style('ovic-options',
                OVIC::include_plugin_url('assets/css/style'.self::$min.'.css'),
                array(), OVIC::$version
            );
            wp_register_style('ovic-options-custom',
                OVIC::include_plugin_url('assets/css/custom'.self::$min.'.css'),
                array(), OVIC::$version
            );

            /* Main RTL styles */
            wp_register_style('ovic-options-rtl',
                OVIC::include_plugin_url('assets/css/style-rtl'.self::$min.'.css'),
                array(), OVIC::$version
            );

            /**
             * Main scripts
             * http://codestarthemes.com/plugins/codestar-framework/wp-content/plugins/codestar-framework/assets/js/plugins.js
             * http://codestarthemes.com/plugins/codestar-framework/wp-content/plugins/codestar-framework/assets/js/main.js
             */
            wp_register_script('ovic-plugins',
                OVIC::include_plugin_url('assets/js/plugins'.self::$min.'.js'),
                array('jquery'), OVIC::$version, true
            );
            wp_register_script('ovic-options',
                OVIC::include_plugin_url('assets/js/main'.self::$min.'.js'),
                array('jquery', 'ovic-plugins'), OVIC::$version, true
            );
        }

        // Include field
        public static function maybe_include_field($type = '')
        {
            if (!class_exists("OVIC_Field_{$type}") && class_exists('OVIC_Fields')) {
                self::include_plugin_file("fields/{$type}/{$type}.php");
            }
        }

        //
        // Add a new framework field
        public static function field($field = array(), $value = '', $unique = '', $where = '', $parent = '')
        {
            // language for fields
            $languages = ovic_language_defaults();

            // Check for unallow fields
            if (!empty($field['_notice'])) {
                $field_type       = $field['type'];
                $field            = array();
                $field['content'] = sprintf(
                    esc_html__('Ooops! This field type (%s) can not be used here, yet.', 'ovic-addon-toolkit'),
                    '<strong>'.$field_type.'</strong>'
                );
                $field['type']    = 'notice';
                $field['class']   = 'warning';
            }

            $output     = '';
            $depend     = '';
            $classname  = 'OVIC_Field_'.$field['type'];
            $unique     = (!empty($unique)) ? $unique : '';
            $wrap_class = (!empty($field['class'])) ? ' '.$field['class'] : '';
            $el_class   = (!empty($field['title'])) ? sanitize_title($field['title']) : 'no-title';
            $hidden     = (!empty($field['show_only_language']) && ($field['show_only_language'] != $languages['current'])) ? ' hidden' : '';
            $is_pseudo  = (!empty($field['pseudo'])) ? ' ovic-pseudo-field' : '';
            $field_type = (!empty($field['type'])) ? $field['type'] : '';

            if (!empty($field['dependency'])) {

                $dependency = $field['dependency'];
                $hidden     = ' hidden';

                if (is_array($dependency[0])) {
                    $data_controller = implode('|', array_column($dependency, 0));
                    $data_condition  = implode('|', array_column($dependency, 1));
                    $data_value      = implode('|', array_column($dependency, 2));
                    $data_global     = implode('|', array_column($dependency, 3));
                } else {
                    $data_controller = (!empty($dependency[0])) ? $dependency[0] : '';
                    $data_condition  = (!empty($dependency[1])) ? $dependency[1] : '';
                    $data_value      = (!empty($dependency[2])) ? $dependency[2] : '';
                    $data_global     = (!empty($dependency[3])) ? $dependency[3] : '';
                }

                $depend .= ' data-controller="'.$data_controller.'"';
                $depend .= ' data-condition="'.$data_condition.'"';
                $depend .= ' data-value="'.$data_value.'"';
                $depend .= (!empty($data_global)) ? ' data-depend-global="true"' : '';
            }
            $output .= '<div class="ovic-field ovic-field-key-'.$el_class.' ovic-field-'.$field_type.$is_pseudo.$wrap_class.$hidden.'"'.$depend.'>';

            if (!empty($field['title'])) {
                $subtitle = (!empty($field['subtitle'])) ? '<p class="ovic-text-subtitle">'.$field['subtitle'].'</p>' : '';
                $subtitle = (!empty($field['desc'])) ? '<p class="ovic-text-subtitle">'.$field['desc'].'</p>' : $subtitle;
                $output   .= '<div class="ovic-title"><h4>'.wp_kses_post($field['title']).'</h4>'.$subtitle.'</div>';
            }

            $output .= (!empty($field['title'])) ? '<div class="ovic-fieldset">' : '';

            $value = (!isset($value) && isset($field['default'])) ? $field['default'] : $value;
            $value = (isset($field['value'])) ? $field['value'] : $value;

            self::maybe_include_field($field['type']);

            if (class_exists($classname)) {
                ob_start();
                $instance = new $classname($field, $value, $unique, $where, $parent);
                if (method_exists($classname, 'enqueue')) {
                    $instance->enqueue();
                }
                $instance->render();
                $output .= ob_get_clean();
            } else {
                $output .= '<p>'.esc_html__('This field class is not available!', 'ovic-addon-toolkit').'</p>';
            }

            $output .= (!empty($field['title'])) ? '</div>' : '';
            $output .= '<div class="clear"></div>';
            $output .= '</div>';

            return $output;
        }

        //
        // Create custom field class
        public static function createField($field = array(), $is_ajax = false)
        {
            if (!isset($field['type'])) {
                return '';
            }
            $output    = '';
            $onload    = ($is_ajax) ? ' ovic-onload' : '';
            $output    .= '<div class="ovic-field-custom'.$onload.'">';
            $classname = 'OVIC_Field_'.$field['type'];
            self::maybe_include_field($field['type']);
            if (class_exists($classname) && method_exists($classname, 'enqueue')) {
                $instance = new $classname($field);
                if (method_exists($classname, 'enqueue')) {
                    $instance->enqueue();
                }
                unset($instance);
            }
            $output .= self::field($field);
            $output .= '</div>';

            return $output;
        }
    }

    OVIC::instance();
}