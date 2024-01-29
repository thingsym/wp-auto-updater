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
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string $option_group   The group name of option
	 */
	public $option_group = 'wp_auto_updater';

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string $capability   The types of capability
	 */
	public $capability = 'update_core';

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string $table_name   The name of the table
	 */
	public $table_name = 'auto_updater_history';

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string|null $history_table_name   The name of the table with prefix
	 */
	public $history_table_name = null;

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var string $table_version   The version of the table
	 */
	public $table_version = '1.0.1';

	/**
	 * Public variable.
	 *
	 * @access public
	 *
	 * @var array $this->nonce {
	 *   @type array clear_logs {
	 *       @type string name
	 *       @type string action
	 *   }
	 * }
	 */
	public $nonce = array(
		'clear_logs' => array(
			'name'   => '_wpnonce_clear_logs',
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

		add_action( 'plugins_loaded', array( $this, 'init' ) );

		add_action( 'admin_menu', array( $this, 'add_option_page' ) );

		add_action( 'plugins_loaded', array( $this, 'check_table_version' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		register_activation_hook( __WP_AUTO_UPDATER__, array( $this, 'activate' ) );
	}

	/**
	 * Initialize.
	 *
	 * Hooks to plugins_loaded
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_filter( 'option_page_capability_' . $this->option_group, array( $this, 'option_page_capability' ) );
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
	}

	/**
	 * Check table version.
	 *
	 * Compare table version.
	 *
	 * @return void
	 *
	 * @since 1.0.2
	 */
	public function check_table_version() {
		if ( version_compare( (string) $this->get_table_version(), $this->table_version, '<' ) ) {
			$this->migrate_table( $this->history_table_name );
		}
	}

	/**
	 * Migrate table.
	 *
	 * @param string $table_name The name of table.
	 *
	 * @return string|bool
	 *
	 * @since 1.0.2
	 */
	public function migrate_table( $table_name = null ) {
		if ( ! isset( $table_name ) ) {
			return false;
		}
		if ( ! $this->table_exists( $table_name ) ) {
			return false;
		}

		global $wpdb;

		if ( version_compare( (string) $this->get_table_version(), '1.0.1', '<' ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->get_results( "ALTER TABLE {$table_name} ADD user varchar(255) NOT NULL;" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->get_results( "ALTER TABLE {$table_name} MODIFY info text NOT NULL;" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->get_results( "ALTER TABLE {$table_name} ADD INDEX user (user);" );
		}

		$this->set_table_version();
		set_transient( 'wp_auto_updater/history_table/updated', '1', 5 );

		return true;
	}

	/**
	 * Hooks to admin_notices and display notice to admin panel.
	 *
	 * @return void
	 *
	 * @since 1.0.2
	 */
	public function admin_notice() {
		if ( get_transient( 'wp_auto_updater/history_table/created' ) ) {
			?>
<div class="notice notice-success is-dismissible">
<p>
			<?php
			printf(
				/* translators: table create notice: 1: table name, 2: table version */
				__( 'Table <strong>%1$s (%2$s)</strong> create succeeded.', 'wp-auto-updater' ), /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
				esc_html( $this->history_table_name ),
				esc_html( $this->table_version )
			);
			?>
</p>

</div>
			<?php
			delete_transient( 'wp_auto_updater/history_table/created' );
		}

		if ( get_transient( 'wp_auto_updater/history_table/updated' ) ) {
			?>
<div class="notice notice-success is-dismissible">
<p>
			<?php
			printf(
				/* translators: table update notice: 1: table name, 2: table version */
				__( 'Table <strong>%1$s (%2$s)</strong> update succeeded.', 'wp-auto-updater' ), /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
				esc_html( $this->history_table_name ),
				esc_html( $this->table_version )
			);
			?>
</p>
</div>
			<?php
			delete_transient( 'wp_auto_updater/history_table/updated' );
		}

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
			'SHOW TABLES LIKE %s',
			$table_name
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
	 * @return int|null
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
		update_option( 'wp_auto_updater_history_table_version', $this->table_version );
	}

	/**
	 * Create table.
	 *
	 * @access public
	 *
	 * @param string $table_name The name of table.
	 *
	 * @return array|bool
	 *
	 * @since 1.0.0
	 */
	public function create_table( $table_name = null ) {
		if ( ! isset( $table_name ) ) {
			return false;
		}
		if ( $this->table_exists( $table_name ) ) {
			return false;
		}

		/**
		 * Version 1.0.0
		 * $schema = "CREATE TABLE $table_name (
		 * ID      bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		 * date    datetime            NOT NULL DEFAULT '0000-00-00 00:00:00',
		 * status  varchar(255)        NOT NULL,
		 * mode    varchar(255)        NOT NULL,
		 * label   varchar(255)        NOT NULL,
		 * info    text                NULL,
		 * PRIMARY KEY (ID),
		 * KEY status (status),
		 * KEY mode (mode),
		 * KEY label (label)
		 * );";
		 */

		/**
		 * Version 1.0.1
		 */
		$schema = "CREATE TABLE $table_name (
			ID      bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			date    datetime            NOT NULL DEFAULT '0000-00-00 00:00:00',
			user    varchar(255)        NOT NULL,
			status  varchar(255)        NOT NULL,
			mode    varchar(255)        NOT NULL,
			label   varchar(255)        NOT NULL,
			info    text                NOT NULL,
			PRIMARY KEY (ID),
			KEY status (status),
			KEY user (user),
			KEY mode (mode),
			KEY label (label)
		);";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		/* @phpstan-ignore-next-line */
		$results = dbDelta( $schema );

		if ( in_array( 'Created table ' . $this->history_table_name, $results, true ) ) {
			$this->set_table_version();
			set_transient( 'wp_auto_updater/history_table/created', 1, 5 );
		}

		return $results;
	}

	/**
	 * Delete table.
	 *
	 * @access public
	 *
	 * @param string $table_name The name of table.
	 *
	 * @return object|null
	 *
	 * @since 1.0.0
	 */
	public function drop_table( $table_name = null ) {
		global $wpdb;
		if ( $this->table_exists( $table_name ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			return $wpdb->get_results( "DROP TABLE IF EXISTS {$table_name}" );
		}

		return null;
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
	 * @return string|bool
	 *
	 * @since 1.0.0
	 */
	public function logging( $date = null, $status = null, $mode = null, $label = null, $info = null ) {
		if ( ! $this->table_exists( $this->history_table_name ) ) {
			return false;
		}

		if ( is_user_logged_in() ) {
			$user_name = wp_get_current_user()->user_login;
			/* @phpstan-ignore-next-line */
			$user_id = wp_get_current_user()->id;
			$user    = $user_name . ' (' . $user_id . ')';
		}
		else {
			$user = 'nobody';
		}

		$data = array(
			'date'   => isset( $date ) ? $date : current_time( 'mysql' ),
			'user'   => $user,
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
			'%s',
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
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
	public function page_hook_suffix() {
		$args = array(
			'label'   => __( 'Number of items per page:', 'wp-auto-updater' ),
			'default' => 10,
			'option'  => 'wp_auto_updater_history_per_page',
		);

		add_screen_option( 'per_page', $args );
	}

	/**
	 * Set screen option.
	 *
	 * Hooks to set-screen-option.
	 *
	 * @access public
	 *
	 * @return mixed
	 *
	 * @since 1.6.0
	 */
	public function set_screen_option( $status, $option, $value ) {
		if ( 'wp_auto_updater_history_per_page' === $option ) {
			return $value;
		}
	}

	/**
	 * Display paginate.
	 *
	 * @access public
	 *
	 * @param integer $row_count
	 * @param integer $per_page
	 * @param integer $current_paged
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function paginate( $row_count = 0, $per_page = 0, $current_paged = 0 ) {
		if ( empty( $row_count ) || empty( $per_page ) || empty( $current_paged ) ) {
			return '';
		}

		$paginate    = '';
		$total_pages = intval( ceil( $row_count / $per_page ) );

		if ( 2 >= $current_paged ) {
			$paginate .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		}
		else {
			$paginate .= sprintf(
				'<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
				esc_url( add_query_arg( 'paged', 1 ) ),
				__( 'First page', 'wp-auto-updater' ),
				'&laquo;'
			);
		}

		$paginate .= ' ';

		if ( 1 === $current_paged ) {
			$paginate .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		}
		else {
			$paginate .= sprintf(
				'<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
				esc_url( add_query_arg( 'paged', max( 1, $current_paged - 1 ) ) ),
				__( 'Previous page', 'wp-auto-updater' ),
				'&lsaquo;'
			);
		}

		$paginate .= ' ' . $current_paged . ' / ' . $total_pages . ' ';

		if ( $current_paged === $total_pages ) {
			$paginate .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		}
		else {
			$paginate .= sprintf(
				'<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
				esc_url( add_query_arg( 'paged', min( $total_pages, $current_paged + 1 ) ) ),
				__( 'Next page', 'wp-auto-updater' ),
				'&rsaquo;'
			);
		}

		$paginate .= ' ';

		if ( $current_paged >= $total_pages - 1 ) {
			$paginate .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		}
		else {
			$paginate .= sprintf(
				'<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
				esc_url( add_query_arg( 'paged', $total_pages ) ),
				__( 'Last page', 'wp-auto-updater' ),
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

		if ( ! $this->table_exists( $this->history_table_name ) ) {
			?>
<div class="notice notice-error is-dismissible"><p><strong><?php esc_html_e( 'Table no exists.', 'wp-auto-updater' ); ?></strong></p></div>
			<?php
			return;
		}

		global $wpdb;

		$cleared = null;
		if ( ! empty( $_POST[ $this->nonce['clear_logs']['name'] ] ) && current_user_can( 'manage_options' ) && check_admin_referer( $this->nonce['clear_logs']['action'], $this->nonce['clear_logs']['name'] ) ) {
			$priod = '';
			if ( isset( $_POST['delete_priod'] ) ) {
				$priod = sanitize_text_field( wp_unslash( $_POST['delete_priod'] ) );
			}
			$cleared = $this->clear_logs( $priod );
		}

		$screen        = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page      = get_user_meta( get_current_user_id(), $screen_option, true );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = 10;
		}

		$paged  = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
		$offset = ( $paged - 1 ) * $per_page;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$logs = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->history_table_name} ORDER BY date DESC LIMIT %d, %d",
				$offset,
				$per_page
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->history_table_name}" );
		$paginate  = $this->paginate( $row_count, $per_page, $paged );

		?>
<div class="wrap">
<h2><?php esc_html_e( 'Update History', 'wp-auto-updater' ); ?></h2>
		<?php if ( $cleared ) { ?>
<div class="notice notice-error is-dismissible"><p><strong><?php esc_html_e( 'Logs cleared.', 'wp-auto-updater' ); ?></strong></p></div>
<?php } ?>

<div class="tablenav top">

<div class="tablenav-pages">
<span class="displaying-num">
		<?php
		if ( ! empty( $row_count ) ) {
			printf(
				/* translators: item: 1: item, 2: items */
				esc_html( _n( '%d item', '%d items', $row_count, 'wp-auto-updater' ) ),
				esc_html( number_format_i18n( $row_count ) )
			);
		}
		?>
</span>
		<?php echo $paginate; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<br class="clear">
</div>

<table class="wp-list-table widefat striped">
<thead>
<tr>
	<th scope="col" class="manage-column column-date"><?php esc_html_e( 'Date', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-user"><?php esc_html_e( 'User', 'wp-auto-updater' ); ?></th>
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
	<td><?php echo esc_html( $row->user ); ?></td>
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
	<th scope="col" class="manage-column column-user"><?php esc_html_e( 'User', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-mode"><?php esc_html_e( 'Mode', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-label"><?php esc_html_e( 'Label', 'wp-auto-updater' ); ?></th>
	<th scope="col" class="manage-column column-info"><?php esc_html_e( 'Info', 'wp-auto-updater' ); ?></th>
</tr>
</tfoot>
</table>

<div class="tablenav bottom">

<div class="alignleft">
Table Version: <?php echo esc_html( (string) $this->get_table_version() ); ?>
</div>

<div class="tablenav-pages">
<span class="displaying-num">
		<?php
		if ( ! empty( $row_count ) ) {
			printf(
				esc_html( _n( '%d item', '%d items', $row_count, 'wp-auto-updater' ) ),
				esc_html( number_format_i18n( $row_count ) )
			);
		}
		?>
</span>
		<?php echo $paginate; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
</div>
<br class="clear">

<div class="clear-logs alignright">
<form action="" method="post">
<?php wp_nonce_field( $this->nonce['clear_logs']['action'], $this->nonce['clear_logs']['name'], true, true ); ?>
<select name="delete_priod">
<option value=""><?php esc_html_e( 'Select keep logs period', 'wp-auto-updater' ); ?></option>
<option value="delete_all"><?php esc_html_e( 'Delete all', 'wp-auto-updater' ); ?></option>
<option value="1month"><?php esc_html_e( 'for last 1 month', 'wp-auto-updater' ); ?></option>
<option value="3months"><?php esc_html_e( 'for last 3 month', 'wp-auto-updater' ); ?></option>
<option value="6months"><?php esc_html_e( 'for last 6 months', 'wp-auto-updater' ); ?></option>
<option value="1year"><?php esc_html_e( 'for last 1 year', 'wp-auto-updater' ); ?></option>
<option value="3years"><?php esc_html_e( 'for last 3 years', 'wp-auto-updater' ); ?></option>
</select>
<input type="submit" id="clear-logs" class="button button-primary" value="<?php esc_html_e( 'Clear Logs', 'wp-auto-updater' ); ?>" onclick="if(window.confirm('<?php esc_html_e( 'Would you like to delete the logs?', 'wp-auto-updater' ); ?>')){return true;}else{return false;}"></form>
</div>
<br class="clear">

</div>
		<?php
	}

	/**
	 * Clear logs.
	 *
	 * @access public
	 *
	 * @param string $delete_priod
	 *
	 * @return array|object|null Database query results.
	 *
	 * @since 1.6.0
	 */
	public function clear_logs( $delete_priod ) {
		if ( ! $delete_priod ) {
			return null;
		}

		global $wpdb;
		$cleared = null;

		if ( 'delete_all' === $delete_priod ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$cleared = $wpdb->get_results( "DELETE FROM {$this->history_table_name}" );
		}
		elseif ( '1month' === $delete_priod ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$cleared = $wpdb->get_results( "DELETE FROM {$this->history_table_name} WHERE (date < DATE_SUB(CURDATE(), INTERVAL 1 MONTH))" );
		}
		elseif ( '3months' === $delete_priod ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$cleared = $wpdb->get_results( "DELETE FROM {$this->history_table_name} WHERE (date < DATE_SUB(CURDATE(), INTERVAL 3 MONTH))" );
		}
		elseif ( '6months' === $delete_priod ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$cleared = $wpdb->get_results( "DELETE FROM {$this->history_table_name} WHERE (date < DATE_SUB(CURDATE(), INTERVAL 6 MONTH))" );
		}
		elseif ( '1year' === $delete_priod ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$cleared = $wpdb->get_results( "DELETE FROM {$this->history_table_name} WHERE (date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR))" );
		}
		elseif ( '3years' === $delete_priod ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$cleared = $wpdb->get_results( "DELETE FROM {$this->history_table_name} WHERE (date < DATE_SUB(CURDATE(), INTERVAL 3 YEAR))" );
		}

		return $cleared;
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
		$auto_updater_history = new WP_Auto_Updater_History();
		$auto_updater_history->drop_table( $auto_updater_history->history_table_name );

		delete_option( 'wp_auto_updater_history_table_version' );
	}
}
