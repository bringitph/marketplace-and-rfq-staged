<?php
/**
 * Seller backend shop followers
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Seller_Backend_Shop_Followers' ) ) {

	/**
	 * Class for listing seller queries.
	 */
	class WKMP_Seller_Backend_Shop_Followers extends \WP_List_Table {
		/**
		 * WKMP_Seller_Backend_Shop_Followers constructor.
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
		 * Prepare item.
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();
			$this->process_bulk_action();

			$data = ( $this->table_data() ) ? $this->table_data() : array();

			$total_items           = count( $data );
			$per_page              = 20;
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
			$orderby      = ( isset( $request_data['orderby'] ) && ! empty( $request_data['orderby'] ) ) ? $request_data['orderby'] : 'name'; // If no sort, default to title.
			$order        = ( isset( $request_data['order'] ) && ! empty( $request_data['order'] ) ) ? $request_data['order'] : 'desc'; // If no order, default to asc.
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort.
		}

		/**
		 * For hidden columns.
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
			$columns = array(
				'cb'    => '<input type="checkbox" />',
				'name'  => esc_html__( 'Customer Name', 'wk-marketplace' ),
				'email' => esc_html__( 'Customer Email', 'wk-marketplace' ),
			);

			return $columns;
		}

		/**
		 * Get sortable columns.
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name' => array( 'date', true ),
			);

			return $sortable_columns;
		}

		/**
		 * Get Default columns.
		 *
		 * @param array  $item item data.
		 * @param string $column_name col name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'cb':
				case 'name':
				case 'email':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Column checkbox data.
		 *
		 * @param array $item item array.
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="user_%s"name="user[]" value="%s" />', $item['id'], $item['id'] );
		}

		/**
		 * Column checkbox data.
		 *
		 * @param array $item item array.
		 */
		public function column_name( $item ) {

			$actions = array(
				'delete' => sprintf( '<a class="submitdelete" href="?page=seller-shop-followers&action=delete&user=%s&_wpnonce=%s">Delete</a>', $item['id'], wp_create_nonce( 'del_mp_nonceuser_' . $item['id'] ) ),
			);

			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );
		}

		/**
		 * Bulk action.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => 'Delete',
			);

			return $actions;
		}

		/**
		 * Process bulk action.
		 */
		public function process_bulk_action() {
			$get    = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action = isset( $get['action'] ) ? $get['action'] : '';

			if ( ! empty( $action ) ) {
				if ( isset( $get['user'] ) ) {
					switch ( $action ) {
						case 'delete':
							$user_id = $get['user'];
							if ( ! is_array( $user_id ) ) {
								$user_id = array( $user_id );
							}
							foreach ( $user_id as $u_id ) {
								$seller       = get_current_user_id();
								$customer_acc = intval( $u_id );
								if ( ! empty( $seller ) && ! empty( $customer_acc ) ) {
									$res = delete_user_meta( $customer_acc, 'favourite_seller', $seller );
								} else {
									$res = 0;
								}
							}
					}
					?>
					<div class="updated notice is-dismissible">
						<p><?php esc_html_e( 'User has been deleted successfully', 'wk-marketplace' ); ?> </p>
					</div>
					<?php
				}
			}
		}

		/**
		 * Get data for Table.
		 */
		private function table_data() {
			$data          = array();
			$current_user  = get_current_user_id();
			$customer_list = get_users(
				array(
					'meta_key'   => 'favourite_seller',
					'meta_value' => $current_user,
				)
			);

			if ( $customer_list ) {
				foreach ( $customer_list as $value ) {
					$data[] = array(
						'id'    => $value->data->ID,
						'name'  => $value->data->display_name,
						'email' => $value->data->user_email,
					);
				}
			}

			return $data;
		}
	}
}
