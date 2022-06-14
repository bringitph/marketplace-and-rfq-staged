<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$loginurl  = $data['user_login'];
$welcome   = sprintf( utf8_decode( esc_html__( 'Welcome to ', 'wk-marketplace' ) ) . get_option( 'blogname' ) . '!' ) . "\r\n\n";
$msg       = utf8_decode( esc_html__( 'Your account has been created awaiting for admin approval.', 'wk-marketplace' ) ) . "\n\n\r\n\r\n\n\n";
$username  = utf8_decode( esc_html__( 'User :- ', 'wk-marketplace' ) ) . $data['user_email'];
$password  = utf8_decode( esc_html__( 'User Password :- ', 'wk-marketplace' ) ) . $data['user_pass'];
$admin     = get_option( 'admin_email' );
$reference = utf8_decode( esc_html__( 'If you have any problems, please contact me at', 'wk-marketplace' ) ) . "\r\n\r\n";

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo sprintf( /* translators: %s: Login URL. */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_html( utf8_decode( $loginurl ) ) ) . "\n\n";

echo $welcome . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $msg . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $username . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $password . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $reference . "\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<a href="mailto:' . $admin . '">' . $admin . '</a>'; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
