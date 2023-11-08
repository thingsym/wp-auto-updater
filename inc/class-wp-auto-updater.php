<?php
/**
 * WP_Auto_Updater class
 *
 * @package WP_Auto_Updater
 *
 * @since 1.0.0
 */

/**
 * Core class WP_Auto_Updater
 *
 * @since 1.0.0
 */
class WP_Auto_Updater {

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string $option_group   The group name of option
	 */
	public $option_group = 'wp_auto_updater';

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string $option_name   The option name
	 */
	public $option_name = 'wp_auto_updater_options';

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string $capability   The types of capability
	 */
	public $capability = 'update_core';

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var array $default_options {
	 *   default options
	 *
	 *   @type string core    minor|major|minor-only|pre-version|null
	 *   @type bool   theme
	 *   @type bool   plugin
	 *   @type bool   translation
	 *   @type array  disable_auto_update {
	 *       @type array themes
	 *       @type array plugins
	 *   }
	 *   @type array  schedule {
	 *       @type string      interval
	 *       @type int|string  day
	 *       @type string      weekday
	 *       @type int         hour
	 *       @type int         minute
	 *   }
	 * }
	 */
	public $default_options = array(
		'core'                => 'minor',
		'theme'               => false,
		'plugin'              => false,
		'translation'         => true,
		'disable_auto_update' => array(
			'themes'  => array(),
			'plugins' => array(),
		),
		'schedule'            => array(
			'interval' => 'twicedaily',
			'day'      => 1,
			'weekday'  => 'monday',
			'hour'     => 4,
			'minute'   => 0,
		),
	);

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var array|null $upgraded_version {
	 *   @type string core
	 *   @type array  theme
	 *   @type array  plugin
	 * }
	 */
	public $upgraded_version = null;

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var object|null $update_history   update_history object
	 */
	public $update_history = null;

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var object|null $notification   notification object
	 */
	public $notification = null;

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var array|null $plugin_data
	 */
	public $plugin_data = array();

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'wp_loaded', array( $this, 'auto_update' ) );

		add_action( 'plugins_loaded', [ $this, 'load_plugin_data' ] );

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_option_page' ) );

		add_action( 'wp_auto_updater/set_cron', array( $this, 'set_schedule' ) );
		add_action( 'wp_auto_updater/clear_schedule', array( $this, 'clear_schedule' ) );

		if ( class_exists( 'WP_Auto_Updater_History' ) ) {
			$this->update_history = new WP_Auto_Updater_History();
			add_action( 'automatic_updates_complete', array( $this, 'auto_update_result' ) );
		}

		if ( class_exists( 'WP_Auto_Updater_Notification' ) ) {
			$this->notification = new WP_Auto_Updater_Notification();
		}

		register_activation_hook( __WP_AUTO_UPDATER__, array( $this, 'activate' ) );
		register_deactivation_hook( __WP_AUTO_UPDATER__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __WP_AUTO_UPDATER__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Initialize.
	 *
	 * Hooks to plugins_loaded
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'pre_auto_update', array( $this, 'gather_upgraded_version' ) );

		add_filter( 'option_page_capability_' . $this->option_group, array( $this, 'option_page_capability' ) );

		add_filter( 'plugin_row_meta', array( $this, 'plugin_metadata_links' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this, 'plugin_action_links' ) );

		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );

		// Disable auto-update UI elements.
		add_filter( 'plugins_auto_update_enabled', '__return_false' );
		add_filter( 'themes_auto_update_enabled', '__return_false' );
		add_action( 'after_core_auto_updates_settings', array( $this, 'hidden_auto_update_status' ) );
	}

	/**
	 * Load plugin data
	 *
	 * @access public
	 *
	 * @return bool
	 *
	 * @since 1.6.1
	 */
	public function load_plugin_data() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$this->plugin_data = get_plugin_data( __WP_AUTO_UPDATER__ );

		if ( ! $this->plugin_data ) {
			return false;
		}

		return true;
	}

	/**
	 * Plugin activate.
	 *
	 * Hooks to activation_hook
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		$option = $this->get_options( 'schedule' );
		do_action( 'wp_auto_updater/set_cron', $option );

		// Set auto_update_core_major to disable.
		update_site_option( 'auto_update_core_major' , 'disable' );
	}

	/**
	 * Plugin deactivate.
	 *
	 * Hooks to deactivation_hook
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		do_action( 'wp_auto_updater/clear_schedule' );
	}

	/**
	 * Auto Updates.
	 *
	 * Hooks to wp_loaded
	 *
	 * @access public
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function auto_update() {
		if ( is_multisite() && ! is_main_site() ) {
			return false;
		}

		if ( ! class_exists( 'WP_Automatic_Updater' ) ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$updater = new WP_Automatic_Updater();

		if ( $updater->is_disabled() ) {
			return false;
		}

		do_action( 'wp_auto_updater/before_auto_update' );

		$this->auto_update_wordpress_core();
		$this->auto_update_theme();
		$this->auto_update_plugin();
		$this->auto_update_translation();

		do_action( 'wp_auto_updater/after_auto_update' );

		return true;
	}

	/**
	 * Gather present version of core/themes/plugins
	 *
	 * Hooks to pre_auto_update
	 *
	 * @access public
	 *
	 * @since 1.0.2
	 */
	public function gather_upgraded_version() {
		$this->upgraded_version = get_site_transient( 'wp_auto_updater/upgraded_version' );

		if ( false === $this->upgraded_version ) {
			global $wp_version;
			/* @phpstan-ignore-next-line */
			$this->upgraded_version['core']   = $wp_version;
			$this->upgraded_version['theme']  = wp_get_themes();
			$this->upgraded_version['plugin'] = get_plugins();

			set_site_transient( 'wp_auto_updater/upgraded_version', $this->upgraded_version, 5 * MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Gets update results.
	 *
	 * Logging update results
	 *
	 * @access public
	 *
	 * @param array $update_results
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function auto_update_result( $update_results = null ) {
		if ( empty( $update_results ) ) {
			return;
		}

		$date = current_time( 'mysql' );

		foreach ( $update_results as $type => $items ) {
			$info_success = array();
			$info_failed  = array();

			foreach ( $items as $update ) {
				$new_version  = isset( $update->item->new_version ) ? ' v' . $update->item->new_version : '';
				$from_version = '';

				if ( 'core' === $type ) {
					$from_version = isset( $this->upgraded_version['core'] ) ? ' (upgraded from v' . $this->upgraded_version['core'] . ')' : '';
				}
				elseif ( 'theme' === $type ) {
					$from_version = ' (upgraded from v' . $this->upgraded_version['theme'][ $update->item->theme ]->get( 'Version' ) . ')';
				}
				elseif ( 'plugin' === $type ) {
					$from_version = isset( $this->upgraded_version['plugin'][ $update->item->plugin ]['Version'] ) ? ' (upgraded from v' . $this->upgraded_version['plugin'][ $update->item->plugin ]['Version'] . ')' : '';
				}

				if ( $update->result ) {
					$info_success[] = $update->name . $new_version . $from_version;
				}
				else {
					$info_failed[] = $update->name . $new_version;
				}
			}

			if ( ! empty( $info_success ) ) {
				$this->update_history->logging( $date, 'success', 'auto-update', $type, implode( "\n", $info_success ) );
			}
			if ( ! empty( $info_failed ) ) {
				$this->update_history->logging( $date, 'failed', 'auto-update', $type, implode( "\n", $info_failed ) );
			}

			$this->notification->send_email( $type, $info_success, $info_failed );
		}

	}

	/**
	 * Add schedules to Cron.
	 *
	 * Hooks to cron_schedules.
	 *
	 * @access public
	 *
	 * @param array $schedules
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function add_cron_interval( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => 7 * DAY_IN_SECONDS,
			'display'  => esc_html__( 'Once Weekly', 'wp-auto-updater' ),
		);

		$schedules['monthly'] = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => esc_html__( 'Once Monthly', 'wp-auto-updater' ),
		);

		return apply_filters( 'wp_auto_updater/add_cron_interval', $schedules );
	}

	/**
	 * Set schedule.
	 *
	 * @access public
	 *
	 * @param array $schedule
	 *
	 * @return void
	 */
	public function set_schedule( $schedule = null ) {
		if ( ! isset( $schedule['interval'] ) ) {
			return;
		}

		$timestamp = $this->get_timestamp( $schedule );

		if ( $timestamp ) {
			wp_unschedule_event( wp_next_scheduled( 'wp_version_check' ), 'wp_version_check' );
			wp_unschedule_event( wp_next_scheduled( 'wp_update_themes' ), 'wp_update_themes' );
			wp_unschedule_event( wp_next_scheduled( 'wp_update_plugins' ), 'wp_update_plugins' );

			if ( ! wp_next_scheduled( 'wp_version_check' ) ) {
				wp_schedule_event( $timestamp, $schedule['interval'], 'wp_version_check' );
			}
			if ( ! wp_next_scheduled( 'wp_update_themes' ) ) {
				wp_schedule_event( $timestamp, $schedule['interval'], 'wp_update_themes' );
			}
			if ( ! wp_next_scheduled( 'wp_update_plugins' ) ) {
				wp_schedule_event( $timestamp, $schedule['interval'], 'wp_update_plugins' );
			}
		}
	}

	/**
	 * Returns timestamp.
	 *
	 * @access public
	 *
	 * @param array $schedule
	 *
	 * @return int
	 */
	public function get_timestamp( $schedule = null ) {
		$timestamp      = 0;
		$gmt_offset_sec = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		$current_time   = time();

		if ( 'twicedaily' === $schedule['interval'] ) {
			$diff_time_sec = $schedule['hour'] * HOUR_IN_SECONDS + $schedule['minute'] * MINUTE_IN_SECONDS;
			$timestamp     = strtotime( 'today 00:00:00' ) + $diff_time_sec - $gmt_offset_sec;

			if ( $current_time > $timestamp ) {
				$timestamp = strtotime( 'today 12:00:00' ) + $diff_time_sec - $gmt_offset_sec;
			}

			if ( $current_time > $timestamp ) {
				if ( 43200 > $current_time - $timestamp ) {
					$timestamp = strtotime( '+1 day 00:00:00' ) + $diff_time_sec - $gmt_offset_sec;
				}
				else {
					$timestamp = strtotime( '+1 day 12:00:00' ) + $diff_time_sec - $gmt_offset_sec;
				}
			}
		}
		elseif ( 'daily' === $schedule['interval'] ) {
			$diff_time_sec = $schedule['hour'] * HOUR_IN_SECONDS + $schedule['minute'] * MINUTE_IN_SECONDS;
			$timestamp     = strtotime( 'today 00:00:00' ) + $diff_time_sec - $gmt_offset_sec;

			if ( $current_time > $timestamp ) {
				if ( 86400 > $current_time - $timestamp ) {
					$timestamp = strtotime( '+1 day 00:00:00' ) + $diff_time_sec - $gmt_offset_sec;
				}
				else {
					$timestamp = strtotime( '+2 day 00:00:00' ) + $diff_time_sec - $gmt_offset_sec;
				}
			}
		}
		elseif ( 'weekly' === $schedule['interval'] ) {
			$diff_time_sec = $schedule['hour'] * HOUR_IN_SECONDS + $schedule['minute'] * MINUTE_IN_SECONDS;
			$timestamp     = strtotime( "this {$schedule['weekday']} 00:00:00" ) + $diff_time_sec - $gmt_offset_sec;

			if ( $current_time > $timestamp ) {
				if ( 604800 > $current_time - $timestamp ) {
					$timestamp = strtotime( "+1 weeks {$schedule['weekday']} 00:00:00" ) + $diff_time_sec - $gmt_offset_sec;
				}
				else {
					$timestamp = strtotime( "+2 weeks {$schedule['weekday']} 00:00:00" ) + $diff_time_sec - $gmt_offset_sec;
				}
			}
		}
		elseif ( 'monthly' === $schedule['interval'] ) {
			$diff_last_day_sec = 0;

			if ( 'last_day' === $schedule['day'] ) {
				$schedule['day'] = 31;
			}

			if ( 28 <= $schedule['day'] ) {
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$last_day      = intval( date( 't', strtotime( 'first day of this month 00:00:00' ) ) );
				$diff_last_day = $schedule['day'] - $last_day;
				if ( 0 < $diff_last_day ) {
					$diff_last_day_sec = $diff_last_day * DAY_IN_SECONDS;
				}
			}

			$diff_time_sec = ( $schedule['day'] - 1 ) * DAY_IN_SECONDS + $schedule['hour'] * HOUR_IN_SECONDS + $schedule['minute'] * MINUTE_IN_SECONDS;
			$timestamp     = strtotime( 'first day of this month 00:00:00' ) + $diff_time_sec - $gmt_offset_sec - $diff_last_day_sec;

			if ( $current_time > $timestamp ) {
				if ( 28 <= $schedule['day'] ) {
					// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					$last_day      = intval( date( 't', strtotime( 'first day of next month 00:00:00' ) ) );
					$diff_last_day = $schedule['day'] - $last_day;
					if ( 0 < $diff_last_day ) {
						$diff_last_day_sec = $diff_last_day * DAY_IN_SECONDS;
					}
				}

				$timestamp = strtotime( 'first day of next month 00:00:00' ) + $diff_time_sec - $gmt_offset_sec - $diff_last_day_sec;
			}
		}

		return apply_filters( 'wp_auto_updater/get_timestamp', $timestamp, $schedule );
	}

	/**
	 * Clear schedule.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function clear_schedule() {
		wp_clear_scheduled_hook( 'wp_update_plugins' );
		wp_clear_scheduled_hook( 'wp_update_themes' );
		wp_clear_scheduled_hook( 'wp_version_check' );
	}

	/**
	 * Auto update WordPress core.
	 *
	 * @access public
	 *
	 * @return void|bool
	 *
	 * @since 1.0.0
	 */
	public function auto_update_wordpress_core() {
		$option = $this->get_options( 'core' );

		if ( ! $option ) {
			return false;
		}

		$update_core = get_site_transient( 'update_core' );

		if ( ! $update_core || empty( $update_core->updates ) ) {
			return false;
		}

		foreach ( $update_core->updates as $update ) {
			if ( 'autoupdate' === $update->response ) {
				$auto_update_info = $update;
				break;
			}
		}

		if ( empty( $auto_update_info ) ) {
			return false;
		}

		global $wp_version;
		$old_core_version = $wp_version;
		$new_core_version = $auto_update_info->current;

		$old_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $old_core_version ), 0, 2 ) );
		$new_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $new_core_version ), 0, 2 ) );

		do_action( 'wp_auto_updater/before_auto_update/wordpress_core' );

		if ( 'minor' === $option ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
			// default, Nothing to do.
		}
		elseif ( 'major' === $option ) {
			add_filter( 'allow_major_auto_core_updates', '__return_true' );
		}
		elseif ( 'minor-only' === $option ) {
			if ( version_compare( $new_core_version_xy, $old_core_version_xy, '>' ) ) {
				$version_z = implode( '.', array_slice( preg_split( '/[.-]/', $new_core_version ), 2, 1 ) );

				if ( ! empty( $version_z ) ) {
					add_filter( 'allow_major_auto_core_updates', '__return_true' );
				}
			}
		}
		elseif ( 'pre-version' === $option ) {
			/**
			 * See THE FLOATING-POINT GUIDE
			 * https://floating-point-gui.de/
			 */
			$version_diff = floatval( $new_core_version_xy ) - floatval( $old_core_version_xy );
			$float_diff   = abs( $version_diff - 0.2 );
			$epsilon      = 0.00001;

			if ( $float_diff < $epsilon ) {
				add_filter( 'allow_major_auto_core_updates', '__return_true' );
				add_filter( 'pre_site_option_update_core', array( $this, 'updates_previous_version' ) );
				add_filter( 'site_transient_update_core', array( $this, 'updates_previous_version' ) );
			}
		}
		elseif ( 'disable-auto-update' === $option ) {
			add_filter( 'auto_update_core', '__return_false' );
		}

		do_action( 'wp_auto_updater/after_auto_update/wordpress_core' );
	}

	/**
	 * Trim current version update.
	 *
	 * @access public
	 *
	 * @param object $updates
	 *
	 * @return object|null
	 *
	 * @since 1.0.0
	 */
	public function updates_previous_version( $updates ) {

		if ( ! is_object( $updates ) ) {
			return null;
		}

		foreach ( $updates->updates as $key => $update ) {
			if ( 'autoupdate' === $update->response ) {
				array_splice( $updates->updates, $key, 1 );
				break;
			}
		}

		return $updates;
	}

	/**
	 * Auto update theme.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function auto_update_theme() {
		$option = $this->get_options( 'theme' );

		if ( $option ) {
			do_action( 'wp_auto_updater/before_auto_update/theme' );
			add_filter( 'auto_update_theme', array( $this, 'auto_update_specific_theme' ), 10, 2 );
			do_action( 'wp_auto_updater/after_auto_update/theme' );
		}
	}

	/**
	 * Check auto update specific theme.
	 *
	 * @access public
	 *
	 * @param bool $update
	 * @param bool $item
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function auto_update_specific_theme( $update, $item ) {
		$option = $this->get_options( 'disable_auto_update' );

		/* @phpstan-ignore-next-line */
		if ( ! empty( $item->theme ) && in_array( $item->theme, $option['themes'], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Auto update plugin.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function auto_update_plugin() {
		$option = $this->get_options( 'plugin' );

		if ( $option ) {
			do_action( 'wp_auto_updater/before_auto_update/plugin' );
			add_filter( 'auto_update_plugin', array( $this, 'auto_update_specific_plugin' ), 10, 2 );
			do_action( 'wp_auto_updater/after_auto_update/plugin' );
		}
	}

	/**
	 * Check auto update specific plugin.
	 *
	 * @access public
	 *
	 * @param bool $update
	 * @param bool $item
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function auto_update_specific_plugin( $update, $item ) {
		$option = $this->get_options( 'disable_auto_update' );

		/* @phpstan-ignore-next-line */
		if ( ! empty( $item->plugin ) && in_array( $item->plugin, $option['plugins'], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Auto update translation.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function auto_update_translation() {
		$option = $this->get_options( 'translation' );

		if ( ! $option ) {
			do_action( 'wp_auto_updater/before_auto_update/translation' );
			add_filter( 'auto_update_translation', '__return_false' );
			do_action( 'wp_auto_updater/after_auto_update/translation' );
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
	 * @since 1.0
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
			'version',
			__( 'WordPress Version', 'wp-auto-updater' ),
			array( $this, 'settings_section_cb_nothing' ),
			'wp_auto_updater'
		);

		add_settings_field(
			'current_wp_version',
			__( 'Current Version', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_current_wp_version' ),
			'wp_auto_updater',
			'version'
		);

		add_settings_field(
			'newer_wp_version',
			__( 'Newer Version', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_newer_wp_version' ),
			'wp_auto_updater',
			'version'
		);

		add_settings_section(
			'scenario',
			__( 'Auto Update Scenario', 'wp-auto-updater' ),
			array( $this, 'settings_section_cb_nothing' ),
			'wp_auto_updater'
		);

		add_settings_field(
			'core',
			__( 'WordPress Core', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_scenario_core' ),
			'wp_auto_updater',
			'scenario'
		);

		add_settings_field(
			'theme',
			__( 'Theme', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_scenario_theme' ),
			'wp_auto_updater',
			'scenario'
		);

		add_settings_field(
			'plugin',
			__( 'Plugin', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_scenario_plugin' ),
			'wp_auto_updater',
			'scenario'
		);

		add_settings_field(
			'translation',
			__( 'Translation', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_scenario_translation' ),
			'wp_auto_updater',
			'scenario'
		);

		add_settings_section(
			'schedule',
			__( 'Schedule', 'wp-auto-updater' ),
			array( $this, 'settings_section_cb_nothing' ),
			'wp_auto_updater'
		);

		add_settings_field(
			'next_schedule',
			__( 'Next Update Date', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_schedule_next_updete_date' ),
			'wp_auto_updater',
			'schedule'
		);

		add_settings_field(
			'interval',
			__( 'Update Interval', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_schedule_interval' ),
			'wp_auto_updater',
			'schedule'
		);

		add_settings_field(
			'date',
			__( 'Update Date', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_schedule_date' ),
			'wp_auto_updater',
			'schedule'
		);

		add_settings_section(
			'themes',
			__( 'Disable Auto Update Themes', 'wp-auto-updater' ),
			array( $this, 'settings_section_cb_themes' ),
			'wp_auto_updater'
		);

		add_settings_field(
			'themes',
			__( 'Themes', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_scenario_themes' ),
			'wp_auto_updater',
			'themes'
		);

		add_settings_section(
			'plugins',
			__( 'Disable Auto Update Plugins', 'wp-auto-updater' ),
			array( $this, 'settings_section_cb_plugins' ),
			'wp_auto_updater'
		);

		add_settings_field(
			'plugins',
			__( 'Plugins', 'wp-auto-updater' ),
			array( $this, 'settings_field_cb_scenario_plugins' ),
			'wp_auto_updater',
			'plugins'
		);

	}

	/**
	 * Returns capability.
	 *
	 * @access public
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function option_page_capability() {
		return $this->capability;
	}

	/**
	 * Adds option page.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function add_option_page() {
		$page_hook = add_dashboard_page(
			__( 'Auto Updater', 'wp-auto-updater' ),
			__( 'Auto Updater', 'wp-auto-updater' ),
			$this->option_page_capability(),
			'wp-auto-updater',
			array( $this, 'render_option_page' )
		);

		if ( empty( $page_hook ) ) {
			return;
		}

		add_action( 'load-' . $page_hook, array( $this, 'page_hook_suffix' ) );
	}

	/**
	 * Page Hook Suffix.
	 *
	 * Hooks to load-{$page_hook}.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function page_hook_suffix() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		if ( class_exists( 'WP_Automatic_Updater' ) ) {
			$updater = new WP_Automatic_Updater();
			if ( $updater->is_disabled() ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_upgrader_disabled' ) );
			}
		}
	}

	/**
	 * Returns the options array or value.
	 *
	 * @access public
	 *
	 * @param string $option_name Optional. The option name.
	 *
	 * @return string|int|bool|array|null
	 *
	 * @since 1.0.0
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
			 * @since 1.0.0
			 */
			return apply_filters( 'wp_auto_updater/get_options', $options );
		}

		if ( array_key_exists( $option_name, $options ) ) {
			/**
			 * Filters the option.
			 *
			 * @param mixed   $option           The value of option.
			 * @param string   $option_name      The option name via argument.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'wp_auto_updater/get_option', $options[ $option_name ], $option_name );
		}
		else {
			return null;
		}
	}

	/**
	 * Load textdomain
	 *
	 * @access public
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		return load_plugin_textdomain(
			'wp-auto-updater',
			false,
			plugin_dir_path( __WP_AUTO_UPDATER__ ) . 'languages'
		);
	}

	/**
	 * Display option page.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render_option_page() {
		?>
<div class="wrap">
<h2><?php esc_html_e( 'WP Auto Updater', 'wp-auto-updater' ); ?></h2>
		<?php settings_errors(); ?>

<form method="post" action="options.php">
		<?php
		settings_fields( $this->option_group );
		do_settings_sections( $this->option_group );
		submit_button();
		?>
</form>
</div>
		<?php
	}

	/**
	 * Get schedule interval variable.
	 *
	 * @access public
	 *
	 * @return array
	 *
	 * @since 1.4.0
	 */
	public function get_schedule_interval() {
		$schedule_interval = array(
			'twicedaily' => __( 'Twice Daily (12 hours interval)', 'wp-auto-updater' ),
			'daily'      => __( 'Daily', 'wp-auto-updater' ),
			'weekly'     => __( 'Weekly', 'wp-auto-updater' ),
			'monthly'    => __( 'Monthly', 'wp-auto-updater' ),
		);

		return $schedule_interval;
	}

	/**
	 * Callback function for nothing settings_section
	 *
	 * Display nothing.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_section_cb_nothing() {}

	/**
	 * Callback function for settings_field 'newer_wp_version'
	 *
	 * Display newer WordPress version.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_newer_wp_version() {
		$updates = get_core_updates();
		if ( isset( $updates[0]->response ) ) {
			/* @phpstan-ignore-next-line */
			echo esc_html( $updates[0]->version );
		}
	}

	/**
	 * Callback function for settings_field 'current_wp_version'
	 *
	 * Display current WordPress version.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_current_wp_version() {
		global $wp_version;
		echo esc_html( $wp_version );
	}

	/**
	 * Callback function for settings_field 'scenario_core'
	 *
	 * Get the settings option array and print one of its values
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_scenario_core() {
		$option = $this->get_options( 'core' );
		?>
<select name="wp_auto_updater_options[core]">
<option value="minor"<?php selected( 'minor', $option ); ?>><?php esc_html_e( 'Minor Version Update', 'wp-auto-updater' ); ?></option>
<option value="major"<?php selected( 'major', $option ); ?>><?php esc_html_e( 'Major Version Update', 'wp-auto-updater' ); ?></option>
<option value="minor-only"<?php selected( 'minor-only', $option ); ?>><?php esc_html_e( 'Minor Only Version Update', 'wp-auto-updater' ); ?></option>
<option value="pre-version"<?php selected( 'pre-version', $option ); ?>><?php esc_html_e( 'Previous Generation Version Update', 'wp-auto-updater' ); ?></option>
<option value="disable-auto-update"<?php selected( 'disable-auto-update', $option ); ?>><?php esc_html_e( 'Manual Update', 'wp-auto-updater' ); ?></option>
</select>

<p><span class="dashicons dashicons-info"></span><a href="<?php echo esc_url( plugins_url( 'screenshot-3.png', __WP_AUTO_UPDATER__ ) ); ?>" target="_blank"><?php esc_html_e( 'See WordPress Update Process Chart', 'wp-auto-updater' ); ?></a></p>
		<?php
	}

	/**
	 * Callback function for settings_field 'theme'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_scenario_theme() {
		$option = $this->get_options( 'theme' );
		?>
<select name="wp_auto_updater_options[theme]">
<option value="1"<?php selected( true, $option ); ?>><?php esc_html_e( 'Auto Update', 'wp-auto-updater' ); ?></option>
<option value="0"<?php selected( false, $option ); ?>><?php esc_html_e( 'Manual Update', 'wp-auto-updater' ); ?></option>
</select>
		<?php
	}

	/**
	 * Callback function for settings_field 'plugin'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_scenario_plugin() {
		$option = $this->get_options( 'plugin' );
		?>
<select name="wp_auto_updater_options[plugin]">
<option value="1"<?php selected( true, $option ); ?>><?php esc_html_e( 'Auto Update', 'wp-auto-updater' ); ?></option>
<option value="0"<?php selected( false, $option ); ?>><?php esc_html_e( 'Manual Update', 'wp-auto-updater' ); ?></option>
</select>
		<?php
	}

	/**
	 * Callback function for settings_field 'translation'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_scenario_translation() {
		$option = $this->get_options( 'translation' );
		?>
<select name="wp_auto_updater_options[translation]">
<option value="1"<?php selected( true, $option ); ?>><?php esc_html_e( 'Auto Update', 'wp-auto-updater' ); ?></option>
<option value="0"<?php selected( false, $option ); ?>><?php esc_html_e( 'Manual Update', 'wp-auto-updater' ); ?></option>
</select>
		<?php
	}

	/**
	 * Callback function for settings_field 'next_schedule'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_schedule_next_updete_date() {
		$option           = $this->get_options( 'schedule' );
		$next_updete_date = wp_next_scheduled( 'wp_version_check' );
		if ( empty( $next_updete_date ) ) {
			return;
		}

		$gmt_offset_sec    = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		$schedule_interval = $this->get_schedule_interval();
		echo '<p>' . esc_html( $schedule_interval[ $option['interval'] ] ) . '</p>';
		?>
<p><?php echo esc_html( date_i18n( 'Y-m-d H:i:s', $next_updete_date + $gmt_offset_sec ) ); ?> (<?php esc_html_e( 'Local time', 'wp-auto-updater' ); ?> <?php echo wp_timezone_string(); ?>)</p>
<p><?php echo esc_html( date( 'Y-m-d H:i:s', $next_updete_date ) /* phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date */ ); ?> (<?php esc_html_e( 'GMT', 'wp-auto-updater' ); ?>)</p>
		<?php
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$current_time = new DateTime( date( 'Y-m-d H:i:s', time() ) );
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$datetime     = new DateTime( date( 'Y-m-d H:i:s', $next_updete_date ) );

		$diff = $current_time->diff( $datetime );

		if ( $next_updete_date != $this->get_timestamp( $option ) ) {
			echo '<p><span class="dashicons dashicons-warning"></span> ' . __( 'The cron schedule is out of sync with the set schedule. You may have changed the cron schedule or the timezone somewhere else.', 'wp-auto-updater' ) . '</p>';
		}

		$this->print_update_message( $diff );
	}

	/**
	 * Print the time to update.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.6.1
	 */
	public function print_update_message( $diff ) {
		if ( $diff->d ) {
			echo '<p><span class="dashicons dashicons-clock"></span> ';
			printf(
				/* translators: day: 1: day, 2: days */
				esc_html( _n( '%d day', '%d days', $diff->d, 'wp-auto-updater' ) ),
				/* @phpstan-ignore-next-line */
				esc_html( $diff->d )
			);
			if ( $diff->h ) {
				echo ' ';
				printf(
					/* translators: hour: 1: hour, 2: hours */
					esc_html( _n( '%d hour', '%d hours', $diff->h, 'wp-auto-updater' ) ),
					/* @phpstan-ignore-next-line */
					esc_html( $diff->h )
				);
			}
			if ( $diff->i ) {
				echo ' ';
				printf(
					/* translators: minute: 1: minute, 2: minutes */
					esc_html( _n( '%d minute', '%d minutes', $diff->i, 'wp-auto-updater' ) ),
					/* @phpstan-ignore-next-line */
					esc_html( $diff->i )
				);
			}
			echo ' ';
			$diff->invert ? esc_html_e( 'ago', 'wp-auto-updater' ) : esc_html_e( 'later', 'wp-auto-updater' );
			echo '</p>';
		}
		elseif ( $diff->h ) {
			echo '<p><span class="dashicons dashicons-clock"></span> ';
			printf(
				esc_html( _n( '%d hour', '%d hours', $diff->h, 'wp-auto-updater' ) ),
				/* @phpstan-ignore-next-line */
				esc_html( $diff->h )
			);
			if ( $diff->i ) {
				echo ' ';
				printf(
					esc_html( _n( '%d minute', '%d minutes', $diff->i, 'wp-auto-updater' ) ),
					/* @phpstan-ignore-next-line */
					esc_html( $diff->i )
				);
			}
			echo ' ';
			$diff->invert ? esc_html_e( 'ago', 'wp-auto-updater' ) : esc_html_e( 'later', 'wp-auto-updater' );
			echo '</p>';
		}
		elseif ( $diff->i ) {
			echo '<p><span class="dashicons dashicons-clock"></span> ';
			printf(
				esc_html( _n( '%d minute', '%d minutes', $diff->i, 'wp-auto-updater' ) ),
				/* @phpstan-ignore-next-line */
				esc_html( $diff->i )
			);
			echo ' ';
			$diff->invert ? esc_html_e( 'ago', 'wp-auto-updater' ) : esc_html_e( 'later', 'wp-auto-updater' );
			echo '</p>';
		}
	}

	/**
	 * Callback function for settings_field 'interval'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_schedule_interval() {
		$option            = $this->get_options( 'schedule' );
		$schedule_interval = $this->get_schedule_interval();
		foreach ( $schedule_interval as $key => $label ) {
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			echo '<p><label><input type="radio" name="wp_auto_updater_options[schedule][interval]" value="' . esc_attr( $key ) . '"' . checked( $key, $option['interval'], false ) . '> ' . esc_html( $label ) . '</label></p>';
		}
	}

	/**
	 * Callback function for settings_field 'date'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_schedule_date() {
		$option = $this->get_options( 'schedule' );
		?>

<p class="schedule_day"><?php esc_html_e( 'Day: ', 'wp-auto-updater' ); ?>
<select name="wp_auto_updater_options[schedule][day]">
		<?php
		foreach ( range( 1, 31 ) as $day ) {
			/* @phpstan-ignore-next-line */
			echo '<option value="' . esc_attr( $day ) . '"' . selected( $day, $option['day'], false ) . '>' . esc_html( $day ) . '</option>';
		}
		echo '<option value="last_day"' . selected( 'last_day', $option['day'], false ) . '>' . esc_html__( 'last day', 'wp-auto-updater' ) . '</option>';
		?>
</select></p>

<p class="schedule_weekday"><?php esc_html_e( 'Weekday: ', 'wp-auto-updater' ); ?>
<select name="wp_auto_updater_options[schedule][weekday]">
		<?php
		$schedule_weekdays = array(
			'monday'    => __( 'Monday', 'wp-auto-updater' ),
			'tuesday'   => __( 'Tuesday', 'wp-auto-updater' ),
			'wednesday' => __( 'Wednesday', 'wp-auto-updater' ),
			'thursday'  => __( 'Thursday', 'wp-auto-updater' ),
			'friday'    => __( 'Friday', 'wp-auto-updater' ),
			'saturday'  => __( 'Saturday', 'wp-auto-updater' ),
			'sunday'    => __( 'Sunday', 'wp-auto-updater' ),
		);

		foreach ( $schedule_weekdays as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $option['weekday'], false ) . '>' . esc_html( $label ) . '</option>';
		}
		?>
</select></p>

<p class="schedule_hour"><?php esc_html_e( 'Hour: ', 'wp-auto-updater' ); ?>
<select name="wp_auto_updater_options[schedule][hour]">
		<?php
		foreach ( range( 0, 23 ) as $hour ) {
			/* @phpstan-ignore-next-line */
			echo '<option value="' . esc_attr( $hour ) . '"' . selected( $hour, $option['hour'], false ) . '>' . esc_html( $hour ) . '</option>';
		}
		?>
</select></p>

<p class="schedule_minute"><?php esc_html_e( 'Minute: ', 'wp-auto-updater' ); ?>
<select name="wp_auto_updater_options[schedule][minute]">
		<?php
		foreach ( range( 0, 59, 5 ) as $minute ) {
			/* @phpstan-ignore-next-line */
			echo '<option value="' . esc_attr( $minute ) . '"' . selected( $minute, $option['minute'], false ) . '>' . esc_html( $minute ) . '</option>';
		}
		?>
</select></p>

		<?php
	}

	/**
	 * Callback function for settings_section 'themes'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_section_cb_themes() {
		esc_html_e( 'Select a theme that you do not want to automatically update.', 'wp-auto-updater' );
	}

	/**
	 * Callback function for settings_field 'themes'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_scenario_themes() {
		$option = $this->get_options( 'disable_auto_update' );
		$themes = wp_get_themes();

		printf(
			/* translators: installed: 1: count */
			__( '%d installed', 'wp-auto-updater' ), /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
			/* @phpstan-ignore-next-line */
			esc_html( count( $themes ) )
		);

		foreach ( $themes as $theme ) {
			?>
<p><label><input type="checkbox" name="wp_auto_updater_options[disable_auto_update][themes][]" value="<?php echo esc_attr( $theme->get_stylesheet() ); ?>"<?php checked( true, in_array( $theme->get_stylesheet(), $option['themes'], true ) ); ?>> <?php echo esc_html( $theme->get( 'Name' ) ); ?> v<?php echo esc_html( $theme->get( 'Version' ) ); ?></label></p>
			<?php
		}
	}

	/**
	 * Callback function for settings_section 'plugins'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_section_cb_plugins() {
		esc_html_e( 'Select a plugin that you do not want to automatically update.', 'wp-auto-updater' );
	}

	/**
	 * Callback function for settings_field 'plugins'
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function settings_field_cb_scenario_plugins() {
		$option  = $this->get_options( 'disable_auto_update' );
		$plugins = get_plugins();

		printf(
			/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
			__( '%d installed', 'wp-auto-updater' ),
			/* @phpstan-ignore-next-line */
			esc_html( count( $plugins ) )
		);

		foreach ( $plugins as $path => $plugin ) {
			?>
<p><label><input type="checkbox" name="wp_auto_updater_options[disable_auto_update][plugins][]" value="<?php echo esc_attr( $path ); ?>"<?php checked( true, in_array( $path, $option['plugins'], true ) ); ?>> <?php echo esc_html( $plugin['Name'] ); ?> v<?php echo esc_html( $plugin['Version'] ); ?></label></p>
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

		$output['core']        = empty( $input['core'] ) ? null : $input['core'];
		$output['theme']       = empty( $input['theme'] ) ? false : true;
		$output['plugin']      = empty( $input['plugin'] ) ? false : true;
		$output['translation'] = empty( $input['translation'] ) ? false : true;

		$output['disable_auto_update']['themes']  = isset( $input['disable_auto_update']['themes'] ) ? $input['disable_auto_update']['themes'] : array();
		$output['disable_auto_update']['plugins'] = isset( $input['disable_auto_update']['plugins'] ) ? $input['disable_auto_update']['plugins'] : array();

		$output['schedule']['interval'] = isset( $input['schedule']['interval'] ) ? $input['schedule']['interval'] : $this->default_options['schedule']['interval'];

		$output['schedule']['day'] = (int) $this->default_options['schedule']['day'];
		if ( isset( $input['schedule']['day'] ) ) {
			if ( $input['schedule']['day'] === 'last_day' ) {
				$output['schedule']['day'] = $input['schedule']['day'];
			}
			else {
				$output['schedule']['day'] = (int) $input['schedule']['day'];
			}
		}

		$output['schedule']['weekday'] = empty( $input['schedule']['weekday'] ) ? $this->default_options['schedule']['weekday'] : strtolower( $input['schedule']['weekday'] );

		$output['schedule']['hour']   = isset( $input['schedule']['hour'] ) ? (int) $input['schedule']['hour'] : (int) $this->default_options['schedule']['hour'];
		$output['schedule']['minute'] = isset( $input['schedule']['minute'] ) ? (int) $input['schedule']['minute'] : (int) $this->default_options['schedule']['minute'];

		$output = apply_filters( 'wp_auto_updater/validate_options', $output, $input, $this->default_options );

		if ( isset( $input['schedule'] ) ) {
			do_action( 'wp_auto_updater/set_cron', $input['schedule'] );
		}

		return $output;
	}

	/**
	 * Enqueue scripts.
	 *
	 * Hooks to admin_enqueue_scripts.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts( $hook_suffix = '' ) {
		wp_enqueue_script(
			'wp-auto-updater-admin',
			plugins_url( 'js/admin.js', __WP_AUTO_UPDATER__ ),
			array( 'jquery' ),
			$this->plugin_data['Version'],
			true
		);
	}

	/**
	 * Set links below a plugin on the Plugins page.
	 *
	 * Hooks to plugin_row_meta
	 *
	 * @see https://developer.wordpress.org/reference/hooks/plugin_row_meta/
	 *
	 * @access public
	 *
	 * @param array  $links  An array of the plugin's metadata.
	 * @param string $file   Path to the plugin file relative to the plugins directory.
	 *
	 * @return array $links
	 *
	 * @since 1.5.1
	 */
	public function plugin_metadata_links( $links, $file ) {
		if ( $file == plugin_basename( __WP_AUTO_UPDATER__ ) ) {
			$links[] = '<a href="https://github.com/sponsors/thingsym">' . __( 'Become a sponsor', 'wp-auto-updater' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Set link to customizer section on the plugins page.
	 *
	 * Hooks to plugin_action_links_{$plugin_file}
	 *
	 * @see https://developer.wordpress.org/reference/hooks/plugin_action_links_plugin_file/
	 *
	 * @access public
	 *
	 * @param array $links An array of plugin action links.
	 *
	 * @return array $links
	 *
	 * @since 1.0.0
	 */
	public function plugin_action_links( $links = array() ) {
		$settings_link = '<a href="index.php?page=wp-auto-updater">' . __( 'Settings', 'wp-auto-updater' ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Uninstall.
	 *
	 * Hooks to uninstall_hook
	 *
	 * @access public static
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		$wp_auto_updater = new WP_Auto_Updater();
		delete_option( $wp_auto_updater->option_name );
		do_action( 'wp_auto_updater/clear_schedule' );

		$wp_auto_updater_history = new WP_Auto_Updater_History();
		$wp_auto_updater_history->uninstall();
	}

	/**
	 * Hidden auto update status on the update-core screen
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.6.4
	 */
	public function hidden_auto_update_status( $auto_update_settings ) {
?>
<style>
.auto-update-status {
	display: none
}
</style><?php
	}

	/**
	 * Display notice.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function admin_notice_upgrader_disabled() {
		?>
<div class="notice notice-warning">
<p><?php esc_html_e( 'Automatic updating is not possible.', 'wp-auto-updater' ); ?></p>
</div>
		<?php
	}
}
