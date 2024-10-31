<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Get icons from admin ajax
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_get_icons')) {
    function ovic_get_icons()
    {
        $content    = '';
        $nav        = '';
        $icon_lists = [];
        $nonce      = (!empty($_POST['nonce'])) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

        if (!wp_verify_nonce($nonce, 'ovic_icon_nonce')) {
            wp_send_json_error(array(
                'error' => esc_html__('Error: Nonce verification has failed. Please try again.', 'ovic-addon-toolkit')
            ));
        }

        ob_start();

        $icon_library = (OVIC::get_config('fontawesome') == 'fa4') ? 'fa4' : 'fa5';

        OVIC::include_plugin_file('fields/icon/'.$icon_library.'-icons.php');

        if (OVIC::get_config('fontawesome') == 'fa5' && OVIC::get_config('fa4_support') == 1) {
            OVIC::include_plugin_file('fields/icon/fa4-icons.php');
            $icon_lists = call_user_func('ovic_get_fa4_icons');
        }

        $icon_lists = array_merge($icon_lists, apply_filters('ovic_field_icon_add_icons',
            call_user_func('ovic_get_'.$icon_library.'_icons')
        ));

        if (!empty($icon_lists)) {
            foreach ($icon_lists as $key => $list) {

                $active         = '';
                $class_icon     = 'fa-folder';
                $sanitize_class = strtolower(sanitize_html_class($list['title']));
                $class          = $sanitize_class;

                if ($key > 0) {
                    $class .= ' hidden';
                } else {
                    $active     = 'ovic-section-active';
                    $class_icon = 'fa-folder-open';
                }

                $nav .= '<li><a href="#" data-active=".'.esc_attr($sanitize_class).'" class="'.esc_attr($active).'">';
                $nav .= '<span class="ovic-tab-icon fa '.esc_attr($class_icon).'"></span>'.esc_html($list['title']).'';
                $nav .= '</a></li>';

                $content .= '<div class="'.esc_attr($class).'">';

                foreach ($list['icons'] as $icon) {
                    $content .= '<i title="'.esc_attr($icon).'" class="'.esc_attr($icon).'"></i>';
                }

                $content .= '</div>';
            }
        } else {
            $content .= '<div class="ovic-text-error">'.esc_html__('No data provided by developer',
                    'ovic-addon-toolkit').'</div>';
        }

        wp_send_json_success(
            array(
                'nav'     => $nav,
                'content' => $content,
            )
        );

    }

    add_action('wp_ajax_ovic-get-icons', 'ovic_get_icons');
}
/**
 *
 * Export
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_export')) {
    function ovic_export()
    {

        $nonce  = (!empty($_GET['nonce'])) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';
        $unique = (!empty($_GET['unique'])) ? sanitize_text_field(wp_unslash($_GET['unique'])) : '';

        if (!wp_verify_nonce($nonce, 'ovic_backup_nonce')) {
            die(esc_html__('Error: Nonce verification has failed. Please try again.', 'ovic-addon-toolkit'));
        }

        if (empty($unique)) {
            die(esc_html__('Error: Options unique id could not valid.', 'ovic-addon-toolkit'));
        }

        // Export
        header('Content-Type: application/json');
        header('Content-disposition: attachment; filename=backup-'.gmdate('d-m-Y').'.json');
        header('Content-Transfer-Encoding: binary');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode(get_option($unique));

        die();

    }

    add_action('wp_ajax_ovic-export', 'ovic_export');
}
/**
 *
 * Import Ajax
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_import_ajax')) {
    function ovic_import_ajax()
    {

        $nonce  = (!empty($_POST['nonce'])) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        $unique = (!empty($_POST['unique'])) ? sanitize_text_field(wp_unslash($_POST['unique'])) : '';
        $data   = (!empty($_POST['data'])) ? wp_kses_post_deep(json_decode(wp_unslash(trim($_POST['data'])),
            true)) : array();

        if (!wp_verify_nonce($nonce, 'ovic_backup_nonce')) {
            wp_send_json_error(array(
                'error' => esc_html__('Error: Nonce verification has failed. Please try again.', 'ovic-addon-toolkit')
            ));
        }

        if (empty($unique)) {
            wp_send_json_error(array(
                'error' => esc_html__('Error: Options unique id could not valid.', 'ovic-addon-toolkit')
            ));
        }

        if (empty($data) || !is_array($data)) {
            wp_send_json_error(array(
                'error' => esc_html__('Error: Import data could not valid.', 'ovic-addon-toolkit')
            ));
        }

        // Success
        update_option($unique, $data);

        wp_send_json_success();

    }

    add_action('wp_ajax_ovic-import', 'ovic_import_ajax');
}

/**
 *
 * Reset Ajax
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_reset_ajax')) {
    function ovic_reset_ajax()
    {

        $nonce  = (!empty($_POST['nonce'])) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        $unique = (!empty($_POST['unique'])) ? sanitize_text_field(wp_unslash($_POST['unique'])) : '';

        if (!wp_verify_nonce($nonce, 'ovic_backup_nonce')) {
            wp_send_json_error(array(
                'error' => esc_html__('Error: Nonce verification has failed. Please try again.', 'ovic-addon-toolkit')
            ));
        }

        // Success
        delete_option($unique);

        wp_send_json_success();

    }

    add_action('wp_ajax_ovic-reset', 'ovic_reset_ajax');
}
/**
 *
 * Chosen Ajax
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_chosen_ajax')) {
    function ovic_chosen_ajax()
    {

        $nonce = (!empty($_POST['nonce'])) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        $type  = (!empty($_POST['type'])) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
        $term  = (!empty($_POST['term'])) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
        $query = (!empty($_POST['query_args'])) ? wp_kses_post_deep($_POST['query_args']) : array();

        if (!wp_verify_nonce($nonce, 'ovic_chosen_ajax_nonce')) {
            wp_send_json_error(array(
                'error' => esc_html__('Error: Nonce verification has failed. Please try again.', 'ovic-addon-toolkit')
            ));
        }

        if (empty($type) || empty($term)) {
            wp_send_json_error(array('error' => esc_html__('Error: Missing request arguments.', 'ovic-addon-toolkit')));
        }

        $capability = apply_filters('ovic_chosen_ajax_capability', 'manage_options');

        if (!current_user_can($capability)) {
            wp_send_json_error(array(
                'error' => esc_html__('You do not have required permissions to access.', 'ovic-addon-toolkit')
            ));
        }

        // Success
        $options = OVIC_Fields::field_data($type, $term, $query);

        wp_send_json_success($options);

    }

    add_action('wp_ajax_ovic-chosen', 'ovic_chosen_ajax');
}
/**
 *
 * Set icons for wp dialog
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if (!function_exists('ovic_set_icons')) {
    function ovic_set_icons()
    {
        ?>
        <div id="ovic-modal-icon" class="ovic-modal-v2 ovic-modal-icon">
            <div class="ovic-modal-table">
                <div class="ovic-modal-table-cell">
                    <div class="ovic-modal-overlay"></div>
                    <div class="ovic-modal-inner ovic ovic-theme-dark">
                        <div class="ovic-header">
                            <div class="ovic-header-inner">
                                <div class="ovic-header-left">
                                    <h1>
                                        <?php esc_html_e('Add Icon', 'ovic-addon-toolkit'); ?>
                                    </h1>
                                </div>
                                <div class="ovic-header-right">
                                    <div class="ovic-search-icon">
                                        <input type="text"
                                               placeholder="<?php esc_html_e('Search a Icon...',
                                                   'ovic-addon-toolkit'); ?>"
                                               class="ovic-icon-search"/>
                                    </div>
                                    <div class="ovic-buttons">
                                        <input class="button button-secondary ovic-warning-primary ovic-modal-close"
                                               type="button" value="<?php echo esc_html__('Close',
                                            'ovic-addon-toolkit'); ?>">
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="ovic-nav">
                            <ul></ul>
                        </div>
                        <div class="ovic-modal-content">
                            <div class="ovic-modal-loading">
                                <div class="ovic-loading"></div>
                            </div>
                            <div class="ovic-modal-load"></div>
                        </div>
                        <div class="ovic-nav-background"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}