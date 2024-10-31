<?php
/**
 * Plugin Name: Ovic Addon Toolkit
 * Plugin URI: https://themeforest.net/user/kutethemes/portfolio
 * Description: The Ovic Addon Toolkit For WordPress Theme Kutethemes.
 * Author: Ovic Team
 * Author URI: https://kutethemes.com/contact-us/
 * Version: 2.6.2
 * WC requires at least: 6.0
 * WC tested up to: 8.6.1
 * Text Domain: ovic-addon-toolkit
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Ovic_Addon_Toolkit')) {
    class Ovic_Addon_Toolkit
    {
        /**
         * @var Ovic_Addon_Toolkit The one true Ovic_Addon_Toolkit
         */
        private static $instance;

        public static function instance()
        {
            /* Include function plugins if not include. */
            if (!function_exists('is_plugin_active')) {
                require_once(ABSPATH.'wp-admin/includes/plugin.php');
            }
            if (!isset(self::$instance) && !(self::$instance instanceof Ovic_Addon_Toolkit)) {
                self::$instance = new Ovic_Addon_Toolkit;
                /* Install plugin */
                self::$instance->setup_constants();
                self::$instance->setup_plugins();
            }

            return self::$instance;
        }

        public function setup_constants()
        {
            // Plugin version.
            if (!defined('OVIC_VERSION')) {
                define('OVIC_VERSION', '2.6.2');
            }
            // Plugin Folder File.
            if (!defined('OVIC_PLUGIN_FILE')) {
                define('OVIC_PLUGIN_FILE', __FILE__);
            }
            // Plugin Base Name.
            if (!defined('OVIC_PLUGIN_BASENAME')) {
                define('OVIC_PLUGIN_BASENAME', plugin_basename(__FILE__));
            }
            // Plugin Folder Path.
            if (!defined('OVIC_PLUGIN_DIR')) {
                define('OVIC_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
            }
            // Plugin Folder URL.
            if (!defined('OVIC_PLUGIN_URL')) {
                define('OVIC_PLUGIN_URL', trailingslashit(plugin_dir_url(__FILE__)));
            }
        }

        public function setup_plugins()
        {
            /* LOAD CORE OVIC */
            require_once OVIC_PLUGIN_DIR.'includes/classes/class-core.php';

            /**
             * Returns the main instance of OVIC_CORE.
             *
             * @return OVIC_CORE
             * @since  1.0
             */
            function OVIC_CORE()
            { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
                return OVIC_CORE::instance();
            }

            // Global for backwards compatibility.
            $GLOBALS['ovic_core'] = OVIC_CORE();
        }
    }
}
if (!function_exists('ovic_addon_toolkit')) {
    function ovic_addon_toolkit()
    {
        return Ovic_Addon_Toolkit::instance();
    }
}
ovic_addon_toolkit();