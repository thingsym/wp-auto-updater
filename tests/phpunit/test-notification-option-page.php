<?php
/**
 * Class Test_Wp_Auto_Updater_Notification_Option_Page
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Notification_Option_Page extends WP_UnitTestCase {
	public $wp_auto_updater_notification;

	public function setUp(): void {
		parent::setUp();
		$this->wp_auto_updater_notification = new WP_Auto_Updater_Notification();
	}

	/**
	 * @test
	 * @group notification
	 */
	public function register_settings() {
		$this->wp_auto_updater_notification->register_settings();

		global $wp_registered_settings;
		global $wp_settings_sections;
		global $wp_settings_fields;

		$this->assertTrue( isset( $wp_registered_settings['wp_auto_updater_notification_options'] ) );
		$this->assertSame( 'wp_auto_updater', $wp_registered_settings['wp_auto_updater_notification_options']['group'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_registered_settings['wp_auto_updater_notification_options']['sanitize_callback'] ) );
		$this->assertTrue( in_array( 'validate_options', $wp_registered_settings['wp_auto_updater_notification_options']['sanitize_callback'] ) );

		$this->assertTrue( isset( $wp_settings_sections['wp_auto_updater']['notification'] ) );
		$this->assertSame( 'notification', $wp_settings_sections['wp_auto_updater']['notification']['id'] );
		$this->assertSame( 'Notification', $wp_settings_sections['wp_auto_updater']['notification']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_settings_sections['wp_auto_updater']['notification']['callback'] ) );
		$this->assertTrue( in_array( 'settings_section_cb_notification', $wp_settings_sections['wp_auto_updater']['notification']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['notification']['core'] ) );
		$this->assertSame( 'core', $wp_settings_fields['wp_auto_updater']['notification']['core']['id'] );
		$this->assertSame( 'WordPress Core', $wp_settings_fields['wp_auto_updater']['notification']['core']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_settings_fields['wp_auto_updater']['notification']['core']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_core_notification', $wp_settings_fields['wp_auto_updater']['notification']['core']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['notification']['theme'] ) );
		$this->assertSame( 'theme', $wp_settings_fields['wp_auto_updater']['notification']['theme']['id'] );
		$this->assertSame( 'Theme', $wp_settings_fields['wp_auto_updater']['notification']['theme']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_settings_fields['wp_auto_updater']['notification']['theme']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_theme_notification', $wp_settings_fields['wp_auto_updater']['notification']['theme']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['notification']['plugin'] ) );
		$this->assertSame( 'plugin', $wp_settings_fields['wp_auto_updater']['notification']['plugin']['id'] );
		$this->assertSame( 'Plugin', $wp_settings_fields['wp_auto_updater']['notification']['plugin']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_settings_fields['wp_auto_updater']['notification']['plugin']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_plugin_notification', $wp_settings_fields['wp_auto_updater']['notification']['plugin']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['notification']['translation'] ) );
		$this->assertSame( 'translation', $wp_settings_fields['wp_auto_updater']['notification']['translation']['id'] );
		$this->assertSame( 'Translation', $wp_settings_fields['wp_auto_updater']['notification']['translation']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_settings_fields['wp_auto_updater']['notification']['translation']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_translation_notification', $wp_settings_fields['wp_auto_updater']['notification']['translation']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['notification']['from_mail'] ) );
		$this->assertSame( 'from_mail', $wp_settings_fields['wp_auto_updater']['notification']['from_mail']['id'] );
		$this->assertSame( 'From Email', $wp_settings_fields['wp_auto_updater']['notification']['from_mail']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_settings_fields['wp_auto_updater']['notification']['from_mail']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_from_mail', $wp_settings_fields['wp_auto_updater']['notification']['from_mail']['callback'] ) );

		$this->assertTrue( isset( $wp_settings_fields['wp_auto_updater']['notification']['recipients'] ) );
		$this->assertSame( 'recipients', $wp_settings_fields['wp_auto_updater']['notification']['recipients']['id'] );
		$this->assertSame( 'Recipients', $wp_settings_fields['wp_auto_updater']['notification']['recipients']['title'] );
		$this->assertTrue( in_array( $this->wp_auto_updater_notification, $wp_settings_fields['wp_auto_updater']['notification']['recipients']['callback'] ) );
		$this->assertTrue( in_array( 'settings_field_cb_recipients', $wp_settings_fields['wp_auto_updater']['notification']['recipients']['callback'] ) );

	}

	/**
	 * @test
	 * @group notification
	 */
	public function capability() {
		$this->assertSame( 'update_core', $this->wp_auto_updater_notification->option_page_capability() );
	}

}
