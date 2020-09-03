<?php
/**
 * WP_Auto_Updater_Notification class
 *
 * @package WP_Auto_Updater
 *
 * @since 1.4.0
 */

/**
 * Core class WP_Auto_Updater_Notification
 *
 * @since 1.4.0
 */
class WP_Auto_Updater_Notification {

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var string $option_group   The group name of option
	 */
	protected $option_group = 'wp_auto_updater';

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var string $option_name   The option name
	 */
	protected $option_name = 'wp_auto_updater_notification_options';

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var string $capability   The types of capability
	 */
	protected $capability = 'update_core';

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var array $default_options {
	 *   default options
	 *
	 *   @type array notification {
	 *       @type bool core
	 *       @type bool theme
	 *       @type bool plugin
	 *       @type bool translation
	 *   }
	 *   @type array notification {
	 *       @type string from
	 *       @type bool   admin_email
	 *       @type array  recipients
	 *   }
	 * }
	 */
	protected $default_options = array(
		'notification' => array(
			'core'        => true,
			'theme'       => false,
			'plugin'      => false,
			'translation' => false,
		),
		'mail'         => array(
			'from'        => '',
			'admin_email' => true,
			'recipients'  => array(),
		),
	);

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @since 1.4.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Initialize.
	 *
	 * Hooks to init
	 *
	 * @access public
	 *
	 * @since 1.4.0
	 */
	public function init() {
		add_action( 'wp_loaded', array( $this, 'set_update_notification_core' ) );
		add_filter( 'auto_core_update_email', array( $this, 'change_core_update_email' ), 10, 4 );
		add_action( 'wp_loaded', array( $this, 'disable_theme_and_plugin_update_notification' ) );
	}

	/**
	 * Sends an email.
	 *
	 * Note that Core update uses built-in notification mail.
	 *
	 * @access public
	 *
	 * @param string $type           The type of update. Can be one of 'theme', 'plugin', 'translation'.
	 * @param array  $info_success   A list of updates that succeeded.
	 * @param array  $info_failed    A list of updates that failed.
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function send_email( $type, $info_success, $info_failed ) {
		if ( empty( $type ) ) {
			return;
		}

		if ( 'core' === $type ) {
			return;
		}

		if ( empty( $info_success ) && empty( $info_failed ) ) {
			return;
		}

		$notification = $this->get_options( 'notification' );

		if ( ! $notification['theme'] && ! $notification['plugin'] && ! $notification['translation'] ) {
			return;
		}

		if ( 'theme' === $type && $notification['theme'] ) {
			/* translators: %s: Site title. */
			$subject = __( '[%s] Some themes were automatically updated', 'wp-auto-updater' );

