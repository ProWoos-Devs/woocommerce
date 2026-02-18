<?php
/**
 * Tests for Product List Tables in WooCommerce Admin
 */

declare( strict_types = 1 );

require_once WC_ABSPATH . '/includes/admin/list-tables/class-wc-admin-list-table-products.php';

/**
 * WC Admin List Table Products test
 */
class WC_Admin_List_Table_Products_Test extends WC_Unit_Test_Case {

	/**
	 * Test that the featured status filter renders correctly.
	 */
	public function test_featured_filter_renders_dropdown() {
		$GLOBALS['pagenow'] = 'edit.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$list_table = new WC_Admin_List_Table_Products();

		ob_start();
		// Use reflection to call the protected method.
		$method = new ReflectionMethod( WC_Admin_List_Table_Products::class, 'render_products_featured_filter' );
		$method->setAccessible( true );
		$method->invoke( $list_table );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<select name="featured_status">', $output );
		$this->assertStringContainsString( 'value="featured"', $output );
		$this->assertStringContainsString( 'value="not-featured"', $output );

		// Cleanup.
		unset( $GLOBALS['pagenow'] );
	}

	/**
	 * Test that filtering by featured status returns only featured products.
	 */
	public function test_featured_filter_returns_featured_products() {
		// Create a featured product.
		$featured_product = WC_Helper_Product::create_simple_product();
		$featured_product->set_featured( true );
		$featured_product->save();

		// Create a non-featured product.
		$regular_product = WC_Helper_Product::create_simple_product();
		$regular_product->set_featured( false );
		$regular_product->save();

		$_GET['featured_status'] = 'featured';
		$GLOBALS['pagenow']      = 'edit.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$list_table = new WC_Admin_List_Table_Products();
		$query      = new WP_Query(
			array(
				'post_type'   => 'product',
				'post_status' => 'all',
				'fields'      => 'ids',
			)
		);

		$results = $query->get_posts();

		$this->assertContains( $featured_product->get_id(), $results, 'Featured product should appear when filtering by featured.' );
		$this->assertNotContains( $regular_product->get_id(), $results, 'Non-featured product should not appear when filtering by featured.' );

		// Cleanup.
		unset( $_GET['featured_status'], $GLOBALS['pagenow'] );
		wp_delete_post( $featured_product->get_id(), true );
		wp_delete_post( $regular_product->get_id(), true );
	}

	/**
	 * Test that filtering by not-featured status excludes featured products.
	 */
	public function test_featured_filter_returns_not_featured_products() {
		// Create a featured product.
		$featured_product = WC_Helper_Product::create_simple_product();
		$featured_product->set_featured( true );
		$featured_product->save();

		// Create a non-featured product.
		$regular_product = WC_Helper_Product::create_simple_product();
		$regular_product->set_featured( false );
		$regular_product->save();

		$_GET['featured_status'] = 'not-featured';
		$GLOBALS['pagenow']      = 'edit.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$list_table = new WC_Admin_List_Table_Products();
		$query      = new WP_Query(
			array(
				'post_type'   => 'product',
				'post_status' => 'all',
				'fields'      => 'ids',
			)
		);

		$results = $query->get_posts();

		$this->assertContains( $regular_product->get_id(), $results, 'Non-featured product should appear when filtering by not-featured.' );
		$this->assertNotContains( $featured_product->get_id(), $results, 'Featured product should not appear when filtering by not-featured.' );

		// Cleanup.
		unset( $_GET['featured_status'], $GLOBALS['pagenow'] );
		wp_delete_post( $featured_product->get_id(), true );
		wp_delete_post( $regular_product->get_id(), true );
	}

	/**
	 * Test that the featured filter is included in the default filters array.
	 */
	public function test_featured_filter_is_registered() {
		$GLOBALS['pagenow'] = 'edit.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$list_table = new WC_Admin_List_Table_Products();

		// Capture the filters output to verify the featured filter is rendered.
		ob_start();
		$method = new ReflectionMethod( WC_Admin_List_Table_Products::class, 'render_filters' );
		$method->setAccessible( true );
		$method->invoke( $list_table );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'featured_status', $output, 'Featured filter should be present in the rendered filters.' );

		// Cleanup.
		unset( $GLOBALS['pagenow'] );
	}
}
