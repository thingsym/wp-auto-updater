<?php
/**
 * Class Test_Wp_Auto_Updater_Multisite
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Multisite extends WP_UnitTestCase {
	public $wp_auto_updater;

	public function setUp(): void {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group multisite
	 */
	public function multisite() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
