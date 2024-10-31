<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Select Preview
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Field_select_preview')) {
    class OVIC_Field_select_preview extends OVIC_Fields
    {
        public function __construct($field, $value = '', $unique = '', $where = '')
        {
            parent::__construct($field, $value, $unique, $where);
        }

        public function render()
        {
            echo $this->field_before();

            if (isset($this->field['options'])) {
                echo '<div class="container-select_preview">';
                $options = $this->field['options'];
                $options = (is_array($options)) ? $options : array_filter($this->field_data($options));

                echo '<select name="'.$this->field_name().'"'.$this->field_attributes().' class="ovic_select_preview">';
                echo (isset($this->field['default_option'])) ? '<option value="">'.$this->field['default_option'].'</option>' : '';
                if (!empty($options)) {
                    foreach ($options as $key => $value) {
                        $data_url = !empty($value['url']) ? $value['url'] : 'javascript:void(0);';
                        echo '<option data-preview="'.esc_attr($value['preview']).'" data-url="'.esc_attr($data_url).'" value="'.esc_attr($key).'" '.selected($this->value, $key).'>'.$value['title'].'</option>';
                    }
                }
                echo '</select>';

                $url     = 'javascript:void(0);';
                $target  = '_self';
                $preview = '#';
                if (!empty($this->field['options'][$this->value]['preview'])) {
                    $preview = $this->field['options'][$this->value]['preview'];
                }
                if (!empty($this->field['options'][$this->value]['url'])) {
                    $url    = $this->field['options'][$this->value]['url'];
                    $target = '_blank';
                }
                echo '<div class="image-preview" style="margin-top:10px;display:inline-block;width:100%;">';
                echo '<a href="'.esc_url($url).'" target="'.esc_attr($target).'" style="display:inline-block;"><img src="'.esc_url($preview).'" alt=""></a>';
                echo '</div>';
                echo '</div>';
            }

            echo $this->field_after();
        }
    }
}