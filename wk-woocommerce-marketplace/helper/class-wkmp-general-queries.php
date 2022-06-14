<?php
/**
 * General queries class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_General_Queries' ) ) {

	/**
	 * General queries class
	 */
	class WKMP_General_Queries {

		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Constructor
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Get seller page slug.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_seller_page_slug() {
			$seller_page_id = get_option( 'wkmp_seller_page_id' );
			$page_name      = apply_filters( 'wkmp_seller_page_slug', get_option( 'wkmp_seller_page_slug', 'seller' ) );
			if ( $seller_page_id > 0 ) {
				$seller_page = get_post( $seller_page_id );
				$page_name   = isset( $seller_page->post_name ) ? $seller_page->post_name : $page_name;
			}

			return apply_filters( 'wkmp_seller_page_slug', $page_name, $seller_page_id );
		}

		/**
		 * Check if user is seller.
		 *
		 * @param int $user_id User id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_check_if_seller( $user_id ) {
			$wpdb_obj = $this->wpdb;
			$data     = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->prefix}mpsellerinfo WHERE user_id=%d and seller_value=%s", intval( $user_id ), esc_sql( 'seller' ) ) );

			return apply_filters( 'wkmp_is_user_seller', $data, $user_id );
		}

		/**
		 * Set new seller meta data
		 *
		 * @param int $user_id User ID.
		 *
		 * @return $meta_id
		 */
		public function wkmp_set_seller_meta( $user_id ) {
			$wpdb_obj     = $this->wpdb;
			$seller_table = $wpdb_obj->prefix . 'mpsellerinfo';
			$user         = new \WP_User( $user_id );

			if ( get_option( '_wkmp_auto_approve_seller' ) ) {
				$seller_data = array(
					'user_id'      => intval( $user_id ),
					'seller_key'   => 'role',
					'seller_value' => 'seller',
				);

				$user->set_role( 'wk_marketplace_seller' );
			} else {
				$seller_data = array(
					'user_id'      => intval( $user_id ),
					'seller_key'   => 'role',
					'seller_value' => 'customer',
				);

				$user->set_role( get_option( 'default_role' ) );
			}

			$meta_id = $wpdb_obj->insert( $seller_table, $seller_data );

			return apply_filters( 'wkmp_new_sellerinfo_id', $meta_id, $user_id );
		}

		/**
		 * Save seller default commission on seller registration.
		 *
		 * @param [type] $seller_id int.
		 *
		 * @return void
		 */
		public function wkmp_set_seller_default_commission( $seller_id ) {
			$wpdb_obj     = $this->wpdb;
			$insert_query = $wpdb_obj->insert(
				$wpdb_obj->prefix . 'mpcommision',
				array(
					'seller_id'             => esc_attr( $seller_id ),
					'commision_on_seller'   => esc_attr( get_option( '_wkmp_default_commission', 0 ) ),
					'admin_amount'          => 0,
					'seller_total_ammount'  => 0,
					'paid_amount'           => 0,
					'last_paid_ammount'     => 0,
					'last_com_on_total'     => 0,
					'total_refunded_amount' => 0,
				),
				array( '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d' )
			);
		}

		/**
		 * Get Seller follower by seller id
		 *
		 * @param int $seller_id seller id.
		 *
		 * @return array $user_ids
		 */
		public function wkmp_get_seller_followers( $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$user_ids = array();
			$rows     = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT user_id, meta_value FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key=%s", esc_sql( 'favourite_seller' ) ) );

			if ( count( $rows ) > 0 ) {
				foreach ( $rows as $row ) {
					if ( isset( $row->meta_value ) ) {
						$seller_ids = explode( ',', $row->meta_value );
						$seller_ids = array_map( 'intval', $seller_ids );
						if ( in_array( intval( $seller_id ), $seller_ids, true ) ) {
							$user_ids[] = $row->user_id;
						}
					}
				}
			}

			return apply_filters( 'wkmp_get_seller_followers', $user_ids, $seller_id );
		}

		/**
		 * Get Seller id by shop address
		 *
		 * @param string $shop_address shop address.
		 *
		 * @return int $seller_id seller id.
		 */
		public function wkmp_get_seller_id_by_shop_address( $shop_address ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key=%s AND meta_value=%s", esc_sql( 'shop_address' ), esc_sql( $shop_address ) ) );

			return apply_filters( 'wkmp_get_seller_id_by_shop_address', $seller_id, $shop_address );
		}

		/**
		 * Get pending seller id for given user id.
		 *
		 * @param int $user_id User id.
		 *
		 * @return int
		 */
		public function wkmp_get_pending_seller_id( $user_id ) {
			$wpdb_obj = $this->wpdb;
			return $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT `seller_id` FROM {$wpdb_obj->prefix}mpsellerinfo WHERE user_id=%d AND `seller_value`='customer'", $user_id ) );
		}

		/**
		 * Delete ICL user meta for WPML.
		 *
		 * @param string $key User meta key.
		 * @param int    $user_id User id.
		 *
		 * @return void
		 */
		public function wkmp_delete_icl_user_meta( $key = '', $user_id ) {
			$user_id  = empty( $user_id ) ? get_current_user_id() : $user_id;
			$wpdb_obj = $this->wpdb;
			delete_user_meta( $user_id, $wpdb_obj->prefix . $key );
		}

		/**
		 * Get Seller order data for Seller front end order history and seller separate page order list.
		 *
		 * @param array $query_args Query args.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_order_data( $query_args ) {
			$wpdb_obj   = $this->wpdb;
			$user_id    = empty( $query_args['user_id'] ) ? get_current_user_id() : intval( $query_args['user_id'] );
			$search     = empty( $query_args['search'] ) ? 0 : intval( $query_args['search'] );
			$orderby    = empty( $query_args['order_by'] ) ? 'order_id' : $query_args['order_by'];
			$sort_order = empty( $query_args['sort_order'] ) ? 'desc' : $query_args['sort_order'];
			$per_page   = empty( $query_args['per_page'] ) ? '-1' : intval( $query_args['per_page'] );
			$offset     = empty( $query_args['offset'] ) ? 0 : intval( $query_args['offset'] );

			$order_approval_enabled = get_user_meta( $user_id, '_wkmp_enable_seller_order_approval', true );

			$order_detail_sql = "SELECT * FROM {$wpdb_obj->prefix}mporders mo";

			if ( $order_approval_enabled ) {
				$order_detail_sql .= " LEFT JOIN {$wpdb_obj->prefix}mporders_meta mpom ON ( mo.order_id = mpom.order_id )";
			}

			$order_detail_sql .= $wpdb_obj->prepare( ' WHERE mo.seller_id = %d', $user_id );

			if ( $order_approval_enabled ) {
				$order_detail_sql .= " AND mpom.meta_key='paid_status' AND mpom.meta_value IN ('paid','approved')";
			}

			if ( ! empty( $search ) ) {
				$order_detail_sql .= $wpdb_obj->prepare( ' AND mo.order_id = %d', $search );
			}

			$order_detail_sql .= $wpdb_obj->prepare( ' ORDER BY mo.%1s %2s', $orderby, $sort_order );
			$order_detail_sql  = apply_filters( 'wkmp_get_seller_orders_query', $order_detail_sql );
			$order_details     = $wpdb_obj->get_results( apply_filters( 'wkmp_get_seller_orders_total_query', $order_detail_sql, $query_args, $user_id ), ARRAY_A );

			$order_counts = wp_list_pluck( $order_details, 'order_id' );
			$total_orders = is_iterable( $order_counts ) ? count( $order_counts ) : 0;

			if ( intval( $per_page ) > 0 ) {
				$order_detail_sql .= $wpdb_obj->prepare( ' LIMIT %d, %d', $offset, $per_page );
				$order_details     = $wpdb_obj->get_results( apply_filters( 'wkmp_get_seller_orders_query', $order_detail_sql, $query_args, $user_id ), ARRAY_A );
			}

			$order_details = apply_filters( 'mp_vendor_split_orders', $order_details, $user_id );
			$order_id_list = wp_list_pluck( $order_details, 'order_id' );
			$order_id_list = empty( $order_id_list ) ? array() : array_values( $order_id_list );
			$order_id_list = apply_filters( 'wkmp_get_seller_orders', $order_id_list, $user_id );
			$order_id_list = array_map( 'intval', $order_id_list );

			$data         = array();
			$status_array = wc_get_order_statuses();

			foreach ( $order_details as $mp_order_data ) {
				$order_id = empty( $mp_order_data['order_id'] ) ? 0 : intval( $mp_order_data['order_id'] );
				if ( in_array( $order_id, $order_id_list, true ) ) {
					$seller_order = wc_get_order( $order_id );

					if ( ! is_a( $seller_order, 'WC_Order' ) ) {
						continue;
					}

					$qty     = empty( $mp_order_data['quantity'] ) ? 0 : $mp_order_data['quantity'];
					$item_id = empty( $mp_order_data['product_id'] ) ? 0 : intval( $mp_order_data['product_id'] );
					$total   = empty( $mp_order_data['seller_amount'] ) ? 0 : $mp_order_data['seller_amount'];

					if ( array_key_exists( $order_id, $data ) ) {
						$item_ids = empty( $data[ $order_id ]['item_ids'] ) ? array() : $data[ $order_id ]['item_ids'];

						if ( ! empty( $item_id ) && ! in_array( $item_id, $item_ids, true ) ) {
							array_push( $item_ids, $item_id );
						}

						$total_qty  = $data[ $order_id ]['total_qty'];
						$total_qty += $qty;

						$total_amount  = $data[ $order_id ]['order_total'];
						$total_amount += $total;

						$data[ $order_id ]['total_qty']   = $total_qty;
						$data[ $order_id ]['order_total'] = $total_amount;
						$data[ $order_id ]['item_ids']    = $item_ids;
					} else {
						$item_ids       = array( $item_id );
						$display_status = '-';

						if ( $wpdb_obj->get_var( $wpdb_obj->prepare( 'SHOW TABLES LIKE %s;', $wpdb_obj->prefix . 'mpseller_orders' ) ) === $wpdb_obj->prefix . 'mpseller_orders' ) {
							$mp_order_status = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT order_status from {$wpdb_obj->prefix}mpseller_orders where order_id =%d and seller_id=%d", $order_id, $user_id ) );
							if ( empty( $mp_order_status ) ) {
								$mp_order_status = get_post_field( 'post_status', $order_id );
							}
							$display_status = empty( $status_array[ $mp_order_status ] ) ? $display_status : $status_array[ $mp_order_status ];
						}

						$data[ $order_id ] = array(
							'order_id'       => $order_id,
							'order_status'   => ucfirst( $display_status ),
							'order_date'     => date_format( $seller_order->get_date_created(), 'Y-m-d H:i:s' ),
							'total_qty'      => $qty,
							'item_ids'       => $item_ids,
							'order_total'    => $total,
							'order_currency' => get_woocommerce_currency_symbol( $seller_order->get_currency() ),
							'action'         => '<a href="' . admin_url( 'admin.php?page=order-history&action=view&oid=' . $order_id ) . '" class="button button-primary">' . esc_html__( 'View', 'wk-marketplace' ) . '</a>',
							'view'           => home_url( $this->wkmp_get_seller_page_slug() . '/' . get_option( '_wkmp_order_history_endpoint', 'order-history' ) . '/' . (int) $order_id ),
						);
					}
				}
			}

			foreach ( $data as $order_id => $order_data ) {
				$seller_order_meta = $wpdb_obj->get_results( $wpdb_obj->prepare( "Select meta_key, meta_value from {$wpdb_obj->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key IN ('seller_order_tax','_wkmp_refund_status','shipping_cost') ", $user_id, $order_id ), ARRAY_A );
				$ship_total        = 0;
				$tax_total         = 0;
				$refund_data       = array();

				foreach ( $seller_order_meta as $order_meta ) {
					if ( 'shipping_cost' === $order_meta['meta_key'] ) {
						$ship_total = floatval( $order_meta['meta_value'] );
					}
					if ( 'seller_order_tax' === $order_meta['meta_key'] ) {
						$tax_total = floatval( $order_meta['meta_value'] );
					}
					if ( '_wkmp_refund_status' === $order_meta['meta_key'] ) {
						$refund_data = maybe_unserialize( $order_meta['meta_value'] );
					}
				}
				$total = $order_data['order_total'] + $ship_total + $tax_total;

				if ( ! empty( $refund_data['refunded_amount'] ) ) {
					$total = '<del>' . $total . '</del> ' . $order_data['order_currency'] . round( $total - $refund_data['refunded_amount'], 2 );
				}
				$total = apply_filters( 'wkmp_add_order_fee_to_total', $order_id, $total );

				$data[ $order_id ]['order_total'] = $order_data['order_currency'] . $total . ' ' . esc_html__( 'for', 'wk-marketplace' ) . ' ' . $order_data['total_qty'] . ' ' . esc_html__( ' items', 'wk-marketplace' );
			}

			return array(
				'data'         => $data,
				'total_orders' => $total_orders,
			);
		}
	}
}
