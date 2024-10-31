<?php
if ( !class_exists( 'Ovic_Mailchimp' ) && !class_exists( '_mc4wp_load_plugin' ) ) {
	class Ovic_Mailchimp
	{
		public         $options = array();
		private static $instance;

		public static function instance()
		{
			if ( !isset( self::$instance ) && !( self::$instance instanceof Ovic_Mailchimp ) ) {
				self::$instance = new Ovic_Mailchimp;
			}
			self::includes();
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'scripts' ) );
			add_action( 'wp_ajax_submit_mailchimp_via_ajax', array( self::$instance, 'submit_mailchimp_via_ajax' ) );
			add_action( 'wp_ajax_nopriv_submit_mailchimp_via_ajax', array( self::$instance, 'submit_mailchimp_via_ajax' ) );
			add_shortcode( 'ovic_mailchimp', array( self::$instance, 'mailchimp_shortcode' ) );

			return self::$instance;
		}

		public function __construct()
		{
			$this->options = get_option( '_ovic_mailchimp_settings' );
		}

		public static function includes()
		{
			include_once( 'MCAPI.class.php' );
			include_once( 'mailchimp-settings.php' );
		}

		public function scripts()
		{
			wp_enqueue_script( 'ovic-mailchimp', OVIC_PLUGIN_URL . '/includes/extends/mailchimp/mailchimp.js', array( 'jquery' ), '1.0', true );
			wp_localize_script( 'ovic-mailchimp', 'ovic_mailchimp', array(
					'ajaxurl'      => admin_url( 'admin-ajax.php' ),
					'security'     => wp_create_nonce( 'ovic_mailchimp' ),
					'text_empty'   => esc_html__( 'Email field is empty.', 'ovic-addon-toolkit' ),
					'format_email' => esc_html__( 'Wrong email format', 'ovic-addon-toolkit' ),
				)
			);
		}

		public function submit_mailchimp_via_ajax()
		{
			if ( !class_exists( 'MCAPI' ) ) {
				include_once( 'MCAPI.class.php' );
			}
			$response        = array(
				'html'    => '',
				'message' => '',
				'success' => 'no',
			);
			$api_key         = "";
			$list_id         = "";
			$merge_vars      = array();
			$success_message = esc_html__( 'Your email added...', 'ovic-addon-toolkit' );
			if ( $this->options ) {
				$api_key = isset( $this->options['api_key'] ) ? $this->options['api_key'] : '';
				$list_id = isset( $this->options['email_lists'] ) ? $this->options['email_lists'] : '';
				if ( isset( $this->options['success_message'] ) && $this->options['success_message'] != "" ) {
					$success_message = $this->options['success_message'];
				}
			}
			$data  = isset( $_POST['data'] ) ? ovic_clean( wp_unslash( $_POST['data'] ) ) : array();
			$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
			if ( isset( $_POST['list_id'] ) ) {
				$list_id = absint( $_POST['list_id'] );
			}
			$response['message'] = esc_html__( 'Failed', 'ovic-addon-toolkit' );
			unset( $data['email'] );
			if ( !empty( $data ) ) {
				foreach ( $data as $key => $datum ) {
					$merge_vars[$key] = $datum;
				}
			}
			if ( class_exists( 'MCAPI' ) ) {
				$api = new MCAPI( $api_key );
				if ( $api->subscribe( $list_id, $email, $merge_vars ) === true ) {
					$response['message'] = sanitize_text_field( $success_message );
					$response['success'] = 'yes';
				} else {
					// Sending failed
					$response['message'] = $api->get_error_message();
				}
			}
			wp_send_json( $response );
			die();
		}

		public function mailchimp_shortcode( $atts, $content = '' )
		{
			$default = array(
				'field_name'  => 'no',
				'list_id'     => '',
				'fname_text'  => 'First Name',
				'lname_text'  => 'Last Name',
				'placeholder' => 'Your email letter',
				'button_text' => 'Subscribe',
			);
			$atts    = shortcode_atts( $default, $atts );
			extract( $atts );
			$class = array( 'newsletter-form-wrap' );
			if ( $atts['field_name'] == 'yes' ) {
				$class[] = 'has-name-field';
			}
			ob_start();
			?>
            <form class="<?php echo esc_attr( implode( ' ', $class ) ); ?>">
				<?php if ( isset( $atts['list_id'] ) && $atts['list_id'] !== '' ): ?>
                    <input type="hidden" name="list_id"
                           value="<?php echo esc_attr( $atts['list_id'] ); ?>">
				<?php endif; ?>
				<?php if ( $atts['field_name'] == 'yes' ): ?>
                    <label class="text-field field-fname">
                        <input class="input-text fname" type="text" name="FNAME"
                               placeholder="<?php echo esc_html( $atts['fname_text'] ); ?>">
                    </label>
                    <label class="text-field field-lname">
                        <input class="input-text lname" type="text" name="LNAME"
                               placeholder="<?php echo esc_html( $atts['lname_text'] ); ?>">
                    </label>
				<?php endif; ?>
                <label class="text-field field-email">
                    <input class="input-text email email-newsletter" type="email" name="email"
                           placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>">
                </label>
                <a href="#" class="button btn-submit submit-newsletter">
					<?php echo esc_html( $atts['button_text'] ); ?>
                </a>
            </form>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'ovic_output_mailchimp_form', $html, $atts );
		}
	}
}
$ovic_mailchimp = new Ovic_Mailchimp();
$ovic_mailchimp::instance();
