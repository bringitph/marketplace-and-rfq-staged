<?php
/**
 * Seller order at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Orders;

use WkMarketplace\Helper\Front;
use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Orders' ) ) {
	/**
	 * Seller orders class.
	 *
	 * Class WKMP_Orders
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Orders
	 */
	class WKMP_Orders {
		/**
		 * DB Order object.
		 *
		 * @var Front\WKMP_Order_Queries
		 */
		private $db_order_obj;

		/**
		 * Seller ids.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Orders constructor.
		 *
		 * @param int     $seller_id Seller id.
		 * @param boolean $invoice Invoice.
		 * @param int     $order_id Order id.
		 *
		 * @throws \Exception Throwing Exception.
		 */
		public function __construct( $seller_id = 0, $invoice = false, $order_id = 0 ) {
			global $wpdb;
			$this->wpdb         = $wpdb;
			$this->db_order_obj = new Front\WKMP_Order_Queries();
			$this->seller_id    = $seller_id;

			if ( ! $order_id ) {
				$order_id = get_query_var( 'order_id' );
			}

			if ( $invoice && $order_id ) {
				$this->wkmp_order_invoice( $order_id );
			} elseif ( $order_id ) {
				$this->wkmp_order_views( $order_id );
			} else {
				$this->wkmp_order_list();
			}
		}

		/**
		 * Method for display seller order list.
		 *
		 * @return void
		 */
		public function wkmp_order_list() {
			global $wkmarketplace;

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$filter_id   = '';

			// Filter Orders.
			if ( isset( $posted_data['wkmp_order_search_nonce'] ) && ! empty( $posted_data['wkmp_order_search_nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp_order_search_nonce'] ), 'wkmp_order_search_nonce_action' ) ) {
				$filter_id = filter_input( INPUT_POST, 'wkmp_search', FILTER_SANITIZE_NUMBER_INT );
			}

			$page  = get_query_var( 'pagenum' ) ? get_query_var( 'pagenum' ) : 1;
			$limit = 20;

			$filter_data = array(
				'user_id'  => $this->seller_id,
				'search'   => $filter_id,
				'per_page' => $limit,
				'page_no'  => $page,
				'offset'   => ( $page - 1 ) * $limit,
			);

			$final_data = $wkmarketplace->wkmp_get_seller_order_table_data( $filter_data );

			$orders      = empty( $final_data['data'] ) ? array() : $final_data['data'];
			$total_count = empty( $final_data['total_orders'] ) ? 0 : $final_data['total_orders'];

			$url        = get_permalink() . get_option( '_wkmp_order_history_endpoint', 'order-history' );
			$pagination = $wkmarketplace->wkmp_get_pagination( $total_count, $page, $limit, $url );

			require_once __DIR__ . '/wkmp-order-list.php';
		}

		/**
		 * Seller order views
		 *
		 * @param int $order_id Order id.
		 *
		 * @throws \Exception Throwing Exception.
		 */
		public function wkmp_order_views( $order_id ) {
			global $wkmarketplace;
			$wpdb_obj       = $this->wpdb;
			$obj_commission = new Common\WKMP_Commission();
			$order_refund   = new Common\WKMP_Order_Refund();

			$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

			if ( is_admin() && 'view' === $action ) {
				$view_order_id = filter_input( INPUT_GET, 'oid', FILTER_SANITIZE_NUMBER_INT );
				$order_id      = ( $view_order_id > 0 ) ? $view_order_id : $order_id;
			}

			$seller_order = wc_get_order( $order_id );

			if ( ! $seller_order instanceof \WC_Order ) {
				echo '<h2>' . esc_html__( 'Order not found!!', 'wk-marketplace' ) . '</h2>';

				return false;
			}

			$tax_list_name = array();

			foreach ( $seller_order->get_items( 'tax' ) as $item_tax ) {
				$tax_list_name[ $item_tax->get_rate_id() ] = $item_tax->get_label();
			}

			$tax_table_name      = "{$wpdb_obj->prefix}mp_seller_tax_rates";
			$seller_tax_rate_ids = array();

			if ( $wpdb_obj->get_var( "SHOW TABLES LIKE '$tax_table_name'" ) === $tax_table_name ) {
				$seller_tax_rate_ids = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT `tax_rate_id` FROM {$wpdb_obj->prefix}mp_seller_tax_rates WHERE `user_id`=%d", $this->seller_id ), ARRAY_A );
				$seller_tax_rate_ids = array_map( 'intval', wp_list_pluck( $seller_tax_rate_ids, 'tax_rate_id' ) );
			}

			$mp_order_data            = $obj_commission->wkmp_get_seller_final_order_info( $order_id, $this->seller_id );
			$seller_order_refund_data = $order_refund->wkmp_get_seller_order_refund_data( $order_id );
			$posted_data              = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $posted_data['mp-submit-status'] ) ) {
				if ( ! empty( $posted_data['mp-order-status'] ) && 'wc-refunded' === $posted_data['mp-order-status'] ) {
					$refund_amount = ! empty( $seller_order_refund_data['refunded_amount'] ) ? $mp_order_data['total_seller_amount'] - $seller_order_refund_data['refunded_amount'] : $mp_order_data['total_seller_amount'];
					if ( ! empty( $refund_amount ) ) {
						$args = array(
							'amount'     => $refund_amount,
							'reason'     => esc_html__( 'Order fully refunded by Seller.', 'wk-marketplace' ),
							'order_id'   => $order_id,
							'line_items' => array(),
						);

						$order_refund->wkmp_set_refund_args( $args );
						$order_refund->wkmp_process_refund();
					}
				}
				$this->wkmp_order_update_status( $posted_data );
			}

			if ( isset( $posted_data['refund_manually'] ) || isset( $posted_data['do_api_refund'] ) ) {
				$line_items             = array();
				$order_items            = ! empty( $posted_data['item_refund_amount'] ) ? $posted_data['item_refund_amount'] : array();
				$order_item_total       = ! empty( $posted_data['refund_line_total'] ) ? $posted_data['refund_line_total'] : array();
				$refund_reason          = ! empty( $posted_data['refund_reason'] ) ? wp_strip_all_tags( $posted_data['refund_reason'] ) : '';
				$order_id               = ! empty( $posted_data['order_id'] ) ? intval( $posted_data['order_id'] ) : '';
				$restock_refunded_items = ( ! empty( $posted_data['restock_refunded_items'] ) && ( 1 === intval( $posted_data['restock_refunded_items'] ) ) );

				$refund_tax_items       = ! empty( $posted_data['refund_line_tax'] ) ? $posted_data['refund_line_tax'] : array();
				$refund_line_tax_amount = ! empty( $posted_data['refund_line_tax_amount'] ) ? $posted_data['refund_line_tax_amount'] : array();

				$api_refund          = isset( $posted_data['do_api_refund'] );
				$total_refund_amount = 0;

				foreach ( $order_items as $item_id => $order_item ) {
					$qty = ! empty( $order_item_total[ $item_id ] ) ? $order_item_total[ $item_id ] : 0;
					if ( $qty > 0 ) {
						$line_items[ $item_id ]['qty']          = $qty;
						$line_items[ $item_id ]['refund_total'] = round( floatval( $order_item ) * $qty, 2 );
						$total_refund_amount                   += round( $line_items[ $item_id ]['refund_total'], 2 );
					}
				}

				foreach ( $refund_tax_items as $refund_item_key => $refund_item_value ) {

					if ( isset( $refund_line_tax_amount[ $refund_item_key ] ) ) {
						$line_items[ $refund_item_key ]['refund_tax'] = array_filter( array_map( 'wc_format_decimal', $refund_line_tax_amount[ $refund_item_key ] ) );
						$total_refund_amount                         += array_sum( $refund_line_tax_amount[ $refund_item_key ] );
					} elseif ( ! empty( $refund_item_value ) ) {
						$line_items[ $refund_item_key ]['refund_tax'] = array_filter( array_map( 'wc_format_decimal', $refund_item_value ) );
						$total_refund_amount                         += array_sum( $refund_item_value );
					}
				}

				if ( ! empty( $total_refund_amount ) ) {
					$args = array(
						'amount'         => $total_refund_amount,
						'reason'         => $refund_reason,
						'order_id'       => $order_id,
						'line_items'     => $line_items,
						'refund_payment' => $api_refund,
						'restock_items'  => $restock_refunded_items,
					);

					$order_refund->wkmp_set_refund_args( $args );
					$order_refund->wkmp_process_refund();
					$seller_order_refund_data = $order_refund->wkmp_get_seller_order_refund_data( $order_id );

					if ( ! empty( $seller_order_refund_data['refunded_amount'] ) && trim( $seller_order_refund_data['refunded_amount'] ) === trim( $mp_order_data['total_seller_amount'] ) ) {

						$wpdb_obj->update(
							$wpdb_obj->prefix . 'mpseller_orders',
							array( 'order_status' => 'wc-refunded' ),
							array(
								'order_id'  => $order_id,
								'seller_id' => $this->seller_id,
							),
							array( '%s' ),
							array( '%d', '%d' )
						);
					}
				} else {
					$msg = esc_html__( 'Please select items to refund.', 'wk-marketplace' );
					if ( is_admin() ) {
						?>
						<div class="wrap">
							<div class="notice notice-error">
								<p> <?php echo esc_html( $msg ); ?> </p>
							</div>
						</div>
						<?php
					} else {
						wc_print_notice( $msg, 'error' );
					}
				}
			}

			$seller_order_refund_data = $order_refund->wkmp_get_seller_order_refund_data( $order_id );

			$order_status = '';
			$query_result = '';

			if ( $wpdb_obj->get_var( $wpdb_obj->prepare( 'SHOW TABLES LIKE %s;', $wpdb_obj->prefix . 'mpseller_orders' ) ) === $wpdb_obj->prefix . 'mpseller_orders' ) {
				$query_result = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT order_status from {$wpdb_obj->prefix}mpseller_orders where order_id = %d and seller_id = %d", $order_id, $this->seller_id ), ARRAY_A );
				$order_status = empty( $query_result['order_status'] ) ? $order_status : $query_result['order_status'];
			}

			if ( ! $order_status ) {
				$order_status = get_post_field( 'post_status', $order_id );
			}

			// Preparing order views data.
			$payment_gateway = wc_get_payment_gateway_by_order( $seller_order );
			$gateway_name    = __( 'Payment gateway', 'wk-marketplace' );

			if ( ! empty( $payment_gateway ) ) {
				$gateway_name = empty( $payment_gateway->method_title ) ? $payment_gateway->get_title() : $payment_gateway->method_title;
			}

			$reward_points = empty( $GLOBALS['reward'] ) ? 0 : $GLOBALS['reward']->get_woocommerce_reward_point_weightage();
			$order_details = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders mo WHERE mo.seller_id = %d and order_id=%d", $this->seller_id, $order_id ), ARRAY_A );
			$order_data    = array();
			$order_items   = $seller_order->get_items();

			foreach ( $order_details as $details ) {
				$product_id = empty( $details['product_id'] ) ? 0 : intval( $details['product_id'] );
				if ( empty( $product_id ) ) {
					continue;
				}

				$product_name = '';
				$variable_id  = 0;
				$item_key     = '';
				$meta_data    = array();
				$item_data    = array();
				$tax_total    = 0;

				foreach ( $order_items as $order_item_key => $order_item ) {
					$order_product_id = $order_item->get_product_id();
					if ( intval( $order_product_id ) === $product_id ) {
						$product_name = empty( $order_item['name'] ) ? '' : $order_item['name'];
						$variable_id  = $order_item->get_variation_id();
						$item_key     = $order_item_key;
						$meta_data    = $order_item->get_meta_data();
						$tax_total    = $order_item->get_taxes()['total'];
						break;
					}
				}

				if ( empty( $product_name ) ) {
					$product_obj  = wc_get_product( $product_id );
					$product_name = $product_obj->get_title();
				}

				if ( ! empty( $meta_data ) ) {
					foreach ( array_keys( $meta_data ) as $meta_key ) {
						$item_data[] = $meta_data[ $meta_key ]->get_data();
					}
				}

				$order_data[ $product_id ] = array(
					'product_name'        => $product_name,
					'variable_id'         => $variable_id,
					'qty'                 => empty( $details['quantity'] ) ? 0 : $details['quantity'],
					'item_key'            => $item_key,
					'product_total_price' => empty( $details['amount'] ) ? 0 : $details['amount'],
					'meta_data'           => $item_data,
					'tax_rates'           => $tax_total,
				);
			}

			$views_data = array(
				'seller_id'         => $this->seller_id,
				'order_id'          => $order_id,
				'gateway_name'      => $gateway_name,
				'reward_points'     => $reward_points,
				'seller_order_data' => $order_data,
			);

			require_once __DIR__ . '/wkmp-order-views.php';
		}

		/**
		 * Update order status
		 *
		 * @param array $data Data.
		 *
		 * @return void
		 */
		public function wkmp_order_update_status( $data ) {
			global $wkmarketplace;
			$wpdb_obj   = $this->wpdb;
			$table_name = $wpdb_obj->prefix . 'mpseller_orders';
			$error      = new \WP_Error();

			if ( ! isset( $data['mp_order_status_nonce'] ) || ! wp_verify_nonce( $data['mp_order_status_nonce'], 'mp_order_status_nonce_action' ) ) {
				$error->add( 'nonce-error', esc_html__( 'Sorry, your nonce did not verify.', 'wk-marketplace' ) );
			} else {

				$order_status = ( $data['mp-order-status'] ) ? sanitize_text_field( $data['mp-order-status'] ) : '';
				$order_id     = ( $data['mp-order-id'] ) ? intval( $data['mp-order-id'] ) : '';
				$seller_id    = ( $data['mp-seller-id'] ) ? intval( $data['mp-seller-id'] ) : '';
				$old_status   = ( $data['mp-old-order-status'] ) ? sanitize_text_field( $data['mp-old-order-status'] ) : '';

				$order        = new \WC_Order( $order_id );
				$items        = $order->get_items();
				$author_array = array();

				foreach ( $items as $value ) {
					$author_array[] = get_post_field( 'post_author', $value->get_product_id() );
				}

				$order_author_count = count( $author_array );

				if ( $wpdb_obj->get_var( $wpdb_obj->prepare( 'SHOW TABLES LIKE %s;', $wpdb_obj->prefix . 'mpseller_orders' ) ) === $wpdb_obj->prefix . 'mpseller_orders' ) {

					if ( empty( $order_status ) ) {
						$error->add( 'status-error', esc_html__( 'Select status for order.', 'wk-marketplace' ) );
					} elseif ( $order_status === $old_status ) {
						$status = str_replace( 'wc-', '', $order_status );
						$error->add( 'status-error', esc_html__( 'Order status is already "', 'wk-marketplace' ) . ucfirst( $status ) . '".' );
					} else {
						$sql = $wpdb_obj->update(
							$table_name,
							array( 'order_status' => $order_status ),
							array(
								'order_id'  => $order_id,
								'seller_id' => $seller_id,
							),
							array( '%s' ),
							array( '%d', '%d' )
						);

						$author_name  = get_user_by( 'ID', $seller_id );
						$status_array = wc_get_order_statuses();
						$author_name  = $author_name->user_nicename;
						$note         = sprintf( /* translators: %1$s: Vendor name, %2$s: Order status, %3$s: New order status. */ esc_html__( 'Vendor `{%1$s}` changed Order Status from {%2$s} to {%3$s} for it\'s own products.', 'wk-marketplace' ), $author_name, $status_array[ $old_status ], $status_array[ $order_status ] );

						$query_result = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT count(*) as total FROM $table_name WHERE order_id = %d AND order_status = %s", $order_id, $order_status ) );

						if ( intval( $query_result[0]->total ) === $order_author_count ) {
							$order->update_status( $order_status, sprintf( /* translators: %s: Order status. */ esc_html__( "Status updated to {%s} based on status updated by vendor's.", 'wk-marketplace' ), $status_array[ $order_status ] ) );
						} else {
							$order->add_order_note( $note, 1 );
						}

						if ( is_admin() ) {
							?>
							<div class="wrap">
								<div class="notice notice-success">
									<p><?php esc_html_e( 'Order status updated.', 'wk-marketplace' ); ?></p>
								</div>
							</div>
							<?php
						} else {
							wc_add_notice( esc_html__( 'Order status updated.', 'wk-marketplace' ), 'success' );
						}

						do_action( 'wkmp_after_seller_update_order_status', $order, $data );

						if ( ! is_admin() ) {
							wp_safe_redirect( home_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_order_history_endpoint', 'order-history' ) . '/' . $order_id ) );
							exit;
						}
					}
				} else {
					$error->add( 'status-error', esc_html__( 'Database table does not exist.', 'wk-marketplace' ) );
				}
			}

			if ( is_wp_error( $error ) ) {
				foreach ( $error->get_error_messages() as $value ) {
					if ( is_admin() ) {
						?>
						<div class="wrap">
							<div class="notice notice-error">
								<p><?php echo esc_html( $value ); ?></p>
							</div>
						</div>
						<?php
					} else {
						wc_print_notice( $value, 'error' );
					}
				}
			}
		}

		/**
		 * Display seller order invoice
		 *
		 * @param int $order_id Order id.
		 *
		 * @return void
		 */
		public function wkmp_order_invoice( $order_id ) {
			global $wkmarketplace;
			$wpdb_obj     = $this->wpdb;
			$data         = array();
			$order_id     = base64_decode( $order_id );
			$order_refund = new Common\WKMP_Order_Refund();
			$refund_data  = $order_refund->wkmp_get_seller_order_refund_data( $order_id );
			$seller_order = new \WC_Order( $order_id );

			$data['seller_info'] = $wkmarketplace->wkmp_get_seller_info( $this->seller_id );
			$data['store_url']   = home_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . $data['seller_info']->shop_address );

			$data['customer_details'] = array(
				'name'      => $seller_order->get_billing_first_name() . ' ' . $seller_order->get_billing_last_name(),
				'email'     => $seller_order->get_billing_email(),
				'telephone' => $seller_order->get_billing_phone(),
			);

			$data['currency_symbol'] = get_woocommerce_currency_symbol( $seller_order->get_currency() );
			$data['shipping_method'] = $seller_order->get_shipping_method();
			$data['payment_method']  = $seller_order->get_payment_method_title();

			$data['billing_address']  = wp_kses_post( $seller_order->get_formatted_billing_address( esc_html__( 'N/A', 'wk-marketplace' ) ) );
			$data['shipping_address'] = wp_kses_post( $seller_order->get_formatted_shipping_address( esc_html__( 'N/A', 'wk-marketplace' ) ) );
			$data['date_created']     = $seller_order->get_date_created()->format( 'Y F j, g:i a' );
			$shipping_cost            = 0;

			if ( 'null' !== $seller_order->get_total_shipping() ) {
				$ship_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'shipping_cost' ", $this->seller_id, $order_id ) );

				if ( ! empty( $ship_data ) ) {
					$shipping_cost         = $ship_data[0]->meta_value;
					$data['shipping_cost'] = wc_format_decimal( $ship_data[0]->meta_value, 2 );
				} else {
					$data['shipping_cost'] = '0.00';
				}
			}

			$seller_order_tax = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'seller_order_tax' ", $this->seller_id, $order_id ) );

			if ( empty( $seller_order_tax ) ) {
				$seller_order_tax = 0;
			}

			$data['ordered_products'] = array();
			$subtotal                 = 0;

			foreach ( $seller_order->get_items() as $product ) {
				$item_data = array();

				$value_data   = $product->get_data();
				$product_id   = $product->get_product_id();
				$product_post = get_post( $product_id );
				$meta_data    = $product->get_meta_data();

				if ( ! empty( $meta_data ) ) {
					foreach ( $meta_data as $key1 => $value1 ) {
						$item_data[] = $meta_data[ $key1 ]->get_data();
					}
				}

				if ( intval( $product_post->post_author ) === intval( $this->seller_id ) ) {
					$subtotal += $value_data['total'];

					$data['ordered_products'][] = array(
						'product_name' => $product['name'],
						'quantity'     => $value_data['quantity'],
						'variable_id'  => $product->get_variation_id(),
						'unit_price'   => number_format( $value_data['total'] / $value_data['quantity'], 2 ),
						'total_price'  => number_format( $value_data['total'], 2 ),
						'meta_data'    => $item_data,
					);
				}
			}

			$data['sub_total'] = number_format( $subtotal, 2 );

			if ( ! empty( $refund_data['refunded_amount'] ) ) {
				$data['total']             = number_format( $seller_order_tax + $subtotal + $shipping_cost - $refund_data['refunded_amount'], 2 );
				$data['subtotal_refunded'] = number_format( $seller_order_tax + $subtotal + $shipping_cost, 2 );
			} else {
				$data['total'] = number_format( $seller_order_tax + $subtotal + $shipping_cost, 2 );
			}
			require_once __DIR__ . '/wkmp-order-invoice.php';
			die;
		}
	}
}
