<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Metabox Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Metabox')) {
    class OVIC_Metabox extends OVIC_Abstract
    {
        // constants
        public $options  = array();
        public $errors   = array();
        public $abstract = 'metabox';
        // default args
        public $args = array(
            'id'              => '',
            'title'           => '',
            'post_type'       => '',
            'context'         => '',
            'priority'        => '',
            'page_templates'  => '',
            'post_formats'    => '',
            // typography options
            'enqueue_webfont' => true,
            'async_webfont'   => false,
            // others
            'output_css'      => true,
        );

        // run metabox construct
        public function __construct($options)
        {
            // Get options metabox
            $this->args    = apply_filters('ovic_options_metabox_settings', $this->args, $this);
            $this->options = apply_filters('ovic_options_metabox', $options);

            if (in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php'))) {
                // Actions metabox
                add_action('add_meta_boxes', array(&$this, 'add_meta_box'));
                add_action('save_post', array(&$this, 'save_meta_box'), 10, 2);
            }

            // wp enqueue for typography and output css
            parent::__construct();
        }

        // instance
        public static function instance($options = array())
        {
            return new self($options);
        }

        // add metabox
        public function add_meta_box($post_type)
        {
            foreach ($this->options as $meta) {
                //$value['__back_compat_meta_box'] = true;
                add_meta_box(
                    $meta['id'],
                    $meta['title'],
                    array(&$this, 'add_meta_box_content'),
                    $meta['post_type'],
                    $meta['context'],
                    $meta['priority'],
                    $meta
                );

                // add metabox classes
                $prefix = '';
                if ($meta['post_type'] == 'page') {
                    $prefix = 'page_templates';
                } elseif ($meta['post_type'] == 'post') {
                    $prefix = 'post_formats';
                }
                $templates = (!empty($prefix) && !empty($meta[$prefix])) ? $meta[$prefix] : '';

                add_filter('postbox_classes_'.$meta['post_type'].'_'.$meta['id'],
                    function ($classes) use ($templates, $prefix) {
                        return $this->get_class($classes, $templates, $prefix);
                    }
                );
            }
        }

        public function get_class($classes, $templates, $prefix)
        {
            $page_class    = (!empty($templates)) ? explode(',', $templates) : array();
            $page_template = ($prefix == 'page_templates') ? get_page_template_slug() : get_post_format();
            $page_template = (!empty($page_template)) ? $page_template : 'default';

            if (!empty($page_class)) {
                $classes[] = ($prefix == 'page_templates') ? 'ovic-page-templates' : 'ovic-post-formats';
                foreach ($page_class as $class) {
                    $class     = preg_replace('/[^a-zA-Z0-9]+/', '-', $class);
                    $classes[] = ($prefix == 'page_templates') ? 'ovic-page-'.$class : 'ovic-post-format-'.$class;
                }
            }

            if (!empty($page_class) && !in_array($page_template, $page_class)) {
                $classes[] = 'ovic-hide';
            } else {
                $classes[] = 'ovic-show';
            }

            return $classes;
        }

        // add metabox content
        public function add_meta_box_content($post, $callback)
        {
            global $post, $typenow;

            wp_nonce_field('ovic-metabox', 'ovic-metabox-nonce');

            $args       = $callback['args'];
            $unique     = $args['id'];
            $sections   = (!empty($args['sections'])) ? $args['sections'] : $args['fields'];
            $meta_value = get_post_meta($post->ID, $unique, true);
            $has_nav    = (count($sections) >= 2 && $args['context'] != 'side') ? true : false;
            $show_all   = (!$has_nav) ? ' ovic-show-all' : '';
            $timenow    = round(microtime(true));
            $errors     = (isset($meta_value['_transient']['errors'])) ? $meta_value['_transient']['errors'] : array();
            $section    = (isset($meta_value['_transient']['section'])) ? $meta_value['_transient']['section'] : false;
            $expires    = (isset($meta_value['_transient']['expires'])) ? $meta_value['_transient']['expires'] : 0;
            $timein     = ovic_timeout($timenow, $expires, 20);
            $section_id = ($timein && $section) ? $section : '';
            $section_id = ovic_get_var('ovic-section', $section_id);

            // add error
            $this->errors = ($timein) ? $errors : array();

            do_action('ovic_html_metabox_before');

            echo '<div class="ovic ovic-theme-dark ovic-metabox">';

            echo '<input type="hidden" name="'.esc_attr($unique).'[_transient][section]" class="ovic-section-id" value="'.esc_attr($section_id).'">';

            echo '<div class="ovic-wrapper'.esc_attr($show_all).'">';

            if ($has_nav) {
                echo '<div class="ovic-nav ovic-nav-metabox" data-unique="'.esc_attr($unique).'">';
                echo '<ul>';
                $num = 0;
                foreach ($sections as $tab) {
                    if (!empty($tab['typenow']) && $tab['typenow'] !== $typenow) {
                        continue;
                    }
                    $tab_error = $this->error_check($tab);
                    $tab_icon  = (!empty($tab['icon'])) ? '<i class="ovic-tab-icon '.esc_attr($tab['icon']).'"></i>' : '';
                    if (isset($tab['fields'])) {
                        echo '<li><a href="#" data-section="'.esc_attr($unique).'_'.esc_attr($tab['name']).'">'.wp_kses_post($tab_icon.$tab['title'].$tab_error).'</a></li>';
                    } else {
                        echo '<li><div class="ovic-seperator">'.wp_kses_post($tab_icon.$tab['title'].$tab_error).'</div></li>';
                    }
                    $num++;
                }
                echo '</ul>';
                echo '</div>';
            }

            echo '<div class="ovic-content">';

            echo '<div class="ovic-sections">';

            $num = 0;

            foreach ($sections as $fields) {
                if (!empty($fields['typenow']) && $fields['typenow'] !== $typenow) {
                    continue;
                }
                if (isset($fields['fields'])) {
                    $active_content = (!$has_nav) ? 'ovic-onload' : '';

                    echo '<div id="ovic-section-'.esc_attr($unique).'_'.esc_attr($fields['name']).'" class="ovic-section '.esc_attr($active_content).'">';

                    echo (isset($fields['title'])) ? '<div class="ovic-section-title"><h3>'.wp_kses_post($fields['title']).'</h3></div>' : '';

                    foreach ($fields['fields'] as $field_key => $field) {
                        $is_field_error = $this->error_check($field);
                        if (!empty($is_field_error)) {
                            $field['_error'] = $is_field_error;
                        }
                        $default    = (isset($field['default'])) ? $field['default'] : '';
                        $elem_id    = (isset($field['id'])) ? $field['id'] : '';
                        $elem_value = (is_array($meta_value) && isset($meta_value[$elem_id])) ? $meta_value[$elem_id] : $default;
                        if (!empty($args['prefix'])) {
                            $elem_value = get_post_meta($post->ID, "{$args['prefix']}{$elem_id}", true);
                            if (empty($elem_value)) {
                                $elem_value = $default;
                            }
                        }
                        echo OVIC::field($field, $elem_value, $unique, 'metabox');
                    }

                    echo '</div>';
                }
                $num++;
            }

            echo '</div>';

            echo '<div class="clear"></div>';

            if (!empty($args['show_restore'])) {
                echo '<div class="ovic-restore-wrapper">';
                echo '    <label>';
                echo '        <input type="checkbox" name="'.esc_attr($unique).'[_restore]" />';
                echo '        <span class="button ovic-button-restore">'.esc_html__('Restore', 'ovic-addon-toolkit').'</span>';
                echo '        <span class="button ovic-button-cancel">'.sprintf('<small>( %s )</small> %s', esc_html__('update post for restore ', 'ovic-addon-toolkit'), esc_html__('Cancel', 'ovic-addon-toolkit')).'</span>';
                echo '    </label>';
                echo '</div>';
            }

            echo '</div>';
            echo ($has_nav) ? '<div class="ovic-nav-background"></div>' : '';
            echo '<div class="clear"></div>';
            echo '</div>';
            echo '</div>';

            do_action('ovic_html_metabox_after');
        }

        // save metabox
        public function save_meta_box($post_id, $post)
        {
            if (wp_verify_nonce(ovic_get_var('ovic-metabox-nonce'), 'ovic-metabox')) {
                $errors    = array();
                $post_type = ovic_get_var('post_type');
                foreach ($this->options as $request_value) {
                    if (in_array($post_type, (array) $request_value['post_type'])) {
                        $request_key = $request_value['id'];
                        $request     = ovic_get_var($request_key, array());
                        // ignore _nonce
                        if (isset($request['_nonce'])) {
                            unset($request['_nonce']);
                        }
                        // sanitize and validate
                        foreach ($request_value['sections'] as $key => $section) {
                            if (!empty($section['fields'])) {
                                foreach ($section['fields'] as $field) {
                                    if (!empty($field['id'])) {
                                        // sanitize
                                        if (!empty($field['sanitize'])) {
                                            $sanitize = $field['sanitize'];
                                            if (function_exists($sanitize)) {
                                                $value_sanitize        = ovic_get_vars($request_key, $field['id']);
                                                $request[$field['id']] = call_user_func($sanitize, $value_sanitize);
                                            }
                                        }
                                        // validate
                                        if (!empty($field['validate'])) {
                                            $validate = $field['validate'];
                                            if (function_exists($validate)) {
                                                $value_validate = ovic_get_vars($request_key, $field['id']);
                                                $has_validated  = call_user_func($validate, $value_validate);
                                                if (!empty($has_validated)) {
                                                    $meta_value            = get_post_meta($post_id, $request_key, true);
                                                    $errors[$field['id']]  = $has_validated;
                                                    $default_value         = isset($field['default']) ? $field['default'] : '';
                                                    $request[$field['id']] = (isset($meta_value[$field['id']])) ? $meta_value[$field['id']] : $default_value;
                                                }
                                            }
                                        }
                                        // auto sanitize
                                        if (!isset($request[$field['id']]) || is_null($request[$field['id']])) {
                                            $request[$field['id']] = '';
                                        }
                                    }
                                }
                            }
                        }
                        $request['_transient']['expires'] = round(microtime(true));
                        if (!empty($errors)) {
                            $request['_transient']['errors'] = $errors;
                        }
                        $request = apply_filters('ovic_save_metabox', $request, $request_key, $post);

                        if (empty($request) || !empty($request['_restore'])) {
                            if (!empty($request_value['prefix'])) {
                                foreach ($request as $key => $value) {
                                    if ($key != '_transient' && $key != '_restore' && $key != '_nonce') {
                                        delete_post_meta($post_id, "{$request_value['prefix']}{$key}", $value);
                                    }
                                }
                            }
                            delete_post_meta($post_id, $request_key);
                        } else {
                            if (!empty($request_value['prefix'])) {
                                foreach ($request as $key => $value) {
                                    if ($key != '_transient' && $key != '_restore' && $key != '_nonce') {
                                        unset($request[$key]);
                                        update_post_meta($post_id, "{$request_value['prefix']}{$key}", $value);
                                    }
                                }
                            }
                            update_post_meta($post_id, $request_key, $request);
                        }
                    }
                }
            }
        }
    }
}
