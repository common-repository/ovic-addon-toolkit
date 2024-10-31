<?php
if ( !class_exists( 'Ovic_MailChimp_Settings' ) ) {
	class Ovic_MailChimp_Settings
	{
		/**
		 * Holds the values to be used in the fields callbacks
		 */
		private $options;

		/**
		 * Start up
		 */
		public function __construct()
		{
			$this->options = get_option( '_ovic_mailchimp_settings' );
			add_action( 'admin_init', array( $this, 'page_init' ) );
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_notices', array( $this, 'display_message' ) );
			add_action( 'wp_ajax_get_list_ajax', array( $this, 'get_list_ajax' ) );
		}

		/**
		 * Add options page
		 */
		public function add_plugin_page()
		{
			// This page will be under "Settings"
			add_submenu_page( 'ovic_addon-dashboard',
				esc_html__( 'MailChimp Settings', 'ovic-addon-toolkit' ),
				esc_html__( 'MailChimp Settings', 'ovic-addon-toolkit' ),
				'manage_options',
				'mailchimp-settings',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Options page callback
		 */
		public function create_admin_page()
		{
			?>
            <div class="wrap">
                <h2><?php _e( 'MailChimp Settings', 'ovic-addon-toolkit' ); ?></h2>
                <form method="post" action="options.php">
					<?php
					// This prints out all hidden setting fields
					settings_fields( 'ovic_mailchimp_group' );
					do_settings_sections( 'mailchimp-settings' );
					submit_button();
					?>
                </form>
                <script>
                    var is_busy = false;
                    $("#api_key").keyup(function () {
                        if ( is_busy === true ) {
                            return false;
                        }
                        is_busy  = true;
                        var _key = $(this).val();

                        $('.email_lists_spinner').addClass('is-active');
                        $.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: {
                                action: 'get_list_ajax',
                                key: _key
                            },
                            success: function (xhr, textStatus) {
                                if ( textStatus === 'success' ) {
                                    $('#mailcimp-email').replaceWith($(xhr[ 'html' ]));
                                }
                                is_busy = false;
                                $('.email_lists_spinner').removeClass('is-active');
                            }
                        });
                    });
                </script>
            </div>
			<?php
		}

		public function get_list_ajax()
		{
			$response = array(
				'html' => '',
			);
			$key      = isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : null;
			ob_start();
			$this->email_lists_callback( $key );
			$response['html'] = ob_get_clean();
			wp_send_json( $response );
			die();
		}

		/**
		 * Register and add settings
		 */
		public function page_init()
		{
			register_setting(
				'ovic_mailchimp_group', // Option group
				'_ovic_mailchimp_settings', // Option name
				array( $this, 'sanitize' ) // Sanitize
			);
			add_settings_section(
				'setting_section_id', // ID
				__( 'Settings', 'ovic-addon-toolkit' ), // Title
				array( $this, 'print_section_info' ), // Callback
				'mailchimp-settings' // Page
			);
			add_settings_field(
				'api_key', // ID
				__( 'Mail Chimp API Key', 'ovic-addon-toolkit' ), // Title
				array( $this, 'api_key_callback' ), // Callback
				'mailchimp-settings', // Page
				'setting_section_id' // Section
			);
			add_settings_field(
				'email_lists', // ID
				__( 'Email Lists', 'ovic-addon-toolkit' ), // Title
				array( $this, 'email_lists_callback' ), // Callback
				'mailchimp-settings', // Page
				'setting_section_id' // Section
			);
			add_settings_field(
				'success_message', // ID
				__( 'Success message', 'ovic-addon-toolkit' ), // Title
				array( $this, 'success_message_option_callback' ), // Callback
				'mailchimp-settings', // Page
				'setting_section_id' // Section
			);
		}

		/**
		 * Sanitize each setting field as needed
		 **/
		public function sanitize( $input )
		{
			if ( isset( $input['api_key'] ) )
				$new_input['api_key'] = sanitize_text_field( $input['api_key'] );
			if ( isset( $input['email_lists'] ) )
				$new_input['email_lists'] = sanitize_text_field( $input['email_lists'] );
			if ( isset( $input['success_message'] ) )
				$new_input['success_message'] = sanitize_text_field( $input['success_message'] );

			return $new_input;
		}

		/**
		 * Print the notices text
		 */
		public function display_message()
		{
			$html    = '';
			$api_key = $this->options['api_key'];
			$message = $this->options['success_message'];
			$list    = $this->options['email_lists'];
			if ( !$api_key || !$message || !$list ):
				?>
                <div class="notice-warning settings-error notice is-dismissible">
					<?php
					$html .= '<p>';
					$html .= '<strong><a href="' . admin_url( '/admin.php?page=mailchimp-settings' ) . '">' . esc_html__( 'Missing Settings Mailchimp!', 'ovic-addon-toolkit' ) . '</a></strong>';
					if ( !$api_key ) {
						$html .= ' <code>' . esc_html__( 'API Key', 'ovic-addon-toolkit' ) . '</code>';
					}
					if ( !$message ) {
						$html .= ' <code>' . esc_html__( 'Success Message', 'ovic-addon-toolkit' ) . '</code>';
					}
					if ( !$list ) {
						$html .= ' <code>' . esc_html__( 'Mail List', 'ovic-addon-toolkit' ) . '</code>';
					}
					$html .= '</p>';
					echo $html;
					?>
                </div>
			<?php
			endif;
		}

		/**
		 * Print the Section text
		 */
		public function print_section_info()
		{
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function api_key_callback()
		{
			printf(
				'<label style="display:inline-block;"><span class="spinner email_lists_spinner" style="margin: 5px 10px 0;"></span><input type="text" id="api_key" size="60" name="_ovic_mailchimp_settings[api_key]" value="%s" /></label>',
				isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key'] ) : ''
			);
			printf(
				'<p class="description">%s</p>',
				__( 'Enter your mail Chimp API key to enable a newsletter signup option with the registration form.', 'ovic-addon-toolkit' )
			);
			printf( '<a href="%s" target="_blank">%s</a>',
				'https://admin.mailchimp.com/account/api',
				esc_html__( 'Click here to get your Mailchimp API key', 'ovic-addon-toolkit' )
			);
		}

		public function email_lists_callback( $api_key = null )
		{
			$list = '';
			if ( $api_key == null ) {
				$api_key = $this->options['api_key'];
			}
			if ( isset( $this->options['email_lists'] ) && $this->options['email_lists'] ) {
				$list = $this->options['email_lists'];
			}
			$mcapi = new MCAPI( $api_key );
			$lists = $mcapi->get_lists();
			if ( !empty( $lists ) ) {
				echo '<select name="_ovic_mailchimp_settings[email_lists]" id="mailcimp-email">';
				foreach ( $lists as $key => $item ) {
					echo '<option ' . selected( $item->id, $list ) . ' value="' . $item->id . '">' . $item->name . '</option>';
				}
				echo '</select>';
			} else {
				echo '<select name="_ovic_mailchimp_settings[email_lists]" id="mailcimp-email">';
				echo '<option selected value="">' . esc_html__( 'Not found email list', 'ovic-addon-toolkit' ) . '</option>';
				echo '</select>';
			}
		}

		public function success_message_option_callback()
		{
			$message = esc_html__( 'Thanks for Subscribe !', 'ovic-addon-toolkit' );
			if ( isset( $this->options['success_message'] ) ) {
				$message = $this->options['success_message'];
			}
			printf(
				'<input type="text" id="success_message" size="40" name="_ovic_mailchimp_settings[success_message]" value="%s" />',
				esc_attr( $message )
			);
		}
	}
}
if ( is_admin() ) {
	new Ovic_MailChimp_Settings();
}