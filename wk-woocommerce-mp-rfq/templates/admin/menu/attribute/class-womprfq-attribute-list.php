<?php
/**
 * Load products.
 *
 * @author     Webkul.
 * @implements Assets_Interface
 */

namespace wooMarketplaceRFQ\Templates\Admin\Menu\Attribute;

use DateTime;
use WP_List_table;
use wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Womprfq_Attribute_List' ) ) {
	/**
	 * Connected product list.
	 */
	class Womprfq_Attribute_List extends WP_List_Table {

		/**
		 * Class constructor.
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => esc_html__( 'Attribute', 'wk-mp-rfq' ),
					'plural'   => esc_html__( 'Attributes', 'wk-mp-rfq' ),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Function prepare items.
		 */
		public function prepare_items() {
			global $wpdb;

			$columns = $this->get_columns();

			$this->process_bulk_action();

			$data = $this->table_data();

			$totalitems = count( $data );

			$user = get_current_user_id();

			$screen = get_current_screen();

			$option = $screen->get_option( 'per_page', 'option' );

			if ( empty( $option ) ) {
				$option = 20;
			}

			$sortable = $this->get_sortable_column();

			$this->_column_headers = array( $columns, array(), $sortable );

			$perpage = get_user_meta( $user, $option, true );

			if ( empty( $perpage ) || $perpage < 1 ) {

				$perpage = $screen->get_option( 'per_page', 'default' );
			}

			$totalpages = ceil( $totalitems / $perpage );

			$current_page = $this->get_pagenum();

			$data = array_slice( $data, ( ( $current_page - 1 ) * $perpage ), $perpage );

			$this->set_pagination_args(
				array(
					'total_items' => $totalitems,
					'total_pages' => $totalpages,
					'per_page'    => $perpage,
				)
			);

			$this->items = $data;
		}

		/**
		 * Returns sortable columns
		 */
		public function get_sortable_column() {
			$sortable = array(
				'id' => array( 'id', true ),
			);
			return $sortable;
		}

		/**
		 * Define the columns that are going to be used in the table.
		 *
		 * @return array $columns, the array of columns to use with the table
		 */
		public function get_columns() {
			$columns = array(
				'cb'           => '<input type="checkbox" />',
				'id'           => esc_html__( 'Atttribute Id', 'wk-mp-rfq' ),
				'attr_name'    => esc_html__( 'Attribute Name', 'wk-mp-rfq' ),
				'attr_type'    => esc_html__( 'Attribute Type', 'wk-mp-rfq' ),
				'show_infornt' => esc_html__( 'Show in Quote Form', 'wk-mp-rfq' ),
				'date_created' => esc_html__( 'Created Date ', 'wk-mp-rfq' ),
				'action'       => esc_html__( 'Action', 'wk-mp-rfq' ),
			);

			return $columns;
		}

		/**
		 * Set default columns.
		 *
		 * @param array  $item        data array
		 * @param string $column_name column_name
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'cb':
				case 'id':
				case 'attr_name':
				case 'attr_type':
				case 'show_infornt':
				case 'date_created':
				case 'action':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Column checkbox.
		 *
		 * @param array $item item array
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" id="attrid_%s" name="attrid[]" value="%s" />',
				$item['id'],
				$item['id']
			);
		}

		/**
		 * Column id.
		 *
		 * @param array $item item array
		 *
		 * @return string
		 */
		public function column_id( $item ) {
			$actions = array(
				'delete' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-mp-rfq-attributes&action=delete&' . 'attrid=' . intval( $item['id'] ) ), esc_html__( 'Delete', 'wk-mp-rfq' ) ),
			);

			return sprintf( '# %s %2$s', $item['id'], $this->row_actions( $actions ) );
		}

		/**
		 * Column action.
		 *
		 * @param array $item item array
		 */
		public function column_action( $item ) {
			return sprintf(
				'<a class=" button button-primary edit-slots" href="%s">%s</a>',
				admin_url( 'admin.php?page=wc-mp-rfq-attributes&perform=manage-attr&aid=' . intval( $item['id'] ) ),
				esc_html__( 'Manage', 'wk-mp-rfq' )
			);
		}

		/**
		 * Column checkbox.
		 *
		 * @param array $item item array
		 */
		public function column_show_infornt( $item ) {
			$status = '';
			if ( $item['status'] == 1 ) {
				$status = esc_html__( 'Enable', 'wk-mp-rfq' );
			} elseif ( $item['status'] == 2 ) {
				$status = esc_html__( 'Disable', 'wk-mp-rfq' );
			}
			return sprintf( '<span>%s</span>', $status );
		}

		/**
		 * Column checkbox.
		 *
		 * @param array $item item array
		 */
		public function column_date_created( $item ) {
			$fdate = '';
			if ( $item['date_created'] ) {
				$date  = new DateTime( $item['date_created'] );
				$fdate = $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
			}
			return $fdate;
		}

		/**
		 * Process table data.
		 */
		private function table_data() {
			$post_data = $_GET;
			$list_data = array();

			$search      = '';
			$fnl_data    = array();
			$filter_type = '';

			if ( isset( $post_data['s'] ) && ! empty( $post_data['s'] ) ) {
				$search = $post_data['s'];
			}

			$list_obj = new Helper\Womprfq_Attribute_Handler();

			$attrlist = $list_obj->womprfq_get_attribute_info( $search );

			if ( $attrlist ) {
				foreach ( $attrlist as $data ) {
					if ( $data ) {
						$fnl_data[] = array(
							'id'           => intval( $data->id ),
							'attr_name'    => $data->label,
							'attr_type'    => $data->type,
							'status'       => $data->status,
							'date_created' => $data->created,
						);
					}
				}
			}

			return $fnl_data;
		}

		/**
		 * Bulk action options  .
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => esc_html__( 'Delete', 'wk-mp-rfq' ),
			);

			return $actions;
		}

		/**
		 * Process bu;lk action.
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			if ( $this->current_action() == 'delete' ) {
				if ( isset( $_GET['attrid'] ) && ! empty( $_GET['attrid'] ) ) {
					$attrid = $_GET['attrid'];
					$obj    = new Helper\Womprfq_Attribute_Handler();
					$obj->womprfq_delete_attribute_by_id( $attrid );
					?>
					<div class="updated notice">
						<p>
							<?php esc_html_e( 'Deleted Successfully.', 'wk-mp-rfq' ); ?>
						</p>
					</div>
					<?php
				}
			}
		}
	}
}
