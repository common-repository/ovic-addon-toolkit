<?php
/**
 * Handles taxonomies in admin
 *
 * @class    Ovic_Brand_Taxonomies
 * @version  2.3.10
 * @package  WooCommerce/Admin
 * @brand Class
 * @author   WooThemes
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Ovic_Brand_Taxonomies class.
 */
if (!class_exists('Ovic_Brand_Taxonomies')) {
    /**
     * Add Widgets.
     */
    require_once dirname(__FILE__).'/product-brand-widget.php';

    class Ovic_Brand_Taxonomies
    {
        /**
         * Class instance.
         *
         * @var Ovic_Brand_Taxonomies instance
         */
        protected static $instance = false;

        /**
         * Default brand ID.
         *
         * @var int
         */
        private $default_brand_id = 0;

        /**
         * Get class instance
         */
        public static function get_instance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor.
         */
        public function __construct()
        {
            // Default brand ID.
            $this->default_brand_id = get_option('default_product_brand', 0);

            add_action('woocommerce_after_register_taxonomy', array($this, 'register_product_taxonomy'));

            // Brand/term ordering
            add_action('create_term', array($this, 'create_term'), 5, 3);

            // Add form
            add_action('product_brand_add_form_fields', array($this, 'add_brand_fields'));
            add_action('product_brand_edit_form_fields', array($this, 'edit_brand_fields'), 10);
            add_action('created_term', array($this, 'save_brand_fields'), 10, 3);
            add_action('edit_term', array($this, 'save_brand_fields'), 10, 3);

            // Add columns
            add_filter('manage_edit-product_brand_columns', array($this, 'product_brand_columns'));
            add_filter('manage_product_brand_custom_column', array($this, 'product_brand_column'), 10, 3);

            // Add row actions.
            add_filter('product_brand_row_actions', array($this, 'product_brand_row_actions'), 10, 2);
            add_filter('admin_init', array($this, 'handle_product_brand_row_actions'));

            // Taxonomy page descriptions.
            add_action('product_brand_pre_add_form', array($this, 'product_brand_description'));
            add_action('after-product_brand-table', array($this, 'product_brand_notes'));

            $attribute_taxonomies = wc_get_attribute_taxonomies();

            if (!empty($attribute_taxonomies)) {
                foreach ($attribute_taxonomies as $attribute) {
                    add_action('pa_'.$attribute->attribute_name.'_pre_add_form',
                        array($this, 'product_attribute_description'));
                }
            }

            // Maintain hierarchy of terms
            add_filter('wp_terms_checklist_args', array($this, 'disable_checked_ontop'));

            // Admin footer scripts for this product categories admin screen.
            add_action('admin_footer', array($this, 'scripts_at_product_brand_screen_footer'));

            // Add tab brand single product.
            add_filter('woocommerce_product_tabs', array($this, 'product_tabs'));

            // Add sort brand.
            add_filter('woocommerce_sortable_taxonomies', array($this, 'sortable_taxonomies'));

            // SEO brand.
            add_filter('woocommerce_structured_data_product', array($this, 'structured_data_product'), 10, 2);
        }

        function structured_data_product($markup, $product)
        {
            $terms = get_the_terms($product->get_id(), 'product_brand');

            if ($product->get_sku()) {
                $markup['mpn'] = $product->get_sku();
            } else {
                $markup['mpn'] = $product->get_id();
            }

            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $key => $term) {
                    $markup['brand'] = $term->name;
                }
            }

            return $markup;
        }

        function sortable_taxonomies($sorting)
        {
            $sorting[] = 'product_brand';

            return $sorting;
        }

        function register_product_taxonomy()
        {
            $permalinks = ovic_get_permalink_structure();

            register_taxonomy('product_brand',
                apply_filters('woocommerce_taxonomy_objects_product_cat', array('product')),
                array(
                    'hierarchical'          => true,
                    'update_count_callback' => '_wc_term_recount',
                    'label'                 => esc_html__('Brands', 'ovic-addon-toolkit'),
                    'labels'                => array(
                        'name'              => esc_html__('Product brands', 'ovic-addon-toolkit'),
                        'singular_name'     => esc_html__('Brands', 'ovic-addon-toolkit'),
                        'menu_name'         => esc_html_x('Brands', 'Admin menu name', 'ovic-addon-toolkit'),
                        'search_items'      => esc_html__('Search brands', 'ovic-addon-toolkit'),
                        'all_items'         => esc_html__('All brands', 'ovic-addon-toolkit'),
                        'parent_item'       => esc_html__('Parent brand', 'ovic-addon-toolkit'),
                        'parent_item_colon' => esc_html__('Parent brand:', 'ovic-addon-toolkit'),
                        'edit_item'         => esc_html__('Edit brand', 'ovic-addon-toolkit'),
                        'update_item'       => esc_html__('Update brand', 'ovic-addon-toolkit'),
                        'add_new_item'      => esc_html__('Add new brand', 'ovic-addon-toolkit'),
                        'new_item_name'     => esc_html__('New brand name', 'ovic-addon-toolkit'),
                        'not_found'         => esc_html__('No brands found', 'ovic-addon-toolkit'),
                    ),
                    'show_ui'               => true,
                    'query_var'             => true,
                    'capabilities'          => array(
                        'manage_terms' => 'manage_product_terms',
                        'edit_terms'   => 'edit_product_terms',
                        'delete_terms' => 'delete_product_terms',
                        'assign_terms' => 'assign_product_terms',
                    ),
                    'rewrite'               => array(
                        'slug'         => $permalinks['brand_rewrite_slug'],
                        'with_front'   => false,
                        'hierarchical' => true,
                    ),
                )
            );
        }

        function product_tabs($tabs)
        {
            global $product;

            $terms = get_the_terms($product->get_id(), 'product_brand');
            if (!empty($terms) && !is_wp_error($terms)) {
                $tabs['ovic_brands'] = array(
                    'title'    => sprintf(esc_html__('Brands (%d)', 'ovic-addon-toolkit'), count($terms)),
                    'priority' => 50,
                    'callback' => array($this, 'tab_brand_content'),
                );
            }

            return $tabs;
        }

        function tab_brand_content()
        {
            global $product;

            $terms = get_the_terms($product->get_id(), 'product_brand');
            if (!empty($terms) && !is_wp_error($terms)) : ?>
                <div class="product-tab-brands">
                    <?php foreach ($terms as $term) : ?>
                        <?php
                        $term_url = get_term_link($term->term_id, 'product_brand');
                        $logo     = get_term_meta($term->term_id, 'logo_id', true);
                        ?>
                        <div class="brand-item">
                            <?php if (!empty($logo)): ?>
                                <div class="term-thumbnail">
                                    <a href="<?php echo esc_url($term_url); ?>" class="brand-link">
                                        <figure><?php echo wp_get_attachment_image($logo, 'full'); ?></figure>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <h3 class="term-name">
                                <a href="<?php echo esc_url($term_url); ?>" class="brand-link">
                                    <?php echo esc_html($term->name); ?>
                                </a>
                            </h3>
                            <?php if (!empty($term->description)): ?>
                                <div class="term-description">
                                    <?php echo wc_format_content($term->description); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif;
        }

        /**
         * Order term when created (put in position 0).
         *
         * @param  mixed  $term_id
         * @param  mixed  $tt_id
         * @param  string  $taxonomy
         */
        public function create_term($term_id, $tt_id = '', $taxonomy = '')
        {
            if ('product_brand' != $taxonomy && !taxonomy_is_product_attribute($taxonomy)) {
                return;
            }

            $meta_name = taxonomy_is_product_attribute($taxonomy) ? 'order_'.esc_attr($taxonomy) : 'order';

            update_term_meta($term_id, $meta_name, 0);
        }

        /**
         * Brand thumbnail fields.
         */
        public function add_brand_fields()
        {
            ?>
            <div class="form-field term-thumbnail-wrap">
                <label><?php esc_html_e('Brand Logo', 'ovic-addon-toolkit'); ?></label>
                <div class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_logo_id"/>
                        <button type="button" class="upload_image_button button">
                            <?php esc_html_e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                        </button>
                        <button type="button" class="remove_image_button button" style="display: none;">
                            <?php esc_html_e('Remove image', 'ovic-addon-toolkit'); ?>
                        </button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-field term-thumbnail-wrap">
                <label><?php esc_html_e('Banner', 'ovic-addon-toolkit'); ?></label>
                <div class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_thumbnail_id"/>
                        <button type="button" class="upload_image_button button">
                            <?php esc_html_e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                        </button>
                        <button type="button" class="remove_image_button button" style="display: none;">
                            <?php esc_html_e('Remove image', 'ovic-addon-toolkit'); ?>
                        </button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <?php
        }

        /**
         * Edit brand thumbnail field.
         *
         * @param  mixed  $term  Term (brand) being edited
         */
        public function edit_brand_fields($term)
        {
            $logo_id         = absint(get_term_meta($term->term_id, 'logo_id', true));
            $thumbnail_id    = absint(get_term_meta($term->term_id, 'thumbnail_id', true));
            $logo_image      = $logo_id ? wp_get_attachment_thumb_url($logo_id) : wc_placeholder_img_src();
            $thumbnail_image = $thumbnail_id ? wp_get_attachment_thumb_url($thumbnail_id) : wc_placeholder_img_src();
            ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label><?php esc_html_e('Brand Logo', 'ovic-addon-toolkit'); ?></label>
                </th>
                <td class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url($logo_image); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_logo_id"
                               value="<?php echo $logo_id; ?>"/>
                        <button type="button" class="upload_image_button button">
                            <?php esc_html_e('Upload/Add logo', 'ovic-addon-toolkit'); ?>
                        </button>
                        <button type="button" class="remove_image_button button">
                            <?php esc_html_e('Remove logo', 'ovic-addon-toolkit'); ?>
                        </button>
                    </div>
                    <div class="clear"></div>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top"><label><?php esc_html_e('Thumbnail', 'ovic-addon-toolkit'); ?></label>
                </th>
                <td class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url($thumbnail_image); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_thumbnail_id"
                               value="<?php echo $thumbnail_id; ?>"/>
                        <button type="button" class="upload_image_button button">
                            <?php esc_html_e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                        </button>
                        <button type="button" class="remove_image_button button">
                            <?php esc_html_e('Remove image', 'ovic-addon-toolkit'); ?>
                        </button>
                    </div>
                    <div class="clear"></div>
                </td>
            </tr>
            <?php
        }

        /**
         * save_brand_fields function.
         *
         * @param  mixed  $term_id  Term ID being saved
         * @param  mixed  $tt_id
         * @param  string  $taxonomy
         */
        public function save_brand_fields($term_id, $tt_id = '', $taxonomy = '')
        {
            if ('product_brand' === $taxonomy) {
                if (isset($_POST['product_brand_thumbnail_id'])) {
                    update_term_meta($term_id, 'thumbnail_id', absint($_POST['product_brand_thumbnail_id']));
                }
                if (isset($_POST['product_brand_logo_id'])) {
                    update_term_meta($term_id, 'logo_id', absint($_POST['product_brand_logo_id']));
                }
            }
        }

        /**
         * Thumbnail column added to brand admin.
         *
         * @param  mixed  $columns
         *
         * @return array
         */
        public function product_brand_columns($columns)
        {
            $new_columns = array();
            if (isset($columns['cb'])) {
                $new_columns['cb'] = $columns['cb'];
                unset($columns['cb']);
            }
            $new_columns['logo']  = esc_html__('Logo', 'ovic-addon-toolkit');
            $new_columns['thumb'] = esc_html__('Image', 'ovic-addon-toolkit');
            $columns              = array_merge($new_columns, $columns);
            $columns['handle']    = '';

            return $columns;
        }

        /**
         * Adjust row actions.
         *
         * @param  array  $actions  Array of actions.
         * @param  object  $term  Term object.
         *
         * @return array
         */
        public function product_brand_row_actions($actions, $term)
        {
            $default_brand_id = absint(get_option('default_product_brand', 0));
            if ($default_brand_id !== $term->term_id && current_user_can('edit_term', $term->term_id)) {
                $actions['make_default'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    wp_nonce_url('edit-tags.php?action=make_default&amp;taxonomy=product_brand&amp;tag_ID='.absint($term->term_id),
                        'make_default_'.absint($term->term_id)),
                    /* translators: %s: taxonomy term name */
                    esc_attr(sprintf(esc_html__('Make &#8220;%s&#8221; the default brand', 'ovic-addon-toolkit'),
                        $term->name)),
                    esc_html__('Make default', 'ovic-addon-toolkit')
                );
            }

            return $actions;
        }

        /**
         * Handle custom row actions.
         */
        public function handle_product_brand_row_actions()
        {
            if (isset($_GET['action'], $_GET['tag_ID'], $_GET['_wpnonce']) && 'make_default' === $_GET['action']) {
                $make_default_id = absint($_GET['tag_ID']);
                if (wp_verify_nonce($_GET['_wpnonce'],
                        'make_default_'.$make_default_id) && current_user_can('edit_term', $make_default_id)) {
                    update_option('default_product_brand', $make_default_id);
                }
            }
        }

        /**
         * Description for product_cat page to aid users.
         */
        public function product_brand_description()
        {
            echo wp_kses(
                wpautop(__('Product brands for your store can be managed here. To change the order of brands on the front-end you can drag and drop to sort them. To see more brands listed click the "screen options" link at the top-right of this page.',
                    'ovic-addon-toolkit')),
                array('p' => array())
            );
        }

        /**
         * Add some notes to describe the behavior of the default brand.
         */
        public function product_brand_notes()
        {
            $brand_id   = get_option('default_product_cat', 0);
            $brand      = get_term($brand_id, 'product_cat');
            $brand_name = (!$brand || is_wp_error($brand)) ? esc_html_x('Uncategorized', 'Default brand slug',
                'ovic-addon-toolkit') : $brand->name;
            ?>
            <div class="form-wrap edit-term-notes">
                <p>
                    <strong><?php esc_html_e('Note:', 'ovic-addon-toolkit'); ?></strong><br>
                    <?php
                    printf(
                    /* translators: %s: default brand */
                        esc_html__('Deleting a brand does not delete the products in that brand. Instead, products that were only assigned to the deleted brand are set to the brand %s.',
                            'ovic-addon-toolkit'),
                        '<strong>'.esc_html($brand_name).'</strong>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }

        /**
         * Description for shipping class page to aid users.
         */
        public function product_attribute_description()
        {
            echo wp_kses(
                wpautop(__('Attribute terms can be assigned to products and variations.<br/><br/><b>Note</b>: Deleting a term will remove it from all products and variations to which it has been assigned. Recreating a term will not automatically assign it back to products.',
                    'ovic-addon-toolkit')),
                array('p' => array())
            );
        }

        /**
         * Thumbnail column value added to brand admin.
         *
         * @param  string  $columns
         * @param  string  $column
         * @param  int  $id
         *
         * @return string
         */
        public function product_brand_column($columns, $column, $id)
        {
            if ('thumb' === $column) {
                // Prepend tooltip for default brand.
                $default_brand_id = absint(get_option('default_product_brand', 0));
                if ($default_brand_id === $id) {
                    $columns .= wc_help_tip(esc_html__('This is the default brand and it cannot be deleted. It will be automatically assigned to products with no brand.',
                        'ovic-addon-toolkit'));
                }
                $thumbnail_id = get_term_meta($id, 'thumbnail_id', true);
                if ($thumbnail_id) {
                    $thumbnail = wp_get_attachment_thumb_url($thumbnail_id);
                } else {
                    $thumbnail = wc_placeholder_img_src();
                }
                // Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605
                $thumbnail = str_replace(' ', '%20', $thumbnail);
                $columns   .= '<img src="'.esc_url($thumbnail).'" alt="'.esc_attr__('Thumbnail',
                        'ovic-addon-toolkit').'" class="wp-post-image" height="48" width="48" />';
            }
            if ('logo' === $column) {
                // Prepend tooltip for default brand.
                $default_brand_id = absint(get_option('default_product_brand', 0));
                if ($default_brand_id === $id) {
                    $columns .= wc_help_tip(esc_html__('This is the default brand and it cannot be deleted. It will be automatically assigned to products with no brand.',
                        'ovic-addon-toolkit'));
                }
                $logo_id = get_term_meta($id, 'logo_id', true);
                if ($logo_id) {
                    $logo = wp_get_attachment_thumb_url($logo_id);
                } else {
                    $logo = wc_placeholder_img_src();
                }
                // Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605
                $logo    = str_replace(' ', '%20', $logo);
                $columns .= '<img src="'.esc_url($logo).'" alt="'.esc_attr__('Logo',
                        'ovic-addon-toolkit').'" class="wp-post-image" height="48" width="48" />';
            }
            if ('handle' === $column) {
                $columns .= '<input type="hidden" name="term_id" value="'.esc_attr($id).'" />';
            }

            return $columns;
        }

        /**
         * Maintain term hierarchy when editing a product.
         *
         * @param  array  $args
         *
         * @return array
         */
        public function disable_checked_ontop($args)
        {
            if (!empty($args['taxonomy']) && 'product_brand' === $args['taxonomy']) {
                $args['checked_ontop'] = false;
            }

            return $args;
        }

        /**
         * Admin footer scripts for the product categories admin screen
         *
         * @return void
         */
        public function scripts_at_product_brand_screen_footer()
        {
            if (!isset($_GET['taxonomy']) || 'product_brand' !== $_GET['taxonomy']) { // WPCS: CSRF ok, input var ok.
                return;
            }

            wp_enqueue_style('product-brand',
                trailingslashit(plugin_dir_url(__FILE__)).'product-brand.css',
                array(), '1.0'
            );
            wp_enqueue_script('product-brand',
                trailingslashit(plugin_dir_url(__FILE__)).'product-brand.js',
                array(), '1.0', true
            );
            wp_localize_script('product-brand', 'product_brand_params', array(
                    'placeholder' => wc_placeholder_img_src(),
                )
            );
            // Ensure the tooltip is displayed when the image column is disabled on product categories.
            wc_enqueue_js(
                "(function( $ ) {
				'use strict';
				var product_brand = $( 'tr#tag-".absint($this->default_brand_id)."' );
				product_brand.find( 'th' ).empty();
				product_brand.find( 'td.thumb span' ).detach( 'span' ).appendTo( product_brand.find( 'th' ) );
			})( jQuery );"
            );
        }
    }

    $ovic_admin_taxonomies = Ovic_Brand_Taxonomies::get_instance();
}