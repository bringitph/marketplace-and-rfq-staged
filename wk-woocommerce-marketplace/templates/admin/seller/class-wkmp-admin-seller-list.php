<?php
/**
 * Seller List In Admin Dashboard
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Admin as AdminHelper;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Seller_List' ) ) {
	/**
	 * Seller List Class.
	 *
	 * Class WKMP_Admin_Seller_List
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Admin_Seller_List extends \WP_List_Table {
		/**
		 * Seller DB Object
		 *
		 * @var object
		 */
		protected $seller_db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Seller_List constructor.
		 */
		public function __construct() {
			$this->seller_db_obj = new AdminHelper\WKMP_Seller_Data();
			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller List', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Sellers List', 'wk-marketplace' ),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepare Items
		 *
		 * @return void
		 */
		public function prepare_items() {
			$columns               = $this->get_columns();
			$sortable              = $this->get_sortable_columns();
			$hidden                = $this->get_hidden_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'product_per_page', 20 );
			$current_page = $this->get_pagenum();
			$filter_name  = isset( $_REQUEST['s'] ) ? wc_clean( $_REQUEST['s'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$filter_data = array(
				'start'        => ( $current_page - 1 ) * $per_page,
				'limit'        => $per_page,
				'filter_email' => $filter_name,
			);

			$total_items = $this->seller_db_obj->wkmp_get_total_sellers( $filter_data );
			$sellers     = $this->seller_db_obj->wkmp_get_sellers( $filter_data );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$this->items = $this->wkmp_get_sellers_data( $sellers );
		}


		/**
		 * Get Sellers data.
		 *
		 * @param array $sellers Sellers.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_sellers_data( $sellers ) {
			$data = array();

			foreach ( $sellers as $seller ) {
				$first_name = get_user_meta( $seller->user_id, 'first_name', true );
				$last_name  = get_user_meta( $seller->user_id, 'last_name', true );

				if ( 'seller' === $seller->seller_value ) {
					$status = '<button type="button" class="button button-warning wkmp-approve-for-seller" data-seller-id="' . esc_attr( $seller->user_id ) . '">' . esc_html__( 'Disapprove', 'wk-marketplace' ) . '</button>';
				} else {
					$status = '<button type="button" class="button button-success wkmp-approve-for-seller" data-seller-id="' . esc_attr( $seller->user_id ) . '">' . esc_html__( 'Approve', 'wk-marketplace' ) . '</button>';
				}

				$data[] = array(
					'sid'       => $seller->user_id,
					'name'      => $first_name . ' ' . $last_name,
					'username'  => $seller->display_name,
					'shop_name' => get_user_meta( $seller->user_id, 'shop_name', true ),
					'email'     => sprintf( '<a href="mailto:%s">%s</a>', $seller->user_email, $seller->user_email ),
					'products'  => $this->seller_db_obj->wkmp_get_seller_product_count( $seller->user_id ),
					'status'    => $status,
					'date'      => $seller->user_registered,
				);
			}

			usort( $data, array( $this, 'usort_reorder' ) );

			return apply_filters( 'wkmp_admin_seller_list_data', $data );
		}

		/**
		 * Hidden Columns
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'name'      => esc_html__( 'Name', 'wk-marketplace' ),
				'username'  => esc_html__( 'Username', 'wk-marketplace' ),
				'shop_name' => esc_html__( 'Shop Name', 'wk-marketplace' ),
				'email'     => esc_html__( 'Email', 'wk-marketplace' ),
				'products'  => esc_html__( 'Item Count', 'wk-marketplace' ),
				'status'    => esc_html__( 'Status', 'wk-marketplace' ),
				'date'      => esc_html__( 'Date Created', 'wk-marketplace' ),
			);

			return apply_filters( 'wkmp_admin_seller_list_columns', $columns );
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Name.
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'name':
				case 'username':
				case 'shop_name':
				case 'email':
				case 'products':
				case 'status':
				case 'date':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name'      => array( 'name', true ),
				'username'  => array( 'username', true ),
				'shop_name' => array( 'shop_name', true ),
				'email'     => array( 'email', true ),
				'products'  => array( 'products', true ),
				'status'    => array( 'status', true ),
				'date'      => array( 'date', true ),
			);

			return apply_filters( 'wkmp_admin_seller_list_sortable_columns', $sortable_columns );
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="seller-id[]" value="%s" />', $item['sid'] );
		}

		/**
		 * Column actions
		 *
		 * @param array $item Items.
		 *
		 * @return $actions
		 */
		public function column_name( $item ) {
			$click   = "return confirm('" . esc_html__( 'Are you sure?', 'wk-marketplace' ) . "')";
			$actions = array(
				'edit'   => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', get_edit_user_link( $item['sid'] ), esc_html__( 'Edit', 'wk-marketplace' ) ),
				'manage' => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', admin_url( 'admin.php?page=wk-marketplace&tab-action=manage&seller-id=' . intval( $item['sid'] ) ), esc_html__( 'Manage', 'wk-marketplace' ) ),
				'delete' => sprintf( '<a onclick="' . $click . '" class="wkmp-seller-edit-link" href="%s">%s</a>', admin_url( 'admin.php?page=wk-marketplace&tab-action=delete&seller-id=' . intval( $item['sid'] ) ), esc_html__( 'Delete', 'wk-marketplace' ) ),
			);

			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( apply_filters( 'wkmp_seller_list_line_actions', $actions ) ) );
		}

		/**
		 * Bulk actions
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array(
				'trash' => esc_html__( 'Delete', 'wk-marketplace' ),
			);

			return apply_filters( 'wkmp_admin_seller_list_bulk_actions', $actions );
		}

		/**
		 * Process row and bulk actions
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			if ( $this->current_action() === esc_attr( 'trash' ) ) {
				$seller_ids = isset( $_REQUEST['seller-id'] ) ? wc_clean( $_REQUEST['seller-id'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$success    = 0;

				if ( is_iterable( $seller_ids ) ) {
					foreach ( $seller_ids as $seller_id ) {
						$this->seller_db_obj->wkmp_delete_seller( $seller_id );
					}
					$success = 1;
				}
				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$url = 'admin.php?page=' . $page_name . '&success=' . $success;
				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			}
		}

		/**
		 * Usort reorder.
		 *
		 * @param array $a First argument.
		 * @param array $b Second argument.
		 *
		 * @return float|int
		 */
		public function usort_reorder( $a, $b ) {
			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = ! empty( $request_data['orderby'] ) ? $request_data['orderby'] : 'name';
			$order        = ! empty( $request_data['order'] ) ? $request_data['order'] : 'desc';
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : - $result;
		}
	}
}
