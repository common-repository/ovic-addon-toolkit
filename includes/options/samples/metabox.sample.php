<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

//
// Metabox Settings
//
$options = array();

$options[] = array(
    'id'           => '_dependency_page_templates',
    'title'        => 'Dependency Page Templates',
    'post_type'    => 'page',
    'context'      => 'normal',
    'priority'     => 'high',
    'show_restore' => true,
    'sections'     => array(

        array(
            'name'   => 'section_1',
            'title'  => 'Section 1',
            'icon'   => 'fa fa-cog',
            'fields' => array(
                array(
                    'id'       => 'validate_2',
                    'type'     => 'text',
                    'title'    => 'Validate Example 2',
                    'desc'     => 'This text field only accepted numbers',
                    'default'  => '123456',
                    'validate' => 'ovic_validate_numeric',
                ),
            ),
        ),
    ),
);

$options[] = array(
    'id'           => '_custom_page_options',
    'title'        => 'Custom Page Options',
    'post_type'    => 'page',
    'context'      => 'normal',
    'priority'     => 'high',
    'show_restore' => true,
    'sections'     => array(

        array(
            'name'   => 'section_1',
            'title'  => 'Section 1',
            'icon'   => 'fa fa-cog',
            'fields' => array(

                array(
                    'id'       => 'validate_2',
                    'type'     => 'text',
                    'title'    => 'Validate Example 2',
                    'desc'     => 'This text field only accepted numbers',
                    'default'  => '123456',
                    'validate' => 'ovic_validate_numeric',
                ),

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
                    'type'    => 'color',
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
                    'shortcode' => true,
                ),

            ),
        ),

        array(
            'name'   => 'section_2',
            'title'  => 'Section 2',
            'icon'   => 'fa fa-tint',
            'fields' => array(
                array(
                    'id'    => 'opt-color-1',
                    'type'  => 'color',
                    'title' => 'Color',
                ),

                array(
                    'id'      => 'opt-color-2',
                    'type'    => 'color',
                    'title'   => 'Color with default (hex)',
                    'default' => '#3498db',
                ),

                array(
                    'id'      => 'opt-color-3',
                    'type'    => 'color',
                    'title'   => 'Color with default (rgba)',
                    'default' => 'rgba(255,255,0,0.25)',
                ),

                array(
                    'id'      => 'opt-color-4',
                    'type'    => 'color',
                    'title'   => 'Color with default (transparent)',
                    'default' => 'transparent',
                ),

                array(
                    'id'      => 'opt-color-group-1',
                    'type'    => 'color_group',
                    'title'   => 'Color Group',
                    'options' => array(
                        'color-1' => 'Color 1',
                        'color-2' => 'Color 2',
                    ),
                ),

                array(
                    'id'      => 'opt-color-group-2',
                    'type'    => 'color_group',
                    'title'   => 'Color Group',
                    'options' => array(
                        'color-1' => 'Color 1',
                        'color-2' => 'Color 2',
                        'color-3' => 'Color 3',
                    ),
                ),

                array(
                    'id'       => 'opt-color-group-3',
                    'type'     => 'color_group',
                    'title'    => 'Color Group with default',
                    'subtitle' => 'Can be add unlimited color options.',
                    'options'  => array(
                        'color-1' => 'Color 1',
                        'color-2' => 'Color 2',
                        'color-3' => 'Color 3',
                        'color-4' => 'Color 4',
                        'color-5' => 'Color 5',
                    ),
                    'default'  => array(
                        'color-1' => '#000100',
                        'color-2' => '#002642',
                        'color-3' => '#ffce4b',
                        'color-4' => '#ff595e',
                        'color-5' => '#0052cc',
                    ),
                ),
            ),
        ),

        array(
            'name'   => 'section_3',
            'title'  => 'Section 3',
            'icon'   => 'fa fa-tint',
            'fields' => array(

                array(
                    'id'              => 'unique_group_1',
                    'type'            => 'group',
                    'title'           => 'Group',
                    'button_title'    => 'Add New',
                    'accordion_title' => 'Add New Field',
                    'fields'          => array(

                        array(
                            'id'    => 'unique_group_1_text',
                            'type'  => 'text',
                            'title' => 'Text Field',
                        ),

                        array(
                            'id'    => 'unique_group_1_switcher',
                            'type'  => 'switcher',
                            'title' => 'Switcher Field',
                        ),

                        array(
                            'id'    => 'unique_group_1_textarea',
                            'type'  => 'textarea',
                            'title' => 'Textarea Field',
                        ),

                    ),
                ),

                array(
                    'id'              => 'unique_group_2',
                    'type'            => 'group',
                    'title'           => 'Group Field with Default',
                    'button_title'    => 'Add New',
                    'accordion_title' => 'Add New Field',
                    'fields'          => array(

                        array(
                            'id'    => 'unique_group_2_text',
                            'type'  => 'text',
                            'title' => 'Text Field',
                        ),

                        array(
                            'id'    => 'unique_group_2_switcher',
                            'type'  => 'switcher',
                            'title' => 'Switcher Field',
                        ),

                        array(
                            'id'    => 'unique_group_2_textarea',
                            'type'  => 'textarea',
                            'title' => 'Textarea Field',
                        ),

                    ),
                    'default'         => array(
                        array(
                            'unique_group_2_text'     => 'Some text',
                            'unique_group_2_switcher' => true,
                            'unique_group_2_textarea' => 'Some content',
                        ),
                        array(
                            'unique_group_2_text'     => 'Some text 2',
                            'unique_group_2_switcher' => true,
                            'unique_group_2_textarea' => 'Some content 2',
                        ),
                    ),
                ),

                array(
                    'id'              => 'unique_group_3',
                    'type'            => 'group',
                    'title'           => 'Group Field',
                    'info'            => 'You can use any option field on group',
                    'button_title'    => 'Add New Something',
                    'accordion_title' => 'Adding New Thing',
                    'fields'          => array(

                        array(
                            'id'    => 'unique_group_3_text',
                            'type'  => 'upload',
                            'title' => 'Text Field',
                        ),

                    ),
                ),

                array(
                    'id'              => 'unique_group_4',
                    'type'            => 'group',
                    'title'           => 'Group Field',
                    'desc'            => 'Accordion title using the ID of the field, for eg. "Text Field 2" using as accordion title here.',
                    'button_title'    => 'Add New',
                    'accordion_title' => 'unique_group_4_text_2',
                    'fields'          => array(

                        array(
                            'id'    => 'unique_group_4_text_1',
                            'type'  => 'text',
                            'title' => 'Text Field 1',
                        ),

                        array(
                            'id'    => 'unique_group_4_text_2',
                            'type'  => 'text',
                            'title' => 'Text Field 2',
                        ),

                        array(
                            'id'    => 'unique_group_4_text_3',
                            'type'  => 'text',
                            'title' => 'Text Field 3',
                        ),

                    ),
                ),

            ),
        ),

    ),
);

