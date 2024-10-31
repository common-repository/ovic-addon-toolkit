<?php

namespace Elementor\TemplateLibrary;

use Elementor\Plugin;

/**
 * Custom template library remote source.
 *
 * https://dinhtungdu.github.io/create-your-own-elementor-template-library/
 */
if ( ! class_exists('Ovic_Source_Remote')) {
    class Ovic_Source_Remote extends Source_Base
    {
        /**
         * Elementor library option key.
         */
        const LIBRARY_OPTION_KEY = 'ovic_remote_info_library';

        /**
         * Elementor feed option key.
         */
        const FEED_OPTION_KEY = 'ovic_remote_info_feed_data';

        /**
         * API get template content URL.
         *
         * Holds the URL of the template content API.
         *
         * @access private
         * @static
         *
         * https://raw.githubusercontent.com/dinhtungdu/custom-elementor-library-dummy-api/master/templates/%d.json
         *
         * @var string API get template content URL.
         */
        private static $api_get_template_content_url = '{THEME_URI}/libary-elementor/templates/elementor-%d-%s.json';

        /**
         * API info URL.
         *
         * Holds the URL of the info API.
         *
         * @access public
         * @static
         *
         * https://my.elementor.com/api/v1/info/
         * https://raw.githubusercontent.com/dinhtungdu/custom-elementor-library-dummy-api/master/info.json
         *
         * @var string API info URL.
         */
        public static $api_info_url = '{THEME_URI}/libary-elementor/info.json';

        /**
         * Get remote template ID.
         *
         * Retrieve the remote template ID.
         *
         * @return string The remote template ID.
         * @since 1.0.0
         * @access public
         *
         */
        public function get_id()
        {
            return 'remote';
        }

        /**
         * Get remote template title.
         *
         * Retrieve the remote template title.
         *
         * @return string The remote template title.
         * @since 1.0.0
         * @access public
         *
         */
        public function get_title()
        {
            return esc_html__('Remote', 'ovic-addon-toolkit');
        }

        /**
         * Return api url.
         *
         * @param $api
         * @param  bool  $info
         *
         * @return mixed|void
         * @since 1.0.0
         * @access public
         */
        public static function get_api($api, $info = true)
        {
            $theme   = OVIC_CORE()->get_stylesheet();
            $find    = '{THEME_URI}';
            $replace = $theme['theme_uri'];

            if ($info && ! empty($theme['el_api_info'])) {
                $find    = '{THEME_URI}/libary-elementor/';
                $replace = $theme['el_api_info'];
            }
            if ( ! $info && ! empty($theme['el_api_content'])) {
                $find    = '{THEME_URI}/libary-elementor/';
                $replace = $theme['el_api_content'];
            }

            $url = str_replace($find, $replace, $api);

            return apply_filters('ovic_get_api_libary_elementor', $url, $api, $info);
        }

        /**
         * Register remote template data.
         *
         * Used to register custom template data like a post type, a taxonomy or any
         * other data.
         *
         * @since 1.0.0
         * @access public
         */
        public function register_data()
        {
        }

        /**
         * Get remote templates.
         *
         * Retrieve remote templates from Elementor.com servers.
         *
         * @param  array  $args  Optional. Nou used in remote source.
         *
         * @return array Remote templates.
         * @since 1.0.0
         * @access public
         *
         */
        public function get_items($args = [])
        {
            $library_data = self::get_library_data();

            $templates = [];

            if ( ! empty($library_data['templates'])) {
                foreach ($library_data['templates'] as $template_data) {
                    $templates[] = $this->prepare_template($template_data);
                }
            }

            return $templates;
        }

        /**
         * Get remote template.
         *
         * Retrieve a single remote template from Elementor.com servers.
         *
         * @param  int  $template_id  The template ID.
         *
         * @return array Remote template.
         * @since 1.0.0
         * @access public
         *
         */
        public function get_item($template_id)
        {
            $templates = $this->get_items();

            return $templates[$template_id];
        }

        /**
         * Save remote template.
         *
         * Remote template from Elementor.com servers cannot be saved on the
         * database as they are retrieved from remote servers.
         *
         * @param  array  $template_data  Remote template data.
         *
         * @return \WP_Error
         * @since 1.0.0
         * @access public
         *
         */
        public function save_item($template_data)
        {
            return new \WP_Error('invalid_request', 'Cannot save template to a remote source');
        }

        /**
         * Update remote template.
         *
         * Remote template from Elementor.com servers cannot be updated on the
         * database as they are retrieved from remote servers.
         *
         * @param  array  $new_data  New template data.
         *
         * @return \WP_Error
         * @since 1.0.0
         * @access public
         *
         */
        public function update_item($new_data)
        {
            return new \WP_Error('invalid_request', 'Cannot update template to a remote source');
        }

        /**
         * Delete remote template.
         *
         * Remote template from Elementor.com servers cannot be deleted from the
         * database as they are retrieved from remote servers.
         *
         * @param  int  $template_id  The template ID.
         *
         * @return \WP_Error
         * @since 1.0.0
         * @access public
         *
         */
        public function delete_template($template_id)
        {
            return new \WP_Error('invalid_request', 'Cannot delete template from a remote source');
        }

        /**
         * Export remote template.
         *
         * Remote template from Elementor.com servers cannot be exported from the
         * database as they are retrieved from remote servers.
         *
         * @param  int  $template_id  The template ID.
         *
         * @return \WP_Error
         * @since 1.0.0
         * @access public
         *
         */
        public function export_template($template_id)
        {
            return new \WP_Error('invalid_request', 'Cannot export template from a remote source');
        }

        /**
         * Get remote template data.
         *
         * Retrieve the data of a single remote template from Elementor.com servers.
         *
         * @param  array  $args  Custom template arguments.
         * @param  string  $context  Optional. The context. Default is `display`.
         *
         * @return array|\WP_Error Remote Template data.
         * @since 1.5.0
         * @access public
         *
         */
        public function get_data(array $args, $context = 'display')
        {
            $data = self::get_template_content($args['template_id']);

            if (is_wp_error($data)) {
                return $data;
            }

            // BC.
            $data = (array) $data;

            $data['content'] = $this->replace_elements_ids($data['content']);
            $data['content'] = $this->process_export_import_content($data['content'], 'on_import');

            $post_id  = $args['editor_post_id'];
            $document = Plugin::$instance->documents->get($post_id);
            if ($document) {
                $data['content'] = $document->get_elements_raw_data($data['content'], true);
            }

            return $data;
        }

        /**
         * Get templates data.
         *
         * Retrieve the templates data from a remote server.
         *
         * @param  bool  $force_update  Optional. Whether to force the data update or
         *                                     not. Default is false.
         *
         * @return array The templates data.
         * @since 2.0.0
         * @access public
         * @static
         *
         */
        public static function get_library_data($force_update = false)
        {
            self::get_info_data($force_update);

            $library_data = get_option(self::LIBRARY_OPTION_KEY);

            if (empty($library_data)) {
                return [];
            }

            return $library_data;
        }

        /**
         * Get info data.
         *
         * This function notifies the user of upgrade notices, new templates and contributors.
         *
         * @param  bool  $force_update  Optional. Whether to force the data retrieval or
         *                                     not. Default is false.
         *
         * @return array|false Info data, or false.
         * @since 2.0.0
         * @access private
         * @static
         *
         */
        private static function get_info_data($force_update = false)
        {
            $cache_key = sanitize_key(implode('-', array(
                'ovic_remote_info_api_data',
                get_template(),
                ELEMENTOR_VERSION,
                OVIC_VERSION
            )));

            $info_data = get_transient($cache_key);

            if ($force_update || false === $info_data) {
                $timeout = ($force_update) ? 25 : 8;
                $url     = self::get_api(self::$api_info_url);

                $response = wp_remote_get($url, [
                    'timeout' => $timeout,
                    'body'    => [
                        // Which API version is used.
                        'api_version' => ELEMENTOR_VERSION,
                        // Which language to return.
                        'site_lang'   => get_bloginfo('language'),
                    ],
                ]);

                if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
                    set_transient($cache_key, [], 2 * HOUR_IN_SECONDS);

                    return false;
                }

                $info_data = json_decode(wp_remote_retrieve_body($response), true);

                if (empty($info_data) || ! is_array($info_data)) {
                    set_transient($cache_key, [], 2 * HOUR_IN_SECONDS);

                    return false;
                }

                if (isset($info_data['library'])) {
                    update_option(self::LIBRARY_OPTION_KEY, $info_data['library'], 'no');

                    unset($info_data['library']);
                }

                if (isset($info_data['feed'])) {
                    update_option(self::FEED_OPTION_KEY, $info_data['feed'], 'no');

                    unset($info_data['feed']);
                }

                set_transient($cache_key, $info_data, 12 * HOUR_IN_SECONDS);
            }

            return $info_data;
        }

        /**
         * Get template content.
         *
         * Retrieve the templates content received from a remote server.
         *
         * @access public
         * @static
         *
         * @param  int  $id  The template ID.
         *
         * @return \WP_Error The template content.
         */
        public static function get_template_content($id)
        {
            $data      = self::get_info_data();
            $timestamp = gmdate('Y-m-d', $data['timestamp']);
            $url       = sprintf(self::$api_get_template_content_url, $id, $timestamp);
            $url       = self::get_api($url, false);

            $body_args = [
                'id'          => $id,
                // Which API version is used.
                'api_version' => ELEMENTOR_VERSION,
                // Which language to return.
                'site_lang'   => get_bloginfo('language'),
            ];

            /**
             * API: Template body args.
             *
             * Filters the body arguments send with the GET request when fetching the content.
             *
             * @param  array  $body_args  Body arguments.
             */
            $body_args = apply_filters('elementor/api/get_templates/body_args', $body_args);

            $response = wp_remote_get($url, [
                'timeout'   => 40,
                'body'      => $body_args,
                'sslverify' => is_ssl() ? true : false,
            ]);

            if (is_wp_error($response)) {
                wp_die($response, [
                    'back_link' => true,
                ]);
            }

            $body          = wp_remote_retrieve_body($response);
            $response_code = (int) wp_remote_retrieve_response_code($response);

            if ( ! $response_code) {
                return new \WP_Error(500, 'No Response');
            }

            // Server sent a success message without content.
            if ('null' === $body) {
                $body = true;
            }

            $body = json_decode($body, true);

            if (false === $body) {
                return new \WP_Error(422, 'Wrong Server Response');
            }

            if (200 !== $response_code) {
                // In case $as_array = true.
                $body = (object) $body;

                $message = isset($body->message) ? $body->message : wp_remote_retrieve_response_message($response);
                $code    = isset($body->code) ? $body->code : $response_code;

                return new \WP_Error($code, $message);
            }

            return $body;
        }

        /**
         * @since 2.2.0
         * @access private
         */
        private function prepare_template(array $template_data)
        {
            $favorite_templates = $this->get_user_meta('favorites');

            return [
                'template_id'     => $template_data['id'],
                'source'          => $this->get_id(),
                'type'            => $template_data['type'],
                'subtype'         => $template_data['subtype'],
                'title'           => $template_data['title'],
                'thumbnail'       => $template_data['thumbnail'],
                'date'            => $template_data['tmpl_created'],
                'author'          => $template_data['author'],
                'tags'            => json_decode($template_data['tags']),
                'isPro'           => ('1' === $template_data['is_pro']),
                'popularityIndex' => (int) $template_data['popularity_index'],
                'trendIndex'      => (int) $template_data['trend_index'],
                'hasPageSettings' => ('1' === $template_data['has_page_settings']),
                'url'             => $template_data['url'],
                'favorite'        => ! empty($favorite_templates[$template_data['id']]),
            ];
        }
    }
}