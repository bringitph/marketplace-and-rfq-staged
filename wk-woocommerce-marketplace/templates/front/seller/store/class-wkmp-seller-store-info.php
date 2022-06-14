<?php
/**
 * Seller Store info class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Store;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Seller_Store_Info' ) ) {
	/**
	 * Class WKMP_Seller_Store_Info
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Store
	 */
	class WKMP_Seller_Store_Info {
		/**
		 * Error.
		 *
		 * @var array $error Error array.
		 */
		private $error = array();

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Marketplace class object.
		 *
		 * @var \Marketplace $marketplace Marketplace object.
		 */
		private $marketplace;

		/**
		 * Feedback Object.
		 *
		 * @var Common\WKMP_Seller_Feedback
		 */
		private $feedback_obj;

		/**
		 * WKMP_Seller_Store_Info constructor.
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->marketplace  = $wkmarketplace;
			$this->feedback_obj = new Common\WKMP_Seller_Feedback();
			$shop_address       = get_query_var( 'info' );
			$this->seller_id    = $wkmarketplace->wkmp_get_seller_id_by_shop_address( $shop_address );
			$main_page          = get_query_var( 'main_page' );

			if ( $this->seller_id ) {
				if ( get_option( '_wkmp_store_endpoint', 'store' ) === $main_page ) {
					$this->wkmp_display_seller_store();
				} elseif ( get_option( '_wkmp_seller_product_endpoint', 'seller-product' ) === $main_page ) {
					$this->wkmp_seller_store_collection();
				} elseif ( 'add-feedback' === $main_page ) {
					$this->wkmp_seller_add_feedback();
				} elseif ( 'feedback' === $main_page ) {
					$this->wkmp_seller_all_feedback();
				}
			} else {
				esc_html_e( 'page not found', 'wk-marketplace' );
				die();
			}
		}

		/**
		 * Display seller all feedback
		 *
		 * @return void
		 */
		public function wkmp_seller_all_feedback() {
			$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
			$page        = get_query_var( 'pagenum' ) ? get_query_var( 'pagenum' ) : 1;
			$limit       = 20;

			$filter_data = array(
				'filter_seller_id' => $this->seller_id,
				'status'           => 1,
				'start'            => ( $page - 1 ) * $limit,
				'limit'            => $limit,
			);

			$reviews    = $this->feedback_obj->wkmp_get_seller_feedbacks( $filter_data );
			$total      = $this->feedback_obj->wkmp_get_seller_total_feedbacks( $filter_data );
			$url        = home_url( $this->marketplace->seller_page_slug . '/feedback/' . $seller_info->shop_address );
			$pagination = $this->marketplace->wkmp_get_pagination( $total, $page, $limit, $url );

			require_once __DIR__ . '/wkmp-seller-all-feedback.php';
		}

		/**
		 * Seller product collection
		 *
		 * @return void
		 */
		public function wkmp_seller_store_collection() {
			$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
			require_once __DIR__ . '/wkmp-seller-store-collection.php';
		}

		/**
		 * Add seller feedback
		 *
		 * @return void
		 */
		public function wkmp_seller_add_feedback() {
			$seller_info     = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
			$current_user_id = get_current_user_id();

			$review_check  = $this->feedback_obj->wkmp_get_seller_feedbacks(
				array(
					'filter_seller_id' => $this->seller_id,
					'filter_user_id'   => $current_user_id,
				)
			);
			$review_status = isset( $review_check[0]->status ) ? $review_check[0]->status : '3';

			if ( $current_user_id > 0 && 0 !== intval( $review_status ) ) {
				if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && $this->wkmp_validate_add_feedback_form() ) {
					$posted_data                 = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
					$posted_data['mp_wk_user']   = $current_user_id;
					$posted_data['mp_wk_seller'] = $this->seller_id;

					do_action( 'wkmp_save_seller_feedback', $posted_data, $this->seller_id );

					wc_print_notice( esc_html__( 'Feedback added successfully.', 'wk-marketplace' ), 'success' );
				}
			}

			if ( $this->error ) {
				wc_print_notice( $this->error['warning_error'], 'error' );
				extract( $this->error );
				extract( $posted_data );
			}

			$add_feed_status = $this->feedback_obj->wkmp_get_seller_feedbacks(
				array(
					'filter_seller_id' => $this->seller_id,
					'filter_user_id'   => $current_user_id,
				)
			);

			require_once __DIR__ . '/wkmp-seller-add-feedback.php';
		}

		/**
		 * Validate seller feedback form
		 *
		 * @return bool
		 */
		private function wkmp_validate_add_feedback_form() {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( empty( $posted_data['wkmp-add-feedback-nonce'] ) || ! wp_verify_nonce( wp_unslash( $posted_data['wkmp-add-feedback-nonce'] ), 'wkmp-add-feedback-nonce-action' ) ) {
				$this->error['nonce_error'] = esc_html__( 'Nonce not validated', 'wk-marketplace' );
			}

			if ( ! isset( $posted_data['feed_price'] ) || ! $posted_data['feed_price'] ) {
				$this->error['feed_price_error'] = esc_html__( 'Price field require', 'wk-marketplace' );
			}

			if ( ! isset( $posted_data['feed_value'] ) || ! $posted_data['feed_value'] ) {
				$this->error['feed_value_error'] = esc_html__( 'Value field require', 'wk-marketplace' );
			}

			if ( ! isset( $posted_data['feed_quality'] ) || ! $posted_data['feed_quality'] ) {
				$this->error['feed_quality_error'] = esc_html__( 'Quality field require', 'wk-marketplace' );
			}

			if ( ! isset( $posted_data['feed_summary'] ) || strlen( $posted_data['feed_summary'] ) < 3 ) {
				$this->error['feed_summary_error'] = esc_html__( 'Please add summary more than 3 charcter', 'wk-marketplace' );
			}

			if ( ! isset( $posted_data['feed_review'] ) || strlen( $posted_data['feed_review'] ) < 5 ) {
				$this->error['feed_description_error'] = esc_html__( 'Please add summary more than 5 charcter', 'wk-marketplace' );
			}

			if ( $this->error ) {
				$this->error['warning_error'] = esc_html__( 'Warning: Please check the form carefully for the errors', 'wk-marketplace' );
			}

			return ! $this->error;
		}

		/**
		 * Display seller store
		 *
		 * @return void
		 */
		public function wkmp_display_seller_store() {
			$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
			$shop_banner = WKMP_PLUGIN_URL . 'assets/images/mp-banner.png';

			if ( isset( $seller_info->shop_banner_visibility ) && 'yes' === $seller_info->shop_banner_visibility ) {
				if ( isset( $seller_info->_thumbnail_id_shop_banner ) && $seller_info->_thumbnail_id_shop_banner ) {
					$shop_banner = wp_get_attachment_image_src( $seller_info->_thumbnail_id_shop_banner, array( 750, 320 ) )[0];
				}
			}

			$shop_logo = WKMP_PLUGIN_URL . 'assets/images/shop-logo.png';
			if ( isset( $seller_info->_thumbnail_id_company_logo ) && $seller_info->_thumbnail_id_company_logo ) {
				$shop_logo = wp_get_attachment_image_src( $seller_info->_thumbnail_id_company_logo )[0];
			}

			$add_review = home_url( $this->marketplace->seller_page_slug . '/add-feedback/' . $seller_info->shop_address );
			$all_review = home_url( $this->marketplace->seller_page_slug . '/feedback/' . $seller_info->shop_address );

			$seller_collection = home_url( $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_seller_product_endpoint', 'seller-product' ) . '/' . $seller_info->shop_address );

			$filter_data = array(
				'filter_seller_id' => $this->seller_id,
				'status'           => 1,
				'start'            => 0,
				'limit'            => $this->feedback_obj->wkmp_get_seller_total_feedbacks(
					array(
						'filter_seller_id' => $this->seller_id,
						'status'           => 1,
					)
				),
			);

			$reviews = $this->feedback_obj->wkmp_get_seller_feedbacks( $filter_data );

			$review_check = $this->feedback_obj->wkmp_get_seller_feedbacks(
				array(
					'filter_seller_id' => $this->seller_id,
					'filter_user_id'   => get_current_user_id(),
				)
			);

			$num_of_stars   = 0;
			$total_feedback = 0;
			$price_stars    = 0;
			$value_stars    = 0;
			$quality_stars  = 0;

			if ( $reviews ) {
				foreach ( $reviews as $item ) {
					$num_of_stars  += $item->price_r;
					$price_stars   += $item->price_r;
					$num_of_stars  += $item->value_r;
					$value_stars   += $item->value_r;
					$num_of_stars  += $item->quality_r;
					$quality_stars += $item->quality_r;
					$total_feedback ++;
				}
			}

			$quality = 0;
			if ( $num_of_stars > 0 ) {
				$quality = $num_of_stars / ( $total_feedback * 3 );

				$price_stars   /= $total_feedback;
				$value_stars   /= $total_feedback;
				$quality_stars /= $total_feedback;
			}
			require_once __DIR__ . '/wkmp-seller-store-info.php';
		}

		/**
		 * Display seller profile details section.
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_details_section() {
			$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
			$shop_logo   = WKMP_PLUGIN_URL . 'assets/images/shop-logo.png';

			if ( isset( $seller_info->_thumbnail_id_company_logo ) && $seller_info->_thumbnail_id_company_logo ) {
				$shop_logo = wp_get_attachment_image_src( $seller_info->_thumbnail_id_company_logo )[0];
			}

			$add_review        = home_url( $this->marketplace->seller_page_slug . '/add-feedback/' . $seller_info->shop_address );
			$seller_store      = home_url( $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . $seller_info->shop_address );
			$seller_collection = home_url( $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_seller_product_endpoint', 'seller-product' ) . '/' . $seller_info->shop_address );

			$filter_data = array(
				'filter_seller_id' => $this->seller_id,
				'status'           => 1,
				'start'            => 0,
				'limit'            => $this->feedback_obj->wkmp_get_seller_total_feedbacks(
					array(
						'filter_seller_id' => $this->seller_id,
						'status'           => 1,
					)
				),
			);

			$reviews = $this->feedback_obj->wkmp_get_seller_feedbacks( $filter_data );

			$review_check = $this->feedback_obj->wkmp_get_seller_feedbacks(
				array(
					'filter_seller_id' => $this->seller_id,
					'filter_user_id'   => get_current_user_id(),
				)
			);

			$num_of_stars   = 0;
			$total_feedback = 0;
			$price_stars    = 0;
			$value_stars    = 0;
			$quality_stars  = 0;

			if ( $reviews ) {
				foreach ( $reviews as $item ) {
					$num_of_stars  += $item->price_r;
					$price_stars   += $item->price_r;
					$num_of_stars  += $item->value_r;
					$value_stars   += $item->value_r;
					$num_of_stars  += $item->quality_r;
					$quality_stars += $item->quality_r;
					$total_feedback ++;
				}
			}

			$quality = 0;
			if ( $num_of_stars > 0 ) {
				$quality = $num_of_stars / ( $total_feedback * 3 );

				$price_stars   /= $total_feedback;
				$value_stars   /= $total_feedback;
				$quality_stars /= $total_feedback;
			}

			require_once __DIR__ . '/wkmp-seller-store-details-section.php';
		}
	}
}
