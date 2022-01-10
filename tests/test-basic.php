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
	public function classAttr() {
		$this->assertClassHasAttribute( 'option_group', 'WP_Auto_Updater' );
		$this->assertClassHasAttribute( 'option_name', 'WP_Auto_Updater' );
		$this->assertClassHasAttribute( 'capability', 'WP_Auto_Updater' );
		$this->assertClassHasAttribute( 'default_options', 'WP_Auto_Updater' );
		$this->assertClassHasAttribute( 'upgraded_version', 'WP_Auto_Updater' );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function objectAttr() {
		$this->assertObjectHasAttribute( 'option_group', new WP_Auto_Updater() );
		$this->assertObjectHasAttribute( 'option_name', new WP_Auto_Updater() );
		$this->assertObjectHasAttribute( 'capability', new WP_Auto_Updater() );
		$this->assertObjectHasAttribute( 'default_options', new WP_Auto_Updater() );
		$this->assertObjectHasAttribute( 'upgraded_version', new WP_Auto_Updater() );
	}

	/**
	 * @test
	 * @group basic
	 */
	function public_variable() {
		$this->assertEquals( 'wp_auto_updater', $this->wp_auto_updater->option_group );
		$this->assertEquals( 'wp_auto_updater_options', $this->wp_auto_updater->option_name );
		$this->assertEquals( 'update_core', $this->wp_auto_updater->capability );

		$expected = array(
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
		$this->assertEquals( $expected, $this->wp_auto_updater->default_options );

		$this->assertNull( $this->wp_auto_updater->upgraded_version );

		$this->assertIsArray( $this->wp_auto_updater->plugin_data );
		$this->assertEmpty( $this->wp_auto_updater->plugin_data );

		$this->assertIsObject( $this->wp_auto_updater->update_history );
		$this->assertIsObject( $this->wp_auto_updater->notification );
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

		$this->assertEquals( 10, has_filter( 'wp_auto_updater/set_cron', array( $this->wp_auto_updater, 'set_schedule' ) ) );
		$this->assertEquals( 10, has_filter( 'wp_auto_updater/clear_schedule', array( $this->wp_auto_updater, 'clear_schedule' ) ) );

		$this->assertTrue( class_exists( 'WP_Auto_Updater_History' ) );
		$this->assertEquals( 10, has_filter( 'automatic_updates_complete', array( $this->wp_auto_updater, 'auto_update_result' ) ) );

		$this->assertEquals( 10, has_filter( 'activate_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater, 'activate' ) ) );

		$this->assertEquals( 10, has_filter( 'deactivate_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater, 'deactivate' ) ) );

		$uninstallable_plugins = (array) get_option( 'uninstall_plugins' );
		$this->assertEquals( array( 'WP_Auto_Updater', 'uninstall' ), $uninstallable_plugins[ plugin_basename( __WP_AUTO_UPDATER__ ) ] );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function init() {
		$this->wp_auto_updater->init();

		$this->assertEquals( 10, has_filter( 'option_page_capability_wp_auto_updater', array( $this->wp_auto_updater, 'option_page_capability' ) ) );
		$this->assertEquals( 10, has_filter( 'plugin_row_meta', array( $this->wp_auto_updater, 'plugin_metadata_links' ) ) );
		$this->assertEquals( 10, has_filter( 'plugin_action_links_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater, 'plugin_action_links' ) ) );
		$this->assertEquals( 10, has_filter( 'cron_schedules', array( $this->wp_auto_updater, 'add_cron_interval' ) ) );

		$this->assertEquals( 10, has_filter( 'plugins_auto_update_enabled', '__return_false' ) );
		$this->assertEquals( 10, has_filter( 'themes_auto_update_enabled', '__return_false' ) );
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
		$this->wp_auto_updater->load_plugin_data();

		$this->wp_auto_updater->admin_enqueue_scripts();
		$this->assertTrue( wp_script_is( 'wp-auto-updater-admin' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function plugin_metadata_links() {
		$links = $this->wp_auto_updater->plugin_metadata_links( array(), plugin_basename( __WP_AUTO_UPDATER__ ) );
		$this->assertContains( '<a href="https://github.com/sponsors/thingsym">Become a sponsor</a>', $links );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function plugin_action_links() {
		$links = $this->wp_auto_updater->plugin_action_links( array() );
		$this->assertContains( '<a href="index.php?page=wp-auto-updater">Settings</a>', $links );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function load_textdomain() {
		$result = $this->wp_auto_updater->load_textdomain();
		$this->assertNull( $result );
	}

	/**
	 * @test
	 * @group basic
	 */
	function load_plugin_data() {
		$this->wp_auto_updater->load_plugin_data();
		$result = $this->wp_auto_updater->plugin_data;

		$this->assertTrue( is_array( $result ) );
	}

}
