<?php
/**
 * Front ajax hooks
 *
 * @package Multi Vendor Marketplace
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Front_Ajax_Hooks' ) ) {
	/**
	 * Front ajax hooks
	 */
	class WKMP_Front_Ajax_Hooks {
		/**
		 * Constructor
		 */
		public function __construct() {
			$function_handler = new WKMP_Front_Ajax_Functions();

			add_action( 'wp_ajax_nopriv_wkmp_check_shop_url', array( $function_handler, 'wkmp_check_shop_url' ) );
			add_action( 'wp_ajax_wkmp_check_shop_url', array( $function_handler, 'wkmp_check_shop_url' ) );

			add_action( 'wp_ajax_wkmp_add_favourite_seller', array( $function_handler, 'wkmp_add_favourite_seller' ) );

			add_action( 'wp_ajax_wkmp_get_state_by_country_code', array( $function_handler, 'wkmp_get_state_by_country_code' ) );

			add_action( 'wp_ajax_wkmp_save_shipping_cost', array( $function_handler, 'wkmp_save_shipping_cost' ) );
			add_action( 'wp_ajax_wkmp_delete_shipping_class', array( $function_handler, 'wkmp_delete_shipping_class' ) );
			add_action( 'wp_ajax_wkmp_add_shipping_class', array( $function_handler, 'wkmp_add_shipping_class' ) );
			add_action( 'wp_ajax_wkmp_add_shipping_method', array( $function_handler, 'wkmp_add_shipping_method' ) );
			add_action( 'wp_ajax_wkmp_delete_shipping_method', array( $function_handler, 'wkmp_delete_shipping_method' ) );
			add_action( 'wp_ajax_wkmp_del_zone', array( $function_handler, 'wkmp_del_zone' ) );

			add_action( 'wp_ajax_wkmp_marketplace_attributes_variation', array( $function_handler, 'wkmp_marketplace_attributes_variation' ) );
			add_action( 'wp_ajax_wkmp_attributes_variation_remove', array( $function_handler, 'wkmp_attributes_variation_remove' ) );
			add_action( 'wp_ajax_wkmp_productgallary_image_delete', array( $function_handler, 'wkmp_productgallary_image_delete' ) );
			add_action( 'wp_ajax_wkmp_downloadable_file_add', array( $function_handler, 'wkmp_downloadable_file_add' ) );
			add_action( 'wp_ajax_wkmp_product_sku_validation', array( $function_handler, 'wkmp_product_sku_validation' ) );

			add_action( 'wp_ajax_wkmp_change_seller_dashboard', array( $function_handler, 'wkmp_change_seller_dashboard' ) );
		}
	}
}
