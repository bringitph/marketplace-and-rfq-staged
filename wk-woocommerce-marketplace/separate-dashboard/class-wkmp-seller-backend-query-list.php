<?php
/**
 * Seller backend query list
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Seller_Backend_Query_List' ) ) {

	/**
	 * WKMP_Seller_Backend query class
	 */
	class WKMP_Seller_Backend_Query_List extends \WP_List_Table {
		/**
		 * Class constructor
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'query',
					'plural'   => 'queries',
					'ajax'     => false,
				) 
			);
		}

		/**
		 * Function prepare items
		 */
		public function prepare_items() {
			$columns     = $this->get_columns();
			$sortable    = $this->get_sortable_columns();
			$hidden      = $this->get_hidden_columns();
			$data        = ( $this->table_data() ) ? $this->table_data() : array();
			$per_page    = 20;
			$total_items = count( $data );

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
			$orderby      = ( isset( $request_data['orderby'] ) && ! empty( $request_data['orderby'] ) ) ? $request_data['orderby'] : 'date'; // If no sort, default to date.
			$order        = ( isset( $request_data['order'] ) && ! empty( $request_data['order'] ) ) ? $request_data['order'] : 'desc'; // If no order, default to asc.
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort.
		}

		/**
		 * Get hidden columns
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Get columns.
		 */
		public function get_columns() {
			$columns = array(
				'date'    => esc_html__( 'Date', 'wk-marketplace' ),
				'subject' => esc_html__( 'Subject', 'wk-marketplace' ),
				'message' => esc_html__( 'Message', 'wk-marketplace' ),
			);

			return $columns;
		}

		/**
		 * Get sortable columns
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'date'    => array( 'date', true ),
				'subject' => array( 'subject', true ),
			);

			return $sortable_columns;
		}

		/**
		 * Returns columns data.
		 *
		 * @param array  $item data array.
		 * @param string $column_name col name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'date':
				case 'subject':
				case 'message':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Function for retrieving data from table.
		 */
		private function table_data() {
			global $wpdb;

			$user_id      = get_current_user_id();
			$search       = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
			$query_result = array();

			if ( empty( $search ) ) {
				$query_result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where seller_id=%d", $user_id ), ARRAY_A );
			} else {
				$query_result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where seller_id=%d and subject like %s", $user_id, '%' . $search . '%' ), ARRAY_A );
			}

			$data = array();

			foreach ( is_array( $query_result ) ? $query_result : array() as $value ) {
				$data[] = array(
					'date'    => isset( $value['create_date'] ) ? get_date_from_gmt( $value['create_date'] ) : '-',
					'subject' => isset( $value['subject'] ) ? $value['subject'] : '-',
					'message' => isset( $value['message'] ) ? $value['message'] : '-',
				);
			}

			return $data;
		}
	}
}
