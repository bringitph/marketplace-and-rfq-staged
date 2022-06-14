<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Plugin Name: Marketplace for WooCommerce
 * Description: This plugin converts the WooCommerce store into multi-vendor store. Using this plugin, the seller can manage the inventory, shipment, seller profile page, seller collection page and much more.
 * version: 5.1.2
 * Author: Webkul
 * Author URI: https://webkul.com
 * Plugin URI: https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408
 * Domain Path: /languages
 * License: license.txt included with plugin
 * License URI: https://store.webkul.com/license.html
 * Text Domain: wk-marketplace
 *
 * WC requires at least: 4.0.0
 * WC tested up to: 5.9.x
 *
 * @package Multi Vendor Marketplace
 */

defined( 'ABSPATH' ) || exit();

use WkMarketplace\Includes;

// Define Constants.
defined( 'WKMP_FILE' ) || define( 'WKMP_FILE', __FILE__ );
defined( 'WKMP_PLUGIN_FILE' ) || define( 'WKMP_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
defined( 'WKMP_PLUGIN_URL' ) || define( 'WKMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'WKMP_SCRIPT_VERSION' ) || define( 'WKMP_SCRIPT_VERSION', '1.0.6' );
defined( 'MARKETPLACE_VERSION' ) || define( 'MARKETPLACE_VERSION', '5.1.2' );
defined( 'WKMP_DB_VERSION' ) || define( 'WKMP_DB_VERSION', '1.0.6' );

require_once WKMP_PLUGIN_FILE . 'inc/autoload.php';

// Class Marketplace for addon support.
if ( ! class_exists( 'Marketplace' ) ) {
	/**
	 * Marketplace class.
	 */
	final class Marketplace {
		/**
		 * Marketplace Constructor.
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'wkmp_load_plugin_text_domain' ) );
		}

		/**
		 * Loading test domain.
		 *
		 * @return void
		 */
		public function wkmp_load_plugin_text_domain() {
			load_plugin_textdomain( 'wk-marketplace', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}
	}

	new Marketplace();
}

/**
 * Allow seller to translate the products.
 *
 * @param array $capability Vendor capability.
 *
 * @return array
 */
function wkmp_allow_seller_to_translate( $capability ) {
	global $wkmarketplace;
	$capability        = is_array( $capability ) ? $capability : array();
	$translate_allowed = get_option( '_wkmp_wcml_allow_product_translate', false );
	$user_id           = 0;

	if ( ! $translate_allowed ) {
		$user_id           = get_current_user_id();
		$translate_allowed = get_user_meta( $user_id, '_wkmp_wcml_allow_product_translate', true );
	}

	if ( $translate_allowed ) {
		$capability['vendor_capability'] = 'wk_marketplace_seller';
	} else {
		$wkmarketplace->wkmp_remove_seller_translate_capability( $user_id );
	}

	return $capability;
}

add_filter( 'wcml_vendor_addon_configuration', 'wkmp_allow_seller_to_translate' );


// Global for backwards compatibility.
$GLOBALS['wkmarketplace'] = Includes\WKMarketplace::instance();
