<?php
/**
 * Seller Order List In Admin Dashboard
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Queries;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Queries' ) ) {
	/**
	 * Seller List Class.
	 *
	 * Class WKMP_Admin_Queries
	 *
	 * @package WkMarketplace\Templates\Admin\Queries
	 */
	class WKMP_Admin_Queries extends \WP_List_Table {
		/**
		 * Query DB Object.
		 *
		 * @var Common\WKMP_Seller_Ask_Queries
		 */
		private $query_db_object;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Queries constructor.
		 */
		public function __construct() {
			$this->query_db_object = new Common\WKMP_Seller_Ask_Queries();

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Queries', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Queries', 'wk-marketplace' ),
					'ajax'     => false,
				) 
			);
		}

		/**
		 * Add thickbox.
		 *
		 * @param string $query Query.
		 */
		public function thickbox_content( $query ) {
			?>
			<div id="meta-box-<?php echo esc_attr( $query->id ); ?>" class="meta-bx" style="display:none">
				<h2><?php esc_html_e( 'Reply to', 'wk-marketplace' ); ?><?php echo esc_html( $query->seller_name ); ?> </h2>
				<table style="width:100%">
					<tr>
						<td><label><h4><b> <?php esc_html_e( 'Subject', 'wk-marketplace' ); ?> </b></h4></label></td>
						<td colspan="2"><span> <?php echo esc_html( $query->subject ); ?> </span></td>
					</tr>
					<tr>
						<td><label><h4><b> <?php esc_html_e( 'Query', 'wk-marketplace' ); ?> </b></h4></label></td>
						<td colspan="2"><span> <?php echo esc_html( $query->message ); ?> </span></td>
					</tr>
				</table>
				<div class="reply-mes">
					<label><h3> <?php esc_html_e( 'Reply Message', 'wk-marketplace' ); ?> </h3></label>
					<textarea name="reply" class="admin_msg_to_seller" style="white-space: pre-wrap; margin:10px;width:90%" rows="5" cols="60"></textarea>
				</div>
				<button class="button-primary seller-query-revert" data-qid="<?php echo esc_attr( intval( $query->id ) ); ?>"><?php esc_html_e( 'Send', 'wk-marketplace' ); ?></button>
			</div>
			<?php
		}

		/**
		 * Prepare items.
		 */
		public function prepare_items() {
			global $wkmarketplace;

			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'wkmp_seller_per_page', 20 );
			$current_page = $this->get_pagenum();
			$screen       = get_current_screen();

			$filter_name = isset( $_REQUEST['s'] ) ? wc_clean( $_REQUEST['s'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$filter_data = array(
				'start'          => ( $current_page - 1 ) * $per_page,
				'limit'          => $per_page,
				'filter_subject' => $filter_name,
			);

			$data    = array();
			$queries = $this->query_db_object->wkmp_get_all_seller_queries( $filter_data );

			foreach ( $queries as $query ) {
				$seller_info        = $wkmarketplace->wkmp_get_seller_info( $query->seller_id );
				$query->seller_name = $seller_info->first_name ? $seller_info->first_name . ' ' . $seller_info->last_name : $seller_info->user_nicename;

				if ( $this->query_db_object->wkmp_check_seller_replied_by_admin( $query->id ) ) {
					$action = '<span><b>' . esc_html__( 'Replied', 'wk-marketplace' ) . '<b></span>';
				} else {
					add_thickbox();
					$this->thickbox_content( $query );
					$action = '<a href="#TB_inline?width=600&height=400&inlineId=meta-box-' . $query->id . '" title="' . __( 'Reply', 'wk-marketplace' ) . '" class="thickbox button button-primary">' . __( 'Reply', 'wk-marketplace' ) . '</a>';
				}

				$data[] = array(
					'id'           => $query->id,
					'seller'       => $query->seller_name,
					'date_created' => $query->create_date,
					'subject'      => $query->subject,
					'message'      => $query->message,
					'action'       => $action,
				);
			}

			$total_items = $this->query_db_object->wkmp_get_total_seller_queries( $filter_data );

			usort( $data, array( $this, 'usort_reorder' ) );

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
		 * @return array, the array of columns to use with the table
		 */
		public function get_columns() {
			return array(
				'cb'           => '<input type="checkbox" />',
				'seller'       => esc_html__( 'Seller', 'wk-marketplace' ),
				'date_created' => esc_html__( 'Date Created', 'wk-marketplace' ),
				'subject'      => esc_html__( 'Subject', 'wk-marketplace' ),
				'message'      => esc_html__( 'Message', 'wk-marketplace' ),
				'action'       => esc_html__( 'Action', 'wk-marketplace' ),
			);
		}

		/**
		 * Column default.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'seller':
				case 'date_created':
				case 'subject':
				case 'message':
				case 'action':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @return array, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return array(
				'seller'       => array( 'seller', true ),
				'date_created' => array( 'date_created', true ),
				'subject'      => array( 'subject', true ),
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
		 * @param array|object $item Items.
		 *
		 * @return string|void
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="customer_%d" name="ids[]" value="%d" />', $item['id'], $item['id'] );
		}

		/**
		 * Get bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => esc_html__( 'Delete', 'wk-marketplace' ),
			);

			return $actions;
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() === esc_attr( 'delete' ) ) {
				$ids     = isset( $_REQUEST['ids'] ) ? wc_clean( $_REQUEST['ids'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$success = 0;
				if ( is_iterable( $ids ) ) {
					foreach ( $ids as $id ) {
						$this->query_db_object->wkmp_delete_seller_query( $id );
					}
					$success = 1;
				}

				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$url       = 'admin.php?page=' . $page_name . '&success=' . $success;

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
			$orderby      = ! empty( $request_data['orderby'] ) ? $request_data['orderby'] : 'seller';
			$order        = ! empty( $request_data['order'] ) ? $request_data['order'] : 'desc';
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : - $result;
		}
	}
}
