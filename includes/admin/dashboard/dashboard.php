<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

if (!class_exists('Ovic_Addon_Welcome')) {
    class Ovic_Addon_Welcome
    {
        public $tabs          = array();
        public $theme;
        public $theme_slug    = '';
        public $theme_root    = '';
        public $theme_name    = '';
        public $theme_version = '';
        public $theme_url     = '';
        public $stylesheet    = array();
        public $verify        = array();
        /* Dashboard */
        public $dashboard_url = '';
        public $dashboard_dir = '';
        /* Token */
        public $token_buy  = '';
        public $token_list = 'sntVqHmrHVU5FGEkESRFHdE45rJs9AIg';

        public function __construct()
        {
            $current_theme       = wp_get_theme(get_template());
            $this->theme_slug    = get_template();
            $this->theme         = $current_theme;
            $this->theme_root    = $current_theme->theme_root.'/'.$this->theme_slug;
            $this->theme_name    = $current_theme->get('Name');
            $this->theme_version = $current_theme->get('Version');
            $this->theme_url     = $current_theme->get('ThemeURI');
            $this->stylesheet    = OVIC_CORE()->get_stylesheet();
            $this->verify        = OVIC_CORE()->verify_envato();
            /* Dashboard */
            $this->dashboard_url = trailingslashit(plugin_dir_url(__FILE__));
            $this->dashboard_dir = trailingslashit(plugin_dir_path(__FILE__));

            $this->auto_update();

            $this->set_tabs();

            add_action('admin_menu', array($this, 'admin_menu'), 5);
            add_action('admin_enqueue_scripts', array($this, 'dashboard_admin_scripts'), 30);
            // add_action('wp_dashboard_setup', array($this, 'dashboard_add_widgets'));
        }

        public function dashboard_add_widgets()
        {
            wp_add_dashboard_widget(
                'ovic_dashboard_widgets',
                esc_html__('KuteThemes Feature Products', 'ovic-addon-toolkit'),
                array($this, 'dashboard_widgets_handler')
            );
        }

        public function dashboard_admin_scripts($hook)
        {
            if ($hook == 'toplevel_page_ovic_addon-dashboard') {
                wp_enqueue_style(
                    'ovic-addon-dashboard',
                    $this->dashboard_url.'assets/dashboard.css',
                    array(),
                    OVIC_VERSION
                );
                wp_enqueue_script(
                    'ovic-addon-dashboard',
                    $this->dashboard_url.'assets/dashboard.js',
                    array('jquery'),
                    OVIC_VERSION,
                    true
                );
                OVIC::enqueue_scripts();
            }
            if ($hook == 'index.php') {
                wp_enqueue_style(
                    'ovic-widget-dashboard',
                    $this->dashboard_url.'assets/dashboard-widgets.css',
                    array(),
                    OVIC_VERSION
                );
                wp_enqueue_script(
                    'ovic-widget-dashboard',
                    $this->dashboard_url.'assets/dashboard-widgets.js',
                    array('jquery'),
                    OVIC_VERSION,
                    true
                );
            }
        }

        public function dashboard_widgets_handler()
        {
            $feeds = array(
                'products' => array(
                    'link'         => 'https://kutethemes.com/',
                    'url'          => add_query_arg(
                        array(
                            'post_type'      => 'download',
                            'dashboard_feed' => 1,
                        ),
                        'https://kutethemes.com/feed/'
                    ),
                    'title'        => 'KuteThemes Products',
                    'items'        => 6,
                    'show_summary' => 0,
                    'show_author'  => 0,
                    'show_date'    => 1,
                ),
            );
            ?>
            <div class="ovic-dashboard-news hide-if-no-js">
                <?php wp_dashboard_primary_output('ovic_dashboard_widgets', $feeds); ?>
            </div>
            <p class="ovic-dashboard-footer">
                <?php
                printf(
                    '<a href="%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
                    esc_url('admin.php?page=ovic_addon-dashboard&tab=our_theme'),
                    __('Our Themeforest'),
                    /* translators: accessibility text */
                    __('(opens in a new tab)')
                );
                ?>

                |

                <?php
                printf(
                    '<a href="%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
                    esc_url('https://kutethemes.com/'),
                    __('Our Club Themes'),
                    /* translators: accessibility text */
                    __('(opens in a new tab)')
                );
                ?>

                |

                <?php
                printf(
                    '<a href="%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
                    /* translators: If a Rosetta site exists (e.g. https://es.wordpress.org/news/), then use that. Otherwise, leave untranslated. */
                    'admin.php?page=ovic_addon-dashboard&tab=changelog',
                    __('Changelog'),
                    /* translators: accessibility text */
                    __('(opens in a new tab)')
                );
                ?>
            </p>
            <?php
        }

        public function admin_menu()
        {
            if (current_user_can('edit_theme_options')) {
                add_menu_page(
                    __('Ovic Panel'),
                    __('Ovic Panel'),
                    'manage_options',
                    'ovic_addon-dashboard',
                    array($this, 'welcome'),
                    $this->dashboard_url.'images/icon-menu.png',
                    2
                );
            }
        }

        public function auto_update()
        {
            include_once dirname(__FILE__).'/settings.php';

            $parse = parse_url($this->theme_url);

            if (!empty($this->stylesheet['envato']) && apply_filters('ovic_disable_envato_license', false) == false) {
                include_once OVIC_PLUGIN_DIR.'includes/admin/dashboard/envato.php';
            }
            if (!empty($parse['host']) && $parse['host'] == 'kutethemes.com') {
                include_once OVIC_PLUGIN_DIR.'includes/admin/class-admin-license.php';
            }
            if (!class_exists('TGM_Plugin_Activation')) {
                include_once OVIC_PLUGIN_DIR.'includes/classes/class-tgm-plugin-activation.php';
            }
            if (!class_exists('Puc_v4_Factory') && OVIC_CORE()->get_config('auto_update') == true) {
                include_once OVIC_PLUGIN_DIR.'includes/admin/plugin-update-checker/plugin-update-checker.php';
            }
            /* UPDATE PLUGIN AUTOMATIC */
            if (class_exists('Puc_v4_Factory') && OVIC_CORE()->get_config('auto_update') == true) {
                if ($this->verify['id'] > 0 && empty($this->verify['support']) && !$this->verify['support']) {
                    return;
                }
                try {
                    /* UPDATE THEME AUTOMATIC */
                    if (class_exists('Ovic_Core_Updater')) {
                        $config  = array(
                            'item_name'       => $this->theme_name,    // Name of theme
                            'item_slug'       => $this->theme_slug,    // Theme slug
                            'version'         => $this->theme_version, // The current version of this theme
                            'root_uri'        => $this->theme_root,    // The author of this theme
                            'item_link'       => $this->theme_url,
                            'setting_license' => admin_url('admin.php?page=ovic_addon-dashboard&tab=license'),
                        );
                        $license = new Ovic_Core_Updater($config);
                        $license->updater();
                    } elseif (!empty($this->stylesheet['theme_update'])) {
                        $Theme_Updater = Puc_v4_Factory::buildUpdateChecker(
                            $this->stylesheet['theme_update'],
                            $this->theme_root,
                            $this->theme_slug
                        );
                        if (strpos($this->stylesheet['theme_update'], 'github.com') !== false) {
                            if (strpos($this->stylesheet['theme_update'], 'github.com/kutethemes') !== false) {
                                $Theme_Updater->setAuthentication('3d2ea11164978344615b2cf736b6dc4592a52769');
                            } else {
                                $Theme_Updater->setAuthentication('d32d9be4641ab98eeb335c806020231568e47825');
                            }
                        }
                    }
                } catch (Exception $error) {
                    echo "<pre>";
                    print_r($error->getMessage());
                    echo "</pre>";
                }
            }
        }

        public function set_tabs()
        {
            $tabs = array(
                'dashboard' => esc_html__('Welcome', 'ovic-addon-toolkit'),
            );
            if (class_exists('Ovic_Core_Updater')) {
                $tabs['license'] = esc_html__('Theme License', 'ovic-addon-toolkit');
            }
            if (class_exists('Ovic_Verify_Envato')) {
                $tabs['envato_license'] = esc_html__('Envato License', 'ovic-addon-toolkit');
            }
            $tabs['our_theme'] = esc_html__('More Theme', 'ovic-addon-toolkit');
            $tabs['changelog'] = esc_html__('Changelog', 'ovic-addon-toolkit');
            $tabs['settings']  = esc_html__('Settings', 'ovic-addon-toolkit');

            $this->tabs = apply_filters('ovic_registered_dashboard_tabs', $tabs);
        }

        public function welcome()
        {
            $default = 'dashboard';
            if (isset($_GET['tab'])) {
                $default = sanitize_key($_GET['tab']);
            }
            ?>
            <div class="ovic-addon-dashboard">
                <div id="tabs-container" role="tabpanel">
                    <div class="nav-tab-wrapper">
                        <?php foreach ($this->tabs as $function => $name): ?>
                            <?php
                            $class = 'nav-tab';
                            if ($default == $function) {
                                $class .= ' nav-tab-active';
                            }
                            $url = add_query_arg(
                                array(
                                    'page' => 'ovic_addon-dashboard',
                                    'tab'  => $function,
                                ),
                                admin_url('admin.php')
                            );
                            ?>
                            <a class="<?php echo esc_attr($class); ?>"
                               href="<?php echo esc_url($url); ?>" data-tab=".<?php echo esc_attr($function); ?>">
                                <?php echo esc_html($name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="tab-content-wrapper">
                        <?php foreach ($this->tabs as $function => $name): ?>
                            <?php
                            $class = 'tab-content '.$function;
                            if ($default == $function) {
                                $class .= ' active';
                            }
                            ?>
                            <div class="<?php echo esc_attr($class); ?>">
                                <?php
                                ob_start();
                                if (function_exists($function)) {
                                    $function();
                                } elseif (method_exists($this, $function)) {
                                    $this->$function();
                                }
                                $content_tab = ob_get_clean();
                                $content_tab = apply_filters('ovic_addon_dashboard_tab_content_'.$function,
                                    $content_tab, $function);
                                /* HTML Tab */
                                echo $content_tab;
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
        }

        public function support()
        {
            ?>
            <div class="rp-row support-tabs">
                <div class="rp-col">
                    <div class="support-item">
                        <h3><?php esc_html_e('Documentation', 'ovic-addon-toolkit'); ?></h3>
                        <p><?php esc_html_e('Here is our user guide for '.ucfirst(esc_html($this->theme_name)).', including basic setup steps, as well as '.ucfirst(esc_html($this->theme_name)).' features and elements for your reference.',
                                'ovic-addon-toolkit'); ?></p>
                        <a href="<?php echo esc_url($this->theme_url.'/documentation'); ?>"
                           class="button button-primary"><?php esc_html_e('Read Document', 'ovic-addon-toolkit'); ?></a>
                    </div>
                </div>
                <div class="rp-col closed">
                    <div class="support-item">
                        <h3><?php esc_html_e('Video Tutorials', 'ovic-addon-toolkit'); ?></h3>
                        <p><?php esc_html_e('Video tutorials is the great way to show you how to setup '.ucfirst(esc_html($this->theme_name)).' theme, make sure that the feature works as it\'s designed.',
                                'ovic-addon-toolkit'); ?></p>
                        <a href="<?php echo esc_url('https://www.youtube.com/watch?v=Vq6nMIyj3gg&feature=youtu.be') ?>"
                           class="button button-primary"><?php esc_html_e('See Video', 'ovic-addon-toolkit'); ?></a>
                    </div>
                </div>
                <div class="rp-col">
                    <div class="support-item">
                        <h3><?php esc_html_e('Forum', 'ovic-addon-toolkit'); ?></h3>
                        <p><?php esc_html_e('Can\'t find the solution on documentation? We\'re here to help, even on weekend. Just click here to start 1on1 chatting with us!',
                                'ovic-addon-toolkit'); ?></p>
                        <a href="<?php echo esc_url('http://support.kutethemes.net/support-system'); ?>"
                           class="button button-primary"><?php esc_html_e('Request Support',
                                'ovic-addon-toolkit'); ?></a>
                    </div>
                </div>
            </div>
            <?php
        }

        public function dashboard()
        {
            ?>
            <div class="dashboard">
                <h1>Welcome to <?php echo ucfirst(esc_html($this->theme_name)); ?> -
                    Version <?php echo esc_html($this->theme_version); ?></h1>
                <p class="about-text">Thanks for using our theme, we have worked very hard to release a great product
                    and we will do our absolute best to support this theme and fix all the issues. </p>
                <div class="dashboard-intro">
                    <div class="image">
                        <img src="<?php echo esc_url(wp_get_theme()->get_screenshot()); ?>"
                             alt="<?php echo esc_attr($this->theme_name); ?>">
                    </div>
                    <div class="intro">
                        <p class="text">
                            <strong><?php echo ucfirst(esc_html($this->theme_name)); ?></strong> is a
                            modern, clean
                            and professional WooCommerce WordPress Theme, It
                            is fully responsive, it looks stunning on all types of screens and devices.</p>
                        <?php $this->support(); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        public function license()
        {
            ?>
            <div id="dashboard-license" class="dashboard-license tab-panel">
                <?php do_action('ovic_license_'.$this->theme_slug.'_page'); ?>
            </div>
            <?php
        }

        public function envato_license()
        {
            do_action('ovic_envato_license');
        }

        public function envato_items()
        {
            $api     = add_query_arg(
                array(
                    'site'           => 'themeforest.net',
                    'page'           => '1',
                    'username'       => 'kutethemes',
                    'sort_by'        => 'sales',
                    'sort_direction' => 'desc',
                    'page_size'      => '30',
                    'term'           => 'wordpress',
                ),
                'https://api.envato.com/v1/discovery/search/search/item'
            );
            $api_key = 'ovic_dashboard_themeforest_'.md5($api);
            $items   = get_transient($api_key);

            if ($items === false) {
                $response = wp_remote_get($api, array(
                        'sslverify' => false,
                        'headers'   => array(
                            'authorization' => 'Bearer '.$this->token_list,
                        ),
                    )
                );
                if (!is_wp_error($response) && !empty($response['body'])) {
                    $data    = json_decode($response['body'], true);
                    $matches = isset($data['matches']) ? $data['matches'] : array();
                    foreach ($matches as $match) {
                        $items[$match['id']] = array(
                            'id'              => $match['id'],
                            'previews'        => $match['previews']['landscape_preview']['landscape_url'],
                            'url'             => $match['url'],
                            'rating'          => $match['rating']['rating'],
                            'number_of_sales' => $match['number_of_sales'],
                            'name'            => $match['name'],
                            'price_cents'     => $match['price_cents'],
                        );
                    }
                    set_transient($api_key, $items, 12 * HOUR_IN_SECONDS);
                }
            }

            return $items;
        }

        public function our_theme()
        {
            $items = $this->envato_items();

            if (!empty($items)) {
                if (!function_exists('ovic_link_affiliates')) {
                    /**
                     * affiliates
                     * CDN: https://cdn.staticaly.com/wp/p/:plugin_name/:version/:file
                     */
                    include OVIC_PLUGIN_DIR.'affiliates.php';
                }
                $affiliates = ovic_link_affiliates();
                ?>
                <div class="rp-row plugin-tabs">
                    <?php
                    foreach ($items as $key => $item) {
                        $url = !empty($affiliates[$item['id']]) ? $affiliates[$item['id']] : $item['url'];
                        ?>
                        <div class="rp-col">
                            <div class="plugin theme-item">
                                <div class="thumb">
                                    <a target="_blank" href="<?php echo esc_url($url); ?>">
                                        <img src="<?php echo $item['previews'] ?>"
                                             alt="<?php echo esc_attr($item['name']); ?>">
                                    </a>
                                </div>
                                <div class="meta">
                                    <?php $percent = $item['rating'] / 5 * 100; ?>
                                    <div class="star-rating">
                                        <span style="width:<?php echo esc_attr($percent); ?>%"></span>
                                    </div>
                                    <strong class="sale">
                                        <?php echo sprintf('%s %s',
                                            $item['number_of_sales'],
                                            esc_html__('Sales', 'ovic-addon-toolkit')
                                        ); ?>
                                    </strong>
                                </div>
                                <h4 class="name">
                                    <a target="_blank" href="<?php echo esc_url($url); ?>"
                                       title="<?php echo esc_attr($item['name']); ?>">
                                        <?php echo esc_html($item['name']); ?>
                                    </a>
                                </h4>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="rp-col">
                        <div class="plugin theme-item">
                            <a target="_blank" class="view-all"
                               href="https://themeforest.net/user/kutethemes/portfolio"><?php esc_html_e('View All Our Themes',
                                    'ovic-addon-toolkit'); ?></a>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        public function changelog()
        {
            if (file_exists(get_template_directory().'/changelog.txt')) {
                $changelog = file_get_contents(get_template_directory().'/changelog.txt');
                echo '<pre class="changelog">';
                print_r($changelog);
                echo '</pre>';
            } else {
                echo '<pre class="changelog">';
                print_r('No Change Log Found!');
                echo '</pre>';
            }
        }

        public function settings()
        {
            do_action('ovic_addon_settings');
        }
    }

    new Ovic_Addon_Welcome();
}