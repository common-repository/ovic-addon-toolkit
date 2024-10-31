<?php
/**
 * Ovic Admin
 *
 * @class    Ovic_Admin
 * @author   KuteThemes
 * @category Admin
 * @package  Ovic/Admin
 * @version  1.0.1
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Ovic_Admin class.
 */
if (!class_exists('Ovic_Admin')) {
    class Ovic_Admin
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            add_action('init', array($this, 'includes'));
            add_action('admin_init', array($this, 'plugin_activate'));
            add_action('current_screen', array($this, 'conditional_includes'));
            /* add plugin meta */
            add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
            add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            add_filter('network_admin_plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
        }

        /**
         * Dependent plugins
         */
        public function plugin_activate()
        {
            $dependents = array(
                'boutique-toolkit/boutique-toolkit.php',
                'ovic-toolkit/ovic-toolkit.php',
                'mosa-toolkit/toolkit.php',
                'voka-toolkit/toolkit.php',
            );

            foreach ($dependents as $dependent) {
                if (is_plugin_active($dependent)) {
                    deactivate_plugins($dependent);
                }
            }
        }

        /**
         * Include any classes we need within admin.
         */
        public function includes()
        {
            require_once dirname(__FILE__).'/dashboard/dashboard.php';
            include_once dirname(__FILE__).'/class-admin-assets.php';
            include_once dirname(__FILE__).'/admin-functions.php';
        }

        /**
         * Include admin files conditionally.
         */
        public function conditional_includes()
        {
            if (!$screen = get_current_screen()) {
                return;
            }
            switch ($screen->id) {
                case 'options-permalink':
                    include_once dirname(__FILE__).'/class-admin-permalink.php';
                    break;
            }
        }

        /**
         * Show row meta on the plugin screen.
         *
         * @param $actions
         * @param $plugin_file
         *
         * @return array
         */
        public function plugin_row_meta($actions, $plugin_file)
        {
            if (OVIC_PLUGIN_BASENAME === $plugin_file) {
                $row_meta = array(
                    'donate' => '<a href="https://paypal.me/hoangkhanh92">Buy me a coffee</a>',
                );

                return array_merge($actions, $row_meta);
            }

            return (array) $actions;
        }

        /**
         * Show action links on the plugin screen.
         *
         * @param $actions
         * @param $plugin_file
         *
         * @return array
         */
        public static function plugin_action_links($actions, $plugin_file)
        {
            if (OVIC_PLUGIN_BASENAME === $plugin_file) {
                $action_links = array(
                    'settings' => '<a href="'.admin_url('/admin.php?page=ovic_addon-dashboard&tab=settings').'" aria-label="'.esc_attr__('View Ovic settings', 'ovic-addon-toolkit').'">'.esc_html__('Settings', 'ovic-addon-toolkit').'</a>',
                );

                return array_merge($action_links, $actions);
            }

            return (array) $actions;
        }
    }
}

return new Ovic_Admin();
