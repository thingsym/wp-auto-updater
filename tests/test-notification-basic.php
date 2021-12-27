<?php
/**
 * Class Test_Wp_Auto_Updater_Notification_Basic
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Notification_Basic extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater_notification = new WP_Auto_Updater_Notification();
	}

	/**
	 * @test
	 * @group notification
	 */
	public function classAttr() {
		$this->assertClassHasAttribute( 'option_group', 'WP_Auto_Updater_Notification' );
		$this->assertClassHasAttribute( 'option_name', 'WP_Auto_Updater_Notification' );
		$this->assertClassHasAttribute( 'capability', 'WP_Auto_Updater_Notification' );
		$this->assertClassHasAttribute( 'default_options', 'WP_Auto_Updater_Notification' );
	}

	/**
	 * @test
	 * @group notification
	 */
	public function objectAttr() {
		$this->assertObjectHasAttribute( 'option_group', new WP_Auto_Updater_Notification() );
		$this->assertObjectHasAttribute( 'option_name', new WP_Auto_Updater_Notification() );
		$this->assertObjectHasAttribute( 'capability', new WP_Auto_Updater_Notification() );
		$this->assertObjectHasAttribute( 'default_options', new WP_Auto_Updater_Notification() );
	}

	/**
	 * @test
	 * @group notification
	 */
	function public_variable() {
		$this->assertEquals( 'wp_auto_updater', $this->wp_auto_updater_notification->option_group );
		$this->assertEquals( 'wp_auto_updater_notification_options', $this->wp_auto_updater_notification->option_name );
		$this->assertEquals( 'update_core', $this->wp_auto_updater_notification->capability );

		$expected = array(
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
		$this->assertEquals( $expected, $this->wp_auto_updater_notification->default_options );
	}

	/**
	 * @test
	 * @group notification
	 */
	public function constructor() {
		$this->assertEquals( 10, has_filter( 'init', array( $this->wp_auto_updater_notification, 'init' ) ) );
		$this->assertEquals( 10, has_filter( 'admin_init', array( $this->wp_auto_updater_notification, 'register_settings' ) ) );
	}

	/**
	 * @test
	 * @group notification
	 */
	public function init() {
		$this->wp_auto_updater_notification->init();

		$this->assertEquals( 10, has_filter( 'wp_loaded', array( $this->wp_auto_updater_notification, 'set_update_notification_core' ) ) );
		$this->assertEquals( 10, has_filter( 'auto_core_update_email', array( $this->wp_auto_updater_notification, 'change_core_update_email' ) ) );
		$this->assertEquals( 10, has_filter( 'wp_loaded', array( $this->wp_auto_updater_notification, 'disable_theme_and_plugin_update_notification' ) ) );
	}

	/**
	 * @test
	 * @group notification
	 */
	public function activate() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group notification
	 */
	public function deactivate() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
