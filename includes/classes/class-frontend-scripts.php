<?php
/**
 * Handle frontend scripts
 *
 * @package Ovic/Classes
 * @version 2.3.0
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend scripts class.
 */
if (!function_exists('Ovic_Frontend_Scripts')):
    class Ovic_Frontend_Scripts
    {
        /**
         * Contains an array of script handles registered by Ovic.
         *
         * @var array
         */
        private static $scripts = array();
        /**
         * Contains an array of script handles registered by Ovic.
         *
         * @var array
         */
        private static $styles = array();
        /**
         * Contains an array of script handles localized by Ovic.
         *
         * @var array
         */
        private static $wp_localize_scripts = array();
        /**
         * Contains an string min file of script handles by Ovic.
         *
         * @var array
         */
        private static $suffix = '';

        /**
         * Hook in methods.
         */
        public static function init()
        {
            /* check for developer mode */
            self::$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

            add_action('wp_enqueue_scripts', array(__CLASS__, 'load_scripts'), 10);
            add_action('wp_print_scripts', array(__CLASS__, 'localize_printed_scripts'), 5);
            add_action('wp_print_footer_scripts', array(__CLASS__, 'localize_printed_scripts'), 5);
            // Elementor style
            add_action('elementor/frontend/after_enqueue_styles', array(__CLASS__, 'after_enqueue_styles'), 10);
        }

        /**
         * Get styles for the frontend.
         *
         * @return array
         */
        public static function get_styles()
        {
            $dependencies = array(
                'animate-css',
            );
            if (OVIC_CORE()->get_config('popup_notice')) {
                $dependencies[] = 'growl';
            }

            return apply_filters('ovic_enqueue_styles', array(
                'ovic-core' => array(
                    'src'     => self::get_asset_url('assets/css/ovic-core'.self::$suffix.'.css'),
                    'deps'    => $dependencies,
                    'version' => OVIC_VERSION,
                    'media'   => 'all',
                    'has_rtl' => false,
                ),
            ));
        }

        /**
         * after styles for the elementor.
         */
        public static function after_enqueue_styles()
        {
            self::enqueue_style('ovic-elementor',
                self::get_asset_url('assets/css/elementor.min.css'),
                array(),
                OVIC_VERSION,
                false
            );
            if (OVIC_CORE()->get_config('elementor_grid')) {
                self::enqueue_style('ovic-elementor-grid',
                    self::get_asset_url('assets/css/elementor-grid.min.css'),
                    array(),
                    OVIC_VERSION,
                    false
                );
            }
        }

        /**
         * Return asset URL.
         *
         * @param  string  $path  Assets path.
         *
         * @return string
         */
        private static function get_asset_url($path)
        {
            return apply_filters('ovic_get_asset_url', plugins_url($path, OVIC_PLUGIN_FILE), $path);
        }

        /**
         * Register a script for use.
         *
         * @param  string  $handle  Name of the script. Should be unique.
         * @param  string  $path  Full URL of the script, or path of the script relative to the WordPress root directory.
         * @param  string[]  $deps  An array of registered script handles this script depends on.
         * @param  string  $version  String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
         * @param  boolean  $in_footer  Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
         *
         * @uses   wp_register_script()
         *
         */
        private static function register_script($handle, $path, $deps = array('jquery'), $version = OVIC_VERSION, $in_footer = true)
        {
            self::$scripts[] = $handle;
            wp_register_script($handle, $path, $deps, $version, $in_footer);
        }

        /**
         * Register and enqueue a script for use.
         *
         * @param  string  $handle  Name of the script. Should be unique.
         * @param  string  $path  Full URL of the script, or path of the script relative to the WordPress root directory.
         * @param  string[]  $deps  An array of registered script handles this script depends on.
         * @param  string  $version  String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
         * @param  boolean  $in_footer  Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
         *
         * @uses   wp_enqueue_script()
         *
         */
        private static function enqueue_script($handle, $path = '', $deps = array('jquery'), $version = OVIC_VERSION, $in_footer = true)
        {
            if (!in_array($handle, self::$scripts, true) && $path) {
                self::register_script($handle, $path, $deps, $version, $in_footer);
            }
            wp_enqueue_script($handle);
        }

        /**
         * Register a style for use.
         *
         * @param  string  $handle  Name of the stylesheet. Should be unique.
         * @param  string  $path  Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
         * @param  string[]  $deps  An array of registered stylesheet handles this stylesheet depends on.
         * @param  string  $version  String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
         * @param  string  $media  The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
         * @param  boolean  $has_rtl  If has RTL version to load too.
         *
         * @uses   wp_register_style()
         *
         */
        private static function register_style($handle, $path, $deps = array(), $version = OVIC_VERSION, $media = 'all', $has_rtl = false)
        {
            self::$styles[] = $handle;
            wp_register_style($handle, $path, $deps, $version, $media);
            if ($has_rtl) {
                wp_style_add_data($handle, 'rtl', 'replace');
            }
        }

        /**
         * Register and enqueue a styles for use.
         *
         * @param  string  $handle  Name of the stylesheet. Should be unique.
         * @param  string  $path  Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
         * @param  string[]  $deps  An array of registered stylesheet handles this stylesheet depends on.
         * @param  string  $version  String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
         * @param  string  $media  The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
         * @param  boolean  $has_rtl  If has RTL version to load too.
         *
         * @uses   wp_enqueue_style()
         *
         */
        private static function enqueue_style($handle, $path = '', $deps = array(), $version = OVIC_VERSION, $media = 'all', $has_rtl = false)
        {
            if (!in_array($handle, self::$styles, true) && $path) {
                self::register_style($handle, $path, $deps, $version, $media, $has_rtl);
            }
            wp_enqueue_style($handle);
        }

        /**
         * Register all Ovic scripts.
         */
        private static function register_scripts()
        {
            $dependencies     = array(
                'jquery',
            );
            $register_scripts = array(
                'growl'     => array(
                    'src'     => self::get_asset_url('assets/3rd-party/growl/growl.min.js'),
                    'deps'    => array(),
                    'version' => '1.3.5',
                ),
                'lazysizes' => array(
                    'src'     => self::get_asset_url('assets/3rd-party/lazysizes/lazysizes.min.js'),
                    'deps'    => array(),
                    'version' => '5.3.2',
                ),
                'slick'     => array(
                    'src'     => self::get_asset_url('assets/3rd-party/slick/slick.min.js'),
                    'deps'    => array(),
                    'version' => '1.0.1',
                ),
                'appear'    => array(
                    'src'     => self::get_asset_url('assets/3rd-party/appear/appear.min.js'),
                    'deps'    => array(),
                    'version' => '1.2.1',
                ),
            );
            if (OVIC_CORE()->get_config('popup_notice')) {
                $dependencies[] = 'growl';
                $dependencies[] = 'wp-util';
            }
            if (OVIC_CORE()->is_lazy()) {
                $dependencies[] = 'lazysizes';
            }
            $register_scripts['ovic-core'] = array(
                'src'     => self::get_asset_url('assets/js/ovic-core'.self::$suffix.'.js'),
                'deps'    => $dependencies,
                'version' => OVIC_VERSION,
            );

            foreach ($register_scripts as $name => $props) {
                self::register_script($name, $props['src'], $props['deps'], $props['version']);
            }
        }

        /**
         * Register all Ovic styles.
         */
        private static function register_styles()
        {
            $register_styles = array(
                'growl'       => array(
                    'src'     => self::get_asset_url('assets/3rd-party/growl/growl.min.css'),
                    'deps'    => array(),
                    'version' => '1.3.5',
                    'media'   => 'all',
                    'has_rtl' => false,
                ),
                'slick'       => array(
                    'src'     => self::get_asset_url('assets/3rd-party/slick/slick.min.css'),
                    'deps'    => array(),
                    'version' => '1.0.1',
                    'media'   => 'all',
                    'has_rtl' => false,
                ),
                'animate-css' => array(
                    'src'     => self::get_asset_url('assets/css/animate.min.css'),
                    'deps'    => array(),
                    'version' => '3.7.0',
                    'media'   => 'all',
                    'has_rtl' => false,
                ),
            );
            foreach ($register_styles as $name => $props) {
                self::register_style($name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl']);
            }
        }

        /**
         * Register/queue frontend scripts.
         */
        public static function load_scripts()
        {
            self::register_scripts();
            self::register_styles();
            // Global frontend scripts.
            if (apply_filters('ovic_enqueue_scripts', true) !== false) {
                self::enqueue_script('ovic-core');
            }
            // CSS Styles.
            $enqueue_styles = self::get_styles();
            if (!empty($enqueue_styles)) {
                foreach ($enqueue_styles as $handle => $args) {
                    if (!isset($args['has_rtl'])) {
                        $args['has_rtl'] = false;
                    }
                    self::enqueue_style($handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl']);
                }
            }
        }

        /**
         * Localize a Ovic script once.
         *
         * @since 2.3.0 this needs less wp_script_is() calls due to https://core.trac.wordpress.org/ticket/28404 being added in WP 4.0.
         *
         * @param  string  $handle  Script handle the data will be attached to.
         */
        private static function localize_script($handle)
        {
            if (!in_array($handle, self::$wp_localize_scripts, true) && wp_script_is($handle)) {
                $data = self::get_script_data($handle);
                if (!$data) {
                    return;
                }
                $name                        = str_replace('-', '_', $handle).'_params';
                self::$wp_localize_scripts[] = $handle;
                wp_localize_script($handle, $name, apply_filters($name, $data));
            }
        }

        /**
         * Return data for script handles.
         *
         * @param  string  $handle  Script handle the data will be attached to.
         *
         * @return array|bool
         */
        private static function get_script_data($handle)
        {
            switch ($handle) {
                case 'ovic-core':

                    $params = array(
                        'ajax_url'                => admin_url('admin-ajax.php', 'relative'),
                        'security'                => wp_create_nonce('ovic_core_frontend'),
                        'ovic_ajax_url'           => OVIC_AJAX::get_endpoint('%%endpoint%%'),
                        'cart_url'                => function_exists('wc_get_cart_url') ? apply_filters('woocommerce_add_to_cart_redirect', wc_get_cart_url(), null) : '#',
                        'cart_redirect_after_add' => get_option('woocommerce_cart_redirect_after_add'),
                        'ajax_single_add_to_cart' => (bool) OVIC_CORE()->get_config('add_to_cart'),
                        'is_preview'              => (bool) OVIC_CORE()->is_elementor_editor(),
                    );
                    if (OVIC_CORE()->get_config('popup_notice')) {
                        /* Get notice popup */
                        ovic_get_template('notices/notice-popup.php');
                        /* add params */
                        $params['growl_notice'] = apply_filters('ovic_growl_notice_params',
                            array(
                                'view_cart'                  => esc_html__('View cart', 'ovic-addon-toolkit'),
                                'added_to_cart_text'         => esc_html__('Product has been added to cart!', 'ovic-addon-toolkit'),
                                'added_to_wishlist_text'     => get_option('yith_wcwl_product_added_text', esc_html__('Product has been added to wishlist!', 'ovic-addon-toolkit')),
                                'removed_from_wishlist_text' => esc_html__('Product has been removed from wishlist!', 'ovic-addon-toolkit'),
                                'wishlist_url'               => function_exists('YITH_WCWL') ? esc_url(YITH_WCWL()->get_wishlist_url()) : '',
                                'browse_wishlist_text'       => get_option('yith_wcwl_browse_wishlist_text', esc_html__('Browse Wishlist', 'ovic-addon-toolkit')),
                                'growl_notice_text'          => esc_html__('Notice!', 'ovic-addon-toolkit'),
                                'removed_cart_text'          => esc_html__('Product Removed', 'ovic-addon-toolkit'),
                                'growl_duration'             => 3000,
                            )
                        );
                    }
                    break;
                default:
                    $params = false;
            }

            return apply_filters('ovic_get_script_data', $params, $handle);
        }

        /**
         * Localize scripts only when enqueued.
         */
        public static function localize_printed_scripts()
        {
            foreach (self::$scripts as $handle) {
                self::localize_script($handle);
            }
        }
    }

    Ovic_Frontend_Scripts::init();
endif;