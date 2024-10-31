<?php
/**
 * Adds settings to the permalinks admin settings page
 *
 * @class       Ovic_Admin_Permalink_Settings
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin
 * @version     2.3.0
 */

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Ovic_Admin_Permalink_Settings Class.
 */
if ( ! class_exists('Ovic_Admin_Permalink_Settings')) {
    class Ovic_Admin_Permalink_Settings
    {
        /**
         * Permalink settings.
         *
         * @var array
         */
        private $permalinks = array();

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            $this->settings_init();
            $this->settings_save();
        }

        /**
         * Init our settings.
         */
        public function settings_init()
        {
            if (class_exists('Woocommerce')) {
                add_settings_field(
                    'ovic_taxonomy_brand_slug',
                    esc_html__('Product brand base', 'ovic-addon-toolkit'),
                    array(&$this, 'product_brand_slug_input'),
                    'permalink',
                    'optional'
                );
            }

            $this->permalinks = ovic_get_permalink_structure();
        }

        /**
         * Show a slug input box.
         */
        public function product_brand_slug_input()
        {
            ?>
            <input name="ovic_taxonomy_brand_slug" type="text" class="regular-text code"
                   value="<?php echo esc_attr($this->permalinks['brand_base']); ?>"
                   placeholder="<?php echo esc_html_x('product-brand', 'slug', 'ovic-addon-toolkit') ?>"/>
            <?php
        }

        /**
         * Save the settings.
         */
        public function settings_save()
        {
            if ( ! is_admin()) {
                return;
            }

            // We need to save the options ourselves; settings api does not trigger save for the permalinks page
            if (isset($_POST['permalink_structure']) || isset($_POST['ovic_taxonomy_brand_slug'])) {
                $permalinks = (array) get_option('ovic_addon_permalinks', array());
                if (class_exists('Woocommerce')) {
                    // Cat and tag bases
                    $permalinks['brand_base'] = ovic_sanitize_permalink(wp_unslash($_POST['ovic_taxonomy_brand_slug']));
                }
                update_option('ovic_addon_permalinks', $permalinks);
            }
        }
    }

    return new Ovic_Admin_Permalink_Settings();
}
