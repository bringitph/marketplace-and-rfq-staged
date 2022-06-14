<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_EMAIL_Mp_Rfq_Notification' ) ) :

	/**
	 * Customer quatation to admin.
	 */
	class WC_EMAIL_Mp_Rfq_Notification extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'mp_rfq_notification';
			$this->customer_email = true;
			$this->title          = esc_html__( 'RFQ Notification', 'wk-mp-rfq' );
			$this->description    = esc_html__( 'RFQ Notification email are sent to respective customers and sellers', 'wk-mp-rfq' );
			$this->heading        = esc_html__( 'RFQ Notification', 'wk-mp-rfq' );
			$this->subject        = '[' . get_option( 'blogname' ) . ']' . esc_html__( ' RFQ Notification', 'wk-mp-rfq' );
			$this->template_html  = 'emails/wo-mp-rfq-notification.php';
			$this->template_plain = 'emails/plain/wo-mp-rfq-notification.php';
			$this->footer         = esc_html__( 'Thanks for choosing Marketplace RFQ.', 'wk-mp-rfq' );
			$this->template_base  = WK_MP_RFQ_FILE . 'woocommerce/templates/';
			add_action( 'womprfq_quotation_notification', array( $this, 'trigger' ), 10, 1 );

			parent::__construct();
		}

		/**
		 * Trigger.
		 *
		 * @param array $data quotation data.
		 *
		 * @return void
		 */
		public function trigger( $data ) {
			$this->email_message  = $data['msg'];
			$this->recipient      = $data['sendto'];
			$this->customer_email = $data['sendto'];
			$this->heading        = $data['heading'];
			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'email_heading'  => $this->get_heading(),
					'customer_email' => $this->customer_email,
					'email_message'  => $this->email_message,
					'sent_to_admin'  => false,
					'plain_text'     => false,
					'email'          => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'email_heading'  => $this->get_heading(),
					'customer_email' => $this->customer_email,
					'email_message'  => $this->email_message,
					'sent_to_admin'  => false,
					'plain_text'     => true,
					'email'          => $this,
				),
				'',
				$this->template_base
			);
		}
	}

endif;

return new WC_EMAIL_Mp_Rfq_Notification();
