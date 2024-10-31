<?php
/**
 * Ovic Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package Ovic\Functions
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!function_exists('is_ajax')) {
    /**
     * Is_ajax - Returns true when the page is loaded via ajax.
     *
     * @return bool
     */
    function is_ajax()
    {
        return function_exists('wp_doing_ajax') ? wp_doing_ajax() : defined('DOING_AJAX');
    }
}
if (!function_exists('ovic_install_widget')) {
    function ovic_install_widget($widget)
    {
        register_widget($widget);
    }
}
if (!function_exists('ovic_install_taxonomy')) {
    function ovic_install_taxonomy($slug, $object, $args)
    {
        register_taxonomy($slug, $object, $args);
    }
}
if (!function_exists('ovic_install_post_type')) {
    function ovic_install_post_type($slug, $args)
    {
        register_post_type($slug, $args);
    }
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param  string|array  $var  Data to sanitize.
 *
 * @return string|array
 */
if (!function_exists('ovic_clean')) {
    function ovic_clean($var)
    {
        if (is_array($var)) {
            return array_map('ovic_clean', $var);
        } else {
            return is_scalar($var) ? sanitize_text_field($var) : $var;
        }
    }
}

/**
 * Get permalink settings for things like products and taxonomies.
 *
 * As of 3.3.0, the permalink settings are stored to the option instead of
 * being blank and inheritting from the locale. This speeds up page loading
 * times by negating the need to switch locales on each page load.
 *
 * This is more inline with WP core behavior which does not localize slugs.
 *
 * @return array
 * @since  3.0.0
 */
if (!function_exists('ovic_get_permalink_structure')) {
    function ovic_get_permalink_structure()
    {
        $saved_permalinks = (array) get_option('ovic_addon_permalinks', array());
        $permalinks       = wp_parse_args(
            array_filter($saved_permalinks),
            array(
                'brand_base' => esc_html_x('product-brand', 'slug', 'ovic-addon-toolkit'),
            )
        );

        if ($saved_permalinks !== $permalinks) {
            update_option('ovic_addon_permalinks', $permalinks);
        }

        $permalinks['brand_rewrite_slug'] = untrailingslashit($permalinks['brand_base']);

        return $permalinks;
    }
}

/**
 * Sanitize permalink values before insertion into DB.
 *
 * Cannot use wc_clean because it sometimes strips % chars and breaks the user's setting.
 *
 * @param  string  $value  Permalink.
 *
 * @return string
 * @since  2.6.0
 */
if (!function_exists('ovic_sanitize_permalink')) {
    function ovic_sanitize_permalink($value)
    {
        global $wpdb;

        $value = $wpdb->strip_invalid_text_for_column($wpdb->options, 'option_value', $value);

        if (is_wp_error($value)) {
            $value = '';
        }

        $value = esc_url_raw(trim($value));
        $value = str_replace('http://', '', $value);

        return untrailingslashit($value);
    }
}

/**
 * Given a path, this will convert any of the subpaths into their corresponding tokens.
 *
 * @param  string  $path  The absolute path to tokenize.
 * @param  array  $path_tokens  An array keyed with the token, containing paths that should be replaced.
 *
 * @return string The tokenized path.
 * @since 4.3.0
 */
if (!function_exists('ovic_tokenize_path')) {
    function ovic_tokenize_path($path, $path_tokens)
    {
        // Order most to least specific so that the token can encompass as much of the path as possible.
        uasort(
            $path_tokens,
            function ($a, $b) {
                $a = strlen($a);
                $b = strlen($b);

                if ($a > $b) {
                    return -1;
                }

                if ($b > $a) {
                    return 1;
                }

                return 0;
            }
        );

        foreach ($path_tokens as $token => $token_path) {
            if (0 !== strpos($path, $token_path)) {
                continue;
            }

            $path = str_replace($token_path, '{{'.$token.'}}', $path);
        }

        return $path;
    }
}

/**
 * Given a tokenized path, this will expand the tokens to their full path.
 *
 * @param  string  $path  The absolute path to expand.
 * @param  array  $path_tokens  An array keyed with the token, containing paths that should be expanded.
 *
 * @return string The absolute path.
 * @since 4.3.0
 */

if (!function_exists('ovic_untokenize_path')) {
    function ovic_untokenize_path($path, $path_tokens)
    {
        foreach ($path_tokens as $token => $token_path) {
            $path = str_replace('{{'.$token.'}}', $token_path, $path);
        }

        return $path;
    }
}

/**
 * Fetches an array containing all of the configurable path constants to be used in tokenization.
 *
 * @return array The key is the define and the path is the constant.
 */
if (!function_exists('ovic_get_path_define_tokens')) {
    function ovic_get_path_define_tokens()
    {
        $defines = array(
            'ABSPATH',
            'WP_CONTENT_DIR',
            'WP_PLUGIN_DIR',
            'WPMU_PLUGIN_DIR',
            'PLUGINDIR',
            'WP_THEME_DIR',
        );

        $path_tokens = array();
        foreach ($defines as $define) {
            if (defined($define)) {
                $path_tokens[$define] = constant($define);
            }
        }

        return apply_filters('ovic_get_path_define_tokens', $path_tokens);
    }
}

/**
 * Add a template to the template cache.
 *
 * @param  string  $cache_key  Object cache key.
 * @param  string  $template  Located template.
 *
 * @since 4.3.0
 */
if (!function_exists('ovic_set_template_cache')) {
    function ovic_set_template_cache($cache_key, $template)
    {
        wp_cache_set($cache_key, $template, 'ovic');

        $cached_templates = wp_cache_get('cached_templates', 'ovic');
        if (is_array($cached_templates)) {
            $cached_templates[] = $cache_key;
        } else {
            $cached_templates = array($cache_key);
        }

        wp_cache_set('cached_templates', $cached_templates, 'ovic');
    }
}

/**
 * Clear the template cache.
 *
 * @since 4.3.0
 */
if (!function_exists('ovic_clear_template_cache')) {
    function ovic_clear_template_cache()
    {
        $cached_templates = wp_cache_get('cached_templates', 'ovic');
        if (is_array($cached_templates)) {
            foreach ($cached_templates as $cache_key) {
                wp_cache_delete($cache_key, 'ovic');
            }

            wp_cache_delete('cached_templates', 'ovic');
        }
    }
}
/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param  string  $template_name  Template name.
 * @param  string  $template_path  Template path. (default: '').
 * @param  string  $default_path  Default path. (default: '').
 *
 * @return string
 */
if (!function_exists('ovic_locate_template')) {
    function ovic_locate_template($template_name, $template_path = '', $default_path = '')
    {
        if (!$template_path) {
            $template_path = OVIC_CORE()->template_path();
        }

        if (!$default_path) {
            $default_path = OVIC_CORE()->plugin_path().'/templates/';
        }

        // Look within passed path within the theme - this is priority.
        $template = locate_template(array(
            trailingslashit($template_path).$template_name,
            $template_name,
        ));

        // Get default template/.
        if (!$template) {
            $template = $default_path.$template_name;
        }

        // Return what we found.
        return apply_filters('ovic_locate_template', $template, $template_name, $template_path);
    }
}

/**
 * Get template part (for templates like the shop-loop).
 *
 * @param  mixed  $slug  Template slug.
 * @param  string  $name  Template name (default: '').
 */
if (!function_exists('ovic_get_template_part')) {
    function ovic_get_template_part($slug, $name = '')
    {
        $cache_key = sanitize_key(implode('-', array('template-part', $slug, $name, OVIC_VERSION)));
        $template  = (string) wp_cache_get($cache_key, 'ovic');

        if (!$template) {
            if ($name) {
                $template = locate_template(array(
                    "{$slug}-{$name}.php",
                    OVIC_CORE()->template_path()."{$slug}-{$name}.php",
                ));

                if (!$template) {
                    $fallback = OVIC_CORE()->plugin_path()."/templates/{$slug}-{$name}.php";
                    $template = file_exists($fallback) ? $fallback : '';
                }
            }

            if (!$template) {
                // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php.
                $template = locate_template(array(
                    "{$slug}.php",
                    OVIC_CORE()->template_path()."{$slug}.php",
                ));
            }

            // Don't cache the absolute path so that it can be shared between web servers with different paths.
            $cache_path = ovic_tokenize_path($template, ovic_get_path_define_tokens());

            ovic_set_template_cache($cache_key, $cache_path);
        } else {
            // Make sure that the absolute path to the template is resolved.
            $template = ovic_untokenize_path($template, ovic_get_path_define_tokens());
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters('ovic_get_template_part', $template, $slug, $name);

        if ($template) {
            load_template($template, false);
        }
    }
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param  string  $template_name  Template name.
 * @param  array  $args  Arguments. (default: array).
 * @param  string  $template_path  Template path. (default: '').
 * @param  string  $default_path  Default path. (default: '').
 */
if (!function_exists('ovic_get_template')) {
    function ovic_get_template($template_name, $args = array(), $template_path = '', $default_path = '')
    {
        $cache_key = sanitize_key(implode('-', array(
            'template',
            $template_name,
            $template_path,
            $default_path,
            OVIC_VERSION
        )));
        $template  = (string) wp_cache_get($cache_key, 'ovic');

        if (!$template) {
            $template = ovic_locate_template($template_name, $template_path, $default_path);

            // Don't cache the absolute path so that it can be shared between web servers with different paths.
            $cache_path = ovic_tokenize_path($template, ovic_get_path_define_tokens());

            ovic_set_template_cache($cache_key, $cache_path);
        } else {
            // Make sure that the absolute path to the template is resolved.
            $template = ovic_untokenize_path($template, ovic_get_path_define_tokens());
        }

        // Allow 3rd party plugin filter template file from their plugin.
        $filter_template = apply_filters('ovic_get_template', $template, $template_name, $args, $template_path, $default_path);

        if ($filter_template !== $template) {
            if (!file_exists($filter_template)) {
                /* translators: %s template */
                ovic_doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'ovic-addon-toolkit'), '<code>'.$template.'</code>'), '2.1');

                return;
            }
            $template = $filter_template;
        }

        $action_args = array(
            'template_name' => $template_name,
            'template_path' => $template_path,
            'located'       => $template,
            'args'          => $args,
        );

        if (!empty($args) && is_array($args)) {
            if (isset($args['action_args'])) {
                ovic_doing_it_wrong(
                    __FUNCTION__,
                    __('action_args should not be overwritten when calling ovic_get_template.', 'ovic-addon-toolkit'),
                    '3.6.0'
                );
                unset($args['action_args']);
            }
            extract($args); // @codingStandardsIgnoreLine
        }

        do_action('ovic_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args']);

        include $action_args['located'];

        do_action('ovic_after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args']);
    }
}
/**
 * Like ovic_get_template, but returns the HTML instead of outputting.
 *
 * @param  string  $template_name  Template name.
 * @param  array  $args  Arguments. (default: array).
 * @param  string  $template_path  Template path. (default: '').
 * @param  string  $default_path  Default path. (default: '').
 *
 * @return string
 * @since 2.5.0
 * @see ovic_get_template
 */
