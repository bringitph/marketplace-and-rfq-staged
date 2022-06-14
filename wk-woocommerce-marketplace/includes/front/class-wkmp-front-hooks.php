<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Templates\Front as FrontTemplates;

if ( ! class_exists( 'WKMP_Front_Hooks' ) ) {
	/**
	 * Front hooks class.
	 *
	 * Class WKMP_Front_Hooks
	 *
	 * @package WkMarketplace\Includes\Front
	 */
	class WKMP_Front_Hooks {
		/**
		 * Constructor.
		 *
		 * WKMP_Front_Hooks constructor.
		 */
		public function __construct() {
			$template_handler = new FrontTemplates\WKMP_Front_Template_Functions();
			$function_handler = new WKMP_Front_Functions( $template_handler );

			add_action( 'wp_enqueue_scripts', array( $function_handler, 'wkmp_front_scripts' ) );
			add_action( 'wp', array( $function_handler, 'wkmp_call_seller_pages' ) );
			add_action( 'woocommerce_register_form', array( $function_handler, 'wkmp_seller_registration_form' ) );

			add_filter( 'woocommerce_process_registration_errors', array( $function_handler, 'wkmp_seller_registration_errors' ) );
			add_filter( 'registration_errors', array( $function_handler, 'wkmp_seller_registration_errors' ) );
			add_filter( 'woocommerce_new_customer_data', array( $function_handler, 'wkmp_new_user_data' ) );

			add_action( 'woocommerce_created_customer', array( $function_handler, 'wkmp_process_registration' ), 10, 2 );

			add_filter( 'woocommerce_login_redirect', array( $function_handler, 'wkmp_seller_login_redirect' ), 10, 2 );
			add_filter( 'woocommerce_account_menu_items', array( $function_handler, 'wkmp_seller_menu_items_my_account' ) );
			add_filter( 'mp_get_wc_account_menu', array( $function_handler, 'wkmp_return_wc_account_menu' ) );

			add_action( 'mp_get_wc_account_menu', array( $function_handler, 'wkmp_return_wc_account_menu' ) );
			add_action( 'wp_head', array( $function_handler, 'wkmp_shipping_icon_style' ) );

			add_filter( 'the_title', array( $function_handler, 'wkmp_update_page_title' ) );

			add_action( 'woocommerce_checkout_order_processed', array( $function_handler, 'wkmp_new_order_map_seller' ), 10, 1 );
			add_action( 'woocommerce_thankyou', array( $function_handler, 'wkmp_clear_shipping_session' ) );

			add_action( 'marketplace_after_shop_loop', array( $function_handler, 'wkmp_seller_collection_pagination' ) );
			add_action( 'marketplace_before_shop_loop', array( $function_handler, 'wkmp_seller_collection_pagination' ) );

			add_action( 'admin_init', array( $function_handler, 'wkmp_redirect_seller_tofront' ) );
			add_action( 'template_redirect', array( $function_handler, 'wkmp_redirect_seller_tofront' ) );
			add_action( 'woocommerce_account_navigation', array( $function_handler, 'wkmp_show_register_success_notice' ), 1 );
			add_action( 'wp_head', array( $function_handler, 'wkmp_add_ga_script' ) );
			add_action( 'woocommerce_checkout_order_processed', array( $function_handler, 'collect_order_data_for_ga' ), 10, 3 );

			// Adding sold item meta to order items.
			add_action( 'woocommerce_checkout_create_order_line_item', array( $function_handler, 'wkmp_add_order_item_meta' ), 10, 4 );

			// Validating and showing notice on cart page when cart total is less than threshold amount.
			add_action( 'woocommerce_checkout_process', array( $function_handler, 'wkmp_validate_minimum_order_amount' ) );
			add_action( 'woocommerce_before_cart', array( $function_handler, 'wkmp_validate_minimum_order_amount' ) );

			// Showing the notice on checkout page when total volume less that threshold amount.
			add_action( 'woocommerce_checkout_update_order_review', array( $function_handler, 'wkmp_validate_minimum_order_amount_checkout' ) );

			// Replacing the Place order button when total volume less that threshold amount.
			add_filter( 'woocommerce_order_button_html', array( $function_handler, 'wkmp_remove_place_order_button' ), 10 );
			add_filter( 'body_class', array( $function_handler, 'wkmp_add_body_class' ) );
			add_filter( 'woocommerce_account_menu_item_classes', array( $function_handler, 'wkmp_wc_menu_active_class' ), 10, 2 );
			add_filter( 'woocommerce_registration_redirect', array( $function_handler, 'wkmp_registration_redirect' ) );
			add_action( 'after_setup_theme', array( $function_handler, 'wkmp_remove_admin_bar' ) );

			$seller_id   = get_current_user_id();
			$seller_user = get_user_by( 'ID', $seller_id );

			if ( is_a( $seller_user, 'WP_User' ) && in_array( 'wk_marketplace_seller', $seller_user->roles, true ) ) {
				add_filter( 'woocommerce_checkout_update_customer_data', '__return_false' );
			}

		}
	}
}
