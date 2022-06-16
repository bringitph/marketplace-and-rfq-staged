<?php
/**
 * Store info.
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

<div class="mp-profile-wrapper woocommerce">
	<h1 class="mp-page-title"><?php echo esc_html( $mp_page_title ); ?></h1>
	<div class="mp-profile-banner">
		<?php if ( isset( $shop_banner ) && $shop_banner ) { ?>
			<img src="<?php echo esc_url( $shop_banner ); ?>" class="mp-shop-banner">
		<?php } ?>
	</div>
	<div class="mp-profile-information">
		<div class="mp-shop-stats">
			<img src="<?php echo esc_url( $shop_logo ); ?>" class="mp-shop-logo">
			<div class="mp-seller-avg-rating">
				<?php if ( $quality ) { ?>
					<h2><span class="single-star"></span><?php echo esc_html( number_format( $quality, 2 ) ); ?></h2>
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
							<a class="open-review-form forloginuser wk_mpsocial_feedback" href="<?php echo esc_url( $add_review ); ?>">
								<?php esc_html_e( 'Be the first one to review!', 'wk-marketplace' ); ?>
							</a>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<?php do_action( 'mp_edit_seller_profile' ); ?>
		<div class="mp-shop-actions-info">
			<div class="mp-shop-action-wrapper">
				<div class="mp-shop-info">
					<div>
						<?php if ( 'yes' === get_option( '_wkmp_is_seller_email_visible' ) ) { ?>
							<span class="dashicons dashicons-email" style="margin-top:4px;"></span>
							<a href="mailto:<?php echo esc_attr( $seller_info->user_email ); ?>"><?php echo esc_html( $seller_info->user_email ); ?></a>
						<?php } ?>
					</div>
					<div>
						<?php if ( 'yes' === get_option( '_wkmp_is_seller_contact_visible' ) ) { ?>
							<span class="dashicons dashicons-phone" style="margin-top:4px;"></span>
							<a href="tel:<?php echo esc_attr( $seller_info->billing_phone ); ?>" target="_blank" title="<?php esc_attr_e( 'Click to Dial - Phone Only', 'wk-marketplace' ); ?>"><?php echo isset( $seller_info->billing_phone ) ? esc_attr( $seller_info->billing_phone ) : ''; ?></a>
						<?php } ?>
					</div>
					<div>
						<?php if ( 'yes' === get_option( '_wkmp_is_seller_address_visible' ) ) { ?>
							<?php
							$address = '';

							$address .= isset( $seller_info->billing_address_1 ) ? $seller_info->billing_address_1 : '';
							$address .= isset( $seller_info->billing_address_2 ) ? ' ' . $seller_info->billing_address_2 : '';
							$address .= isset( $seller_info->billing_postcode ) ? ' (' . $seller_info->billing_postcode . ') ' : '';
							$address .= sprintf( '%s (%s)', isset( $seller_info->billing_city ) ? $seller_info->billing_city : '', isset( $seller_info->billing_country ) ? $seller_info->billing_country : '' );
							?>
							<span class="dashicons dashicons-location" style="margin-top:4px;"> </span> <?php echo esc_html( $address ); ?>
						<?php } ?>
					</div>
					<?php if ( 'yes' === get_option( '_wkmp_is_seller_social_links_visible' ) ) : ?>
						<?php require_once __DIR__ . '/wkmp-seller-social-links-section.php'; ?>
					<?php endif; ?>
				</div>
				<div class="mp-shop-actions">
					<a class="button wc-forward" href="<?php echo esc_url( $seller_collection ); ?>" target="_blank"><?php esc_html_e( 'View Collection', 'wk-marketplace' ); ?></a>

					<?php if ( get_current_user_id() ) { ?>
						<?php if ( get_current_user_id() !== intval( $this->seller_id ) && empty( $review_check ) ) { ?>
							<div class="wk_write_review">
								<a class="btn btn-default button button-small open-review-form forloginuser wk_mpsocial_feedback" href="<?php echo esc_url( $add_review ); ?>" target="_blank"><?php esc_html_e( 'Write A Review!', 'wk-marketplace' ); ?></a>
							</div>
						<?php } ?>
					<?php } else { ?>
						<div class="wk_write_review">
							<a class="btn btn-default button button-small open-review-form forloginuser wk_mpsocial_feedback" href="<?php echo esc_url( $add_review ); ?>" target="_blank"><?php esc_html_e( 'Write A Review!', 'wk-marketplace' ); ?></a>
						</div>
					<?php } ?>

				</div>
			</div>
		</div>
	</div><!-- mp-profile-information -->
	<?php do_action( 'mkt_before_seller_preview_products', $this->seller_id ); ?>

	<div class="mp-seller-recent-product">
		<h3><?php esc_html_e( 'Recent Product from Seller', 'wk-marketplace' ); ?></h3>
		<?php
		$page_no    = ( get_query_var( 'pagenum' ) ) ? get_query_var( 'pagenum' ) : 1;
		$query_args = array(
			'author'         => $this->seller_id,
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 9,
			'paged'          => $page_no,
		);

		$query_args = apply_filters( 'mp_seller_collection_product_args', $query_args );

		$products = new \WP_Query( $query_args );

		if ( $products->have_posts() ) {
			do_action( 'marketplace_before_shop_loop', $products->max_num_pages );
			woocommerce_product_loop_start();
			while ( $products->have_posts() ) :
				$products->the_post();
				wc_get_template_part( 'content', 'product' );
			endwhile;
			woocommerce_product_loop_end();
			do_action( 'marketplace_after_shop_loop', $products->max_num_pages );
		} else {
			esc_html_e( 'No product available !', 'wk-marketplace' );
		}
		wp_reset_postdata();
		?>
	</div>
	<?php do_action( 'mkt_after_seller_preview_products' ); ?>

	<!-- About shop -->
	<div class="mp-about-shop">
		<p><b><?php esc_html_e( 'About', 'wk-marketplace' ); ?></b></p>
		<p><?php echo isset( $seller_info->about_shop ) ? esc_html( $seller_info->about_shop ) : ''; ?></p>
	</div>

	<?php do_action( 'mkt_before_seller_review_data' ); ?>

	<!-- Shop reviews -->
	<?php
	if ( $reviews ) {
		$count = 0;
		?>
		<div class="mp-shop-reviews">
			<?php foreach ( $reviews as $key => $review ) { ?>
				<?php
				if ( 5 === intval( $count ) ) {
					break;
				}
				?>
				<div class="mp-shop-review-row">
					<div class="mp-shop-review-rating">
						<p><b><?php esc_html_e( 'Review', 'wk-marketplace' ); ?></b></p>
						<div class="rating">
							<span><b><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
									<?php if ( $i <= $review->price_r ) { ?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
						<div class="rating">
							<span><b><?php esc_html_e( 'Value', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
									<?php if ( $i <= $review->value_r ) { ?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
						<div>
							<span><b><?php esc_html_e( 'Quality', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
									<?php if ( $i <= $review->quality_r ) { ?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="mp-shop-review-detail">
						<p><b><?php echo esc_html( $review->review_summary ); ?></b></p>
						<p><?php esc_html_e( 'By', 'wk-marketplace' ); ?> <b><?php echo esc_html( $review->nickname ); ?></b>
							, <?php echo esc_html( gmdate( 'd-F-Y', strtotime( $review->review_time ) ) ); ?></p>
						<p><?php echo esc_html( $review->review_desc ); ?></p>
					</div>
				</div>
				<?php
				$count ++;
			}
			?>
			<?php if ( count( $reviews ) > 5 ) { ?>
				<div class="mp-review-page-link">
					<a href="<?php echo esc_url( $all_review ); ?>" class="button"><?php esc_html_e( 'View All Reviews', 'wk-marketplace' ); ?></a>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

</div>
