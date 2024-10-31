<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: sortable
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Field_sortable')) {
    class OVIC_Field_sortable extends OVIC_Fields
    {
        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {
            if (!wp_script_is('jquery-ui-sortable')) {
                wp_enqueue_script('jquery-ui-sortable');
            }

            echo $this->field_before();

            echo '<div class="ovic--sortable">';

            $pre_sortby = array();
            $pre_fields = array();

            // Add array-keys to defined fields for sort by
            foreach ($this->field['fields'] as $key => $field) {
                $pre_fields[$field['id']] = $field;
            }

            // Set sort by by saved-value or default-value
            if (!empty($this->value)) {
                foreach ($this->value as $key => $value) {
                    $pre_sortby[$key] = $pre_fields[$key];
                }
            } else {
                foreach ($pre_fields as $key => $value) {
                    $pre_sortby[$key] = $value;
                }
            }

            foreach ($pre_sortby as $key => $field) {
                echo '<div class="ovic--sortable-item">';

                echo '<div class="ovic--sortable-content">';

                $field_default = (isset($this->field['default'][$key])) ? $this->field['default'][$key] : '';
                $field_value   = (isset($this->value[$key])) ? $this->value[$key] : $field_default;
                $unique_id     = (!empty($this->unique)) ? $this->unique.'['.$this->field['id'].']' : $this->field['id'];

                echo OVIC::field($field, $field_value, $unique_id, 'field/sortable');

                echo '</div>';

                echo '<div class="ovic--sortable-helper"><i class="fa fa-arrows"></i></div>';

                echo '</div>';
            }

            echo '</div>';

            echo $this->field_after();
        }
    }
}
