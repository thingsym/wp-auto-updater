<?php
/**
 * Class Test_Wp_Auto_Updater_Options
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Options extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_default() {
		$options  = $this->wp_auto_updater->get_options();
		$expected = array(
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

		$this->assertEquals( $expected, $options );
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_migrate() {
		$expected = array(
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

		$options = array();

		update_option( 'wp_auto_updater_options', $options );

		$options = $this->wp_auto_updater->get_options();
		$this->assertEquals( $expected, $options );

		$options = array(
			'disable_auto_update' => array(
				'themes'  => array(),
				'plugins' => array(),
			),
			'schedule'            => array(
			),
		);

		update_option( 'wp_auto_updater_options', $options );

		$options = $this->wp_auto_updater->get_options();
		$this->assertEquals( $expected, $options );

		$options = array(
			'core'                => 'minor',
			'theme'               => false,
			'disable_auto_update' => array(
				'themes'  => array(),
				'plugins' => array(),
			),
			'schedule'            => array(
				'interval' => 'twicedaily',
				'minute'   => 0,
			),
		);

		update_option( 'wp_auto_updater_options', $options );

		$options = $this->wp_auto_updater->get_options();
		$this->assertEquals( $expected, $options );
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_case_1() {
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

		$options = $this->wp_auto_updater->get_options();
		$this->assertEquals( 'minor', $options['core'] );

		$options = $this->wp_auto_updater->get_options( 'core' );
		$this->assertEquals( 'minor', $options );

		$option = $this->wp_auto_updater->get_options( 'test' );
		$this->assertNull( $option );
	}

	/**
	 * @test
	 * @group options
	 */
	public function get_options_case_filters() {
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

		add_filter( 'wp_auto_updater/get_options', array( $this, '_filter_options' ), 10 );

		$options = $this->wp_auto_updater->get_options();
		$this->assertEquals( 'aaa', $options['core'] );

		add_filter( 'wp_auto_updater/get_option', array( $this, '_filter_option' ), 10, 2 );

		$options = $this->wp_auto_updater->get_options( 'core' );
		$this->assertEquals( 'bbb', $options );
	}

	public function _filter_options( $options ) {
		$this->assertTrue( is_array( $options ) );

		$options['core'] = 'aaa';
		return $options;
	}

	public function _filter_option( $option, $name ) {
		$this->assertEquals( 'minor', $option );
		$this->assertEquals( 'core', $name );

		$option = 'bbb';
		return $option;
	}
}
