<?php
/**
 * WooCommerce Template
 *
 * Functions for the templating system.
 *
 * @package  Ovic\Functions
 * @version  1.0.2
 */
defined('ABSPATH') || exit;

/**
 *
 * WOOCOMMERCE SINGLE PRODUCT BUY NOW
 *
 * using class: product-buy-now
 * input name: buy-now-redirect
 *
 * example: <a href="#" class="product-buy-now button">Buy Now</a>
 *          <input type="hidden" name="buy-now-redirect" value="0">
 */
if (!function_exists('ovic_redirect_cart_buy_now')) {
    function ovic_redirect_cart_buy_now($url, $adding_to_cart)
    {
        if (isset($_REQUEST['buy-now-redirect']) && $_REQUEST['buy-now-redirect'] == 1) {
            return wc_get_cart_url();
        }

        return $url;
    }
}
/**
 *
 * WOOCOMMERCE SINGLE PRODUCT BRAND
 */
if (!function_exists('ovic_woocommerce_single_product_brand')) {
    function ovic_woocommerce_single_product_brand()
    {
        global $product;
        $terms = get_the_terms($product->get_id(), 'product_brand');
        if (!empty($terms) && !is_wp_error($terms)) : ?>
            <div class="product-brand">
                <?php foreach ($terms as $term) : ?>
                    <?php
                    $term_url = get_term_link($term->term_id, 'product_brand');
                    $logo     = get_term_meta($term->term_id, 'logo_id', true);
                    ?>
                    <a href="<?php echo esc_url($term_url); ?>" class="brand-item">
                        <?php if ($logo) : ?>
                            <?php echo wp_get_attachment_image($logo, 'full'); ?>
                        <?php else: ?>
                            <?php echo esc_html($term->name); ?>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif;
    }
}
/**
 *
 * WOOCOMMERCE VARIABLE PRODUCT
 */
if (!function_exists('ovic_custom_available_variation')) {
    function ovic_custom_available_variation($data, $product, $variation)
    {
        if (isset($_POST['custom_data']) && $_POST['custom_data'] != '') {
            // GET SIZE IMAGE SETTING
            list($width, $height) = explode('x', sanitize_text_field($_POST['custom_data']));
            $image_variable             = ovic_resize_image($data['image_id'], $width, $height, true, false);
            $data['image']['src']       = $image_variable['url'];
            $data['image']['url']       = $image_variable['url'];
            $data['image']['full_src']  = $image_variable['url'];
            $data['image']['thumb_src'] = $image_variable['url'];
            $data['image']['srcset']    = $image_variable['url'];
            $data['image']['src_w']     = $width;
            $data['image']['src_h']     = $height;
        }

        return $data;
    }
}
/**
 *
 * TOTAL REVIEW
 */
if (!function_exists('ovic_customer_review')) {
    function ovic_customer_review()
    {
        global $product, $comment;

        $args         = array(
            'post_type'   => 'product',
            'post_status' => 'publish',
            'post_id'     => $product->get_id(),
        );
        $comments     = get_comments($args);
        $average      = $product->get_average_rating();
        $rating_count = $product->get_rating_count();
        $review_count = $product->get_review_count();
        $stars        = array(
            '5' => 0,
            '4' => 0,
            '3' => 0,
            '2' => 0,
            '1' => 0,
        );
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));
                if ($rating && '0' != $comment->comment_approved) {
                    $stars[$rating]++;
                }
            }
        }
        ovic_get_template(
            'single-product/review-average.php',
            array(
                'average'      => $average,
                'stars'        => $stars,
                'review_count' => $review_count,
                'rating_count' => $rating_count,
            )
        );
    }
}