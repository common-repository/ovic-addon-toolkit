<?php
/**
 * Ovic Footer Builder setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Footer_Builder
 * @since    1.0.1
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Ovic_Footer_Builder')) :
    class Ovic_Footer_Builder
    {
        public $post_type = 'ovic_footer';

        public function __construct()
        {
            add_action('init', array(&$this, 'post_type'));
            /* admin bar footer */
            add_action('admin_bar_menu', array($this, 'footer_admin_bar'), 999);
            /* enqueue */
            add_action('wp_enqueue_scripts', array($this, 'inline_css'), 999);
            /* content footer */
            add_action('ovic_footer_content', array($this, 'footer_content'));
        }

        public function get_footer_option()
        {
            $footer_option = apply_filters('ovic_override_footer_template', 'footer-01');

            if (has_filter('ovic_overide_footer_template')) {
                $footer_option = apply_filters('ovic_overide_footer_template', 'footer-01');
            }

            return $footer_option;
        }

        public function get_footer_query()
        {
            $options = $this->get_footer_option();

            if (empty($options)) {
                return array();
            }
            $args = array(
                'post_type'      => $this->post_type,
                'posts_per_page' => 1,
            );
            if (is_numeric($options)) {
                $args['p'] = $options;
            } else {
                $args['name'] = $options;
            }

            return get_posts($args);
        }

        public function footer_admin_bar()
        {
            global $wp_admin_bar;

            if (!is_super_admin() || !is_admin_bar_showing() || is_network_admin()) {
                return;
            }
            // Add Parent Menu
            $options = $this->get_footer_option();
            if ($post = get_page_by_path($options, OBJECT, $this->post_type)) {
                $post_id = $post->ID;
            } else {
                $post_id = 0;
            }
            if ($post_id > 0 && !OVIC_CORE()->is_elementor($post_id)) {
                $args = array(
                    'id'    => 'footer_option',
                    'title' => esc_html__('Edit Footer', 'ovic-addon-toolkit'),
                    'href'  => admin_url('post.php?post='.$post_id.'&action=edit'),
                );
                $wp_admin_bar->add_menu($args);
            }
        }

        public function inline_css()
        {
            $css   = '';
            $posts = $this->get_footer_query();
            if (!empty($posts)) {
                foreach ($posts as $post) {
                    if (!OVIC_CORE()->is_elementor($post->ID)) {
                        $post_custom_css[] = get_post_meta($post->ID, '_wpb_post_custom_css', true);
                        $post_custom_css[] = get_post_meta($post->ID, '_wpb_shortcodes_custom_css', true);
                        $post_custom_css[] = get_post_meta($post->ID, '_Ovic_Shortcode_custom_css', true);
                        $post_custom_css[] = get_post_meta($post->ID, '_Ovic_VC_Shortcode_Custom_Css', true);
                        if (count($post_custom_css) > 0) {
                            $css = implode(' ', $post_custom_css);
                        }
                    }
                }
            }
            wp_add_inline_style('ovic-core', preg_replace('/\s+/', ' ', $css));
        }

        public function footer_content()
        {
            $posts = $this->get_footer_query();
            if (!empty($posts)):
                foreach ($posts as $post): ?>
                    <?php
                    $options = $this->get_footer_option();
                    $class   = apply_filters('ovic_footer_main_class',
                        array('footer', $post->post_name),
                        $options
                    );
                    ?>
                    <footer class="<?php echo esc_attr(implode(' ', $class)); ?>">
                        <?php
                        if (OVIC_CORE()->is_elementor($post->ID)) {
                            $content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($post->ID);
                        } else {
                            $content = $post->post_content;
                            $content = apply_filters('the_content', $content);
                            $content = str_replace(']]>', ']]>', $content);
                        }

                        echo apply_filters('ovic_footer_main_content', $content, $post, $options);
                        ?>
                    </footer>
                <?php
                endforeach;
            endif;
        }

        public function post_type()
        {
            /* Footer */
            $args = array(
                'labels'              => array(
                    'name'          => __('Footer'),
                    'singular_name' => __('Footer'),
                    'all_items'     => __('Footer Builder'),
                ),
                'hierarchical'        => false,
                'supports'            => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'revisions',
                    'elementor',
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
                    'slug'       => 'footer',
                    'with_front' => false
                ),
            );
            register_post_type($this->post_type, $args);
        }
    }

    new Ovic_Footer_Builder();
endif;