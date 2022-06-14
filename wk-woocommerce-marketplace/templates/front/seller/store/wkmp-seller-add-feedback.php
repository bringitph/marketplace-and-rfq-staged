<?php
/**
 * Add Feedback.
 *
 * @package WkMarketplace\Includes\Shipping
 */

defined( 'ABSPATH' ) || exit;

$current_user_id = get_current_user_id();
$review_status   = isset( $add_feed_status[0]->status ) ? $add_feed_status[0]->status : '3';
?>

<div id="wkmp_seller_review_form" class="mp-profile-wrapper woocommerce">

	<?php $this->wkmp_seller_profile_details_section(); ?>

	<?php if ( $current_user_id && intval( $this->seller_id ) !== $current_user_id && $review_status > 1 ) { ?>
		<div class="mp-add-feedback-section">
			<h4><?php esc_html_e( 'Write your review', 'wk-marketplace' ); ?></h4>
			<b><p><?php esc_html_e( 'How do you rate this store ?', 'wk-marketplace' ); ?> <span class="error-class">*</span></p></b>
			<form action="" class="mp-seller-review-form" method="post" enctype="multipart/form-data">
				<div class="wkmp_feedback_main_in">
					<div class="mp-feedback-price-rating mp-rating-input" data-id="#feed-price-rating">
						<p><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></p>
						<?php if ( isset( $feed_price_error ) && $feed_price_error ) { ?>
							<div class="text-danger"><?php echo esc_html( $feed_price_error ); ?></div>
						<?php } ?>
						<p class="stars">
						<span>
							<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
								<?php if ( isset( $feed_price ) && $feed_price === $i ) { ?>
									<a class="star-<?php echo esc_attr( $i ); ?> active"><?php echo esc_html( $i ); ?></a>
								<?php } else { ?>
									<a class="star-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></a>
								<?php } ?>
							<?php } ?>
						</span>
						</p>
						<select name="feed_price" id="feed-price-rating" aria-required="true" style="display:none;">
							<option value=""></option>
							<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
								<?php if ( isset( $feed_price ) && $feed_price === $i ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" selected></option>
								<?php } else { ?>
									<option value="<?php echo esc_attr( $i ); ?>"></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="mp-feedback-value-rating mp-rating-input" data-id="#feed-value-rating">
						<p><?php esc_html_e( 'Value', 'wk-marketplace' ); ?></p>
						<?php if ( isset( $feed_value_error ) && $feed_value_error ) { ?>
							<div class="text-danger"><?php echo esc_html( $feed_value_error ); ?></div>
						<?php } ?>
						<p class="stars">
						<span>
							<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
								<?php if ( isset( $feed_value ) && $feed_value === $i ) { ?>
									<a class="star-<?php echo esc_attr( $i ); ?> active"><?php echo esc_html( $i ); ?></a>
								<?php } else { ?>
									<a class="star-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></a>
								<?php } ?>
							<?php } ?>
						</span>
						</p>
						<select name="feed_value" id="feed-value-rating" aria-required="true" style="display:none;">
							<option value=""></option>
							<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
								<?php if ( isset( $feed_value ) && $feed_value === $i ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" selected></option>
								<?php } else { ?>
									<option value="<?php echo esc_attr( $i ); ?>"></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="mp-feedback-quality-rating mp-rating-input" data-id="#feed-quality-rating">
						<p><?php esc_html_e( 'Quality', 'wk-marketplace' ); ?></p>
						<?php if ( isset( $feed_quality_error ) && $feed_quality_error ) { ?>
							<div class="text-danger"><?php echo esc_html( $feed_quality_error ); ?></div>
						<?php } ?>
						<p class="stars">
						<span>
							<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
								<?php if ( isset( $feed_quality ) && $feed_quality === $i ) { ?>
									<a class="star-<?php echo esc_attr( $i ); ?> active"><?php echo esc_html( $i ); ?></a>
								<?php } else { ?>
									<a class="star-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></a>
								<?php } ?>
							<?php } ?>
						</span>
						</p>
						<select name="feed_quality" id="feed-quality-rating" aria-required="true" style="display:none;">
							<option value=""></option>
							<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
								<?php if ( isset( $feed_quality ) && $feed_quality === $i ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" selected></option>
								<?php } else { ?>
									<option value="<?php echo esc_attr( $i ); ?>"></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>

				</div>

				<div class="error-class" id="feedback-rate-error"></div>
				<div class="wkmp_feedback_fields_in">
					<p><b><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?><span class="error-class">*</span></b></p>
					<input type="text" name="feed_summary" class="form-row-wide" value="<?php echo isset( $feed_summary ) ? esc_attr( $feed_summary ) : ''; ?>">
					<?php if ( isset( $feed_summary_error ) && $feed_summary_error ) { ?>
						<div class="text-danger"><?php echo esc_html( $feed_summary_error ); ?></div>
					<?php } ?>
				</div>
				<div class="wkmp_feedback_fields_in">
					<p><b><?php esc_html_e( 'Review', 'wk-marketplace' ); ?><span class="error-class">*</span></b></p>
					<textarea rows="4" name="feed_review" class="form-row-wide"><?php echo isset( $feed_review ) ? esc_html( $feed_review ) : ''; ?></textarea>
					<?php if ( isset( $feed_description_error ) && $feed_description_error ) { ?>
						<div class="text-danger"><?php echo esc_html( $feed_description_error ); ?></div>
					<?php } ?>
				</div>
				<?php wp_nonce_field( 'wkmp-add-feedback-nonce-action', 'wkmp-add-feedback-nonce' ); ?>
				<p><input type="submit" id="wk_mp_reviews_user" value="<?php esc_attr_e( 'Submit Review', 'wk-marketplace' ); ?>" class="button"></p>
			</form>
		</div>
	<?php } ?>

</div>
