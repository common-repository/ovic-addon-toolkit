<?php
/**
 * OVIC_CORE setup
 *
 * @package OVIC_CORE
 * @since   3.2.0
 */
defined('ABSPATH') || exit;

if (!class_exists('OVIC_CORE')) :
    /**
     * Main OVIC_CORE Class.
     *
     * @class OVIC_CORE
     */
    final class OVIC_CORE
    {
        /**
         * OVIC_CORE version.
         *
         * @var string
         */
        public $version = OVIC_VERSION;

        /**
         * The single instance of the class.
         *
         * @var OVIC_CORE
         * @since 1.0
         */
        protected static $_instance = null;

        /**
         * Array of deprecated hook handlers.
         *
         * @var array of Ovic_Deprecated_Hooks
         */
        public $deprecated_hook_handlers = array();

        /**
         * Array of Envato license.
         *
         * @var array of license
         */
        private $license = array(
            'voka'          => '22555312',
            'lewear'        => '24743918',
            'armania'       => '26743715',
            'kute-boutique' => '14799139',
        );
        public  $default = array(
            'footer'           => true,
            'megamenu'         => true,
            'mobile'           => true,
            'product_brand'    => true,
            'post_like'        => true,
            'add_to_cart'      => true,
            'popup_notice'     => true,
            'editor_term'      => true,
            'auto_update'      => true,
            'elementor_grid'   => true,
            'lazyload'         => false,
            'crop'             => false,
            'photo_editor'     => false,
            'question_answers' => false,
            'remote_source'    => false,
            'demo_mode'        => false,
            'fa4_support'      => false,
            'snow_effect'      => false,
            'placeholder'      => '',
            'fontawesome'      => 'fa4',
            'snow_text'        => '❅',
            'snow_color'       => '#fff',
            'snow_background'  => 'transparent',
            'snow_limit'       => 60,
            'snow_speed'       => 30,
            'snow_size'        => array(
                'width'  => 20,
                'height' => 30,
                'unit'   => 'px',
            ),
            'mobile_delay'     => 0,
            'mobile_menu'      => '',
            'clear_cache'      => '',
            'megamenu_resize'  => '',
        );

        /**
         * Main OVIC_CORE Instance.
         *
         * Ensures only one instance of WooCommerce is loaded or can be loaded.
         *
         * @return OVIC_CORE - Main instance.
         * @see OVIC_CORE()
         * @since 1.0
         * @static
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * OVIC_CORE Constructor.
         */
        public function __construct()
        {
            $this->includes();
            $this->init_hooks();
        }

        /**
         * Hook into actions and filters.
         *
         * @since 2.3
         */
        private function init_hooks()
        {
            add_filter('cron_schedules', array($this, 'add_cron_interval'));
            add_action('plugins_loaded', array($this, 'on_plugins_loaded'), -1);
            add_action('after_setup_theme', array($this, 'include_template_functions'), 11);
            add_action('init', array($this, 'init'), 0);
        }

        /**
         * What type of request is this?
         *
         * @param  string  $type  admin, ajax, cron or frontend.
         *
         * @return bool
         */
        public function is_request($type)
        {
            switch ($type) {
                case 'admin':
                    return is_admin();
                case 'ajax':
                    return function_exists('wp_doing_ajax') ? wp_doing_ajax() : defined('DOING_AJAX');
                case 'cron':
                    return defined('DOING_CRON');
                case 'frontend':
                    return !is_admin() && !defined('DOING_CRON');
                default:
                    return false;
            }
        }

        function add_cron_interval($schedules)
        {
            $schedules['monthly'] = array(
                'interval' => 60 * 60 * 24 * 30,
                'display'  => esc_html__('Once Monthly', 'ovic-addon-toolkit'),
            );
            $schedules['yearly']  = array(
                'interval' => 60 * 60 * 24 * 365,
                'display'  => esc_html__('Once Yearly', 'ovic-addon-toolkit'),
            );

            return $schedules;
        }

        /**
         * What is support elementor or not?
         *
         * @param  string  $type  post_type or id.
         *
         * @return bool
         */
        public function is_support_elementor($type)
        {
            $post_type   = is_numeric($type) ? get_post_type($type) : $type;
            $cpt_support = get_option('elementor_cpt_support', ['page', 'post']);

            if (class_exists('Elementor\Plugin') && in_array($post_type, $cpt_support)) {
                return true;
            }

            return false;
        }

        /**
         * What is elementor or not?
         *
         * @param  int  $post_id  post_type or id.
         *
         * @return bool
         */
        public function is_elementor($post_id)
        {
            if (class_exists('Elementor\Plugin') && $this->is_support_elementor($post_id)) {
                if (get_post_meta($post_id, '_elementor_edit_mode', true)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * is WPBackery editor?
         *
         * @return bool
         */
        public function is_vc_editor()
        {
            if ($get_referer = wp_get_referer()) {
                if (strpos(parse_url($get_referer, PHP_URL_QUERY), 'vc_action=vc_inline') !== false) {
                    return true;
                }
            }

            return false;
        }

        /**
         * is Elementor editor?
         *
         * @return bool
         */
        public function is_elementor_editor()
        {
            if (class_exists('Elementor\Plugin')) {
                if (Elementor\Plugin::$instance->preview->is_preview_mode() || Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    return true;
                }
            }

            return false;
        }

        /**
         * is lazy load
         *
         * @return bool
         */
        public function is_lazy()
        {
            if ($this->is_request('ajax')) {
                return false;
            }

            $enable_lazy = apply_filters('ovic_resize_enable_lazy_load', $this->get_config('lazyload'));

            if ($enable_lazy == true) {
                return true;
            }

            return false;
        }

        /**
         * is crop image
         *
         * @return bool
         */
        public function is_crop()
        {
            $disable_crop = apply_filters('ovic_resize_disable_crop_image', $this->get_config('crop'));

            if ($disable_crop == true) {
                return true;
            }

            return false;
        }

        /**
         * placeholder image
         *
         * @return bool
         */
        public function placeholder()
        {
            return apply_filters('ovic_resize_placeholder_image', $this->get_config('placeholder'));
        }

        /**
         * placeholder svg
         *
         * @param $width
         * @param $height
         *
         * @param  bool  $image
         *
         * @return string
         */
        public function image_svg($width, $height, $image = false)
        {
            $data = rawurldecode("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22{$width}%22%20height%3D%22{$height}%22%20viewBox%3D%220%200%20{$width}%20{$height}%22%3E%3C%2Fsvg%3E");

            if (!$image) {
                return $data;
            }

            return "<img src='{$data}' width='{$width}' height='{$height}' alt='svg placeholder'/>";
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        public function includes()
        {
            /**
             * Abstract classes.
             */
            require_once OVIC_PLUGIN_DIR.'includes/classes/abstract-widget.php';
            require_once OVIC_PLUGIN_DIR.'includes/classes/abstract-breadcrumb.php';
            include_once OVIC_PLUGIN_DIR.'includes/classes/abstract-deprecated-hooks.php';
            /**
             * Core classes.
             */
            include_once OVIC_PLUGIN_DIR.'includes/classes/class-ajax.php';
            include_once OVIC_PLUGIN_DIR.'includes/classes/class-deprecated-action-hooks.php';
            include_once OVIC_PLUGIN_DIR.'includes/classes/class-deprecated-filter-hooks.php';
            /**
             * Core functions.
             */
            include_once OVIC_PLUGIN_DIR.'includes/classes/ovic-core-functions.php';
            include_once OVIC_PLUGIN_DIR.'includes/classes/ovic-deprecated-functions.php';
            include_once OVIC_PLUGIN_DIR.'includes/admin/class-admin-profile.php';
            /**
             * Libraries
             */
            if ($this->is_request('admin')) {
                include_once OVIC_PLUGIN_DIR.'includes/admin/class-admin.php';
            }
            if ($this->is_request('frontend')) {
                $this->frontend_includes();
            }
            /**
             * Abstract shortcode.
             */
            require_once OVIC_PLUGIN_DIR.'includes/classes/abstract-shortcode.php';
        }

        /**
         * Include required frontend files.
         */
        public function frontend_includes()
        {
            include_once OVIC_PLUGIN_DIR.'includes/classes/ovic-template-hooks.php';
            include_once OVIC_PLUGIN_DIR.'includes/classes/class-frontend-scripts.php';
            include_once OVIC_PLUGIN_DIR.'includes/classes/class-adjacent-products.php';
        }

        /**
         * Function used to Init OVIC_CORE Template Functions - This makes them pluggable by plugins and themes.
         */
        public function include_template_functions()
        {
            if (class_exists('WooCommerce')) {
                include_once OVIC_PLUGIN_DIR.'includes/classes/woocommerce-functions.php';
            }
            include_once OVIC_PLUGIN_DIR.'includes/classes/ovic-template-functions.php';
        }

        /**
         * Init OVIC_CORE when WordPress Initialises.
         */
        public function init()
        {
            // Before init action.
            do_action('before_ovic_init');

            // Set up localisation.
            $this->load_plugin_textdomain();

            $this->deprecated_hook_handlers['actions'] = new Ovic_Deprecated_Action_Hooks();
            $this->deprecated_hook_handlers['filters'] = new Ovic_Deprecated_Filter_Hooks();

            // Init action.
            do_action('ovic_init');
        }

        /**
         * When WP has loaded all plugins, trigger the `ovic_loaded` hook.
         *
         * This ensures `ovic_loaded` is called only after all other plugins
         * are loaded, to avoid issues caused by plugin directory naming changing
         * the load order. See #21524 for details.
         *
         * @since 3.6.0
         */
        public function on_plugins_loaded()
        {
            /* LOAD THEME OPTIONS */
            require_once OVIC_PLUGIN_DIR.'includes/options/options.php';

            /* LOAD EXTENDS */
            if ($this->get_config('megamenu') == true) {
                require_once OVIC_PLUGIN_DIR.'includes/extends/megamenu/megamenu.php';
            }

            if ($this->get_config('footer') == true) {
                require_once OVIC_PLUGIN_DIR.'includes/extends/footer-builder/footer-builder.php';
            }

            if ($this->get_config('question_answers') == true) {
                require_once OVIC_PLUGIN_DIR.'includes/extends/question-answers/question-answers.php';
            }

            if ($this->get_config('post_like') == true) {
                require_once OVIC_PLUGIN_DIR.'includes/extends/post-like/post-like.php';
            }

            if ($this->get_config('snow_effect') == true) {
                require_once OVIC_PLUGIN_DIR.'includes/extends/snow-effect/snow-effect.php';
            }

            if ($this->get_config('photo_editor') == true) {
                require_once OVIC_PLUGIN_DIR.'includes/extends/photo-editor/photo-editor.php';
            }

            if ($this->get_config('editor_term') == true) {
                require_once OVIC_PLUGIN_DIR.'includes/extends/description-editor/description-editor.php';
            }

            if (class_exists('WooCommerce')) {

                if ($this->get_config('product_brand') == true) {
                    require_once OVIC_PLUGIN_DIR.'includes/extends/product-brand/product-brand.php';
                }

                require_once OVIC_PLUGIN_DIR.'includes/extends/product-cat/product-cat.php';
                require_once OVIC_PLUGIN_DIR.'includes/widgets/widget-attribute-product.php';

            }

            /* LOAD WIDGETS */
            require_once OVIC_PLUGIN_DIR.'includes/widgets/widget-iconbox.php';

            require_once OVIC_PLUGIN_DIR.'includes/widgets/widget-custommenu.php';

            do_action('ovic_loaded');
        }

        /**
         * Load Localisation files.
         *
         * Note: the first-loaded translation file overrides any following ones if the same translation is present.
         *
         * Locales found in:
         *      - WP_LANG_DIR/ovic-addon-toolkit/ovic-addon-toolkit-LOCALE.mo
         *      - WP_LANG_DIR/plugins/ovic-addon-toolkit-LOCALE.mo
         */
        public function load_plugin_textdomain()
        {
            if (function_exists('determine_locale')) {
                $locale = determine_locale();
            } else {
                // @todo Remove when start supporting WP 5.0 or later.
                $locale = is_admin() ? get_user_locale() : get_locale();
            }

            $locale = apply_filters('plugin_locale', $locale, 'ovic-addon-toolkit');

            unload_textdomain('ovic-addon-toolkit');
            load_textdomain('ovic-addon-toolkit', WP_LANG_DIR.'/ovic-addon-toolkit/ovic-addon-toolkit-'.$locale.'.mo');
            load_plugin_textdomain('ovic-addon-toolkit', false, plugin_basename(dirname(OVIC_PLUGIN_FILE)).'/languages');
        }

        /**
         * Get the key config.
         *
         * @param  string  $type
         *
         * @return string
         */
        public function get_key($type = 'settings')
        {
            if ($type == 'envato') {
                return 'ovic_envato_license';
            }

            return 'ovic_addon_settings';
        }

        /**
         * Get the config.
         *
         * @param  string  $key
         * @param  bool  $default
         *
         * @return string
         */
        public function get_config($key = '', $default = false)
        {
            $key    = trim($key);
            $config = get_option($this->get_key());
            $config = wp_parse_args($config, $this->default);

            if (empty($key)) {
                return $config;
            }

            if (!empty($config[$key])) {
                return $config[$key];
            }

            return $default;
        }

        /**
         * Update the config.
         *
         * @param $key
         * @param $value
         */
        public function set_config($key, $value = '')
        {
            if (is_array($key)) {

                update_option($this->get_key(), $key);

            } else {

                $config       = get_option($this->get_key());
                $key          = trim($key);
                $config[$key] = $value;

                update_option($this->get_key(), $config);

            }
        }

        /**
         * get stylesheet data.
         */
        public function get_stylesheet()
        {
            $slug            = get_template();
            $file_stylesheet = trailingslashit(get_template_directory()).'/style.css';
            $file_data       = get_file_data($file_stylesheet,
                array(
                    'market'         => 'Market',
                    'theme_name'     => 'Theme Name',
                    'theme_uri'      => 'Theme URI',
                    'author_uri'     => 'Author URI',
                    'version'        => 'Version',
                    'text_domain'    => 'Text Domain',
                    'author'         => 'Author',
                    'theme_update'   => 'Theme Update',
                    'el_api_content' => 'El Api Content',
                    'el_api_info'    => 'El Api Info',
                )
            );

            if (in_array($slug, array_keys($this->license))) {
                $file_data['envato'] = $this->license[$slug];
            }

            return $file_data;
        }

        /*
         *
         * 200 – Everything was okay and the purchase code is valid!
         * 404 – The purchase code was invalid, not real, or was not from one of your customers.
         * 403 – The personal token is incorrect or does not have the required permission(s).
         * 401 – The authorization header is missing or malformed. Verify that your code is correct.
         * 400 – A parameter or argument in the request was invalid.
         *
         * exp:
         * - code:  ca637a3c-7c72-42c2-8f2b-fb66a8cac7f1
         * - Token: ItdonUWAUYnQBvEXOcB7ugEIEOQhZU6s
         *
         * */
        public function verify_envato()
        {
            $key        = $this->get_key('envato');
            $stylesheet = $this->get_stylesheet();
            $id         = !empty($stylesheet['envato']) ? $stylesheet['envato'] : 0;
            $settings   = get_option($key);
            $response   = array(
                'id'       => $id,
                'active'   => false,
                'support'  => false,
                'settings' => array(),
                'product'  => array(),
            );

            if (empty($settings['purchased_code']) || empty($stylesheet['envato'])) {
                return $response;
            }

            $api_key   = array_merge(array('ovic-envato-license', $id, OVIC_VERSION), $settings);
            $cache_key = sanitize_key(implode('-', $api_key));
            $options   = get_transient($cache_key);

            if ($options) {
                return $options;
            }

            $home_url             = home_url('/');
            $code                 = $settings['purchased_code'];
            $personalToken        = "ItdonUWAUYnQBvEXOcB7ugEIEOQhZU6s";
            $userAgent            = "Purchase code verification on {$home_url}";
            $response['settings'] = $settings;

            // Surrounding whitespace can cause a 404 error, so trim it first
            $code = trim($code);

            // Make sure the code looks valid before sending it to Envato
            if (!preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $code)) {
                return $response;
            }

            // Build the request
            $data = wp_remote_get("https://api.envato.com/v3/market/author/sale?code={$code}",
                array(
                    'sslverify' => true,
                    'headers'   => array(
                        'authorization' => 'Bearer '.$personalToken,
                        'User-Agent'    => $userAgent,
                    ),
                )
            );
            if (!is_wp_error($data) && wp_remote_retrieve_response_code($data) == 200) {
                $content = wp_remote_retrieve_body($data);
                $content = json_decode($content, true);

                if ($content['item']['id'] == $stylesheet['envato']) {
                    $response['product'] = $content;
                    $response['active']  = true;

                    if (!empty($content['supported_until'])) {
                        $response['support'] = false;
                    } else {
                        $response['support'] = true;
                    }

                    set_transient($cache_key, $response, 12 * HOUR_IN_SECONDS);

                    return $response;
                }
            }

            return $response;
        }

        /**
         * Get the plugin url.
         *
         * @return string
         */
        public function plugin_url()
        {
            return untrailingslashit(plugins_url('/', OVIC_PLUGIN_FILE));
        }

        /**
         * Get the plugin path.
         *
         * @return string
         */
        public function plugin_path()
        {
            return untrailingslashit(plugin_dir_path(OVIC_PLUGIN_FILE));
        }

        /**
         * Get the template path.
         *
         * @return string
         */
        public function template_path()
        {
            return apply_filters('ovic_template_path', 'woocommerce/');
        }

        /**
         * Get Ajax URL.
         *
         * @return string
         */
        public function ajax_url()
        {
            return admin_url('admin-ajax.php', 'relative');
        }
    }
endif;