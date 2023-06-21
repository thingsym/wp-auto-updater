<?php
/**
 * Class Test_Wp_Auto_Updater_Uninstall
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Uninstall extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group basic
	 */
	public function uninstall() {
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		$options = array(
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

		update_option( 'wp_auto_updater_options', $options );

		$this->wp_auto_updater->uninstall();

		$this->assertFalse( get_option( 'wp_auto_updater_options' ) );

		$this->assertFalse( wp_next_scheduled( 'wp_version_check' ) );
		$this->assertFalse( wp_next_scheduled( 'wp_update_themes' ) );
		$this->assertFalse( wp_next_scheduled( 'wp_update_plugins' ) );

		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$sql = $wpdb->prepare(
			'SHOW TABLES LIKE "%s"',
			$table_name
		);
		$this->assertNull( $wpdb->get_var( $sql ) );

		$this->assertFalse( get_option( 'wp_auto_updater_history_table_version' ) );
	}
}
