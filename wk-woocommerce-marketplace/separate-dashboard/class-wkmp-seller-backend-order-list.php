<?php
/**
 * Seller backend order list
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WKMP_Seller_Backend_Order_List' ) ) {

	/**
	 * Class WKMP_Seller_Backend_Order_List
	 *
	 * @package WkMarketplace\Separate_Dashboard
	 */
	class WKMP_Seller_Backend_Order_List extends \WP_List_Table {
		/**
		 * WKMP_Seller_Backend_Order_List constructor.
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
			$final_data  = ( $this->table_data() ) ? $this->table_data() : array();
			$data        = empty( $final_data['data'] ) ? array() : $final_data['data'];
			$total_items = empty( $final_data['total_orders'] ) ? 0 : $final_data['total_orders'];

			$per_page              = $this->get_items_per_page( 'order_per_page', 20 );
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$total_pages           = ceil( $total_items / $per_page );

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
		 * Hidden columns.
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Get columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'order_id'     => esc_html__( 'Order', 'wk-marketplace' ),
				'order_status' => esc_html__( 'Status', 'wk-marketplace' ),
				'order_date'   => esc_html__( 'Date', 'wk-marketplace' ),
				'order_total'  => esc_html__( 'Total', 'wk-marketplace' ),
				'action'       => esc_html__( 'Action', 'wk-marketplace' ),
			);
		}

		/**
		 * Get sortable columns.
		 *
		 * @return array[]
		 */
		public function get_sortable_columns() {
			return array( 'order_id' => array( 'order_id', true ) );
		}

		/**
		 * Default columns values.
		 *
		 * @param array  $item Order item.
		 * @param string $column_name Column name.
		 *
		 * @return bool|string
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'order_id':
				case 'order_status':
				case 'order_date':
				case 'order_total':
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
			global $wkmarketplace;

			$search = filter_input( INPUT_GET, 's', FILTER_SANITIZE_NUMBER_INT );

			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = empty( $request_data['orderby'] ) ? 'order_id' : $request_data['orderby']; // If no sort, default to title.
			$sort_order   = empty( $request_data['order'] ) ? 'desc' : $request_data['order']; // If no order, default to asc.

			$per_page = $this->get_items_per_page( 'order_per_page', 20 );
			$page_no  = $this->get_pagenum();
			$offset   = $per_page * ( $page_no - 1 );

			$filter_data = array(
				'user_id'    => get_current_user_id(),
				'search'     => $search,
				'order_by'   => $orderby,
				'sort_order' => $sort_order,
				'per_page'   => $per_page,
				'page_no'    => $page_no,
				'offset'     => $offset,
			);

			return $wkmarketplace->wkmp_get_seller_order_table_data( $filter_data );
		}
	}
}
