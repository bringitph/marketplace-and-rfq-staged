<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$username      = utf8_decode( esc_html__( 'Email: ', 'wk-marketplace' ) );
$username_mail = utf8_decode( $data['email'] );

$user_obj          = get_user_by( 'email', $username_mail );
$user_name         = $user_obj->first_name ? $user_obj->first_name . ' ' . $user_obj->last_name : esc_html__( 'Someone', 'wk-marketplace' );
$msg               = utf8_decode( $user_name . ' ' . esc_html__( 'asked a query from following account:', 'wk-marketplace' ) );
$admin             = utf8_decode( esc_html__( 'Message: ', 'wk-marketplace' ) );
$admin_message     = utf8_decode( $data['ask'] );
$reference         = utf8_decode( esc_html__( 'Subject : ', 'wk-marketplace' ) );
$reference_message = utf8_decode( $data['subject'] );

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo utf8_decode( esc_html__( 'Hi', 'wk-marketplace' ) ) . ', ' . $admin_email . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $msg . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "<strong>$username</strong>" . $username_mail . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo "<strong>$reference</strong>" . $reference_message . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo "<strong>$admin</strong>" . $admin_message . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
