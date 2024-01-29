<?php
/**
 * Class Test_Wp_Auto_Updater_History
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_History extends WP_UnitTestCase {
	public $wp_auto_updater_history;

	public function setUp(): void {
		parent::setUp();
		$this->wp_auto_updater_history = new WP_Auto_Updater_History();

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}

	/**
	 * Delete the custom table on teardown.
	 */
	public function tearDown(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$wpdb->get_results( "DROP TABLE IF EXISTS {$table_name}" );

		parent::tearDown();
	}

	/**
	 * @test
	 * @group history
	 */
	public function classAttr() {
		$this->assertClassHasAttribute( 'option_group', 'WP_Auto_Updater_History' );
		$this->assertClassHasAttribute( 'table_name', 'WP_Auto_Updater_History' );
		$this->assertClassHasAttribute( 'table_version', 'WP_Auto_Updater_History' );
		$this->assertClassHasAttribute( 'nonce', 'WP_Auto_Updater_History' );
	}

	/**
	 * @test
	 * @group history
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
	function public_variable() {
		$this->assertSame( 'wp_auto_updater', $this->wp_auto_updater_history->option_group );
		$this->assertSame( 'update_core', $this->wp_auto_updater_history->capability );
		$this->assertSame( 'auto_updater_history', $this->wp_auto_updater_history->table_name );
		$this->assertSame( '1.0.1', $this->wp_auto_updater_history->table_version );

		global $wpdb;
		$this->assertSame( $wpdb->prefix . $this->wp_auto_updater_history->table_name, $this->wp_auto_updater_history->history_table_name );

		$expected = array(
			'clear_logs' => array(
				'name'   => '_wpnonce_clear_logs',
				'action' => 'clear_logs',
			),
		);
		$this->assertSame( $expected, $this->wp_auto_updater_history->nonce );
	}

	/**
	 * @test
	 * @group history
	 */
	public function constructor() {
		$this->assertSame( 10, has_filter( 'plugins_loaded', array( $this->wp_auto_updater_history, 'init' ) ) );

		$this->assertSame( 10, has_filter( 'admin_menu', array( $this->wp_auto_updater_history, 'add_option_page' ) ) );

		$this->assertSame( 10, has_filter( 'plugins_loaded', array( $this->wp_auto_updater_history, 'check_table_version' ) ) );
		$this->assertSame( 10, has_filter( 'admin_notices', array( $this->wp_auto_updater_history, 'admin_notice' ) ) );

		$this->assertSame( 10, has_filter( 'activate_' . plugin_basename( __WP_AUTO_UPDATER__ ), array( $this->wp_auto_updater_history, 'activate' ) ) );
	}

	/**
	 * @test
	 * @group history
	 */
	public function init() {
		$this->wp_auto_updater_history->init();

		$this->assertSame( 10, has_filter( 'option_page_capability_wp_auto_updater', array( $this->wp_auto_updater_history, 'option_page_capability' ) ) );
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

		$this->assertSame( $table_name, $wpdb->get_var( $sql ) );
		$this->assertSame( $this->wp_auto_updater_history->table_version, get_option( 'wp_auto_updater_history_table_version' ) );
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
		$this->assertSame( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );
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
		$this->assertSame( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );
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

		$this->assertSame( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );
		$this->assertSame( '1', get_transient( 'wp_auto_updater/history_table/updated' ) );

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

		$this->assertSame( $table_name, $wpdb->get_var( $sql ) );

		$expected = array(
			"{$table_name}" => "Created table {$table_name}",
		);

		$this->assertSame( $created, $expected );
		$this->assertSame( '1', get_transient( 'wp_auto_updater/history_table/created' ) );
		$this->assertSame( $this->wp_auto_updater_history->table_version, $this->wp_auto_updater_history->get_table_version() );

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

		$this->assertSame( 1, $log );

		$this->wp_auto_updater_history->history_table_name = 'test';
		$result = $this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * @group history
	 */
	public function paginate() {
		$paginate = $this->wp_auto_updater_history->paginate( 0, 5, 1 );
		$this->assertSame( '', $paginate );
		$paginate = $this->wp_auto_updater_history->paginate( 30, 0, 1 );
		$this->assertSame( '', $paginate );
		$paginate = $this->wp_auto_updater_history->paginate( 30, 5, 0 );
		$this->assertSame( '', $paginate );

		$paginate = $this->wp_auto_updater_history->paginate( 30, 5, 1 );
		$this->assertStringContainsString( '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>', $paginate );
		$this->assertStringContainsString( '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>', $paginate );
		$this->assertStringContainsString( '1 / 6', $paginate );
		$this->assertStringContainsString( '<a class="next-page button" href="?paged=2"><span class="screen-reader-text">Next page</span><span aria-hidden="true">&rsaquo;</span></a>', $paginate );
		$this->assertStringContainsString( '<a class="last-page button" href="?paged=6"><span class="screen-reader-text">Last page</span><span aria-hidden="true">&raquo;</span></a>', $paginate );

		$paginate = $this->wp_auto_updater_history->paginate( 30, 5, 3 );
		$this->assertStringContainsString( '<a class="first-page button" href="?paged=1"><span class="screen-reader-text">First page</span><span aria-hidden="true">&laquo;</span></a>', $paginate );
		$this->assertStringContainsString( '<a class="prev-page button" href="?paged=2"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">&lsaquo;</span></a>', $paginate );
		$this->assertStringContainsString( '3 / 6', $paginate );
		$this->assertStringContainsString( '<a class="next-page button" href="?paged=4"><span class="screen-reader-text">Next page</span><span aria-hidden="true">&rsaquo;</span></a>', $paginate );
		$this->assertStringContainsString( '<a class="last-page button" href="?paged=6"><span class="screen-reader-text">Last page</span><span aria-hidden="true">&raquo;</span></a>', $paginate );

		$paginate = $this->wp_auto_updater_history->paginate( 30, 5, 6 );
		$this->assertStringContainsString( '<a class="first-page button" href="?paged=1"><span class="screen-reader-text">First page</span><span aria-hidden="true">&laquo;</span></a>', $paginate );
		$this->assertStringContainsString( '<a class="prev-page button" href="?paged=5"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">&lsaquo;</span></a>', $paginate );
		$this->assertStringContainsString( '6 / 6', $paginate );
		$this->assertStringContainsString( '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>', $paginate );
		$this->assertStringContainsString( '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>', $paginate );
	}

	/**
	 * @test
	 * @group history
	 */
	public function capability() {
		$this->assertSame( 'update_core', $this->wp_auto_updater_history->option_page_capability() );
	}

	/**
	 * @test
	 * @group history
	 */
	public function clear_logs() {
		$this->wp_auto_updater_history->activate();
		global $wpdb;
		$table_name = $wpdb->prefix . 'auto_updater_history';

		$this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );

		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		$this->assertSame( '3', $row_count );

		$this->wp_auto_updater_history->clear_logs( 'delete_all' );
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		$this->assertSame( '0', $row_count );

		$this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( null, 'aa', 'bbb', 'cccc', 'dddd' );

		$data = date( 'Y-m-d H:i:s', strtotime( '-32 days', current_time( 'timestamp' ) ) );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );

		$this->wp_auto_updater_history->clear_logs( '1month' );
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		$this->assertSame( '3', $row_count );

		$data = date( 'Y-m-d H:i:s', strtotime( '-92 days', current_time( 'timestamp' ) ) );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );

		$this->wp_auto_updater_history->clear_logs( '3months' );
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		$this->assertSame( '6', $row_count );

		$data = date( 'Y-m-d H:i:s', strtotime( '-182 days', current_time( 'timestamp' ) ) );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );

		$this->wp_auto_updater_history->clear_logs( '6months' );
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		$this->assertSame( '9', $row_count );

		$data = date( 'Y-m-d H:i:s', strtotime( '-362 days', current_time( 'timestamp' ) ) );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );

		$this->wp_auto_updater_history->clear_logs( '1year' );
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		$this->assertSame( '12', $row_count );

		$data = date( 'Y-m-d H:i:s', strtotime( '-1082 days', current_time( 'timestamp' ) ) );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );
		$this->wp_auto_updater_history->logging( $data, 'aa', 'bbb', 'cccc', 'dddd' );

		$this->wp_auto_updater_history->clear_logs( '3years' );
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		$this->assertSame( '15', $row_count );
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
