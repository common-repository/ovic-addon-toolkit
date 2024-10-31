<?php
/**
 * Ovic Template
 *
 * Functions for the templating system.
 *
 * @package  Ovic\Functions
 * @version  1.0.2
 */
defined('ABSPATH') || exit;
/**
 *
 * ACTION
 * $functions = array(
 *      array( {action},{tag}, {callback},{priority}, {arg} ),
 *      array( {action},{tag}, {callback},{priority}, {arg} ),
 * );
 */
if (!function_exists('ovic_add_action')) {
    function ovic_add_action($functions, $reverse = false)
    {
        if (!empty($functions)) {
            foreach ($functions as $function) {
                $actions  = $function[0];
                $priority = isset($function[3]) ? $function[3] : 10;
                $args     = isset($function[4]) ? $function[4] : 1;
                if ($reverse) {
                    $search  = 'add_';
                    $replace = 'remove_';
                    if (strpos($actions, 'add_') === false) {
                        $search  = 'remove_';
                        $replace = 'add_';
                    }
                    $actions = str_replace($search, $replace, $actions);
                }
                call_user_func($actions, $function[1], $function[2], $priority, $args);
            }
        }
    }
}
/**
 * Call a shortcode function by tag name.
 *
 * @param  string  $tag  The shortcode whose function to call.
 * @param  array  $atts  The attributes to pass to the shortcode function. Optional.
 * @param  array  $content  The shortcode's content. Default is null (none).
 *
 * @return string|bool False on failure, the result of the shortcode on success.
 * @since  1.4.6
 *
 */
if (!function_exists('ovic_do_shortcode')) {
    function ovic_do_shortcode($tag, array $atts = array(), $content = null)
    {
        global $shortcode_tags;

        if (!isset($shortcode_tags[$tag])) {
            return false;
        }

        return call_user_func($shortcode_tags[$tag], $atts, $content, $tag);
    }
}
/**
 *
 * POST VIEW COUNT
 */
if (!function_exists('ovic_set_post_views')) {
    function ovic_set_post_views($postID = false, $post_type = 'post', $count_key = 'ovic_post_views_count')
    {
        if (!$postID) {
            $postID = get_the_ID();
        }
        if (get_post_type($postID) === $post_type) {
            $count = get_post_meta($postID, $count_key, true);
            if ($count == '') {
                delete_post_meta($postID, $count_key);
                add_post_meta($postID, $count_key, '0');
            } else {
                $count++;
                update_post_meta($postID, $count_key, $count);
            }
        }
    }
}
if (!function_exists('ovic_get_post_views')) {
    function ovic_get_post_views($postID = false, $count_key = 'ovic_post_views_count')
    {
        if (!$postID) {
            $postID = get_the_ID();
        }
        $count = get_post_meta($postID, $count_key, true);
        if ($count == '') {
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
            echo '0';
        }
        echo ovic_number_format_short($count);
    }
}
/**
 * @param $n
 *
 * @return string
 * Use to convert large positive numbers in to short form like 1K+, 100K+, 199K+, 1M+, 10M+, 1B+ etc
 */
if (!function_exists('ovic_number_format_short')) {
    function ovic_number_format_short($n)
    {
        if ($n >= 0 && $n < 1000) {
            // 1 - 999
            $n_format = floor($n);
            $suffix   = '';
        } elseif ($n >= 1000 && $n < 1000000) {
            // 1k-999k
            $n_format = floor($n / 1000);
            $suffix   = 'K+';
        } elseif ($n >= 1000000 && $n < 1000000000) {
            // 1m-999m
            $n_format = floor($n / 1000000);
            $suffix   = 'M+';
        } elseif ($n >= 1000000000 && $n < 1000000000000) {
            // 1b-999b
            $n_format = floor($n / 1000000000);
            $suffix   = 'B+';
        } elseif ($n >= 1000000000000) {
            // 1t+
            $n_format = floor($n / 1000000000000);
            $suffix   = 'T+';
        }

        return !empty($n_format) ? $n_format.$suffix : 0;
    }
}
/**
 *
 * POST LOAD MORE
 */
if (!function_exists('ovic_custom_pagination')) {
    function ovic_custom_pagination($options, $args = array())
    {
        global $wp_query;

        $defaults  = array(
            'pagination'    => 'pagination',// pagination, load_more, infinite
            'class'         => '',
            'animate'       => 'fadeInUp',
            'wrapper'       => '.site-content',
            'response'      => '.response-content',
            'text_loadmore' => esc_html__('Load more', 'ovic-addon-toolkit'),
            'text_infinite' => esc_html__('Loading', 'ovic-addon-toolkit'),
        );
        $total     = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        $paged     = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $options   = apply_filters('ovic_options_button_load_more', wp_parse_args($options, $defaults));
        $next_post = get_next_posts_page_link();

        if ($next_post && $total > 1) {

            echo '<div class="woocommerce-pagination pagination-nav type-'.$options['pagination'].'">';

            if ($options['pagination'] == 'pagination') {
                echo paginate_links($args);
            } else {
                ?>
                <a href="#" class="button-loadmore <?php echo esc_attr($options['class']); ?>"
                   data-url="<?php echo esc_attr($next_post); ?>"
                   data-wrapper="<?php echo esc_attr($options['wrapper']); ?>"
                   data-response="<?php echo esc_attr($options['response']); ?>"
                   data-animate="<?php echo esc_attr($options['animate']); ?>"
                   data-total="<?php echo esc_attr($total); ?>"
                   data-current="<?php echo esc_attr($paged); ?>">
                    <?php
                    if ($options['pagination'] == 'load_more') {
                        echo esc_html($options['text_loadmore']);
                    } else {
                        echo esc_html($options['text_infinite']);
                    }
                    ?>
                </a>
                <?php
            }

            echo '</div>';

        }
    }
}
if (!function_exists('ovic_share_button')) {
    function ovic_share_button($id = null)
    {
        ovic_get_template(
            'share-button/share-button.php',
            array(
                'id' => $id,
            )
        );
    }
}