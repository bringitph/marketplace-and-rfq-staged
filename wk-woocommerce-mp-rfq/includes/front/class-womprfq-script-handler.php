<?php
/**
 * Load scripts.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Front;

use wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Script_Handler' ) ) {
	/**
	 * Class loads scripts.
	 */
	class Womprfq_Script_Handler {

		protected $helper;
		/**
		 * Contructor function.
		 */
		public function __construct() {
			$this->helper = new Helper\Womprfq_Quote_Handler();
			add_action( 'wp_enqueue_scripts', array( $this, 'womprfq_enqueue_scripts' ) );
		}

		/**
		 * Front scripts and style enqueue.
		 */
		public function womprfq_enqueue_scripts() {
			if ( $this->helper->enabled ) {
				if ( $this->helper->wpmprfq_check_if_seller_page() ) {
					wp_enqueue_media();
					wp_enqueue_script(
						'womprfq-seller-page-script',
						WK_MP_RFQ_URL . 'assets/js/seller.js',
						WK_MP_RFQ_SCRIPT_VERSION
					);
					wp_localize_script(
						'womprfq-seller-page-script',
						'womprfq_script_obj',
						array(
							'ajaxurl'           => admin_url( 'admin-ajax.php' ),
							'seller_ajax_nonce' => wp_create_nonce( 'womprfq_seller_ajax_nonce' ),
							'seller_id'         => get_current_user_id(),
							'rfq_trans_arr'     => array(
								'rfq1' => esc_html__( 'Select Image', 'wk-mp-rfq' ),
								'rfq2' => esc_html__( 'Done', 'wk-mp-rfq' ),
							),
						)
					);
					wp_enqueue_style(
						'womprfq-seller-page-style',
						WK_MP_RFQ_URL . 'assets/css/seller.css'
					);
				}

				if ( is_account_page() ) {
					wp_enqueue_media();
					wp_enqueue_script(
						'womprfq-account-page-script',
						WK_MP_RFQ_URL . 'assets/js/account.js',
						WK_MP_RFQ_SCRIPT_VERSION
					);
					wp_localize_script(
						'womprfq-account-page-script',
						'womprfq_script_obj',
						array(
							'ajaxurl'            => admin_url( 'admin-ajax.php' ),
							'account_ajax_nonce' => wp_create_nonce( 'womprfq_account_ajax_nonce' ),
							'user_id'            => get_current_user_id(),
							'rfq_trans_arr'      => array(
								'rfq1' => esc_html__( 'Select Image', 'wk-mp-rfq' ),
								'rfq2' => esc_html__( 'Done', 'wk-mp-rfq' ),
							),
						)
					);

					wp_enqueue_style(
						'womprfq-seller-page-style',
						WK_MP_RFQ_URL . 'assets/css/seller.css'
					);
				}
				if ( is_product() ) {
					wp_enqueue_media();
					global $post;
					wp_enqueue_script(
						'womprfq-product-page-script',
						WK_MP_RFQ_URL . 'assets/js/product.js',
						WK_MP_RFQ_SCRIPT_VERSION
					);
					wp_localize_script(
						'womprfq-product-page-script',
						'womprfq_script_obj',
						array(
							'ajaxurl'            => admin_url( 'admin-ajax.php' ),
							'product_ajax_nonce' => wp_create_nonce( 'womprfq_product_ajax_nonce' ),
							'product_id'         => $post->ID,
							'customer_id'        => get_current_user_id(),
							'rfq_trans_arr'      => array(
								'rfq1' => esc_html__( 'Select Image', 'wk-mp-rfq' ),
								'rfq2' => esc_html__( 'Done', 'wk-mp-rfq' ),
								'rfq3' => esc_html__( 'There are some required fields!', 'wk-mp-rfq' ),
							),
						)
					);
					wp_enqueue_style(
						'womprfq-product-page-style',
						WK_MP_RFQ_URL . 'assets/css/product.css'
					);
				}
			}
		}
	}
}
