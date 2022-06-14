<?php
/**
 * WKMP seller commission queries
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WKMP_Commission' ) ) {

	/**
	 * Seller Commission related queries class
	 */
	class WKMP_Commission {

		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Order db object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data $order_db_obj Order db object.
		 */
		private $order_db_obj;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb         = $wpdb;
			$this->order_db_obj = new Admin\WKMP_Seller_Order_Data();
		}

		/**
		 * Get Commission per order item.
		 *
		 * @param int $order_id order ID.
		 * @param int $product_id product ID.
		 * @param int $quantity product Quantity.
		 */
		public function get_order_item_commission( $order_id, $product_id, $quantity = '' ) {
			$order = wc_get_order( $order_id );
		}

		/**
		 * Get admin commission rate.
		 *
		 * @param int $seller_id seller ID.
		 */
		public function wkmp_get_admin_rate( $seller_id ) {
			$wpdb_obj   = $this->wpdb;
			$admin_rate = 1;

			$admin_commission = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mpcommision  WHERE seller_id = %d", esc_attr( $seller_id ) ) );

			if ( $admin_commission ) {
				$admin_rate = floatval( $admin_commission[0]->commision_on_seller ) / 100;
			}

			return apply_filters( 'wkmp_get_admin_rate', $admin_rate, $seller_id );
		}

		/**
		 * Update Seller Commission data.
		 *
		 * @param int $seller_id seller ID.
		 * @param int $order_id order ID.
		 */
		public function wkmp_update_seller_commission( $seller_id, $order_id ) {
			$result            = 0;
			$seller_order_data = $this->wkmp_get_seller_final_order_info( $order_id, $seller_id );
			$exchange_rate     = get_post_meta( $order_id, 'mpmc_exchange_rate', true );
			$exchange_rate     = ! empty( $exchange_rate ) ? $exchange_rate : 1;
			$sel_pay_amount    = $seller_order_data['total_seller_amount'] / $exchange_rate;
			$response          = $this->wkmp_update( $seller_id, $sel_pay_amount );

			if ( 0 === intval( $response['error'] ) ) {
				$result = $sel_pay_amount;
			}

			return apply_filters( 'wkmp_update_seller_commission', $result, $seller_id, $order_id );
		}

		/**
		 * Updating seller commission .
		 *
		 * @param int $seller_id seller ID.
		 * @param int $pay_amount admin Commission Rate.
		 */
		public function wkmp_update( $seller_id, $pay_amount ) {
			$wpdb_obj = $this->wpdb;
			$result   = array(
				'error' => 1,
			);

			$seller_id = intval( $seller_id );

			if ( ! empty( $seller_id ) && ! empty( $pay_amount ) ) {

				$seller_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mpcommision WHERE seller_id = %d", esc_attr( $seller_id ) ) );

				if ( ! empty( $seller_data ) ) {

					$paid_amount      = $seller_data[0]->paid_amount + $pay_amount;
					$last_paid_amount = $pay_amount;

					$res = $wpdb_obj->update(
						"{$wpdb_obj->prefix}mpcommision",
						array(
							'paid_amount'       => wc_format_decimal( $paid_amount, 2 ),
							'last_paid_ammount' => wc_format_decimal( $last_paid_amount, 2 ),
						),
						array( 'seller_id' => $seller_id ),
						array( '%f', '%f', '%f' ),
						array( '%d' )
					);
					if ( $res ) {
						$result = array(
							'error' => 0,
							'msg'   => esc_html__( 'Amount Transferred Successfully.!', 'wk-marketplace' ),
						);
					}
				}
			}

			return $result;
		}

		/**
		 * Calculate product commission.
		 *
		 * @param int   $product_id product id.
		 * @param int   $pro_qty product quantity.
		 * @param int   $pro_price product price.
		 * @param int   $assigned_seller seller field.
		 * @param float $tax_amount tax amount.
		 *
		 * @return array
		 */
		public function wkmp_calculate_product_commission( $product_id = '', $pro_qty = '', $pro_price = '', $assigned_seller = '', $tax_amount = 0 ) {
			$data     = array();
			$wpdb_obj = $this->wpdb;

			if ( ! empty( $product_id ) ) {
				$product              = get_post( $product_id );
				$seller_id            = empty( $assigned_seller ) ? $product->post_author : $assigned_seller;
				$commission_on_seller = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT commision_on_seller FROM {$wpdb_obj->prefix}mpcommision WHERE seller_id = %d", esc_attr( $seller_id ) ) );
				$commission_on_seller = apply_filters( 'wkmp_alter_seller_commission', $commission_on_seller, $product_id, $pro_qty, $seller_id );
				$product_price        = $pro_price + $tax_amount;
				$comm_type            = 'percent';
				$commission_applied   = 0;
				$admin_commission     = $product_price;

				if ( empty( $commission_on_seller ) ) {
					if ( ! user_can( $seller_id, 'administrator' ) ) {
						$default_commission = get_option( '_wkmp_default_commission', 0 );
						$admin_commission   = ( $product_price / 100 ) * $default_commission;
						$commission_applied = $default_commission;
					} else {
						$comm_type = 'fixed';
					}
				} else {
					$admin_commission   = ( $product_price / 100 ) * $commission_on_seller;
					$commission_applied = $commission_on_seller;
				}

				$data = array(
					'seller_id'          => $seller_id,
					'total_amount'       => $product_price,
					'admin_commission'   => $admin_commission,
					'seller_amount'      => $product_price - $admin_commission,
					'commission_applied' => $commission_applied,
					'commission_type'    => $comm_type,
				);
			}

			return apply_filters( 'wkmp_calculate_product_commission', $data );
		}

		/**
		 * Get seller ids regarding order id.
		 *
		 * @param int $order_id order id.
		 */
		public function wkmp_get_sellers_in_order( $order_id = '' ) {
			$wpdb_obj     = $this->wpdb;
			$seller_ids   = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT DISTINCT seller_id FROM {$wpdb_obj->prefix}mporders WHERE order_id = %d", esc_attr( $order_id ) ) );
			$seller_array = wp_list_pluck( $seller_ids, 'seller_id' );

			return apply_filters( 'wkmp_get_sellers_in_order', $seller_array, $order_id );
		}

		/**
		 * Returns final seller data according to order id.
		 *
		 * @param int $order_id order id.
		 * @param int $seller_id seller id.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_final_order_info( $order_id, $seller_id ) {
			$wpdb_obj   = $this->wpdb;
			$sell_order = wc_get_order( $order_id );
			$data       = array();

			if ( $sell_order instanceof \WC_Order ) {
				$or_status    = $sell_order->get_status();
				$sel_ord_data = $this->wkmp_get_seller_order_info( $order_id, $seller_id );

				$sel_amt   = 0;
				$admin_amt = 0;

				if ( ! empty( $sel_ord_data ) ) {
					$sel_amt   = $sel_ord_data['total_sel_amt'] + $sel_ord_data['ship_data'];
					$admin_amt = $sel_ord_data['total_comision'];
				}

				$rwd_data = array();

				$reward_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'order_reward' ", esc_attr( $seller_id ), esc_attr( $order_id ) ) );

				if ( ! empty( $reward_data ) ) {
					$sel_amt            = $sel_amt - $reward_data[0]->meta_value;
					$rwd_data['seller'] = $reward_data[0]->meta_value;
				}

				$walt_data = array();

				$wallet_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'order_wallet_amt' ", esc_attr( $seller_id ), esc_attr( $order_id ) ) );

				if ( ! empty( $wallet_data ) ) {
					$sel_amt             = $sel_amt - $wallet_data[0]->meta_value;
					$walt_data['seller'] = $wallet_data[0]->meta_value;
				}

				$seller_order_tax = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'seller_order_tax' ", $seller_id, $order_id ) );

				if ( ! empty( $seller_order_tax ) ) {
					$sel_amt += $seller_order_tax;
				}

				$paid_status = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );
				$act_status  = empty( $paid_status ) ? 'not_paid' : $paid_status;

				$seller_order_refund_data = $this->wkmp_get_seller_order_refund_data( $order_id, $seller_id );
				$refunded_amount          = empty( $seller_order_refund_data['refunded_amount'] ) ? 0 : $seller_order_refund_data['refunded_amount'];

				$data = array(
					'id'                  => $order_id . '-' . $seller_id,
					'order_id'            => $order_id,
					'product'             => $sel_ord_data['pro_info'],
					'quantity'            => $sel_ord_data['total_qty'],
					'product_total'       => $sel_ord_data['pro_total'],
					'total_seller_amount' => $sel_amt,
					'refunded_amount'     => $refunded_amount,
					'total_commission'    => $admin_amt,
					'status'              => $or_status,
					'reward_data'         => $rwd_data,
					'wallet_data'         => $walt_data,
					'shipping'            => $sel_ord_data['ship_data'],
					'discount'            => $sel_ord_data['discount'],
					'action'              => $act_status,
					'tax'                 => $seller_order_tax,
				);
			}

			return apply_filters( 'wk_marketplace_final_seller_ord_info', $data );
		}

		/**
		 * Get seller refund data
		 *
		 * @param int    $order_id Order id.
		 * @param string $user_id User id.
		 *
		 * @return array|mixed|string
		 */
		public function wkmp_get_seller_order_refund_data( $order_id, $user_id = '' ) {
			$wpdb_obj                 = $this->wpdb;
			$seller_order_refund_data = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = %s ", esc_attr( $user_id ), esc_attr( $order_id ), esc_attr( '_wkmp_refund_status' ) ) );

			return ! empty( $seller_order_refund_data ) ? maybe_unserialize( $seller_order_refund_data ) : array();
		}

		/**
		 * Update seller data according to order id.
		 *
		 * @param int $order_id order id.
		 */
		public function wkmp_update_seller_order_info( $order_id ) {
			if ( $order_id ) {
				$wpdb_obj = $this->wpdb;
				$sellers  = $this->wkmp_get_sellers_in_order( $order_id );
				$order    = wc_get_order( $order_id );

				do_action( 'wkmp_manage_order_fee', $order_id );

				if ( ! empty( $sellers ) ) {
					foreach ( $sellers as $seller_id ) {
						$sel_ord_data = $this->wkmp_get_seller_order_info( $order_id, $seller_id );
						$sel_amt      = 0;
						$admin_amt    = 0;

						$sel_ord_data = apply_filters( 'wk_marketplace_manage_order_fee', $sel_ord_data, $order_id, $seller_id );

						if ( ! empty( $sel_ord_data ) ) {
							$sel_amt   = apply_filters( 'mpmc_get_default_price', $sel_ord_data['total_sel_amt'] + $sel_ord_data['ship_data'], $order->get_currency() );
							$admin_amt = apply_filters( 'mpmc_get_default_price', $sel_ord_data['total_comision'], $order->get_currency() );
						}

						$sel_com_data = $wpdb_obj->get_results( $wpdb_obj->prepare( " SELECT * FROM {$wpdb_obj->prefix}mpcommision WHERE seller_id = %d", esc_attr( $seller_id ) ) );

						if ( $sel_com_data ) {
							$sel_com_data  = $sel_com_data[0];
							$admin_amount  = ( floatval( $sel_com_data->admin_amount ) + $admin_amt );
							$seller_amount = ( floatval( $sel_com_data->seller_total_ammount ) + $sel_amt );

							$wpdb_obj->get_results( $wpdb_obj->prepare( " UPDATE {$wpdb_obj->prefix}mpcommision SET admin_amount = %f, seller_total_ammount = %f, last_com_on_total = %f WHERE seller_id = %d", esc_attr( $admin_amount ), esc_attr( $seller_amount ), esc_attr( $seller_amount ), esc_attr( $seller_id ) ) );
						} else {
							$wpdb_obj->insert(
								$wpdb_obj->prefix . 'mpcommision',
								array(
									'seller_id'            => $seller_id,
									'admin_amount'         => wc_format_decimal( $admin_amt, 2 ),
									'seller_total_ammount' => wc_format_decimal( $sel_amt, 2 ),
								)
							);
						}
					}
				}
			}
		}

		/**
		 * Returns seller data according to order id.
		 *
		 * @param int $order_id order id.
		 * @param int $seller_id seller id.
		 *
		 * @return array $data
		 */
		public function wkmp_get_seller_order_info( $order_id, $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$discount = array(
				'seller' => 0,
				'admin'  => 0,
			);

			$product_info        = array();
			$quantity            = 0;
			$product_total       = 0;
			$total_seller_amount = 0;
			$total_commission    = 0;
			$shipping            = 0;

			$sel_order = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders WHERE seller_id = %d AND order_id = %d", esc_attr( $seller_id ), esc_attr( $order_id ) ) );

			if ( ! empty( $sel_order ) ) {
				foreach ( $sel_order as $ord_info ) {
					if ( ! empty( $ord_info->product_id ) ) {
						$product_info[ $ord_info->product_id ] = array(
							'id'         => $ord_info->product_id,
							'title'      => get_the_title( $ord_info->product_id ),
							'quantity'   => $ord_info->quantity,
							'line_total' => $ord_info->amount,
							'discount'   => $ord_info->discount_applied,
							'commission' => $ord_info->admin_amount,
						);
					}

					if ( ! empty( $ord_info->quantity ) ) {
						$quantity = $quantity + $ord_info->quantity;
					}

					if ( ! empty( $ord_info->amount ) ) {
						$product_total = $product_total + $ord_info->amount;
					}

					if ( ! empty( $ord_info->seller_amount ) ) {
						$total_seller_amount = $total_seller_amount + $ord_info->seller_amount;
					}

					if ( ! empty( $ord_info->admin_amount ) ) {
						$total_commission = $total_commission + $ord_info->admin_amount;
					}

					if ( ! empty( $ord_info->discount_applied ) ) {

						$discount_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'discount_code' ", $seller_id, $ord_info->order_id ) );

						if ( ! empty( $discount_data ) ) {
							$discount['seller'] = $discount['seller'] + $ord_info->discount_applied;
						} elseif ( $ord_info->discount_applied > 0 ) {
							$discount['admin'] = $discount['admin'] + $ord_info->discount_applied;
						}
					}

					$ship_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'shipping_cost' ", esc_attr( $seller_id ), esc_attr( $ord_info->order_id ) ) );

					if ( ! empty( $ship_data ) ) {
						$shipping = $ship_data[0]->meta_value;
					}
				}
			}

			$data = array(
				'pro_info'       => $product_info,
				'total_qty'      => $quantity,
				'pro_total'      => $product_total,
				'total_sel_amt'  => $total_seller_amount,
				'total_comision' => $total_commission,
				'discount'       => $discount,
				'ship_data'      => $shipping,
			);

			return apply_filters( 'wkmp_get_seller_final_order_info', $data, $order_id, $seller_id );
		}

		/**
		 * Get seller commission
		 *
		 * @param int $order_id order id.
		 * @param int $seller_id seller id.
		 *
		 * @return array $data
		 */
		public function wkmp_get_sel_comission_via_order( $order_id, $seller_id ) {
			$wpdb_obj            = $this->wpdb;
			$data                = array();
			$i                   = 0;
			$ord_id              = array();
			$product             = array();
			$quantity            = array();
			$product_total       = array();
			$total_seller_amount = array();
			$total_commission    = array();
			$status              = array();
			$paid_status         = array();

			$sel_order = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders WHERE seller_id = %d AND order_id = %d", esc_attr( $seller_id ), esc_attr( $order_id ) ) );

			if ( ! empty( $sel_order ) ) {
				$order_arr      = array();
				$id             = array();
				$shipping       = array();
				$discount       = array();
				$action         = array();
				$final_discount = array();

				foreach ( $sel_order as $value ) {
					$discount_arr = array();
					$o_id         = $value->order_id;
					$order        = wc_get_order( $o_id );
					$product_id   = $value->product_id;

					if ( in_array( $o_id, $order_arr, true ) ) {

						$key                      = array_search( $o_id, $order_arr, true );
						$product_info             = get_the_title( $product_id ) . '( #' . $product_id . ' )';
						$quantity_info            = $value->quantity;
						$product_total_info       = $value->amount;
						$total_seller_amount_info = $value->seller_amount;
						$total_commission_info    = $value->admin_amount;
						$discount_by              = '';
						$o_discount               = 0;

						if ( 0 !== $value->discount_applied ) {

							$discount_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'discount_amt' ", esc_attr( $seller_id ), esc_attr( $o_id ) ) );

							if ( ! empty( $discount_data ) ) {
								$discount_by              = 'S';
								$total_seller_amount_info = $total_seller_amount_info - $value->discount_applied;
								$o_discount               = $value->discount_applied;
							} else {
								$discount_by           = 'A';
								$total_commission_info = $total_commission_info - $value->discount_applied;
								$o_discount            = $value->discount_applied;
							}
						}

						$discount_arr = $data[ $key ]['discount'];

						if ( '' !== $discount_by && 0 !== $o_discount ) {
							array_push(
								$discount_arr,
								array(
									'by'     => $discount_by,
									'amount' => $o_discount,
								)
							);
						}

						$data[ $key ]['product']             = $data[ $key ]['product'] . ' + ' . $product_info;
						$data[ $key ]['quantity']            = $data[ $key ]['quantity'] + $quantity_info;
						$data[ $key ]['discount']            = $discount_arr;
						$data[ $key ]['product_total']       = $data[ $key ]['product_total'] + $product_total_info;
						$data[ $key ]['total_seller_amount'] = $data[ $key ]['total_seller_amount'] + $total_seller_amount_info;
						$data[ $key ]['total_commission']    = $data[ $key ]['total_commission'] + $total_commission_info;

						continue;
					} else {
						$order_arr[ $i ] = $o_id;
					}

					$product_id            = $value->product_id;
					$id[]                  = $o_id . '-' . $seller_id;
					$ord_id[]              = $o_id;
					$product[]             = get_the_title( $product_id ) . '( #' . $product_id . ' )';
					$quantity[]            = $value->quantity;
					$product_total[]       = $value->amount;
					$total_seller_amount[] = $value->seller_amount;
					$total_commission[]    = $value->admin_amount;
					$status[]              = $order->get_status();

					$ship_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'shipping_cost' ", $seller_id, $o_id ) );

					if ( ! empty( $ship_data ) ) {
						$shipping[] = $ship_data[0]->meta_value;
					} else {
						$shipping[] = 0;
					}

					$discount_by = '';

					if ( 0 !== $value->discount_applied ) {
						$discount_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'discount_amt' ", $seller_id, $o_id ) );
						if ( ! empty( $discount_data ) ) {
							$discount_by = 'S';
							$discount[]  = $value->discount_applied;
						} else {
							$discount_by = 'A';
							$discount[]  = $value->discount_applied;
						}
					} else {
						$discount[] = 0;
					}

					$pay_data = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'paid_status' ", esc_attr( $seller_id ), esc_attr( $o_id ) ) );

					if ( ! empty( $pay_data ) ) {
						$paid_status[] = $pay_data;
					} else {
						$paid_status[] = 'Not Paid';
					}

					if ( 'paid' === $paid_status[ $i ] ) {
						$action[] = '<button class="button button-primary" class="admin-order-pay" disabled>' . esc_html__( 'Paid', 'wk-marketplace' ) . '</button>';
					} else {
						$action[] = '<a href="javascript:void(0)" data-id="' . esc_attr( $id[ $i ] ) . '" class="page-title-action admin-order-pay">' . esc_html__( 'Pay', 'wk-marketplace' ) . '</a>';
					}

					if ( 'S' === $discount_by ) {
						$total_seller_amount[ $i ] = $total_seller_amount[ $i ] + $shipping[ $i ] - $discount[ $i ];
						$final_discount[]          = $discount[ $i ];
					} else {
						$total_seller_amount[ $i ] = $total_seller_amount[ $i ] + $shipping[ $i ];
					}

					if ( 'A' === $discount_by ) {
						$total_commission[ $i ] = $total_commission[ $i ] - $discount[ $i ];
						$final_discount[]       = $discount[ $i ];
					} else {
						$total_commission[ $i ] = $total_commission[ $i ];
					}

					if ( '' === $discount_by ) {
						$final_discount[] = $discount[ $i ];
					}

					if ( '' !== $discount_by && 0 !== $final_discount[ $i ] ) {
						array_push(
							$discount_arr,
							array(
								'by'     => $discount_by,
								'amount' => $final_discount[ $i ],
							)
						);
					}

					$data[] = array(
						'id'                  => $id[ $i ],
						'order_id'            => $ord_id[ $i ],
						'product'             => $product[ $i ],
						'quantity'            => $quantity[ $i ],
						'product_total'       => $product_total[ $i ],
						'total_seller_amount' => $total_seller_amount[ $i ],
						'total_commission'    => $total_commission[ $i ],
						'status'              => $status[ $i ],
						'shipping'            => $shipping[ $i ],
						'discount'            => $discount_arr,
						'paid_status'         => $paid_status[ $i ],
						'action'              => $action[ $i ],
					);
					++ $i;
				}
			}

			return apply_filters( 'wkmp_get_sel_comission_via_order', $data[0], $order_id, $seller_id );
		}
	}
}
