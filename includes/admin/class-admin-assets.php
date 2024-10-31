<?php
/**
 * Load assets
 *
 * @package     Ovic/Admin
 * @version     2.1.0
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Ovic_Admin_Assets')) :
    /**
     * Ovic_Admin_Assets Class.
     */
    class Ovic_Admin_Assets
    {
        /**
         * Contains an string min file of script handles by Ovic.
         *
         * @var array
         */
        private static $suffix = '';

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            /* check for developer mode */
            self::$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

            add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
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
         * Enqueue styles.
         */
        public function admin_styles()
        {
            $screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';

            // Register admin styles.
            wp_enqueue_style('ovic-admin', self::get_asset_url('assets/admin/ovic-admin.css'), array(), OVIC_VERSION);

            do_action('ovic_admin_style_assets');
        }

        /**
         * Enqueue scripts.
         */
        public function admin_scripts()
        {
            $screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';

            // Enqueue scripts.
            wp_enqueue_script('ovic-admin', self::get_asset_url('assets/admin/ovic-admin.js'), array('jquery'), OVIC_VERSION, true);

            do_action('ovic_admin_script_assets');
        }
    }
endif;

return new Ovic_Admin_Assets();
