<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

use WkMarketplace\Helper\Common;
use WkMarketplace\Helper\Admin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Notification_Functions' ) ) {

	/**
	 * Class WKMP_Notification_Functions
	 *
	 * @package WkMarketplace\Includes\Common
	 */
	class WKMP_Notification_Functions {
		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Notification
		 */
		private $db_obj;

		/**
		 * Order db object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data $order_db_obj Order db object.
		 */
		private $order_db_obj;

		/**
		 * WKMP_Notification_Functions constructor.
		 */
		public function __construct() {
			$this->db_obj       = new Common\WKMP_Seller_Notification();
			$this->order_db_obj = new Admin\WKMP_Seller_Order_Data();
		}

		/**
		 * Custom order processing.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked woocommerce_checkout_order_processed
		 */
		public function wkmp_custom_process_order( $order_id ) {
			$seller_ids          = array();
			$order               = wc_get_order( $order_id );
			$send_mail_to_seller = apply_filters( 'wkmp_send_notification_mail_to_seller_for_new_order', true, $order );

			if ( $send_mail_to_seller ) {
				$this->wkmp_send_mail_to_inform_seller( $order );
			}

			$items        = $order->get_items();
			$order_author = get_post_meta( $order_id, '_customer_user', true );

			$author_name = 'Guest';
			if ( $order_author > 0 ) {
				$author_name = get_user_by( 'ID', $order_author )->display_name;
			}

			foreach ( $items as $item ) {
				$product      = get_post( $item['product_id'] );
				$seller_ids[] = $product->post_author;
			}

			$now     = new \DateTime( 'now' );
			$content = sprintf( /* Translators: %s: Author name. */ esc_html__( 'Order has been placed by %s', 'wk-marketplace' ), '<strong>' . esc_html( $author_name ) . '</strong>' );

			foreach ( $seller_ids as $seller_id ) {
				$order_approval_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_order_approval', true );
				$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

				if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
					continue;
				}

				$data = array(
					'type'      => 'order',
					'author_id' => $seller_id,
					'context'   => $order_id,
					'content'   => $content,
					'read_flag' => 0,
					'timestamp' => $now->format( 'Y-m-d H:i:s' ),
				);
				$this->db_obj->wkmp_add_new_notification( $data );
			}
		}

		/**
		 * Save on product update.
		 *
		 * @param string   $new_status New status.
		 * @param string   $old_status Old status.
		 * @param \WP_Post $post Post object.
		 *
		 * @hooked transition_post_status
		 */
		public function wkmp_save_on_product_update( $new_status, $old_status, $post ) {
			if ( 'publish' !== $old_status && 'publish' === $new_status && ! empty( $post->ID ) && in_array( $post->post_type, array( 'product' ), true ) ) {
				$product_data = get_post( $post->ID );
				$content      = esc_html__( 'has been approved', 'wk-marketplace' );
				$now          = new \DateTime( 'now' );

				$data = array(
					'type'      => 'product',
					'author_id' => $product_data->post_author,
					'context'   => $post->ID,
					'content'   => $content,
					'read_flag' => 0,
					'timestamp' => $now->format( 'Y-m-d H:i:s' ),
				);

				$this->db_obj->wkmp_add_new_notification( $data );
			}
		}

		/**
		 * Save seller review notification.
		 *
		 * @param array $data Data.
		 * @param int   $review_id Review id.
		 */
		public function wkmp_save_seller_review_notification( $data, $review_id ) {
			$content = esc_html__( 'New review has been received from', 'wk-marketplace' );
			$now     = new \DateTime( 'now' );
			$args    = array(
				'type'      => 'seller',
				'author_id' => $data['mp_wk_seller'],
				'context'   => $data['mp_wk_user'],
				'content'   => $content,
				'read_flag' => 0,
				'timestamp' => $now->format( 'Y-m-d H:i:s' ),
			);
			$this->db_obj->wkmp_add_new_notification( $args );

		}

		/**
		 * Low stock.
		 *
		 * @param \WC_Product $product Product object.
		 *
		 * @hooked woocommerce_low_stock_notification
		 */
		public function wkmp_low_stock( $product ) {
			$product_data = get_post( $product->get_id() );
			$author_id    = $product_data->post_author;
			$content      = sprintf( /* translators: %s stock quantity. */ esc_html__( 'Product is low in stock. There are %s left', 'wk-marketplace' ), $product->get_stock_quantity() );

			$now = new \DateTime( 'now' );

			$data = array(
				'type'      => 'product',
				'author_id' => $author_id,
				'context'   => $product->get_id(),
				'content'   => $content,
				'read_flag' => 0,
				'timestamp' => $now->format( 'Y-m-d H:i:s' ),
			);
			$this->db_obj->wkmp_add_new_notification( $data );
		}

		/**
		 * No stock.
		 *
		 * @param \WC_Product $product Product Object.
		 */
		public function wkmp_no_stock( $product ) {
			$product_data = get_post( $product->get_id() );
			$author_id    = $product_data->post_author;
			$content      = esc_html__( 'Product is out of stock', 'wk-marketplace' );
			$now          = new \DateTime( 'now' );
			$args         = array(
				'type'      => 'product',
				'author_id' => $author_id,
				'context'   => $product->get_id(),
				'content'   => $content,
				'read_flag' => 0,
				'timestamp' => $now->format( 'Y-m-d H:i:s' ),
			);
			$this->db_obj->wkmp_add_new_notification( $args );
		}

		/**
		 * Order processing.
		 *
		 * @param int $order_id Order id.
		 */
		public function wkmp_order_processing_notification( $order_id ) {
			$seller_ids = array();
			$order      = wc_get_order( $order_id );
			$items      = $order->get_items();

			if ( ! empty( $items ) ) {

				foreach ( $items as $item ) {
					if ( isset( $item['product_id'] ) && $item['product_id'] > 0 ) {
						$product      = get_post( $item['product_id'] );
						$seller_ids[] = $product->post_author;
					}
				}

				if ( ! empty( $seller_ids ) ) {

					$content = esc_html__( 'Order status has been changed to <strong>Processing</strong>', 'wk-marketplace' );

					foreach ( $seller_ids as $seller_id ) {
						$order_approval_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_order_approval', true );
						$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

						if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
							continue;
						}

						$now  = new \DateTime( 'now' );
						$data = array(
							'type'      => 'order',
							'author_id' => $seller_id,
							'context'   => $order_id,
							'content'   => $content,
							'read_flag' => 0,
							'timestamp' => $now->format( 'Y-m-d H:i:s' ),
						);

						$this->db_obj->wkmp_add_new_notification( $data );
					}
				}
			}
		}

		/**
		 * Order completed notification.
		 *
		 * @param int $order_id Order Id.
		 */
		public function wkmp_order_completed_notification( $order_id ) {
			$seller_ids = array();
			$order      = wc_get_order( $order_id );
			$items      = $order->get_items();

			if ( ! empty( $items ) ) {
				foreach ( $items as $item ) {
					if ( isset( $item['product_id'] ) && $item['product_id'] > 0 ) {
						$product      = get_post( $item['product_id'] );
						$seller_ids[] = $product->post_author;
					}
				}

				if ( ! empty( $seller_ids ) ) {
					$content = esc_html__( 'Order status has been changed to <strong>Completed</strong>', 'wk-marketplace' );

					foreach ( $seller_ids as $seller_id ) {
						$order_approval_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_order_approval', true );
						$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

						if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
							continue;
						}

						$now  = new \DateTime( 'now' );
						$data = array(
							'type'      => 'order',
							'author_id' => $seller_id,
							'context'   => $order_id,
							'content'   => $content,
							'read_flag' => 0,
							'timestamp' => $now->format( 'Y-m-d H:i:s' ),
						);
						$this->db_obj->wkmp_add_new_notification( $data );
					}
				}
			}
		}

		/**
		 * Seller Information.
		 *
		 * @param \WC_Order $order Order Object.
		 */
		public function wkmp_send_mail_to_inform_seller( $order ) {
			global $wkmarketplace;
			$items    = $order->get_items();
			$order_id = $order->get_id();
			$sellers  = array();

			foreach ( $items as $item ) {
				$item_id   = $item['product_id'];
				$author_id = get_post_field( 'post_author', $item_id );
				$is_seller = $wkmarketplace->wkmp_user_is_seller( $author_id );

				if ( ! $is_seller ) {
					continue;
				}

				$order_approval_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_order_approval', true );
				$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $author_id, $order_id );

				if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
					continue;
				}

				$send_to               = $this->db_obj->wkmp_get_author_email_by_item_id( $item_id );
				$sellers[ $send_to ][] = $item;
			}

			foreach ( $sellers as $email => $items ) {
				do_action( 'wkmp_seller_product_ordered', $order_id, $items, $email );
			}
		}
	}
}
