<?php
/**
 * Class Test_Wp_Auto_Float_Version
 *
 * @package Wp_Auto_Updater
 */

class Test_Wp_Auto_Float_Version extends WP_UnitTestCase {
	public $wp_auto_updater;

	public function setUp(): void {
		parent::setUp();
		$this->wp_auto_updater = new WP_Auto_Updater();
	}

	/**
	 * @test
	 * @group float_version
	 */
	public function pre_version_case_true_diff_2() {
		$epsilon = 0.00001;

		$old_core_version    = '4.5.1';
		$old_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $old_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $old_core_version ) );
		$this->assertTrue( is_string( $old_core_version_xy ) );

		$new_core_version    = '4.7.3';
		$new_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $new_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $new_core_version_xy ) );

		$version_diff = floatval( $new_core_version_xy ) - floatval( $old_core_version_xy );
		$float_diff   = abs( $version_diff - 0.2 );

		$this->assertTrue( is_float( $version_diff ) );
		$this->assertTrue( is_float( $float_diff ) );

		$this->assertGreaterThan( $float_diff, $epsilon );
	}

	/**
	 * @test
	 * @group float_version
	 */
	public function pre_version_case_false_diff_1() {
		$epsilon = 0.00001;

		$old_core_version    = '4.5.1';
		$old_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $old_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $old_core_version ) );
		$this->assertTrue( is_string( $old_core_version_xy ) );

		$new_core_version    = '4.6.3';
		$new_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $new_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $new_core_version_xy ) );

		$version_diff = floatval( $new_core_version_xy ) - floatval( $old_core_version_xy );
		$float_diff   = abs( $version_diff - 0.2 );

		$this->assertTrue( is_float( $version_diff ) );
		$this->assertTrue( is_float( $float_diff ) );

		$this->assertLessThan( $float_diff, $epsilon );
	}

	/**
	 * @test
	 * @group float_version
	 */
	public function pre_version_case_false__diff_0() {
		$epsilon = 0.00001;

		$old_core_version    = '4.5.1';
		$old_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $old_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $old_core_version ) );
		$this->assertTrue( is_string( $old_core_version_xy ) );

		$new_core_version    = '4.5.3';
		$new_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $new_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $new_core_version_xy ) );

		$version_diff = floatval( $new_core_version_xy ) - floatval( $old_core_version_xy );
		$float_diff   = abs( $version_diff - 0.2 );

		$this->assertTrue( is_float( $version_diff ) );
		$this->assertTrue( is_float( $float_diff ) );

		$this->assertLessThan( $float_diff, $epsilon );
	}

	/**
	 * @test
	 * @group float_version
	 */
	public function pre_version_case_true_diff_3() {
		$epsilon = 0.00001;

		$old_core_version    = '4.5.1';
		$old_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $old_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $old_core_version ) );
		$this->assertTrue( is_string( $old_core_version_xy ) );

		$new_core_version    = '4.8.3';
		$new_core_version_xy = implode( '.', array_slice( preg_split( '/[.-]/', $new_core_version ), 0, 2 ) );

		$this->assertTrue( is_string( $new_core_version_xy ) );

		$version_diff = floatval( $new_core_version_xy ) - floatval( $old_core_version_xy );
		$float_diff   = abs( $version_diff - 0.2 );

		$this->assertTrue( is_float( $version_diff ) );
		$this->assertTrue( is_float( $float_diff ) );

		$this->assertLessThan( $float_diff, $epsilon );
	}

}
