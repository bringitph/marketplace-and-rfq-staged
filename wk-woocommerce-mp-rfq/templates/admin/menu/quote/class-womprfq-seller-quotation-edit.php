<?php
/**
 * Load ajax functions.
 *
 * @author     Webkul.
 * @implements Assets_Interface
 */

namespace wooMarketplaceRFQ\Templates\Admin\Menu\Quote;

use wooMarketplaceRFQ\Helper;
use wooMarketplaceRFQ\Templates\Admin;
use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Seller_Quotation_Edit' ) ) {
	/**
	 * Class menu template.
	 */
	class Womprfq_Seller_Quotation_Edit {

		protected $seller_quote_id;

		protected $obj;

		protected $status_title;
		/**
		 * Class constructor.
		 *
		 * @param int $q_id seller quote id
		 */
		public function __construct( $q_id ) {
			$this->seller_quote_id = $q_id;
			$this->status_title    = array(
				0 => esc_html__( 'Open', 'wk-mp-rfq' ),
				1 => esc_html__( 'Pending', 'wk-mp-rfq' ),
				2 => esc_html__( 'Answered', 'wk-mp-rfq' ),
				3 => esc_html__( 'Resolved', 'wk-mp-rfq' ),
				4 => esc_html__( 'Closed', 'wk-mp-rfq' ),
			);
			$this->obj             = new Helper\Womprfq_Quote_Handler();
			$this->womprfq_get_edit_seller_quote_template();
		}

		/**
		 * Displays seller quote detail tab.
		 *
		 * @return void.
		 */
		public function womprfq_get_edit_seller_quote_template() {
			$wk_message = array();
			if ( $this->seller_quote_id ) {
				$q_price    = '';
				$q_quantity = '';
				$q_status   = '';

				if ( isset( $_POST['update-seller-quotation-submit'] ) ) {

					if ( ! empty( $postdta['wc-seller-quote-nonce'] ) && wp_verify_nonce( wp_unslash( $postdta['wc-seller-quote-nonce'] ), 'wc-seller-quote-nonce-action' ) ) {

						if ( isset( $postdta['seller-quote-quantity'] ) && ! empty( $postdta['seller-quote-quantity'] ) ) {
							$q_quantity = $postdta['seller-quote-quantity'];
						}
						if ( isset( $postdta['seller-quote-price'] ) && ! empty( $postdta['seller-quote-price'] ) ) {
							$q_price = $postdta['seller-quote-price'];
						}
						if ( isset( $postdta['seller-quote-commission'] ) && ! empty( $postdta['seller-quote-commission'] ) ) {
							$q_commission = $postdta['seller-quote-commission'];
						}

						if ( ! empty( $q_quantity ) && ! empty( $q_price ) && ! empty( $q_commission ) ) {
							$is_comment = false;
							if ( isset( $postdta['seller-quote-comment'] ) && ! empty( $postdta['seller-quote-comment'] ) ) {
								$q_comment  = wc_clean( wp_unslash( $postdta['seller-quote-comment'] ) );
								$is_comment = true;
							}
							if ( isset( $postdta['seller-quote-comment-image'] ) && ! empty( $postdta['seller-quote-comment-image'] ) ) {
								$q_comnt_img = $postdta['seller-quote-comment-image'];
							} else {
								$q_comnt_img = null;
							}

							$info = array(
								'id'         => $this->seller_quote_id,
								'price'      => $q_price,
								'commission' => $q_commission,
								'quantity'   => $q_quantity,
								'status'     => 1,
							);

							$save_res = $this->obj->womprfq_update_seller_quotation( $info );

							if ( $save_res['status'] || $is_comment ) {
								if ( $is_comment ) {
									$comment_info = array(
										'seller_quotation_id' => $this->seller_quote_id,
										'comment_text' => stripslashes( $q_comment ),
										'sender_id'    => 0,
										'image'        => $q_comnt_img,
									);
									$this->obj->womprfq_update_seller_quotation_comment( $comment_info );
								}
								$wk_message[] = array(
									'status' => 'updated',
									'msg'    => esc_html__( 'Seller Quotation Updated successfully.', 'wk-mp-rfq' ),
								);
								$smes         = array(
									esc_html__( 'Quotation status has been updated by Admin', 'wk-mp-rfq' ) . ' ( #' . intval( $this->seller_quote_id ) . ' ).',
								);
								$sel_q_data   = $this->obj->womprfq_get_seller_quotation_details( $this->seller_quote_id );
								if ( $sel_q_data ) {
									$main_dat = $this->obj->womprfq_get_main_quotation_by_id( $sel_q_data->main_quotation_id );
									$smes     = $this->obj->womprfq_get_mail_quotation_detail( $sel_q_data->main_quotation_id, $smes );
									if ( $main_dat ) {
										$user = get_user_by( 'ID', $main_dat->customer_id );
										if ( $user ) {
											$sdata = array(
												'msg'     => $smes,
												'sendto'  => $user->user_email,
												'heading' => esc_html__( 'Quotation Updated', 'wk-mp-rfq' ),
											);
											do_action( 'womprfq_quotation', $sdata );
										}
									}
								}
							} else {
								$wk_message = $save_res['msg'];
							}
						} else {
							$wk_message[] = array(
								'status' => 'error',
								'msg'    => esc_html__( 'Please Enter Valid Details.', 'wk-mp-rfq' ),
							);
						}
					}
				}

				$sel_quoatation_detail = $this->obj->womprfq_get_seller_quotation_details( $this->seller_quote_id );
				$pic_url               = wc_placeholder_img_src();

				if ( $sel_quoatation_detail ) {
					$seller_id  = $sel_quoatation_detail->seller_id;
					$q_price    = $sel_quoatation_detail->price;
					$q_quantity = $sel_quoatation_detail->quantity;
					$q_status   = $sel_quoatation_detail->status;
				} else {
					wp_safe_redirect( admin_url( '?page=wc-main-quote' ) );
					wp_die();
				}

				if ( ! empty( $wk_message ) ) {
					foreach ( $wk_message as $wk_msg ) {
						?>
							<div id="message" class="<?php esc_attr_e( $wk_msg['status'] ); ?> inline"><p><strong><?php esc_html_e( $wk_msg['msg'] ); ?></strong></p></div>
						<?php
					}
				}

				$main_quotation_info = $this->obj->womprfq_get_main_quotation_by_id( intval( $sel_quoatation_detail->main_quotation_id ) );
				?>
				<div class="wrap woocommerce" id="wk-sup-quotation-edit">
					<h1 class="wp-heading-inline">
						<?php esc_html_e( 'Edit Seller Quotation', 'wk-mp-rfq' ); ?>
					</h1>
					<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=wk-mp-rfq&perform=seller-quote&qid=' . intval( $sel_quoatation_detail->main_quotation_id ) ) ); ?>">
						<?php esc_html_e( 'Back', 'wk-mp-rfq' ); ?>
					</a>
					<?php
					if ( $main_quotation_info->variation_id ) {
						$product = wc_get_product( $main_quotation_info->variation_id );
					} else {
						$product = wc_get_product( $main_quotation_info->product_id );
					}

					$comment_data = $this->obj->womprfq_get_seller_quote_comment_details( $this->seller_quote_id );

					?>
					<br>
					<div>
					<div>
						<?php
							$admin_template = new Admin\Womprfq_Admin_Menu_Template();
							$admin_template->womprfq_get_main_quote_template( $main_quotation_info );
						?>
					</div>
					<form  method="POST" class="wk-seller-quotation-form" id="wk-seller-quotation-form">
					<div class="wk-seller_tab">
					<table class="form-table wc_status_table widefat">
						<thead>
							<tr>
								<th scope="row" class="titledesc" colspan="2">
									<h2>
										<?php esc_html_e( 'SELLER QUOTATION', 'wk-mp-rfq' ); ?>
									</h2>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<b>
										<label for="seller-quote-price"><?php esc_html_e( 'Prices/Product', 'wk-mp-rfq' ); ?></label>
									</b>
								</th>
								<td class="forminp">
									<?php
									if ( $sel_quoatation_detail->status < 3 ) {
										?>
										<span class="required">*</span>
										<input type="text" autoComplete="Off" required="required" class="regular-text" name="seller-quote-price" value="<?php echo esc_html( $q_price ); ?>"  id="seller-quote-price" />
										<?php
										echo wc_help_tip( esc_html__( 'Seller Quoted Price.', 'wk-mp-rfq' ), false );
									} else {
										?>
										<p>
										<?php
											echo wc_price( $q_price );
										?>
										</p>
										<?php
									}
									?>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row" class="titledesc">
									<b>
										<label for="seller-quote-quantity"><?php esc_html_e( 'Quantity', 'wk-mp-rfq' ); ?></label>
									</b>
								</th>
								<td class="forminp">
									<?php
									if ( $sel_quoatation_detail->status < 3 ) {
										?>
									<span class="required">*</span>
									<input type="number" autoComplete="Off" required="required" class="regular-text" name="seller-quote-quantity" value="<?php echo esc_html( $q_quantity ); ?>" id="seller-quote-quantity" />
										<?php
										echo wc_help_tip( esc_html__( 'Seller Quoted Quantity.', 'wk-mp-rfq' ), false );
									} else {
										?>
										<p>
										<?php
											echo esc_html( $q_quantity );
										?>
										</p>
										<?php
									}
									?>
								</td>
							</tr>
							<?php
							if ( $sel_quoatation_detail->status < 3 ) {
								?>
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label for="seller-quote-comment"><?php esc_html_e( 'Comment', 'wk-mp-rfq' ); ?></label></th>
									<td class="forminp">
										<span class="required">*</span>
										<textarea rows="6" cols="23" id="seller-quote-comment" class="regular-text" name="seller-quote-comment"></textarea>
										<?php echo wc_help_tip( esc_html__( 'Enter text to add comment to quote.', 'wk-mp-rfq' ), false ); ?>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row" class="titledesc">
									</th>
									<td class="forminp">
										<span id="wk-mp-rfq-image-container"></span>
										<input type="hidden" id="seller-quote-comment-image" name="seller-quote-comment-image">
										<span class="seller-quote-comment-image-add" title="<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>" id="seller-quote-comment-image-add">
											<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>
										</span>
									</td>
								</tr>
								<tr  valign="top">
									<td class="forminp">
										<?php wp_nonce_field( 'wc-seller-quote-nonce-action', 'wc-seller-quote-nonce' ); ?>
										<input type="submit" name="update-seller-quotation-submit" value="<?php esc_html_e( 'Update Quotation', 'wk-mp-rfq' ); ?>" class="button button-primary" />
									</td>
								</tr>
								<?php
							}
							if ( $sel_quoatation_detail->status == 3 || $sel_quoatation_detail->status == 4 ) {
								?>
								<tr valign="top">
									<th scope="row" class="titledesc">
										<b>
											<label for="seller-quote-status"><?php esc_html_e( 'Quote Status', 'wk-mp-rfq' ); ?></label>
										</b>
									</th>
									<td class="forminp">
										<?php
											echo esc_html( $this->status_title[ intval( $sel_quoatation_detail->status ) ] );
										?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					</div>
					<div class="wk-seller_tab_comment">
					<table class="wc_status_table widefat">
						<thead>
							<tr>
								<th scope="row" class="titledesc">
									<h2>
										<?php esc_html_e( 'COMMENTS', 'wk-mp-rfq' ); ?>
									</h2>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $comment_data ) ) {
								foreach ( $comment_data as $comment ) {
									if ( 0 == $comment['sender_id'] ) {
										$pos_class       = 'wkmprfq-message-self';
										$pos_arrow_class = 'wkmprfq-message-arrow-self';
									} else {
										$pos_class       = 'wkmprfq-message-other';
										$pos_arrow_class = 'wkmprfq-message-arrow-other';
									}
									?>
									<tr valign="top">
										<td class="forminp" >
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
													esc_html_e( 'Seller', 'wk-mp-rfq' );
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
								<td class="forminp">
									<p>
									<?php esc_html_e( 'No comment present yet!', 'wk-mp-rfq' ); ?>
									</p>
								</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					</div>
					</form>

				</div>
				</div>
				<?php
			} else {
				wp_safe_redirect( admin_url( '?page=wk-mp-rfq' ) );
				wp_die();
			}
		}
	}
}
