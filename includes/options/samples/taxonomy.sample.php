<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

//
// Taxonomy Options
//
$options = array();

$options[] = array(
    'id'       => '_custom_taxonomy_options',
    'taxonomy' => 'category', // category, post_tag or your custom taxonomy name
    'fields'   => array(

        array(
            'id'    => 'text_1',
            'type'  => 'text',
            'title' => 'Text',
        ),

        array(
            'id'    => 'textarea_1',
            'type'  => 'textarea',
            'title' => 'Textarea',
            'help'  => 'This option field is useful. You will love it!',
        ),

        array(
            'id'    => 'upload_1',
            'type'  => 'upload',
            'title' => 'Upload',
            'help'  => 'Upload a site logo for your branding.',
        ),

        array(
            'id'    => 'switcher_1',
            'type'  => 'switcher',
            'title' => 'Switcher',
            'label' => 'You want to update for this framework ?',
        ),

        array(
            'id'      => 'color_picker_1',
            'type'    => 'color_picker',
            'title'   => 'Color Picker',
            'default' => '#3498db',
        ),

        array(
            'id'    => 'checkbox_1',
            'type'  => 'checkbox',
            'title' => 'Checkbox',
            'label' => 'Did you like this framework ?',
        ),

        array(
            'id'      => 'radio_1',
            'type'    => 'radio',
            'title'   => 'Radio',
            'options' => array(
                'yes' => 'Yes, Please.',
                'no'  => 'No, Thank you.',
            ),
            'help'    => 'Are you sure for this choice?',
        ),

        array(
            'id'             => 'select_1',
            'type'           => 'select',
            'title'          => 'Select',
            'options'        => array(
                'bmw'        => 'BMW',
                'mercedes'   => 'Mercedes',
                'volkswagen' => 'Volkswagen',
                'other'      => 'Other',
            ),
            'default_option' => 'Select your favorite car',
        ),

        array(
            'id'      => 'number_1',
            'type'    => 'number',
            'title'   => 'Number',
            'default' => '10',
            'after'   => ' <i class="ovic-text-muted">$ (dollars)</i>',
        ),

        array(
            'id'      => 'image_select_1',
            'type'    => 'image_select',
            'title'   => 'Image Select',
            'options' => array(
                'value-1' => 'https://codestarframework.com/assets/images/placeholder/100x80-2ecc71.gif',
                'value-2' => 'https://codestarframework.com/assets/images/placeholder/100x80-e74c3c.gif',
                'value-3' => 'https://codestarframework.com/assets/images/placeholder/100x80-ffbc00.gif',
                'value-4' => 'https://codestarframework.com/assets/images/placeholder/100x80-3498db.gif',
                'value-5' => 'https://codestarframework.com/assets/images/placeholder/100x80-555555.gif',
            ),
        ),

        array(
            'type'    => 'notice',
            'class'   => 'info',
            'content' => 'This is info notice field for your highlight sentence.',
        ),

        array(
            'id'    => 'background_1',
            'type'  => 'background',
            'title' => 'Background',
        ),

        array(
            'type'    => 'notice',
            'class'   => 'warning',
            'content' => 'This is info warning field for your highlight sentence.',
        ),

        array(
            'id'    => 'icon_1',
            'type'  => 'icon',
            'title' => 'Icon',
            'desc'  => 'Some description here for this option field.',
        ),

        array(
            'id'    => 'text_2',
            'type'  => 'text',
            'title' => 'Text',
            'desc'  => 'Some description here for this option field.',
        ),

        array(
            'id'        => 'textarea_2',
            'type'      => 'textarea',
            'title'     => 'Textarea',
            'info'      => 'Some information here for this option field.',
            'shortcode' => array(
                'id'    => 'ovic_shortcode',
                'title' => 'shortcode',
            ),
        ),

    ),
);

OVIC_Taxonomy::instance($options);
