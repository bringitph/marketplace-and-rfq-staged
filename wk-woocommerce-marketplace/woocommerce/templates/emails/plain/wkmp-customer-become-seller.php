<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$seller_login = $data['user_login'];
$auto_approve = $data['auto_approve'];

$welcome = sprintf( utf8_decode( esc_html__( 'Thank you for your request to become a seller on ', 'wk-marketplace' ) ) . get_option( 'blogname' ) . '!' ) . "\r\n\n";
$welcome = $auto_approve ? sprintf( utf8_decode( esc_html__( 'Congratulations!! Now you are a seller on ', 'wk-marketplace' ) ) . get_option( 'blogname' ) . '!' ) . "\r\n\n" : $welcome;

$store_name = utf8_decode( esc_html__( 'Your store name is: ', 'wk-marketplace' ) ) . $data['store_name'] . "\n\n\r\n\r\n\n\n";
$store_url  = utf8_decode( esc_html__( 'Your store URL is: ', 'wk-marketplace' ) ) . $data['shop_url'] . "\n\n\r\n\r\n\n\n";
$phone      = utf8_decode( esc_html__( 'Your store URL is: ', 'wk-marketplace' ) ) . $data['phone'] . "\n\n\r\n\r\n\n\n";

$msg = utf8_decode( esc_html__( 'Your request is under process, you will get notified in next email once it is approved.', 'wk-marketplace' ) ) . "\n\n\r\n\r\n\n\n";
$msg = $auto_approve ? utf8_decode( esc_html__( 'You can add your products and continue selling.', 'wk-marketplace' ) ) . "\n\n\r\n\r\n\n\n" : $msg;

$admin     = get_option( 'admin_email' );
$reference = utf8_decode( esc_html__( 'If you have any problems, please contact me at', 'wk-marketplace' ) ) . "\r\n\r\n";

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo sprintf( /* translators: %s Customer first name. */esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_html( utf8_decode( $seller_login ) ) ) . "\n\n";

echo $welcome . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $store_name . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $store_url . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $phone . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $msg . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $reference . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<a href="mailto:' . $admin . '">' . $admin . '</a>'; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
