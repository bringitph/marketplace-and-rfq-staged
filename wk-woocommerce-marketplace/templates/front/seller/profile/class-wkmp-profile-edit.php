<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Profile;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Profile_Edit' ) ) {
	/**
	 * Seller Profile Edit.
	 *
	 * Class WKMP_Profile_Edit
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Profile
	 */
	class WKMP_Profile_Edit {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		protected $seller_id;

		/**
		 * Marketplace class object.
		 *
		 * @var object $marketplace Marketplace class object.
		 */
		private $marketplace;

		/**
		 * Errors.
		 *
		 * @var array $errors Errors.
		 */
		private $errors = array();

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Profile_Edit constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			global $wkmarketplace;

			$this->seller_id   = $seller_id;
			$this->marketplace = $wkmarketplace;

			$this->wkmp_seller_profile_form();
		}

		/**
		 * Seller profile form.
		 */
		public function wkmp_seller_profile_form() {
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( empty( $posted_data['wkmp-user-nonce'] ) || ! wp_verify_nonce( wp_unslash( $posted_data['wkmp-user-nonce'] ), 'wkmp-user-nonce-action' ) ) {
					$this->errors['nonce_error'] = esc_html__( 'Nonce not validated', 'wk-marketplace' );
				} else {
					do_action( 'wkmp_validate_update_seller_profile', $posted_data, $this->seller_id );

					$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
					$errors      = empty( $posted_data['wkmp_errors'] ) ? array() : $posted_data['wkmp_errors'];

					if ( empty( $errors ) ) {
						wc_add_notice( esc_html__( 'Profile has been updated.', 'wk-marketplace' ), 'success' );
					} else {
						$this->errors = $errors;
					}
				}
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$edit_form_obj = new WKMP_Seller_Profile_Form();
			$edit_form_obj->wkmp_seller_profile_edit_form( $this->seller_id, $this->errors, $posted_data );
		}
	}
}
