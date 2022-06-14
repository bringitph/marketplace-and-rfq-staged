<?php
/**
 * Details Sections on Seller Collection page.
 *
 * @package WkMarketplace
 */

defined( 'ABSPATH' ) || exit;

$current_user_id = get_current_user_id();
$review_status   = isset( $review_check[0]->status ) ? $review_check[0]->status : '3';
$mp_page_title   = empty( $seller_info->shop_name ) ? '' : $seller_info->shop_name;
if ( empty( $mp_page_title ) ) {
	$seller_name   = empty( $seller_info->first_name ) ? '' : $seller_info->first_name;
	$seller_name   = ( ! empty( $seller_name ) && ! empty( $seller_info->last_name ) ) ? $seller_name . ' ' . $seller_info->last_name : $seller_name;
	$mp_page_title = empty( $seller_name ) ? esc_html__( 'Store Page', 'wk-marketplace' ) : $seller_name;
}

?>
<h1 class="mp-page-title"><?php echo esc_html( $mp_page_title ); ?></h1>

<div class="mp-profile-information">
	<div class="mp-shop-stats">
		<img src="<?php echo esc_url( $shop_logo ); ?>" class="mp-shop-logo">
		<div class="mp-seller-avg-rating">
			<?php if ( $quality > 0 ) { ?>
				<h2><span class="single-star"></span><?php echo number_format( $quality, 2 ); ?></h2>
				<a href="javascript:void(0)" class="mp-avg-rating-box-link"><?php esc_html_e( 'Average Rating', 'wk-marketplace' ); ?>
					<div class="mp-avg-rating-box">
						<div class="mp-avg-rating">
							<p><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></p>
							<?php echo wp_kses_post( wc_get_rating_html( $price_stars ) ); ?>
							<p>( <?php echo esc_html( number_format( $price_stars, 2 ) . '/' . $total_feedback ); ?> )</p>
						</div>
						<div class="mp-avg-rating">
							<p><?php esc_html_e( 'Value', 'wk-marketplace' ); ?></p>
							<?php echo wp_kses_post( wc_get_rating_html( $value_stars ) ); ?>
							<p>( <?php echo esc_html( number_format( $value_stars, 2 ) . '/' . $total_feedback ); ?> )</p>
						</div>
						<div class="mp-avg-rating">
							<p><?php esc_html_e( 'Quality', 'wk-marketplace' ); ?></p>
							<?php echo wp_kses_post( wc_get_rating_html( $quality_stars ) ); ?>
							<p>( <?php echo esc_html( number_format( $quality_stars, 2 ) . '/' . $total_feedback ); ?> )</p>
						</div>
					</div>
				</a>
				<?php
			} else {
				if ( $current_user_id > 0 && intval( $this->seller_id ) !== $current_user_id && 0 === intval( $review_status ) ) {
					?>
					<div class="wk_write_review">
						<p class="wkmp-pending-reviews">
							<?php esc_html_e( 'Your review is pending for approval.', 'wk-marketplace' ); ?>
						</p>
					</div>
				<?php } else { ?>
					<div class="wk_write_review">
						<a class="open-review-form forloginuser wk_mpsocial_feedback" href="#wkmp_seller_review_form"><?php esc_html_e( 'Be the first one to review!', 'wk-marketplace' ); ?></a>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>

	<div class="mp-shop-actions-info">
		<div class="mp-shop-action-wrapper">
			<div class="mp-shop-info">
				<div>
					<?php if ( 'yes' === get_option( '_wkmp_is_seller_email_visible' ) ) { ?>
						<span class="dashicons dashicons-email" style="margin-top:4px;"></span>
						<a href="mailto:<?php echo esc_url( $seller_info->user_email ); ?>"><?php echo esc_html( $seller_info->user_email ); ?></a>
					<?php } ?>
				</div>
				<div>
					<?php if ( 'yes' === get_option( '_wkmp_is_seller_contact_visible' ) ) { ?>
						<span class="dashicons dashicons-phone" style="margin-top:4px;"></span>
						<a href="tel:<?php echo esc_attr( $seller_info->billing_phone ); ?>" target="_blank" title="<?php esc_attr_e( 'Click to Dial - Phone Only', 'wk-marketplace' ); ?>"><?php echo isset( $seller_info->billing_phone ) ? esc_html( $seller_info->billing_phone ) : ''; ?></a>
					<?php } ?>
				</div>
				<div>
					<?php if ( 'yes' === get_option( '_wkmp_is_seller_address_visible' ) ) { ?>
						<span class="dashicons dashicons-location" style="margin-top:4px;"> </span>
						<?php
						echo isset( $seller_info->billing_city ) ? esc_html( $seller_info->billing_city ) : '';
						echo isset( $seller_info->billing_country ) ? ' ' . esc_html( $seller_info->billing_country ) : '';
						?>
					<?php } ?>
				</div>
				<?php if ( 'yes' === get_option( '_wkmp_is_seller_social_links_visible' ) ) : ?>
					<?php require_once __DIR__ . '/wkmp-seller-social-links-section.php'; ?>
				<?php endif; ?>
			</div>
			<div class="mp-shop-actions">
				<a class="button wc-forward" href="<?php echo esc_url( $seller_store ); ?>"><?php esc_html_e( 'View Profile', 'wk-marketplace' ); ?></a>
			</div>
		</div>
	</div>
</div>
