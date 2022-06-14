<?php
/**
 * Seller Order List In Admin Dashboard
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Feedback;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Admin as AdminHelper;
use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Feedback' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Admin_Feedback extends \WP_List_Table {
		/**
		 * Feedback DB Object.
		 *
		 * @var Common\WKMP_Seller_Feedback
		 */
		private $feedback_db_obj;

		/**
		 * Marketplace class Object.
		 *
		 * @var $marketplace \Marketplace
		 */
		private $marketplace;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Feedback constructor.
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->feedback_db_obj = new Common\WKMP_Seller_Feedback();
			$this->marketplace     = $wkmarketplace;

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Feedback', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Feedback', 'wk-marketplace' ),
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

			$per_page     = $this->get_items_per_page( 'wkmp_seller_per_page', 20 );
			$current_page = $this->get_pagenum();
			$screen       = get_current_screen();
			$filter_name  = isset( $_REQUEST['s'] ) ? wc_clean( $_REQUEST['s'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$filter_data = array(
				'start'       => ( $current_page - 1 ) * $per_page,
				'limit'       => $per_page,
				'filter_name' => $filter_name,
			);

			$feedbacks = $this->feedback_db_obj->wkmp_get_seller_feedbacks( $filter_data );
			$data      = array();

			foreach ( $feedbacks as $feedback ) {
				$seller_info = $this->marketplace->wkmp_get_seller_info( $feedback->seller_id );
				$url         = 'admin.php?page=wk-marketplace&tab-action=manage&seller-id=' . $feedback->seller_id;
				$shop_name   = sprintf( '<a href="%s"><strong>%s(#%d)</strong></a>', esc_url( admin_url( $url ) ), $seller_info->shop_name, $feedback->seller_id );

				$status = empty( $feedback->status ) ? esc_html__( 'Pending', 'wk-marketplace' ) : esc_html__( 'Approved', 'wk-marketplace' );
				$status = ( 2 === intval( $feedback->status ) ) ? esc_html__( 'Disapproved', 'wk-marketplace' ) : $status;

				$data[] = array(
					'id'             => $feedback->ID,
					'shop_name'      => $shop_name,
					'value_rating'   => '5/' . $feedback->value_r,
					'price_rating'   => '5/' . $feedback->price_r,
					'quality_rating' => '5/' . $feedback->quality_r,
					'summary'        => $feedback->review_summary,
					'description'    => $feedback->review_desc,
					'status'         => $status,
					'date_created'   => $feedback->review_time,
				);
			}

			$total_items = $this->feedback_db_obj->wkmp_get_seller_total_feedbacks( $filter_data );

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
		 * Define the columns that are going to be used in the table
		 *
		 * @return array the array of columns to use with the table
		 */
		public function get_columns() {
			return array(
				'cb'             => '<input type="checkbox" />',
				'shop_name'      => esc_html__( 'Shop Name', 'wk-marketplace' ),
				'value_rating'   => esc_html__( 'Value Rating', 'wk-marketplace' ),
				'price_rating'   => esc_html__( 'Price Rating', 'wk-marketplace' ),
				'quality_rating' => esc_html__( 'Quality Rating', 'wk-marketplace' ),
				'summary'        => esc_html__( 'Summary', 'wk-marketplace' ),
				'description'    => esc_html__( 'Description', 'wk-marketplace' ),
				'status'         => esc_html__( 'Status', 'wk-marketplace' ),
				'date_created'   => esc_html__( 'Date Created', 'wk-marketplace' ),
			);
		}

		/**
		 * Default columns values.
		 *
		 * @param array|object $item Item.
		 * @param string       $column_name Column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'shop_name':
				case 'value_rating':
				case 'price_rating':
				case 'quality_rating':
				case 'summary':
				case 'description':
				case 'status':
				case 'date_created':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @return array the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return array(
				'shop_name'      => array( 'shop_name', true ),
				'value_rating'   => array( 'value_rating', true ),
				'price_rating'   => array( 'price_rating', true ),
				'quality_rating' => array( 'quality_rating', true ),
				'summary'        => array( 'summary', true ),
				'description'    => array( 'description', true ),
				'status'         => array( 'status', true ),
				'date_created'   => array( 'status', true ),
			);
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
		 * Column callback.
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="feedback_%d" name="ids[]" value="%d" />', $item['id'], $item['id'] );
		}

		/**
		 * Get bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			return array(
				'approve'    => esc_html__( 'Approve', 'wk-marketplace' ),
				'disapprove' => esc_html__( 'Disapprove', 'wk-marketplace' ),
				'delete'     => esc_html__( 'Delete', 'wk-marketplace' ),
			);
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() ) {
				$delete  = false;
				$success = 0;

				$ids = isset( $_REQUEST['ids'] ) ? wc_clean( $_REQUEST['ids'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( is_iterable( $ids ) ) {
					if ( $this->current_action() === esc_attr( 'approve' ) ) {
						$status  = 1;
						$success = 1;
					} elseif ( $this->current_action() === esc_attr( 'disapprove' ) ) {
						$status  = 2;
						$success = 2;
					} elseif ( $this->current_action() === esc_attr( 'delete' ) ) {
						$delete  = true;
						$success = 3;
					}

					foreach ( $ids as $id ) {
						if ( $delete ) {
							$this->feedback_db_obj->wkmp_delete_seller_feedback( $id );
						} else {
							$this->feedback_db_obj->wkmp_update_feedback_status( $id, $status );
						}
					}
				}

				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$url       = 'admin.php?page=' . $page_name . '&success=' . $success;

				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			}
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
			$orderby      = ( isset( $request_data['orderby'] ) && ! empty( $request_data['orderby'] ) ) ? $request_data['orderby'] : 'shop_name'; // If no sort, default to shop_name.
			$order        = ( isset( $request_data['order'] ) && ! empty( $request_data['order'] ) ) ? $request_data['order'] : 'desc'; // If no order, default to asc.
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort.
		}
	}
}
