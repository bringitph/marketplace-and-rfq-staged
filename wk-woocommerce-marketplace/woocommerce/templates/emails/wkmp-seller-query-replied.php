<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( $data ) {
	$mail_to    = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
	$mail_data  = empty( $data['mail_data'] ) ? array() : $data['mail_data'];
	$reply_data = empty( $data['reply_data'] ) ? array() : $data['reply_data'];
	$query_info = empty( $reply_data['query_info'] ) ? array() : $reply_data['query_info'];

	$msg         = html_entity_decode( esc_html__( 'We received your query about: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . "\r\n\r\n";
	$closing_msg = html_entity_decode( esc_html__( 'Please, do contact us if you have additional queries. Thanks again!', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );

	do_action( 'woocommerce_email_header', $email_heading, $mail_to );

	$result = '
			<p>' . $mail_data['hi_msg'] . ',</p>
			<p>' . $msg . '</p>
			<p><strong>' . $mail_data['subject_label'] . '</strong>' . $query_info->subject . '</p>
			<p><strong>' . $mail_data['message_label'] . '</strong>' . $query_info->message . '</p>
			<p><strong>' . $mail_data['answer_label'] . '</strong>' . $reply_data['reply_message'] . '</p>
			<p>' . $closing_msg . '</p>';

	if ( ! empty( $additional_content ) ) {
		$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
	}
	echo wp_kses_post( $result );

	do_action( 'woocommerce_email_footer', $mail_to );
}


