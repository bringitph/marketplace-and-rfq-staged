<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$_product     = wc_get_product( $product );
$product_name = utf8_decode( $_product->get_name() );
$user_name    = utf8_decode( get_user_meta( $user, 'first_name', true ) );
$welcome      = utf8_decode( esc_html__( 'Vendor ', 'wk-marketplace' ) ) . $user_name . utf8_decode( esc_html__( ' has requested to publish ', 'wk-marketplace' ) ) . '<strong>' . $product_name . '</strong> ' . utf8_decode( esc_html__( 'product', 'wk-marketplace' ) ) . ' ! ';
$msg          = utf8_decode( esc_html__( 'Please review the request', 'wk-marketplace' ) );
$review_here  = sprintf( admin_url( 'post.php?post=%s&action=edit' ), $product );
$admin        = get_option( 'admin_email' );

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo utf8_decode( esc_html__( 'Hi', 'wk-marketplace' ) ) . ', ' . $admin . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $welcome; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $msg . "\n\n" . '<a href=' . $review_here . '>' . utf8_decode( esc_html__( 'Here', 'wk-marketplace' ) ) . '</a>' . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
