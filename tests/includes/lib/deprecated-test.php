<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Unit tests covering deprecated functions.
 *
 * @package Papi
 */

class Papi_Lib_Deprecated_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$_GET = [];

		add_action( 'deprecated_function_run', [$this, 'deprecated_function_run'] );
		tests_add_filter( 'papi/settings/directories', function () {
			return [1,  PAPI_FIXTURE_DIR . '/page-types'];
		} );

		$this->post_id = $this->factory->post->create();
		update_post_meta( $this->post_id, PAPI_PAGE_TYPE_KEY, 'simple-page-type' );
	}

	public function deprecated_function_run( $function ) {
		add_filter( 'deprecated_function_trigger_error', '__return_false' );
	}

	public function tearDown() {
		parent::tearDown();
		remove_filter( 'deprecated_function_trigger_error', '__return_false' );
		unset( $_GET, $this->post_id );
	}

	/**
	 * `papi_field` is deprecated since 2.0.0
	 */

	public function test_papi_field() {
		update_post_meta( $this->post_id, 'name', 'fredrik' );

		$this->assertNull( papi_field( '' ) );
		$this->assertNull( papi_field( $this->post_id, '' ) );

		$this->assertEquals( 'fredrik', papi_field( $this->post_id, 'name' ) );
		$this->assertEquals( 'fredrik', papi_field( $this->post_id, 'name', '', 'post' ) );

		$this->assertEquals( 'world', papi_field( $this->post_id, 'hello', 'world' ) );

		$_GET['post_id'] = $this->post_id;
		$this->assertNull( papi_field( 'name' ) );
		$this->assertEquals( 'fredrik', papi_field( '', 'fredrik' ) );
	}
}