if (!function_exists('ovic_get_template_html')) {
    function ovic_get_template_html($template_name, $args = array(), $template_path = '', $default_path = '')
    {
        ob_start();
        ovic_get_template($template_name, $args, $template_path, $default_path);

        return ob_get_clean();
    }
}
/**
 *
 * RESIZE IMAGE
 * svg: <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"></svg>
 **/
if (!function_exists('ovic_image_lazy_loading')) {
    function ovic_image_lazy_loading($default, $tag_name, $context)
    {
        if (OVIC_CORE()->is_lazy()) {
            return true;
        }

        return false;
    }
}
if (!function_exists('ovic_vc_wpb_getimagesize')) {
    function ovic_vc_wpb_getimagesize($img, $attach_id, $params)
    {
        if (OVIC_CORE()->is_lazy()) {
            $img['thumbnail'] = '<figure>'.$img['thumbnail'].'</figure>';
        }

        return $img;
    }
}
if (!function_exists('ovic_dokan_image_attributes')) {
    function ovic_dokan_image_attributes($image_attributes)
    {
        $image_attributes['img']['data-src'] = array();

        return $image_attributes;
    }
}
if (!function_exists('ovic_wp_kses_allowed_html')) {
    function ovic_wp_kses_allowed_html($allowedposttags, $context)
    {
        $allowedposttags['img']['data-src']    = true;
        $allowedposttags['img']['data-srcset'] = true;
        $allowedposttags['img']['data-sizes']  = true;

        return $allowedposttags;
    }
}
if (!function_exists('ovic_post_thumbnail_html')) {
    function ovic_post_thumbnail_html($html, $post_ID, $post_thumbnail_id, $size, $attr)
    {
        if (OVIC_CORE()->is_lazy()) {
            $html = '<figure>'.$html.'</figure>';
        }

        return $html;
    }
}
if (!function_exists('ovic_lazy_attachment_image')) {
    function ovic_lazy_attachment_image($attr, $attachment, $size)
    {
        if (!empty($attr['class']) && strpos($attr['class'], 'lazyload') !== false) {
            return $attr;
        }
        if (OVIC_CORE()->is_lazy()) {
            list($url, $width, $height) = wp_get_attachment_image_src($attachment->ID, $size);

            $attr['data-src'] = $attr['src'];
            $attr['src']      = OVIC_CORE()->image_svg($width, $height);

            if (!empty($attr['srcset'])) {
                $attr['data-srcset'] = $attr['srcset'];
                $attr['data-sizes']  = $attr['sizes'];
                unset($attr['srcset']);
                unset($attr['sizes']);
            }

            $attr['class'] .= ' lazyload';
        }

        return $attr;
    }
}
if (!function_exists('ovic_get_attachment_image')) {
    function ovic_get_attachment_image($attachment_id, $src, $width, $height, $lazy, $class)
    {
        $lazy = false;

        if ($src) {
            $hwstring   = image_hwstring($width, $height);
            $size       = $width.'x'.$height;
            $attachment = get_post($attachment_id);
            $attr       = array(
                'src'   => $src,
                'class' => "attachment-$size size-$size",
                'alt'   => trim(strip_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true))),
            );

            if ($class != '') {
                $attr['class'] .= " $class";
            }

            // Add `loading` attribute.
            if ($lazy == true) {
                $attr['data-src'] = $src;
                $attr['src']      = OVIC_CORE()->image_svg($width, $height);
                $attr['class']    .= ' lazyload';
            }

            // Add `loading` attribute.
            if (function_exists('wp_lazy_loading_enabled') && wp_lazy_loading_enabled('img', 'wp_get_attachment_image')) {
                $attr['loading'] = 'lazy';
            }

            // If `loading` attribute default of `lazy` is overridden for this
            // image to omit the attribute, ensure it is not included.
            if (array_key_exists('loading', $attr) && !$attr['loading']) {
                unset($attr['loading']);
            }

            /**
             * Filters the list of attachment image attributes.
             *
             * @param  array  $attr  Array of attribute values for the image markup, keyed by attribute name.
             *                                 See wp_get_attachment_image().
             * @param  WP_Post  $attachment  Image attachment post.
             * @param  string|array  $size  Requested size. Image size or array of width and height values
             *                                 (in that order). Default 'thumbnail'.
             *
             * @since 2.8.0
             *
             */
            $attr = apply_filters('wp_get_attachment_image_attributes', $attr, $attachment, $size);

            $attr = array_map('esc_attr', $attr);
            $html = rtrim("<img $hwstring");

            foreach ($attr as $name => $value) {
                $html .= " $name=".'"'.$value.'"';
            }

            $html .= ' />';
        }

        return apply_filters('ovic_resize_attachment_image_data',
            array(
                'url'    => $src,
                'width'  => $width,
                'height' => $height,
                'img'    => $html,
            ),
            $attachment_id, $src, $width, $height, $lazy
        );
    }
}
/**
 *    RESIZE IMAGE
 *
 *    Enable Lazy    : enable_lazy_load
 *    Disable Crop    : disable_crop_image
 *    Placeholder    : placeholder_image
 **/
