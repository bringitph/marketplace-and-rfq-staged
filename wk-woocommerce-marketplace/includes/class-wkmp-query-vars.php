<?php
/**
 * Query functions template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Query_Vars' ) ) {

	/**
	 * Query functions class
	 */
	class WKMP_Query_Vars {

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			add_filter( 'query_vars', array( $this, 'wkmp_insert_custom_query_variables' ) );
			add_filter( 'rewrite_rules_array', array( $this, 'wkmp_insert_custom_rules' ) );
		}

		/**
		 * Insert Custom Query Variables
		 *
		 * @param array $vars Query Variables.
		 * @return $vars
		 */
		public function wkmp_insert_custom_query_variables( $vars ) {
			$new_vars = array( 'main_page', 'pid', 'sid', 'action', 'info', 'shop_name', 'order_id', 'ship', 'zone_id', 'pagenum', 'ship_page' );

			array_push( $vars, ...$new_vars );

			return $vars;
		}

		/**
		 * Insert custom query rules
		 *
		 * @param array $rules Rules.
		 * @return $rules
		 */
		public function wkmp_insert_custom_rules( $rules ) {
			global $wkmarketplace;
			$page_name = $wkmarketplace->seller_page_slug;

			$my_account = get_post( get_option( 'woocommerce_myaccount_page_id' ) );
			$my_account = $my_account->post_name;

			$new_rules = array(

				'(.+)/(.+)/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '/edit/([0-9]+)/?' => 'index.php?pagename=$matches[1]&main_page=$matches[2]&ship=' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '&action=edit&zone_id=$matches[3]',
				'(.+)/(.+)/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '/add/?' => 'index.php?pagename=$matches[1]&main_page=$matches[2]&ship=' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '&action=add',
				'(.+)/(.+)/edit/([0-9]+)/?'              => 'index.php?pagename=$matches[1]&main_page=$matches[2]&action=edit&pid=$matches[3]',
				'(.+)/(.+)/view/([0-9]+)/?'              => 'index.php?pagename=$matches[1]&main_page=$matches[2]&action=view&pid=$matches[3]',
				$page_name . '/(.+)/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '/?' => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]&ship_page=' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '',
				'(.+)/invoice/(.+)/?'                    => 'index.php?pagename=$matches[1]&main_page=invoice&order_id=$matches[2]',
				'(.+)/(.+)/delete/([0-9]+)/?'            => 'index.php?pagename=$matches[1]&main_page=$matches[2]&action=delete&pid=$matches[3]',

				$page_name . '/invoice/(.+)/?'           => 'index.php?pagename=' . $page_name . '&main_page=invoice&order_id=$matches[1]',
				$page_name . '/(.+)/delete/([0-9]+)/?'   => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]&action=delete&pid=$matches[2]',
				$page_name . '/' . get_option( '_wkmp_order_history_endpoint', 'order-history' ) . '/([0-9]+)/?' => 'index.php?pagename=' . $page_name . '&main_page=' . get_option( '_wkmp_order_history_endpoint', 'order-history' ) . '&order_id=$matches[1]',
				$page_name . '/([-a-z]+)/page/([0-9]+)?' => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]&action=page&pagenum=$matches[2]',
				$page_name . '/([-a-z]+)/(.+)/page/([0-9]+)?' => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]&info=$matches[2]&action=page&pagenum=$matches[3]',
				$page_name . '/([-a-z]+)/(.+)/?'         => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]&info=$matches[2]',
				$page_name . '/' . get_option( '_wkmp_seller_product_endpoint', 'seller-product' ) . '/(.+)/?' => 'index.php?pagename=' . $page_name . '&main_page=' . get_option( '_wkmp_seller_product_endpoint', 'seller-product' ) . '&info=$matches[1]',
				$page_name . '/(.+)/?'                   => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]',
				$my_account . '/(.+)/(.+)?'              => 'index.php?pagename=' . $my_account . '&$matches[1]=$matches[1]&$matches[1]=$matches[2]',
				$my_account . '/(.+)/?'                  => 'index.php?pagename=' . $my_account . '&$matches[1]=$matches[1]',
			);

			$rules = array_merge( $new_rules, $rules );
			return $rules;
		}
	}
}
