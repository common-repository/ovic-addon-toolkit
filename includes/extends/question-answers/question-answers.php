<?php
/**
 * Ovic Question Answers
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Question_Answers
 * @since    1.0.1
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Ovic_Question_Answers')) {
    class Ovic_Question_Answers
    {
        public $post_type = 'question_answers';

        public function __construct()
        {
            add_action('init', array(&$this, 'post_type'));
            add_action('init', array(&$this, 'post_editor'));

            add_action('wp_enqueue_scripts', array($this, 'load_scripts'), 10);

            add_filter('manage_'.$this->post_type.'_posts_columns', array($this, 'register_columns'));
            add_action('manage_'.$this->post_type.'_posts_custom_column', array($this, 'display_columns'), 10, 2);

            add_shortcode('ovic_question', array($this, 'content'));

            add_action('wp_ajax_ovic_add_question', array($this, 'add_question'));
            add_action('wp_ajax_nopriv_ovic_add_question', array($this, 'add_question'));
            add_action('wp_ajax_ovic_get_question', array($this, 'get_question'));
            add_action('wp_ajax_nopriv_ovic_get_question', array($this, 'get_question'));
        }

        public function load_scripts()
        {
            wp_register_style('question-answers',
                trailingslashit(plugin_dir_url(__FILE__)).'question-answers.css'
            );
            wp_register_script('question-answers',
                trailingslashit(plugin_dir_url(__FILE__)).'question-answers.js'
            );

            wp_localize_script('question-answers', 'question_params', array(
                'ajax_url' => admin_url('admin-ajax.php', 'relative'),
                'security' => wp_create_nonce('ovic_question_answers'),
            ));
        }

        public function content($atts, $content = null)
        {
            // Extract shortcode parameters.
            $atts = shortcode_atts(array(
                'title'    => esc_html__('Questions and answers of the customers', 'ovic-addon-toolkit'),
                'text_btn' => esc_html__('Question & Answers', 'ovic-addon-toolkit'),
                'text_ask' => esc_html__('Ask', 'ovic-addon-toolkit'),
                'popup'    => true, // true / false
                'ajax'     => true, // true / false
                'lock'     => false, // true / false
            ), $atts, 'ovic_question');

            global $post;

            $atts['popup'] = filter_var($atts['popup'], FILTER_VALIDATE_BOOLEAN);
            $atts['lock']  = filter_var($atts['lock'], FILTER_VALIDATE_BOOLEAN);
            $atts['ajax']  = filter_var($atts['ajax'], FILTER_VALIDATE_BOOLEAN);

            $uniqueID = uniqid('question-input-');
            $classes  = array('ovic-question-answers');
            $post_id  = !empty($post->ID) ? $post->ID : 0;

            if ($atts['popup'] == true) {
                $classes[] = 'popup-on';
            } else {
                $classes[] = 'popup-off';
            }
            if ($atts['ajax'] == true) {
                $classes[] = 'load-ajax';
            }

            $classes = implode(' ', $classes);

            wp_enqueue_style('question-answers');
            wp_enqueue_script('question-answers');

            ob_start();
            ?>
            <div class="<?php echo esc_attr($classes) ?>" data-post_id="<?php echo esc_attr($post_id) ?>">
                <?php if ($atts['popup'] == true): ?>
                    <a href="#" class="load-question" rel="nofollow">
                        <?php echo esc_html($atts['text_btn']); ?>
                    </a>
                <?php endif; ?>
                <span class="overlay-question" <?php if ($atts['popup'] == true): ?>style="opacity: 0"<?php endif; ?>></span>
                <div class="content-question" <?php if ($atts['popup'] == true): ?>style="opacity: 0"<?php endif; ?>>
                    <div class="notice"></div>
                    <?php if ($atts['popup'] == true): ?>
                        <a href="#" class="close-question" rel="nofollow"></a>
                    <?php endif; ?>
                    <?php if (!empty($atts['title'])): ?>
                        <h3 class="title-question">
                            <?php echo esc_html($atts['title']); ?>
                        </h3>
                    <?php endif; ?>
                    <?php if ($atts['lock'] == false): ?>
                        <div class="ovic-ask-question">
                            <label for="<?php echo esc_attr($uniqueID) ?>">
                                <input id="<?php echo esc_attr($uniqueID) ?>" type="text" class="ask-question"
                                       placeholder="<?php echo esc_html__('Ask a question', 'ovic-addon-toolkit'); ?>">
                            </label>
                            <a href="#" class="add-question button alt" rel="nofollow">
                                <?php echo esc_html($atts['text_ask']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="list-question">
                        <?php if ($atts['ajax'] == false): ?>
                            <?php
                            $question = $this->get_query($post_id);

                            if ($question->have_posts()) {
                                while ($question->have_posts()) {
                                    $question->the_post();
                                    ?>
                                    <div class="item-question">
                                        <div class="question">
                                            <span class="icon"></span>
                                            <div class="text paragraph"><?php echo get_the_title() ?></div>
                                        </div>
                                        <div class="answers">
                                            <span class="icon"></span>
                                            <div class="text paragraph"><?php echo $this->get_answers(get_the_ID()) ?></div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                wp_reset_postdata();
                            }
                            ?>
                        <?php elseif ($atts['popup'] == false): ?>
                            <a href="#" class="load-question" rel="nofollow">
                                <?php echo esc_html($atts['text_btn']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php

            return ob_get_clean();
        }

        public function get_query($post_id)
        {
            $args = array(
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'post_type'      => $this->post_type,
                'meta_query'     => array(
                    array(
                        'key'     => 'question_from',
                        'compare' => 'IN',
                        'value'   => $post_id,
                    )
                ),
                'orderby'        => 'meta_key',
                'order'          => 'DESC',
            );

            return new WP_Query($args);
        }

        /**
         * Register columns for in stock alert subscription screen.
         *
         * @param array $columns Current columns.
         *
         * @return  array
         */
        public function register_columns($columns)
        {
            $columns = array(
                'cb'            => '<input type="checkbox" />',
                'title'         => esc_html__('Question', 'ovic-addon-toolkit'),
                'answers'       => esc_html__('Answers', 'ovic-addon-toolkit'),
                'question_from' => esc_html__('Question from', 'ovic-addon-toolkit'),
                'date'          => esc_html__('Time', 'ovic-addon-toolkit'),
            );

            return $columns;
        }

        public function get_answers($post_id)
        {
            $meta_question = get_post_meta($post_id, 'question_answers', true);
            if (!empty($meta_question['answers'])) {
                return wpautop($meta_question['answers']);
            } else {
                return esc_html__('There are no answers for this question yet.', 'ovic-addon-toolkit');
            }
        }

        /**
         * Display columns for in stock alert subscription screen.
         *
         * @param array $column Column to display content for.
         * @param int $post_id Post ID to display content for.
         */
        public function display_columns($column, $post_id)
        {
            switch ($column) {
                case 'answers' :
                    echo $this->get_answers($post_id);
                    break;
                case 'question_from' :
                    $meta_id = get_post_meta($post_id, 'question_from', true);
                    echo sprintf('<a href="%s" target="_blank"><strong>%s</strong></a>',
                        get_permalink($meta_id),
                        get_the_title($meta_id)
                    );
                    break;
            }
        }

        public function get_question()
        {
            check_ajax_referer('ovic_question_answers', 'security');

            $post_id = !empty($_POST['post_id']) ? $_POST['post_id'] : 0;

            if (empty($post_id)) {
                return array(
                    'status' => false,
                    'data'   => esc_html__('ID post do not exists.', 'ovic-addon-toolkit'),
                );
            }

            $data     = array();
            $question = $this->get_query($post_id);

            if ($question->have_posts()) {
                while ($question->have_posts()) {
                    $question->the_post();
                    $data[] = array(
                        'question' => get_the_title(),
                        'answers'  => $this->get_answers(get_the_ID()),
                    );
                }
                wp_reset_postdata();
            }

            wp_send_json(array(
                'status' => true,
                'data'   => $data,
            ));

            wp_die();
        }

        public function add_question()
        {
            check_ajax_referer('ovic_question_answers', 'security');

            $question = !empty($_POST['question']) ? $_POST['question'] : '';
            $post_id  = !empty($_POST['post_id']) ? $_POST['post_id'] : 0;

            if (empty($question)) {
                wp_send_json(array(
                    'status'  => false,
                    'message' => esc_html__('Question is empty.', 'ovic-addon-toolkit'),
                ));
            } elseif (empty($post_id)) {
                wp_send_json(array(
                    'status'  => false,
                    'message' => esc_html__('ID post do not exists.', 'ovic-addon-toolkit'),
                ));
            }

            $question_post = array(
                'post_title'  => sanitize_text_field($question),
                'post_status' => 'publish',
                'post_type'   => $this->post_type,
            );
            // Insert the post into the database
            $question_id = wp_insert_post($question_post);

            update_post_meta($question_id,
                'question_from', absint($post_id)
            );


            wp_send_json(array(
                'status'  => true,
                'message' => esc_html__('Question has been sent successfully.', 'ovic-addon-toolkit'),
            ));

            wp_die();
        }

        public function post_editor()
        {
            global $pagenow;

            if (!in_array($pagenow, array('post.php', 'post-new.php'))) {
                return;
            }

            $meta_id       = !empty($_REQUEST['post']) ? get_post_meta($_REQUEST['post'], 'question_from', true) : 0;
            $question_from = sprintf('%s: <a href="%s" target="_blank"><strong>%s</strong></a>',
                esc_html__('Question from', 'ovic-addon-toolkit'),
                get_permalink($meta_id),
                get_the_title($meta_id)
            );
            OVIC_Metabox::instance(array(
                array(
                    'id'        => 'question_answers',
                    'title'     => 'Answers',
                    'post_type' => $this->post_type,
                    'context'   => 'normal',
                    'priority'  => 'high',
                    'sections'  => array(
                        array(
                            'name'   => 'section_answers',
                            'fields' => array(
                                array(
                                    'type'    => 'notice',
                                    'style'   => 'warning',
                                    'content' => $question_from,
                                ),
                                array(
                                    'id'            => 'answers',
                                    'type'          => 'wp_editor',
                                    'media_buttons' => false,
                                ),
                            ),
                        ),
                    ),
                )
            ));
        }

        public function post_type()
        {
            /* Footer */
            $args = array(
                'labels'              => array(
                    'name'          => __('Question & Answers'),
                    'singular_name' => __('Question & Answers'),
                    'all_items'     => __('Question & Answers'),
                ),
                'hierarchical'        => false,
                'supports'            => array(
                    'title',
                ),
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => 'ovic_addon-dashboard',
                'menu_position'       => 5,
                'show_in_nav_menus'   => true,
                'publicly_queryable'  => true,
                'exclude_from_search' => true,
                'has_archive'         => false,
                'query_var'           => true,
                'can_export'          => true,
                'show_in_rest'        => true,
                'capability_type'     => 'page',
                'rewrite'             => array(
                    'slug'       => 'question-answers',
                    'with_front' => false
                ),
            );
            register_post_type($this->post_type, $args);
        }
    }

    new Ovic_Question_Answers();
}