<?php
/**
 * WooCommerce Template Draft
 *
 * Functions for the templating system.
 *
 * @package  Ovic\Functions
 * @version  1.0.2
 */
defined('ABSPATH') || exit;

add_action('wp_footer', function () {
    ?>
    <style type="text/css">
        .quantity-add-to-cart {
            position: relative;
        }

        .quantity-add-to-cart.open-qty .btn-add,
        .quantity-add-to-cart .added_to_cart {
            display: none;
        }

        .quantity-add-to-cart .btn-add.qty-change {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
        }

        .quantity-add-to-cart.loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 3;
            background-color: rgba(0, 0, 0, 0.6);
        }
    </style>
    <script type="application/javascript">
        jQuery(document).ready(function ($) {
            $(document).on('change', '.quantity-variable-items', function () {
                var select   = $(this),
                    quantity = select.parent().find('.quantity-add-to-cart');

                quantity.find('input').attr('data-variation_id', select.val());
                quantity.find('input').val('1').trigger('change');
                quantity.find('.qty-change').show();
                quantity.removeClass('open-qty');
            });

            $(document).on('click', '.quantity-add-to-cart .qty-change', function () {
                var button   = $(this),
                    quantity = button.closest('.quantity-add-to-cart'),
                    input    = quantity.find('input'),
                    qty      = parseInt(input.val());

                if (button.hasClass('qty-minus')) {
                    qty--;
                }
                if (button.hasClass('qty-add')) {
                    qty++;
                }

                if (input.val() >= 0) {
                    quantity.addClass('loading');
                    $.ajax({
                        type   : 'POST',
                        url    : button.attr('href'),
                        data   : {
                            quantity    : qty,
                            variation_id: parseInt(input.attr('data-variation_id')),
                        },
                        success: function (response) {
                            // Trigger event so themes can refresh other areas.
                            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);

                            // Change qty.
                            input.val(qty);

                            if (button.hasClass('btn-add')) {
                                quantity.addClass('open-qty');
                                button.hide();
                            }
                            quantity.removeClass('loading');
                        }
                    });
                }

                return false;
            });
        });
    </script>
    <?php
});

function ovic_change_quantity_add_to_cart()
{
    check_ajax_referer('nonce_quantity_add_to_cart', 'security');

    if (!isset($_REQUEST['product_id'])) {
        return;
    }

    $product_id   = apply_filters('woocommerce_add_to_cart_product_id', absint($_REQUEST['product_id']));
    $product_type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'add';
    $quantity     = !empty($_REQUEST['quantity']) ? $_REQUEST['quantity'] : 0;
    $variation_id = !empty($_REQUEST['variation_id']) ? $_REQUEST['variation_id'] : 0;

    if ($product_type == 'add') {
        WC()->cart->add_to_cart($product_id, 1, $variation_id);
    } else {
        $product_cart_id = WC()->cart->generate_cart_id($product_id, $variation_id);
        if ($variation_id > 0) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                if ($cart_item['product_id'] == $product_id && $cart_item['variation_id'] == $variation_id) {
                    $product_cart_id = $cart_item_key;
                    break;
                }
            }
        }
        WC()->cart->set_quantity($product_cart_id, $quantity);
    }

    // Return fragments
    WC_AJAX::get_refreshed_fragments();
}

add_action('wp_ajax_change_quantity_add_to_cart', 'ovic_change_quantity_add_to_cart');
add_action('wp_ajax_nopriv_change_quantity_add_to_cart', 'ovic_change_quantity_add_to_cart');

function ovic_woocommerce_quantity_add_to_cart()
{
    global $product;

    $product_id      = $product->get_id();
    $product_cart_id = WC()->cart->generate_cart_id($product_id);
    $cart_item       = WC()->cart->get_cart_item($product_cart_id);
    $min_value       = apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product);
    $max_value       = apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product);
    $quantity        = !empty($cart_item['quantity']) ? wc_stock_amount($cart_item['quantity']) : 1;
    $ajaxUrl         = add_query_arg(
        [
            'security'   => wp_create_nonce('nonce_quantity_add_to_cart'),
            'action'     => 'change_quantity_add_to_cart',
            'product_id' => $product_id,
        ],
        admin_url('admin-ajax.php')
    );
    $variation_id    = 0;
    $btnClass        = ' qty-change';
    $qtyClass        = !empty($cart_item) ? ' open-qty' : '';
    $urlAdd          = add_query_arg(['type' => 'add'], $ajaxUrl);
    $urlDel          = add_query_arg(['type' => 'del'], $ajaxUrl);
    $btnText         = 'ADD TO CART';
    if (!$product->is_type('simple') && !$product->is_type('variable')) {
        $btnClass = '';
        $urlAdd   = $product->get_permalink();
        $btnText  = $product->add_to_cart_text();
    }

    if ($product->is_type('variable')): ?>
        <select class="quantity-variable-items">
            <?php
            $variation_ids = $product->get_children();
            foreach ($variation_ids as $key => $id) {
                $selected        = '';
                $variation       = new WC_Product_Variation($id);
                $variation_name  = implode(', ', $variation->get_variation_attributes());
                $variation_title = "{$variation->get_title()} - {$variation_name}";
                if ($key == 0) {
                    $selected     = 'selected';
                    $variation_id = $variation->get_id();
                }
                echo "<option value='{$variation->get_id()}' {$selected}>$variation_title</option>";
            }
            wp_reset_postdata();
            ?>
        </select>
    <?php endif; ?>
    <div class="wrap-inner-quantity">
            <span class="price">
                <?php echo $product->get_price_html(); ?>
            </span>
        <div class="quantity-add-to-cart<?php echo esc_attr($qtyClass) ?>">
            <?php if ($product->is_type('simple') || $product->is_type('variable')): ?>
                <div class="quantity">
                    <a href="<?php echo esc_url($urlDel) ?>"
                       class="qty-change qty-minus"><i class="fa fa-minus" aria-hidden="true"></i>
                    </a>
                    <input type="text"
                           step="1"
                           min="<?php echo esc_attr($min_value) ?>"
                           max="<?php echo esc_attr($max_value) ?>"
                           name="quantity"
                           value="<?php echo esc_attr($quantity) ?>"
                           size="4"
                           placeholder=""
                           data-variation_id="<?php echo esc_attr($variation_id) ?>"
                           inputmode="numeric">
                    <a href="<?php echo esc_url($urlAdd) ?>"
                       class="qty-change qty-add"><i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                </div>
            <?php endif; ?>
            <a href="<?php echo esc_url($urlAdd) ?>"
               class="button btn-add<?php echo esc_attr($btnClass) ?>">
                <?php echo esc_html($btnText); ?>
            </a>
        </div>
    </div>
    <?php
}