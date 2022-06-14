<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Front_Action_Functions' ) ) {
	/**
	 * Front action function class
	 */
	class WKMP_Front_Action_Functions {

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Front_Action_Functions constructor.
		 */
		public function __construct() {

		}

		/**
		 * Save seller ask query info.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $data Query info.
		 */
		public function wkmp_save_seller_ask_query( $seller_id, $data ) {
			$obj = new Common\WKMP_Seller_Ask_Queries();
			$obj->wkmp_save_seller_ask_query( $seller_id, $data );
		}

		/**
		 * Seller feedback.
		 *
		 * @param array $data Data.
		 * @param int   $seller_id Seller id.
		 */
		public function wkmp_save_seller_feedback( $data, $seller_id ) {
			$obj             = new Common\WKMP_Seller_Feedback();
			$wkmp_first_name = get_user_meta( $seller_id, 'first_name', true );

			if ( empty( $wkmp_first_name ) ) {
				$user_details     = get_user_by( 'ID', $data['mp_wk_user'] );
				$data['nickname'] = $user_details->display_name;
			} else {
				$data['nickname'] = $wkmp_first_name . ' ' . get_user_meta( $seller_id, 'last_name', true );
			}

			$obj->wkmp_save_seller_feedback( $data, $seller_id );
		}
	}
}
