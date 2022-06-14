<?php
/**
 * This file handles functions.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Common;

use wooMarketplaceRFQ\Includes\Common;
use wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Hook_Handler' ) ) {
	/**
	 * Load hooks.
	 */
	class Womprfq_Hook_Handler {

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$function_handler = new Common\Womprfq_Function_Handler();
			$helper           = new Helper\Womprfq_Quote_Handler();

			if ( $helper->enabled ) {
				add_action( 'wp_ajax_nopriv_womprfq_get_product_template', array( $function_handler, 'womprfq_get_product_template' ) );
				add_action( 'wp_ajax_womprfq_get_product_template', array( $function_handler, 'womprfq_get_product_template' ) );

				add_action( 'wp_ajax_nopriv_womprfq_return_productdata_quotation_form', array( $function_handler, 'womprfq_return_productdata_quotation_form' ) );
				add_action( 'wp_ajax_womprfq_return_productdata_quotation_form', array( $function_handler, 'womprfq_return_productdata_quotation_form' ) );

				add_action( 'wp_ajax_nopriv_womprfq_submit_quotation_form', array( $function_handler, 'womprfq_submit_quotation_form' ) );
				add_action( 'wp_ajax_womprfq_submit_quotation_form', array( $function_handler, 'womprfq_submit_quotation_form' ) );

				add_action( 'wp_ajax_nopriv_womprfq_product_update_after_approval', array( $function_handler, 'womprfq_product_update_after_approval' ) );
				add_action( 'wp_ajax_womprfq_product_update_after_approval', array( $function_handler, 'womprfq_product_update_after_approval' ) );
			}

			add_action( 'wp_ajax_nopriv_womprfq_notify_seller_via_mail', array( $function_handler, 'womprfq_notify_seller_via_mail' ) );
			add_action( 'wp_ajax_womprfq_notify_seller_via_mail', array( $function_handler, 'womprfq_notify_seller_via_mail' ) );

			add_action( 'init', array( $function_handler, 'womprfq_add_endpoints' ) );

			add_filter( 'womprfq_add_admin_attribute_template', array( $function_handler, 'womprfq_get_admin_attribute_template' ), 10, 1 );

			add_action( 'womprfq_save_quotation_meta', array( $function_handler, 'womprfq_save_quotation_meta_on_create' ), 10, 2 );

			add_action( 'woocommerce_order_status_completed', array( $function_handler, 'womprfq_after_order_completed' ), 99, 1 );

			add_action( 'woocommerce_order_status_cancelled', array( $function_handler, 'womprfq_after_order_cancel' ), 99, 1 );

			add_action( 'woocommerce_checkout_order_processed', array( $function_handler, 'womprfq_add_action_on_checkout_processed' ), 10, 1 );

			add_filter( 'woocommerce_email_actions', array( $function_handler, 'womprfq_add_woocommerce_email_actions' ), 10, 1 );

			add_filter( 'woocommerce_email_classes', array( $function_handler, 'womprfq_add_new_email_notification' ), 10, 1 );

			add_action( 'wkmp_add_fee_to_seller_order_view', array( $function_handler, 'wkmp_rfq_add_fee_to_seller_order' ), 10, 2 );

			add_filter( 'wkmp_add_order_fee_to_total', array( $function_handler, 'wkmp_add_fee_to_total' ), 10, 2 );

			add_filter( 'wkmp_rfq_add_order_fee_to_tooltip', array( $function_handler, 'wkmp_rfq_add_fee_to_tooltip' ), 10, 2 );

			add_filter( 'wkmp_order_list_alter_columns', array( $function_handler, 'wkmp_rfq_add_order_list_column_fee' ), 10, 1 );

			add_filter( 'wkmp_order_list_alter_column_data', array( $function_handler, 'wkmp_rfq_add_order_list_column_fee_data' ), 10, 1 );

			add_filter( 'wkmp_account_transactions_columns', array( $function_handler, 'wkmp_rfq_transactions_columns' ), 10, 1 );

			add_filter( 'wkmp_account_transactions_columns_data', array( $function_handler, 'wkmp_rfq_transactions_columns_data' ), 10, 1 );

			add_filter( 'wkmp_seller_order_fee_name', array( $function_handler, 'wkmp_rfq_change_fee_name' ), 10, 1 );

			add_filter( 'wkmp_seller_order_fee_amount', array( $function_handler, 'wkmp_rfq_change_fee_amount' ), 10, 3 );

			add_filter( 'wkmp_seller_email_order_fee_amount', array( $function_handler, 'wkmp_rfq_change_email_fee_amount' ), 10, 3 );

		}
	}
}
