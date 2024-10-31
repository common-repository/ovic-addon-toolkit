<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Abstract Class
 * A helper class for action and filter hooks
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Abstract')) {
    abstract class OVIC_Abstract
    {
        /**
         * @access public
         * @var array
         */
        public $tabs         = array();
        public $fields       = array();
        public $errors       = array();
        public $args         = array();
        public $options      = array();
        public $abstract     = '';
        public $unique       = '';
        public $output_css   = '';
        public $typographies = array();

        public function __construct()
        {
            // Check for embed google web fonts
            if (!empty($this->args['enqueue_webfont']) && $this->args['enqueue_webfont'] == true) {
                add_action('wp_enqueue_scripts', array(&$this, 'add_enqueue_google_fonts'), 100);
            }

            // Check for embed custom css styles
            if (!empty($this->args['output_css']) && $this->args['output_css'] == true) {
                add_action('wp_head', array(&$this, 'add_output_css'), 100);
            }

            // Loads scripts and styles only when needed
            add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'), 30);

            // enqueue scripts elementor
            add_action('elementor/editor/after_enqueue_scripts', array(&$this, 'enqueue_field_scripts'), 30);
        }

        public function enqueue_scripts()
        {
            $enqueue  = false;
            $wpscreen = get_current_screen();

            if ($this->abstract == 'options') {
                if (substr($wpscreen->id, -strlen($this->args['menu_slug'])) === $this->args['menu_slug']) {
                    $enqueue = true;
                }
            }
            if ($this->abstract == 'taxonomy') {
                foreach ($this->options as $argument) {
                    if (in_array($wpscreen->taxonomy, (array) $argument['taxonomy'])) {
                        $enqueue = true;
                    }
                }
            }
            if ($this->abstract == 'metabox') {
                foreach ($this->options as $argument) {
                    if (in_array($wpscreen->post_type, (array) $argument['post_type'])) {
                        $enqueue = true;
                    }
                }
            }
            if ($this->abstract == 'shortcode') {
                $enqueue = true;
            }
            if ($wpscreen->id === 'widgets' || $wpscreen->id === 'customize') {
                $enqueue = true;
            }
            if ($wpscreen->id === 'nav-menus') {
                $enqueue = true;
            }
            if ($this->abstract == 'profile' && ($wpscreen->id === 'profile' || $wpscreen->id === 'user-edit')) {
                $enqueue = true;
            }

            if (apply_filters('ovic_abstract_enqueue_scripts', $enqueue, $this)) {
                OVIC::enqueue_scripts();
            }
        }

        public function get_tabs($sections)
        {
            $result  = array();
            $parents = array();
            $count   = 100;

            foreach ($sections as $key => $section) {
                if (!empty($section['parent'])) {
                    $section['priority']           = (isset($section['priority'])) ? $section['priority'] : $count;
                    $parents[$section['parent']][] = $section;
                    unset($sections[$key]);
                }
                $count++;
            }

            foreach ($sections as $key => $section) {
                $section['priority'] = (isset($section['priority'])) ? $section['priority'] : $count;
                if (!empty($section['id']) && !empty($parents[$section['id']])) {
                    $section['subs'] = wp_list_sort($parents[$section['id']], array('priority' => 'ASC'), 'ASC', true);
                }
                $result[] = $section;
                $count++;
            }

            return wp_list_sort($result, array('priority' => 'ASC'), 'ASC', true);
        }

        public function get_fields($sections)
        {
            $result   = array();
            $sections = $this->get_sections($sections);
            if (!empty($sections)) {
                foreach ($sections as $key => $section) {
                    if (!empty($section['fields'])) {
                        foreach ($section['fields'] as $field) {
                            if ($this->abstract === 'metabox' && !empty($section['meta_key'])) {
                                $field['meta_key'] = $section['meta_key'];
                            }
                            $result[] = $field;
                        }
                    }
                }
            }

            return $result;
        }

        public function get_sections($sections)
        {
            $count  = 0;
            $result = array();

            if (!empty($sections)) {
                foreach ($sections as $tab) {
                    $count++;
                    if (!empty($tab['sections'])) {
                        foreach ($tab['sections'] as $key => $sub) {
                            if (!empty($tab['id'])) {
                                $sub['meta_key'] = $tab['id'];
                            }
                            $result[] = $sub;
                        }
                    }
                    if (empty($tab['sections'])) {
                        $result[] = $tab;
                    }
                }
            }

            return $result;
        }

        public function error_check($sections, $err = '')
        {
            if (!empty($sections['fields'])) {
                foreach ($sections['fields'] as $field) {
                    if (!empty($field['id'])) {
                        if (array_key_exists($field['id'], (array) $this->errors)) {
                            $err = '<span class="ovic-label-error">!</span>';
                        }
                    }
                }
            }

            if (!empty($sections['sections'])) {
                foreach ($sections['sections'] as $sub) {
                    $err = $this->error_check($sub, $err);
                }
            }

            if (!empty($sections['id']) && array_key_exists($sections['id'], (array) $this->errors)) {
                $err = $this->errors[$sections['id']];
            }

            return $err;
        }

        public function enqueue_field_scripts()
        {
            $fields = (array) $this->get_fields($this->options);
            foreach ($fields as $field) {
                if (!empty($field['type'])) {
                    $classname = 'OVIC_Field_'.$field['type'];
                    OVIC::maybe_include_field($field['type']);
                    if (class_exists($classname) && method_exists($classname, 'enqueue')) {
                        $instance = new $classname($field);
                        if (method_exists($classname, 'enqueue')) {
                            $instance->enqueue();
                        }
                        unset($instance);
                    }
                }
            }
        }

        public function get_meta_value($field, $default)
        {
            $field_value = '';
            if (!empty($field['meta_key'])) {
                $post_id    = apply_filters('ovic_abstract_post_meta_id', get_the_ID(), $field['meta_key']);
                $meta_value = get_post_meta($post_id, $field['meta_key'], true);
                if (isset($meta_value[$field['id']])) {
                    $field_value = $meta_value[$field['id']];
                } else {
                    $field_value = $default;
                }
            }

            return $field_value;
        }

        public function add_enqueue_google_fonts()
        {
            $fields = (array) $this->get_fields($this->options);

            foreach ($fields as $field) {
                $field_id      = (!empty($field['id'])) ? $field['id'] : '';
                $field_type    = (!empty($field['type'])) ? $field['type'] : '';
                $field_default = (!empty($field['default'])) ? $field['default'] : '';
                $field_output  = (!empty($field['output'])) ? $field['output'] : '';
                $field_check   = ($field_type === 'typography' || $field_output) ? true : false;

                if ($field_type && $field_id) {

                    OVIC::maybe_include_field($field_type);

                    $class_name = 'OVIC_Field_'.$field_type;

                    if (class_exists($class_name)) {
                        if (method_exists($class_name, 'output') || method_exists($class_name, 'enqueue_google_fonts')) {
                            $field_value = '';

                            if ($field_check) {
                                if ($this->abstract === 'options' || $this->abstract === 'customize') {
                                    $field_value = ovic_get_option($field_id, $field_default, $this->unique);
                                } elseif ($this->abstract === 'metabox') {
                                    $field_value = $this->get_meta_value($field, $field_default);
                                }
                            }

                            $instance = new $class_name($field, $field_value, $this->unique, 'wp/enqueue', $this);

                            // typography enqueue and embed google web fonts
                            if ($field_type === 'typography' && $this->args['enqueue_webfont'] && !empty($field_value['font-family'])) {
                                $instance->enqueue_google_fonts();
                            }

                            // output css
                            if ($field_output && $this->args['output_css']) {
                                $instance->output();
                            }

                            unset($instance);
                        }

                    }

                }

            }

            $this->typographies = apply_filters('ovic_option_typographies', $this->typographies);

            if (!empty($this->typographies)) {
                if (empty($this->args['async_webfont'])) {
                    $handle = 'ovic-google-web-fonts-'.$this->unique;
                    $api    = add_query_arg(
                        array(
                            'family'  => implode('%7C', $this->typographies),
                            'display' => 'swap'
                        ),
                        '//fonts.googleapis.com/css'
                    );
                    wp_enqueue_style($handle, esc_url($api), array(), null);
                } else {
                    wp_enqueue_script('ovic-google-web-fonts',
                        esc_url('//ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js'),
                        array(), '1.6.26'
                    );
                    wp_localize_script('ovic-google-web-fonts', 'WebFontConfig', array(
                        'google' => array(
                            'families' => array_values($this->typographies)
                        )
                    ));
                }
            }
        }

        public function add_output_css()
        {
            $this->output_css = apply_filters("ovic_{$this->unique}_output_css", $this->output_css, $this);

            if (!empty($this->output_css)) {
                echo '<style type="text/css">'.wp_strip_all_tags($this->output_css).'</style>';
            }
        }
    }
}
