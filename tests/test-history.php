<?php
/**
 * Class Test_Wp_Auto_Updater_History
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_History extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater_history = new WP_Auto_Updater_History();

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}

	/**
	 * Delete the custom table on teardown.
	 */
	public function tearDown() {
		parent::tearDown();

		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$wpdb->get_results( "DROP TABLE IF EXISTS {$table_name}" );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function classAttr() {
		$this->assertClassHasAttribute( 'option_group', 'WP_Auto_Updater_History' );
		$this->assertClassHasAttribute( 'table_name', 'WP_Auto_Updater_History' );
		$this->assertClassHasAttribute( 'table_version', 'WP_Auto_Updater_History' );
		$this->assertClassHasAttribute( 'nonce', 'WP_Auto_Updater_History' );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function objectAttr() {
		$this->assertObjectHasAttribute( 'option_group', new WP_Auto_Updater_History() );
		$this->assertObjectHasAttribute( 'table_name', new WP_Auto_Updater_History() );
		$this->assertObjectHasAttribute( 'table_version', new WP_Auto_Updater_History() );
		$this->assertObjectHasAttribute( 'nonce', new WP_Auto_Updater_History() );
	}

	/**
	 * @test
	 * @group history
	 */
	public function constructor() {
		$this->assertEquals( 10, has_filter( 'init', array( $this->wp_auto_updater_history, 'init' ) ) );

		$this->assertEquals( 10, has_filter( 'admin_menu', array( $this->wp_auto_updater_history, 'add_option_page' ) ) );

		$this->assertEquals( 10, has_filter( 'plugins_loaded', array( $this->wp_auto_updater_history, 'check_table_version' ) ) );
		$this->assertEquals( 10, has_filter( 'admin_notices', array( $this->wp_auto_updater_history, 'admin_notice' ) ) );

		$this->assertEquals( 10, has_filter( 'activate_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater_history, 'activate' ) ) );
	}

	/**
	 * @test
	 * @group history
	 */
	public function init() {
		$this->wp_auto_updater_history->init();

		$this->assertEquals( 10, has_filter( 'option_page_capability_wp_auto_updater', array( $this->wp_auto_updater_history, 'option_page_capability' ) ) );
	}

	/**
	 * @test
	 * @group history
	 */
	public function activate() {
		$this->wp_auto_updater_history->activate();

		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$sql = $wpdb->prepare(
			'SHOW TABLES LIKE "%s"',
			$table_name
		);

		$this->assertEquals( $table_name, $wpdb->get_var( $sql ) );
		$this->assertEquals( $this->wp_auto_updater_history->table_version, get_option( 'wp_auto_updater_history_table_version' ) );
	}

	/**
	 * @test
	 * @group history
	 */
	public function table_exists() {
		$this->assertFalse( $this->wp_auto_updater_history->table_exists() );

		$this->wp_auto_updater_history->activate();

		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$this->assertTrue( $this->wp_auto_updater_history->table_exists( $table_name ) );

		$this->wp_auto_updater_history->drop_table( $table_name );

		$this->assertFalse( $this->wp_auto_updater_history->table_exists( $table_name ) );
	}

	/**
	 * @test
	 * @group history
	 */
	public function version() {
		delete_option( 'wp_auto_updater_history_table_version' );
		$this->assertNull( $this->wp_auto_updater_history->get_table_version() );

		$this->wp_auto_updater_history->set_table_version();
		$this->assertEquals( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );
	}

	/**
	 * @test
	 * @group history
	 */
	public function check_table_version() {
		$this->wp_auto_updater_history->activate();
		$this->assertNull( $this->wp_auto_updater_history->check_table_version() );

		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';
		$this->wp_auto_updater_history->drop_table( $table_name );

		// version 1.0.0
		$schema = "CREATE TABLE $table_name (
			ID      bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			date    datetime            NOT NULL DEFAULT '0000-00-00 00:00:00',
			status  varchar(255)        NOT NULL,
			mode    varchar(255)        NOT NULL,
			label   varchar(255)        NOT NULL,
			info    text                NULL,
			PRIMARY KEY (ID),
			KEY status (status),
			KEY mode (mode),
			KEY label (label)
		);";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$results = dbDelta( $schema );

		update_option( 'wp_auto_updater_history_table_version', '1.0.0' );

		$this->assertNull( $this->wp_auto_updater_history->check_table_version() );
		$this->assertEquals( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );
	}

	/**
	 * @test
	 * @group history
	 */
	public function migrate_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		// version 1.0.0
		$schema = "CREATE TABLE $table_name (
			ID      bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			date    datetime            NOT NULL DEFAULT '0000-00-00 00:00:00',
			status  varchar(255)        NOT NULL,
			mode    varchar(255)        NOT NULL,
			label   varchar(255)        NOT NULL,
			info    text                NULL,
			PRIMARY KEY (ID),
			KEY status (status),
			KEY mode (mode),
			KEY label (label)
		);";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$results = dbDelta( $schema );

		update_option( 'wp_auto_updater_history_table_version', '1.0.0' );

		$this->wp_auto_updater_history->migrate_table( $table_name );

		$this->assertEquals( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );
		$this->assertEquals( 1, get_transient( 'wp_auto_updater/history_table/updated' ) );

		// $sql = $wpdb->prepare(
		// 'SHOW COLUMNS FROM "%s"',
		// $table_name
		// );
		// $a = $wpdb->get_results( $sql );
		// // var_dump( $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" ) );
		// var_dump( $a );

		$result = $this->wp_auto_updater_history->migrate_table();
		$this->assertFalse( $result );

		$result = $this->wp_auto_updater_history->migrate_table( 'test');
		$this->assertFalse( $result );

	}

	/**
	 * @test
	 * @group history
	 */
	public function create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$created = $this->wp_auto_updater_history->create_table( $table_name );

		$sql = $wpdb->prepare(
			'SHOW TABLES LIKE "%s"',
			$table_name
		);

		$this->assertEquals( $table_name, $wpdb->get_var( $sql ) );

		$expected = array(
			"{$table_name}" => "Created table {$table_name}",
		);

		$this->assertEquals( $created, $expected );
		$this->assertEquals( 1, get_transient( 'wp_auto_updater/history_table/created' ) );
		$this->assertEquals( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );

		$this->assertFalse( $this->wp_auto_updater_history->create_table( $table_name ) );
		$this->assertFalse( $this->wp_auto_updater_history->create_table() );
	}

	/**
	 * @test
	 * @group history
	 */
	public function drop_table() {
		$this->wp_auto_updater_history->activate();

		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$this->wp_auto_updater_history->drop_table( $table_name );

		$this->assertFalse( $this->wp_auto_updater_history->table_exists( $table_name ) );
		$this->assertFalse( $this->wp_auto_updater_history->table_exists() );
	}

	/**
	 * @test
	 * @group history
	 */
	public function logging() {
		$this->wp_auto_updater_history->activate();

		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$log = $this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );

		$this->assertEquals( 1, $log );

		$this->wp_auto_updater_history->history_table_name = 'test';
		$result = $this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * @group history
	 */
	public function paginate() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group history
	 */
	public function capability() {
		$this->assertEquals( 'update_core', $this->wp_auto_updater_history->option_page_capability() );
	}

	/**
	 * @test
	 * @group history
	 */
	public function uninstall() {
		$this->wp_auto_updater_history->activate();
		$this->wp_auto_updater_history->uninstall();

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
