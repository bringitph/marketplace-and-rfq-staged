<?php
/**
 * Seller Transaction List In Admin Dashboard
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Admin as AdminHelper;
use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Seller_Transaction_List' ) ) {
	/**
	 * Seller Transaction List Class.
	 *
	 * Class WKMP_Seller_Transaction_List
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Transaction_List extends \WP_List_Table {
		/**
		 * Seller id.
		 *
		 * @var int|mixed
		 */
		private $seller_id;

		/**
		 * Transaction DB Object.
		 *
		 * @var Common\WKMP_Transaction
		 */
		private $transaction_db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Transaction_List constructor.
		 */
		public function __construct() {
			$seller_id                = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			$this->seller_id          = empty( $seller_id ) ? 0 : $seller_id;
			$this->transaction_db_obj = new Common\WKMP_Transaction();

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Transaction', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Transaction', 'wk-marketplace' ),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepare items.
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'product_per_page', 20 );
			$current_page = $this->get_pagenum();
			$screen       = get_current_screen();

			$filter_name = isset( $_REQUEST['s'] ) ? wc_clean( $_REQUEST['s'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$filter_data = array(
				'start'                 => ( $current_page - 1 ) * $per_page,
				'limit'                 => $per_page,
				'filter_transaction_id' => $filter_name,
			);

			$transactions = $this->transaction_db_obj->wkmp_get_seller_transactions( $filter_data, $this->seller_id );

			$data = array();

			foreach ( $transactions as $transaction ) {
				$order_id   = isset( $transaction->order_id ) ? $transaction->order_id : 0;
				$order_info = ( $order_id > 0 ) ? wc_get_order( $order_id ) : new \stdClass();
				if ( ! $order_info instanceof \WC_Order ) {
					continue;
				}

				$data[] = array(
					'id'             => isset( $transaction->id ) ? $transaction->id : 0,
					'transaction_id' => isset( $transaction->transaction_id ) ? $transaction->transaction_id : '',
					'order_id'       => $order_id,
					'amount'         => isset( $transaction->amount ) ? wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $order_id, $transaction->amount ), array( 'currency' => $order_info->get_currency() ) ) : '',
					'type'           => isset( $transaction->type ) ? ucfirst( $transaction->type ) : '',
					'method'         => isset( $transaction->method ) ? ucfirst( $transaction->method ) : '',
					'created_on'     => isset( $transaction->transaction_date ) ? $transaction->transaction_date : '',
				);
			}

			$total_items = $this->transaction_db_obj->wkmp_get_seller_total_transactions( $filter_data, $this->seller_id );

			usort( $data, array( $this, 'wk_usort_reorder' ) );

			$total_pages = ceil( $total_items / $per_page );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
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
			$orderby      = ( isset( $request_data['orderby'] ) && ! empty( $request_data['orderby'] ) ) ? $request_data['orderby'] : 'order_id'; // If no sort, default to order_id.
			$order        = ( isset( $request_data['order'] ) && ! empty( $request_data['order'] ) ) ? $request_data['order'] : 'desc'; // If no order, default to asc.
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort.
		}


		/**
		 * Define the columns that are going to be used in the table
		 *
		 * @return array the array of columns to use with the table
		 */
		public function get_columns() {
			return array(
				'cb'             => '<input type="checkbox" />',
				'transaction_id' => esc_html__( 'Transaction Id', 'wk-marketplace' ),
				'order_id'       => esc_html__( 'Order Id', 'wk-marketplace' ),
				'amount'         => esc_html__( 'Amount', 'wk-marketplace' ),
				'type'           => esc_html__( 'Type', 'wk-marketplace' ),
				'method'         => esc_html__( 'Method', 'wk-marketplace' ),
				'created_on'     => esc_html__( 'Created On', 'wk-marketplace' ),
			);
		}

		/**
		 * Column default values.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Column name.
		 *
		 * @return string
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'transaction_id':
				case 'order_id':
				case 'amount':
				case 'type':
				case 'method':
				case 'created_on':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Column transaction id.
		 *
		 * @param array $item Items.
		 *
		 * @return string
		 */
		public function column_transaction_id( $item ) {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			$page_name   = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action      = filter_input( INPUT_GET, 'tab-action', FILTER_SANITIZE_STRING );

			$url     = admin_url( 'admin.php?page=' . $page_name . '&tab-action=' . $action . '&seller-id=' . intval( $this->seller_id ) . '&tab=' . $current_tab . '&id=' . $item['id'] );
			$actions = array(
				'view' => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', esc_url( $url ), esc_html__( 'View', 'wk-marketplace' ) ),
			);

			return sprintf( '%1$s %2$s', $item['transaction_id'], $this->row_actions( apply_filters( 'wkmp_seller_transaction_line_actions', $actions ) ) );
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @return array the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return array(
				'transaction_id' => array( 'transaction_id', true ),
				'order_id'       => array( 'order_id', true ),
				'amount'         => array( 'amount', true ),
				'type'           => array( 'type', true ),
				'method'         => array( 'method', true ),
				'created_on'     => array( 'created_on', true ),
			);
		}

		/**
		 * Hidden columns.
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Column callback.
		 *
		 * @param array $item Items.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="customer_%d" name="ids[]" value="%d" />', $item['id'], $item['id'] );
		}
	}
}
