<?php

/**
 * Plugin Name: Marketplace Request for Quotation
 * Description: This Plugin allow sellers to make quotation for customer requested Quotations.
 * Version: Customised-1.0.0
 * Author: Webkul
 * Author URI: http://webkul.com
 * Domain Path: /languages/
 * License: GNU/GPL for more info see license.txt included with plugin
 * Text Domain: wk-mp-rfq
 * License URI: https://store.webkul.com/license.html.
 **/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

! defined( 'WK_MP_RFQ_URL' ) && define( 'WK_MP_RFQ_URL', plugin_dir_url( __FILE__ ) );
! defined( 'WK_MP_RFQ_FILE' ) && define( 'WK_MP_RFQ_FILE', plugin_dir_path( __FILE__ ) );
! defined( 'WK_MP_RFQ_DIR' ) && define( 'WK_MP_RFQ_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
! defined( 'WK_MP_RFQ_SCRIPT_VERSION' ) && define( 'WK_MP_RFQ_SCRIPT_VERSION', '1.0.0' );

if ( ! function_exists( 'womprfq_install' ) ) {
	/**
	 * Check dependency is installed or not.
	 *
	 * @return void.
	 */
	function womprfq_install() {
		if ( womprfq_dependencies_satisfied() ) {

			if ( ! function_exists( 'WC' ) || ! class_exists( 'Marketplace' ) ) {
				add_action( 'admin_notices', 'womprfq_admin_notice' );
			} else {
				new WooWkMarketplaceRFQ();
				do_action( 'woo_marketplace_rfq_init' );
			}
			load_plugin_textdomain( 'wk-mp-rfq', false, basename( dirname( __FILE__ ) ) . '/languages' );
		} else {
			add_action( 'admin_notices', 'womprfq_admin_notice' );
			return;
		}
	}
}
add_action( 'plugins_loaded', 'womprfq_install' );


if ( ! function_exists( 'womprfq_install_schema' ) ) {
	/**
	 * Schema install callback.
	 *
	 * @return void.
	 */
	function womprfq_install_schema() {
		include_once WK_MP_RFQ_FILE . 'includes/class-womprfq-install-schema.php';
		$obj = new Womprfq_Install_Schema();
	}
}

register_activation_hook( __FILE__, 'womprfq_install_schema' );

/**
 * Add notice for dependency
 *
 * @return void
 */
function womprfq_admin_notice() {
	$message = sprintf(
		esc_html__( 'The Marketplace RFQ plugin for WooCommerce requires %1$s and %2$s to be installed and active.', 'wk-mp-rfq' ),
		'<a href="https://wordpress.org/plugins/woocommerce/">' . esc_html__( 'WooCommerce', 'wk-mp-rfq' ) . '</a>',
		'<a href="https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408">' . esc_html__( 'Marketplace', 'wk-mp-rfq' ) . '</a>'
	);
	printf( '<div class="error"><p>%s</p></div>', $message );
}

/**
 * Check dependancy
 *
 * @return void
 */
function womprfq_dependencies_satisfied() {
	if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'Marketplace' ) ) {
		return false;
	}

	return true;
}




if ( ! class_exists( 'WooWkMarketplaceRFQ' ) ) {
	/**
	 * Main classs.
	 */
	class WooWkMarketplaceRFQ {

		/**
		 * Class constructor.
		 */
		public function __construct() {
			ob_start();
			add_action( 'woo_marketplace_rfq_init', array( $this, 'womprfq_includes' ) );
		}

		/**
		 * Includes neccessary files.
		 *
		 * @return void
		 */
		public function womprfq_includes() {
			include_once WK_MP_RFQ_FILE . 'includes/class-womprfq-file-handler.php';
			new Womprfq_File_Handler();
		}
	}
}
