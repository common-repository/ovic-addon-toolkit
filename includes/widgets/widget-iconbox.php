<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Ovic iconbox
 *
 * Displays iconbox widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if (!class_exists('Ovic_Iconbox_Widget')) {
    class Ovic_Iconbox_Widget extends OVIC_Widget
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $array_settings           = apply_filters('ovic_filter_settings_widget_iconbox',
                array(
                    'title'        => array(
                        'type'  => 'text',
                        'title' => esc_html__('Title', 'ovic-addon-toolkit'),
                    ),
                    'ovic_icon'    => array(
                        'type'  => 'icon',
                        'title' => esc_html__('Icon', 'ovic-addon-toolkit'),
                    ),
                    'ovic_title'   => array(
                        'type'  => 'text',
                        'title' => esc_html__('Title Icon', 'ovic-addon-toolkit'),
                    ),
                    'ovic_content' => array(
                        'type'     => 'textarea',
                        'sanitize' => 'disabled',
                        'title'    => esc_html__('Descriptions', 'ovic-addon-toolkit'),
                    ),
                )
            );
            $this->widget_cssclass    = 'widget-ovic-iconbox';
            $this->widget_description = esc_html__('Display the customer Iconbox.', 'ovic-addon-toolkit');
            $this->widget_id          = 'widget_ovic_iconbox';
            $this->widget_name        = esc_html__('Ovic: Iconbox', 'ovic-addon-toolkit');
            $this->settings           = $array_settings;

            parent::__construct();
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
            $this->widget_start($args, $instance);

            ob_start();
            ?>
            <div class="iconbox-inner">
                <?php if ($instance['ovic_icon']): ?>
                    <div class="icon"><span class="<?php echo esc_attr($instance['ovic_icon']) ?>"></span></div>
                <?php endif; ?>
                <div class="content">
                    <?php if ($instance['ovic_title']): ?>
                        <h4 class="title"><?php echo esc_html($instance['ovic_title']); ?></h4>
                    <?php endif;
                    if ($instance['ovic_content']): ?>
                        <p class="text"><?php echo wp_specialchars_decode($instance['ovic_content']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            echo apply_filters('ovic_filter_widget_iconbox', ob_get_clean(), $instance);

            $this->widget_end($args);
        }
    }

    /**
     * Register Widgets.
     *
     * @since 2.3.0
     */
    add_action('widgets_init',
        function () {
            register_widget('Ovic_Iconbox_Widget');
        }
    );
}