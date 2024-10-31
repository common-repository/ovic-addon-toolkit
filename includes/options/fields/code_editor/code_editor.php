<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access directly.
/**
 *
 * Field: code_editor
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Field_code_editor')) {
    class OVIC_Field_code_editor extends OVIC_Fields
    {
        public $version = '5.60.0';
        public $cdn_url = 'https://cdn.jsdelivr.net/npm/codemirror@';

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {
            $default_settings = array(
                'tabSize'     => 2,
                'lineNumbers' => true,
                'theme'       => 'default',
                'mode'        => 'htmlmixed',
                'cdnURL'      => $this->cdn_url.$this->version,
            );

            $settings = (!empty($this->field['settings'])) ? $this->field['settings'] : array();
            $settings = wp_parse_args($settings, $default_settings);

            echo $this->field_before();
            echo '<textarea name="'.esc_attr($this->field_name()).'"'.$this->field_attributes().' data-editor="'.esc_attr(json_encode($settings)).'">'.$this->value.'</textarea>';
            echo $this->field_after();
        }

        public function enqueue()
        {
            // Do not loads CodeMirror in revslider page.
            if (OVIC::disable_scripts()) {
                return;
            }

            if (!wp_script_is('ovic-codemirror')) {
                wp_enqueue_script('ovic-codemirror', $this->cdn_url.$this->version.'/lib/codemirror.min.js', array('ovic-options'), $this->version, true);
                wp_enqueue_script('ovic-codemirror-loadmode', $this->cdn_url.$this->version.'/addon/mode/loadmode.min.js', array('ovic-codemirror'), $this->version, true);
            }

            if (!wp_style_is('ovic-codemirror')) {
                wp_enqueue_style('ovic-codemirror', $this->cdn_url.$this->version.'/lib/codemirror.min.css', array(), $this->version);
            }
        }
    }
}
