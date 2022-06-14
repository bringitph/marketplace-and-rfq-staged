<?php
/**
 * Front template hooks
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Front_Template_Hooks' ) ) {
	/**
	 * Admin template hooks
	 */
	class WKMP_Front_Template_Hooks {
		/**
		 * Constructor of the class
		 */
		public function __construct() {
			$function_handler = new WKMP_Front_Template_Functions();

			// Product by Feature on product page.
			add_action( 'woocommerce_single_product_summary', array( $function_handler, 'wkmp_product_by' ), 11 );

			// Customer Account menu.
			add_filter( 'query_vars', array( $function_handler, 'wkmp_add_query_vars' ) );
			add_filter( 'woocommerce_account_menu_items', array( $function_handler, 'wkmp_new_menu_items' ) );
			add_filter( 'the_title', array( $function_handler, 'wkmp_endpoint_title' ) );
			add_action( 'woocommerce_account_favourite-seller_endpoint', array( $function_handler, 'wkmp_endpoint_content' ) );
			add_action( 'woocommerce_account_become-mp-seller_endpoint', array( $function_handler, 'wkmp_mp_become_seller_endpoint_content' ) );

			add_action( 'wp_footer', array( $function_handler, 'wkmp_front_footer_templates' ) );
			add_filter( 'woocommerce_get_item_data', array( $function_handler, 'wkmp_add_sold_by_cart_data' ), 10, 2 );
			add_action( 'woocommerce_product_meta_start', array( $function_handler, 'wkmp_add_seller_prefix_to_sku' ) );
			add_action( 'woocommerce_product_meta_end', array( $function_handler, 'wkmp_remove_seller_prefix_to_sku' ) );
		}
	}
}
