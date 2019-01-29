<?php
/**
 * Class Test_Wp_Auto_Updater_Auto_Update
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Auto_Update extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update() {
		$this->assertTrue( $this->wp_auto_updater->auto_update() );

		add_filter( 'automatic_updater_disabled', '__return_true' );
		$this->assertFalse( $this->wp_auto_updater->auto_update() );

	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_result() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );

		// $this->wp_auto_updater->auto_update_result();
		// todo: database check
	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_wordpress_core_case1() {
		// https://api.wordpress.org/core/version-check/1.7/?locale=ja
		$upgrade                  = new stdClass();
		$upgrade->response        = 'upgrade';
		$upgrade->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.1.zip';
		$upgrade->locale          = 'en_US';
		$upgrade->packages        = '';
		$upgrade->current         = '4.8.1';
		$upgrade->version         = '4.8.1';
		$upgrade->php_version     = '5.2.4';
		$upgrade->mysql_version   = '5.0';
		$upgrade->new_bundled     = '4.7';
		$upgrade->partial_version = '';
		$upgrade->new_files       = 1;

		$autoupdate                  = new stdClass();
		$autoupdate->response        = 'autoupdate';
		$autoupdate->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.1.zip';
		$autoupdate->locale          = 'en_US';
		$autoupdate->packages        = '';
		$autoupdate->current         = '4.8.1';
		$autoupdate->version         = '4.8.1';
		$autoupdate->php_version     = '5.2.4';
		$autoupdate->mysql_version   = '5.0';
		$autoupdate->new_bundled     = '4.7';
		$autoupdate->partial_version = '';
		$autoupdate->new_files       = 1;

		$update_core                  = new stdClass();
		$update_core->updates         = array( $upgrade, $autoupdate );
		$update_core->last_checked    = 1506247076;
		$update_core->version_checked = '4.8.1';
		$update_core->translations    = array();

		global $wp_version;
		$now_wp_version = $wp_version;
		$wp_version     = '4.6.1';

		set_site_transient( 'update_core', $update_core );

		$options = array(
			'core' => 'minor',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_minor_auto_core_updates', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_minor_auto_core_updates', '__return_false' ) );

		$options = array(
			'core' => 'major',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'minor-only',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'pre-version',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );
		$this->assertEquals( 10, has_filter( 'pre_site_option_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );
		$this->assertEquals( 10, has_filter( 'site_transient_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );

		remove_filter( 'pre_site_option_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) );
		remove_filter( 'site_transient_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) );

		$options = array(
			'core' => 'disable-auto-update',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_core', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'auto_update_core', '__return_false' ) );

	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_wordpress_core_case2() {
		$upgrade                  = new stdClass();
		$upgrade->response        = 'upgrade';
		$upgrade->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.1.zip';
		$upgrade->locale          = 'en_US';
		$upgrade->packages        = '';
		$upgrade->current         = '4.8.1';
		$upgrade->version         = '4.8.1';
		$upgrade->php_version     = '5.2.4';
		$upgrade->mysql_version   = '5.0';
		$upgrade->new_bundled     = '4.7';
		$upgrade->partial_version = '';
		$upgrade->new_files       = 1;

		$autoupdate                  = new stdClass();
		$autoupdate->response        = 'autoupdate';
		$autoupdate->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.1.zip';
		$autoupdate->locale          = 'en_US';
		$autoupdate->packages        = '';
		$autoupdate->current         = '4.8.1';
		$autoupdate->version         = '4.8.1';
		$autoupdate->php_version     = '5.2.4';
		$autoupdate->mysql_version   = '5.0';
		$autoupdate->new_bundled     = '4.7';
		$autoupdate->partial_version = '';
		$autoupdate->new_files       = 1;

		$update_core                  = new stdClass();
		$update_core->updates         = array( $upgrade, $autoupdate );
		$update_core->last_checked    = 1506247076;
		$update_core->version_checked = '4.8.1';
		$update_core->translations    = array();

		global $wp_version;
		$now_wp_version = $wp_version;
		$wp_version     = '4.7.0';

		set_site_transient( 'update_core', $update_core );

		$options = array(
			'core' => 'minor',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_minor_auto_core_updates', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_minor_auto_core_updates', '__return_false' ) );

		$options = array(
			'core' => 'major',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'minor-only',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'pre-version',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_major_auto_core_updates', '__return_true' ) );
		$this->assertFalse( has_filter( 'pre_site_option_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );
		$this->assertFalse( has_filter( 'site_transient_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );

		$options = array(
			'core' => 'disable-auto-update',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_core', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'auto_update_core', '__return_false' ) );

	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_wordpress_core_case3() {
		$upgrade                  = new stdClass();
		$upgrade->response        = 'upgrade';
		$upgrade->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.1.zip';
		$upgrade->locale          = 'en_US';
		$upgrade->packages        = '';
		$upgrade->current         = '4.8.1';
		$upgrade->version         = '4.8.1';
		$upgrade->php_version     = '5.2.4';
		$upgrade->mysql_version   = '5.0';
		$upgrade->new_bundled     = '4.7';
		$upgrade->partial_version = '';
		$upgrade->new_files       = 1;

		$autoupdate                  = new stdClass();
		$autoupdate->response        = 'autoupdate';
		$autoupdate->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.1.zip';
		$autoupdate->locale          = 'en_US';
		$autoupdate->packages        = '';
		$autoupdate->current         = '4.8.1';
		$autoupdate->version         = '4.8.1';
		$autoupdate->php_version     = '5.2.4';
		$autoupdate->mysql_version   = '5.0';
		$autoupdate->new_bundled     = '4.7';
		$autoupdate->partial_version = '';
		$autoupdate->new_files       = 1;

		$update_core                  = new stdClass();
		$update_core->updates         = array( $upgrade, $autoupdate );
		$update_core->last_checked    = 1506247076;
		$update_core->version_checked = '4.8.1';
		$update_core->translations    = array();

		global $wp_version;
		$now_wp_version = $wp_version;
		$wp_version     = '4.7.1';

		set_site_transient( 'update_core', $update_core );

		$options = array(
			'core' => 'minor',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_minor_auto_core_updates', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_minor_auto_core_updates', '__return_false' ) );

		$options = array(
			'core' => 'major',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'minor-only',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'pre-version',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_major_auto_core_updates', '__return_true' ) );
		$this->assertFalse( has_filter( 'pre_site_option_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );
		$this->assertFalse( has_filter( 'site_transient_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );

		$options = array(
			'core' => 'disable-auto-update',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_core', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'auto_update_core', '__return_false' ) );

	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_wordpress_core_case4() {
		$upgrade                  = new stdClass();
		$upgrade->response        = 'upgrade';
		$upgrade->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.0.zip';
		$upgrade->locale          = 'en_US';
		$upgrade->packages        = '';
		$upgrade->current         = '4.8.0';
		$upgrade->version         = '4.8.0';
		$upgrade->php_version     = '5.2.4';
		$upgrade->mysql_version   = '5.0';
		$upgrade->new_bundled     = '4.7';
		$upgrade->partial_version = '';
		$upgrade->new_files       = 1;

		$autoupdate                  = new stdClass();
		$autoupdate->response        = 'autoupdate';
		$autoupdate->download        = 'https://downloads.wordpress.org/release/wordpress-4.8.0.zip';
		$autoupdate->locale          = 'en_US';
		$autoupdate->packages        = '';
		$autoupdate->current         = '4.8.0';
		$autoupdate->version         = '4.8.0';
		$autoupdate->php_version     = '5.2.4';
		$autoupdate->mysql_version   = '5.0';
		$autoupdate->new_bundled     = '4.7';
		$autoupdate->partial_version = '';
		$autoupdate->new_files       = 1;

		$update_core                  = new stdClass();
		$update_core->updates         = array( $upgrade, $autoupdate );
		$update_core->last_checked    = 1506247076;
		$update_core->version_checked = '4.8.0';
		$update_core->translations    = array();

		global $wp_version;
		$now_wp_version = $wp_version;
		$wp_version     = '4.7.1';

		set_site_transient( 'update_core', $update_core );

		$options = array(
			'core' => 'minor',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_minor_auto_core_updates', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_minor_auto_core_updates', '__return_false' ) );

		$options = array(
			'core' => 'major',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'minor-only',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_major_auto_core_updates', '__return_true' ) );

		$options = array(
			'core' => 'pre-version',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'allow_major_auto_core_updates', '__return_true' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertFalse( has_filter( 'allow_major_auto_core_updates', '__return_true' ) );
		$this->assertFalse( has_filter( 'pre_site_option_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );
		$this->assertFalse( has_filter( 'site_transient_update_core', array( $this->wp_auto_updater, 'updates_previous_version' ) ) );

		$options = array(
			'core' => 'disable-auto-update',
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_core', '__return_false' );
		$this->wp_auto_updater->auto_update_wordpress_core();
		$this->assertEquals( 10, has_filter( 'auto_update_core', '__return_false' ) );

	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function updates_previous_version() {
		$upgrade               = new stdClass();
		$upgrade->response     = 'upgrade';
		$upgrade->current      = '4.9.0';
		$autoupdate1           = new stdClass();
		$autoupdate1->response = 'autoupdate';
		$autoupdate1->current  = '4.9.0';
		$autoupdate2           = new stdClass();
		$autoupdate2->response = 'autoupdate';
		$autoupdate2->current  = '4.8.0';
		$autoupdate3           = new stdClass();
		$autoupdate3->response = 'autoupdate';
		$autoupdate3->current  = '4.7.0';

		$updates          = new stdClass();
		$updates->updates = array( $upgrade, $autoupdate1, $autoupdate2, $autoupdate3 );

		$updates = $this->wp_auto_updater->updates_previous_version( $updates );

		$this->assertEquals( '4.8.0', $updates->updates[1]->current );

		$updates = $this->wp_auto_updater->updates_previous_version( array() );
		$this->assertNull( $updates );
	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_theme() {
		$options = array(
			'theme' => true,
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_theme', array( $this->wp_auto_updater, 'auto_update_specific_theme' ) );
		$this->wp_auto_updater->auto_update_theme();

		$this->assertEquals( 10, has_filter( 'auto_update_theme', array( $this->wp_auto_updater, 'auto_update_specific_theme' ) ) );

		$options = array(
			'theme' => false,
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_theme', array( $this->wp_auto_updater, 'auto_update_specific_theme' ) );
		$this->wp_auto_updater->auto_update_theme();

		$this->assertFalse( has_filter( 'auto_update_theme', array( $this->wp_auto_updater, 'auto_update_specific_theme' ) ) );
	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_specific_theme() {
		$options = array(
			'disable_auto_update' => array(
				'themes'  => array(
					'twentysixteen',
					'twentyseventeen',
				),
				'plugins' => array(),
			),
		);

		update_option( 'wp_auto_updater_options', $options );

		$item              = new stdClass();
		$item->theme       = 'twentyfifteen';
		$item->new_version = '1.8';
		$item->url         = 'https://wordpress.org/themes/twentyfifteen/';
		$item->package     = 'https://downloads.wordpress.org/theme/twentyfifteen.1.8.zip';

		$updated = $this->wp_auto_updater->auto_update_specific_theme( false, $item );
		$this->assertTrue( $updated );

		$item              = new stdClass();
		$item->theme       = 'twentyseventeen';
		$item->new_version = '1.8';
		$item->url         = 'https://wordpress.org/themes/twentyseventeen/';
		$item->package     = 'https://downloads.wordpress.org/theme/twentyseventeen.1.8.zip';

		$updated = $this->wp_auto_updater->auto_update_specific_theme( false, $item );
		$this->assertFalse( $updated );

	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_plugin() {
		$options = array(
			'plugin' => true,
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_plugin', array( $this->wp_auto_updater, 'auto_update_specific_plugin' ) );
		$this->wp_auto_updater->auto_update_plugin();

		$this->assertEquals( 10, has_filter( 'auto_update_plugin', array( $this->wp_auto_updater, 'auto_update_specific_plugin' ) ) );

		$options = array(
			'plugin' => false,
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_plugin', array( $this->wp_auto_updater, 'auto_update_specific_plugin' ) );
		$this->wp_auto_updater->auto_update_plugin();

		$this->assertFalse( has_filter( 'auto_update_plugin', array( $this->wp_auto_updater, 'auto_update_specific_plugin' ) ) );
	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_specific_plugin() {
		$options = array(
			'disable_auto_update' => array(
				'themes'  => array(),
				'plugins' => array(
					'wp-multibyte-patch/wp-multibyte-patch.php',
				),
			),
		);

		update_option( 'wp_auto_updater_options', $options );

		$item                = new stdClass();
		$item->id            = 'w.org/plugins/akismet';
		$item->slug          = 'akismet';
		$item->plugin        = 'akismet/akismet.php';
		$item->new_version   = '4.0';
		$item->url           = 'https://wordpress.org/plugins/akismet/';
		$item->package       = 'https://downloads.wordpress.org/plugin/akismet.4.0.zip';
		$item->tested        = '4.8.1';
		$item->compatibility = '';

		$updated = $this->wp_auto_updater->auto_update_specific_plugin( false, $item );
		$this->assertTrue( $updated );

		$item                = new stdClass();
		$item->id            = 'w.org/plugins/wp-multibyte-patch';
		$item->slug          = 'wp-multibyte-patch';
		$item->plugin        = 'wp-multibyte-patch/wp-multibyte-patch.php';
		$item->new_version   = '2.8.1';
		$item->url           = 'https://wordpress.org/plugins/wp-multibyte-patch/';
		$item->package       = 'https://downloads.wordpress.org/plugin/wp-multibyte-patch.2.8.1.zip';
		$item->tested        = '4.8';
		$item->compatibility = '';

		$updated = $this->wp_auto_updater->auto_update_specific_plugin( false, $item );
		$this->assertFalse( $updated );

	}

	/**
	 * @test
	 * @group auto_update
	 */
	public function auto_update_translation() {
		$options = array(
			'translation' => false,
		);

		update_option( 'wp_auto_updater_options', $options );

		remove_filter( 'auto_update_translation', '__return_false' );
		$this->wp_auto_updater->auto_update_translation();

		$this->assertEquals( 10, has_filter( 'auto_update_translation', '__return_false' ) );

	}

}
