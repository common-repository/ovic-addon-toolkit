<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Options Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Fields')) {
    abstract class OVIC_Fields
    {
        public $field     = array();
        public $value     = '';
        public $org_value = '';
        public $unique    = '';
        public $where     = '';
        public $parent    = '';
        public $multilang = '';

        public function __construct($field = array(), $value = '', $unique = '', $where = '', $parent = '')
        {
            $this->field     = $field;
            $this->org_value = $value;
            $this->multilang = $this->field_multilang();
            $this->value     = $this->field_value($value);
            $this->unique    = $unique;
            $this->where     = $where;
            $this->parent    = $parent;
        }

        public function field_value($value = '')
        {
            if ($this->multilang != false && !empty($this->multilang) && is_array($value)) {
                $current = $this->multilang['current'];
                if (isset($value[$current])) {
                    $value = $value[$current];
                } else {
                    $value = '';
                }
            } elseif (!is_array($this->multilang) && is_array($value) && isset($value['multilang'])) {
                $value = array_values((array) $value)[0];
            } elseif (is_array($this->multilang) && !is_array($value) && ($this->multilang['current'] != $this->multilang['default'])) {
                $value = '';
            }

            return $value;
        }

        public function field_name($nested_name = '', $multilang = false)
        {
            $field_id        = (!empty($this->field['id'])) ? $this->field['id'] : '';
            $unique_id       = (!empty($this->unique)) ? $this->unique.'['.$field_id.']' : $field_id;
            $extra_multilang = (!$multilang && !empty($this->multilang['current']) && is_array($this->multilang)) ? '['.$this->multilang['current'].']' : '';
            $field_name      = (!empty($this->field['name'])) ? $this->field['name'] : $unique_id;
            $tag_prefix      = (!empty($this->field['tag_prefix'])) ? $this->field['tag_prefix'] : '';

            if (!empty($tag_prefix)) {
                $nested_name = str_replace('[', '['.$tag_prefix, $nested_name);
            }

            return $field_name.$extra_multilang.$nested_name;
        }

        public function field_class($el_class = '')
        {
            $field_class = (isset($this->field['class'])) ? ' '.$this->field['class'] : '';

            return ($field_class || $el_class) ? ' class="'.$el_class.$field_class.'"' : '';
        }

        public function field_attributes($custom_atts = array())
        {
            $field_id   = (!empty($this->field['id'])) ? $this->field['id'] : '';
            $attributes = (!empty($this->field['attributes'])) ? $this->field['attributes'] : array();

            if (!empty($field_id) && empty($attributes['data-depend-id'])) {
                $attributes['data-depend-id'] = $field_id;
            }

            if (!empty($this->field['placeholder'])) {
                $attributes['placeholder'] = $this->field['placeholder'];
            }

            $attributes = wp_parse_args($attributes, $custom_atts);

            $attr = '';

            if (!empty($attributes)) {
                foreach ($attributes as $key => $value) {
                    if ($value === 'only-key') {
                        $attr .= ' '.$key;
                    } else {
                        $attr .= ' '.$key.'="'.$value.'"';
                    }
                }
            }

            return $attr;
        }

        public function field_before()
        {
            return (!empty($this->field['before'])) ? $this->field['before'] : '';
        }

        public function field_after()
        {
            $outputput = '';
            $outputput .= (!empty($this->field['after'])) ? $this->field['after'] : '';
//			$outputput .= ( ! empty( $this->field['desc'] ) ) ? '<p class="ovic-text-desc">' . $this->field['desc'] . '</p>' : '';
            $outputput .= (!empty($this->field['help'])) ? '<span class="ovic-help"><span class="ovic-help-text">'.$this->field['help'].'</span><span class="fa fa-question-circle"></span></span>' : '';
            $outputput .= (!empty($this->field['_error'])) ? '<p class="ovic-text-error">'.$this->field['_error'].'</p>' : '';
            $outputput .= $this->field_after_multilang();
            $outputput .= $this->field_debug();

            return $outputput;
        }

        public function field_debug()
        {
            $output = '';
            if (!empty($this->where) && (!empty($this->field['debug']) || (defined('OVIC_DEBUG') && OVIC_DEBUG))) {
                $value  = $this->field_value();
                $output .= "<pre>";
                $output .= "<strong>".esc_html__('CONFIG', 'ovic-addon-toolkit').":</strong>";
                $output .= "\n";
                ob_start();
                var_export($this->field);
                $output .= htmlspecialchars(ob_get_clean());
                $output .= "\n";
                if (!empty($this->field['id'])) {
                    $output .= "\n";
                    $output .= "<strong>".esc_html__('ID', 'ovic-addon-toolkit').":</strong>";
                    $output .= "\n";
                    $output .= $this->field['id'];
                    $output .= "\n";
                    $output .= "\n";
                    $output .= "<strong>".esc_html__('USAGE', 'ovic-addon-toolkit').":</strong>";
                    $output .= "\n";
                    if ($this->where === 'options' || $this->where === 'customize') {
                        $output .= "\$my_options = get_option( '".$this->unique."' );\necho \$my_options['".$this->field['id']."'];";
                    } elseif ($this->where === 'metabox') {
                        $output .= "\$my_options = get_post_meta( THE_POST_ID, '".$this->unique."', true );\necho \$my_options['".$this->field['id']."'];";
                    } elseif ($this->where === 'taxonomy') {
                        $output .= "\$my_options = get_term_meta( THE_TERM_ID, '".$this->unique."', true );\necho \$my_options['".$this->field['id']."'];";
                    }
                    if (isset($value)) {
                        $output .= "\n\n";
                        $output .= "<strong>".esc_html__('VALUE', 'ovic-addon-toolkit').":</strong>";
                        $output .= "\n";
                        ob_start();
                        var_export($value);
                        $output .= htmlspecialchars(ob_get_clean());
                    }
                }
                $output .= "</pre>";
            }
            if (!empty($this->where) && !empty($this->field['id']) && (!empty($this->field['debug_light']) || (defined('OVIC_DEBUG_LIGHT') && OVIC_DEBUG_LIGHT))) {
                $output .= "<pre>";
                $output .= "\n";
                $output .= "<strong>".esc_html__('ID', 'ovic-addon-toolkit').":</strong>";
                $output .= "\n";
                $output .= $this->field['id'];
                $output .= "\n";
                $output .= "\n";
                $output .= "<strong>".esc_html__('USAGE', 'ovic-addon-toolkit').":</strong>";
                $output .= "\n";
                if ($this->where === 'options' || $this->where === 'customize') {
                    $output .= "\$my_options = get_option( '".$this->unique."' );\necho \$my_options['".$this->field['id']."'];";
                } elseif ($this->where === 'metabox') {
                    $output .= "\$my_options = get_post_meta( THE_POST_ID, '".$this->unique."', true );\necho \$my_options['".$this->field['id']."'];";
                } elseif ($this->where === 'taxonomy') {
                    $output .= "\$my_options = get_term_meta( THE_TERM_ID, '".$this->unique."', true );\necho \$my_options['".$this->field['id']."'];";
                }
                $output .= "\n";
                $output .= "</pre>";
            }

            return $output;
        }

        public function field_multilang()
        {
            return (!empty($this->field['multilang'])) ? ovic_language_defaults() : false;
        }

        public function field_after_multilang()
        {
            $output = '';

            if (is_array($this->multilang)) {
                $output .= '<fieldset class="hidden">';
                foreach ($this->multilang['languages'] as $key => $val) {
                    // ignore current language for hidden element
                    if ($key != $this->multilang['current']) {
                        $simple_value = [
                            'button_set',
                            'checkbox',
                            'select',
                            'radio',
                        ];
                        // set default value
                        if (isset($this->org_value[$key])) {
                            $value = $this->org_value[$key];
                        } elseif (!isset($this->org_value[$key]) && ($key == $this->multilang['default'])) {
                            $value = $this->org_value;
                        } else {
                            $value = '';
                        }

                        $cache_field = $this->field;
                        unset($cache_field['multilang']);
                        $cache_field['name'] = $this->field_name('['.$key.']', true);

                        if (in_array($this->field['type'], $simple_value)) {
                            if (is_array($value)) {
                                foreach ($value as $data) {
                                    $output .= '<input type="hidden" name="'.$cache_field['name'].'[]" value="'.$data.'" />';
                                }
                            } else {
                                $output .= '<input type="hidden" name="'.$cache_field['name'].'" value="'.$value.'" />';
                            }
                        } else {
                            $output .= OVIC::field($cache_field, $value, $this->unique);
                        }
                    }
                }
                $output .= '<input type="hidden" name="'.$this->field_name('[multilang]', true).'" value="true" />';
                $output .= '</fieldset>';
                $output .= '<p class="ovic-text-desc">'.sprintf('%s ( <strong>%s</strong> )', esc_html__('You are editing language:', 'ovic-addon-toolkit'), $this->multilang['current']).'</p>';
            }

            return $output;
        }

        public static function field_data($type = '', $term = false, $query_args = array())
        {
            $options      = array();
            $array_search = false;

            // sanitize type name
            if (in_array($type, array('page', 'pages'))) {
                $option = 'page';
            } elseif (in_array($type, array('post', 'posts'))) {
                $option = 'post';
            } elseif (in_array($type, array('category', 'categories'))) {
                $option = 'category';
            } elseif (in_array($type, array('tag', 'tags'))) {
                $option = 'post_tag';
            } elseif (in_array($type, array('menu', 'menus'))) {
                $option = 'nav_menu';
            } else {
                $option = '';
            }

            // switch type
            switch ($type) {
                case 'page':
                case 'pages':
                case 'post':
                case 'posts':

                    // term query required for ajax select
                    if (!empty($term)) {
                        $query = new WP_Query(wp_parse_args($query_args, array(
                            's'              => $term,
                            'post_type'      => $option,
                            'post_status'    => 'publish',
                            'posts_per_page' => 25,
                        )));
                    } else {
                        $query = new WP_Query(wp_parse_args($query_args, array(
                            'post_type'   => $option,
                            'post_status' => 'publish',
                        )));
                    }

                    if (!is_wp_error($query) && !empty($query->posts)) {
                        foreach ($query->posts as $item) {
                            $value           = !empty($query_args['data-slug']) ? $item->post_name : $item->ID;
                            $options[$value] = $item->post_title;
                        }
                    }

                    break;

                case 'category':
                case 'categories':
                case 'tag':
                case 'tags':
                case 'menu':
                case 'menus':

                    if (!empty($term)) {
                        $query = new WP_Term_Query(wp_parse_args($query_args, array(
                            'search'     => $term,
                            'taxonomy'   => $option,
                            'hide_empty' => false,
                            'number'     => 25,
                        )));
                    } else {
                        $query = new WP_Term_Query(wp_parse_args($query_args, array(
                            'taxonomy'   => $option,
                            'hide_empty' => false,
                        )));
                    }

                    if (!is_wp_error($query) && !empty($query->terms)) {
                        foreach ($query->terms as $item) {
                            $value           = !empty($query_args['data-slug']) ? $item->slug : $item->term_id;
                            $options[$value] = $item->name;
                        }
                    }

                    break;

                case 'user':
                case 'users':

                    if (!empty($term)) {
                        $query = new WP_User_Query(array(
                            'search'  => '*'.$term.'*',
                            'number'  => 25,
                            'orderby' => 'title',
                            'order'   => 'ASC',
                            'fields'  => array('display_name', 'ID'),
                        ));
                    } else {
                        $query = new WP_User_Query(array('fields' => array('display_name', 'ID')));
                    }

                    if (!is_wp_error($query) && !empty($query->get_results())) {
                        foreach ($query->get_results() as $item) {
                            $options[$item->ID] = $item->display_name;
                        }
                    }

                    break;

                case 'sidebar':
                case 'sidebars':

                    global $wp_registered_sidebars;

                    if (!empty($wp_registered_sidebars)) {
                        foreach ($wp_registered_sidebars as $sidebar) {
                            $options[$sidebar['id']] = $sidebar['name'];
                        }
                    }

                    $array_search = true;

                    break;

                case 'role':
                case 'roles':

                    global $wp_roles;

                    if (!empty($wp_roles)) {
                        if (!empty($wp_roles->roles)) {
                            foreach ($wp_roles->roles as $role_key => $role_value) {
                                $options[$role_key] = $role_value['name'];
                            }
                        }
                    }

                    $array_search = true;

                    break;

                case 'post_type':
                case 'post_types':

                    $post_types = get_post_types(array('show_in_nav_menus' => true), 'objects');

                    if (!is_wp_error($post_types) && !empty($post_types)) {
                        foreach ($post_types as $post_type) {
                            $options[$post_type->name] = $post_type->labels->name;
                        }
                    }

                    $array_search = true;

                    break;

                case 'location':
                case 'locations':

                    $nav_menus = get_registered_nav_menus();

                    if (!is_wp_error($nav_menus) && !empty($nav_menus)) {
                        foreach ($nav_menus as $nav_menu_key => $nav_menu_name) {
                            $options[$nav_menu_key] = $nav_menu_name;
                        }
                    }

                    $array_search = true;

                    break;

                default:

                    if (is_callable($type)) {
                        if (!empty($term)) {
                            $options = call_user_func($type, $query_args);
                        } else {
                            $options = call_user_func($type, $term, $query_args);
                        }
                    }

                    break;
            }

            // Array search by "term"
            if (!empty($term) && !empty($options) && !empty($array_search)) {
                $options = preg_grep('/'.$term.'/i', $options);
            }

            // Make multidimensional array for ajax search
            if (!empty($term) && !empty($options)) {
                $arr = array();
                foreach ($options as $option_key => $option_value) {
                    $arr[] = array('value' => $option_key, 'text' => $option_value);
                }
                $options = $arr;
            }

            return $options;
        }

        public function field_wp_query_data_title($type, $values)
        {

            $options = array();

            // sanitize type name
            if (in_array($type, array('page', 'pages'))) {
                $option = 'page';
            } elseif (in_array($type, array('post', 'posts'))) {
                $option = 'post';
            } elseif (in_array($type, array('category', 'categories'))) {
                $option = 'category';
            } elseif (in_array($type, array('tag', 'tags'))) {
                $option = 'post_tag';
            } elseif (in_array($type, array('menu', 'menus'))) {
                $option = 'nav_menu';
            } else {
                $option = '';
            }

            if (!empty($values) && is_array($values)) {
                foreach ($values as $value) {
                    switch ($type) {
                        case 'post':
                        case 'posts':
                        case 'page':
                        case 'pages':

                            if (is_numeric($value)) {
                                $title = get_the_title($value);
                            } else {
                                $id    = get_page_by_path($value, OBJECT, $option);
                                $title = get_the_title($id);
                            }

                            if (!is_wp_error($title) && !empty($title)) {
                                $options[$value] = $title;
                            }

                            break;

                        case 'category':
                        case 'categories':
                        case 'tag':
                        case 'tags':
                        case 'menu':
                        case 'menus':

                            if (is_numeric($value)) {
                                $field = 'term_id';
                            } else {
                                $field = 'slug';
                            }

                            $term = get_term_by($field, $value, $option);

                            if (!is_wp_error($term) && !empty($term)) {
                                $options[$value] = $term->name;
                            }

                            break;

                        case 'user':
                        case 'users':

                            $user = get_user_by('id', $value);

                            if (!is_wp_error($user) && !empty($user)) {
                                $options[$value] = $user->display_name;
                            }

                            break;

                        case 'sidebar':
                        case 'sidebars':

                            global $wp_registered_sidebars;

                            if (!empty($wp_registered_sidebars[$value])) {
                                $options[$value] = $wp_registered_sidebars[$value]['name'];
                            }

                            break;

                        case 'role':
                        case 'roles':

                            global $wp_roles;

                            if (!empty($wp_roles) && !empty($wp_roles->roles) && !empty($wp_roles->roles[$value])) {
                                $options[$value] = $wp_roles->roles[$value]['name'];
                            }

                            break;

                        case 'post_type':
                        case 'post_types':

                            $post_types = get_post_types(array('show_in_nav_menus' => true));

                            if (!is_wp_error($post_types) && !empty($post_types) && !empty($post_types[$value])) {
                                $options[$value] = ucfirst($value);
                            }

                            break;

                        case 'location':
                        case 'locations':

                            $nav_menus = get_registered_nav_menus();

                            if (!is_wp_error($nav_menus) && !empty($nav_menus) && !empty($nav_menus[$value])) {
                                $options[$value] = $nav_menus[$value];
                            }

                            break;

                        default:

                            if (is_callable($type.'_title')) {
                                $options[$value] = call_user_func($type.'_title', $value);
                            }

                            break;
                    }
                }
            }

            return $options;
        }
    }
}
