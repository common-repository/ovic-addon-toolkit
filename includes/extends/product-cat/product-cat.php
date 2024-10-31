<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Ovic_Category_Taxonomies')) {
    class Ovic_Category_Taxonomies
    {
        public function __construct()
        {
            // Add form
            add_action('product_cat_add_form_fields', array($this, 'add_category_fields'), 20);
            add_action('product_cat_edit_form_fields', array($this, 'edit_category_fields'), 20);
            add_action('created_term', array($this, 'save_category_fields'), 20, 3);
            add_action('edit_term', array($this, 'save_category_fields'), 20, 3);

            // Add columns
            add_filter('manage_edit-product_cat_columns', array($this, 'product_cat_columns'), 20);
            add_filter('manage_product_cat_custom_column', array($this, 'product_cat_column'), 20, 3);
        }

        /**
         * Category thumbnail fields.
         */
        public function add_category_fields()
        {
            ?>
            <div class="form-field term-banner-wrap">
                <label><?php esc_html_e('Banner', 'ovic-addon-toolkit'); ?></label>
                <div id="product_cat_banner" style="float: left; margin-right: 10px;">
                    <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" width="60px" height="60px" alt=""/>
                </div>
                <div style="line-height: 60px;">
                    <input type="hidden" id="product_cat_banner_id" name="product_cat_banner_id"/>
                    <button type="button" class="upload_image button">
                        <?php esc_html_e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                    </button>
                    <button type="button" class="remove_image button">
                        <?php esc_html_e('Remove image', 'ovic-addon-toolkit'); ?>
                    </button>
                </div>
                <style type="text/css">
                    table.wp-list-table .column-banner {
                        width: 52px;
                        text-align: center;
                        white-space: nowrap;
                    }

                    table.wp-list-table td.column-banner img {
                        margin: 0;
                        width: auto;
                        height: auto;
                        max-width: 40px;
                        max-height: 40px;
                        vertical-align: middle;
                    }
                </style>
                <script type="text/javascript">

                    // Only show the "remove image" button when needed
                    if (!jQuery('.term-banner-wrap #product_cat_banner_id').val()) {
                        jQuery('.term-banner-wrap .remove_image').hide();
                    }

                    // Uploading files
                    var file_frame;

                    jQuery(document).on('click', '.term-banner-wrap .upload_image', function (event) {

                        event.preventDefault();

                        // If the media frame already exists, reopen it.
                        if (file_frame) {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.downloadable_file = wp.media({
                            title   : '<?php esc_html_e('Choose an image', 'ovic-addon-toolkit'); ?>',
                            button  : {
                                text: '<?php esc_html_e('Use image', 'ovic-addon-toolkit'); ?>'
                            },
                            multiple: false
                        });

                        // When an image is selected, run a callback.
                        file_frame.on('select', function () {
                            var attachment           = file_frame.state().get('selection').first().toJSON();
                            var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                            jQuery('.term-banner-wrap #product_cat_banner_id').val(attachment.id);
                            jQuery('.term-banner-wrap #product_cat_banner').find('img').attr('src', attachment_thumbnail.url);
                            jQuery('.term-banner-wrap .remove_image').show();
                        });

                        // Finally, open the modal.
                        file_frame.open();
                    });

                    jQuery(document).on('click', '.term-banner-wrap .remove_image', function () {
                        jQuery('.term-banner-wrap #product_cat_banner').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
                        jQuery('.term-banner-wrap #product_cat_banner_id').val('');
                        jQuery('.term-banner-wrap .remove_image').hide();
                        return false;
                    });

                    jQuery(document).ajaxComplete(function (event, request, options) {
                        if (request && 4 === request.readyState && 200 === request.status
                            && options.data && 0 <= options.data.indexOf('action=add-tag')) {

                            var res = wpAjax.parseAjaxResponse(request.responseXML, 'ajax-response');
                            if (!res || res.errors) {
                                return;
                            }
                            // Clear Thumbnail fields on submit
                            jQuery('.term-banner-wrap #product_cat_banner').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
                            jQuery('.term-banner-wrap #product_cat_banner_id').val('');
                            jQuery('.term-banner-wrap .remove_image').hide();
                            // Clear Display type field on submit
                            jQuery('#display_type').val('');
                            return;
                        }
                    });

                </script>
                <div class="clear"></div>
            </div>
            <?php
        }

        /**
         * Edit category thumbnail field.
         *
         * @param  mixed  $term  Term (category) being edited
         */
        public function edit_category_fields($term)
        {
            $thumbnail_id = absint(get_term_meta($term->term_id, 'banner_id', true));
            if ($thumbnail_id) {
                $image = wp_get_attachment_thumb_url($thumbnail_id);
            } else {
                $image = wc_placeholder_img_src();
            }
            ?>
            <tr class="form-field term-banner-wrap">
                <th scope="row" valign="top"><label><?php esc_html_e('Banner', 'ovic-addon-toolkit'); ?></label></th>
                <td>
                    <div id="product_cat_banner" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url($image); ?>" width="60px" height="60px" alt=""/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" id="product_cat_banner_id" name="product_cat_banner_id"
                               value="<?php echo esc_attr($thumbnail_id); ?>"/>
                        <button type="button" class="upload_image button">
                            <?php esc_html_e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                        </button>
                        <button type="button" class="remove_image button">
                            <?php esc_html_e('Remove image', 'ovic-addon-toolkit'); ?>
                        </button>
                    </div>
                    <script type="text/javascript">

                        // Only show the "remove image" button when needed
                        if ('0' === jQuery('.term-banner-wrap #product_cat_banner_id').val()) {
                            jQuery('.term-banner-wrap .remove_image').hide();
                        }

                        // Uploading files
                        var file_frame;

                        jQuery(document).on('click', '.term-banner-wrap .upload_image', function (event) {

                            event.preventDefault();

                            // If the media frame already exists, reopen it.
                            if (file_frame) {
                                file_frame.open();
                                return;
                            }

                            // Create the media frame.
                            file_frame = wp.media.frames.downloadable_file = wp.media({
                                title   : '<?php esc_html_e('Choose an image', 'ovic-addon-toolkit'); ?>',
                                button  : {
                                    text: '<?php esc_html_e('Use image', 'ovic-addon-toolkit'); ?>'
                                },
                                multiple: false
                            });

                            // When an image is selected, run a callback.
                            file_frame.on('select', function () {
                                var attachment           = file_frame.state().get('selection').first().toJSON();
                                var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                                jQuery('.term-banner-wrap #product_cat_banner_id').val(attachment.id);
                                jQuery('.term-banner-wrap #product_cat_banner').find('img').attr('src', attachment_thumbnail.url);
                                jQuery('.term-banner-wrap .remove_image').show();
                            });

                            // Finally, open the modal.
                            file_frame.open();
                        });

                        jQuery(document).on('click', '.term-banner-wrap .remove_image', function () {
                            jQuery('.term-banner-wrap #product_cat_banner').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
                            jQuery('.term-banner-wrap #product_cat_banner_id').val('');
                            jQuery('.term-banner-wrap .remove_image').hide();
                            return false;
                        });

                    </script>
                    <div class="clear"></div>
                </td>
            </tr>
            <?php
        }

        /**
         * save_category_fields function.
         *
         * @param  mixed  $term_id  Term ID being saved
         * @param  mixed  $tt_id
         * @param  string  $taxonomy
         */
        public function save_category_fields($term_id, $tt_id = '', $taxonomy = '')
        {
            if (isset($_POST['product_cat_banner_id']) && 'product_cat' === $taxonomy) {
                update_term_meta($term_id, 'banner_id', absint($_POST['product_cat_banner_id']));
            }
        }

        /**
         * Thumbnail column added to category admin.
         *
         * @param  mixed  $columns
         *
         * @return array
         */
        public function product_cat_columns($columns)
        {
            $new_columns = array();

            if (isset($columns['cb'])) {
                $new_columns['cb'] = $columns['cb'];
                unset($columns['cb']);
            }

            $new_columns['banner'] = esc_html__('Banner', 'ovic-addon-toolkit');

            $columns           = array_merge($new_columns, $columns);
            $columns['handle'] = '';

            return $columns;
        }

        /**
         * Thumbnail column value added to category admin.
         *
         * @param  string  $columns  Column HTML output.
         * @param  string  $column  Column name.
         * @param  int  $id  Product ID.
         *
         * @return string
         */
        public function product_cat_column($columns, $column, $id)
        {
            if ('banner' === $column) {
                $banner_id = get_term_meta($id, 'banner_id', true);

                if ($banner_id) {
                    $image = wp_get_attachment_thumb_url($banner_id);
                } else {
                    $image = wc_placeholder_img_src();
                }

                // Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605 .
                $image   = str_replace(' ', '%20', $image);
                $columns .= '<img src="'.esc_url($image).'" alt="'.esc_attr__('Banner', 'ovic-addon-toolkit').'" class="wp-post-image" height="48" width="48" />';
            }
            if ('handle' === $column) {
                $columns .= '<input type="hidden" name="term_id" value="'.esc_attr($id).'" />';
            }

            return $columns;
        }
    }

    new Ovic_Category_Taxonomies();
}