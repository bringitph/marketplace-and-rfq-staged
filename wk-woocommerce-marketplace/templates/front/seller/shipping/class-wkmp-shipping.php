<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Shipping;

use WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Shipping' ) ) {
	/**
	 * Seller products class
	 */
	class WKMP_Shipping {
		/**
		 * DB Shipping Object.
		 *
		 * @var Front\WKMP_Shipping_Queries $db_shipping_obj DB Shipping Object.
		 */
		private $db_shipping_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Shipping constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->db_shipping_obj = new Front\WKMP_Shipping_Queries();
			$this->seller_id       = $seller_id;
			$posted_data           = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $posted_data['shipping_nonce'] ) && ! empty( $posted_data['shipping_nonce'] ) ) {
				if ( ! wp_verify_nonce( $posted_data['shipping_nonce'], 'shipping_action' ) ) {
					wc_add_notice( esc_html__( 'Sorry, your nonce did not verify.', 'wk-marketplace' ), 'error' );
				} else {
					if ( isset( $posted_data['save_shipping_details'] ) && ! empty( $posted_data['save_shipping_details'] ) || isset( $posted_data['update_shipping_details'] ) && ! empty( $posted_data['update_shipping_details'] ) ) {
						$this->db_shipping_obj->wkmp_save_shipping_details( $posted_data );
					}
				}
			}

			$wkmp_ship_edit = filter_input( INPUT_GET, 'wkmp_ship_edit', FILTER_SANITIZE_NUMBER_INT );
			$action         = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

			if ( 'add' === $action && empty( $posted_data['save_shipping_details'] ) ) {
				$this->wkmp_add_shipping();
			} elseif ( $wkmp_ship_edit > 0 ) {
				$this->wkmp_edit_shipping( $wkmp_ship_edit );
			} else {
				$this->wkmp_shipping_list();
				unset( $_POST );
			}
		}

		/**
		 * Add seller shipping.
		 *
		 * @return void
		 */
		public function wkmp_add_shipping() {
			require_once __DIR__ . '/wkmp-add-shipping.php';
		}

		/**
		 * Edit seller shipping.
		 *
		 * @param int $zone_id Zone id.
		 *
		 * @return void
		 */
		public function wkmp_edit_shipping( $zone_id ) {
			global $wpdb;
			$main_page         = get_query_var( 'main_page' );
			$zones             = new \WC_Shipping_Zone( $zone_id );
			$shop_address      = get_user_meta( $this->seller_id, 'shop_address', true );
			$seller_zone_check = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_meta WHERE seller_id = %d AND zone_id = %d", $this->seller_id, $zone_id ) );
			$zone_name         = $zones->get_zone_name();
			$zone_locations    = $zones->get_zone_locations();

			require_once __DIR__ . '/wkmp-edit-shipping.php';
		}

		/**
		 * Seller shipping list
		 *
		 * @return void
		 */
		public function wkmp_shipping_list() {
			global $wkmarketplace;
			$seller_info = $wkmarketplace->wkmp_get_seller_info( $this->seller_id );

			require_once __DIR__ . '/wkmp-shipping-list.php';
		}
	}
}
