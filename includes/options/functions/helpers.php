<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Array search key & value
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_array_search')) {
    function ovic_array_search($array, $key, $value)
    {

        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $sub_array) {
                $results = array_merge($results, ovic_array_search($sub_array, $key, $value));
            }

        }

        return $results;

    }
}
/**
 *
 * GET OPTION
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 **/
if (!function_exists('ovic_get_option')) {
    function ovic_get_option($option_name = '', $default = '', $key = '_ovic_customize_options')
    {
        $options = get_option($key);

        if (isset($_GET[$option_name])) {
            $default               = wp_filter_post_kses($_GET[$option_name]);
            $options[$option_name] = wp_filter_post_kses($_GET[$option_name]);
        }

        $options = apply_filters('ovic_get_framework_option', $options, $option_name, $default);

        if (!empty($options) && isset($options[$option_name])) {
            $option = $options[$option_name];
            if (is_array($option) && isset($option['multilang']) && $option['multilang'] == true) {
                if (defined('ICL_LANGUAGE_CODE')) {
                    if (isset($option[ICL_LANGUAGE_CODE])) {
                        return $option[ICL_LANGUAGE_CODE];
                    }
                } else {
                    $option = reset($option);
                }
            }

            return $option;
        } else {
            return $default;
        }
    }
}
/**
 *
 * Multi language option
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_get_multilang_option')) {
    function ovic_get_multilang_option($option_name = '', $default = '')
    {
        $value     = ovic_get_option($option_name, $default);
        $languages = ovic_language_defaults();
        $default   = $languages['default'];
        $current   = $languages['current'];
        if (is_array($value) && is_array($languages) && isset($value[$current])) {
            return $value[$current];
        } else {
            if ($default != $current) {
                return '';
            }
        }

        return $value;
    }
}
/**
 *
 * Multi language value
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_get_multilang_value')) {
    function ovic_get_multilang_value($value = '', $default = '')
    {
        $languages = ovic_language_defaults();
        $default   = $languages['default'];
        $current   = $languages['current'];
        if (is_array($value) && is_array($languages) && isset($value[$current])) {
            return $value[$current];
        } else {
            if ($default != $current) {
                return '';
            }
        }

        return $value;
    }
}
/**
 *
 * Add framework element
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_add_field')) {
    function ovic_add_field($field = array(), $value = '', $unique = '', $where = '', $parent = '')
    {
        return OVIC::field($field, $value, $unique, $where, $parent);
    }
}
/**
 *
 * Array search key & value
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_array_search')) {
    function ovic_array_search($array, $key, $value)
    {
        $results = array();
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }
            foreach ($array as $sub_array) {
                $results = array_merge($results, ovic_array_search($sub_array, $key, $value));
            }
        }

        return $results;
    }
}

/**
 *
 * Getting POST Var
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_get_var')) {
    function ovic_get_var($var, $default = '')
    {
        if (isset($_POST[$var])) {
            return wp_unslash($_POST[$var]);
        }
        if (isset($_GET[$var])) {
            return wp_unslash($_GET[$var]);
        }

        return $default;
    }
}
/**
 *
 * Getting POST Vars
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_get_vars')) {
    function ovic_get_vars($var, $depth, $default = '')
    {
        if (isset($_POST[$var][$depth])) {
            return wp_unslash($_POST[$var][$depth]);
        }
        if (isset($_GET[$var][$depth])) {
            return wp_unslash($_GET[$var][$depth]);
        }

        return $default;
    }
}
/**
 *
 * Between Microtime
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_microtime')) {
    function ovic_timeout($timenow, $starttime, $timeout = 30)
    {
        return (($timenow - $starttime) < $timeout) ? true : false;
    }
}
/**
 *
 * Check for wp editor api
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_wp_editor_api')) {
    function ovic_wp_editor_api()
    {
        global $wp_version;

        return version_compare($wp_version, '4.8', '>=');
    }
}
/**
 *
 * Encode string for backup options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_encode_string')) {
    function ovic_encode_string($string)
    {
        return json_encode(trim($string));
    }
}
/**
 *
 * Decode string for backup options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_decode_string')) {
    function ovic_decode_string($string)
    {
        return json_decode(wp_unslash(trim($string)), true);
    }
}
/**
 *
 * Getting Custom Options for Fields
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_get_custom_options')) {
    function ovic_get_custom_options()
    {
        $default = array(
            'key-1' => 'Key 1',
            'key-2' => 'Key 2',
            'key-3' => 'Key 3',
        );

        return $default;
    }
}
/**
 *
 * Get language defaults
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_language_defaults')) {
    function ovic_language_defaults()
    {
        $multilang = array();
        if (class_exists('SitePress') || class_exists('Polylang') || function_exists('qtrans_getSortedLanguages')) {
            if (class_exists('SitePress')) {
                global $sitepress;
                $multilang['default']   = $sitepress->get_default_language();
                $multilang['current']   = $sitepress->get_current_language();
                $multilang['languages'] = $sitepress->get_active_languages();
            } else {
                if (class_exists('Polylang')) {
                    global $polylang;
                    $current    = pll_current_language();
                    $default    = pll_default_language();
                    $current    = (empty($current)) ? $default : $current;
                    $poly_langs = $polylang->model->get_languages_list();
                    $languages  = array();
                    foreach ($poly_langs as $p_lang) {
                        $languages[$p_lang->slug] = $p_lang->slug;
                    }
                    $multilang['default']   = $default;
                    $multilang['current']   = $current;
                    $multilang['languages'] = $languages;
                } else {
                    if (function_exists('qtrans_getSortedLanguages')) {
                        global $q_config;
                        $multilang['default']   = $q_config['default_language'];
                        $multilang['current']   = $q_config['language'];
                        $multilang['languages'] = array_flip(qtrans_getSortedLanguages());
                    }
                }
            }
        }
        $multilang = apply_filters('ovic_language_defaults', $multilang);

        return (!empty($multilang)) ? $multilang : false;
    }
}
