<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access directly.
/**
 *
 * Field: repeater
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Field_repeater')) {
    class OVIC_Field_repeater extends OVIC_Fields
    {

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {

            $args = wp_parse_args($this->field, array(
                'max'          => 0,
                'min'          => 0,
                'button_title' => '<i class="fas fa-plus-circle"></i>',
            ));

            $unique_id = (!empty($this->unique)) ? '['.$this->field['id'].']' : $this->field['id'];

            if (preg_match('/'.preg_quote('['.$this->field['id'].']').'/', $this->unique)) {

                echo '<div class="ovic-notice ovic-notice-danger">'.esc_html__('Error: Field ID conflict.', 'ovic-addon-toolkit').'</div>';

            } else {

                echo $this->field_before();

                echo '<div class="ovic-repeater-item ovic-repeater-hidden">';
                echo '<div class="ovic-repeater-content">';
                foreach ($this->field['fields'] as $field) {

                    $field_default = (isset($field['default'])) ? $field['default'] : '';
                    $field_unique  = (!empty($this->unique)) ? $this->unique.'['.$this->field['id'].'][0]' : $this->field['id'].'[0]';

                    echo OVIC::field($field, $field_default, '___'.$field_unique, 'field/repeater');

                }
                echo '</div>';
                echo '<div class="ovic-repeater-helper">';
                echo '<div class="ovic-repeater-helper-inner">';
                echo '<i class="ovic-repeater-sort fas fa-arrows-alt"></i>';
                echo '<i class="ovic-repeater-clone far fa-clone"></i>';
                echo '<i class="ovic-repeater-remove ovic-confirm fas fa-times" data-confirm="'.esc_html__('Are you sure to delete this item?', 'ovic-addon-toolkit').'"></i>';
                echo '</div>';
                echo '</div>';
                echo '</div>';

                echo '<div class="ovic-repeater-wrapper ovic-data-wrapper" data-field-id="'.esc_attr($unique_id).'" data-max="'.esc_attr($args['max']).'" data-min="'.esc_attr($args['min']).'">';

                if (!empty($this->value) && is_array($this->value)) {

                    $num = 0;

                    foreach ($this->value as $key => $value) {

                        echo '<div class="ovic-repeater-item">';
                        echo '<div class="ovic-repeater-content">';
                        foreach ($this->field['fields'] as $field) {

                            $field_unique = (!empty($this->unique)) ? $this->unique.'['.$this->field['id'].']['.$num.']' : $this->field['id'].'['.$num.']';
                            $field_value  = (isset($field['id']) && isset($this->value[$key][$field['id']])) ? $this->value[$key][$field['id']] : '';

                            echo OVIC::field($field, $field_value, $field_unique, 'field/repeater');

                        }
                        echo '</div>';
                        echo '<div class="ovic-repeater-helper">';
                        echo '<div class="ovic-repeater-helper-inner">';
                        echo '<i class="ovic-repeater-sort fas fa-arrows-alt"></i>';
                        echo '<i class="ovic-repeater-clone far fa-clone"></i>';
                        echo '<i class="ovic-repeater-remove ovic-confirm fas fa-times" data-confirm="'.esc_html__('Are you sure to delete this item?', 'ovic-addon-toolkit').'"></i>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';

                        $num++;

                    }

                }

                echo '</div>';

                echo '<div class="ovic-repeater-alert ovic-repeater-max">'.esc_html__('You cannot add more.', 'ovic-addon-toolkit').'</div>';
                echo '<div class="ovic-repeater-alert ovic-repeater-min">'.esc_html__('You cannot remove more.', 'ovic-addon-toolkit').'</div>';
                echo '<a href="#" class="button button-primary ovic-repeater-add">'.$args['button_title'].'</a>';

                echo $this->field_after();

            }

        }

        public function enqueue()
        {

            if (!wp_script_is('jquery-ui-sortable')) {
                wp_enqueue_script('jquery-ui-sortable');
            }

        }

    }
}
