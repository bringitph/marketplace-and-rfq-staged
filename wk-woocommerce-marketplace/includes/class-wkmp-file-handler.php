<?php
/**
 * File handler class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Templates\Admin as AdminTemplates;
use WkMarketplace\Templates\Front as FrontTemplates;
use WkMarketplace\Includes\Front;
use WkMarketplace\Includes\Admin;
use WkMarketplace\Includes\Common;
use WkMarketplace\Separate_Dashboard as SellerDashboard;

if ( ! class_exists( 'WKMP_File_Handler' ) ) {

	/**
	 * File handler class
	 */
	class WKMP_File_Handler {

		/**
		 * File handler constructor
		 */
		public function __construct() {

			if ( is_admin() ) {
				new Admin\WKMP_Admin_Hooks();
				new Admin\WKMP_Admin_Ajax_Hooks();
				new AdminTemplates\WKMP_Admin_Template_Hooks();
			} else {
				new Front\WKMP_Front_Hooks();
				new Front\WKMP_Front_Action_Hooks();
				new FrontTemplates\WKMP_Front_Template_Hooks();
			}

			new WKMP_Query_Vars();
			new WKMP_Emails();
			new WKMP_Flat_Rate_Shipping();
			new WKMP_Free_Shipping();
			new WKMP_Local_Pickup_Shipping();
			new Front\WKMP_Front_Ajax_Hooks();
			new Common\WKMP_Common_Hooks();
			new Common\WKMP_Notification_Hooks();

			if ( get_option( '_wkmp_separate_seller_dashboard' ) && get_user_meta( get_current_user_id(), 'wkmp_seller_backend_dashboard', true ) ) {
				new SellerDashboard\WKMP_Seller_Backend_Hooks();
			}
		}
	}
}
