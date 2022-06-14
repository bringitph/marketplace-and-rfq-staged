<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Notification_Hooks' ) ) {
	/**
	 * Class WKMP_Notification_Hooks
	 *
	 * @package WkMarketplace\Includes\Common
	 */
	class WKMP_Notification_Hooks {

		/**
		 * WKMP_Notification_Hooks constructor.
		 */
		public function __construct() {
			$function_handler = new WKMP_Notification_Functions();
			add_action( 'woocommerce_checkout_order_processed', array( $function_handler, 'wkmp_custom_process_order' ), 999, 1 );
			add_action( 'transition_post_status', array( $function_handler, 'wkmp_save_on_product_update' ), 10, 3 );
			add_action( 'wkmp_save_seller_review_notification', array( $function_handler, 'wkmp_save_seller_review_notification' ), 10, 2 );
			add_action( 'woocommerce_low_stock_notification', array( $function_handler, 'wkmp_low_stock' ) );
			add_action( 'woocommerce_no_stock_notification', array( $function_handler, 'wkmp_no_stock' ) );
			add_action( 'woocommerce_order_status_processing', array( $function_handler, 'wkmp_order_processing_notification' ), 10, 1 );
			add_action( 'woocommerce_order_status_completed', array( $function_handler, 'wkmp_order_completed_notification' ), 10, 1 );
		}
	}
}
