<?php
/**
 * This file handles functions.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Front;

use wooMarketplaceRFQ\Includes\Front;
use wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Hook_Handler' ) ) {
	/**
	 * Load Admin side hooks.
	 */
	class Womprfq_Hook_Handler {

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$helper           = new Helper\Womprfq_Quote_Handler();
			$function_handler = new Front\Womprfq_Function_Handler();
			$script_loader    = new Front\Womprfq_Script_Handler();

			if ( $helper->enabled ) {
				add_action( 'init', array( $function_handler, 'tdp_add_dealer_caps' ) );
				add_action( 'woocommerce_single_product_summary', array( $function_handler, 'womprfq_add_quote_system_btn' ) );
				add_filter( 'woocommerce_account_menu_items', array( $function_handler, 'womprfq_add_customer_rfq_menu' ), 10, 1 );
				add_filter( 'query_vars', array( $function_handler, 'womprfq_add_query_vars' ), 0 );
				add_action( 'mp_woocommerce_account_menu_options', array( $function_handler, 'womprfq_add_manage_rfq_menu' ), 10, 1 );
				add_action( 'woocommerce_account_' . esc_attr( $helper->endpoint ) . '_endpoint', array( $function_handler, 'womprfq_customer_endpoint_template' ) );
				add_action( 'woocommerce_account_main-quote_endpoint', array( $function_handler, 'womprfq_customer_endpoint_template' ) );
				add_action( 'woocommerce_account_seller-quote_endpoint', array( $function_handler, 'womprfq_customer_endpoint_template' ) );
				add_action( 'woocommerce_account_add-quote_endpoint', array( $function_handler, 'womprfq_customer_endpoint_template' ) );
				add_action( 'wp_head', array( $function_handler, 'womprfq_add_rfq_calling_pages' ), 10, 1 );
				add_action( 'womprfq_manage_rfq_template', array( $function_handler, 'womprfq_manage_rfq_template' ) );
				add_action( 'wkmprfq_after_customer_submit_form', array( $function_handler, 'wkmprfq_after_customer_submit_form_handle' ), 10, 2 );
				add_action( 'womprfq_seller_quotation_save_form', array( $function_handler, 'womprfq_seller_quotation_save_form_handler' ), 10, 3 );
				add_action( 'wkmprfq_after_customer_new_product_submit_form', array( $function_handler, 'wkmprfq_after_customer_new_product_submit_form_handler' ), 10, 3 );
				add_action( 'woocommerce_before_calculate_totals', array( $function_handler, 'wkmprfq_add_quoted_price_for_product' ), 99, 1 );
				add_filter( 'woocommerce_cart_item_price', array( $function_handler, 'wkmprfq_change_quote_product_price_in_mini_cart' ), 99, 3 );
				add_filter( 'woocommerce_login_redirect', array( $function_handler, 'wkmprfq_change_my_account_redirect_url' ), 10, 2 );
				add_action( 'woocommerce_cart_calculate_fees', array( $function_handler, 'wkmprfq_add_fee_to_cart_product' ), 10, 2 );
				add_filter( 'woocommerce_add_to_cart_redirect', array( $function_handler, 'wkmprfq_redirect_to_cart' ), 10, 1 );
				add_filter( 'woocommerce_update_cart_validation', array( $function_handler, 'wkmprfq_update_cart_validation' ), 10, 4 );
				add_filter( 'woocommerce_add_to_cart_validation', array( $function_handler, 'wkmprfq_validate_add_to_cart_product' ), 10, 5 );
				add_action( 'woocommerce_checkout_create_order_line_item', array( $function_handler, 'wkmp_add_order_item_meta' ), 10, 4 );
			}
		}
	}
}
