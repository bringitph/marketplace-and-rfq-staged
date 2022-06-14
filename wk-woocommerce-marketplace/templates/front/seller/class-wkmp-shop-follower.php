<?php
/**
 * Seller Shop follower class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper;

if ( ! class_exists( 'WKMP_Shop_Follower' ) ) {
	/**
	 * Seller Shop follower.
	 *
	 * Class WKMP_Shop_Follower
	 *
	 * @package WkMarketplace\Templates\Front\Seller
	 */
	class WKMP_Shop_Follower {
		/**
		 * DB Object.
		 *
		 * @var Helper\WKMP_General_Queries $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Shop_Follower constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id ) {
			$this->db_obj    = new Helper\WKMP_General_Queries();
			$this->seller_id = $seller_id;

			$this->wkmp_display_shop_follower();
		}

		/**
		 * Delete follower.
		 *
		 * @param array $customer_ids Customer ids.
		 */
		public function wkmp_delete_followers( $customer_ids ) {
			foreach ( $customer_ids as $customer_id ) {
				$sellers = get_user_meta( $customer_id, 'favourite_seller', true );
				$sellers = $sellers ? explode( ',', $sellers ) : array();
				$sellers = array_map( 'intval', $sellers );
				$key     = array_search( $this->seller_id, $sellers, true );

				if ( false !== $key ) {
					unset( $sellers[ $key ] );
				}

				delete_user_meta( $customer_id, 'favourite_seller' );
				update_user_meta( $customer_id, 'favourite_seller', implode( ',', $sellers ) );
			}

			wc_print_notice( esc_html__( 'Followers has been deleted successfully', 'wk-marketplace' ), 'success' );
		}

		/**
		 * Display shop follower.
		 */
		public function wkmp_display_shop_follower() {
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();//phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( ! empty( $posted_data['wkmp-delete-followers-nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp-delete-followers-nonce'] ), 'wkmp-delete-followers-nonce-action' ) ) {
					$this->wkmp_delete_followers( $posted_data['selected'] );
				}

				if ( ! empty( $posted_data['wkmp-sendmail-followers-nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp-sendmail-followers-nonce'] ), 'wkmp-sendmail-followers-nonce-action' ) ) {

					$subject  = filter_var( $posted_data['subject'], FILTER_SANITIZE_STRING );
					$feedback = filter_var( $posted_data['message'], FILTER_SANITIZE_STRING );

					foreach ( $posted_data['customer_ids'] as $user_id ) {
						$user_info = get_userdata( $user_id );
						$to        = $user_info->user_email;
						do_action( 'wkmp_seller_to_shop_followers', $to, $subject, $feedback );
					}

					wc_add_notice( esc_html__( 'Notification mail has been send successfully', 'wk-marketplace' ), 'success' );
				}
			}

			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
				$customer_id = filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );
				if ( $customer_id > 0 ) {
					$this->wkmp_delete_followers( array( $customer_id ) );
				}
			}

			$customer_ids = $this->db_obj->wkmp_get_seller_followers( $this->seller_id );
			$followers    = array();
			foreach ( $customer_ids as $customer_id ) {
				$user_info = get_userdata( $customer_id );

				$followers[] = array(
					'customer_id' => $customer_id,
					'name'        => get_user_meta( $customer_id, 'first_name', true ) . ' ' . get_user_meta( $customer_id, 'last_name', true ),
					'email'       => $user_info->user_email,
				);
			}

			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'mp_get_wc_account_menu' ); ?>
				<div id="main_container" class="woocommerce-MyAccount-content">
					<div class="wkmp-table-action-wrap">
						<div class="wkmp-action-section right wkmp-text-right">

							<button type="button" class="button wkmp-bulk-delete" data-form_id="#wkmp-followers-list" title="<?php esc_attr_e( 'Delete Follower', 'wk-marketplace' ); ?>">
								<span class="dashicons dashicons-trash"></span></button>&nbsp;&nbsp;

							<button type="button" class="button wkmp-send-notification" id="wkmp-send-notification"><?php esc_html_e( 'Send Notification', 'wk-marketplace' ); ?></button>
						</div>
					</div>

					<form action="" method="post" enctype="multipart/form-data" id="wkmp-followers-list" style="margin-bottom:unset;">
						<div class="wkmp-table-responsive">
							<table class="table table-bordered table-hover">
								<thead>
								<tr>
									<td style="width:1px;"><input type="checkbox" id="wkmp-checked-all"></td>
									<td><?php esc_html_e( 'Customer Name', 'wk-marketplace' ); ?></td>
									<td><?php esc_html_e( 'Customer Email', 'wk-marketplace' ); ?></td>
									<td style="width:17%;"><?php esc_html_e( 'Action', 'wk-marketplace' ); ?></td>
								</tr>
								</thead>
								<tbody>
								<?php if ( count( $followers ) > 0 ) { ?>
									<?php
									foreach ( $followers as $follower ) {
										$follower_name = trim( $follower['name'] );
										?>
										<tr>
											<td><input type="checkbox" name="selected[]" value="<?php echo esc_attr( $follower['customer_id'] ); ?>"/></td>
											<td><?php echo empty( $follower_name ) ? esc_html__( 'NA', 'wk-marketplace' ) : esc_html( $follower_name ); ?></td>
											<td><?php echo esc_html( $follower['email'] ); ?></td>
											<td>
												<a href="?customer_id=<?php echo esc_attr( $follower['customer_id'] ); ?>" class="button" style="padding:12px;"><span class="dashicons dashicons-trash"></span></a>
											</td>
										</tr>
									<?php } ?>
								<?php } else { ?>
									<tr>
										<td colspan="4" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
						</div>
						<?php wp_nonce_field( 'wkmp-delete-followers-nonce-action', 'wkmp-delete-followers-nonce' ); ?>
					</form>

				</div>

				<div id="wkmp-seller-send-notification" class="wkmp-popup-modal">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title"><?php esc_html_e( 'Confirmation', 'wk-marketplace' ); ?></h4>
						</div>
						<div class="modal-body">
							<form action="" method="post" enctype="multipart/form-data" id="wkmp-seller-sendmail-form">
								<div class="form-group">
									<label for="wkmp-subject"><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
									<input class="form-control" type="text" name="subject" placeholder="Subject" id="wkmp-subject" value="">
									<div id="wkmp-subject-error" class="text-danger"></div>
								</div>
								<div class="form-group">
									<label for="wkmp-message"><?php esc_html_e( 'Message', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
									<textarea rows="4" name="message" id="wkmp-message" placeholder="Message"></textarea>
									<div id="wkmp-message-error" class="text-danger"></div>
								</div>
								<?php wp_nonce_field( 'wkmp-sendmail-followers-nonce-action', 'wkmp-sendmail-followers-nonce' ); ?>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="button close-modal"><?php esc_html_e( 'Close', 'wk-marketplace' ); ?></button>
							<button id="wkmp-submit-ask-form" type="submit" form="wkmp-seller-sendmail-form" class="button"><?php esc_html_e( 'Send Mail', 'wk-marketplace' ); ?></button>
						</div>
					</div>
				</div>

			</div>
			<?php
		}
	}
}
