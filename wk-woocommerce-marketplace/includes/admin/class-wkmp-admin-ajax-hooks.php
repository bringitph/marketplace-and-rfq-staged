<?php
/**
 * Admin End Hooks
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Admin_Ajax_Hooks' ) ) {

	/**
	 * Admin hooks class.
	 *
	 * Class WKMP_Admin_Ajax_Hooks
	 *
	 * @package WkMarketplace\Includes\Admin
	 */
	class WKMP_Admin_Ajax_Hooks {
		/**
		 * Admin hooks constructor.
		 *
		 * WKMP_Admin_Ajax_Hooks constructor.
		 */
		public function __construct() {
			$function_handler = new WKMP_Admin_Ajax_Functions();

			add_action( 'wp_ajax_wkmp_approve_seller', array( $function_handler, 'wkmp_approve_seller' ) );
			add_action( 'wp_ajax_wkmp_admin_replied_to_seller', array( $function_handler, 'wkmp_admin_replied_to_seller' ) );
			add_action( 'wp_ajax_wkmp_check_myshop', array( $function_handler, 'wkmp_check_myshop_value' ) );
			add_action( 'wp_ajax_wkmp_change_seller_dashboard', array( $function_handler, 'wkmp_change_seller_dashboard' ) );
			add_action( 'wp_ajax_wkmp_update_seller_order_status', array( $function_handler, 'wkmp_update_seller_order_status' ) );
		}
	}
}
