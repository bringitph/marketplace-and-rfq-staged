<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $data ) {
	$query_id = $data['q_id'];
	$adm_msg  = utf8_decode( $data['adm_msg'] );
	$query    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where id = %d", $query_id ) );

	if ( $query ) {
		$q_data            = $query[0];
		$msg               = utf8_decode( esc_html__( 'We received your query about: ', 'wk-marketplace' ) );
		$admin             = utf8_decode( esc_html__( 'Message : ', 'wk-marketplace' ) );
		$admin_message     = utf8_decode( $q_data->message );
		$reference         = utf8_decode( esc_html__( 'Subject : ', 'wk-marketplace' ) );
		$reference_message = utf8_decode( $q_data->subject );
		$adm_ans           = utf8_decode( esc_html__( 'Answer : ', 'wk-marketplace' ) );
		$closing_msg       = utf8_decode( esc_html__( 'Please, do contact us if you have additional queries. Thanks again!', 'wk-marketplace' ) );

		echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo sprintf( /* translators: %s Customer first name */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_html( utf8_decode( $customer_email ) ) ) . "\n\n";

		echo $msg . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $reference_message . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $admin . ' ' . $admin_message . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $adm_ans . ' ' . $adm_msg . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $closing_msg . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

		echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}
}
