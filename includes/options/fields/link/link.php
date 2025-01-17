<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access directly.
/**
 *
 * Field: link
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Field_link')) {
    class OVIC_Field_link extends OVIC_Fields
    {

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {

            $args = wp_parse_args($this->field, array(
                'add_title'    => esc_html__('Add Link', 'ovic-addon-toolkit'),
                'edit_title'   => esc_html__('Edit Link', 'ovic-addon-toolkit'),
                'remove_title' => esc_html__('Remove Link', 'ovic-addon-toolkit'),
            ));

            $default_values = array(
                'url'    => '',
                'text'   => '',
                'target' => '',
            );

            $value = wp_parse_args($this->value, $default_values);

            $hidden = (!empty($value['url']) || !empty($value['url']) || !empty($value['url'])) ? ' hidden' : '';

            $maybe_hidden = (empty($hidden)) ? ' hidden' : '';

            echo $this->field_before();

            echo '<textarea readonly="readonly" class="ovic--link hidden"></textarea>';

            echo '<div class="'.esc_attr($maybe_hidden).'"><div class="ovic--result">'.sprintf('{url:"%s", text:"%s", target:"%s"}', $value['url'], $value['text'], $value['target']).'</div></div>';

            echo '<input type="text" name="'.esc_attr($this->field_name('[url]')).'" value="'.esc_attr($value['url']).'"'.$this->field_attributes(array('class' => 'ovic--url hidden')).' />';
            echo '<input type="text" name="'.esc_attr($this->field_name('[text]')).'" value="'.esc_attr($value['text']).'" class="ovic--text hidden" />';
            echo '<input type="text" name="'.esc_attr($this->field_name('[target]')).'" value="'.esc_attr($value['target']).'" class="ovic--target hidden" />';

            echo '<a href="#" class="button button-primary ovic--add'.esc_attr($hidden).'">'.$args['add_title'].'</a> ';
            echo '<a href="#" class="button ovic--edit'.esc_attr($maybe_hidden).'">'.$args['edit_title'].'</a> ';
            echo '<a href="#" class="button ovic-warning-primary ovic--remove'.esc_attr($maybe_hidden).'">'.$args['remove_title'].'</a>';

            echo $this->field_after();

        }

        public function enqueue()
        {

            if (!wp_script_is('wplink')) {
                wp_enqueue_script('wplink');
            }

            if (!wp_script_is('jquery-ui-autocomplete')) {
                wp_enqueue_script('jquery-ui-autocomplete');
            }

            add_action('admin_print_footer_scripts', array(&$this, 'add_wp_link_dialog'));

        }

        public function add_wp_link_dialog()
        {

            if (!class_exists('_WP_Editors')) {
                require_once ABSPATH.WPINC.'/class-wp-editor.php';
            }

            wp_print_styles('editor-buttons');

            _WP_Editors::wp_link_dialog();

        }

    }
}
