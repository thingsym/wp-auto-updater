<?php
/**
 * Class Test_Wp_Auto_Updater_Option_Page
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Updater_Option_Page extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function capability() {
		$this->assertEquals( 'update_core', $this->wp_auto_updater->option_page_capability() );
	}

	/**
	 * @test
	 * @group options_page
	 */
	public function options_page() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}
}
