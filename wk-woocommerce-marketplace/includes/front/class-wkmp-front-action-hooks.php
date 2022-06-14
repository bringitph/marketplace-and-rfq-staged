<?php
/**
 * Front hooks template.
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Front_Action_Hooks' ) ) {

	/**
	 * Front action hooks class.
	 */
	class WKMP_Front_Action_Hooks {

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			$function_obj = new WKMP_Front_Action_Functions();

			add_action( 'wkmp_save_seller_ask_query', array( $function_obj, 'wkmp_save_seller_ask_query' ), 10, 2 );
			add_action( 'wkmp_save_seller_feedback', array( $function_obj, 'wkmp_save_seller_feedback' ), 10, 2 );
		}
	}
}
