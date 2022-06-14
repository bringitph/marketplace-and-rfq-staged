<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Profile;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WKMP_Profile_Info' ) ) {

	/**
	 * Seller products class
	 */
	class WKMP_Profile_Info {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		protected $seller_id;

		/**
		 * Marketplace class object.
		 *
		 * @var object $marketplace Marketplace class object.
		 */
		private $marketplace;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Profile_Info constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			global $wkmarketplace;

			$this->seller_id   = $seller_id;
			$this->marketplace = $wkmarketplace;

			$this->wkmp_seller_profile_info();
		}

		/**
		 * Profile info.
		 */
		public function wkmp_seller_profile_info() {
			global $wkmarketplace;
			$user_id     = get_current_user_id();
			$wkmp_notice = get_user_meta( $user_id, 'wkmp_show_register_notice', true );

			if ( ! empty( $wkmp_notice ) ) {
				wc_print_notice( esc_html( $wkmp_notice ) );
				delete_user_meta( $user_id, 'wkmp_show_register_notice' );
			}

			$is_pending_seller = $wkmarketplace->wkmp_user_is_pending_seller( $user_id );

			if ( $is_pending_seller ) {
				$wkmp_notice = esc_html__( 'Your seller account is under review and will be approved by the admin.', 'wk-marketplace' );
				wc_print_notice( $wkmp_notice, 'notice' );
			}

			if ( $user_id < 1 && 1 === intval( get_option( '_wkmp_separate_seller_registration' ) ) ) {
				wp_enqueue_script( 'wc-password-strength-meter' );
				echo do_shortcode( '[woocommerce_my_account]' );
			}

			if ( $user_id > 0 ) {
				$info        = get_user_by( 'ID', $user_id );
				$meta        = get_user_meta( $user_id, '', true );
				$seller_info = $info->data;

				$seller_info->caps  = isset( $info->caps ) ? $info->caps : array();
				$seller_info->roles = isset( $info->roles ) ? $info->roles : array();

				foreach ( $meta as $key => $value ) {
					$seller_info->$key = $value[0];
				}

				if ( $seller_info ) {
					$avatar_image = WKMP_PLUGIN_URL . 'assets/images/generic-male.png';
					if ( isset( $seller_info->_thumbnail_id_avatar ) && $seller_info->_thumbnail_id_avatar ) {
						$avatar_image = wp_get_attachment_image_src( $seller_info->_thumbnail_id_avatar )[0];
					}
					$seller_name  = isset( $seller_info->first_name ) ? $seller_info->first_name : '';
					$seller_name .= isset( $seller_info->last_name ) ? ' ' . $seller_info->last_name : '';
					?>
					<div class="woocommerce-account woocommerce">
						<?php do_action( 'mp_get_wc_account_menu' ); ?>
						<div id="main_container" class="woocommerce-MyAccount-content">
							<div class="wkmp_seller_profile_info">

								<div class="wkmp_thumb_image">
									<img src="<?php echo esc_url( $avatar_image ); ?>"/>
								</div>

								<div class="wkmp_profile_info">

									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'Username', 'wk-marketplace' ); ?></label> :
										<span> <?php echo isset( $seller_info->user_login ) ? esc_html( $seller_info->user_login ) : ''; ?> </span>
									</div>

									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'E-Mail', 'wk-marketplace' ); ?></label> :
										<span> <?php echo isset( $seller_info->user_email ) ? esc_html( $seller_info->user_email ) : ''; ?> </span>
									</div>

									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'Name', 'wk-marketplace' ); ?> </label> :
										<span> <?php echo esc_html( $seller_name ); ?> </span>
									</div>

									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'Display Name', 'wk-marketplace' ); ?> </label> :
										<span>  <?php echo isset( $seller_info->display_name ) ? esc_html( $seller_info->display_name ) : ''; ?> </span>
									</div>

									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?> </label> :
										<span>  <?php echo isset( $seller_info->shop_name ) ? esc_html( $seller_info->shop_name ) : ''; ?> </span>
									</div>

									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'Shop address', 'wk-marketplace' ); ?> </label> :
										<span>  <?php echo isset( $seller_info->shop_address ) ? esc_html( $seller_info->shop_address ) : ''; ?> </span>
									</div>

									<div class="wkmp_profile_btn">
										<a href="<?php echo esc_url( get_permalink() . get_option( '_wkmp_profile_endpoint', 'profile' ) . '/edit' ); ?>" title="<?php esc_attr_e( 'Edit Profile', 'wk-marketplace' ); ?>" class="button"><?php esc_html_e( 'Edit', 'wk-marketplace' ); ?></a>
										<?php
										if ( $this->marketplace->wkmp_user_is_customer( $user_id ) ) {
											?>
											<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'become-mp-seller' ); ?>" title="<?php esc_attr_e( 'Become a Seller', 'wk-marketplace' ); ?>" class="wkmp-become-seller button"><?php esc_html_e( 'Become a Seller', 'wk-marketplace' ); ?></a>
											<?php
										}
										?>
									</div>

								</div>
							</div>

						</div><!-- main_container -->
					</div><!-- woocommerce-account -->
					<?php
				}
			}
			if ( ! $this->seller_id && ! get_option( '_wkmp_separate_seller_registration' ) ) {
				?>
				<h3><?php esc_html_e( 'Want to sell your own products...!', 'wk-marketplace' ); ?></h3><br/>
				<h3>
					Oops! Please login first.<br/>
					<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>"> <?php esc_html_e( 'Login Here', 'wk-marketplace' ); ?> </a> <?php //esc_html_e( 'OR', 'wk-marketplace' ); ?>
					<!--<a href="<?php //echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>">  <?php //esc_html_e( 'Register', 'wk-marketplace' ); ?></a>-->
				</h3>
				<?php
			}
		}
	}
}
