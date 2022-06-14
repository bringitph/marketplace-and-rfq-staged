<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$mail_to      = empty( $data['mail_to'] ) ? get_option( 'admin_email', false ) : $data['mail_to'];
$seller_login = empty( $data['user_login'] ) ? '' : $data['user_login'];
$auto_approve = empty( $data['auto_approve'] ) ? '' : $data['auto_approve'];
$store_name   = empty( $data['store_name'] ) ? '' : $data['store_name'];
$shop_url     = empty( $data['shop_url'] ) ? '' : $data['shop_url'];
$phone        = empty( $data['phone'] ) ? '' : $data['phone'];
$mail_data    = empty( $data['mail_data'] ) ? array() : $data['mail_data'];

$welcome = sprintf( html_entity_decode( esc_html__( 'Thank you for showing your interest to become a seller on ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . get_option( 'blogname' ) . '!' ) . "\r\n\n";
$welcome = $auto_approve ? sprintf( html_entity_decode( esc_html__( 'Congratulations!! Now you are a seller on ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . get_option( 'blogname' ) . '!' ) . "\r\n\n" : $welcome;

$store_name = html_entity_decode( esc_html__( 'Your store name is: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . $store_name . "\n\n\r\n\r\n\n\n";
$store_url  = html_entity_decode( esc_html__( 'Your store URL is: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . $shop_url . "\n\n\r\n\r\n\n\n";
$phone      = html_entity_decode( esc_html__( 'Your contact is: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . $phone . "\n\n\r\n\r\n\n\n";

$msg = html_entity_decode( esc_html__( 'Your request is under process, you will get notified in next email once it is approved.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . "\n\n\r\n\r\n\n\n";
$msg = $auto_approve ? html_entity_decode( esc_html__( 'You can add your products and continue selling.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . "\n\n\r\n\r\n\n\n" : $msg;

$admin = get_option( 'admin_email' );

do_action( 'woocommerce_email_header', $email_heading, $mail_to );

$result = '<p>' . $mail_data['hi_msg'] . $seller_login . ',</p>
			<p> <strong>' . $welcome . '</strong><p>
			<p>' . $store_name . '</p>
			<p>' . $store_url . '</p>
			<p>' . $phone . '</p>
			<p>' . $msg . '</p>
			<p><a target="_blank" href="' . esc_url( $mail_data['seller_url'] ) . '">' . $mail_data['click_text'] . '</a>' . $mail_data['profile_msg'] . '</p>
			<p>' . $mail_data['reference_msg'] . ' :-</p>
			<p><a href="mailto:' . $admin . '">' . $admin . '</a></p>';

if ( ! empty( $additional_content ) ) {
	$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
}

echo wp_kses_post( $result );
do_action( 'woocommerce_email_footer', $mail_to );
