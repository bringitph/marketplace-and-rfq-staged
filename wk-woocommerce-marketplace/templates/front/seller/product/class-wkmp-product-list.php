<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Product;

use WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Product_List' ) ) {
	/**
	 * Seller products list class.
	 *
	 * Class WKMP_Product_List
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Product
	 */
	class WKMP_Product_List {
		/**
		 * DB Product Object.
		 *
		 * @var Front\WKMP_Product_Queries
		 */
		private $db_product_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Product_List constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->db_product_obj = new Front\WKMP_Product_Queries();
			$this->seller_id      = $seller_id;

			// Delete multiple product.
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $posted_data['wkmp-delete-product-nonce'] ) && ! empty( $posted_data['wkmp-delete-product-nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp-delete-product-nonce'] ), 'wkmp-delete-product-nonce-action' ) ) {
					$this->wkmp_delete_product( $posted_data['selected'] );
				}
			}

			// Delete Single Product.
			if ( 'delete' === get_query_var( 'action' ) && get_query_var( 'pid' ) ) {
				$this->wkmp_delete_product( array( get_query_var( 'pid' ) ) );
			}
			$this->update_minimum_order_amount();
			$this->wkmp_product_list();
		}

		/**
		 * Product list.
		 *
		 * @return void
		 */
		public function wkmp_product_list() {
			global $wkmarketplace;

			$get_data    = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filter_name = '';

			// Filter product.
			if ( isset( $get_data['wkmp_product_search_nonce'] ) && ! empty( $get_data['wkmp_product_search_nonce'] ) && wp_verify_nonce( wp_unslash( $get_data['wkmp_product_search_nonce'] ), 'wkmp_product_search_nonce_action' ) ) {
				if ( isset( $get_data['wkmp_search'] ) && $get_data['wkmp_search'] ) {
					$filter_name = filter_input( INPUT_GET, 'wkmp_search', FILTER_SANITIZE_STRING );
				}
			}

			$page  = get_query_var( 'pagenum' ) ? get_query_var( 'pagenum' ) : 1;
			$limit = 20;

			$filter_data = array(
				'start'       => ( $page - 1 ) * $limit,
				'limit'       => $limit,
				'filter_name' => $filter_name,
			);

			$product_data = $this->db_product_obj->wkmp_get_seller_products( $filter_data, $this->seller_id );
			$total        = $this->db_product_obj->wkmp_get_seller_total_products( $filter_data, $this->seller_id );

			$products             = array();
			$stock_status_options = wc_get_product_stock_status_options();

			foreach ( $product_data as $product ) {
				$img   = wp_get_attachment_image_src( get_post_meta( $product->ID, '_thumbnail_id', true ) );
				$image = wc_placeholder_img_src();

				if ( $img ) {
					$image = $img[0];
				}

				$product_obj          = wc_get_product( $product->ID );
				$price                = $product_obj->get_price_html() ? wp_kses_post( $product_obj->get_price_html() ) : '<span class="na">&ndash;</span>';
				$product_stock_status = $product_obj->get_stock_status();

				$products[] = array(
					'product_id'     => $product->ID,
					'name'           => $product->post_title,
					'product_href'   => get_permalink( $product->ID ),
					'status'         => ucfirst( $product->post_status ),
					'image'          => $image,
					'stock'          => ! empty( $stock_status_options[ $product_stock_status ] ) ? $stock_status_options[ $product_stock_status ] : ucfirst( $product_stock_status ),
					'stock_quantity' => 'outofstock' === $product_stock_status ? 0 : $product_obj->get_stock_quantity(),
					'price'          => $price,
					'edit'           => home_url( $wkmarketplace->seller_page_slug . '/product/?wkmp_product_edit=' . (int) $product->ID ),
					'delete'         => home_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_product_list_endpoint', 'product-list' ) . '/delete/' . (int) $product->ID ),
				);
			}

			$url        = get_permalink() . get_option( '_wkmp_product_list_endpoint', 'product-list' );
			$pagination = $wkmarketplace->wkmp_get_pagination( $total, $page, $limit, $url );

			$wkmp_min_order_enabled         = get_option( '_wkmp_enable_minimum_order_amount', false );
			$wkmp_min_order_amount          = get_user_meta( $this->seller_id, '_wkmp_minimum_order_amount', true );
			$wkmp_product_qty_limit_enabled = get_option( '_wkmp_enable_product_qty_limit', false );
			$wkmp_max_product_qty           = get_user_meta( $this->seller_id, '_wkmp_max_product_qty_limit', true );

			require_once __DIR__ . '/wkmp-seller-product-list.php';
		}

		/**
		 * Delete seller product
		 *
		 * @param array $product_ids product ids.
		 */
		public function wkmp_delete_product( $product_ids ) {
			if ( $product_ids ) {
				foreach ( $product_ids as $product_id ) {
					wp_delete_post( $product_id );
				}
			}

			wc_add_notice( esc_html__( 'Product deleted successfully', 'wk-marketplace' ), 'success' );
		}

		/**
		 * Updating minimum order settings.
		 */
		public function update_minimum_order_amount() {
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();//phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( ! empty( $posted_data['wkmp-min-order-nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp-min-order-nonce'] ), 'wkmp-min-order-nonce-action' ) ) {
					$amount    = isset( $posted_data['_wkmp_minimum_order_amount'] ) ? $posted_data['_wkmp_minimum_order_amount'] : 0;
					$qty       = isset( $posted_data['_wkmp_max_product_qty_limit'] ) ? $posted_data['_wkmp_max_product_qty_limit'] : 0;
					$seller_id = $this->seller_id > 0 ? $this->seller_id : get_current_user_id();
					if ( empty( $amount ) ) {
						delete_user_meta( $seller_id, '_wkmp_minimum_order_amount' );
					} else {
						$amount = number_format( $amount, 2 );
						update_user_meta( $seller_id, '_wkmp_minimum_order_amount', $amount );
					}

					if ( empty( $qty ) ) {
						delete_user_meta( $seller_id, '_wkmp_max_product_qty_limit' );
					} else {
						update_user_meta( $seller_id, '_wkmp_max_product_qty_limit', $qty );
					}
				}
			}
		}
	}
}
