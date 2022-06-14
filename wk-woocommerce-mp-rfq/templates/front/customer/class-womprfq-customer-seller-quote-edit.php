<?php
/**
 * This file handles templates.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Templates\Front\Customer;

use wooMarketplaceRFQ\Helper;
use wooMarketplaceRFQ\Templates\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Customer_Seller_Quote_Edit' ) ) {
	/**
	 * Load hooks.
	 */
	class Womprfq_Customer_Seller_Quote_Edit {

		public $helper;
		public $q_id;
		public $status_title;

		/**
		 * Class constructor.
		 */
		public function __construct( $q_id ) {
			$this->q_id         = $q_id;
			$this->helper       = new Helper\Womprfq_Quote_Handler();
			$this->status_title = array(
				0  => esc_html__( 'Open', 'wk-mp-rfq' ),
				1  => esc_html__( 'Answered', 'wk-mp-rfq' ),
				2  => esc_html__( 'Pending', 'wk-mp-rfq' ),
				3  => esc_html__( 'Resolved', 'wk-mp-rfq' ),
				4  => esc_html__( 'Closed', 'wk-mp-rfq' ),
				22 => esc_html__( 'Open', 'wk-mp-rfq' ),
			);
		}

		/**
		 * Customer template handler
		 */
		public function womprfq_get_customer_seller_quote_template_handler() {
			$post_data = $_REQUEST;
			do_action( 'wkmprfq_after_customer_submit_form', $post_data, $this->q_id );

			if ( $this->q_id ) {
				$seller_data = $this->helper->womprfq_get_seller_quotation_details( $this->q_id );
				if ( $seller_data ) {
					$main_quote_info = $this->helper->womprfq_get_main_quotation_by_id( $seller_data->main_quotation_id );
					if ( $main_quote_info->customer_id == get_current_user_id() ) {
						$temp_obj = new Front\Womprfq_Front_Templates();
						?>
						<div class="wk-mp-rfq-header">
							<h2>
								<?php echo ucfirst( esc_html__( 'Quotation Details', 'wk-mp-rfq' ) ); ?>
							</h2>
						</div>
						<div id="main_container" class="wk_transaction woocommerce-MyAccount-content wk-mp-rfq" style="display: contents;">
						<?php
							$temp_obj->womprfq_get_main_quote_template( $main_quote_info );
							$this->womprfq_get_seller_quote_template( $seller_data );
							$this->womprfq_seller_quote_answer_template( $main_quote_info, $seller_data );
						?>
						</div>
						<?php
					}
				}
			} else {
				// redirect
			}
		}

		public function womprfq_get_seller_quote_template( $seller_data ) {
			if ( $seller_data ) {
				?>
				<div class="wkmp-rfq-seller-quote">
					<h2>
						<?php esc_html_e( 'Seller Quote Details', 'wk-mp-rfq' ); ?>
					</h2>
					<table class="widefat">
						<tbody>
							<tr class="order_item alt-table-row">
								<td colspan="2" class="product-name toptable">
									<strong>
										<?php esc_html_e( 'Quantity', 'wk-mp-rfq' ); ?>
									</strong>
								</td>
								<td colspan="2" class="product-total toptable">
									<?php echo esc_html( $seller_data->quantity ); ?>
								</td>
							</tr>
							<tr class="order_item alt-table-row">
								<td colspan="2" class="product-name toptable">
									<strong>
										<?php esc_html_e( 'Price/Product', 'wk-mp-rfq' ); ?>
									</strong>
								</td>
								<td colspan="2" class="product-total toptable">
									<?php echo wc_price( $seller_data->price ); ?>
								</td>
							</tr>
							<tr class="order_item alt-table-row">
								<td colspan="2" class="product-name toptable">
									<strong>
										<?php esc_html_e( 'Commission/Order', 'wk-mp-rfq' ); ?>
									</strong>
								</td>
								<td colspan="2" class="product-total toptable">
									<?php echo wc_price( $seller_data->commission ); ?>
								</td>
							</tr>
							<tr class="order_item alt-table-row">
								<td colspan="2" class="product-name toptable">
									<strong>
										<?php esc_html_e( 'Status', 'wk-mp-rfq' ); ?>
									</strong>
								</td>
								<td colspan="2" class="product-total toptable">
									<?php echo esc_html( $this->status_title[ $seller_data->status ] ); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php
			}
		}

		/**
		 * List Quotations done by customers
		 *
		 * @param array
		 */
		public function womprfq_seller_quote_answer_template( $quote_data, $seller_data ) {

			$this->womprfq_seller_quote_comment_template( $quote_data );
			$res               = $this->helper->womprfq_get_all_seller_quotation_list( $quote_data->id, '' );
			$check_other_quote = true;
			if ( $res ) {
				foreach ( $res as $value ) {
					if ( $value->status == 3 ) {
						$check_other_quote = false;
						break;
					}
				}
			}
			if ( ( intval( $seller_data->status ) < 3 || intval( $seller_data->status ) === 22 ) && $check_other_quote ) {
				?>
				<form  method="POST" class="wk-seller-quotation-form" id="wk-seller-quotation-form">
					<tr valign="top">
						<th colspan="1">
							<label for="seller-quote-comment"><?php esc_html_e( 'Comment', 'wk-mp-rfq' ); ?><span class="required">*</span></label>
						</th>
						<td colspan="3" class="forminp">
							<textarea rows="6" cols="23" id="seller-quote-comment" class="regular-text" name="seller-quote-comment"></textarea>
							<?php echo wc_help_tip( esc_html__( 'Enter text to add comment to quote.', 'wk-mp-rfq' ), false ); ?>
							<span id="wpmp-rfq-form-image"></span>
							<input type="hidden" id="wpmp-rfq-form-sample-img" name="seller-quote-comment-image">
							<span class="seller-quote-comment-image-add" title="<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>" id="wpmp-rfq-form-upload-button">
								<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<td colspan="4" class="forminp">
							<p>
								<label for="seller-quote-accept-status">
									<input type="checkbox" class="seller-quote-accept-status" id="seller-quote-accept-status" name="seller_quote_accept_status" value="1">
									<?php esc_html_e( 'Click here to approve the quoted price.', 'wk-mp-rfq' ); ?>
								</label>
							</p>
						</td>
					</tr>
					<tr colspan="4" valign="top">
						<td class="forminp">
							<?php wp_nonce_field( 'wc-customer-quote-nonce-action', 'wc-customer-quote-nonce' ); ?>
							<input type="submit" name="update-customer-quotation-submit" value="<?php esc_html_e( 'Send', 'wk-mp-rfq' ); ?>" class="button button-primary" />
						</td>
					</tr>
				</form>
				<?php
			}

			if ( intval( $seller_data->status ) > 3 ) {
				$meta = $this->helper->womprfq_get_quote_meta_info( $seller_data->main_quotation_id );
				if ( isset( $meta['quote_product_id'] ) && ! empty( $meta['quote_product_id'] ) ) {
					$auth  = intval( get_post_field( 'post_author', intval( $meta['quote_product_id'] ) ) );
					$proid = intval( $meta['quote_product_id'] );
				} else {
					$auth = 0;
				}
				if ( isset( $meta['quote_var_id'] ) && intval( $meta['quote_var_id'] ) > 0 ) {
					$proid = intval( $meta['quote_var_id'] );
				}
				if ( $seller_data->seller_id == $auth ) {
					// cjeck for the status
					if ( intval( $seller_data->status ) == 4 ) {
						$rfq_dta = get_post_meta( $proid, 'wkmprfq_quote_data', true );
						if ( isset( $rfq_dta[ get_current_user_id() ] ) ) {
							if ( isset( $rfq_dta[ get_current_user_id() ]['quantity'] ) && isset( $rfq_dta[ get_current_user_id() ]['status'] ) ) {
								$quantity = $rfq_dta[ get_current_user_id() ]['quantity'];

								if ( $quantity > 0 && $rfq_dta[ get_current_user_id() ]['status'] == 0 ) {
									?>
									<tr valign="top">
										<td class="forminp" colspan="2">
											<a class="button" href="
											<?php
											echo esc_url(
												wc_get_cart_url() . '?add-to-cart=' . intval( $proid ) . '&quantity=' . intval( $seller_data->quantity ) .
												'&commission=' . intval( $seller_data->commission )
											);
											?>
											">
												<?php esc_html_e( 'Add Product to cart', 'wk-mp-rfq' ); ?>
											</a>
										</td>
									</tr>
									<?php
								}
							}
						}
					} else {
						if ( $quote_data->status == 2 ) {
							$mes = esc_html__( 'Quotation have been accepted and fulfilled.', 'wk-mp-rfq' );
						} else {
							$mes = esc_html__( 'You accepted this Quotation.', 'wk-mp-rfq' );
						}
						?>
						<tr valign="top">
							<td class="forminp" colspan="2">
								<b>
									<?php esc_html_e( $mes ); ?>
								</b>
							</td>
						</tr>
						<?php
					}
				}
			}
			?>
					</tbody>
				</table>
			</div>
			<?php
		}

		public function womprfq_seller_quote_comment_template( $quote_data ) {
			if ( isset( $quote_data->id ) ) {
				$customer_id  = get_current_user_id();
				$comment_data = $this->helper->womprfq_get_seller_quote_comment_details( $this->q_id );
				if ( ! empty( $comment_data ) ) {
					?>
					<h2>
						<?php esc_html_e( 'Comments', 'wk-mp-rfq' ); ?>
					</h2>
					<div class="wkmp-rfq-sut-edit-quote">
							<table class="form-table wc_status_table widefat">
								<tbody>
								<?php
								foreach ( $comment_data as $comment ) {
									if ( $customer_id == $comment['sender_id'] ) {
										$pos_class       = 'wkmprfq-message-self';
										$pos_arrow_class = 'wkmprfq-message-arrow-self';
									} else {
										$pos_class       = 'wkmprfq-message-other';
										$pos_arrow_class = 'wkmprfq-message-arrow-other';
									}
									?>
								<tr valign="top">
									<td colspan="4" class="forminp" >
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
											} elseif ( $customer_id == $comment['sender_id'] ) {
												esc_html_e( 'You', 'wk-mp-rfq' );
											} else {
												esc_html_e( 'Seller', 'wk-mp-rfq' );
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
					<div class="wkmp-rfq-sut-edit-quote">
							<table class="form-table wc_status_table widefat">
								<tbody>
									<tr valign="top">
										<td class="forminp" colspan="2">
											<?php esc_html_e( 'No Comment Yet.', 'wk-mp-rfq' ); ?>
										</td>
									</tr>
								<?php
				}
			}
		}
	}
}
