<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
/**
 * Implement a visual editor for term descriptions.
 *
 * @since 1.0
 */
if (!class_exists('Ovic_Descriptions_Editor')) :
    class Ovic_Descriptions_Editor
    {
        /**
         * The taxonomy which should use the visual editor.
         *
         * @var string
         * @since 1.0
         */
        public $taxonomy;

        /**
         * The constructor function for the class.
         *
         * @since 1.0
         *
         */
        public function __construct()
        {
            /* Retrieve an array of registered taxonomies */
            add_action('load-edit-tags.php', array($this, 'load_taxonomy'));

            /* Only users with the "publish_posts" capability can use this feature */
            if (current_user_can('publish_posts')) {

                /* Remove the filters which disallow HTML in term descriptions */
                remove_filter('pre_term_description', 'wp_filter_kses');
                remove_filter('term_description', 'wp_kses_data');

                /* Add filters to disallow unsafe HTML tags */
                if (!current_user_can('unfiltered_html')) {
                    add_filter('pre_term_description', 'wp_kses_post');
                    add_filter('term_description', 'wp_kses_post');
                }
            }

            /* Apply `the_content` filters to term description */
            if (isset($GLOBALS['wp_embed'])) {
                add_filter('term_description', array($GLOBALS['wp_embed'], 'run_shortcode'), 8);
                add_filter('term_description', array($GLOBALS['wp_embed'], 'autoembed'), 8);
            }

            add_filter('term_description', 'wptexturize');
            add_filter('term_description', 'convert_smilies');
            add_filter('term_description', 'convert_chars');
            add_filter('term_description', 'wpautop');

            if (!is_admin()) {
                add_filter('term_description', 'shortcode_unautop');
                add_filter('term_description', 'do_shortcode', 11);
            }

            add_action('admin_head-edit-tags.php', array($this, 'fix_editor_style'));
            add_action('admin_head-edit-tags.php', array($this, 'load_wordcount_js'));
            add_action('admin_head-term.php', array($this, 'load_wordcount_js'));
        }

        public function load_taxonomy()
        {
            $screen = get_current_screen();
            /* Retrieve an array of registered taxonomies */
            $taxonomies = get_taxonomies('', 'names');
            $taxonomies = apply_filters('ovic_term_description_taxonomies', $taxonomies);

            if (!empty($screen->taxonomy)) {

                $this->taxonomy = apply_filters('ovic_term_description_taxonomy', $screen->taxonomy);

                /* Loop through the taxonomies, adding actions */
                if (!empty($this->taxonomy)) {
                    add_action($this->taxonomy.'_edit_form_fields', array($this, 'render_field_edit'), 1, 2);
                    add_action($this->taxonomy.'_add_form_fields', array($this, 'render_field_add'), 1, 1);
                }
            }
        }

        /**
         * Fix the formatting buttons on the HTML section of the visual editor from being full-width.
         *
         * @since 1.1
         */
        function fix_editor_style()
        {
            echo '<style>',
            ' .quicktags-toolbar input { width: auto; }',
            ' .column-description img { max-width: 100%; }',
            ' .term-description-wrap #post-status-info { width: auto; }',
            ' </style>';
        }

        /**
         * Load the script for the word count functionality.
         */
        function load_wordcount_js()
        {
            wp_enqueue_script(
                'ovic-description-editor-word-count',
                trailingslashit(plugin_dir_url(__FILE__)).'/wordcount.js',
                array('jquery', 'underscore', 'word-count')
            );
        }

        /**
         * Render the editor word count section.
         */
        private function editor_word_count()
        {
            ?>
            <div id="post-status-info">
                <div id="description-word-count" class="hide-if-no-js" style="padding: 5px 10px;">
                    <?php printf(
                        esc_html__('Word count: %s'),
                        '<span class="word-count">0</span>'
                    ); ?>
                </div>
            </div>
            <?php
        }

        /**
         * Add the visual editor to the edit tag screen.
         *
         * HTML should match what is used in wp-admin/edit-tag-form.php
         *
         * @param  object  $tag  The tag currently being edited.
         * @param  string  $taxonomy  The taxonomy that the tag belongs to.
         *
         * @since 1.0
         */
        public function render_field_edit($tag, $taxonomy)
        {
            $settings = array(
                'textarea_name' => 'description',
                'textarea_rows' => 10,
                'editor_class'  => 'i18n-multilingual',
            );

            ?>
            <tr class="form-field term-description-wrap">
                <th scope="row">
                    <label for="description"><?php _e('Description'); ?></label>
                </th>
                <td>
                    <?php

                    wp_editor(htmlspecialchars_decode($tag->description), 'html-tag-description', $settings);
                    $this->editor_word_count();

                    ?>
                    <p class="description"><?php esc_html_e('The description is not prominent by default; however, some themes may show it.'); ?></p>
                </td>
                <script>
                    // Remove the non-html field
                    jQuery('textarea#description').closest('.form-field').remove();
                </script>
            </tr>
            <?php
        }

        /**
         * Add the visual editor to the add new tag screen.
         *
         * HTML should match what is used in wp-admin/edit-tags.php
         *
         * @param  string  $taxonomy  The taxonomy that a new tag is being added to.
         *
         * @since 1.0
         */
        public function render_field_add($taxonomy)
        {
            $settings = array(
                'textarea_name' => 'description',
                'textarea_rows' => 7,
                'editor_class'  => 'i18n-multilingual',
            );

            ?>
            <div class="form-field term-description-wrap">
                <label for="tag-description"><?php esc_html_e('Description'); ?></label>
                <?php

                wp_editor('', 'html-tag-description', $settings);
                $this->editor_word_count();

                ?>
                <p><?php esc_html_e('The description is not prominent by default; however, some themes may show it.'); ?></p>

                <script>
                    // Remove the non-html field
                    jQuery('textarea#tag-description').closest('.form-field').remove();

                    jQuery(function () {
                        jQuery('#addtag').on('mousedown', '#submit', function () {
                            tinyMCE.triggerSave();

                            jQuery(document).bind('ajaxSuccess.vtde_add_term', function () {
                                if (tinyMCE.activeEditor) {
                                    tinyMCE.activeEditor.setContent('');
                                }
                                jQuery(document).unbind('ajaxSuccess.vtde_add_term', false);
                            });
                        });
                    });
                </script>
            </div>
            <?php
        }
    }

    new Ovic_Descriptions_Editor();
endif;
