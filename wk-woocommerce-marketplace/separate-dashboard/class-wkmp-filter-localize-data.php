<?php
/**
 * Seller filter localize data
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Filter_Localize_Data' ) ) {

	/**
	 * Class WKMP_Filter_Localize_Data
	 *
	 * @package WkMarketplace\Separate_Dashboard
	 */
	class WKMP_Filter_Localize_Data extends \WP_Scripts {
		/**
		 * Localise script.
		 *
		 * @param string $handle Handle.
		 * @param string $object_name Object name.
		 * @param array  $object_data Object data.
		 *
		 * @return bool
		 */
		public function localize( $handle, $object_name, $object_data ) {
			$object_data = apply_filters( 'mp_override_localize_script', $object_data, $handle, $object_name );

			return parent::localize( $handle, $object_name, $object_data );
		}
	}
}
