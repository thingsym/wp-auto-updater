<?php
/**
 * Class Test_Wp_Auto_Updater_Notification_Options
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Notification_Options extends WP_UnitTestCase {
	public $wp_auto_updater_notification;

	public function setUp(): void {
		parent::setUp();
		$this->wp_auto_updater_notification = new WP_Auto_Updater_Notification();
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_default() {
		$options  = $this->wp_auto_updater_notification->get_options();
		$expected = array(
			'notification' => array(
				'core'        => true,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);

		$this->assertSame( $expected, $options );
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_migrate() {
		$expected = array(
			'notification' => array(
				'core'        => true,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);

		$options = array();

		update_option( 'wp_auto_updater_notification_options', $options );

		$options = $this->wp_auto_updater_notification->get_options();
		$this->assertSame( $expected, $options );

		$options = array(
			'notification' => array(),
			'mail' => array(),
		);

		update_option( 'wp_auto_updater_notification_options', $options );

		$options = $this->wp_auto_updater_notification->get_options();
		$this->assertSame( $expected, $options );

		$options = array(
			'notification' => array(
				'core'        => true,
			),
			'mail' => array(
				'from'        => '',
			),
		);

		update_option( 'wp_auto_updater_notification_options', $options );

		$options = $this->wp_auto_updater_notification->get_options();
		$this->assertSame( $expected, $options );
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_case_1() {
		$options = array(
			'notification' => array(
				'core'        => false,
				'theme'       => true,
				'plugin'      => true,
				'translation' => true,
			),
			'mail' => array(
				'from'        => 'test@example.com',
				'admin_email' => false,
				'recipients'  => array(
					1,
					2,
					3,
				),
			),
		);

		update_option( 'wp_auto_updater_notification_options', $options );

		$options = $this->wp_auto_updater_notification->get_options();
		$this->assertFalse( $options['notification']['core'] );
		$this->assertTrue( $options['notification']['theme'] );
		$this->assertSame( 'test@example.com', $options['mail']['from'] );
		$this->assertFalse( $options['mail']['admin_email'] );
		$this->assertContains( 3, $options['mail']['recipients'] );

		$options = $this->wp_auto_updater_notification->get_options( 'notification' );
		$this->assertFalse( $options['core'] );
		$this->assertTrue( $options['theme'] );

		$options = $this->wp_auto_updater_notification->get_options( 'mail' );
		$this->assertSame( 'test@example.com', $options['from'] );
		$this->assertFalse( $options['admin_email'] );
		$this->assertContains( 3, $options['recipients'] );

		$option = $this->wp_auto_updater_notification->get_options( 'test' );
		$this->assertNull( $option );
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_case_filters() {
		$options = array(
			'notification' => array(
				'core'        => false,
				'theme'       => true,
				'plugin'      => true,
				'translation' => true,
			),
			'mail' => array(
				'from'        => 'test@example.com',
				'admin_email' => false,
				'recipients'  => array(
					1,
					2,
					3,
				),
			),
		);

		update_option( 'wp_auto_updater_options', $options );

		add_filter( 'wp_auto_updater_notification/get_options', array( $this, '_filter_options' ), 10 );

		$options = $this->wp_auto_updater_notification->get_options();
		$this->assertTrue( $options['notification']['core'] );
		$this->assertFalse( $options['notification']['plugin'] );

		add_filter( 'wp_auto_updater_notification/get_option', array( $this, '_filter_option' ), 10, 2 );

		$options = $this->wp_auto_updater_notification->get_options( 'mail' );
		$this->assertSame( 'abc@example.com', $options['from'] );
		$this->assertFalse( $options['admin_email'] );
		$this->assertContains( 6, $options['recipients'] );
	}

	public function _filter_options( $options ) {
		$this->assertTrue( is_array( $options ) );

		$options = array(
			'notification' => array(
				'core'        => true,
				'theme'       => true,
				'plugin'      => false,
				'translation' => true,
			),
			'mail' => array(
				'from'        => 'test@example.com',
				'admin_email' => true,
				'recipients'  => array(
					1,
					2,
					3,
				),
			),
		);

		return $options;
	}

	public function _filter_option( $option, $name ) {
		$expected = array(
			'from'        => '',
			'admin_email' => true,
			'recipients'  => array(),
		);

		$this->assertSame( $expected, $option );
		$this->assertSame( 'mail', $name );

		$option = array(
			'from'        => 'abc@example.com',
			'admin_email' => false,
			'recipients'  => array(
				4,
				5,
				6,
			),
		);

		return $option;
	}
}
