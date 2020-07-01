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
		$this->assertEquals( 'wp_auto_updater', $wp_registered_settings['wp_auto_updater_options']['group'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_registered_settings['wp_auto_updater_options']['sanitize_callback'] ) );
		$this->assertTrue( in_array( 'validate_options', $wp_registered_settings['wp_auto_updater_options']['sanitize_callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['version'] ) );
		$this->assertEquals( 'version', $wp_settings_sections['wp_auto_updater']['version']['id'] );
		$this->assertEquals( 'WordPress Version', $wp_settings_sections['wp_auto_updater']['version']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['version']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_nothing', $wp_settings_sections['wp_auto_updater']['version']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['version']['current_wp_version'] ) );
		$this->assertEquals( 'current_wp_version', $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['id'] );
		$this->assertEquals( 'Current Version', $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_current_wp_version', $wp_settings_fields['wp_auto_updater']['version']['current_wp_version']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version'] ) );
		$this->assertEquals( 'newer_wp_version', $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['id'] );
		$this->assertEquals( 'Newer Version', $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_newer_wp_version', $wp_settings_fields['wp_auto_updater']['version']['newer_wp_version']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['scenario'] ) );
		$this->assertEquals( 'scenario', $wp_settings_sections['wp_auto_updater']['scenario']['id'] );
		$this->assertEquals( 'Auto Update Scenario', $wp_settings_sections['wp_auto_updater']['scenario']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['scenario']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_nothing', $wp_settings_sections['wp_auto_updater']['scenario']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['core'] ) );
		$this->assertEquals( 'core', $wp_settings_fields['wp_auto_updater']['scenario']['core']['id'] );
		$this->assertEquals( 'WordPress Core', $wp_settings_fields['wp_auto_updater']['scenario']['core']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['core']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_core', $wp_settings_fields['wp_auto_updater']['scenario']['core']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['theme'] ) );
		$this->assertEquals( 'theme', $wp_settings_fields['wp_auto_updater']['scenario']['theme']['id'] );
		$this->assertEquals( 'Theme', $wp_settings_fields['wp_auto_updater']['scenario']['theme']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['theme']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_theme', $wp_settings_fields['wp_auto_updater']['scenario']['theme']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['plugin'] ) );
		$this->assertEquals( 'plugin', $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['id'] );
		$this->assertEquals( 'Plugin', $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_plugin', $wp_settings_fields['wp_auto_updater']['scenario']['plugin']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['scenario']['translation'] ) );
		$this->assertEquals( 'translation', $wp_settings_fields['wp_auto_updater']['scenario']['translation']['id'] );
		$this->assertEquals( 'Translation', $wp_settings_fields['wp_auto_updater']['scenario']['translation']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['scenario']['translation']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_translation', $wp_settings_fields['wp_auto_updater']['scenario']['translation']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['schedule'] ) );
		$this->assertEquals( 'schedule', $wp_settings_sections['wp_auto_updater']['schedule']['id'] );
		$this->assertEquals( 'Schedule', $wp_settings_sections['wp_auto_updater']['schedule']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['schedule']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_nothing', $wp_settings_sections['wp_auto_updater']['schedule']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule'] ) );
		$this->assertEquals( 'next_schedule', $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['id'] );
		$this->assertEquals( 'Next Update Date', $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_schedule_next_updete_date', $wp_settings_fields['wp_auto_updater']['schedule']['next_schedule']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['schedule']['interval'] ) );
		$this->assertEquals( 'interval', $wp_settings_fields['wp_auto_updater']['schedule']['interval']['id'] );
		$this->assertEquals( 'Update Interval', $wp_settings_fields['wp_auto_updater']['schedule']['interval']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['schedule']['interval']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_schedule_interval', $wp_settings_fields['wp_auto_updater']['schedule']['interval']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['schedule']['date'] ) );
		$this->assertEquals( 'date', $wp_settings_fields['wp_auto_updater']['schedule']['date']['id'] );
		$this->assertEquals( 'Update Date', $wp_settings_fields['wp_auto_updater']['schedule']['date']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['schedule']['date']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_schedule_date', $wp_settings_fields['wp_auto_updater']['schedule']['date']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['themes'] ) );
		$this->assertEquals( 'themes', $wp_settings_sections['wp_auto_updater']['themes']['id'] );
		$this->assertEquals( 'Disable Auto Update Themes', $wp_settings_sections['wp_auto_updater']['themes']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['themes']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_themes', $wp_settings_sections['wp_auto_updater']['themes']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['themes']['themes'] ) );
		$this->assertEquals( 'themes', $wp_settings_fields['wp_auto_updater']['themes']['themes']['id'] );
		$this->assertEquals( 'Themes', $wp_settings_fields['wp_auto_updater']['themes']['themes']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['themes']['themes']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_themes', $wp_settings_fields['wp_auto_updater']['themes']['themes']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['plugins'] ) );
		$this->assertEquals( 'plugins', $wp_settings_sections['wp_auto_updater']['plugins']['id'] );
		$this->assertEquals( 'Disable Auto Update Plugins', $wp_settings_sections['wp_auto_updater']['plugins']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_sections['wp_auto_updater']['plugins']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_plugins', $wp_settings_sections['wp_auto_updater']['plugins']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['plugins']['plugins'] ) );
		$this->assertEquals( 'plugins', $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['id'] );
		$this->assertEquals( 'Plugins', $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater, $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_scenario_plugins', $wp_settings_fields['wp_auto_updater']['plugins']['plugins']['callback'] ) );

	}

	/**
	 * @test
	 * @group options_page
	 */
	public function capability() {
		$this->assertEquals( 'update_core', $this->wp_auto_updater->option_page_capability() );
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function options_page() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function page_hook_suffix() {
		$this->wp_auto_updater->page_hook_suffix();
		$this->assertEquals( 10, has_filter( 'admin_enqueue_scripts', array( $this->wp_auto_updater, 'admin_enqueue_scripts' ) ) );
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function get_schedule_interval() {
		$result = $this->wp_auto_updater->get_schedule_interval();
		$this->assertInternalType( 'array', $result );
	}

}
