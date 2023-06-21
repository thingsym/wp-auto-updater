<?php
/**
 * Class Test_Wp_Auto_Updater_Option_Page
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Option_Page extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function register_settings() {
		$this->wp_auto_updater->register_settings();

		global $wp_registered_settings;
		global $wp_settings_sections;
		global $wp_settings_fields;

		$this->assertTrue( isset( $wp_registered_settings['wp_auto_updater_options'] ) );
		$this->assertSame( 'wp_auto_updater', $wp_registered_settings['wp_auto_updater_options']['group'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_registered_settings['wp_auto_updater_options']['sanitize_callback'] ) );
		$this->assertTrue( in_array( 'validate_options', $wp_registered_settings['wp_auto_updater_options']['sanitize_callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['version'] ) );
		$this->assertSame( 'version', $wp_settings_sections['wp_auto_updater']['version']['id'] );
		$this->assertSame( 'WordPress Version', $wp_settings_sections['wp_auto_updater']['version']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['version']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_nothing', $wp_settings_sections['wp_auto_updater']['version']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['version']['current_wp_version'] ) );
		$this->assertSame( 'current_wp_version', $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['id'] );
		$this->assertSame( 'Current Version', $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_current_wp_version', $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version'] ) );
		$this->assertSame( 'newer_wp_version', $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['id'] );
		$this->assertSame( 'Newer Version', $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_newer_wp_version', $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['scenario'] ) );
		$this->assertSame( 'scenario', $wp_settings_sections['wp_auto_updater']['scenario']['id'] );
		$this->assertSame( 'Auto Update Scenario', $wp_settings_sections['wp_auto_updater']['scenario']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['scenario']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_nothing', $wp_settings_sections['wp_auto_updater']['scenario']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['core'] ) );
		$this->assertSame( 'core', $wp_settings_fields['wp_auto_updater']['scenario']['core']['id'] );
		$this->assertSame( 'WordPress Core', $wp_settings_fields['wp_auto_updater']['scenario']['core']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['core']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_core', $wp_settings_fields['wp_auto_updater']['scenario']['core']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['theme'] ) );
		$this->assertSame( 'theme', $wp_settings_fields['wp_auto_updater']['scenario']['theme']['id'] );
		$this->assertSame( 'Theme', $wp_settings_fields['wp_auto_updater']['scenario']['theme']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['theme']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_theme', $wp_settings_fields['wp_auto_updater']['scenario']['theme']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['plugin'] ) );
		$this->assertSame( 'plugin', $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['id'] );
		$this->assertSame( 'Plugin', $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_plugin', $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['translation'] ) );
		$this->assertSame( 'translation', $wp_settings_fields['wp_auto_updater']['scenario']['translation']['id'] );
		$this->assertSame( 'Translation', $wp_settings_fields['wp_auto_updater']['scenario']['translation']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['translation']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_translation', $wp_settings_fields['wp_auto_updater']['scenario']['translation']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['schedule'] ) );
		$this->assertSame( 'schedule', $wp_settings_sections['wp_auto_updater']['schedule']['id'] );
		$this->assertSame( 'Schedule', $wp_settings_sections['wp_auto_updater']['schedule']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['schedule']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_nothing', $wp_settings_sections['wp_auto_updater']['schedule']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule'] ) );
		$this->assertSame( 'next_schedule', $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['id'] );
		$this->assertSame( 'Next Update Date', $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_schedule_next_updete_date', $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['schedule']['interval'] ) );
		$this->assertSame( 'interval', $wp_settings_fields['wp_auto_updater']['schedule']['interval']['id'] );
		$this->assertSame( 'Update Interval', $wp_settings_fields['wp_auto_updater']['schedule']['interval']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['schedule']['interval']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_schedule_interval', $wp_settings_fields['wp_auto_updater']['schedule']['interval']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['schedule']['date'] ) );
		$this->assertSame( 'date', $wp_settings_fields['wp_auto_updater']['schedule']['date']['id'] );
		$this->assertSame( 'Update Date', $wp_settings_fields['wp_auto_updater']['schedule']['date']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['schedule']['date']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_schedule_date', $wp_settings_fields['wp_auto_updater']['schedule']['date']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['themes'] ) );
		$this->assertSame( 'themes', $wp_settings_sections['wp_auto_updater']['themes']['id'] );
		$this->assertSame( 'Disable Auto Update Themes', $wp_settings_sections['wp_auto_updater']['themes']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['themes']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_themes', $wp_settings_sections['wp_auto_updater']['themes']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['themes']['themes'] ) );
		$this->assertSame( 'themes', $wp_settings_fields['wp_auto_updater']['themes']['themes']['id'] );
		$this->assertSame( 'Themes', $wp_settings_fields['wp_auto_updater']['themes']['themes']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['themes']['themes']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_themes', $wp_settings_fields['wp_auto_updater']['themes']['themes']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['plugins'] ) );
		$this->assertSame( 'plugins', $wp_settings_sections['wp_auto_updater']['plugins']['id'] );
		$this->assertSame( 'Disable Auto Update Plugins', $wp_settings_sections['wp_auto_updater']['plugins']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['plugins']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_plugins', $wp_settings_sections['wp_auto_updater']['plugins']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['plugins']['plugins'] ) );
		$this->assertSame( 'plugins', $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['id'] );
		$this->assertSame( 'Plugins', $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_plugins', $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['callback'] ) );

	}

	/**
	 * @test
	 * @group options_page
	 */
	public function capability() {
		$this->assertSame( 'update_core', $this->wp_auto_updater->option_page_capability() );
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function add_option_page() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );

		// $this->wp_auto_updater->add_option_page();
		// var_dump(has_filter( 'load-dashboard_page_wp-auto-updater', array( $this->wp_auto_updater, 'page_hook_suffix' ) ));
		// $this->assertSame( 10, has_filter( 'load-dashboard_page_wp-auto-updater', array( $this->wp_auto_updater, 'page_hook_suffix' ) ) );
		// global $wp_filter, $wp_actions;
		// var_dump($wp_filter);
		// var_dump($wp_actions);
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function page_hook_suffix() {
		// add_filter( 'automatic_updater_disabled', '__return_false' );
		$this->wp_auto_updater->page_hook_suffix();

		$this->assertSame( 10, has_filter( 'admin_enqueue_scripts', array( $this->wp_auto_updater, 'admin_enqueue_scripts' ) ) );
		// $this->assertSame( 10, has_filter( 'admin_notices', array( $this->wp_auto_updater, 'admin_notice_upgrader_disabled' ) ) );

	}

	/**
	 * @test
	 * @group options_page
	 */
	public function get_schedule_interval() {
		$result = $this->wp_auto_updater->get_schedule_interval();
		$this->assertIsArray( $result );
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function hidden_auto_update_status() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
