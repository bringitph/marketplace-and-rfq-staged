<?php
/**
 * This file handles functions.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use wooMarketplaceRFQ\Includes\Admin;

if ( ! class_exists( 'Womprfq_Hook_Handler' ) ) {
	/**
	 * Load Admin side hooks.
	 */
	class Womprfq_Hook_Handler {

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$function_handler = new Admin\Womprfq_Function_Handler();
			$script_loader    = new Admin\Womprfq_Script_Handler();

			add_action( 'admin_init', array( $function_handler, 'womprfq_register_settings' ) );

			add_action( 'admin_menu', array( $function_handler, 'womprfq_add_dashboard_menu' ), 99 );

			add_filter( 'set-screen-option', array( $function_handler, 'womprfq_set_option' ), 10, 3 );

			add_filter( 'woocommerce_screen_ids', array( $function_handler, 'womprfq_set_wc_screen_ids' ), 10, 1 );

		}
	}
}
