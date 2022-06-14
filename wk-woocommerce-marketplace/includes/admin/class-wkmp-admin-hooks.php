<?php
/**
 * Admin End Hooks
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Templates\Admin as AdminTemplates;


if ( ! class_exists( 'WKMP_Admin_Hooks' ) ) {

	/**
	 * Admin hooks class
	 */
	class WKMP_Admin_Hooks {

		/**
		 * Seller DB variable
		 *
		 * @var object
		 */
		protected $seller_obj;

		/**
		 * Admin hooks constructor
		 *
		 * @return void
		 */
		public function __construct() {

			$template_handler = new AdminTemplates\WKMP_Admin_Template_Functions();
			$function_handler = new WKMP_Admin_Functions( $template_handler );

			add_action( 'admin_init', array( $function_handler, 'wkmp_register_marketplace_options' ) );
			add_action( 'admin_init', array( $function_handler, 'wkmp_prevent_seller_admin_access' ) );
			add_action( 'admin_notices', array( $function_handler, 'wkmp_admin_notices' ) );
			add_action( 'admin_menu', array( $function_handler, 'wkmp_create_dashboard_menu' ) );

			add_action( 'admin_enqueue_scripts', array( $function_handler, 'wkmp_admin_scripts' ) );
			add_action( 'wkmp_save_seller_commission', array( $function_handler, 'wkmp_save_seller_commission' ), 10, 2 );
			add_action( 'admin_menu', array( $function_handler, 'wkmp_virtual_menu_invoice_page' ) );
			add_action( 'woocommerce_admin_order_actions_end', array( $function_handler, 'wkmp_order_invoice_button' ) );
			add_action( 'show_user_profile', array( $function_handler, 'wkmp_extra_user_profile_fields' ), 10 );
			add_action( 'edit_user_profile', array( $function_handler, 'wkmp_extra_user_profile_fields' ) );
			add_action( 'add_meta_boxes', array( $function_handler, 'wkmp_add_seller_metabox' ) );
			add_action( 'woocommerce_order_status_changed', array( $function_handler, 'wkmp_order_status_changed_action' ), 10, 3 );
			add_action( 'deleted_user', array( $function_handler, 'wkmp_delete_seller_on_user_delete' ), 10, 3 );
			add_action( 'woocommerce_product_options_inventory_product_data', array( $function_handler, 'wkmp_add_max_qty_field' ) );
			add_action( 'wcml_emails_options_to_translate', array( $function_handler, 'wkmp_add_email_options_to_translate' ), 11, 1 );

			add_filter( 'set-screen-option', array( $function_handler, 'wkmp_set_screen' ), 10, 3 );
			add_filter( 'woocommerce_screen_ids', array( $function_handler, 'wkmp_set_wc_screen_ids' ), 10, 1 );
			add_filter( 'admin_footer_text', array( $function_handler, 'wkmp_admin_footer_text' ), 99 );
			add_filter( 'get_terms_args', array( $function_handler, 'wkmp_remove_sellers_shipping_classes' ), 10, 2 );
			add_filter( 'woocommerce_products_admin_list_table_filters', array( $function_handler, 'wkmp_remove_restricted_cats' ) );

		}
	}
}
