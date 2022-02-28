<?php
/**
 * Class Test_Wp_Auto_Updater_Validate
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Validate extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group validate
	 */
	public function validate_case_none_input() {
		$new_input = array();
		$expected  = array(
			'core'                => null,
			'theme'               => false,
			'plugin'              => false,
			'translation'         => false,
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

		$output = $this->wp_auto_updater->validate_options( $new_input );

		$this->assertSame( $expected, $output );
	}

	/**
	 * @test
	 * @group validate
	 */
	public function validate_case_initial() {
		$new_input = array(
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
		$expected  = array(
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

		$output = $this->wp_auto_updater->validate_options( $new_input );

		$this->assertSame( $expected, $output );
	}

	/**
	 * @test
	 * @group validate
	 */
	public function validate_case_filter() {
		$new_input = array(
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
		$expected  = array(
			'core'                => 'aaa',
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

		add_filter( 'wp_auto_updater/validate_options', array( $this, '_filter_options' ), 10, 3 );

		$output = $this->wp_auto_updater->validate_options( $new_input );

		$this->assertSame( $expected, $output );
	}

	public function _filter_options( $output, $input, $default_options ) {
		$this->assertTrue( is_array( $output ) );
		$this->assertTrue( is_array( $input ) );
		$this->assertTrue( is_array( $default_options ) );

		$output['core'] = 'aaa';
		return $output;
	}
}
