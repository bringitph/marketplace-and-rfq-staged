<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Seller_Backend_Profile' ) ) {

	/**
	 * Class for backend seller profile.
	 */
	class WKMP_Seller_Backend_Profile {
		/**
		 * WPDB object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Backend_Profile constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;

			$this->wkmp_index();
		}

		/**
		 * Indexing.
		 */
		public function wkmp_index() {
			global $wkmarketplace;
			$current_user_id = get_current_user_id();
			if ( isset( $_POST['update_profile_submit'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$result = $this->wkmp_profile_edit_redirection( $current_user_id );
				if ( isset( $result['success'] ) ) {
					echo '<div class="updated notice" style="margin-bottom: 10px;"><p>' . wp_kses_post( $result['success'] ) . '</p></div>';
				} elseif ( isset( $result['error'] ) ) {
					echo '<div class="error notice" style="margin-bottom: 10px;"><p>' . wp_kses_post( $result['error'] ) . '</p></div>';
				}
			}

			$page_name = isset( $wkmarketplace->seller_page_slug ) ? $wkmarketplace->seller_page_slug : 'seller';

			if ( $current_user_id > 0 ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$seller_info = $wkmarketplace->get_parsed_seller_info( $current_user_id, $posted_data );

				require_once __DIR__ . '/wkmp-seller-backend-profile.php';
			}
		}

		/**
		 * Profile edit redirection.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return array
		 */
		public function wkmp_profile_edit_redirection( $seller_id ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $posted_data['wkmp_first_name'] ) && isset( $posted_data['wk_user_nonece'] ) && wp_verify_nonce( $posted_data['wk_user_nonece'], 'edit_profile' ) ) {

				do_action( 'wkmp_validate_update_seller_profile', $posted_data, $seller_id );

				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

				$errors = empty( $posted_data['wkmp_errors'] ) ? array() : $posted_data['wkmp_errors'];

				if ( ! empty( $errors ) ) {
					$text = '';
					foreach ( $errors as $value ) {
						$text .= $value . '</br>';
					}
					if ( is_admin() ) {
						return array(
							'error' => $text,
						);
					}
				} else {
					if ( is_admin() ) {
						return array(
							'success' => esc_html__( 'Profile updated successfully.', 'wk-marketplace' ),
						);
					}
				}
			}
		}
	}
}
