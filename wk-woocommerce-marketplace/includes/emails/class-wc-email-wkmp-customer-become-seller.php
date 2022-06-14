<?php
/**
 * File Handler
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Emails;

defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'WC_Email_WKMP_Settings' ) ) {
	require_once __DIR__ . '/trait-wc-email-wkmp-settings.php';
}

if ( ! class_exists( 'WC_Email_WKMP_Customer_Become_Seller' ) ) {
	/**
	 * Class WC_Email_WKMP_Customer_Become_Seller
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Customer_Become_Seller extends \WC_Email {
		use WC_Email_WKMP_Settings;
		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Customer_Become_Seller constructor.
		 */
		public function __construct() {
			$this->id             = 'wkmp_customer_become_seller';
			$this->title          = esc_html__( 'Customer Become Seller', 'wk-marketplace' );
			$this->description    = esc_html__( 'Thank you message will be sent to the customer/seller.', 'wk-marketplace' );
			$this->customer_email = true;

			$this->template_html  = 'emails/wkmp-customer-become-seller.php';
			$this->template_plain = 'emails/plain/wkmp-customer-become-seller.php';
			$this->template_base  = WKMP_PLUGIN_FILE . 'woocommerce/templates/';

			// Call parent constructor.
			parent::__construct();

			add_action( 'wkmp_customer_become_seller_notification', array( $this, 'trigger' ), 10, 1 );

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param array $info Info.
		 */
		public function trigger( $info ) {
			$this->setup_locale();

			$info              = is_array( $info ) ? $info : array();
			$seller_email      = empty( $info['user_email'] ) ? '' : $info['user_email'];
			$mail_to           = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();
			$info['mail_to']   = $mail_to;
			$info['mail_data'] = $this->wkmp_get_common_mail_data();
			$this->data        = $info;
			$this->recipient   = $mail_to;

			if ( $this->is_enabled() && $mail_to ) {
				$this->send( $mail_to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
			$this->restore_locale();
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Thank you for showing interest.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			$auto_approve = empty( $this->data['auto_approve'] ) ? false : $this->data['auto_approve'];
			$msg          = __( 'Your Request to become a seller on {site_title} is being processed.', 'wk-marketplace' );
			if ( $auto_approve ) {
				$msg = __( 'Congratulations!! Your request to become a seller on {site_title} has been accepted.', 'wk-marketplace' );
			}
			return $msg;
		}
	}
}
