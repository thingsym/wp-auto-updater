<?php
/**
 * Class Test_Wp_Auto_Updater_Multisite
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Multisite extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group multisite
	 */
	public function multisite() {
	}

}
