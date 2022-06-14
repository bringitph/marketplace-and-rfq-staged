<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Transaction;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Transaction' ) ) {
	/**
	 * Seller products class
	 */
	class WKMP_Transactions {
		/**
		 * Transaction DB Object.
		 *
		 * @var Common\WKMP_Transaction
		 */
		private $transaction_db_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class
		 *
		 * WKMP_Transactions constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->transaction_db_obj = new Common\WKMP_Transaction();
			$this->seller_id          = $seller_id;
			if ( 'view' === get_query_var( 'action' ) && get_query_var( 'pid' ) ) {
				$this->wkmp_transaction_view( get_query_var( 'pid' ) );
			} else {
				$this->wkmp_transaction_list();
			}
		}

		/**
		 * Marketplace Seller transaction view
		 *
		 * @param int $id Transaction id.
		 */
		public function wkmp_transaction_view( $id ) {
			$commission       = new Common\WKMP_Commission();
			$transaction_info = $this->transaction_db_obj->wkmp_get_transaction_details_by_id( $id );
			$order_info       = wc_get_order( $transaction_info->order_id );
			$columns          = apply_filters(
				'wkmp_account_transactions_columns',
				array(
					'order_id'     => esc_html__( 'Order Id', 'wk-marketplace' ),
					'product_name' => esc_html__( 'Product Name', 'wk-marketplace' ),
					'quantity'     => esc_html__( 'Quantity', 'wk-marketplace' ),
					'price'        => esc_html__( 'Total Price', 'wk-marketplace' ),
					'commission'   => esc_html__( 'Commission', 'wk-marketplace' ),
					'subtotal'     => esc_html__( 'Subtotal', 'wk-marketplace' ),
				)
			);

			$seller_order_info = $commission->wkmp_get_seller_final_order_info( $transaction_info->order_id, $this->seller_id );

			$product_name = '';

			foreach ( $seller_order_info['product'] as $pro_nme ) {
				if ( ! empty( $product_name ) ) {
					$product_name = $product_name . ' + ';
				}
				$product_name = $product_name . $pro_nme['title'];
			}

			require_once __DIR__ . '/wkmp-transaction-view.php';
		}

		/**
		 * Marketplace Seller transaction list
		 *
		 * @return void
		 */
		public function wkmp_transaction_list() {
			global $wkmarketplace;

			$get_data    = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filter_name = '';

			// Filter transactions.
			if ( isset( $get_data['wkmp_transaction_search_nonce'] ) && ! empty( $get_data['wkmp_transaction_search_nonce'] ) && wp_verify_nonce( wp_unslash( $get_data['wkmp_transaction_search_nonce'] ), 'wkmp_transaction_search_nonce_action' ) ) {
				if ( isset( $get_data['wkmp_search'] ) && ! empty( $get_data['wkmp_search'] ) ) {
					$filter_name = filter_input( INPUT_GET, 'wkmp_search', FILTER_SANITIZE_STRING );
				}
			}

			$page        = get_query_var( 'pagenum' ) ? get_query_var( 'pagenum' ) : 1;
			$limit       = 20;
			$filter_data = array(
				'start'                 => ( $page - 1 ) * $limit,
				'limit'                 => $limit,
				'filter_transaction_id' => $filter_name,
			);

			$transact_info = $this->transaction_db_obj->wkmp_get_seller_transactions( $filter_data, $this->seller_id );
			$total         = $this->transaction_db_obj->wkmp_get_seller_total_transactions( $filter_data, $this->seller_id );

			$transactions = array();

			foreach ( $transact_info as $value ) {
				$order_info     = wc_get_order( $value->order_id );
				$transactions[] = array(
					'id'             => $value->id,
					'transaction_id' => $value->transaction_id,
					'order_id'       => $value->order_id,
					'amount'         => wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $value->order_id, $value->amount ), array( 'currency' => $order_info->get_currency() ) ),
					'type'           => ucfirst( $value->type ),
					'method'         => ucfirst( $value->method ),
					'created_on'     => $value->transaction_date,
					'view'           => home_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_transaction_endpoint', 'transaction' ) . '/view/' . (int) $value->id ),
				);
			}

			$url        = get_permalink() . get_option( '_wkmp_transaction_endpoint', 'transaction' );
			$pagination = $wkmarketplace->wkmp_get_pagination( $total, $page, $limit, $url );

			require_once __DIR__ . '/wkmp-transaction-list.php';
		}
	}
}
