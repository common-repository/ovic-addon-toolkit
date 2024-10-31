<?php
/**
 * Layered Navigation Widget.
 *
 * @author   WooThemes
 * @category Widgets
 * @package  WooCommerce/Widgets
 * @version 1.0.0
 * @extends  WC_Widget
 */

use Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer;
use Automattic\Jetpack\Constants;

defined('ABSPATH') || exit;

if (!class_exists('Ovic_Attribute_Product_Widget')) {
    class Ovic_Attribute_Product_Widget extends WC_Widget
    {
        public $width           = 20;
        public $height          = 20;
        public $image_size      = 'thumbnail';
        public $font_size       = 16;
        public $clear_transient = '';

        /**
         * Constructor.
         */
        public function __construct()
        {
            if (class_exists('woo_variation_swatches')) {
                $this->width           = woo_variation_swatches()->get_option('width');
                $this->height          = woo_variation_swatches()->get_option('height');
                $this->image_size      = woo_variation_swatches()->get_option('attribute_image_size');
                $this->font_size       = woo_variation_swatches()->get_option('single-font-size');
                $this->clear_transient = 'wvs_clear_transient';
            } elseif (class_exists('rtwpvs')) {
                $this->width           = rtwpvs()->get_option('width');
                $this->height          = rtwpvs()->get_option('height');
                $this->image_size      = rtwpvs()->get_option('attribute_image_size');
                $this->font_size       = rtwpvs()->get_option('single-font-size');
                $this->clear_transient = 'rtwpvs_clear_transient';
            }

            $this->widget_cssclass    = 'ovic_widget_layered_nav widget_layered_nav';
            $this->widget_description = esc_html__('Shows a custom attribute in a widget.', 'ovic-addon-toolkit');
            $this->widget_id          = 'ovic_woocommerce_layered_nav';
            $this->widget_name        = esc_html__('Ovic: Attribute Product', 'ovic-addon-toolkit');
            parent::__construct();
        }

        /**
         * Updates a particular instance of a widget.
         *
         * @param  array  $new_instance
         * @param  array  $old_instance
         *
         * @return array
         * @see WP_Widget->update
         *
         */
        public function update($new_instance, $old_instance)
        {
            $this->init_settings();

            return parent::update($new_instance, $old_instance);
        }

        /**
         * Outputs the settings update form.
         *
         * @param  array  $instance
         *
         * @see WP_Widget->form
         *
         */
        public function form($instance)
        {
            $defaults             = array(
                'title'        => esc_html__('Filter by', 'ovic-addon-toolkit'),
                'attribute'    => '',
                'display_type' => 'list',
                'query_type'   => 'and',
                'width'        => $this->width,
                'height'       => $this->height,
            );
            $instance             = wp_parse_args((array) $instance, $defaults);
            $attribute_array      = array();
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $attribute_default    = $instance['attribute'];
            if (!empty($attribute_taxonomies)) {
                foreach ($attribute_taxonomies as $tax) {
                    if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) {
                        if (!$attribute_default) {
                            $attribute_default = $tax->attribute_name;
                        }
                        $attribute_array[$tax->attribute_name] = array(
                            'id'      => $tax->attribute_id,
                            'name'    => $tax->attribute_name,
                            'label'   => $tax->attribute_label,
                            'type'    => $tax->attribute_type,
                            'orderby' => $tax->attribute_orderby,
                        );
                    }
                }
            }
            $match          = array('color', 'image', 'button');
            $hide           = 'display:none';
            $attribute_list = !empty($attribute_array[$attribute_default]['type']) ? $attribute_array[$attribute_default]['type'] : array();
            if (in_array($attribute_list, $match) && $instance['display_type'] == 'box') {
                $hide = 'display:block';
            }
            ?>
            <div class="ovic_layered_container">
                <p>
                    <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title:',
                            'ovic-addon-toolkit'); ?></label>
                    <input type="text" class="widefat"
                           id="<?php echo $this->get_field_id('title'); ?>"
                           name="<?php echo $this->get_field_name('title'); ?>"
                           value="<?php echo $instance['title']; ?>"/>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id('attribute'); ?>"><?php esc_html_e('Product attribute:',
                            'ovic-addon-toolkit'); ?></label>
                    <select class="widefat ovic_layered_attribute"
                            id="<?php echo $this->get_field_id('attribute'); ?>"
                            name="<?php echo $this->get_field_name('attribute'); ?>">
                        <?php foreach ($attribute_array as $attribute): ?>
                            <option value="<?php echo esc_attr($attribute['name']) ?>"
                                <?php echo esc_attr($instance['attribute'] == $attribute['name'] ? 'selected' : ''); ?>
                                    data-type="<?php echo esc_attr($attribute['type']); ?>"><?php echo esc_html($attribute['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id('display_type'); ?>"><?php esc_html_e('Display type:',
                            'ovic-addon-toolkit'); ?></label>
                    <select class="widefat ovic_layered_display_type"
                            id="<?php echo $this->get_field_id('display_type'); ?>"
                            name="<?php echo $this->get_field_name('display_type'); ?>">
                        <option value="list" <?php echo esc_attr($instance['display_type'] == 'list' ? 'selected' : ''); ?>><?php esc_html_e('List Style',
                                'ovic-addon-toolkit') ?></option>
                        <option value="dropdown" <?php echo esc_attr($instance['display_type'] == 'dropdown' ? 'selected' : ''); ?>><?php esc_html_e('Dropdown Style',
                                'ovic-addon-toolkit') ?></option>
                        <option value="box" <?php echo esc_attr($instance['display_type'] == 'box' ? 'selected' : ''); ?> <?php echo esc_attr(in_array($attribute_list, $match) ? '' : 'disabled') ?> ><?php esc_html_e('Box Style',
                                'ovic-addon-toolkit') ?></option>
                        <option value="inline" <?php echo esc_attr($instance['display_type'] == 'inline' ? 'selected' : ''); ?>><?php esc_html_e('Inline Style',
                                'ovic-addon-toolkit') ?></option>
                    </select>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id('query_type'); ?>"><?php esc_html_e('Query type',
                            'ovic-addon-toolkit'); ?></label>
                    <select class="widefat"
                            id="<?php echo $this->get_field_id('query_type'); ?>"
                            name="<?php echo $this->get_field_name('query_type'); ?>">
                        <option value="and" <?php echo esc_attr($instance['query_type'] == 'and' ? 'selected' : ''); ?>>
                            <?php esc_html_e('AND', 'ovic-addon-toolkit') ?>
                        </option>
                        <option value="or" <?php echo esc_attr($instance['query_type'] == 'or' ? 'selected' : '');; ?>>
                            <?php esc_html_e('OR', 'ovic-addon-toolkit') ?>
                        </option>
                    </select>
                </p>
                <p class="attr_size" style="<?php echo esc_attr($hide); ?>">
                    <label for="<?php echo $this->get_field_id('width'); ?>"><?php esc_html_e('Width',
                            'ovic-addon-toolkit'); ?></label>
                    <input type="number" class="widefat"
                           id="<?php echo $this->get_field_id('width'); ?>"
                           name="<?php echo $this->get_field_name('width'); ?>"
                           value="<?php echo $instance['width']; ?>"/>
                </p>
                <p class="attr_size" style="<?php echo esc_attr($hide); ?>">
                    <label for="<?php echo $this->get_field_id('height'); ?>"><?php esc_html_e('Height',
                            'ovic-addon-toolkit'); ?></label>
                    <input type="number" class="widefat"
                           id="<?php echo $this->get_field_id('height'); ?>"
                           name="<?php echo $this->get_field_name('height'); ?>"
                           value="<?php echo $instance['height']; ?>"/>
                </p>
            </div>
            <script type="text/javascript">
                jQuery('.ovic_layered_attribute').on('change', function () {
                    var attribute    = jQuery(this),
                        match        = ['color', 'button', 'image'],
                        type         = attribute.find(':selected').data('type'),
                        container    = attribute.closest('.ovic_layered_container'),
                        display_type = container.find('.ovic_layered_display_type');

                    if (jQuery.inArray(type, match) !== -1) {
                        display_type.find('option[value="box"]').removeAttr('disabled').trigger('change');
                        if (display_type.val() === 'box') {
                            container.find('.attr_size').css('display', 'block');
                        } else {
                            container.find('.attr_size').css('display', 'none');
                        }
                    } else {
                        container.find('.attr_size').css('display', 'none');
                        display_type.val('list').trigger('change').find('option[value="box"]').attr('disabled', 'disabled');
                    }
                });
                jQuery('.ovic_layered_display_type').on('change', function () {
                    var match     = ['color', 'button', 'image'],
                        container = jQuery(this).closest('.ovic_layered_container'),
                        type      = container.find('.ovic_layered_attribute').val();

                    if (jQuery(this).val() === 'box' && jQuery.inArray(type, match) !== -1) {
                        container.find('.attr_size').css('display', 'block');
                    } else {
                        container.find('.attr_size').css('display', 'none');
                    }
                });
            </script>
            <?php
        }

        /**
         * Init settings after post types are registered.
         */
        public function init_settings()
        {
            $attribute_array      = array();
            $std_attribute        = '';
            $attribute_taxonomies = wc_get_attribute_taxonomies();

            if (!empty($attribute_taxonomies)) {
                foreach ($attribute_taxonomies as $tax) {
                    if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) {
                        $attribute_array[$tax->attribute_name] = $tax->attribute_name;
                    }
                }
                $std_attribute = current($attribute_array);
            }

            $this->settings = array(
                'title'        => array(
                    'type'  => 'text',
                    'std'   => esc_html__('Filter by', 'ovic-addon-toolkit'),
                    'label' => esc_html__('Title', 'ovic-addon-toolkit'),
                ),
                'attribute'    => array(
                    'type'    => 'select',
                    'std'     => $std_attribute,
                    'label'   => esc_html__('Attribute', 'ovic-addon-toolkit'),
                    'options' => $attribute_array,
                ),
                'display_type' => array(
                    'type'    => 'select',
                    'std'     => 'list',
                    'label'   => esc_html__('Display type', 'ovic-addon-toolkit'),
                    'options' => array(
                        'list'     => esc_html__('List', 'ovic-addon-toolkit'),
                        'dropdown' => esc_html__('Dropdown', 'ovic-addon-toolkit'),
                        'box'      => esc_html__('Box Style', 'ovic-addon-toolkit'),
                        'inline'   => esc_html__('Inline Style', 'ovic-addon-toolkit'),
                    ),
                ),
                'query_type'   => array(
                    'type'    => 'select',
                    'std'     => 'and',
                    'label'   => esc_html__('Query type', 'ovic-addon-toolkit'),
                    'options' => array(
                        'and' => esc_html__('AND', 'ovic-addon-toolkit'),
                        'or'  => esc_html__('OR', 'ovic-addon-toolkit'),
                    ),
                ),
                'width'        => array(
                    'type'  => 'number',
                    'std'   => $this->width,
                    'label' => esc_html__('Width', 'ovic-addon-toolkit'),
                ),
                'height'       => array(
                    'type'  => 'number',
                    'std'   => $this->height,
                    'label' => esc_html__('Height', 'ovic-addon-toolkit'),
                ),
            );
        }

        /**
         * Output widget.
         *
         * @param  array  $args
         * @param  array  $instance
         *
         * @see WP_Widget
         *
         */
        public function widget($args, $instance)
        {
            if (!is_shop() && !is_product_taxonomy()) {
                return;
            }

            $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            $taxonomy           = isset($instance['attribute']) ? wc_attribute_taxonomy_name($instance['attribute']) : $this->settings['attribute']['std'];
            $query_type         = isset($instance['query_type']) ? $instance['query_type'] : $this->settings['query_type']['std'];
            $display_type       = isset($instance['display_type']) ? $instance['display_type'] : $this->settings['display_type']['std'];

            if (!taxonomy_exists($taxonomy)) {
                return;
            }

            $terms = get_terms($taxonomy, array('hide_empty' => '1'));

            if (0 === count($terms)) {
                return;
            }

            ob_start();

            $this->widget_start($args, $instance);

            if ('dropdown' === $display_type) {
                wp_enqueue_script('selectWoo');
                wp_enqueue_style('select2');
                $found = $this->layered_nav_dropdown($terms, $taxonomy, $query_type);
            } elseif ('box' === $display_type) {
                $found = $this->layered_nav_box($terms, $taxonomy, $query_type, $instance);
            } elseif ('inline' === $display_type) {
                $found = $this->layered_nav_inline($terms, $taxonomy, $query_type);
            } else {
                $found = $this->layered_nav_list($terms, $taxonomy, $query_type);
            }

            $this->widget_end($args);

            // Force found when option is selected - do not force found on taxonomy attributes.
            if (!is_tax() && is_array($_chosen_attributes) && array_key_exists($taxonomy, $_chosen_attributes)) {
                $found = true;
            }

            if (!$found) {
                ob_end_clean();
            } else {
                echo ob_get_clean(); // @codingStandardsIgnoreLine
            }
        }

        /**
         * Return the currently viewed taxonomy name.
         * @return string
         */
        protected function get_current_taxonomy()
        {
            return is_tax() ? get_queried_object()->taxonomy : '';
        }

        /**
         * Return the currently viewed term ID.
         * @return int
         */
        protected function get_current_term_id()
        {
            return absint(is_tax() ? get_queried_object()->term_id : 0);
        }

        /**
         * Return the currently viewed term slug.
         * @return int
         */
        protected function get_current_term_slug()
        {
            return absint(is_tax() ? get_queried_object()->slug : 0);
        }

        /**
         * Count products within certain terms, taking the main WP query into consideration.
         *
         * This query allows counts to be generated based on the viewed products, not all products.
         *
         * @param  array  $term_ids  Term IDs.
         * @param  string  $taxonomy  Taxonomy.
         * @param  string  $query_type  Query Type.
         *
         * @return array
         */
        protected function get_filtered_term_product_counts($term_ids, $taxonomy, $query_type)
        {
            return wc_get_container()->get(Filterer::class)->get_filtered_term_product_counts($term_ids, $taxonomy, $query_type);
        }

        /**
         * Return the currently viewed taxonomy name.
         * @return object
         */
        protected function get_attribute_taxonomy($attribute_name)
        {
            $transient = sprintf('ovic_get_wc_attribute_taxonomy_%s', $attribute_name);

            if ((defined('WP_DEBUG') && WP_DEBUG) || isset($_GET[$this->clear_transient])) {
                delete_transient($transient);
            }

            if (false === ($attribute_taxonomy = get_transient($transient))) {
                global $wpdb;

                $attribute_name     = str_replace('pa_', '', wc_sanitize_taxonomy_name($attribute_name));
                $attribute_taxonomy = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."woocommerce_attribute_taxonomies WHERE attribute_name='{$attribute_name}'");
                set_transient($transient, $attribute_taxonomy);
            }

            return apply_filters('ovic_get_wc_attribute_taxonomy', $attribute_taxonomy, $attribute_name);
        }

        /**
         * Show dropdown layered nav.
         *
         * @param  array  $terms  Terms.
         * @param  string  $taxonomy  Taxonomy.
         * @param  string  $query_type  Query Type.
         *
         * @return bool Will nav display?
         */
        protected function layered_nav_dropdown($terms, $taxonomy, $query_type)
        {
            global $wp;
            $found = false;

            if ($taxonomy !== $this->get_current_taxonomy()) {
                $term_counts          = $this->get_filtered_term_product_counts(wp_list_pluck($terms, 'term_id'), $taxonomy, $query_type);
                $_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
                $taxonomy_filter_name = wc_attribute_taxonomy_slug($taxonomy);
                $taxonomy_label       = wc_attribute_label($taxonomy);

                /* translators: %s: taxonomy name */
                $any_label      = apply_filters('woocommerce_layered_nav_any_label', sprintf(__('Any %s', 'woocommerce'), $taxonomy_label), $taxonomy_label, $taxonomy);
                $multiple       = 'or' === $query_type;
                $current_values = isset($_chosen_attributes[$taxonomy]['terms']) ? $_chosen_attributes[$taxonomy]['terms'] : array();

                if ('' === get_option('permalink_structure')) {
                    $form_action = remove_query_arg(array(
                        'page',
                        'paged'
                    ), add_query_arg($wp->query_string, '', home_url($wp->request)));
                } else {
                    $form_action = preg_replace('%\/page/[0-9]+%', '', home_url(user_trailingslashit($wp->request)));
                }

                echo '<form method="get" action="'.esc_url($form_action).'" class="woocommerce-widget-layered-nav-dropdown">';
                echo '<select class="woocommerce-widget-layered-nav-dropdown dropdown_layered_nav_'.esc_attr($taxonomy_filter_name).'"'.($multiple ? 'multiple="multiple"' : '').'>';
                echo '<option value="">'.esc_html($any_label).'</option>';

                foreach ($terms as $term) {

                    // If on a term page, skip that term in widget list.
                    if ($term->term_id === $this->get_current_term_id()) {
                        continue;
                    }

                    // Get count based on current view.
                    $option_is_set = in_array($term->slug, $current_values, true);
                    $count         = isset($term_counts[$term->term_id]) ? $term_counts[$term->term_id] : 0;

                    // Only show options with count > 0.
                    if (0 < $count) {
                        $found = true;
                    } elseif (0 === $count && !$option_is_set) {
                        continue;
                    }

                    echo '<option value="'.esc_attr(urldecode($term->slug)).'" '.selected($option_is_set, true, false).'>'.esc_html($term->name).'</option>';
                }

                echo '</select>';

                if ($multiple) {
                    echo '<button class="woocommerce-widget-layered-nav-dropdown__submit" type="submit" value="'.esc_attr__('Apply', 'woocommerce').'">'.esc_html__('Apply', 'woocommerce').'</button>';
                }

                if ('or' === $query_type) {
                    echo '<input type="hidden" name="query_type_'.esc_attr($taxonomy_filter_name).'" value="or" />';
                }

                echo '<input type="hidden" name="filter_'.esc_attr($taxonomy_filter_name).'" value="'.esc_attr(implode(',', $current_values)).'" />';
                echo wc_query_string_form_fields(null, array(
                    'filter_'.$taxonomy_filter_name,
                    'query_type_'.$taxonomy_filter_name
                ), '', true); // @codingStandardsIgnoreLine
                echo '</form>';

                wc_enqueue_js(
                    "
				// Update value on change.
				jQuery( '.dropdown_layered_nav_".esc_js($taxonomy_filter_name)."' ).on( 'change', function() {
					var slug = jQuery( this ).val();
					jQuery( ':input[name=\"filter_".esc_js($taxonomy_filter_name)."\"]' ).val( slug );

					// Submit form on change if standard dropdown.
					if ( ! jQuery( this ).attr( 'multiple' ) ) {
						jQuery( this ).closest( 'form' ).trigger( 'submit' );
					}
				});

				// Use Select2 enhancement if possible
				if ( jQuery().selectWoo ) {
					var wc_layered_nav_select = function() {
						jQuery( '.dropdown_layered_nav_".esc_js($taxonomy_filter_name)."' ).selectWoo( {
							placeholder: decodeURIComponent('".rawurlencode((string) wp_specialchars_decode($any_label))."'),
							minimumResultsForSearch: 5,
							width: '100%',
							allowClear: ".($multiple ? 'false' : 'true').",
							language: {
								noResults: function() {
									return '".esc_js(_x('No matches found', 'enhanced select', 'woocommerce'))."';
								}
							}
						} );
					};
					wc_layered_nav_select();
				}
			"
                );
            }

            return $found;
        }

        /**
         * Show list based layered nav.
         *
         * @param  array  $terms  Terms.
         * @param  string  $taxonomy  Taxonomy.
         * @param  string  $query_type  Query Type.
         *
         * @return bool   Will nav display?
         */
        protected function layered_nav_list($terms, $taxonomy, $query_type)
        {
            // List display.
            echo '<ul class="woocommerce-widget-layered-nav-list">';

            $term_counts        = $this->get_filtered_term_product_counts(wp_list_pluck($terms, 'term_id'), $taxonomy, $query_type);
            $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            $found              = false;
            $base_link          = $this->get_current_page_url();

            foreach ($terms as $term) {
                $current_values = isset($_chosen_attributes[$taxonomy]['terms']) ? $_chosen_attributes[$taxonomy]['terms'] : array();
                $option_is_set  = in_array($term->slug, $current_values, true);
                $count          = isset($term_counts[$term->term_id]) ? $term_counts[$term->term_id] : 0;

                // Skip the term for the current archive.
                if ($this->get_current_term_id() === $term->term_id) {
                    continue;
                }

                // Only show options with count > 0.
                if (0 < $count) {
                    $found = true;
                } elseif (0 === $count && !$option_is_set) {
                    continue;
                }

                $filter_name = 'filter_'.wc_attribute_taxonomy_slug($taxonomy);
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $current_filter = isset($_GET[$filter_name]) ? explode(',', wc_clean(wp_unslash($_GET[$filter_name]))) : array();
                $current_filter = array_map('sanitize_title', $current_filter);

                if (!in_array($term->slug, $current_filter, true)) {
                    $current_filter[] = $term->slug;
                }

                $link = remove_query_arg($filter_name, $base_link);

                // Add current filters to URL.
                foreach ($current_filter as $key => $value) {
                    // Exclude query arg for current term archive term.
                    if ($value === $this->get_current_term_slug()) {
                        unset($current_filter[$key]);
                    }

                    // Exclude self so filter can be unset on click.
                    if ($option_is_set && $value === $term->slug) {
                        unset($current_filter[$key]);
                    }
                }

                if (!empty($current_filter)) {
                    asort($current_filter);
                    $link = add_query_arg($filter_name, implode(',', $current_filter), $link);

                    // Add Query type Arg to URL.
                    if ('or' === $query_type && !(1 === count($current_filter) && $option_is_set)) {
                        $link = add_query_arg('query_type_'.wc_attribute_taxonomy_slug($taxonomy), 'or', $link);
                    }
                    $link = str_replace('%2C', ',', $link);
                }

                if ($count > 0 || $option_is_set) {
                    $link      = apply_filters('woocommerce_layered_nav_link', $link, $term, $taxonomy);
                    $term_html = '<a rel="nofollow" href="'.esc_url($link).'">'.esc_html($term->name).'</a>';
                } else {
                    $link      = false;
                    $term_html = '<span>'.esc_html($term->name).'</span>';
                }

                $term_html .= ' '.apply_filters('woocommerce_layered_nav_count', '<span class="count">('.absint($count).')</span>', $count, $term);

                echo '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term '.($option_is_set ? 'woocommerce-widget-layered-nav-list__item--chosen chosen' : '').'">';
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.EscapeOutput.OutputNotEscaped
                echo apply_filters('woocommerce_layered_nav_term_html', $term_html, $term, $link, $count);
                echo '</li>';
            }

            echo '</ul>';

            return $found;
        }

        /**
         * Show box based layered nav.
         *
         * @param  array  $terms  Terms.
         * @param  string  $taxonomy  Taxonomy.
         * @param  string  $query_type  Query Type.
         *
         * @return bool   Will nav display?
         */
        protected function layered_nav_box($terms, $taxonomy, $query_type, $instance)
        {
            // Box display
            $get_attribute      = $this->get_attribute_taxonomy($taxonomy);
            $term_counts        = $this->get_filtered_term_product_counts(wp_list_pluck($terms, 'term_id'), $taxonomy, $query_type);
            $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            $found              = false;
            $base_link          = $this->get_current_page_url();
            ?>
            <div class="box-group group-<?php echo esc_attr($get_attribute->attribute_type); ?>">
                <?php
                foreach ($terms as $term) {
                    $current_values = isset($_chosen_attributes[$taxonomy]['terms']) ? $_chosen_attributes[$taxonomy]['terms'] : array();
                    $option_is_set  = in_array($term->slug, $current_values);
                    $count          = isset($term_counts[$term->term_id]) ? $term_counts[$term->term_id] : 0;

                    // Skip the term for the current archive.
                    if ($this->get_current_term_id() === $term->term_id) {
                        continue;
                    }

                    // Only show options with count > 0.
                    if (0 < $count) {
                        $found = true;
                    } elseif (0 === $count && !$option_is_set) {
                        continue;
                    }

                    $filter_name = 'filter_'.wc_attribute_taxonomy_slug($taxonomy);
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    $current_filter = isset($_GET[$filter_name]) ? explode(',', wc_clean(wp_unslash($_GET[$filter_name]))) : array();
                    $current_filter = array_map('sanitize_title', $current_filter);

                    if (!in_array($term->slug, $current_filter, true)) {
                        $current_filter[] = $term->slug;
                    }

                    $link = remove_query_arg($filter_name, $base_link);

                    // Add current filters to URL.
                    foreach ($current_filter as $key => $value) {
                        // Exclude query arg for current term archive term
                        if ($value === $this->get_current_term_slug()) {
                            unset($current_filter[$key]);
                        }
                        // Exclude self so filter can be unset on click.
                        if ($option_is_set && $value === $term->slug) {
                            unset($current_filter[$key]);
                        }
                    }

                    if (!empty($current_filter)) {
                        $link = add_query_arg($filter_name, implode(',', $current_filter), $link);
                        // Add Query type Arg to URL
                        if ($query_type === 'or' && !(1 === sizeof($current_filter) && $option_is_set)) {
                            $link = add_query_arg('query_type_'.sanitize_title(str_replace('pa_', '', $taxonomy)), 'or', $link);
                        }
                    }

                    $width  = (empty($instance['height'])) ? $this->width : $instance['width'];
                    $height = (empty($instance['height'])) ? $this->height : $instance['height'];

                    $product_attribute = get_term_meta($term->term_id, 'product_attribute_'.$get_attribute->attribute_type, true);
                    $style             = "display:inline-block;width:{$width}px;height:{$height}px;";

                    if ($get_attribute->attribute_type == 'image') {
                        $imgsrc = wp_get_attachment_image_url($product_attribute, $this->image_size);
                        if ($imgsrc) {
                            $thumbnail_src = $imgsrc;
                        } else {
                            $thumbnail_src = WC()->plugin_url().'/assets/images/placeholder.png';
                        }
                        $style .= "background-image: url($thumbnail_src)";
                    } elseif ($get_attribute->attribute_type == 'color') {
                        $style .= "background-color: $product_attribute";
                    }

                    $style = apply_filters('ovic_woocommerce_layered_nav_style', $style, $instance, $get_attribute, $term);
                    $link  = apply_filters('woocommerce_layered_nav_link', $link, $term, $taxonomy);
                    ?>
                    <a class="term-color<?php if ($option_is_set): ?> selected<?php endif; ?>"
                       href="<?php echo esc_url($link); ?>"
                       style="font-size: <?php echo esc_attr($this->font_size); ?>px">
                        <i style="<?php echo esc_attr($style); ?>"></i>
                        <span class="term-name"><?php echo esc_html($term->name); ?></span>
                        <?php echo apply_filters('woocommerce_layered_nav_count', '<span class="count">('.absint($count).')</span>', $count, $term); ?>
                    </a>
                    <?php
                }
                ?>
            </div>
            <?php
            return $found;
        }

        /**
         * Show inline based layered nav.
         *
         * @param  array  $terms  Terms.
         * @param  string  $taxonomy  Taxonomy.
         * @param  string  $query_type  Query Type.
         *
         * @return bool   Will nav display?
         */
        protected function layered_nav_inline($terms, $taxonomy, $query_type)
        {
            // Inline display
            ?>
            <div class="inline-group">
                <?php
                $term_counts        = $this->get_filtered_term_product_counts(wp_list_pluck($terms, 'term_id'), $taxonomy, $query_type);
                $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
                $found              = false;
                $base_link          = $this->get_current_page_url();

                foreach ($terms as $term) {
                    $current_values = isset($_chosen_attributes[$taxonomy]['terms']) ? $_chosen_attributes[$taxonomy]['terms'] : array();
                    $option_is_set  = in_array($term->slug, $current_values);
                    $count          = isset($term_counts[$term->term_id]) ? $term_counts[$term->term_id] : 0;

                    // Skip the term for the current archive.
                    if ($this->get_current_term_id() === $term->term_id) {
                        continue;
                    }

                    // Only show options with count > 0.
                    if (0 < $count) {
                        $found = true;
                    } elseif (0 === $count && !$option_is_set) {
                        continue;
                    }

                    $filter_name = 'filter_'.wc_attribute_taxonomy_slug($taxonomy);
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    $current_filter = isset($_GET[$filter_name]) ? explode(',', wc_clean(wp_unslash($_GET[$filter_name]))) : array();
                    $current_filter = array_map('sanitize_title', $current_filter);

                    if (!in_array($term->slug, $current_filter, true)) {
                        $current_filter[] = $term->slug;
                    }

                    $link = remove_query_arg($filter_name, $base_link);

                    // Add current filters to URL.
                    foreach ($current_filter as $key => $value) {
                        // Exclude query arg for current term archive term
                        if ($value === $this->get_current_term_slug()) {
                            unset($current_filter[$key]);
                        }
                        // Exclude self so filter can be unset on click.
                        if ($option_is_set && $value === $term->slug) {
                            unset($current_filter[$key]);
                        }
                    }

                    if (!empty($current_filter)) {
                        asort($current_filter);
                        $link = add_query_arg($filter_name, implode(',', $current_filter), $link);

                        // Add Query type Arg to URL.
                        if ('or' === $query_type && !(1 === count($current_filter) && $option_is_set)) {
                            $link = add_query_arg('query_type_'.wc_attribute_taxonomy_slug($taxonomy), 'or', $link);
                        }
                        $link = str_replace('%2C', ',', $link);
                    }

                    $link = apply_filters('woocommerce_layered_nav_link', $link, $term, $taxonomy);
                    ?>
                    <a class="<?php if ($option_is_set): ?> selected <?php endif; ?>"
                       href="<?php echo esc_url($link); ?>">
                        <span class="term-name"><?php echo esc_html($term->name); ?></span>
                        <?php echo apply_filters('woocommerce_layered_nav_count', '<span class="count">('.absint($count).')</span>', $count, $term); ?>
                    </a>
                    <?php
                }
                ?>
            </div>
            <?php
            return $found;
        }
    }

    /**
     * Register Widgets.
     *
     * @since 2.3.0
     */
    add_action('widgets_init',
        function () {
            register_widget('Ovic_Attribute_Product_Widget');
        }
    );
}