			if ( $info_success ) {
				$body[] = __( 'The following themes were successfully updated:', 'wp-auto-updater' );
				$body[] = implode( "\n", $info_success );
			}
			if ( $info_failed ) {
				$body[] = sprintf(
					/* translators: %s: Home URL. */
					__( 'Howdy! Failures occurred when attempting to update themes on your site at %s.', 'wp-auto-updater' ),
					home_url()
				);
				$body[] = "\n";
				$body[] = __( 'Please check out your site now. It’s possible that everything is working. If it says you need to update, you should do so.', 'wp-autoupdates' );
				$body[] = "\n";
				$body[] = __( 'The following themes failed to update:', 'wp-auto-updater' );
				$body[] = implode( "\n", $info_failed );
			}
		}

		if ( 'plugin' === $type && $notification['plugin'] ) {
			$subject = __( '[%s] Some plugins were automatically updated', 'wp-auto-updater' );

			if ( $info_success ) {
				$body[] = __( 'The following plugins were successfully updated:', 'wp-auto-updater' );
				$body[] = implode( "\n", $info_success );
			}
			if ( $info_failed ) {
				$body[] = sprintf(
					/* translators: %s: Home URL. */
					__( 'Howdy! Failures occurred when attempting to update plugins on your site at %s.', 'wp-auto-updater' ),
					home_url()
				);
				$body[] = "\n";
				$body[] = __( 'Please check out your site now. It’s possible that everything is working. If it says you need to update, you should do so.', 'wp-autoupdates' );
				$body[] = "\n";
				$body[] = __( 'The following plugins failed to update:', 'wp-auto-updater' );
				$body[] = implode( "\n", $info_failed );
			}
		}

		if ( 'translation' === $type && $notification['translation'] ) {
			$subject = __( '[%s] Some translations were automatically updated', 'wp-auto-updater' );

			if ( $info_success ) {
				$body[] = __( 'The following translations were successfully updated:', 'wp-auto-updater' );
				$body[] = implode( "\n", $info_success );
			}
			if ( $info_failed ) {
				$body[] = sprintf(
					/* translators: %s: Home URL. */
					__( 'Howdy! Failures occurred when attempting to update translations on your site at %s.', 'wp-auto-updater' ),
					home_url()
				);
				$body[] = "\n";
				$body[] = __( 'Please check out your site now. It’s possible that everything is working. If it says you need to update, you should do so.', 'wp-autoupdates' );
				$body[] = "\n";
				$body[] = __( 'The following translations failed to update:', 'wp-auto-updater' );
				$body[] = implode( "\n", $info_failed );
			}
		}

		$body[] = "\n";
		$body[] = __( 'See Update history:', 'wp-auto-updater' );
		$body[] = admin_url( 'index.php?page=wp-auto-updater-history', 'https' );

		$body    = implode( "\n", $body );
		$to      = get_site_option( 'admin_email' );
		$subject = sprintf( $subject, wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
		$headers = '';

		$email = compact( 'to', 'subject', 'body', 'headers' );

		add_filter( 'wp_mail_from', array( $this, 'change_mail_from' ) );
		add_filter( 'wp_auto_updater_notification/wp_mail', array( $this, 'change_email' ), 10, 3 );

		/**
		 * Filters the email sent following an automatic background plugin update.
		 *
		 * @param array $email {
		 *     Array of email arguments that will be passed to wp_mail().
		 *
		 *     @type string $to      The email recipient. An array of emails
		 *                           can be returned, as handled by wp_mail().
		 *     @type string $subject The email's subject.
		 *     @type string $body    The email message body.
		 *     @type string $headers Any email headers, defaults to no headers.
		 * }
		 * @param object $info_success The updates that succeeded.
		 * @param object $info_failed     The updates that failed.
		 */
		$email = apply_filters( 'wp_auto_updater_notification/wp_mail', $email, $info_success, $info_failed );
		wp_mail( $email['to'], wp_specialchars_decode( $email['subject'] ), $email['body'], $email['headers'] );

		remove_filter( 'wp_mail_from', array( $this, 'change_mail_from' ) );
		remove_filter( 'wp_auto_updater_notification/wp_mail', array( $this, 'change_email' ) );
	}

	/**
	 * Change mail from to the WordPress default.
	 *
	 * Hooks to wp_mail_from.
	 *
	 * @access public
	 *
	 * @param string $from_email   email address.
	 *
	 * @return string
	 *
	 * @since 1.4.0
	 */
	public function change_mail_from( $from_email ) {
		$options = $this->get_options( 'mail' );

		if ( $options['from'] && is_email( $options['from'] ) ) {
			$from_email = $options['from'];
		}

		return $from_email;
	}

	/**
	 * Change email for core update.
	 *
	 * Hooks to auto_core_update_email.
	 * see https://developer.wordpress.org/reference/hooks/auto_core_update_email/
	 *
	 * @access public
	 *
	 * @param object $email
	 * @param string $type
	 * @param object $core_update
	 * @param mixed  $result
	 *
	 * @return object
	 *
	 * @since 1.4.0
	 */
	public function change_core_update_email( $email, $type, $core_update, $result ) {
		add_filter( 'wp_mail_from', array( $this, 'change_mail_from' ) );
		$email = $this->change_email( $email, array(), array() );

		return $email;
	}

	/**
	 * Change email for wp_auto_updater notification.
	 *
	 * Hooks to wp_auto_updater_notification/wp_mail.
	 *
	 * @access public
	 *
	 * @param object $email
	 * @param array  $info_success
	 * @param array  $info_failed
	 *
	 * @return object
	 *
	 * @since 1.4.0
	 */
	public function change_email( $email, $info_success, $info_failed ) {
		$options = $this->get_options( 'mail' );

		if ( ! $options['admin_email'] && ! $options['recipients'] ) {
			return $email;
		}

		$recipients_email_to = array();

		if ( $options['admin_email'] ) {
			$recipients_email_to[] = $email['to'];
		}

		if ( $options['recipients'] ) {
			$args['role'] = 'administrator';
			$users        = get_users( $args );

			foreach ( $users as $user ) {
				if ( in_array( $user->ID, $options['recipients'] ) ) {
					if ( is_email( $user->user_email ) ) {
						$recipients_email_to[] = $user->user_email;
					}
				}
			}

			$email['to'] = $recipients_email_to;
		}

		return $email;
	}

	/**
	 * Set core update notification.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function set_update_notification_core() {
		$options = $this->get_options( 'notification' );
		$option  = $options['core'];

		if ( $option ) {
			add_filter( 'auto_core_update_send_email', '__return_true' );
		}
		else {
			add_filter( 'auto_core_update_send_email', '__return_false' );
		}
	}

	/**
	 * Disable theme and plugin update notification mail.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.5.0
	 */
	public function disable_theme_and_plugin_update_notification() {
		add_filter( 'auto_theme_update_send_email', '__return_false' );
		add_filter( 'auto_plugin_update_send_email', '__return_false' );
	}

	/**
	 * Returns capability.
	 *
	 * @access public
	 *
	 * @return string
	 *
	 * @since 1.4.0
	 */
	public function option_page_capability() {
		return $this->capability;
	}

	/**
	 * Returns the options array or value.
	 *
	 * @access public
	 *
	 * @param string $option_name Optional. The option name.
	 *
	 * @return array|null
	 *
	 * @since 1.4.0
	 */
	public function get_options( $option_name = null ) {
		$options = get_option( $this->option_name, $this->default_options );
		$options = array_replace_recursive( $this->default_options, $options );

		if ( is_null( $option_name ) ) {
			/**
			 * Filters the options.
			 *
			 * @param array    $options     The options.
			 *
			 * @since 1.4.0
			 */
			return apply_filters( 'wp_auto_updater_notification/get_options', $options );
		}

		if ( array_key_exists( $option_name, $options ) ) {
			/**
			 * Filters the option.
			 *
			 * @param mixed   $option           The value of option.
			 * @param string  $option_name      The option name via argument.
			 *
			 * @since 1.4.0
			 */
			return apply_filters( 'wp_auto_updater_notification/get_option', $options[ $option_name ], $option_name );
		}
		else {
			return null;
		}
	}

	/**
	 * Register the form setting.
	 *
	 * Hooks to admin_init.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function register_settings() {
		if ( null === $this->get_options() ) {
			add_option( $this->option_name );
		}

		register_setting(
			$this->option_group,
			$this->option_name,
			array( $this, 'validate_options' )
		);

		add_settings_section(
			'notification',
			__( 'Notification', 'wp-auto-updater' ),
			array( $this, 'settings_section_cb_notification' ),
			'wp_auto_updater'
		);

		add_settings_field(
			'core',
			__( 'WordPress Core', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_core_notification' ),
			'wp_auto_updater',
			'notification'
		);

		add_settings_field(
			'theme',
			__( 'Theme', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_theme_notification' ),
			'wp_auto_updater',
			'notification'
		);

		add_settings_field(
			'plugin',
			__( 'Plugin', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_plugin_notification' ),
			'wp_auto_updater',
			'notification'
		);

		add_settings_field(
			'translation',
			__( 'Translation', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_translation_notification' ),
			'wp_auto_updater',
			'notification'
		);

		add_settings_field(
			'from_mail',
			__( 'From Email', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_from_mail' ),
			'wp_auto_updater',
			'notification'
		);

		add_settings_field(
			'recipients',
			__( 'Recipients', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_recipients' ),
			'wp_auto_updater',
			'notification'
		);
	}

	/**
	 * Callback function for settings_section 'notification'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function settings_section_cb_notification() {}

	/**
	 * Callback function for settings_field 'core_notification'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function settings_field_cb_core_notification() {
		$options = $this->get_options( 'notification' );
		$option  = $options['core'];
		?>
<p><label><input type="checkbox" name="wp_auto_updater_notification_options[notification][core]" value="1"<?php checked( true, $option ); ?>> <?php echo esc_html__( 'Enable update notification', 'wp-auto-updater' ); ?></label></p>
		<?php
	}

	/**
	 * Callback function for settings_field 'theme_notification'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function settings_field_cb_theme_notification() {
		$options = $this->get_options( 'notification' );
		$option  = $options['theme'];
		?>
<p><label><input type="checkbox" name="wp_auto_updater_notification_options[notification][theme]" value="1"<?php checked( true, $option ); ?>> <?php echo esc_html__( 'Enable update notification', 'wp-auto-updater' ); ?></label></p>
		<?php
	}

	/**
	 * Callback function for settings_field 'plugin_notification'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function settings_field_cb_plugin_notification() {
		$options = $this->get_options( 'notification' );
		$option  = $options['plugin'];
		?>
<p><label><input type="checkbox" name="wp_auto_updater_notification_options[notification][plugin]" value="1"<?php checked( true, $option ); ?>> <?php echo esc_html__( 'Enable update notification', 'wp-auto-updater' ); ?></label></p>
		<?php
	}

	/**
	 * Callback function for settings_field 'translation_notification'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function settings_field_cb_translation_notification() {
		$options = $this->get_options( 'notification' );
		$option  = $options['translation'];
		?>
<p><label><input type="checkbox" name="wp_auto_updater_notification_options[notification][translation]" value="1"<?php checked( true, $option ); ?>> <?php echo esc_html__( 'Enable update notification', 'wp-auto-updater' ); ?></label></p>
		<?php
	}

	/**
	 * Callback function for settings_field 'from_mail'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function settings_field_cb_from_mail() {
		$options = $this->get_options( 'mail' );
		$option  = $options['from'];
		?>
<p><input type="text" name="wp_auto_updater_notification_options[mail][from]" value="<?php echo esc_attr( $option ); ?>"</p>
<p><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'WP Auto Updater will send notifications from this email address. Leave blank to use the WordPress default.', 'wp-auto-updater' ); ?></p>
		<?php
	}

	/**
	 * Callback function for settings_field 'recipients'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function settings_field_cb_recipients() {
		$options = $this->get_options( 'mail' );
		$option  = $options['admin_email'];
		?>
<p><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Select one or more recipients. Only users with the Administrator role can select recipients.', 'wp-auto-updater' ); ?></p>
<p><label><input type="checkbox" name="wp_auto_updater_notification_options[mail][admin_email]" value="1"<?php checked( true, $option ); ?>> <?php echo esc_html__( 'Administration Email Address (General Settings)', 'wp-auto-updater' ); ?></label></p>
		<?php
		$recipients   = $options['recipients'];
		$args['role'] = 'administrator';
		$users        = get_users( $args );

		foreach ( $users as $user ) {
			?>
<p><label><input type="checkbox" name="wp_auto_updater_notification_options[mail][recipients][]" value="<?php echo esc_attr( $user->ID ); ?>"<?php checked( true, in_array( $user->ID, $recipients ) ); ?>> <?php echo esc_html( $user->user_login ); ?></label></p>
			<?php
		}

	}

	/**
	 * Validate options.
	 *
	 * @access public
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function validate_options( $input ) {
		$output = $this->default_options;

		$output['notification']['core']        = empty( $input['notification']['core'] ) ? false : true;
		$output['notification']['theme']       = empty( $input['notification']['theme'] ) ? false : true;
		$output['notification']['plugin']      = empty( $input['notification']['plugin'] ) ? false : true;
		$output['notification']['translation'] = empty( $input['notification']['translation'] ) ? false : true;

		$output['mail']['from'] = isset( $input['mail']['from'] ) && is_email( $input['mail']['from'] ) ? $input['mail']['from'] : '';

		if ( empty( $input['mail']['admin_email'] ) && empty( $input['mail']['recipients'] ) ) {
			$output['mail']['admin_email'] = true;
		}
		else {
			$output['mail']['admin_email'] = empty( $input['mail']['admin_email'] ) ? false : true;
		}

		$output['mail']['recipients'] = isset( $input['mail']['recipients'] ) ? $input['mail']['recipients'] : array();

		$output = apply_filters( 'wp_auto_updater_notification/validate_options', $output, $input, $this->default_options );

		return $output;
	}

}
