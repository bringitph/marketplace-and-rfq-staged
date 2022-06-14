<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Common_Hooks' ) ) {
	/**
	 * Front hooks clasṣ
	 *
	 * Class WKMP_Common_Hooks
	 *
	 * @package WkMarketplace\Includes\Common
	 */
	class WKMP_Common_Hooks {
		/**
		 * WKMP_Common_Hooks constructor.
		 */
		public function __construct() {
			$function_handler = new WKMP_Common_Functions();

			add_action( 'woocommerce_shipping_zone_method_added', array( $function_handler, 'wkmp_after_add_admin_shipping_zone' ), 10, 3 );
			add_action( 'woocommerce_delete_shipping_zone', array( $function_handler, 'wkmp_action_woocommerce_delete_shipping_zone' ), 10, 1 );
			add_action( 'woocommerce_shipping_classes_save_class', array( $function_handler, 'wkmp_after_add_admin_shipping_class' ), 10, 2 );

			add_action( 'woocommerce_order_status_cancelled', array( $function_handler, 'wkmp_action_on_order_cancel' ), 10, 1 );
			add_action( 'woocommerce_order_status_failed', array( $function_handler, 'wkmp_action_on_order_changed_mails' ), 10, 1 );
			add_action( 'woocommerce_order_status_on-hold', array( $function_handler, 'wkmp_action_on_order_changed_mails' ), 10, 1 );
			add_action( 'woocommerce_order_status_processing', array( $function_handler, 'wkmp_action_on_order_changed_mails' ), 10, 1 );
			add_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( $function_handler, 'wkmp_action_on_order_changed_mails' ), 10, 1 );
			add_action( 'woocommerce_order_status_completed', array( $function_handler, 'wkmp_action_on_order_changed_mails' ), 10, 1 );
			add_action( 'woocommerce_order_status_refunded', array( $function_handler, 'wkmp_action_on_order_changed_mails' ), 10, 1 );
			add_action( 'woocommerce_order_status_refunded', array( $function_handler, 'wkmp_add_seller_refund_data_on_order_fully_refunded' ), 10, 1 );
			add_action( 'woocommerce_refund_created', array( $function_handler, 'wkmp_add_seller_refund_data_on_order_refund' ), 10, 2 );

			add_action( 'template_redirect', array( $function_handler, 'wkmp_reset_previous_chosen_shipping_method' ), 1 );

			add_action( 'draft_to_publish', array( $function_handler, 'wkmp_action_on_product_approve' ), 10, 1 );
			add_action( 'wp_trash_post', array( $function_handler, 'wkmp_action_on_product_disapprove' ), 10, 1 );
			add_action( 'save_post', array( $function_handler, 'wkmp_save_version_meta' ), 10, 3 );

			add_action( 'personal_options_update', array( $function_handler, 'wkmp_save_extra_user_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $function_handler, 'wkmp_save_extra_user_profile_fields' ) );
			add_action( 'user_profile_update_errors', array( $function_handler, 'wkmp_validate_extra_profile_fields' ), 10, 3 );
			add_action( 'admin_bar_menu', array( $function_handler, 'wkmp_add_toolbar_items' ), 100 );
			add_action( 'pre_get_posts', array( $function_handler, 'wkmp_restrict_media_library' ) );
			add_action( 'woocommerce_init', array( $function_handler, 'wkmp_add_manage_shipping' ) );
			add_action( 'widgets_init', array( $function_handler, 'wkmp_include_widgets' ) );
			add_action( 'wkmp_validate_update_seller_profile', array( $function_handler, 'wkmp_process_seller_profile_data' ), 10, 2 );

			add_filter( 'plugin_row_meta', array( $function_handler, 'wkmp_plugin_row_meta' ), 10, 2 );
			add_filter( 'sidebars_widgets', array( $function_handler, 'wkmp_remove_sidebar_seller_page' ) );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $function_handler, 'hide_my_item_meta' ) );
		}
	}
}
