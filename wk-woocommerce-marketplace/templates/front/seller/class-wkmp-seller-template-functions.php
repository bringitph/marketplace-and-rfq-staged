<?php
/**
 * Seller template functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Front;


if ( ! class_exists( 'WKMP_Seller_Template_Functions' ) ) {

	/**
	 * Seller template functions class
	 */
	class WKMP_Seller_Template_Functions {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id = '';

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			$this->seller_id = get_current_user_id();
		}

		/**
		 * Callback method for seller Dashboard
		 *
		 * @return void
		 */
		public function wkmp_seller_dashboard() {
			$obj = new Dashboard\WKMP_Dashboard();
			$obj->wkmp_dashboard_page();
		}

		/**
		 * Callback method for seller Product List
		 *
		 * @return void
		 */
		public function wkmp_seller_product_list() {
			new Product\WKMP_Product_List( $this->seller_id );
		}

		/**
		 * Callback method for seller Product Form
		 *
		 * @return void
		 */
		public function wkmp_seller_product_form() {
			new Product\WKMP_Product_Form( $this->seller_id );
		}

		/**
		 * Callback method for seller Order History
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_seller_order_history() {
			new Orders\WKMP_Orders( $this->seller_id );
		}

		/**
		 * Callback method for print invoice
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_seller_order_invoice() {
			new Orders\WKMP_Orders( $this->seller_id, true );
		}

		/**
		 * Callback method for seller Transaction
		 *
		 * @return void
		 */
		public function wkmp_seller_transaction() {
			new Transaction\WKMP_Transactions( $this->seller_id );
		}

		/**
		 * Callback method for seller Shipping
		 *
		 * @return void
		 */
		public function wkmp_seller_shipping() {
			if ( 'disabled' !== get_option( 'woocommerce_ship_to_countries', false ) ) {
				new Shipping\WKMP_Shipping( $this->seller_id );
			} else {
				echo '<div class="woocommerce-message woocommerce-error">';
				esc_html_e( 'Shipping is not enabled by the Admin.', 'wk-marketplace' );
				echo '</div>';
			}
		}

		/**
		 * Callback method for seller Profile
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_edit() {
			new Profile\WKMP_Profile_Edit( $this->seller_id );
		}

		/**
		 * Callback method for seller Notification
		 *
		 * @return void
		 */
		public function wkmp_seller_notification() {
			new WKMP_Notification( $this->seller_id );
		}

		/**
		 * Callback method for seller Shop Follower
		 *
		 * @return void
		 */
		public function wkmp_seller_shop_follower() {
			new WKMP_Shop_Follower( $this->seller_id );
		}

		/**
		 * Callback method for seller Ask To admin
		 *
		 * @return void
		 */
		public function wkmp_seller_asktoadmin() {
			new WKMP_Ask_To_Admin( $this->seller_id );
		}

		/**
		 * Callback method for seller profile info
		 * This method is call bydefault
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_info() {
			new Profile\WKMP_Profile_Info( $this->seller_id );
		}

		/**
		 * Callback method for seller Store page
		 *
		 * @return void
		 */
		public function wkmp_seller_store_info() {
			new Store\WKMP_Seller_Store_Info();
		}
	}
}
