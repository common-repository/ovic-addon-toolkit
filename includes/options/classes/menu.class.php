<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Menu Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Menu')) {
    class OVIC_Menu extends OVIC_Abstract
    {
        // constants
        public $options  = array();
        public $errors   = array();
        public $abstract = 'menu';

        // run menu construct
        public function __construct($options)
        {
            // Get options menu
            $this->options = apply_filters('ovic_options_menu', $options);

            // Actions menu
            add_action('wp_nav_menu_item_custom_fields', array(&$this, 'add_meta_box_content'), 10, 5);
            add_action('wp_update_nav_menu_item', array(&$this, 'update_custom_nav_fields'), 10, 3);

            // wp enqueue for typography and output css
            parent::__construct();
        }

        // instance
        public static function instance($options = array())
        {
            return new self($options);
        }

        // add menu content
        public function add_meta_box_content($item_id, $item, $depth, $args, $id)
        {
            echo '<div class="ovic ovic-nav-menu-options">';
            echo '<div class="ovic-fields">';
            foreach ($this->options as $field) {
                $key         = $field['id'];
                $field['id'] = "{$field['id']}[$item_id]";
                echo OVIC::field($field,
                    get_post_meta($item_id, $key, true)
                );
            }
            echo '</div>';
            echo '</div>';
        }

        // save menu
        public function update_custom_nav_fields($menu_id, $menu_item_db_id, $args)
        {
            if (!empty($this->options)) {
                foreach ($this->options as $field) {
                    // Check if element is properly sent
                    if (!empty($_REQUEST[$field['id']][$menu_item_db_id])) {
                        update_post_meta($menu_item_db_id, $field['id'], $_REQUEST[$field['id']][$menu_item_db_id]);
                    }
                }
            }
        }
    }
}
