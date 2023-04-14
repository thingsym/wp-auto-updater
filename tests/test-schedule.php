<?php
/**
 * Class Test_Wp_Auto_Updater_Schedule
 *
 * @package Test_Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Schedule extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group schedule
	 */
	public function cron_interval() {
		$schedules = $this->wp_auto_updater->add_cron_interval( array() );

		$expected = array(
			'weekly'  => array(
				'interval' => 7 * DAY_IN_SECONDS,
				'display'  => esc_html__( 'Once Weekly', 'wp-auto-updater' ),
			),
			'monthly' => array(
				'interval' => 30 * DAY_IN_SECONDS,
				'display'  => esc_html__( 'Once Monthly', 'wp-auto-updater' ),
			),
		);

		$this->assertSame( $expected, $schedules );
	}

	/**
	 * @test
	 * @group schedule
	 */
	public function set_schedule() {
		$schedule = array(
			'interval' => 'twicedaily',
			'day'      => 1,
			'weekday'  => 'monday',
			'hour'     => 6,
			'minute'   => 0,
		);

		$this->wp_auto_updater->set_schedule( $schedule );

		$update_wp_scheduled      = wp_next_scheduled( 'wp_version_check' );
		$update_themes_scheduled  = wp_next_scheduled( 'wp_update_themes' );
		$update_plugins_scheduled = wp_next_scheduled( 'wp_update_plugins' );

		$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );

		$this->assertSame( $timestamp, $update_wp_scheduled );
		$this->assertSame( $timestamp, $update_themes_scheduled );
		$this->assertSame( $timestamp, $update_plugins_scheduled );

		$schedule = array(
			'interval' => '',
			'day'      => 1,
			'weekday'  => 'monday',
			'hour'     => 6,
			'minute'   => 0,
		);

		$result = $this->wp_auto_updater->set_schedule( $schedule );
		$this->assertNull( $result );

		$result = $this->wp_auto_updater->set_schedule();
		$this->assertNull( $result );
	}

	/**
	 * @test
	 * @group schedule
	 */
	public function timestamp() {
		$schedule = array(
			'interval' => 'twicedaily',
			'day'      => 1,
			'weekday'  => 'monday',
			'hour'     => 6,
			'minute'   => 0,
		);

		$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
		$this->assertGreaterThan( time(), $timestamp );

		foreach ( range( 0, 23 ) as $hour ) {
			$schedule = array(
				'interval' => 'twicedaily',
				'day'      => 1,
				'weekday'  => 'monday',
				'hour'     => $hour,
				'minute'   => 0,
			);

			$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
			$this->assertGreaterThan( time(), $timestamp );
		}

		$schedule = array(
			'interval' => 'daily',
			'day'      => 1,
			'weekday'  => 'monday',
			'hour'     => 6,
			'minute'   => 0,
		);

		$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
		$this->assertGreaterThan( time(), $timestamp );

		foreach ( range( 0, 23 ) as $hour ) {
			$schedule = array(
				'interval' => 'daily',
				'day'      => 1,
				'weekday'  => 'monday',
				'hour'     => $hour,
				'minute'   => 0,
			);

			$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
			$this->assertGreaterThan( time(), $timestamp );
		}

		$schedule = array(
			'interval' => 'weekly',
			'day'      => 1,
			'weekday'  => 'monday',
			'hour'     => 6,
			'minute'   => 0,
		);

		$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
		$this->assertGreaterThan( time(), $timestamp );

		$schedule_weekdays = array(
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
			'sunday',
		);

		foreach ( $schedule_weekdays as $key ) {
			$schedule = array(
				'interval' => 'weekly',
				'day'      => 1,
				'weekday'  => $key,
				'hour'     => 6,
				'minute'   => 0,
			);

			$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
			$this->assertGreaterThan( time(), $timestamp );
		}

		$schedule = array(
			'interval' => 'monthly',
			'day'      => 1,
			'weekday'  => 'monday',
			'hour'     => 6,
			'minute'   => 0,
		);

		$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
		$this->assertGreaterThan( time(), $timestamp );

		foreach ( range( 1, 31 ) as $day ) {
			$schedule = array(
				'interval' => 'monthly',
				'day'      => $day,
				'weekday'  => 'monday',
				'hour'     => 6,
				'minute'   => 0,
			);

			$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
			$this->assertGreaterThan( time(), $timestamp );
		}

		$schedule = array(
			'interval' => 'monthly',
			'day'      => 'last_day',
			'weekday'  => 'monday',
			'hour'     => 6,
			'minute'   => 0,
		);

		$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
		$this->assertGreaterThan( time(), $timestamp );
	}

	/**
	 * @test
	 * @group schedule
	 */
	public function timestamp_timezone() {
		$timezone = array(
			'America/New_York',
			'Asia/Tokyo',
			'Europe/London',
			'UTC',
		);

		foreach ( $timezone as $zone ) {
			update_option( 'timezone_string', $zone );

			foreach ( range( 0, 23 ) as $hour ) {
				$schedule = array(
					'interval' => 'twicedaily',
					'day'      => 1,
					'weekday'  => 'monday',
					'hour'     => $hour,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );

				$diff = time() - $timestamp;
				$date = getdate( $timestamp );
				$message = $zone . ' | ' . $hour . ' | ' . $diff . ' | ' . $date['hours'];

				$this->assertGreaterThan( time(), $timestamp, $message );
			}

			foreach ( range( 0, 23 ) as $hour ) {
				$schedule = array(
					'interval' => 'daily',
					'day'      => 1,
					'weekday'  => 'monday',
					'hour'     => $hour,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );

				$diff = time() - (int) $timestamp;
				$date = getdate( $timestamp );
				$message = get_option( 'gmt_offset' ). ' | ' . $zone . ' | ' . $hour . ' | ' . $diff . ' | ' . $date['hours'];

				$this->assertGreaterThan( time(), $timestamp, $message );
			}

			$schedule_weekdays = array(
				'monday',
				'tuesday',
				'wednesday',
				'thursday',
				'friday',
				'saturday',
				'sunday',
			);

			foreach ( $schedule_weekdays as $key ) {
				$schedule = array(
					'interval' => 'weekly',
					'day'      => 1,
					'weekday'  => $key,
					'hour'     => 6,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
				$this->assertGreaterThan( time(), $timestamp );
			}

			foreach ( range( 1, 31 ) as $day ) {
				$schedule = array(
					'interval' => 'monthly',
					'day'      => $day,
					'weekday'  => 'monday',
					'hour'     => 6,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
				$this->assertGreaterThan( time(), $timestamp );
			}

			$schedule = array(
				'interval' => 'monthly',
				'day'      => 'last_day',
				'weekday'  => 'monday',
				'hour'     => 6,
				'minute'   => 0,
			);

			$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
			$this->assertGreaterThan( time(), $timestamp );
		}
	}

	/**
	 * @test
	 * @group schedule
	 */
	public function timestamp_timeoffset() {
		$timeoffset = array(
			'UTC-12',
			'UTC+0',
			'UTC+2.5',
			'UTC+9',
			'UTC+14',
		);

		foreach ( $timeoffset as $offset ) {
			update_option( 'timezone_string', $offset );

			foreach ( range( 0, 23 ) as $hour ) {
				$schedule = array(
					'interval' => 'twicedaily',
					'day'      => 1,
					'weekday'  => 'monday',
					'hour'     => $hour,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );

				$diff = time() - $timestamp;
				$date = getdate( $timestamp );
				$message = $offset . ' | ' . $hour . ' | ' . $diff . ' | ' . $date['mday'] . '/' . $date['hours'];

				$this->assertGreaterThan( time(), $timestamp, $message );
			}

			foreach ( range( 0, 23 ) as $hour ) {
				$schedule = array(
					'interval' => 'daily',
					'day'      => 1,
					'weekday'  => 'monday',
					'hour'     => $hour,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
				$this->assertGreaterThan( time(), $timestamp );
			}

			$schedule_weekdays = array(
				'monday',
				'tuesday',
				'wednesday',
				'thursday',
				'friday',
				'saturday',
				'sunday',
			);

			foreach ( $schedule_weekdays as $key ) {
				$schedule = array(
					'interval' => 'weekly',
					'day'      => 1,
					'weekday'  => $key,
					'hour'     => 6,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
				$this->assertGreaterThan( time(), $timestamp );
			}

			foreach ( range( 1, 31 ) as $day ) {
				$schedule = array(
					'interval' => 'monthly',
					'day'      => $day,
					'weekday'  => 'monday',
					'hour'     => 6,
					'minute'   => 0,
				);

				$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
				$this->assertGreaterThan( time(), $timestamp );
			}

			$schedule = array(
				'interval' => 'monthly',
				'day'      => 'last_day',
				'weekday'  => 'monday',
				'hour'     => 6,
				'minute'   => 0,
			);

			$timestamp = $this->wp_auto_updater->get_timestamp( $schedule );
			$this->assertGreaterThan( time(), $timestamp );
		}
	}

	/**
	 * @test
	 * @group schedule
	 */
	public function clear_schedule() {
		$this->wp_auto_updater->clear_schedule();

		$this->assertFalse( wp_next_scheduled( 'wp_version_check' ) );
		$this->assertFalse( wp_next_scheduled( 'wp_update_themes' ) );
		$this->assertFalse( wp_next_scheduled( 'wp_update_plugins' ) );
	}

}
