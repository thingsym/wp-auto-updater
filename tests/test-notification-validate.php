<?php
/**
 * Class Test_Wp_Auto_Updater_Notification_Validate
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Notification_Validate extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater_notification = new WP_Auto_Updater_Notification();
	}

	/**
	 * @test
	 * @group validate
	 */
	public function validate_case_none_input() {
		$new_input = array();
		$expected  = array(
			'notification' => array(
				'core'        => false,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);

		$output = $this->wp_auto_updater_notification->validate_options( $new_input );

		$this->assertEquals( $expected, $output );
	}

	/**
	 * @test
	 * @group validate
	 */
	public function validate_case_initial() {
		$new_input = array(
			'notification' => array(
				'core'        => true,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);
		$expected  = array(
			'notification' => array(
				'core'        => true,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);

		$output = $this->wp_auto_updater_notification->validate_options( $new_input );

		$this->assertEquals( $expected, $output );
	}

	/**
	 * @test
	 * @group validate
	 */
	public function validate_case_filter() {
		$new_input = array(
			'notification' => array(
				'core'        => true,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);
		$expected  = array(
			'notification' => array(
				'core'        => false,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);

		add_filter( 'wp_auto_updater_notification/validate_options', array( $this, '_filter_options' ), 10, 3 );

		$output = $this->wp_auto_updater_notification->validate_options( $new_input );

		$this->assertEquals( $expected, $output );
	}

	public function _filter_options( $output, $input, $default_options ) {
		$this->assertTrue( is_array( $output ) );
		$this->assertTrue( is_array( $input ) );
		$this->assertTrue( is_array( $default_options ) );

		$output['notification']['core'] = false;
		return $output;
	}

	/**
	 * @test
	 * @group validate
	 */
	public function validate_case_nobody_recipients() {
		$new_input = array(
			'notification' => array(
				'core'        => true,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => null,
				'recipients'  => null,
			),
		);
		$expected  = array(
			'notification' => array(
				'core'        => true,
				'theme'       => false,
				'plugin'      => false,
				'translation' => false,
			),
			'mail' => array(
				'from'        => '',
				'admin_email' => true,
				'recipients'  => array(),
			),
		);

		$output = $this->wp_auto_updater_notification->validate_options( $new_input );

		$this->assertEquals( $expected, $output );
	}

}
