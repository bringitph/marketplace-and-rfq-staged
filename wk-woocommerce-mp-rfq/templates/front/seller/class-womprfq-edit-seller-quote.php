<?php

/**
 * Seller table template
 */

namespace wooMarketplaceRFQ\Templates\Front\Seller;

use wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Edit_Seller_Quote' ) ) {

	/**
	 * Class seller table data
	 */
	class Womprfq_Edit_Seller_Quote {

		protected $sel_quote_id;
		protected $helper;
		protected $status_title;

		public function __construct( $id ) {
			$this->helper       = new Helper\Womprfq_Quote_handler();
			$this->sel_quote_id = $id;
			$this->status_title = array(
				0  => esc_html__( 'Open', 'wk-mp-rfq' ),
				1  => esc_html__( 'Pending', 'wk-mp-rfq' ),
				2  => esc_html__( 'Answered', 'wk-mp-rfq' ),
				3  => esc_html__( 'Resolved', 'wk-mp-rfq' ),
				4  => esc_html__( 'Closed', 'wk-mp-rfq' ),
				22 => esc_html__( 'Answered', 'wk-mp-rfq' ),
			);
		}

		public function womprfq_prepare_seller_edit_template( $seller_quote ) {
			if ( $seller_quote ) {
				if ( isset( $seller_quote->price ) ) {
					$price = floatval( $seller_quote->price );
				} else {
					$price = 0;
				}
				if ( isset( $seller_quote->quantity ) ) {
					$quantity = intval( $seller_quote->quantity );
				} else {
					$quantity = 0;
				}
				if ( isset( $seller_quote->commission ) ) {
					$commission = floatval( $seller_quote->commission );
				} else {
					$commission = 0;
				}
				$this->womprfq_seller_quote_comment_template( $seller_quote );
				?>
				<h2>
					<?php esc_html_e( 'Your Quoted Info', 'wk-mp-rfq' ); ?>
				</h2>
				<div class="wkmp-rfq-sut-edit-quote">
					<form  method="POST" class="wk-seller-quotation-form" id="wk-seller-quotation-form">
						<table class="form-table wc_status_table widefat">
							<tbody>
							<?php
							$this->womprfq_seller_quote_form_template( $seller_quote, $price, $quantity, $commission );
							$meta = $this->helper->womprfq_get_quote_meta_info( $seller_quote->main_quotation_id );
							if ( isset( $meta['quote_product_id'] ) && ! empty( $meta['quote_product_id'] ) ) {
								$auth = intval( get_post_field( 'post_author', intval( $meta['quote_product_id'] ) ) );
							} else {
								$auth = 0;
							}
							if ( $auth == get_current_user_id() ) {
								?>
								<tr valign="top">
									<td colspan="2">
										<b>
											<span class='wk_accepted_quote'>
												<?php esc_html_e( 'Quotation Accepted', 'wo-mop-rfq' ); ?>
											</span>
										</b>
									</td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</form>
				</div>
				<div class="wk-mp-loader-rfq" id="wk-mp-loader-rfq"><div class="wk-mp-spinner wk-mp-skeleton"><!--////--></div></div>
				<?php
			}
		}

		public function womprfq_seller_quote_form_template( $seller_quote, $price, $quantity, $commission ) {
			?>
			<tr valign="top">
				<th>
					<label><?php esc_html_e( 'Status', 'wk-mp-rfq' ); ?></label>
				</th>
				<td class="forminp">
					<span><?php echo esc_html( $this->status_title[ $seller_quote->status ] ); ?></span>
				</td>
			</tr>
			<?php
			if ( in_array( intval( $seller_quote->status ), array( 0, 1, 2, 22 ) ) ) {
				?>
				<tr valign="top">
					<th>
						<label for="seller-quote-quantity"><?php esc_html_e( 'Quantity', 'wk-mp-rfq' ); ?></label>
						<span class="required">*</span>
					</th>
					<td class="forminp">
						<input type="number" id="seller-quote-quantity" name="seller-quote-quantity" value="<?php echo esc_attr( $quantity ); ?>">

					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="seller-quote-price"><?php esc_html_e( 'Price/Product', 'wk-mp-rfq' ); ?></label>
						<span class="required">*</span>
					</th>
					<td class="forminp">
						<input type="text" id="seller-quote-price" name="seller-quote-price" value="<?php echo esc_attr( $price ); ?>">
					</td>
				</tr>
				<tr valign="top">
				<th>
					<label for="seller-quote-commission"><?php esc_html_e( 'Commission/Order', 'wk-mp-rfq' ); ?></label>
					<span class="required">*</span>
				</th>
				<td class="forminp">
					<input type="text" id="seller-quote-commission" name="seller-quote-commission" value="<?php echo esc_attr( $commission ); ?>">
				</td>
			</tr>
				<tr valign="top">
					<th>
						<label for="seller-quote-comment"><?php esc_html_e( 'Comment', 'wk-mp-rfq' ); ?></label>
						<span class="required">*</span>
					</th>
					<td class="forminp">
						<textarea rows="6" cols="23" id="seller-quote-comment" class="regular-text" name="seller-quote-comment"></textarea>
						<?php echo wc_help_tip( esc_html__( 'Enter text to add comment to quote.', 'wk-mp-rfq' ), false ); ?>
						<span id="wk-mp-rfq-image-container"></span>
						<input type="hidden" id="seller-quote-comment-image" name="seller-quote-comment-image">
						<span class="seller-quote-comment-image-add" title="<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>" id="seller-quote-comment-image-add">
							<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>
						</span>
					</td>
				</tr>
				<tr  valign="top">
					<th colspan="2">
						<?php wp_nonce_field( 'wc-seller-quote-nonce-action', 'wc-seller-quote-nonce' ); ?>
						<input type="submit" name="update-seller-new-quotation-submit" value="<?php esc_html_e( 'Update Quotation', 'wk-mp-rfq' ); ?>" class="button button-primary" />
					</th>
				</tr>
				<?php
			} elseif ( intval( $seller_quote->status ) == 3 ) {

				?>
				<tr valign="top">
					<th>
						<label for="seller-quote-quantity"><?php esc_html_e( 'Quantity', 'wk-mp-rfq' ); ?></label>
					</th>
					<td class="forminp">
						<span><?php echo esc_attr( $quantity ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="seller-quote-price"><?php esc_html_e( 'Price/Product', 'wk-mp-rfq' ); ?></label>
					</th>
					<td class="forminp">
					<span><?php echo wc_price( $price ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="seller-quote-commission"><?php esc_html_e( 'Commission/Order', 'wk-mp-rfq' ); ?></label>
					</th>
					<td class="forminp">
					<span><?php echo wc_price( $commission ); ?></span>
					</td>
				</tr>
				<?php
				echo '<pre>';
				$main_quote_data = $this->helper->womprfq_get_main_quotation_by_id( intval( $seller_quote->main_quotation_id ) );
				if ( $main_quote_data ) {
					$update_p = false;
					if ( get_post_field( 'post_author', $main_quote_data->product_id ) != get_current_user_id() ) {
						$sel_data = get_post_meta( $main_quote_data->product_id, 'wkmprfq_main_quote_data', true );
						if ( isset( $sel_data[ get_current_user_id() ] ) ) {
							$update_p = true;
						}
					} else {
						$update_p = true;
					}

					if ( $main_quote_data->product_id != 0 && $update_p ) {
						?>
						<tr valign='top'>
							<td colsapan="2">
								<button id="wkmp-rfq-update-product" data-mqid="<?php echo esc_html( intval( $seller_quote->main_quotation_id ) ); ?>" data-sqid="<?php echo esc_html( intval( $seller_quote->id ) ); ?>" class="button">
									<?php esc_html_e( 'Update Product', 'wk-mp-rfq' ); ?>
								</button>
							</td>
						</tr>
						<?php
					} else {
						?>
						<tr valign='top'>
							<td colsapan="2">
								<button id="wkmp-rfq-create-product" data-mqid="<?php echo esc_html( intval( $seller_quote->main_quotation_id ) ); ?>" data-sqid="<?php echo esc_html( intval( $seller_quote->id ) ); ?>" class="button">
									<?php esc_html_e( 'Create Product', 'wk-mp-rfq' ); ?>
								</button>
							</td>
						</tr>
						<?php
					}
				}
			} else {
				?>
				<tr valign="top">
					<th>
						<label for="seller-quote-quantity"><?php esc_html_e( 'Quantity', 'wk-mp-rfq' ); ?></label>
					</th>
					<td class="forminp">
						<span><?php echo esc_attr( $quantity ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="seller-quote-price"><?php esc_html_e( 'Price/Product', 'wk-mp-rfq' ); ?></label>
					</th>
					<td class="forminp">
					<span><?php echo wc_price( $price ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="seller-quote-commission"><?php esc_html_e( 'Commission/Order', 'wk-mp-rfq' ); ?></label>
					</th>
					<td class="forminp">
					<span><?php echo wc_price( $commission ); ?></span>
					</td>
				</tr>
				<?php
			}
		}

		public function womprfq_seller_quote_comment_template( $quote_data ) {
			if ( isset( $quote_data->id ) ) {
				$seller_id    = get_current_user_id();
				$comment_data = $this->helper->womprfq_get_seller_quote_comment_details( $this->sel_quote_id );
				?>
				<h2><?php esc_html_e( 'Comments', 'wk-mp-rfq' ); ?></h2>
				<div class="wkmp-rfq-sut-edit-quote">
					<table class="form-table wc_status_table widefat">
						<tbody>
						<?php
						if ( ! empty( $comment_data ) ) {
							foreach ( $comment_data as $comment ) {
								if ( $seller_id == $comment['sender_id'] ) {
									$pos_class       = 'wkmprfq-message-self';
									$pos_arrow_class = 'wkmprfq-message-arrow-self';
								} else {
									$pos_class       = 'wkmprfq-message-other';
									$pos_arrow_class = 'wkmprfq-message-arrow-other';
								}
								?>
								<tr valign="top">
									<td colspan="2" class="forminp" >
										<p class="wk-sup-comment-body <?php echo esc_attr( $pos_arrow_class ); ?>">
											<span class="wk-sup-bold"><?php esc_html_e( 'Message : ', 'wk-mp-rfq' ); ?></span>
											<span><?php echo esc_html( $comment['comment_text'] ); ?></span>
											<span class="wk-mp-rfq-comment-image-container" id="wk-mp-rfq-comment-image-container">
												<?php
												if ( ! empty( $comment['image'] ) ) {
													$images = explode( ',', $comment['image'] );
													if ( ! empty( $images ) ) {
														foreach ( $images as $image ) {
															if ( wp_get_attachment_url( intval( $image ) ) ) {
																?>
																<img src='<?php echo esc_url( wp_get_attachment_url( intval( $image ) ) ); ?>' class='wpmp-rfq-form-pro-img'/>
																<?php
															}
														}
													}
												}
												?>
											</span>
										</p>
										<p class="wk-sup-comment-head <?php echo esc_attr( $pos_class ); ?>">
											<span class="wk-sup-date-sections">
												<?php
												$date = new \DateTime( $comment['date'] );
												echo esc_html( $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) );
												esc_html_e( ' by ', 'wk-mp-rfq' );
												if ( $comment['sender_id'] == 0 ) {
													esc_html_e( 'Admin', 'wk-mp-rfq' );
												} elseif ( $seller_id == $comment['sender_id'] ) {
													esc_html_e( 'You', 'wk-mp-rfq' );
												} else {
													esc_html_e( 'Customer', 'wk-mp-rfq' );
												}
												?>

											</span>
										</p>
									</td>
								</tr>
								<?php
							}
						} else {
							?>
							<tr valign="top">
									<td class="forminp" colspan="2">
										<?php esc_html_e( 'No Comment Yet.', 'wk-mp-rfq' ); ?>
									</td>
								</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<?php
			}
		}
	}
}
