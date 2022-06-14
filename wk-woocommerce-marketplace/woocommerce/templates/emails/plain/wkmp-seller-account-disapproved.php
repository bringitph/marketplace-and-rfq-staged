<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$msg        = utf8_decode( esc_html__( 'Your account has been Disapproved by admin ', 'wk-marketplace' ) );
$admin      = get_option( 'admin_email' );
$reference  = utf8_decode( esc_html__( 'If you have any query, please contact us at -', 'wk-marketplace' ) );
$thanks_msg = utf8_decode( esc_html__( 'Thanks for choosing Marketplace.', 'wk-marketplace' ) );

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo esc_html__( 'Hi', 'wk-marketplace' ) . ', ' . utf8_decode( $user_email ) . " \n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $msg . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $reference . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo '<a href="mailto:' . $admin . '">' . $admin . '</a>' . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $thanks_msg . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