$options[] = array(
    'id'           => '_custom_page_side_options',
    'title'        => 'Custom Page Side Options',
    'post_type'    => 'page',
    'context'      => 'side',
    'priority'     => 'default',
    'show_restore' => false,
    'sections'     => array(

        array(
            'name'   => 'section_3',
            'fields' => array(

                array(
                    'id'      => 'section_3_image_select',
                    'type'    => 'image_select',
                    'options' => array(
                        'value-1' => 'https://codestarframework.com/assets/images/placeholder/65x65-2ecc71.gif',
                        'value-2' => 'https://codestarframework.com/assets/images/placeholder/65x65-e74c3c.gif',
                        'value-3' => 'https://codestarframework.com/assets/images/placeholder/65x65-3498db.gif',
                    ),
                    'default' => 'value-2',
                ),

                array(
                    'id'         => 'section_3_text',
                    'type'       => 'text',
                    'attributes' => array(
                        'placeholder' => 'do stuff',
                    ),
                ),

                array(
                    'id'      => 'section_3_switcher',
                    'type'    => 'switcher',
                    'label'   => 'Are you sure ?',
                    'default' => true,
                ),

            ),
        ),

    ),
);

$options[] = array(
    'id'           => '_custom_post_formats',
    'title'        => 'Custom Post Formats',
    'post_type'    => 'post',
    'context'      => 'normal',
    'priority'     => 'default',
    'post_formats' => 'video',
    'show_restore' => true,
    'sections'     => array(

        array(
            'name'   => 'section_4',
            'fields' => array(

                array(
                    'id'    => 'section_4_text',
                    'type'  => 'text',
                    'title' => 'Text Field',
                ),
            ),
        ),
    ),
);

$options[] = array(
    'id'           => '_custom_post_options',
    'title'        => 'Custom Post Options',
    'post_type'    => 'post',
    'context'      => 'normal',
    'priority'     => 'default',
    'show_restore' => true,
    'sections'     => array(

        array(
            'name'   => 'section_4',
            'fields' => array(

                array(
                    'id'    => 'section_4_text',
                    'type'  => 'text',
                    'title' => 'Text Field',
                ),

                array(
                    'id'    => 'section_4_textarea',
                    'type'  => 'textarea',
                    'title' => 'Textarea Field',
                ),

                array(
                    'id'    => 'section_4_upload',
                    'type'  => 'upload',
                    'title' => 'Upload Field',
                ),

                array(
                    'id'    => 'section_4_switcher',
                    'type'  => 'switcher',
                    'title' => 'Switcher Field',
                    'label' => 'Yes, Please do it.',
                ),

            ),
        ),

    ),
);

OVIC_Metabox::instance($options);
