<?php
/**
 * Seller backend Transactions list class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Seller_Backend_Transaction_List' ) ) {
	/**
	 * Class WKMP_Seller_Backend_Transaction_List
	 *
	 * @package WkMarketplace\Separate_Dashboard
	 */
	class WKMP_Seller_Backend_Transaction_List extends \WP_List_Table {
		/**
		 * WKMP_Seller_Backend_Transaction_List constructor.
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'order',
					'plural'   => 'orders',
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepare items.
		 */
		public function prepare_items() {
			$columns     = $this->get_columns();
			$sortable    = $this->get_sortable_columns();
			$hidden      = $this->get_hidden_columns();
			$data        = ( $this->table_data() ) ? $this->table_data() : array();
			$total_items = $this->wkmp_get_total_transaction( get_current_user_id() );

			$per_page              = $this->get_items_per_page( 'order_per_page', 20 );
			$this->_column_headers = array( $columns, $hidden, $sortable );
			usort( $data, array( $this, 'wk_usort_reorder' ) );
			$total_pages  = ceil( $total_items / $per_page );
			$current_page = $this->get_pagenum();
			$data         = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'total_pages' => $total_pages,
					'per_page'    => $per_page,
				)
			);

			$this->items = $data;
		}

		/**
		 * User sorting.
		 *
		 * @param array $a First argument.
		 * @param array $b Second argument.
		 *
		 * @return float|int
		 */
		public function wk_usort_reorder( $a, $b ) {
			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = ( isset( $request_data['orderby'] ) && ! empty( $request_data['orderby'] ) ) ? $request_data['orderby'] : 'txn_id'; // If no sort, default to txn_id.
			$order        = ( isset( $request_data['order'] ) && ! empty( $request_data['order'] ) ) ? $request_data['order'] : 'desc'; // If no order, default to asc.
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort.
		}

		/**
		 * Get hidden columns.
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Ger columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'txn_id'    => esc_html__( 'Transaction ID', 'wk-marketplace' ),
				'txn_date'  => esc_html__( 'Date', 'wk-marketplace' ),
				'txn_total' => esc_html__( 'Amount', 'wk-marketplace' ),
				'action'    => esc_html__( 'Action', 'wk-marketplace' ),
			);
		}

		/**
		 * Get sortable columns.
		 *
		 * @return array[]
		 */
		public function get_sortable_columns() {
			return array(
				'txn_id'   => array( 'txn_id', true ),
				'txn_date' => array( 'txn_date', true ),
			);
		}

		/**
		 * Columns default.
		 *
		 * @param array  $item Product item.
		 * @param string $column_name Column name.
		 *
		 * @return string
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'txn_id':
				case 'txn_date':
				case 'txn_total':
				case 'action':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Table data.
		 *
		 * @return array
		 */
		private function table_data() {
			$user_id      = get_current_user_id();
			$transactions = $this->wkmp_get_seller_dashboard_transactions( $user_id );
			$data         = array();

			if ( $transactions['transaction'] ) {
				foreach ( $transactions['transaction'] as $value ) {
					$transaction_id = isset( $value['id'] ) ? $value['id'] : 0;
					$data[]         = array(
						'txn_id'    => isset( $value['transaction_id'] ) ? $value['transaction_id'] : '-',
						'txn_date'  => isset( $value['transaction_date'] ) ? get_date_from_gmt( $value['transaction_date'] ) : '-',
						'txn_total' => isset( $value['amount'] ) ? wc_price( $value['amount'] ) : '-',
						'action'    => '<a href="' . admin_url( 'admin.php?page=seller-transaction&action=view&tid=' . $transaction_id ) . '" class="button button-primary">' . __( 'View', 'wk-marketplace' ) . '</a>',
					);
				}
			}

			return $data;
		}

		/**
		 * Get transactions.
		 *
		 * @param int $seller_id Seller id.
		 * @param int $current_page Current page.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_dashboard_transactions( $seller_id, $current_page = 1 ) {
			global $wpdb;
			$offset = ( $current_page - 1 ) * 10;
			$resp   = $wpdb->get_results( $wpdb->prepare( "SELECT count(id) as total_transactions FROM {$wpdb->prefix}seller_transaction WHERE seller_id=%d ORDER BY transaction_date DESC", $seller_id ), ARRAY_A );
			$res    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}seller_transaction WHERE seller_id=%d ORDER BY transaction_date DESC LIMIT %d, 10", $seller_id, $offset ), ARRAY_A );

			return array(
				'total_count' => intval( $resp[0]['total_transactions'] ),
				'transaction' => $res,
			);
		}

		/**
		 * Get total transactions.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return string|null
		 */
		public function wkmp_get_total_transaction( $seller_id ) {
			global $wpdb;

			$total  = 0;
			$search = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );

			if ( empty( $search ) ) {
				$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}seller_transaction WHERE seller_id=%d", $seller_id ) );
			} else {
				$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}seller_transaction WHERE seller_id=%d AND transaction_id LIKE %s", $seller_id, '%' . $search . '%' ) );
			}

			return $total;
		}
	}
}
