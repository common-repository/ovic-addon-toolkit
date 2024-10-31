<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Shortcodes Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Shortcode')) {
    class OVIC_Shortcode extends OVIC_Abstract
    {
        // constants
        public $unique   = '';
        public $options  = array();
        public $settings = array();
        public $abstract = 'shortcode';

        // run shortcode construct
        public function __construct($settings = array(), $options = array())
        {
            $this->options  = apply_filters('ovic_options_shortcode', $options);
            $this->settings = wp_parse_args(
                apply_filters('ovic_settings_shortcode', $settings),
                array(
                    'id'           => '',
                    'title'        => esc_html__('Ovic Shortcode', 'ovic-addon-toolkit'),
                    'desc'         => esc_html__('Add ovic shortcode to content editor', 'ovic-addon-toolkit'),
                    'button_title' => esc_html__('shortcode', 'ovic-addon-toolkit'),
                    'select_title' => esc_html__('Select a shortcode', 'ovic-addon-toolkit'),
                    'insert_title' => esc_html__('Insert Shortcode', 'ovic-addon-toolkit'),
                    'close_title'  => esc_html__('Close', 'ovic-addon-toolkit'),
                )
            );

            if (!empty($this->settings['id'])) {
                // ID shortcode
                $this->unique = $this->settings['id'];

                // add button to Editor
                add_action('wp_tiny_mce_init', array(&$this, 'tiny_mce_script'));
                add_filter('mce_buttons', array(&$this, 'register_new_button'));

                // add button
                add_action('media_buttons', array(&$this, 'shortcode_button'), 99);
                add_action('ovic_field_shortcode_buttons', array(&$this, 'shortcode_button'), 99);

                // get shortcode
                add_action('wp_ajax_ovic-get-shortcode-'.$this->unique, array(&$this, 'get_shortcode'));

                // add modal
                add_action('admin_footer', array(&$this, 'shortcode_modal_v2'));
                add_action('elementor/editor/footer', array(&$this, 'shortcode_modal_v2'));
                add_action('customize_controls_print_footer_scripts', array(&$this, 'shortcode_modal_v2'));
            }

            // wp enqueue for typography and output css
            parent::__construct();
        }

        // instance
        public static function instance($settings = array(), $options = array())
        {
            return new self($settings, $options);
        }

        /*
         * add the new button to the tinymce array
        */
        function register_new_button($buttons)
        {
            array_push($buttons, $this->unique);

            return $buttons;
        }

        /*
         * Call the javascript file that loads the
         * instructions for the new button
        */
        function tiny_mce_script($mce_settings)
        {
            ?>
            <script type="text/javascript">
                if (typeof tinymce !== 'undefined' && typeof jQuery !== 'undefined') {

                    jQuery(document).on('tinymce-editor-setup', function (event, editor) {

                        editor.addButton("<?php echo esc_js($this->unique); ?>",
                            {
                                text   : "<?php echo esc_js($this->settings['button_title']); ?>",
                                icon   : "ovic-shortcode",
                                tooltip: "<?php echo esc_js($this->settings['desc']); ?>",
                                classes: "ovic-shortcode-button",
                                onclick: function () {
                                    jQuery(document).triggerHandler("ovic_button_tinymce_<?php echo esc_js($this->unique); ?>", [this, editor.id]);
                                }
                            },
                        );

                    });

                    if (typeof QTags !== 'undefined') {

                        var add_ovic_button = true;

                        if (typeof edButtons !== 'undefined') {
                            for (var key in edButtons) {
                                if (!edButtons.hasOwnProperty(key) || add_ovic_button === false) {
                                    continue;
                                }
                                if (edButtons[key].id === "<?php echo esc_js($this->unique); ?>") {
                                    add_ovic_button = false;
                                }
                            }
                        }

                        if (add_ovic_button) {
                            QTags.addButton(
                                "<?php echo esc_js($this->unique); ?>",
                                "<?php echo esc_js($this->settings['button_title']); ?>",
                                function (element, editor) {
                                    jQuery(document).triggerHandler("ovic_button_tinymce_<?php echo esc_js($this->unique); ?>", [this, editor.id]);
                                }
                            );
                        }

                    }

                }
            </script>
            <?php
        }

        public function shortcode_button($editor_id)
        {
            $rendered = array();
            $attr     = array(
                'href'          => '#',
                'class'         => 'button ovic-shortcode-button',
                'data-modal-id' => $this->unique,
            );
            if (!empty($editor_id)) {
                $attr['data-editor-id'] = $editor_id;
            }
            foreach ($attr as $name => $value) {
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                $rendered[] = sprintf('%1$s="%2$s"', $name, esc_attr($value));
            }
            ob_start();
            ?>
            <a <?php echo implode(' ', $rendered); ?>>
                <span class="wp-media-buttons-icon"></span>
                <?php echo esc_html($this->settings['button_title']); ?>
            </a>
            <?php
            echo ob_get_clean();
        }

        public function shortcode_modal_v2()
        {
            if (OVIC::disable_scripts()) {
                return;
            }
            $html        = "";
            $modal_id    = "ovic-modal-{$this->unique}";
            $modal_class = "wp-core-ui ovic-modal-v2 ovic-shortcode";
            foreach ($this->options as $option) {
                $html .= (!empty($option['title'])) ? '<optgroup label="'.$option['title'].'">' : '';
                foreach ($option['shortcodes'] as $shortcode) {
                    $view = (isset($shortcode['view'])) ? $shortcode['view'] : 'normal';

                    $html .= '<option value="'.$shortcode['name'].'" ';
                    $html .= 'data-view="'.$view.'" ';
                    $html .= 'data-shortcode="'.$shortcode['name'].'" ';
                    if ($view == 'group') {
                        $clone_id = (isset($shortcode['clone_id'])) ? $shortcode['clone_id'] : 'nested_'.$shortcode['name'];

                        $html .= 'data-group="'.$clone_id.'" ';
                    }
                    $html .= '>';
                    $html .= $shortcode['title'];
                    $html .= '</option>';
                }
                $html .= (!empty($option['title'])) ? '</optgroup>' : '';
            }
            // Config _WP_Editors
            if (ovic_wp_editor_api() && class_exists('_WP_Editors')) {
                $defaults = apply_filters('ovic_wp_editor', array(
                    'tinymce' => array(
                        'wp_skip_init' => true,
                    ),
                ));

                $setup = _WP_Editors::parse_settings('ovic_wp_editor', $defaults);

                _WP_Editors::editor_settings('ovic_wp_editor', $setup);
            }
            ?>
            <div id="<?php echo esc_attr($modal_id); ?>" class="<?php echo esc_attr($modal_class); ?>"
                 data-modal-id="<?php echo esc_attr($this->unique); ?>">
                <div class="ovic-modal-table">
                    <div class="ovic-modal-table-cell">
                        <div class="ovic-modal-overlay"></div>
                        <div class="ovic-modal-inner ovic ovic-theme-dark">
                            <div class="ovic-header">
                                <div class="ovic-header-inner">
                                    <div class="ovic-header-left">
                                        <h1><?php echo esc_html($this->settings['title']); ?>
                                            <small>by
                                                <a href="https://kutethemes.com/" target="_blank">
                                                    Kutethemes
                                                </a>
                                            </small>
                                        </h1>
                                    </div>
                                    <div class="ovic-header-right">
                                        <div class="ovic-buttons">
                                            <select>
                                                <option value="">
                                                    <?php echo esc_html($this->settings['select_title']); ?>
                                                </option>
                                                <?php echo wp_specialchars_decode($html); ?>
                                            </select>
                                            <a href="#"
                                               class="button button-primary ovic-top-save ovic-modal-insert">
                                                <?php echo esc_html($this->settings['insert_title']); ?>
                                            </a>
                                            <input class="button button-secondary ovic-warning-primary ovic-modal-close"
                                                   type="button"
                                                   value="<?php echo esc_html($this->settings['close_title']); ?>">
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                            <div class="ovic-modal-content">
                                <div class="ovic-modal-loading">
                                    <div class="ovic-loading"></div>
                                </div>
                                <div class="ovic-modal-load"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function shortcode_modal()
        {
            ?>
            <div id="ovic-modal-<?php echo esc_attr($this->unique); ?>"
                 class="wp-core-ui ovic-modal ovic-shortcode"
                 data-modal-id="<?php echo esc_attr($this->unique); ?>">
                <div class="ovic-modal-table">
                    <div class="ovic-modal-table-cell">
                        <div class="ovic-modal-overlay"></div>
                        <div class="ovic-modal-inner">
                            <div class="ovic-modal-title">
                                <?php echo esc_html($this->settings['button_title']); ?>
                                <div class="ovic-modal-close"></div>
                            </div>
                            <div class="ovic-modal-header">
                                <select>
                                    <option value=""><?php echo esc_html($this->settings['select_title']); ?></option>
                                    <?php
                                    $html = '';
                                    foreach ($this->options as $option) {
                                        $html .= (!empty($option['title'])) ? '<optgroup label="'.$option['title'].'">' : '';
                                        foreach ($option['shortcodes'] as $shortcode) {
                                            $view = (isset($shortcode['view'])) ? $shortcode['view'] : 'normal';

                                            $html .= '<option value="'.$shortcode['name'].'" ';
                                            $html .= 'data-view="'.$view.'" ';
                                            $html .= 'data-shortcode="'.$shortcode['name'].'" ';
                                            if ($view == 'group') {
                                                $clone_id = (isset($shortcode['clone_id'])) ? $shortcode['clone_id'] : 'nested_'.$shortcode['name'];

                                                $html .= 'data-group="'.$clone_id.'" ';
                                            }
                                            $html .= '>';
                                            $html .= $shortcode['title'];
                                            $html .= '</option>';
                                        }
                                        $html .= (!empty($option['title'])) ? '</optgroup>' : '';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                            </div>
                            <div class="ovic-modal-content">
                                <div class="ovic-modal-loading">
                                    <div class="ovic-loading"></div>
                                </div>
                                <div class="ovic-modal-load"></div>
                            </div>
                            <div class="ovic-modal-insert-wrapper hidden">
                                <a href="#" class="button button-primary ovic-modal-insert">
                                    <?php echo esc_html($this->settings['insert_title']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function get_shortcode()
        {
            $html     = '';
            $unallows = array('group', 'repeater');
            $request  = ovic_get_var('shortcode_key');

            if (empty($request)) {
                wp_send_json_error(
                    array(
                        'error' => esc_html__('Error: Shortcode content load failed. Please try again.', 'ovic-addon-toolkit')
                    )
                );
            }

            $shortcode = ovic_array_search($this->options, 'name', $request);
            $shortcode = array_pop($shortcode);

            if (!empty($shortcode)) {

                $html .= '<div class="ovic-fields">';

                foreach ($shortcode['fields'] as $field) {
                    if (in_array($field['type'], $unallows)) {
                        $field['_notice'] = true;
                    }

                    $field['name'] = "{$shortcode['name']}[{$field['id']}]";
                    $field_default = (!empty($field['default'])) ? $field['default'] : '';

                    $html .= OVIC::field($field, $field_default, 'shortcode', 'shortcode');
                }

                $html .= '</div>';
            }

            if (!empty($shortcode['clone_fields'])) {

                $html .= '<div class="ovic--repeatable">';
                $html .= '<div class="ovic--repeat-shortcode">';

                $html .= '<div class="ovic-repeat-remove fa fa-times"></div>';

                $html .= '<div class="ovic-fields">';

                foreach ($shortcode['clone_fields'] as $field) {
                    if (in_array($field['type'], $unallows)) {
                        $field['_notice'] = true;
                    }

                    $name          = ($shortcode['view'] == 'group') ? $shortcode['clone_id'] : $shortcode['name'];
                    $field['sub']  = true;
                    $field['name'] = "{$name}[0][{$field['id']}]";
                    $field_default = (!empty($field['default'])) ? $field['default'] : '';

                    $html .= OVIC::field($field, $field_default, $shortcode['name'], 'shortcode');
                }

                $html .= '</div>'; // .ovic-fields
                $html .= '</div>'; // .ovic--repeat-shortcode
                $html .= '</div>'; // .ovic--repeatable

                $html .= '<div class="ovic--repeat-button-block">';
                $html .= '    <a class="button ovic--repeat-button" href="#">';
                $html .= '        <i class="fa fa-plus-circle"></i> '.$shortcode['clone_title'];
                $html .= '    </a>';
                $html .= '</div>';

            }

            wp_send_json_success(
                array('content' => $html)
            );
        }
    }
}
