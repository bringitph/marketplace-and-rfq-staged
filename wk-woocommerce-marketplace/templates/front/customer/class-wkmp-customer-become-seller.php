<?php
/**
 * Customer Become Seller Class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Customer;

use WkMarketplace\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Customer_Become_Seller' ) ) {
	/**
	 * Customer Become Seller Class.
	 *
	 * Class WKMP_Customer_Become_Seller
	 *
	 * @package WkMarketplace\Templates\Front\Customer
	 */
	class WKMP_Customer_Become_Seller {
		/**
		 * Current logged in customer id.
		 *
		 * @var object
		 */
		private $customer_id;

		/**
		 * General query helper variable
		 *
		 * @var object
		 */
		private $query_handler;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Customer_Become_Seller constructor.
		 *
		 * @param int $customer_id Customer id.
		 */
		public function __construct( $customer_id = 0 ) {
			$this->customer_id   = $customer_id;
			$this->query_handler = new Helper\WKMP_General_Queries();

			$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? wc_clean( $_SERVER['REQUEST_METHOD'] ) : '';
			if ( 'POST' === $request_method ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $posted_data['wkmp-become-seller-nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp-become-seller-nonce'] ), 'wkmp-become-seller-nonce-action' ) ) {
					// Make request for become a seller.
					$this->wkmp_become_a_seller( $posted_data );
				}
			}

			$this->wkmp_display_customer_become_seller_form();
		}

		/**
		 * Become a seller form post handler.
		 *
		 * @param array $customer_data Customer data.
		 */
		public function wkmp_become_a_seller( $customer_data ) {
			global $wkmarketplace;

			$customer_id = isset( $customer_data['wkmp_customer_id'] ) ? intval( wp_unslash( $customer_data['wkmp_customer_id'] ) ) : get_current_user_id();

			if ( $customer_id > 0 ) {
				$errors     = array();
				$first_name = empty( $customer_data['wkmp_firstname'] ) ? '' : $customer_data['wkmp_firstname'];
				$last_name  = empty( $customer_data['wkmp_lastname'] ) ? '' : $customer_data['wkmp_lastname'];
				$shop_name  = empty( $customer_data['wkmp_shopname'] ) ? '' : $customer_data['wkmp_shopname'];
				$store_url  = empty( $customer_data['wkmp_shopurl'] ) ? '' : $customer_data['wkmp_shopurl'];
				$sel_phone  = empty( $customer_data['wkmp_shopphone'] ) ? '' : $customer_data['wkmp_shopphone'];

				if ( empty( $first_name ) ) {
					$errors[] = esc_html__( 'Please enter your first name.', 'wk-marketplace' );
				}

				if ( empty( $last_name ) ) {
					$errors[] = esc_html__( 'Please enter your last name.', 'wk-marketplace' );
				}

				if ( empty( $shop_name ) || ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $shop_name ) ) {
					$errors[] = esc_html__( 'Enter a valid shop name.', 'wk-marketplace' );
				}

				if ( empty( $store_url ) ) {
					$errors[] = esc_html__( 'Please enter valid shop URL.', 'wk-marketplace' );
				} elseif ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $store_url ) ) {
					$errors[] = esc_html__( 'You can not use special characters in shop url except HYPHEN(-).', 'wk-marketplace' );
				} elseif ( ctype_space( $store_url ) ) {
					$errors[] = esc_html__( 'White space(s) aren\'t allowed in shop url.', 'wk-marketplace' );
				} elseif ( get_user_by( 'slug', $store_url ) ) {
					$errors[] = esc_html__( 'This shop URl already EXISTS, please try different shop url.', 'wk-marketplace' );
				}

				if ( empty( $sel_phone ) || strlen( $sel_phone ) > 18 || ! preg_match( '/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$/', $sel_phone ) ) {
					$errors[] = esc_html__( 'Please enter a valid phone number.', 'wk-marketplace' );
				}

				if ( ! empty( $errors ) ) {
					foreach ( $errors as $error ) {
						wc_print_notice( $error, 'error' );
					}
				} else {
					$customer_user = get_user_by( 'ID', $customer_id );
					$auto_approve  = get_option( '_wkmp_auto_approve_seller', false );

					$user_email = ( $customer_user instanceof \WP_User ) ? $customer_user->user_email : 'NA';
					$user_login = ( $customer_user instanceof \WP_User ) ? $customer_user->user_login : 'NA';

					$data = array(
						'user_id'      => $customer_id,
						'user_email'   => $user_email,
						'user_login'   => $user_login,
						'auto_approve' => $auto_approve,
						'firstname'    => $first_name,
						'lastname'     => $last_name,
						'store_name'   => $shop_name,
						'shop_url'     => $store_url,
						'phone'        => $sel_phone,
					);

					update_user_meta( $customer_id, 'first_name', $first_name );
					update_user_meta( $customer_id, 'billing_first_name', $first_name );
					update_user_meta( $customer_id, 'last_name', $last_name );
					update_user_meta( $customer_id, 'billing_last_name', $last_name );
					update_user_meta( $customer_id, 'shop_name', $shop_name );
					update_user_meta( $customer_id, 'shop_address', $store_url );
					update_user_meta( $customer_id, 'billing_phone', $sel_phone );

					$this->query_handler->wkmp_set_seller_meta( $customer_id );
					$this->query_handler->wkmp_set_seller_default_commission( $customer_id );

					$success_message = esc_html__( 'Your request has been successfully sent to Administrator. You will be notified via Email once it is processed.', 'wk-marketplace' );

					if ( $auto_approve ) {
						$success_message = esc_html__( 'Congratulations!! Your request has been accepted. Now you are a seller on the site.', 'wk-marketplace' );
					}

					update_user_meta( $customer_id, 'wkmp_show_register_notice', $success_message );

					do_action( 'wkmp_customer_become_seller', $data );
					do_action( 'wkmp_customer_become_seller_to_admin', $data );

					$redirect = wc_get_page_permalink( 'myaccount' );

					if ( $auto_approve ) {
						$page_name = $wkmarketplace->seller_page_slug;
						$redirect  = get_permalink( get_page_by_path( $page_name ) ) . get_option( '_wkmp_dashboard_endpoint', 'dashboard' );
					}

					wp_safe_redirect( wp_validate_redirect( apply_filters( 'woocommerce_become_a_seller_redirect', $redirect ), wc_get_page_permalink( 'myaccount' ) ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
					exit();
				}
			}
		}

		/**
		 * Display Become seller form.
		 */
		public function wkmp_display_customer_become_seller_form() {
			global $wkmarketplace;
			$customer_id = get_current_user_id();

			if ( ! $wkmarketplace->wkmp_user_is_customer( $customer_id ) ) {
				wp_die( esc_html__( 'You are not allowed to access this page.', 'wk-marketplace' ) );
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$first_name  = empty( $posted_data['wkmp_firstname'] ) ? '' : $posted_data['wkmp_firstname'];
			$last_name   = empty( $posted_data['wkmp_lastname'] ) ? '' : $posted_data['wkmp_lastname'];
			$shop_name   = empty( $posted_data['wkmp_shopname'] ) ? '' : $posted_data['wkmp_shopname'];
			$store_url   = empty( $posted_data['wkmp_shopurl'] ) ? '' : $posted_data['wkmp_shopurl'];
			$phone       = empty( $posted_data['wkmp_shopphone'] ) ? '' : $posted_data['wkmp_shopphone'];

			if ( ! isset( $posted_data['wkmp_firstname'] ) ) {
				$first_name = get_user_meta( $customer_id, 'first_name', true );
				$first_name = empty( $first_name ) ? get_user_meta( $customer_id, 'billing_first_name', true ) : $first_name;
				$first_name = empty( $first_name ) ? get_user_meta( $customer_id, 'shipping_first_name', true ) : $first_name;

				$last_name = get_user_meta( $customer_id, 'last_name', true );
				$last_name = empty( $last_name ) ? get_user_meta( $customer_id, 'billing_last_name', true ) : $last_name;
				$last_name = empty( $last_name ) ? get_user_meta( $customer_id, 'shipping_last_name', true ) : $last_name;

				$phone = get_user_meta( $customer_id, 'billing_phone', true );
			}
			?>
			<form action="" method="post" enctype="multipart/form-data" id="wkmp-customer-become-seller" style="margin-top:10px;margin-bottom:unset;">
				<div class="wkmp-seller-registration-fields">
					<div class="wkmp-show-fields-if-seller">
						<?php
						if ( isset( $posted_data['wkmp_firstname'] ) || ( empty( $first_name ) || empty( $last_name ) ) ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wkmp-firstname"><?php esc_html_e( 'First Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
								<input type="text" class="input-text form-control" name="wkmp_firstname" value="<?php echo esc_attr( $first_name ); ?>" id="wkmp-firstname"/>
							</p>

							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wkmp-lastname"><?php esc_html_e( 'Last Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
								<input type="text" class="input-text form-control" name="wkmp_lastname" value="<?php echo esc_attr( $last_name ); ?>" id="wkmp-lastname"/>
							</p>
							<?php
						} else {
							?>
							<input type="hidden" name="wkmp_firstname" value="<?php echo esc_attr( $first_name ); ?>">
							<input type="hidden" name="wkmp_lastname" value="<?php echo esc_attr( $last_name ); ?>">
							<?php
						}
						?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="wkmp-shopname"><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
							<input type="text" value="<?php echo esc_attr( $shop_name ); ?>" class="input-text form-control" name="wkmp_shopname" id="wkmp-shopname"/>
						</p>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="wkmp-shopurl" class="pull-left"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?> <span class="required">*</span></label>
							<input type="text" value="<?php echo esc_attr( $store_url ); ?>" class="input-text form-control" name="wkmp_shopurl" id="wkmp-shopurl"/>
							<strong id="wkmp-shop-url-availability"></strong>
						</p>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="wkmp-shopphone"><?php esc_html_e( 'Phone Number', 'wk-marketplace' ); ?><span class="required">*</span></label>
							<input type="text" class="input-text form-control" name="wkmp_shopphone" value="<?php echo empty( $phone ) ? '' : esc_attr( $phone ); ?>" id="wkmp-shopphone"/>
						</p>

						<?php do_action( 'wk_mkt_add_register_field' ); ?>
					</div>
					<?php wp_nonce_field( 'wkmp-become-seller-nonce-action', 'wkmp-become-seller-nonce' ); ?>
					<input type="hidden" name="wkmp_customer_id" value="<?php echo esc_attr( $customer_id ); ?>">
					<input type="submit" name="wkmp-submit-become-seller" class="button button-primary" value="<?php esc_attr_e( 'Made Request', 'wk-marketplace' ); ?>"/>
			</form>
			<?php
		}
	}
}
