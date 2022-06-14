<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$msg          = sprintf( /* translators: %s: Blog name. */ esc_html__( 'New Seller registration on %s:', 'wk-marketplace' ), get_option( 'blogname' ) ) . "\n\n\r\n\r\n\n\n";
$username     = utf8_decode( esc_html__( 'Username :- ', 'wk-marketplace' ) ) . $data['user_name'];
$seller_email = utf8_decode( esc_html__( 'User email :- ', 'wk-marketplace' ) ) . $data['user_email'];
$shop_url     = utf8_decode( esc_html__( 'Seller Shop URL :- ', 'wk-marketplace' ) ) . $data['shop_url'];

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo sprintf( /* translators: %s: User Name. */ esc_html__( 'Hi %s,', 'wk-marketplace' ), utf8_decode( $data['user_name'] ) ) . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $msg . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $username . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $seller_email . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $shop_url . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
