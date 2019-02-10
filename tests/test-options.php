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

		$this->assertEquals( $options, $expected );
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
		$this->assertEquals( $options['core'], 'minor' );

		$options = $this->wp_auto_updater->get_options( 'core' );
		$this->assertEquals( $options, 'minor' );

		$option = $this->wp_auto_updater->get_options( 'test' );
		$this->assertEquals( $option, null );
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
		$this->assertEquals( $options['core'], 'aaa' );

		add_filter( 'wp_auto_updater/get_option', array( $this, '_filter_option' ), 10, 2 );

		$options = $this->wp_auto_updater->get_options( 'core' );
		$this->assertEquals( $options, 'bbb' );
	}

	public function _filter_options( $options ) {
		$this->assertTrue( is_array( $options ) );

		$options['core'] = 'aaa';
		return $options;
	}

	public function _filter_option( $option, $name ) {
		$this->assertEquals( $option, 'minor' );
		$this->assertEquals( $name, 'core' );

		$option = 'bbb';
		return $option;
	}
}
