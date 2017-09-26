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

		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	}

	/**
	 * @test
	 * @group history
	 */
	public function constructor() {
		$this->assertEquals( 10, has_filter( 'init', array( $this->wp_auto_updater_history, 'load_textdomain' ) ) );
		$this->assertEquals( 10, has_filter( 'init', array( $this->wp_auto_updater_history, 'init' ) ) );

		$this->assertEquals( 10, has_filter( 'admin_menu', array( $this->wp_auto_updater_history, 'add_option_page' ) ) );

		$this->assertEquals( 10, has_filter( 'activate_' . plugin_basename(__WP_AUTO_UPDATER__), array( $this->wp_auto_updater_history, 'activate' ) ) );
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
		$this->assertEquals( '1.0.0', get_option('wp_auto_updater_history_table_version') );
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
			"{$table_name}" => "Created table {$table_name}"
		);

		$this->assertEquals( $created, $expected );
		$this->assertNull( $this->wp_auto_updater_history->create_table( $table_name ) );
		$this->assertNull( $this->wp_auto_updater_history->create_table() );
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
	}

	/**
	 * @test
	 * @group history
	 */
	public function paginate() {
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
