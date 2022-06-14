<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Seller_Backend_Hooks' ) ) {

	/**
	 * Class for backend seller dashboard.
	 */
	class WKMP_Seller_Backend_Hooks {
		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Backend_Hooks constructor.
		 */
		public function __construct() {
			$function_handler = new WKMP_Seller_Backend_Functions();

			add_action( 'admin_menu', array( $function_handler, 'wkmp_seller_admin_menu' ) );
			add_action( 'wp_loaded', array( $this, 'wkmp_add_localize_script' ) );
			add_action( 'admin_head', array( $function_handler, 'wkmp_hide_publish_button' ) );

			add_filter( 'parse_query', array( $function_handler, 'wkmp_products_admin_filter_query' ) );
			add_filter( 'get_terms_args', array( $function_handler, 'wkmp_override_get_terms_args' ), 10, 2 );
			add_filter( 'product_type_selector', array( $function_handler, 'wkmp_seller_product_type_selector' ) );
			add_filter( 'mp_override_localize_script', array( $function_handler, 'wkmp_override_shipping_zones' ), 10, 3 );
			add_filter( 'woocommerce_settings_tabs_array', array( $function_handler, 'wkmp_manage_wc_settings_tab_seller' ), 21 );
			add_filter( 'woocommerce_get_shipping_classes', array( $function_handler, 'wkmp_filter_seller_shipping_classes' ) );
			add_filter( 'woocommerce_get_sections_shipping', array( $function_handler, 'wkmp_manage_wc_shipping_submenu' ) );
			add_filter( 'views_edit-product', array( $function_handler, 'wkmp_manage_seller_product_count' ), 10, 1 );
			add_filter( 'woocommerce_admin_features', array( $function_handler, 'wkmp_hide_marketing_menu' ) );
			add_filter( 'get_terms_args', array( $function_handler, 'wkmp_remove_others_shipping_classes' ), 10, 2 );

			if ( current_user_can( 'wk_marketplace_seller' ) ) {
				show_admin_bar( false );
			}

			add_filter( 'set-screen-option', array( $function_handler, 'wkmp_set_backend_screen' ), 10, 3 );
		}

		/**
		 * Add localize hooks.
		 */
		public function wkmp_add_localize_script() {
			$GLOBALS['wp_scripts'] = new WKMP_Filter_Localize_Data(); //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
