<?php
/**
 * Theme verify admin page and functions.
 *
 * @package Ovic Verify Envato
 *
 * Disable: add_filter('ovic_disable_envato_license', '__return_true');
 * License: 58051219-96de-4a10-a367-4722ba4b4364
 */
if (!class_exists('Ovic_Verify_Envato')) {
    class Ovic_Verify_Envato
    {
        public $key        = '';
        public $item_slug  = '';
        public $item_name  = '';
        public $verify     = array();
        public $theme      = array();
        public $stylesheet = array();
        public $affiliates = '';
        public $link       = 'https://themeforest.net/user/kutethemes/portfolio/';

        public function __construct()
        {
            if (!function_exists('ovic_link_affiliates')) {
                /**
                 * affiliates
                 * CDN: https://cdn.staticaly.com/wp/p/:plugin_name/:version/:file
                 */
                include OVIC_PLUGIN_DIR.'affiliates.php';
            }
            $this->affiliates = ovic_link_affiliates();
            $this->theme      = wp_get_theme(get_template());
            $this->key        = OVIC_CORE()->get_key('envato');
            $this->verify     = OVIC_CORE()->verify_envato();
            $this->stylesheet = OVIC_CORE()->get_stylesheet();
            $this->item_slug  = get_template();

            if (!empty($this->affiliates[$this->stylesheet['envato']])) {
                $this->link = $this->affiliates[$this->stylesheet['envato']];
            }

            add_action('admin_init', array($this, 'page_init'));
            add_action('admin_notices', array($this, 'display_message'));
            add_action('ovic_envato_license', array($this, 'settings'));

            // Disable options
            if ($this->verify['active'] == false) {
                add_filter('ovic_import_config', '__return_empty_array');
                add_filter('ovic_options_metabox', '__return_empty_array');
                add_action('ovic_html_options_before', array($this, 'remove_options'));
            }
        }

        public function page_init()
        {
            $config = array(
                array(
                    'id'    => 'purchased_code',
                    'type'  => 'text',
                    'title' => esc_html__('Purchased code', 'ovic-addon-toolkit'),
                    'desc'  => esc_html__('Purchased code license item.', 'ovic-addon-toolkit'),
                ),
                array(
                    'id'      => 'time',
                    'type'    => 'text',
                    'default' => time(),
                    'class'   => 'ovic-hidden',
                ),
            );
            $fields = array(
                'title'  => '',
                'id'     => $this->key,
                'type'   => 'fieldset',
                'fields' => $config,
            );

            register_setting(
                'ovic_addon_envato',
                $this->key
            );
            add_settings_section(
                'ovic_envato_license_id',
                null,
                null,
                'ovic-envato-license'
            );
            add_settings_field(
                $this->key,
                '',
                function () use ($fields) {
                    $settings = $this->verify['settings'];
                    if (!empty($settings['time'])) {
                        unset($settings['time']);
                    }
                    echo '<div class="ovic-onload">';
                    echo OVIC::field($fields, $settings);
                    echo '</div>';
                },
                'ovic-envato-license',
                'ovic_envato_license_id'
            );
        }

        public function license_info()
        {
            $time = '00/00/0000';
            $last = '00/00/0000';
            $name = $this->theme->get('Name');
            $link = $this->link;
            $txt  = esc_html__('Not activated', 'ovic-addon-toolkit');
            if ($this->verify['active'] == true) {
                $txt  = esc_html__('Activated', 'ovic-addon-toolkit');
                $time = new DateTime($this->verify['product']['supported_until']);
                $time = $time->format('d/m/Y h:i:s');
                $last = new DateTime($this->verify['product']['item']['updated_at']);
                $last = $last->format('d/m/Y h:i:s');
                $name = $this->verify['product']['item']['name'];
                $id   = $this->verify['product']['item']['id'];
                $url  = $this->verify['product']['item']['url'];
                $link = !empty($this->affiliates[$id]) ? $this->affiliates[$id] : $url;
            }
            $status  = sprintf('<b>%s </b><span>%s</span>',
                esc_html__('Status:', 'ovic-addon-toolkit'),
                $txt
            );
            $support = sprintf('<b>%s </b><span>%s</span>',
                esc_html__('Support until:', 'ovic-addon-toolkit'),
                $time
            );
            $update  = sprintf('<b>%s </b><span>%s</span>',
                esc_html__('Last update:', 'ovic-addon-toolkit'),
                $last
            );
            $product = sprintf('<b>%s </b><a href="%s">%s</a>',
                esc_html__('Product:', 'ovic-addon-toolkit'),
                $link,
                $name
            );
            ?>
            <div class="license-info">
                <p class="product">
                    <?php echo wp_kses_post($product); ?>
                </p>
                <p class="status">
                    <?php echo wp_kses_post($status); ?>
                </p>
                <p class="support">
                    <?php echo wp_kses_post($support); ?>
                </p>
                <p class="update">
                    <?php echo wp_kses_post($update); ?>
                </p>
            </div>
            <?php
        }

        public function settings()
        {
            $class = '';
            if ($this->verify['active'] == true) {
                $class = ' activated';
            }
            ?>
            <div class="wrap ovic-license-settings ovic-addon-settings<?php echo esc_attr($class); ?>">
                <h3><?php echo esc_html__('Active Theme License', 'ovic-addon-toolkit'); ?></h3>
                <?php $this->license_info(); ?>
                <form method="post" action="options.php">
                    <?php
                    // This prints out all hidden setting fields
                    settings_fields('ovic_addon_envato');
                    do_settings_sections('ovic-envato-license');
                    submit_button(null, 'primary', 'submit', true,
                        array(
                            'data-text' => esc_html__('Loading...', 'ovic-addon-toolkit')
                        )
                    );
                    ?>
                </form>
            </div>
            <?php
        }

        public function remove_options()
        {
            wp_die();
        }

        public function remove_metabox($options)
        {
            return array();
        }

        public function display_message()
        {
            // Checks license status to display under license key
            if ($this->verify['active'] == false) {
                $theme_name  = $this->theme->get('Name');
                $message     = esc_html__('Please activate this theme, the functionality will stop working if the theme is not activated.', 'ovic-addon-toolkit');
                $button_text = sprintf(esc_html__('Activate %s', 'ovic-addon-toolkit'), $theme_name);
                $button_link = admin_url('admin.php?page=ovic_addon-dashboard&tab=envato_license');
                $link        = $this->link;
                ?>
                <style>
                    .notice.ovic-addon-toolkit-notice {
                        border-left-color: red !important;
                        padding: 20px !important;
                    }

                    .rtl .notice.ovic-addon-toolkit-notice {
                        border-right-color: red !important;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-inner {
                        display: table;
                        width: 100%;
                    }

                    .notice.ovic-addon-toolkit-notice .message {
                        color: red;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-inner .ovic-addon-toolkit-notice-icon,
                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-inner .ovic-addon-toolkit-notice-content,
                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-inner .ovic-addon-toolkit-install-now {
                        display: table-cell;
                        vertical-align: middle;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-icon {
                        color: red;
                        font-size: 50px;
                        width: 162px;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-icon img {
                        display: block;
                        width: 100%;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-content {
                        padding: 0 20px;
                    }

                    .notice.ovic-addon-toolkit-notice p {
                        padding: 0;
                        margin: 0;
                    }

                    .notice.ovic-addon-toolkit-notice h3 {
                        margin: 0 0 5px;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-install-now {
                        text-align: center;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-install-now .ovic-addon-toolkit-install-button {
                        padding: 5px 30px;
                        height: auto;
                        line-height: 20px;
                        text-transform: capitalize;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-install-now .ovic-addon-toolkit-install-button i {
                        padding-right: 5px;
                        vertical-align: middle;
                    }

                    .rtl .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-install-now .ovic-addon-toolkit-install-button i {
                        padding-right: 0;
                        padding-left: 5px;
                    }

                    .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-install-now .ovic-addon-toolkit-install-button:active {
                        transform: translateY(1px);
                    }

                    @media (max-width: 782px) {
                        .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-install-now .ovic-addon-toolkit-install-button {
                            line-height: 25px;
                        }
                    }

                    @media (max-width: 767px) {
                        .notice.ovic-addon-toolkit-notice {
                            padding: 10px;
                        }

                        .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-inner {
                            display: block;
                        }

                        .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-inner .ovic-addon-toolkit-notice-content {
                            display: block;
                            padding: 0;
                        }

                        .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-notice-inner .ovic-addon-toolkit-notice-icon {
                            display: none;
                        }

                        .notice.ovic-addon-toolkit-notice .ovic-addon-toolkit-install-now .ovic-addon-toolkit-install-button {
                            margin-top: 4px;
                        }
                    }
                </style>
                <div class="notice updated is-dismissible ovic-addon-toolkit-notice ovic-addon-toolkit-install-elementor">
                    <div class="ovic-addon-toolkit-notice-inner">
                        <div class="ovic-addon-toolkit-notice-icon">
                            <img src="<?php echo esc_url(OVIC_PLUGIN_URL.'/assets/images/logo.png'); ?>"
                                 alt="Kutethemes Logo"/>
                        </div>
                        <div class="ovic-addon-toolkit-notice-content">
                            <h3>
                                <?php printf(esc_html__('Thanks for installing %s !', 'ovic-addon-toolkit'),
                                    $theme_name
                                ); ?>
                            </h3>
                            <p class="message"><?php echo esc_html($message); ?></p>
                            <a href="<?php echo esc_url($link); ?>"
                               target="_blank">
                                <?php esc_html_e('Browse theme', 'ovic-addon-toolkit'); ?>
                            </a>
                        </div>
                        <div class="ovic-addon-toolkit-install-now">
                            <a class="button button-primary ovic-addon-toolkit-install-button"
                               href="<?php echo esc_attr($button_link); ?>">
                                <i class="dashicons dashicons-download"></i>
                                <?php echo esc_html($button_text); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }

    return new Ovic_Verify_Envato();
}