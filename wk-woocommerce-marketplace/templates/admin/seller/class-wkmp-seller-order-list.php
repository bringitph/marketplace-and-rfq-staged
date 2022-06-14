<?php
/**
 * Seller Order List In Admin Dashboard
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

if ( ! class_exists( 'WKMP_Seller_Order_List' ) ) {
	/**
	 * Seller order List Class.
	 *
	 * Class WKMP_Seller_Order_List
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Order_List extends \WP_List_Table {
		/**
		 * Seller id.
		 *
		 * @var mixed $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Order approval enabled.
		 *
		 * @var bool $order_approval_enabled Order approval enabled.
		 */
		private $order_approval_enabled = false;

		/**
		 * Order DB Object.
		 *
		 * @var AdminHelper\WKMP_Seller_Order_Data
		 */
		private $order_db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Order_List constructor.
		 */
		public function __construct() {
			$this->seller_id    = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			$this->order_db_obj = new AdminHelper\WKMP_Seller_Order_Data();

			if ( $this->seller_id > 0 ) {
				$this->order_approval_enabled = get_user_meta( $this->seller_id, '_wkmp_enable_seller_order_approval', true );
			}

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Orders', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Orders', 'wk-marketplace' ),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepare items.
		 */
		public function prepare_items() {
			$columns               = $this->get_columns();
			$sortable              = $this->get_sortable_columns();
			$hidden                = $this->get_hidden_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'product_per_page', 20 );
			$current_page = $this->get_pagenum();
			$screen       = get_current_screen();

			$filter_name   = isset( $_REQUEST['s'] ) ? wc_clean( $_REQUEST['s'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$request_data  = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$sorting_order = empty( $request_data['order'] ) ? 'desc' : $request_data['order'];

			$filter_data = array(
				'start'         => ( $current_page - 1 ) * $per_page,
				'limit'         => $per_page,
				'filter_name'   => $filter_name,
				'sorting_order' => $sorting_order,
			);

			$orders      = $this->order_db_obj->wkmp_get_seller_orders( $this->seller_id, $filter_data );
			$data        = $this->wkmp_get_table_data( $orders );
			$total_items = $this->order_db_obj->wkmp_get_total_seller_orders( $this->seller_id );
			$orderby     = empty( $request_data['orderby'] ) ? 'order_id' : $request_data['orderby'];

			if ( 'order_id' !== $orderby ) { // Sorting is already being done in DB query based on 'order_id'.
				$sorting_order   = ( 'desc' === $sorting_order ) ? SORT_DESC : SORT_ASC;
				$sorting_columns = array_column( $data, $orderby );

				array_multisort( $sorting_columns, $sorting_order, $data );
			}

			$total_pages = ceil( $total_items / $per_page );

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
		 * Define the columns that are going to be used in the table
		 *
		 * @return array, the array of columns to use with the table
		 */
		public function get_columns() {
			return apply_filters(
				'wkmp_order_list_alter_columns',
				array(
					'cb'                  => '<input type="checkbox" />',
					'order_id'            => esc_html__( 'Order Id', 'wk-marketplace' ),
					'product'             => esc_html__( 'Product', 'wk-marketplace' ),
					'quantity'            => esc_html__( 'Quantity', 'wk-marketplace' ),
					'product_total'       => esc_html__( 'Product Total', 'wk-marketplace' ),
					'shipping'            => esc_html__( 'Shipping', 'wk-marketplace' ),
					'discount'            => esc_html__( 'Discount', 'wk-marketplace' ),
					'total_commission'    => esc_html__( 'Total Commission', 'wk-marketplace' ),
					'total_seller_amount' => esc_html__( 'Total Seller Amount', 'wk-marketplace' ),
					'action'              => esc_html__( 'Action', 'wk-marketplace' ),
				)
			);

		}

		/**
		 * Column default.
		 *
		 * @param array|object $item items.
		 * @param string       $column_name Column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'order_id':
				case 'product':
				case 'quantity':
				case 'product_total':
				case 'shipping':
				case 'discount':
				case 'total_commission':
				case 'order_commission':
				case 'total_seller_amount':
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
				'order_id' => array( 'order_id', true ),
				'quantity' => array( 'quantity', true ),
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
		 * @param array|object $item Items.
		 *
		 * @return string|void
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="order_%s" name="ids[]" value="%s" />', $item['ids'], $item['ids'] );
		}

		/**
		 * Get bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array( 'pay' => esc_html__( 'Pay', 'wk-marketplace' ) );

			if ( $this->order_approval_enabled ) {
				$actions['approve']    = esc_html__( 'Approve', 'wk-marketplace' );
				$actions['disapprove'] = esc_html__( 'Disapprove', 'wk-marketplace' );
			}

			return $actions;
		}

		/**
		 * Process bulk action.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() ) {
				$ids     = isset( $_REQUEST['ids'] ) ? wc_clean( $_REQUEST['ids'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$success = 0;

				if ( is_iterable( $ids ) ) {
					foreach ( $ids as $id ) {
						$ids       = explode( '-', $id );
						$order_id  = $ids[0];
						$seller_id = $ids[1];
						$this->order_db_obj->wkmp_update_seller_order_status( $order_id, $seller_id, $this->current_action() );
					}
					$success = 1;
				}

				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$action    = isset( $_REQUEST['tab-action'] ) ? wc_clean( $_REQUEST['tab-action'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$seller_id = isset( $_REQUEST['seller-id'] ) ? wc_clean( $_REQUEST['seller-id'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$tab       = isset( $_REQUEST['tab'] ) ? wc_clean( $_REQUEST['tab'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$url = 'admin.php?page=' . $page_name . '&tab-action=' . $action . '&seller-id=' . $seller_id . '&tab=' . $tab . '&success=' . $success;
				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			}
		}

		/**
		 * Get table data.
		 *
		 * @param array $orders Orders.
		 *
		 * @return array
		 */
		private function wkmp_get_table_data( $orders ) {
			$final_data = array();
			foreach ( $orders as $order ) {
				$order_info          = wc_get_order( $order['order_id'] );
				$paid_status         = isset( $order['action'] ) ? $order['action'] : 'not_paid';
				$total_seller_amount = isset( $order['total_seller_amount'] ) ? $order['total_seller_amount'] : 0;

				$action_keyword = 'pay';

				if ( $this->order_approval_enabled ) {
					$action_keyword = ( 'not_paid' === $paid_status ) ? 'select' : $paid_status;
					if ( 'approved' === $paid_status ) {
						$action_keyword = ( $total_seller_amount > 0 ) ? 'pay' : $paid_status;
					}
				} else {
					$action_keyword = ( 'paid' === $paid_status || ! ( $total_seller_amount > 0 ) ) ? 'paid' : $action_keyword;
				}

				$pro_str = '';
				if ( isset( $order['product'] ) && $order['product'] ) {
					foreach ( $order['product'] as $pro ) {
						$pro_str = $pro_str . $pro['title'] . ' ( #' . $pro['id'] . ')<br>';
					}
				}

				$currency = array( 'currency' => $order_info->get_currency() );

				$final_data[] = apply_filters(
					'wkmp_order_list_alter_column_data',
					array(
						'ids'                 => $order['id'],
						'order_id'            => $order['order_id'],
						'product'             => $pro_str,
						'quantity'            => $order['quantity'],
						'product_total'       => wc_price( $order['product_total'], $currency ),
						'shipping'            => wc_price( $order['shipping'], $currency ),
						'discount'            => $this->wkmp_get_discount( $order, $order_info ),
						'total_commission'    => wc_price( $order['total_commission'], $currency ),
						'total_seller_amount' => $this->wkmp_get_total_seller_amount( $order, $order_info ),
						'action'              => $this->get_action_html( $action_keyword, $total_seller_amount, $order['id'] ),
					)
				);
			}

			return $final_data;
		}

		/**
		 * Get discount.
		 *
		 * @param array     $item Items.
		 * @param \WC_Order $order Order.
		 *
		 * @return string
		 */
		private function wkmp_get_discount( $item, $order ) {
			$result = '-';
			if ( ! empty( $item ) ) {
				$discount = $item['discount'];
				$amt      = 0;
				if ( $discount['seller'] > 0 ) {
					$result = '<span class="ord-sel-discount">' . esc_html__( 'Seller', 'wk-marketplace' ) . '</span> ';
					$amt    = $discount['seller'];
				} elseif ( $discount['admin'] > 0 ) {
					$result = '<span class="ord-adm-discount">' . esc_html__( 'Admin', 'wk-marketplace' ) . '</span> ';
					$amt    = $discount['admin'];
				}
				if ( $amt > 0 ) {
					$tip  = '<p>';
					$tip .= wc_price( $amt, array( 'currency' => $order->get_currency() ) );
					$tip .= '</p>';

					return sprintf( '%s %s', $result, wc_help_tip( $tip, true ) );
				}
			}

			return $result;
		}

		/**
		 * Total seller amount.
		 *
		 * @param array     $item Items.
		 * @param \WC_Order $order Order.
		 *
		 * @return string
		 */
		private function wkmp_get_total_seller_amount( $item, $order ) {
			$reward_point_weightage = ! empty( $GLOBALS['reward'] ) ? $GLOBALS['reward']->get_woocommerce_reward_point_weightage() : 0;
			$rwd_note               = '';
			if ( ! empty( $item['reward_data'] ) ) {
				if ( ! empty( $item['reward_data']['seller'] ) ) {
					$rwd_note = ' - ' . wc_price( $item['reward_data']['seller'] * $reward_point_weightage, array( 'currency' => $order->get_currency() ) ) . '( ' . __( 'Reward', 'wk-marketplace' ) . ' )';
				}
			}

			if ( ! empty( $item['wallet_data'] ) ) {
				if ( ! empty( $item['wallet_data']['seller'] ) ) {
					$rwd_note .= ' - ' . wc_price( $item['wallet_data']['seller'], array( 'currency' => $order->get_currency() ) ) . '( ' . __( 'Wallet', 'wk-marketplace' ) . ' )';
				}
			}

			if ( ! empty( $item['refunded_amount'] ) ) {
				$total_seller_amount = '<span style="display:inline-block"><del>' . wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $order->get_id(), $item['total_seller_amount'] ), array( 'currency' => $order->get_currency() ) ) . '</del>' . wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $order->get_id(), $item['total_seller_amount'] ) - $item['refunded_amount'], array( 'currency' => $order->get_currency() ) ) . '</span>';
			} else {
				$total_seller_amount = '<span style="display:inline-block">' . wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $order->get_id(), $item['total_seller_amount'] ), array( 'currency' => $order->get_currency() ) ) . '</span>';
			}

			if ( $item['total_seller_amount'] !== $item['product_total'] ) {
				$tip  = '<p>';
				$tip .= wc_price( $item['total_seller_amount'], array( 'currency' => $order->get_currency() ) );
				$tip .= ' = ';
				$tip .= wc_price( $item['product_total'], array( 'currency' => $order->get_currency() ) );

				if ( ! empty( $item['tax'] ) ) {
					$tip .= ' + ';
					$tip .= wc_price( $item['tax'], array( 'currency' => $order->get_currency() ) ) . ' ( ' . __( 'Tax', 'wk-marketplace' ) . ' ) ';
				}

				if ( $item['shipping'] > 0 ) {
					$tip .= ' + ';
					$tip .= wc_price( $item['shipping'], array( 'currency' => $order->get_currency() ) ) . ' ( ' . __( 'Shipping', 'wk-marketplace' ) . ' ) ';
				}

				if ( $item['total_commission'] > 0 ) {
					$tip .= ' - ';
					$tip .= wc_price( $item['total_commission'], array( 'currency' => $order->get_currency() ) ) . ' ( ' . __( 'Commission', 'wk-marketplace' ) . ' ) ';
				}

				if ( ! empty( $rwd_note ) ) {
					$tip .= $rwd_note;
				}
				$tip .= ' ';
				$tip .= '</p>';

				return sprintf( '%s %s', $total_seller_amount, wc_help_tip( $tip, true ) );
			}

			return sprintf( '%s', $total_seller_amount );
		}

		/**
		 * Return action HTML based on action keyword.
		 *
		 * @param string $action_keyword Action keyword.
		 * @param string $order_amount Order amount.
		 * @param string $order_id Order id and Seller id (e.g. 548-2).
		 *
		 * @return string
		 */
		public function get_action_html( $action_keyword = '', $order_amount = 0, $order_id = '' ) {
			$action_text = ( 'paid' === $action_keyword ) ? esc_html__( 'Paid', 'wk-marketplace' ) : ( 'approved' === $action_keyword ? esc_html__( 'Approved', 'wk-marketplace' ) : esc_html__( 'Disapproved', 'wk-marketplace' ) );
			$action_html = '<button class="button button-primary" class="admin-order-pay" disabled>' . $action_text . '</button>';

			if ( 'pay' === $action_keyword && $order_amount > 0 ) {
				$action_html = '<a href="javascript:void(0)" data-id="' . esc_attr( $order_id ) . '" class="page-title-action admin-order-pay">' . __( 'Pay', 'wk-marketplace' ) . '</a>';
			}

			if ( 'select' === $action_keyword ) {
				$action_html = '<select class="wc-enhanced-select wkmp_seller_order_action"><option value="">' . esc_html__( '--Select--', 'wk-marketplace' ) . '</option>';

				$action_html .= ( $order_amount > 0 ) ? '<option value="' . esc_attr( $order_id ) . '">' . esc_html__( 'Pay', 'wk-marketplace' ) . '</option>' : '';
				$action_html .= '<option value="' . esc_attr( $order_id ) . '-approve-' . esc_attr( $order_amount ) . '">' . esc_html__( 'Approve', 'wk-marketplace' ) . '</option><option value="' . esc_attr( $order_id ) . '-disapprove">' . esc_html__( 'Disapprove', 'wk-marketplace' ) . '</option></select>';
			}

			return $action_html;
		}
	}
}
