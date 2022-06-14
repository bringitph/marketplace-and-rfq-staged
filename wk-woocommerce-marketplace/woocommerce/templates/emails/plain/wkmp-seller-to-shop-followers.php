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
	echo sprintf( /* translators: %s Customer first name */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_html( utf8_decode( $data ) ) ) . "\n\n";

	if ( isset( $additional_content ) && ! empty( $additional_content ) ) {
		echo $additional_content; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

	echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
}
