<?php
/**
 * Seller Data Helper
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Admin;

use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WKMP_Seller_Order_Data' ) ) {

	/**
	 * Seller List Class
	 */
	class WKMP_Seller_Order_Data {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Constructor
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Get seller orders info
		 *
		 * @param int   $seller_id seller id.
		 * @param array $filter_data filter data.
		 *
		 * @return array $data
		 */
		public function wkmp_get_seller_orders( $seller_id, $filter_data = array() ) {
			$wpdb_obj   = $this->wpdb;
			$data       = array();
			$sort_order = empty( $filter_data['sorting_order'] ) ? 'desc' : $filter_data['sorting_order'];
			$sql        = $wpdb_obj->prepare( "SELECT mp.order_id FROM {$wpdb_obj->prefix}mporders mp LEFT JOIN {$wpdb_obj->prefix}posts p ON (mp.order_id = p.ID) WHERE mp.seller_id = %1d AND p.post_status = 'wc-completed' ORDER BY mp.order_id %2s", $seller_id, $sort_order );

			$sql .= $wpdb_obj->prepare( ' LIMIT %d, %d', esc_attr( $filter_data['start'] ), esc_attr( $filter_data['limit'] ) );

			$seller_orders = $wpdb_obj->get_results( $sql );
			$order_ids     = wp_list_pluck( $seller_orders, 'order_id' );
			$order_ids     = array_unique( $order_ids );

			$mp_commission = new Common\WKMP_Commission();

			foreach ( $order_ids as $order_id ) {
				$seller_order = wc_get_order( $order_id );
				$order_status = $seller_order instanceof \WC_Order ? $seller_order->get_status() : '';

				if ( 'completed' === $order_status ) {
					$data[] = $mp_commission->wkmp_get_seller_final_order_info( $order_id, $seller_id );
				}
			}

			return apply_filters( 'wkmp_get_seller_orders', $data, $seller_id );
		}

		/**
		 * Get seller orders info.
		 *
		 * @param int $seller_id seller id.
		 *
		 * @return int
		 */
		public function wkmp_get_total_seller_orders( $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$total    = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(mp.order_id) FROM {$wpdb_obj->prefix}mporders mp LEFT JOIN {$wpdb_obj->prefix}posts p ON (mp.order_id = p.ID) WHERE mp.seller_id =%d AND p.post_status = 'wc-completed'", intval( $seller_id ) ) );

			return apply_filters( 'wkmp_get_total_seller_orders', $total, $seller_id );
		}

		/**
		 * Get seller order pay status
		 *
		 * @param int $seller_id seller id.
		 * @param int $order_id order id.
		 *
		 * @return boolean $pay_status
		 */
		public function wkmp_get_order_pay_status( $seller_id, $order_id ) {
			$wpdb_obj   = $this->wpdb;
			$pay_status = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = %s", esc_attr( $seller_id ), esc_attr( $order_id ), esc_attr( 'paid_status' ) ) );

			return apply_filters( 'wkmp_get_order_pay_status', $pay_status, $seller_id, $order_id );
		}

		/**
		 * Update seller paid status
		 *
		 * @param array $data data.
		 *
		 * @return void
		 */
		public function wkmp_update_seller_order_pay_status( $data ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = empty( $data['seller_id'] ) ? 0 : $data['seller_id'];
			$order_id  = empty( $data['order_id'] ) ? 0 : $data['order_id'];

			if ( ! empty( $seller_id ) && ! empty( $order_id ) ) {
				$meta_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT `mmid` FROM {$wpdb_obj->prefix}mporders_meta WHERE `seller_id` = %s AND `order_id`=%s AND `meta_key`='paid_status'", $seller_id, $order_id ) );
				if ( $meta_id > 0 ) {
					$wpdb_obj->update( $wpdb_obj->prefix . 'mporders_meta', $data, array( 'mmid' => $meta_id ) );
				} else {
					$wpdb_obj->insert( $wpdb_obj->prefix . 'mporders_meta', $data, array( '%d', '%d', '%s', '%s' ) );
				}
			}
		}

		/**
		 * Updated seller order status
		 *
		 * @param int $order_id order id.
		 * @param int $status status.
		 *
		 * @return void
		 */
		public function wkmp_update_order_status_on_changed( $order_id, $status ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->update( $wpdb_obj->prefix . 'mpseller_orders', array( 'order_status' => 'wc-' . $status ), array( 'order_id' => $order_id ), array( '%s' ), array( '%d' ) );
		}

		/**
		 * Save product info.
		 *
		 * @param int    $order_id order id.
		 * @param int    $seller_id seller id.
		 * @param string $action Action.
		 */
		public function wkmp_update_seller_order_status( $order_id, $seller_id, $action ) {
			global $wkmarketplace;

			$mp_commission  = new Common\WKMP_Commission();
			$mp_transaction = new Common\WKMP_Transaction();
			$new_status     = ( 'pay' === $action ) ? 'paid' : 'approved';
			$new_status     = ( 'disapprove' === $action ) ? 'disapproved' : $new_status;

			$paid_status = $this->wkmp_get_order_pay_status( $seller_id, $order_id );

			$wkmarketplace->log( "Update order status request for order id: $order_id, Seller id: $seller_id, Action: $action, New status: $new_status, Paid status: $paid_status " );

			if ( in_array( $paid_status, array( 'paid', 'disapproved' ), true ) ) {
				return;
			}

			if ( ! empty( $order_id ) && $new_status !== $paid_status ) {
				$update_data = array(
					'seller_id'  => $seller_id,
					'order_id'   => $order_id,
					'meta_key'   => 'paid_status',
					'meta_value' => $new_status,
				);

				$this->wkmp_update_seller_order_pay_status( $update_data );

				$amount = 0;
				if ( 'paid' === $new_status ) {
					$amount += $mp_commission->wkmp_update_seller_commission( $seller_id, $order_id );

					if ( $amount ) {
						$mp_transaction->wkmp_generate_transaction( $seller_id, $order_id, $amount, '' );
					}
				}

				$wkmarketplace->log( "Request processed for order id: $order_id with update data: " . print_r( $update_data, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
		}
	}
}
