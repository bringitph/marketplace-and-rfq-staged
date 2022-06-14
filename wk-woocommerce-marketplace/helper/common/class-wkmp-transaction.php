<?php
/**
 * Seller ask queries class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Transaction' ) ) {

	/**
	 * Seller ask query related queries class
	 */
	class WKMP_Transaction {

		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * * Generate Transaction.
		 *
		 * @param int    $seller_id Seller ID.
		 * @param int    $order_id Order ID.
		 * @param int    $amount Order Item ID.
		 * @param string $transaction_id Transaction id.
		 *
		 * @return bool|int|string
		 */
		public function wkmp_generate_transaction( $seller_id, $order_id, $amount, $transaction_id = '' ) {
			$response = '';

			if ( empty( $transaction_id ) ) {
				$order_password = get_post_field( 'post_password', $order_id );
				$replace        = 'tr-' . esc_attr( $seller_id );
				if ( ! empty( $order_password ) ) {
					$transaction_id = str_replace( 'order_', $replace, $order_password );
				}
			}

			if ( ! empty( $transaction_id ) ) {
				$response = $this->wpdb->insert(
					"{$this->wpdb->prefix}seller_transaction",
					array(
						'transaction_id'   => $transaction_id,
						'order_id'         => maybe_serialize( $order_id ),
						'seller_id'        => $seller_id,
						'amount'           => $amount,
						'type'             => 'manual',
						'method'           => 'manual',
						'transaction_date' => gmdate( 'Y-m-d H:i:s' ),
					),
					array( '%s', '%d', '%d', '%f', '%s', '%s', '%s' ) 
				);
			}

			return $response;
		}

		/**
		 * Get Seller Transaction.
		 *
		 * @param int $filter_data Filter data.
		 * @param int $seller_id Seller ID.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_transactions( $filter_data, $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}seller_transaction WHERE seller_id=%d", $seller_id );

			if ( isset( $filter_data['filter_transaction_id'] ) && $filter_data['filter_transaction_id'] ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_id LIKE %s', esc_attr( $filter_data['filter_transaction_id'] ) . '%' );
			}

			$sql .= $wpdb_obj->prepare( ' ORDER BY transaction_date DESC LIMIT %d, %d', esc_attr( $filter_data['start'] ), esc_attr( $filter_data['limit'] ) );

			$transactions = $wpdb_obj->get_results( $sql );

			return apply_filters( 'wkmp_get_seller_transactions', $transactions, $seller_id );
		}

		/**
		 * Get Seller Transaction.
		 *
		 * @param array $filter_data Filter data.
		 * @param int   $seller_id Seller id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_seller_total_transactions( $filter_data, $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$this->wpdb->prefix}seller_transaction WHERE seller_id =%d", $seller_id );

			if ( isset( $filter_data['filter_transaction_id'] ) && $filter_data['filter_transaction_id'] ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_id LIKE %s', esc_attr( $filter_data['filter_transaction_id'] ) . '%' );
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_seller_transactions', $total, $seller_id );
		}

		/**
		 * Get Transaction Detail.
		 *
		 * @param int $id Id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_transaction_details_by_id( $id ) {
			$wpdb_obj = $this->wpdb;
			$result   = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}seller_transaction WHERE id = %d", esc_attr( $id ) ) );

			return apply_filters( 'wkmp_get_transaction_details_by_id', $result, $id );
		}
	}
}
