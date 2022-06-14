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
$msg          = '';
$review_here  = '';
$welcome      = utf8_decode( esc_html__( 'Unfortunately! Your product ( ', 'wk-marketplace' ) ) . '<strong>' . esc_html( $product_name ) . '</strong> ' . utf8_decode( esc_html__( ' ) has been rejected by Admin!', 'wk-marketplace' ) );

if ( $status ) {
	$welcome     = utf8_decode( esc_html__( 'Congrats! Your product ( ', 'wk-marketplace' ) ) . '<strong>' . esc_html( $product_name ) . '</strong> ' . utf8_decode( esc_html__( ' ) has been published!', 'wk-marketplace' ) );
	$msg         = utf8_decode( esc_html__( 'Click here to view it ', 'wk-marketplace' ) );
	$review_here = get_the_permalink( $product );
	$review_here = ' <a href=' . $review_here . '>' . utf8_decode( esc_html__( 'Here', 'wk-marketplace' ) ) . '</a>';
}

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo utf8_decode( esc_html__( 'Hi', 'wk-marketplace' ) ) . ', ' . $user_name . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $welcome; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo $msg . "\n\n" . $review_here . "\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
