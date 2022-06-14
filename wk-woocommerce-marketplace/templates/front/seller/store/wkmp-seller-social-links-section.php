<?php
/**
 * Link Sections.
 *
 * @package WkMarketplace\Includes\Shipping
 */

defined( 'ABSPATH' ) || exit;

echo '<div class="mp-shop-social-links">';
if ( isset( $seller_info->social_facebok ) && $seller_info->social_facebok ) {
	echo '<a href="' . esc_url( $seller_info->social_facebok ) . '" target="_blank" class="mp-social-icon fb"></a>';
}
if ( isset( $seller_info->social_instagram ) && $seller_info->social_instagram ) {
	echo '<a href="' . esc_url( $seller_info->social_instagram ) . '" target="_blank" class="mp-social-icon instagram"></a>';
}
if ( isset( $seller_info->social_twitter ) && $seller_info->social_twitter ) {
	echo '<a href="' . esc_url( $seller_info->social_twitter ) . '" target="_blank" class="mp-social-icon twitter"></a>';
}
if ( isset( $seller_info->social_linkedin ) && $seller_info->social_linkedin ) {
	echo '<a href="' . esc_url( $seller_info->social_linkedin ) . '" target="_blank" class="mp-social-icon in"></a>';
}
if ( isset( $seller_info->social_youtube ) && $seller_info->social_youtube ) {
	echo '<a href="' . esc_url( $seller_info->social_youtube ) . '" target="_blank" class="mp-social-icon yt"></a>';
}
echo '</div>';
