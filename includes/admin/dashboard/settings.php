<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Ovic_Addon_Settings')) {
    class Ovic_Addon_Settings
    {
        public function __construct()
        {
            add_action('admin_init', array($this, 'page_init'));
            add_action('ovic_addon_settings', array($this, 'settings'));
        }

        public function fields()
        {
            return apply_filters('ovic_addon_core_settings', array(
                array(
                    'title'  => __('General'),
                    'fields' => array(
                        array(
                            'id'      => 'demo_mode',
                            'type'    => 'switcher',
                            'title'   => esc_html__('Demo Theme Options', 'ovic-addon-toolkit'),
                            'desc'    => esc_html__('The sample theme options for developer.', 'ovic-addon-toolkit'),
                            'default' => false,
                        ),
                        array(
                            'id'      => 'auto_update',
                            'type'    => 'switcher',
                            'title'   => esc_html__('Auto Update Theme', 'ovic-addon-toolkit'),
                            'desc'    => esc_html__('Check and download new version from Kutethemes.', 'ovic-addon-toolkit'),
                            'default' => true,
                        ),
                        array(
                            'id'      => 'clear_cache',
                            'type'    => 'content',
                            'content' => '<a href="#" data-text-done="'.esc_attr__('%n transient database entries have been deleted.', 'ovic-addon-toolkit').'" class="button button-primary clear-cache"/>'.esc_html__('Delete Cache', 'ovic-addon-toolkit').'</a><span class="spinner" style="float:none;"></span>',
                            'title'   => esc_html__('Delete WP Cache', 'ovic-addon-toolkit'),
                            'after'   => '<p class="ovic-text-success"></p>',
                        ),
                    ),
                ),
                array(
                    'title'  => __('Addon'),
                    'fields' => array(
                        array(
                            'id'      => 'footer',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Footer Builder'),
                        ),
                        array(
                            'id'      => 'post_like',
                            'type'    => 'switcher',
                            'default' => false,
                            'title'   => __('Post Like'),
                        ),
                        array(
                            'id'      => 'editor_term',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Visual Term Descriptions'),
                        ),
                        array(
                            'id'      => 'photo_editor',
                            'type'    => 'switcher',
                            'default' => false,
                            'title'   => __('Photo Editor'),
                            'help'    => '<ul style="text-align:left;">
                                <li><b>width:</b> Number</li>
                                <li><b>height:</b> Number</li>
                                <li><b>images:</b> array ids gallery</li>
                                <li><b>filename:</b> name file when export image</li>
                            </ul>',
                            'after'   => '<code style="margin-top:10px;display:inline-block;">[ovic_photo_editor width="600" height="600" images="" filename="photo-editor.png"]</code>',
                        ),
                        array(
                            'id'      => 'question_answers',
                            'type'    => 'switcher',
                            'default' => false,
                            'title'   => __('Question & Answers'),
                            'help'    => '<ul style="text-align:left;">
                                <li><b>title:</b> String</li>
                                <li><b>text_btn:</b> String</li>
                                <li><b>text_ask:</b> String</li>
                                <li><b>popup:</b> true/false - view Question Answers by popup or not</li>
                                <li><b>lock:</b> true/false - allows post Question or not</li>
                                <li><b>ajax:</b> true/false - load Question Answers by ajax or not</li>
                            </ul>',
                            'after'   => '<code style="margin-top:10px;display:inline-block;">[ovic_question title="" text_btn="" text_ask="" popup="" lock="" ajax=""]</code>',
                        ),
                    ),
                ),
                array(
                    'title'  => __('Elementor'),
                    'fields' => array(
                        array(
                            'id'      => 'remote_source',
                            'type'    => 'switcher',
                            'title'   => esc_html__('Elementor Template', 'ovic-addon-toolkit'),
                            'desc'    => esc_html__('Get our source Elementor template.', 'ovic-addon-toolkit'),
                            'default' => false,
                        ),
                        array(
                            'id'      => 'elementor_grid',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Elementor grid'),
                        ),
                    )
                ),
                array(
                    'title'  => __('WooCommerce'),
                    'fields' => array(
                        array(
                            'id'      => 'product_brand',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Products Brand'),
                        ),
                        array(
                            'id'      => 'add_to_cart',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Ajax Add To Cart'),
                            'desc'    => __('Single product ajax add to cart.'),
                        ),
                        array(
                            'id'      => 'popup_notice',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Popup Notice'),
                            'desc'    => __('Popup Notice when add to cart, add wishlist, etc..'),
                        ),
                    )
                ),
                array(
                    'title'  => __('Megamenu'),
                    'fields' => array(
                        array(
                            'id'      => 'megamenu',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Megamenu Builder'),
                        ),
                        array(
                            'id'      => 'mobile',
                            'type'    => 'switcher',
                            'default' => true,
                            'title'   => __('Mobile Menu'),
                        ),
                        array(
                            'id'      => 'megamenu_resize',
                            'type'    => 'select',
                            'options' => array(
                                ''       => esc_html__('None', 'ovic-addon-toolkit'),
                                'mobile' => esc_html__('Mobile', 'ovic-addon-toolkit'),
                                'tablet' => esc_html__('Tablet', 'ovic-addon-toolkit'),
                            ),
                            'default' => '',
                            'title'   => __('Disable Resize'),
                            'desc'    => __('Disable resize megamenu on device, help your website faster in mobile.'),
                        ),
                        array(
                            'id'      => 'mobile_menu',
                            'type'    => 'button_set',
                            'options' => array(
                                ''      => esc_html__('Default', 'ovic-addon-toolkit'),
                                'click' => esc_html__('Click', 'ovic-addon-toolkit'),
                                'last'  => esc_html__('Last', 'ovic-addon-toolkit'),
                            ),
                            'default' => '',
                            'title'   => __('Load Mobile Menu'),
                        ),
                        array(
                            'id'         => 'mobile_delay',
                            'type'       => 'number',
                            'unit'       => 'ms',
                            'default'    => 0,
                            'title'      => esc_html__('Delay', 'ovic-addon-toolkit'),
                            'dependency' => array('mobile_menu', '==', 'last'),
                        ),
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => sprintf('<b class="in-mess">%s</b>: %s<br><b class="in-mess">%s</b>:  %s',
                                esc_html__('Click', 'ovic-addon-toolkit'),
                                esc_html__('Menu load when click open menu.', 'ovic-addon-toolkit'),
                                esc_html__('Last', 'ovic-addon-toolkit'),
                                esc_html__('Menu load when content load complete.', 'ovic-addon-toolkit')
                            ),
                        ),
                    )
                ),
                array(
                    'title'  => __('Font Awesome'),
                    'fields' => array(
                        array(
                            'id'      => 'fontawesome',
                            'type'    => 'select',
                            'title'   => __('Font Awesome'),
                            'options' => array(
                                'fa4' => __('Font Awesome 4'),
                                'fa5' => __('Font Awesome 5'),
                            ),
                            'default' => 'fa4',
                            'desc'    => __('Font Awesome version using in theme options.'),
                        ),
                        array(
                            'id'      => 'fa4_support',
                            'type'    => 'switcher',
                            'default' => false,
                            'title'   => __('Load Font Awesome 4 Support'),
                            'desc'    => __('Font Awesome 4 support script (shim.js) is a script that makes sure all previously selected Font Awesome 4 icons are displayed correctly while using Font Awesome 5 library.'),
                        ),
                    ),
                ),
                array(
                    'title'  => __('Lazyload'),
                    'fields' => array(
                        array(
                            'id'      => 'lazyload',
                            'type'    => 'switcher',
                            'default' => false,
                            'title'   => __('Lazy load'),
                        ),
                        array(
                            'id'      => 'crop',
                            'type'    => 'switcher',
                            'default' => false,
                            'title'   => __('Disable Crop'),
                        ),
                        array(
                            'id'    => 'placeholder',
                            'type'  => 'media',
                            'title' => __('Placeholder Image'),
                        ),
                    ),
                ),
                array(
                    'title'  => __('Snow Effect'),
                    'fields' => array(
                        array(
                            'id'      => 'snow_effect',
                            'type'    => 'switcher',
                            'title'   => __('Enable Snow Effect'),
                            'default' => false,
                        ),
                        array(
                            'id'      => 'snow_limit',
                            'type'    => 'number',
                            'title'   => __('Limit'),
                            'default' => 60,
                            'unit'    => 'snow',
                        ),
                        array(
                            'id'          => 'snow_size',
                            'type'        => 'dimensions',
                            'title'       => __('Size'),
                            'width'       => 20,
                            'height'      => 30,
                            'width_icon'  => 'min',
                            'height_icon' => 'max',
                        ),
                        array(
                            'id'      => 'snow_text',
                            'type'    => 'text',
                            'title'   => __('Content'),
                            'default' => '❅',
                        ),
                        array(
                            'id'      => 'snow_speed',
                            'type'    => 'slider',
                            'title'   => __('Speed'),
                            'default' => 30,
                            'max'     => 100,
                            'min'     => 10,
                        ),
                        array(
                            'id'      => 'snow_color',
                            'type'    => 'color',
                            'title'   => __('Color'),
                            'default' => '#fff',
                        ),
                        array(
                            'id'      => 'snow_background',
                            'type'    => 'color',
                            'title'   => __('Background'),
                            'default' => 'transparent',
                        ),
                    ),
                ),
            ));
        }

        public function save_options($settings)
        {
            if (empty($settings)) {

                $default = array();

                foreach ($this->fields() as $fields) {
                    if (!empty($fields['fields'])) {
                        foreach ($fields['fields'] as $field) {
                            $default[$field['id']] = !empty($field['default']) ? $field['default'] : '';
                        }
                    }
                }

                OVIC_CORE()->set_config($default);

            }
        }

        public function page_init()
        {
            $key      = OVIC_CORE()->get_key();
            $settings = OVIC_CORE()->get_config();
            $fields   = array(
                'title' => '',
                'id'    => $key,
                'type'  => 'tabbed',
                'tabs'  => $this->fields(),
            );

            $this->save_options($settings);

            register_setting(
                'ovic_addon_group',
                $key
            );
            add_settings_section(
                'ovic_setting_section_id',
                null,
                null,
                'ovic-addon-settings'
            );
            add_settings_field(
                $key,
                '',
                function () use ($settings, $fields) {
                    echo '<div class="ovic-onload">';
                    echo OVIC::field($fields, $settings);
                    echo '</div>';
                },
                'ovic-addon-settings',
                'ovic_setting_section_id'
            );
        }

        public function settings()
        {
            ?>
            <div class="wrap ovic-addon-settings">
                <h3><?php echo __('Ovic Addon Settings'); ?></h3>
                <form method="post" action="options.php">
                    <?php
                    // This prints out all hidden setting fields
                    settings_fields('ovic_addon_group');
                    do_settings_sections('ovic-addon-settings');
                    submit_button(null, 'primary', 'submit', true,
                        array(
                            'data-text' => esc_html__('Loading...', 'ovic-addon-toolkit')
                        )
                    );
                    ?>
                </form>
            </div>
            <?php
        }
    }

    return new Ovic_Addon_Settings();
}