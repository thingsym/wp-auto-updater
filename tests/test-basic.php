<?php
/**
 * Class Test_Wp_Auto_Updater_Basic
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Basic extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group basic
	 */
	public function constructor() {
		$this->assertEquals( 10, has_filter( 'init', array( $this->wp_auto_updater, 'load_textdomain' ) ) );
		$this->assertEquals( 10, has_filter( 'init', array( $this->wp_auto_updater, 'init' ) ) );
		$this->assertEquals( 10, has_filter( 'wp_loaded', array( $this->wp_auto_updater, 'auto_update' ) ) );

		$this->assertEquals( 10, has_filter( 'admin_init', array( $this->wp_auto_updater, 'register_settings' ) ) );
		$this->assertEquals( 10, has_filter( 'admin_menu', array( $this->wp_auto_updater, 'add_option_page' ) ) );

		$this->assertEquals( 10, has_filter( 'wp_auto_updater_set_cron', array( $this->wp_auto_updater, 'set_schedule' ) ) );
		$this->assertEquals( 10, has_filter( 'wp_auto_updater_clear_schedule', array( $this->wp_auto_updater, 'clear_schedule' ) ) );

		$this->assertTrue( class_exists( 'WP_Auto_Updater_History' ) );
		$this->assertEquals( 10, has_filter( 'automatic_updates_complete', array( $this->wp_auto_updater, 'auto_update_result' ) ) );

		$this->assertEquals( 10, has_filter( 'activate_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater, 'activate' ) ) );

		$this->assertEquals( 10, has_filter( 'deactivate_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater, 'deactivate' ) ) );

		$uninstallable_plugins = (array) get_option( 'uninstall_plugins' );
		$this->assertEquals( $uninstallable_plugins[ plugin_basename( __WP_AUTO_UPDATER__ ) ], array( 'WP_Auto_Updater', 'uninstall' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function init() {
		$this->wp_auto_updater->init();

		$this->assertEquals( 10, has_filter( 'option_page_capability_wp_auto_updater', array( $this->wp_auto_updater, 'option_page_capability' ) ) );
		$this->assertEquals( 10, has_filter( 'plugin_action_links_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater, 'plugin_action_links' ) ) );
		$this->assertEquals( 10, has_filter( 'cron_schedules', array( $this->wp_auto_updater, 'add_cron_interval' ) ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function activate() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function deactivate() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function admin_enqueue_scripts() {
		$this->wp_auto_updater->admin_enqueue_scripts();
		$this->assertTrue( wp_script_is( 'wp-auto-updater-admin' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function plugin_action_links() {
		$links = $this->wp_auto_updater->plugin_action_links( array() );
		$this->assertContains( 'index.php?page=wp-auto-updater', $links[0] );
	}
}
