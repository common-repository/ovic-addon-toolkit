<?php
/**
 * Ovic Uninstall
 *
 * Uninstalling Ovic deletes user roles, pages, tables, and options.
 *
 * @package Ovic\Uninstaller
 * @version 1.0.0
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb, $wp_version;

/*
 * Only remove ALL product and page data if OVIC_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if (defined('OVIC_REMOVE_ALL_DATA') && true === OVIC_REMOVE_ALL_DATA) {
    // Delete options.
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ovic\_%';");

    // Delete metakey.
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'ovic\_%';");

    // Delete posts + data.
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'ovic_footer', 'ovic_menu' );");

    // Delete transients.
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%'");

    // Delete terms if > WP 4.2 (term splitting was added in 4.2).
    if (version_compare($wp_version, '4.2', '>=')) {
        // Delete term taxonomies.
        foreach (array('product_brand') as $taxonomy) {
            $wpdb->delete(
                $wpdb->term_taxonomy,
                array(
                    'taxonomy' => $taxonomy,
                )
            );
        }
    }

    // Clear any cached data that has been removed.
    wp_cache_flush();
}
