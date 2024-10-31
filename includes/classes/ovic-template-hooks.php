<?php
/**
 * Ovic Template Hooks
 *
 * Action/filter hooks used for Ovic functions/templates.
 *
 * @package Ovic/Templates
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 *
 * REMOVE RAW CONTENT
 **/
add_filter('get_pagenum_link', function ($result) {
    $result = remove_query_arg('ovic_raw_content', $result);

    return $result;
});
/**
 *
 * WOOCOMMERCE VARIABLE PRODUCT
 */
add_filter('woocommerce_available_variation', 'ovic_custom_available_variation', 10, 3);
/**
 *
 * RESIZE IMAGE
 **/
//add_filter('wp_lazy_loading_enabled', 'ovic_image_lazy_loading', 10, 3);
//add_filter('wp_get_attachment_image_attributes', 'ovic_lazy_attachment_image', 10, 3);
add_filter('wp_kses_allowed_html', 'ovic_wp_kses_allowed_html', 10, 2);
add_filter('vc_wpb_getimagesize', 'ovic_vc_wpb_getimagesize', 10, 3);
add_filter('post_thumbnail_html', 'ovic_post_thumbnail_html', 10, 5);
add_filter('dokan_product_image_attributes', 'ovic_dokan_image_attributes', 10, 3);
/**
 *
 * BUTTON BUY NOW
 **/
add_filter('woocommerce_add_to_cart_redirect', 'ovic_redirect_cart_buy_now', 10, 2);
/**
 *
 * LAZYLOAD IMAGE
 **/
function ovic_lazyload_alter_html($content)
{
    // Don't do anything with the RSS feed.
    if (is_feed() || is_preview() || is_admin()) {
        return $content;
    }

    if (function_exists('amp_is_request') && amp_is_request()) {
        //for AMP pages the <picture> tag is not allowed
        return $content;
    }

    // Exit if it doesn't look like HTML (see #228)
    if (!preg_match("#^\\s*<#", $content)) {
        return $content;
    }

    $callback = function ($image) {
        // check is added lazyload
        if (strpos($image[0], 'lazyload') !== false) {
            return $image[0];
        }

        $placeholder = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        $find        = [' src=', ' srcset=', ' sizes='];
        $replace     = [' src="'.$placeholder.'" data-src=', ' srcset="'.$placeholder.'" data-srcset=', ' data-sizes='];

        if (strpos($image[0], ' loading=') === false) {
            $image[0] = str_replace('<img', '<img loading="lazy"', $image[0]);
            //$image[0] = str_replace('<iframe', '<iframe loading="lazy"', $image[0]);
        }

        if (strpos($image[0], ' class=') === false) {
            $image[0] = str_replace('<img', '<img class="lazyload"', $image[0]);
            //$image[0] = str_replace('<iframe', '<iframe class="lazyload"', $image[0]);
        } else {
            $find    = array_merge($find, [' class="', ' class=\'']);
            $replace = array_merge($replace, [' class="lazyload ', ' class=\'lazyload ']);
        }

        return str_replace($find, $replace, $image[0]);
    };

    return preg_replace_callback("/<img[^>]*>|<iframe[^>]*>/i", $callback, $content);
}

function ovic_lazyload_output_buffer()
{
    if (!is_admin() || (function_exists("wp_doing_ajax") && wp_doing_ajax()) || (defined('DOING_AJAX') && DOING_AJAX)) {
        if (!extension_loaded('zlib')) {
            ob_start('ob_gzhandler');
        }
        if (OVIC_CORE()->is_lazy()) {
            ob_start('ovic_lazyload_alter_html');
        }
    }
}

add_action('init', 'ovic_lazyload_output_buffer', 1);