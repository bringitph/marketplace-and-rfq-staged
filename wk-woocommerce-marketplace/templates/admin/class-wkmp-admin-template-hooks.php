<?php
/**
 * Admin template hooks
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Admin_Template_Hooks' ) ) {
	/**
	 * Admin template hooks
	 */
	class WKMP_Admin_Template_Hooks {
		/**
		 * Constructor
		 */
		public function __construct() {
			$function_handler = new WKMP_Admin_Template_Functions();
		}
	}
}
