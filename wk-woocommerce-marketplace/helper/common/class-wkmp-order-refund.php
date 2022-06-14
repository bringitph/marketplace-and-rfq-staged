<?php
/**
 * WKMP order refund data query
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

use WkMarketplace\Helper\Admin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Order_Refund' ) ) {
	/**
	 * MP_Order_Refund Class.
	 */
	class WKMP_Order_Refund {
		/**
		 * Refund args.
		 *
		 * @var array $refund_args Refund args.
		 */
		protected $refund_args = array();

		/**
		 * User id.
		 *
		 * @var array|int user id.
		 */
		protected $user_id = array();

		/**
		 * WPDB Objet.
		 *
		 * @var \QM_DB|string|\wpdb WPDB Object.
		 */
		protected $wpdb = '';

		/**
		 * Meta table.
		 *
		 * @var string meta table.
		 */
		protected $mporders_meta_table = '';

		/**
		 * Commission table.
		 *
		 * @var string commission table.
		 */
		protected $mpcommision_table = '';

		/**
		 * Order db object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data $order_db_obj Order db object.
		 */
		private $order_db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Order_Refund constructor.
		 *
		 * @param array $args Args.
		 */
		public function __construct( $args = array() ) {
			global $wpdb;
			$this->wpdb                = $wpdb;
			$this->refund_args         = $args;
			$this->user_id             = get_current_user_id();
			$this->mporders_meta_table = $wpdb->prefix . 'mporders_meta';
			$this->mpcommision_table   = $wpdb->prefix . 'mpcommision';
			$this->order_db_obj        = new Admin\WKMP_Seller_Order_Data();
		}

		/**
		 * Seller refund process
		 *
		 * @throws \Exception \WC_REST_Exception WooCommerce Exception.
		 */
		public function wkmp_process_refund() {
			// Create the refund object.
			$refund = wc_create_refund( $this->refund_args );

			if ( ! empty( $refund ) && is_wp_error( $refund ) ) {
				if ( is_admin() ) {
					?>
					<div class='notice notice-error is-dismissible'>
						<p><?php echo esc_html( $refund->get_error_message() ); ?></p>
					</div>
					<?php
				} else {
					wc_print_notice( $refund->get_error_message(), 'error' );
				}
			} else {
				$this->wkmp_set_seller_order_refund_data();
				$order        = wc_get_order( $this->refund_args['order_id'] );
				$seller_email = $this->wkmp_get_seller_email();
				$msg          = esc_html__( 'Refunded successfully.', 'wk-marketplace' );

				do_action( 'wkmp_seller_order_refunded', $order->get_items(), $seller_email, $this->refund_args );

				if ( is_admin() ) {
					?>
					<div class='notice notice-success is-dismissible'>
						<p><?php echo esc_html( $msg ); ?></p>
					</div>
					<?php
				} else {
					wc_print_notice( $msg, 'success' );
				}
			}
		}

		/**
		 * Set refund arguments
		 *
		 * @param array $args Args.
		 *
		 * @return void
		 */
		public function wkmp_set_refund_args( $args = array() ) {
			$this->refund_args = $args;
		}

		/**
		 * Set Seller id
		 *
		 * @param int $user_id User id.
		 *
		 * @return void
		 */
		public function wkmp_set_seller_id( $user_id = '' ) {
			$this->user_id = $user_id;
		}

		/**
		 * Get seller email
		 *
		 * @return string $email
		 */
		public function wkmp_get_seller_email() {
			return get_userdata( $this->user_id )->user_email;
		}

		/**
		 * Set seller order refund data
		 *
		 * @return void
		 */
		public function wkmp_set_seller_order_refund_data() {
			$wpdb_obj                 = $this->wpdb;
			$order_id                 = $this->refund_args['order_id'];
			$this->user_id            = apply_filters( 'wkmp_modify_order_refund_user_id', $this->user_id, $order_id );
			$seller_order_refund_data = $this->wkmp_get_seller_order_refund_data( $order_id );

			if ( empty( $seller_order_refund_data ) ) {
				$seller_order_refund_data = array(
					'line_items'      => $this->refund_args['line_items'],
					'refunded_amount' => round( $this->refund_args['amount'], 2 ),
				);

				$wpdb_obj->insert(
					$this->mporders_meta_table,
					array(
						'seller_id'  => $this->user_id,
						'order_id'   => $order_id,
						'meta_key'   => '_wkmp_refund_status',
						'meta_value' => maybe_serialize( $seller_order_refund_data ),
					),
					array( '%d', '%d', '%s', '%s' )
				);
			} else {
				$seller_order_refund_data = maybe_unserialize( $seller_order_refund_data );

				foreach ( $this->refund_args['line_items'] as $line_item_id => $line_items ) {
					if ( array_key_exists( $line_item_id, $seller_order_refund_data['line_items'] ) ) {
						if ( empty( $seller_order_refund_data['line_items'][ $line_item_id ]['qty'] ) ) {
							$seller_order_refund_data['line_items'][ $line_item_id ]['qty'] = 0;
						}

						if ( empty( $seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] ) ) {
							$seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] = 0;
						}

						$seller_order_refund_data['line_items'][ $line_item_id ]['qty']          += $line_items['qty'];
						$seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] += round( $line_items['refund_total'], 2 );
					} else {
						$seller_order_refund_data['line_items'][ $line_item_id ]['qty']          = $line_items['qty'];
						$seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] = round( $line_items['refund_total'], 2 );
					}

					if ( isset( $line_items['refund_tax'] ) && is_iterable( $line_items['refund_tax'] ) ) {
						foreach ( $line_items['refund_tax'] as $tax_key => $tax_value ) {
							if ( isset( $seller_order_refund_data['line_items'][ $line_item_id ]['refund_tax'][ $tax_key ] ) ) {
								$seller_order_refund_data['line_items'][ $line_item_id ]['refund_tax'][ $tax_key ] += $line_items['refund_tax'][ $tax_key ];
							} else {
								$seller_order_refund_data['line_items'][ $line_item_id ]['refund_tax'][ $tax_key ] = $line_items['refund_tax'][ $tax_key ];
							}
						}
					}
				}

				if ( ! isset( $seller_order_refund_data['fully_refunded'] ) ) {
					$seller_order_refund_data['refunded_amount'] += round( $this->refund_args['amount'], 2 );
				}

				$wpdb_obj->update(
					$this->mporders_meta_table,
					array( 'meta_value' => maybe_serialize( $seller_order_refund_data ) ),
					array(
						'seller_id' => $this->user_id,
						'order_id'  => $order_id,
						'meta_key'  => '_wkmp_refund_status',
					),
					array( '%s' ),
					array( '%d', '%d', '%s' )
				);
			}
			$this->wkmp_update_refund_data_in_seller_sales();
		}

		/**
		 * Set refund arguments
		 *
		 * @return void
		 */
		public function wkmp_update_refund_data_in_seller_sales() {
			$wpdb_obj   = $this->wpdb;
			$sales_data = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT seller_total_ammount, paid_amount, total_refunded_amount FROM $this->mpcommision_table WHERE seller_id = %d", esc_attr( $this->user_id ) ), ARRAY_A );
			$order_id   = $this->refund_args['order_id'];

			$paid_status           = $this->order_db_obj->wkmp_get_order_pay_status( $this->user_id, $order_id );
			$exchange_rate         = get_post_meta( $order_id, 'mpmc_exchange_rate', true );
			$exchange_rate         = ! empty( $exchange_rate ) ? $exchange_rate : 1;
			$refunded_amount       = $this->refund_args['amount'] / $exchange_rate;
			$seller_total_ammount  = floatval( $sales_data['seller_total_ammount'] - round( $refunded_amount, 2 ) );
			$total_refunded_amount = floatval( $sales_data['total_refunded_amount'] + round( $refunded_amount, 2 ) );

			if ( 'paid' === $paid_status ) {
				$paid_amount = floatval( $sales_data['paid_amount'] - round( $refunded_amount, 2 ) );

				$wpdb_obj->update(
					$this->mpcommision_table,
					array(
						'seller_total_ammount'  => $seller_total_ammount,
						'paid_amount'           => $paid_amount,
						'total_refunded_amount' => $total_refunded_amount,
					),
					array( 'seller_id' => $this->user_id ),
					array( '%f', '%f', '%f' ),
					array( '%d' )
				);
			} else {
				$wpdb_obj->update(
					$this->mpcommision_table,
					array(
						'seller_total_ammount'  => $seller_total_ammount,
						'total_refunded_amount' => $total_refunded_amount,
					),
					array( 'seller_id' => $this->user_id ),
					array( '%f', '%f', '%f' ),
					array( '%d' )
				);
			}
		}

		/**
		 * Get seller order refund data
		 *
		 * @param int $order_id order id.
		 * @param int $user_id user id.
		 *
		 * @return array $return
		 */
		public function wkmp_get_seller_order_refund_data( $order_id, $user_id = '' ) {
			$wpdb_obj = $this->wpdb;
			if ( ! empty( $user_id ) ) {
				$this->user_id = $user_id;
			}

			$seller_order_refund_data = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM $this->mporders_meta_table WHERE seller_id = %d AND order_id = %d AND meta_key = %s", esc_attr( $this->user_id ), esc_attr( $order_id ), '_wkmp_refund_status' ) );
			$return                   = ! empty( $seller_order_refund_data ) ? maybe_unserialize( $seller_order_refund_data ) : array();

			return apply_filters( 'wkmp_get_seller_order_refund_data', $return, $order_id, $this->user_id );
		}
	}
}