if (!function_exists('ovic_resize_image')) {
    function ovic_resize_image($attachment_id, $width, $height, $crop = false, $use_lazy = false, $placeholder = true, $class = '')
    {
        $needs_resize      = true;
        $original          = false;
        $image_src         = array();
        $width             = absint($width);
        $height            = absint($height);
        $is_lazy           = OVIC_CORE()->is_lazy();
        $is_crop           = OVIC_CORE()->is_crop();
        $placeholder_image = OVIC_CORE()->placeholder();

        if ($is_lazy == false && $use_lazy == true) {
            $use_lazy = false;
        }
        if ($is_crop == true) {
            $crop = false;
        }
        if ($width == false && $height == false) {
            $original = true;
        }
        if (is_numeric($attachment_id)) {
            $image_src     = wp_get_attachment_image_src($attachment_id, 'full');
            $attached_file = get_attached_file($attachment_id);
            // this is not an attachment, let's use the image url
        } elseif (!empty($attachment_id) && @getimagesize($attachment_id)) {
            $img_url       = $attachment_id;
            $file_path     = parse_url($img_url);
            $attached_file = rtrim(ABSPATH, '/').$file_path['path'];
            $orig_size     = @getimagesize($attached_file);
            $image_src[0]  = $img_url;
            $image_src[1]  = $orig_size[0];
            $image_src[2]  = $orig_size[1];
        }

        if (!empty($attached_file)) {
            // checking if the full size
            if ($crop == false && $original == false) {
                $image_src[1] = $width;
                $image_src[2] = $height;
                $original     = true;
            }
            if ($original == true) {
                return ovic_get_attachment_image(
                    $attachment_id,
                    $image_src[0],
                    $image_src[1],
                    $image_src[2],
                    $use_lazy,
                    $class
                );
            }
            // Look through the attachment meta data for an image that fits our size.
            $meta = wp_get_attachment_metadata($attachment_id);
            if (!empty($meta['file'])) {
                $upload_dir = wp_upload_dir();
                $base_dir   = trim($upload_dir['basedir']);
                $base_url   = trim($upload_dir['baseurl']);
                $src        = trailingslashit($base_url).$meta['file'];
                $path       = trailingslashit($base_dir).$meta['file'];
                if (!empty($meta['sizes'])) {
                    foreach ($meta['sizes'] as $key => $size) {
                        if (($size['width'] == $width && $size['height'] == $height) || $key == sprintf('resized-%dx%d', $width, $height)) {
                            if (!empty($size['file'])) {
                                $file = str_replace(basename($path), $size['file'], $path);
                                if (file_exists($file)) {
                                    $needs_resize = false;
                                    $src          = str_replace(basename($src), $size['file'], $src);
                                }
                            }
                            break;
                        }
                    }
                }
                // checking if the file size is larger than the target size
                // if it is smaller or the same size, stop right here and return
                if ($needs_resize) {
                    $resized = image_make_intermediate_size($attached_file, $width, $height, $crop);

                    if (is_wp_error($resized)) {
                        return ovic_get_attachment_image(
                            $attachment_id,
                            $image_src[0],
                            $image_src[1],
                            $image_src[2],
                            $use_lazy,
                            $class
                        );
                    }
                    if (empty($resized)) {
                        $image_no_crop = wp_get_attachment_image_src($attachment_id, array($width, $height));

                        return ovic_get_attachment_image(
                            $attachment_id,
                            $image_no_crop[0],
                            $image_no_crop[1],
                            $image_no_crop[2],
                            $use_lazy,
                            $class
                        );
                    }

                    // Let metadata know about our new size.
                    $key                 = sprintf('resized-%dx%d', $width, $height);
                    $meta['sizes'][$key] = $resized;
                    if (!empty($resized['file'])) {
                        $src = str_replace(basename($src), $resized['file'], $src);
                    }
                    wp_update_attachment_metadata($attachment_id, $meta);

                    // Record in backup sizes so everything's cleaned up when attachment is deleted.
                    $backup_sizes = get_post_meta($attachment_id, '_wp_attachment_backup_sizes', true);
                    if (!is_array($backup_sizes)) {
                        $backup_sizes = array();
                    }
                    $backup_sizes[$key] = $resized;
                    update_post_meta($attachment_id, '_wp_attachment_backup_sizes', $backup_sizes);
                }

                // output image
                return ovic_get_attachment_image(
                    $attachment_id,
                    $src,
                    $width,
                    $height,
                    $use_lazy,
                    $class
                );
            }
        } elseif (!empty($image_src)) {
            return ovic_get_attachment_image(
                $attachment_id,
                $image_src[0],
                $image_src[1],
                $image_src[2],
                $use_lazy,
                $class
            );
        }
        // placeholder image
        if ($placeholder) {
            if (!empty($placeholder_image['id'])) {
                $placeholder_img = ovic_resize_image($placeholder_image['id'],
                    $width,
                    $height,
                    $crop,
                    $use_lazy,
                    $placeholder,
                    $class
                );
            } else {
                $placeholder_url = "https://via.placeholder.com/{$width}x{$height}?text={$width}x{$height}";
                $placeholder_img = array(
                    'url'    => $placeholder_url,
                    'width'  => $width,
                    'height' => $height,
                    'img'    => "<img class='attachment-{$width}x{$height} size-{$width}x{$height} {$class}' src='{$placeholder_url}' ".image_hwstring($width, $height)." alt='placeholder'>",
                );
            }
        } else {
            $placeholder_img = array(
                'url'    => '',
                'width'  => '',
                'height' => '',
                'img'    => '',
            );
        }

        return $placeholder_img;
    }
}

/*
Plugin Name: Disable Automatic Image Crop
Author: Wordpress Community
Description: wpse124009 - http://wordpress.stackexchange.com/questions/124009/why-wordpress-automatic-cropping-all-my-images and https://developer.wordpress.org/reference/functions/remove_image_size/
*/

//add_action('init', 'ovic_disable_extra_image_sizes');
//add_filter('image_resize_dimensions', 'ovic_disable_crop', 10, 6);

if (!function_exists('ovic_disable_crop')) {
    function ovic_disable_crop($enable, $orig_w, $orig_h, $dest_w, $dest_h, $crop)
    {
        if (OVIC_CORE()->is_crop()) {
            return false;
        }
        // Instantly disable this filter after the first run
        // remove_filter( current_filter(), __FUNCTION__ );
        // return image_resize_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, false );
        return $enable;
    }
}
if (!function_exists('ovic_disable_extra_image_sizes')) {
    function ovic_disable_extra_image_sizes()
    {
        if (OVIC_CORE()->is_crop()) {
            foreach (get_intermediate_image_sizes() as $size) {
                remove_image_size($size);
            }
        }
    }
}