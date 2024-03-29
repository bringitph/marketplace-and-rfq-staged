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

if ( ! class_exists( 'WC_Email_WKMP_Seller_Order_Processing' ) ) {
	/**
	 * Class WC_Email_WKMP_Seller_Order_Processing
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Seller_Order_Processing extends \WC_Email {
		use WC_Email_WKMP_Settings;
		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_Order_Processing constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_seller_order_processing';
			$this->title       = esc_html__( 'Seller Order Processing', 'wk-marketplace' );
			$this->description = esc_html__( 'This is an order notification sent to sellers containing order details on processing.', 'wk-marketplace' );

			$this->wkmp_default_email_place_holder();

			$this->template_html = 'emails/wkmp-seller-order-processing.php';
			$this->template_base = WKMP_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_order_processing_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param int    $order_id Order id.
		 * @param array  $items Items.
		 * @param string $seller_email Seller Email.
		 */
		public function trigger( $order_id, $items, $seller_email ) {
			
			//JS edit. Send processing email to seller when via credit card. Step 4
			//$ordered_sent_emails = get_post_meta( $order_id, 'wkmp_product_ordered_sent_emails', true );

			//if ( in_array( $seller_email, $ordered_sent_emails, true ) ) {
			//	unset( $ordered_sent_emails[ array_search( $seller_email, $ordered_sent_emails, true ) ] );
			//	update_post_meta( $order_id, 'wkmp_product_ordered_sent_emails', $ordered_sent_emails );
			//	return false;
			//}

			$this->setup_locale();
			$this->wkmp_set_placeholder_value( $order_id );

			$mail_to = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();

			$this->data      = array(
				'order_id'        => $order_id,
				'seller_email'    => $seller_email,
				'recipient'       => $this->get_recipient(),
				'mail_to'         => $mail_to,
				'product_details' => $this->wkmp_get_email_product_details( $items ),
				'date_string'     => $this->wkmp_get_email_date_string( $order_id ),
				'commission_data' => $this->wkmp_get_email_commission_data( $seller_email, $order_id ),
			);
			$this->recipient = $mail_to;

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
			return __( 'Your Order: #{order_number} status changed to processing.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: Your order #{order_number} status changed processed.', 'wk-marketplace' );
		}
	}
}
