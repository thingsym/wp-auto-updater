<?php
/**
 * WP_Auto_Updater_History class
 *
 * @package WP_Auto_Updater
 *
 * @since 1.0.0
 */

/**
 * Core class WP_Auto_Updater_History
 *
 * @since 1.0.0
 */
class WP_Auto_Updater_History {

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var string $option_group   The group name of option
	 */
	protected $option_group = 'wp_auto_updater';

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var string $capability   The types of capability
	 */
	protected $capability = 'update_core';

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var string $table_name   The name of the table
	 */
	protected $table_name = 'auto_updater_history';

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var string $table_version   The version of the table
	 */
	protected $table_version = '1.0.0';

	/**
	 * Protected value.
	 *
	 * @access protected
	 *
	 * @var array $this->nonce {
	 *   @type array clear_logs {
	 *       @type string name
	 *       @type string action
	 *   }
	 * }
	 */
	protected $nonce = array(
		'clear_logs' => array(
			'name' => '_wpnonce_clear_logs',
			'action' => 'clear_logs',
		),
	);

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->history_table_name = $wpdb->prefix . $this->table_name;

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'init' ) );

		add_action( 'admin_menu', array( $this, 'add_option_page' ) );

		register_activation_hook( __WP_AUTO_UPDATER__, array( $this, 'activate' ) );
	}

	/**
	 * Initialize.
	 *
	 * Hooks to init
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_filter( 'option_page_capability_' . $this->option_group, array( $this, 'option_page_capability' ) );
	}

	/**
	 * Plugin activate.
	 *
	 * Hooks to activation_hook and create table.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		$this->create_table( $this->history_table_name );
		$this->set_table_version();
	}

	/**
	 * Checks table.
	 *
	 * @access public
	 *
	 * @param string $table_name The name of table.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function table_exists( $table_name = null ) {
		if ( ! isset( $table_name ) ) {
			return false;
		}

		global $wpdb;

		$sql = $wpdb->prepare(
			'SHOW TABLES LIKE "%s"',
			$table_name
		);

		if ( $table_name === $wpdb->get_var( $sql ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns table version.
	 *
	 * @access public
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function get_table_version() {
		return get_option( 'wp_auto_updater_history_table_version', null );
	}

	/**
	 * Sets table version.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function set_table_version() {
		add_option( 'wp_auto_updater_history_table_version', $this->table_version );
	}

	/**
	 * Create table.
	 *
	 * @access public
	 *
	 * @param string $table_name The name of table.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function create_table( $table_name = null ) {
		if ( ! isset( $table_name ) ) {
			return;
		}
		if ( $this->table_exists( $table_name ) ) {
			return;
		}

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
				ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				status varchar(255) NOT NULL,
				mode varchar(255) NOT NULL,
				label varchar(255) NOT NULL,
				info text NULL,
				PRIMARY KEY (ID),
				KEY status (status),
				KEY mode (mode),
				KEY label (label)
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		return dbDelta( $sql );
	}

	/**
	 * Delete table.
	 *
	 * @access public
	 *
	 * @param string $table_name The name of table.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function drop_table( $table_name = null ) {
		global $wpdb;
		if ( $this->table_exists( $table_name ) ) {
			return $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
		}
	}

	/**
	 * Insert log data to tables.
	 *
	 * @access public
	 *
	 * @param string $date
	 * @param string $status
	 * @param string $mode
	 * @param string $label
	 * @param string $info
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function logging( $date = null, $status = null, $mode = null, $label = null, $info = null ) {
		if ( ! $this->table_exists( $this->history_table_name ) ) {
			return;
		}

		$data = array(
			'date'   => isset( $date ) ? $date : current_time( 'mysql' ),
			'status' => $status,
			'mode'   => $mode,
			'label'  => $label,
			'info'   => $info,
		);

		$format = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		);

		global $wpdb;
		return $wpdb->insert( $this->history_table_name, $data, $format );
	}

	/**
	 * Returns capability.
	 *
	 * @access public
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function option_page_capability() {
		return $this->capability;
	}

	/**
	 * Adds history page.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function add_option_page() {
		$page_hook = add_dashboard_page(
			__( 'Update History', 'wp-auto-updater' ),
			__( 'Update History', 'wp-auto-updater' ),
			$this->option_page_capability(),
			'wp-auto-updater-history',
			array( $this, 'render_history_page' )
		);

		if ( empty( $page_hook ) ) {
			return;
		}

		add_action( 'load-' . $page_hook, array( $this, 'page_hook_suffix' ) );
	}

	/**
	 * Page Hook Suffix.
	 *
	 * Hooks to load-{$page_hook}.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function page_hook_suffix() {}

	/**
	 * Load textdomain
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-auto-updater', false, dirname( plugin_basename( __WP_AUTO_UPDATER__ ) ) . '/languages/' );
	}

	/**
	 * Display paginate.
	 *
	 * @access public
	 *
	 * @param string $row_count
	 * @param string $per_page
	 * @param string $current_paged
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	 public function paginate( $row_count = 0, $per_page = 0, $current_paged = 0 ) {
		 if ( empty( $row_count ) || empty( $per_page ) || empty( $current_paged ) ) {
			return '';
		}

		$paginate = '';
		$total_pages = intval( ceil( $row_count / $per_page ) );

		if ( 2 >= $current_paged ) {
			$paginate .= '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		}
		else {
			$paginate .= sprintf(
				"<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', 1 ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		$paginate .= ' ';

		if ( 1 === $current_paged ) {
			$paginate .= '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		}
		else {
			$paginate .= sprintf(
				"<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current_paged - 1 ) ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		$paginate .= ' ' . $current_paged . ' / ' . $total_pages . ' ';

		if ( $current_paged === $total_pages ) {
			$paginate .= '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		}
		else {
			$paginate .= sprintf(
				"<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current_paged + 1 ) ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		$paginate .= ' ';

		if ( $current_paged >= $total_pages - 1 ) {
			$paginate .= '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		}
		else {
			$paginate .= sprintf(
				"<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}

		return $paginate;
	}

	/**
	 * Display history page.
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render_history_page() {
		$message = '';

		if ( ! $this->table_exists( $this->history_table_name ) ) {
			$message = '<div id="message" class="updated"><p><strong>' . __( 'Table no exists.',  'wp-auto-updater' ) . '</strong></p></div>';

			echo $message;

			return;
		}

		global $wpdb;

		if ( ! empty( $_POST[ $this->nonce['clear_logs']['name'] ] ) && current_user_can( 'manage_options' ) && check_admin_referer( $this->nonce['clear_logs']['action'], $this->nonce['clear_logs']['name'] ) ) {
			$cleared = $wpdb->query( "DELETE FROM {$this->history_table_name}" );

			if ( $cleared ) {
				$message = '<div id="message" class="updated"><p><strong>' . __( 'Logs cleared.', 'wp-auto-updater' ) . '</strong></p></div>';
			}
		}

		$per_page = 15;
		$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
		$offset = isset( $paged ) ? ( $paged - 1 ) * $per_page : 0;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->history_table_name} ORDER BY date DESC LIMIT %d, %d",
			$offset,
			$per_page
		);
		$logs = $wpdb->get_results( $sql );

		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->history_table_name}" );
		$paginate = $this->paginate( $row_count, $per_page, $paged );

?>
<div class="wrap">
<h2><?php esc_html_e( 'Update History', 'wp-auto-updater' ); ?></h2>
<?php echo $message; ?>

<div class="tablenav top">

<div class="tablenav-pages">
<span class="displaying-num">
<?php
if ( ! empty( $row_count ) ) {
	printf( esc_html( _n( '%d item', '%d items', $row_count, 'wp-auto-updater' ) ), $row_count );
}
?>
</span>
<?php echo $paginate; ?>
</div>
<br class="clear">
</div>

<table class="wp-list-table widefat striped">
<thead>
<tr>
	<th scope="col" class="manage-column column-date"><?php esc_html_e( 'Date', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-mode"><?php esc_html_e( 'Mode', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-label"><?php esc_html_e( 'Label', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-info"><?php esc_html_e( 'Info', 'wp-auto-updater' ); ?></th>
</tr>
</thead>
<tbody id="the-list">
<?php foreach ( $logs as $row ) { ?>
<tr>
	<td><?php echo esc_html( $row->date ); ?></td>
	<td><?php echo esc_html( $row->status ); ?></td>
	<td><?php echo esc_html( $row->mode ); ?></td>
	<td><?php echo esc_html( $row->label ); ?></td>
	<td><?php echo nl2br( esc_html( $row->info ) ); ?></td>
</tr>
<?php } ?>
</tbody>
<tfoot>
<tr>
	<th scope="col" class="manage-column column-date"><?php esc_html_e( 'Date', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-mode"><?php esc_html_e( 'Mode', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-label"><?php esc_html_e( 'Label', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-info"><?php esc_html_e( 'Info', 'wp-auto-updater' ); ?></th>
</tr>
</tfoot>
</table>

<div class="tablenav bottom">

<div class="alignleft actions">
<form action="" method="post" onclick="if(window.confirm('<?php esc_html_e( 'Would you like to delete the logs?', 'wp-auto-updater' ); ?>')){return ture;}else{return false;}">
<?php wp_nonce_field( $this->nonce['clear_logs']['action'], $this->nonce['clear_logs']['name'], true, true ); ?>
<input type="submit" id="clear-logs" class="button button-primary" value="<?php esc_html_e( 'Clear Logs', 'wp-auto-updater' ); ?>"></form>
</div>

<div class="tablenav-pages">
<span class="displaying-num">
<?php
if ( ! empty( $row_count ) ) {
	printf( esc_html( _n( '%d item', '%d items', $row_count, 'wp-auto-updater' ) ), $row_count );
}
?>
</span>
<?php echo $paginate; ?>
</div>
<br class="clear">
</div>

</div>
<?php
	}

	/**
	 * Uninstall.
	 *
	 * Hooks to uninstall_hook
	 *
	 * @access public static
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		$wp_auto_updater_history = new WP_Auto_Updater_History();
		$wp_auto_updater_history->drop_table( $wp_auto_updater_history->history_table_name );

		delete_option( 'wp_auto_updater_history_table_version' );
	}
}
