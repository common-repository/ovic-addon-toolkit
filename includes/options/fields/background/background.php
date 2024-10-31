<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access directly.
/**
 *
 * Field: background
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Field_background')) {
    class OVIC_Field_background extends OVIC_Fields
    {

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {

            $args = wp_parse_args($this->field, array(
                'background_color'              => true,
                'background_image'              => true,
                'background_position'           => true,
                'background_repeat'             => true,
                'background_attachment'         => true,
                'background_size'               => true,
                'background_origin'             => false,
                'background_clip'               => false,
                'background_blend_mode'         => false,
                'background_gradient'           => false,
                'background_gradient_color'     => true,
                'background_gradient_direction' => true,
                'background_image_preview'      => true,
                'background_auto_attributes'    => false,
                'background_image_library'      => 'image',
                'background_image_placeholder'  => esc_html__('No background selected', 'ovic-addon-toolkit'),
            ));

            $default_value = array(
                'background-color'              => '',
                'background-image'              => '',
                'background-position'           => '',
                'background-repeat'             => '',
                'background-attachment'         => '',
                'background-size'               => '',
                'background-origin'             => '',
                'background-clip'               => '',
                'background-blend-mode'         => '',
                'background-gradient-color'     => '',
                'background-gradient-direction' => '',
            );

            $default_value = (!empty($this->field['default'])) ? wp_parse_args($this->field['default'],
                $default_value) : $default_value;

            $this->value = wp_parse_args($this->value, $default_value);

            echo $this->field_before();

            echo '<div class="ovic--background-colors">';

            //
            // Background Color
            if (!empty($args['background_color'])) {

                echo '<div class="ovic--color">';

                echo (!empty($args['background_gradient'])) ? '<div class="ovic--title">'.esc_html__('From',
                        'ovic-addon-toolkit').'</div>' : '';

                echo OVIC::field(array(
                    'id'      => 'background-color',
                    'type'    => 'color',
                    'default' => $default_value['background-color'],
                ), $this->value['background-color'], $this->field_name(), 'field/background');

                echo '</div>';

            }

            //
            // Background Gradient Color
            if (!empty($args['background_gradient_color']) && !empty($args['background_gradient'])) {

                echo '<div class="ovic--color">';

                echo (!empty($args['background_gradient'])) ? '<div class="ovic--title">'.esc_html__('To',
                        'ovic-addon-toolkit').'</div>' : '';

                echo OVIC::field(array(
                    'id'      => 'background-gradient-color',
                    'type'    => 'color',
                    'default' => $default_value['background-gradient-color'],
                ), $this->value['background-gradient-color'], $this->field_name(), 'field/background');

                echo '</div>';

            }

            //
            // Background Gradient Direction
            if (!empty($args['background_gradient_direction']) && !empty($args['background_gradient'])) {

                echo '<div class="ovic--color">';

                echo (!empty($args['background_gradient'])) ? '<div class="ovic---title">'.esc_html__('Direction',
                        'ovic-addon-toolkit').'</div>' : '';

                echo OVIC::field(array(
                    'id'      => 'background-gradient-direction',
                    'type'    => 'select',
                    'options' => array(
                        ''          => esc_html__('Gradient Direction', 'ovic-addon-toolkit'),
                        'to bottom' => esc_html__('&#8659; top to bottom', 'ovic-addon-toolkit'),
                        'to right'  => esc_html__('&#8658; left to right', 'ovic-addon-toolkit'),
                        '135deg'    => esc_html__('&#8664; corner top to right', 'ovic-addon-toolkit'),
                        '-135deg'   => esc_html__('&#8665; corner top to left', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-gradient-direction'], $this->field_name(), 'field/background');

                echo '</div>';

            }

            echo '</div>';

            //
            // Background Image
            if (!empty($args['background_image'])) {

                echo '<div class="ovic--background-image">';

                echo OVIC::field(array(
                    'id'          => 'background-image',
                    'type'        => 'media',
                    'class'       => 'ovic-assign-field-background',
                    'library'     => $args['background_image_library'],
                    'preview'     => $args['background_image_preview'],
                    'placeholder' => $args['background_image_placeholder'],
                    'attributes'  => array('data-depend-id' => $this->field['id']),
                ), $this->value['background-image'], $this->field_name(), 'field/background');

                echo '</div>';

            }

            $auto_class   = (!empty($args['background_auto_attributes'])) ? ' ovic--auto-attributes' : '';
            $hidden_class = (!empty($args['background_auto_attributes']) && empty($this->value['background-image']['url'])) ? ' ovic--attributes-hidden' : '';

            echo '<div class="ovic--background-attributes'.$auto_class.$hidden_class.'">';

            //
            // Background Position
            if (!empty($args['background_position'])) {

                echo OVIC::field(array(
                    'id'      => 'background-position',
                    'type'    => 'select',
                    'options' => array(
                        ''              => esc_html__('Background Position', 'ovic-addon-toolkit'),
                        'left top'      => esc_html__('Left Top', 'ovic-addon-toolkit'),
                        'left center'   => esc_html__('Left Center', 'ovic-addon-toolkit'),
                        'left bottom'   => esc_html__('Left Bottom', 'ovic-addon-toolkit'),
                        'center top'    => esc_html__('Center Top', 'ovic-addon-toolkit'),
                        'center center' => esc_html__('Center Center', 'ovic-addon-toolkit'),
                        'center bottom' => esc_html__('Center Bottom', 'ovic-addon-toolkit'),
                        'right top'     => esc_html__('Right Top', 'ovic-addon-toolkit'),
                        'right center'  => esc_html__('Right Center', 'ovic-addon-toolkit'),
                        'right bottom'  => esc_html__('Right Bottom', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-position'], $this->field_name(), 'field/background');

            }

            //
            // Background Repeat
            if (!empty($args['background_repeat'])) {

                echo OVIC::field(array(
                    'id'      => 'background-repeat',
                    'type'    => 'select',
                    'options' => array(
                        ''          => esc_html__('Background Repeat', 'ovic-addon-toolkit'),
                        'repeat'    => esc_html__('Repeat', 'ovic-addon-toolkit'),
                        'no-repeat' => esc_html__('No Repeat', 'ovic-addon-toolkit'),
                        'repeat-x'  => esc_html__('Repeat Horizontally', 'ovic-addon-toolkit'),
                        'repeat-y'  => esc_html__('Repeat Vertically', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-repeat'], $this->field_name(), 'field/background');

            }

            //
            // Background Attachment
            if (!empty($args['background_attachment'])) {

                echo OVIC::field(array(
                    'id'      => 'background-attachment',
                    'type'    => 'select',
                    'options' => array(
                        ''       => esc_html__('Background Attachment', 'ovic-addon-toolkit'),
                        'scroll' => esc_html__('Scroll', 'ovic-addon-toolkit'),
                        'fixed'  => esc_html__('Fixed', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-attachment'], $this->field_name(), 'field/background');

            }

            //
            // Background Size
            if (!empty($args['background_size'])) {

                echo OVIC::field(array(
                    'id'      => 'background-size',
                    'type'    => 'select',
                    'options' => array(
                        ''        => esc_html__('Background Size', 'ovic-addon-toolkit'),
                        'cover'   => esc_html__('Cover', 'ovic-addon-toolkit'),
                        'contain' => esc_html__('Contain', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-size'], $this->field_name(), 'field/background');

            }

            //
            // Background Origin
            if (!empty($args['background_origin'])) {

                echo OVIC::field(array(
                    'id'      => 'background-origin',
                    'type'    => 'select',
                    'options' => array(
                        ''            => esc_html__('Background Origin', 'ovic-addon-toolkit'),
                        'padding-box' => esc_html__('Padding Box', 'ovic-addon-toolkit'),
                        'border-box'  => esc_html__('Border Box', 'ovic-addon-toolkit'),
                        'content-box' => esc_html__('Content Box', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-origin'], $this->field_name(), 'field/background');

            }

            //
            // Background Clip
            if (!empty($args['background_clip'])) {

                echo OVIC::field(array(
                    'id'      => 'background-clip',
                    'type'    => 'select',
                    'options' => array(
                        ''            => esc_html__('Background Clip', 'ovic-addon-toolkit'),
                        'border-box'  => esc_html__('Border Box', 'ovic-addon-toolkit'),
                        'padding-box' => esc_html__('Padding Box', 'ovic-addon-toolkit'),
                        'content-box' => esc_html__('Content Box', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-clip'], $this->field_name(), 'field/background');

            }

            //
            // Background Blend Mode
            if (!empty($args['background_blend_mode'])) {

                echo OVIC::field(array(
                    'id'      => 'background-blend-mode',
                    'type'    => 'select',
                    'options' => array(
                        ''            => esc_html__('Background Blend Mode', 'ovic-addon-toolkit'),
                        'normal'      => esc_html__('Normal', 'ovic-addon-toolkit'),
                        'multiply'    => esc_html__('Multiply', 'ovic-addon-toolkit'),
                        'screen'      => esc_html__('Screen', 'ovic-addon-toolkit'),
                        'overlay'     => esc_html__('Overlay', 'ovic-addon-toolkit'),
                        'darken'      => esc_html__('Darken', 'ovic-addon-toolkit'),
                        'lighten'     => esc_html__('Lighten', 'ovic-addon-toolkit'),
                        'color-dodge' => esc_html__('Color Dodge', 'ovic-addon-toolkit'),
                        'saturation'  => esc_html__('Saturation', 'ovic-addon-toolkit'),
                        'color'       => esc_html__('Color', 'ovic-addon-toolkit'),
                        'luminosity'  => esc_html__('Luminosity', 'ovic-addon-toolkit'),
                    ),
                ), $this->value['background-blend-mode'], $this->field_name(), 'field/background');

            }

            echo '</div>';

            echo $this->field_after();

        }

        public function output()
        {

            $output    = '';
            $bg_image  = array();
            $important = (!empty($this->field['output_important'])) ? '!important' : '';
            $element   = (is_array($this->field['output'])) ? join(',',
                $this->field['output']) : $this->field['output'];

            // Background image and gradient
            $background_color        = (!empty($this->value['background-color'])) ? $this->value['background-color'] : '';
            $background_gd_color     = (!empty($this->value['background-gradient-color'])) ? $this->value['background-gradient-color'] : '';
            $background_gd_direction = (!empty($this->value['background-gradient-direction'])) ? $this->value['background-gradient-direction'] : '';
            $background_image        = (!empty($this->value['background-image']['url'])) ? $this->value['background-image']['url'] : '';


            if ($background_color && $background_gd_color) {
                $gd_direction = ($background_gd_direction) ? $background_gd_direction.',' : '';
                $bg_image[]   = 'linear-gradient('.$gd_direction.$background_color.','.$background_gd_color.')';
            }

            if ($background_image) {
                $bg_image[] = 'url('.$background_image.')';
            }

            if (!empty($bg_image)) {
                $output .= 'background-image:'.implode(',', $bg_image).$important.';';
            }

            // Common background properties
            $properties = array('color', 'position', 'repeat', 'attachment', 'size', 'origin', 'clip', 'blend-mode');

            foreach ($properties as $property) {
                $property = 'background-'.$property;
                if (!empty($this->value[$property])) {
                    $output .= $property.':'.$this->value[$property].$important.';';
                }
            }

            if ($output) {
                $output = $element.'{'.$output.'}';
            }

            $this->parent->output_css .= $output;

            return $output;

        }

    }
}
