<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Taxonomy Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!class_exists('OVIC_Profile')) {
    class OVIC_Profile extends OVIC_Abstract
    {
        // constants
        public $options  = array();
        public $errors   = array();
        public $abstract = 'profile';

        // run profile construct
        public function __construct($options)
        {
            // Get options profile
            $this->options = apply_filters('ovic_addon_options_profile', $options);

            // Actions profile
            add_action('show_user_profile', array(&$this, 'render_profile_form_fields'));
            add_action('edit_user_profile', array(&$this, 'render_profile_form_fields'));
            add_action('user_new_form', array(&$this, 'render_profile_form_fields'));

            // Update profile
            add_action('profile_update', array(&$this, 'save_profile'));
            add_action('user_register', array(&$this, 'save_profile'));
            add_action('delete_user', array(&$this, 'delete_profile'));

            // wp enqueue for typography and output css
            parent::__construct();
        }

        // instance
        public static function instance($options = array())
        {
            return new self($options);
        }

        // render profile add/edit form fields
        public function render_profile_form_fields($user)
        {
            $value     = '';
            $form_edit = ($user == 'add-new-user') ? false : true;
            $classname = ($form_edit) ? 'edit' : 'add';

            wp_nonce_field('ovic-profile', 'ovic-profile-nonce');

            do_action('ovic_html_profile_before');

            echo '<div class="ovic ovic-profile ovic-profile-'.$classname.'-fields ovic-onload">';

            foreach ($this->options as $option) {
                if ($form_edit) {
                    $value        = get_user_meta($user->ID, $option['id'], true);
                    $timenow      = round(microtime(true));
                    $expires      = (isset($value['_transient']['expires'])) ? $value['_transient']['expires'] : 0;
                    $errors       = (isset($value['_transient']['errors'])) ? $value['_transient']['errors'] : array();
                    $timein       = ovic_timeout($timenow, $expires, 30);
                    $this->errors = ($timein) ? $errors : array();
                }
                if (isset($option['title'])) {
                    echo '<h2>'.esc_html($option['title']).'</h2>';
                }
                if (isset($option['desc'])) {
                    echo '<p class="description">'.wp_specialchars_decode($option['desc']).'</p>';
                }
                foreach ($option['fields'] as $field) {
                    $is_field_error = $this->error_check($field);
                    if (!empty($is_field_error)) {
                        $field['_error'] = $is_field_error;
                    }
                    $default    = (isset($field['default'])) ? $field['default'] : '';
                    $elem_id    = (isset($field['id'])) ? $field['id'] : '';
                    $elem_value = (is_array($value) && isset($value[$elem_id])) ? $value[$elem_id] : $default;
                    if (!empty($option['prefix'])) {
                        $elem_value = get_user_meta($user->ID, "{$option['prefix']}{$elem_id}", true);
                        if (empty($elem_value)) {
                            $elem_value = $default;
                        }
                    }
                    echo OVIC::field($field, $elem_value, $option['id'], 'profile');
                }
            }

            echo '</div>';

            do_action('ovic_html_profile_after');
        }

        // save profile form fields
        public function save_profile($user_id)
        {
            if (wp_verify_nonce(ovic_get_var('ovic-profile-nonce'), 'ovic-profile')) {
                $errors  = array();
                $profile = ovic_get_var('profile');
                foreach ($this->options as $request_value) {
                    if ($profile == $request_value['profile']) {
                        $request_key = $request_value['id'];
                        $request     = ovic_get_var($request_key, array());
                        // ignore _nonce
                        if (isset($request['_nonce'])) {
                            unset($request['_nonce']);
                        }
                        // sanitize and validate
                        if (!empty($request_value['fields'])) {
                            foreach ($request_value['fields'] as $field) {
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
                                                $meta_value            = get_user_meta($user_id, $request_key, true);
                                                $errors[$field['id']]  = array(
                                                    'code'    => $field['id'],
                                                    'message' => $has_validated,
                                                    'type'    => 'error'
                                                );
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
                        $request['_transient']['expires'] = round(microtime(true));
                        if (!empty($errors)) {
                            $request['_transient']['errors'] = $errors;
                        }
                        $request = apply_filters('ovic_save_profile', $request, $request_key, $user_id);
                        if (empty($request)) {
                            if (!empty($request_value['prefix'])) {
                                foreach ($request as $key => $value) {
                                    if ($key != '_transient' && $key != '_restore' && $key != '_nonce') {
                                        delete_user_meta($user_id, "{$request_value['prefix']}{$key}", $value);
                                    }
                                }
                            }
                            delete_user_meta($user_id, $request_key);
                        } else {
                            if (!empty($request_value['prefix'])) {
                                foreach ($request as $key => $value) {
                                    if ($key != '_transient' && $key != '_restore' && $key != '_nonce') {
                                        unset($request[$key]);
                                        update_user_meta($user_id, "{$request_value['prefix']}{$key}", $value);
                                    }
                                }
                            }
                            update_user_meta($user_id, $request_key, $request);
                        }
                    }
                }
                set_transient('ovic-profile-transient', $errors, 10);
            }
        }

        // delete profile
        public function delete_profile($user_id)
        {
            foreach ($this->options as $request_value) {
                $request_key = $request_value['id'];
                delete_user_meta($user_id, $request_key);
            }
        }
    }
}
