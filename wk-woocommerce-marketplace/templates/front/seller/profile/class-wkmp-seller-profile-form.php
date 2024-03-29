<?php
/**
 * Seller profile HTML form
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Profile;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WKMP_Seller_Profile_Form' ) ) {
	/**
	 * Seller Profile Edit Form Class.
	 *
	 * Class WKMP_Seller_Profile_Form
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Profile
	 */
	class WKMP_Seller_Profile_Form {
		/**
		 * Marketplace class object.
		 *
		 * @var object $marketplace Marketplace class object.
		 */
		private $marketplace;

		/**
		 * WKMP_Seller_Profile_Form constructor.
		 */
		public function __construct() {
			global $wkmarketplace;
			$this->marketplace = $wkmarketplace;
		}

		/**
		 * * Profile Edit form.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $errors Errors.
		 * @param array $posted_data Posted data.
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_edit_form( $seller_id, $errors, $posted_data ) {
			$seller_info = $this->marketplace->get_parsed_seller_info( $seller_id, $posted_data );
			if ( ! empty( $errors ) ) {
				wc_add_notice( esc_html__( 'Warning Please check the form carefully for the errors.', 'wk-marketplace' ), 'error' );
			}
			?>

			<div class="woocommerce-account woocommerce">

				<?php do_action( 'mp_get_wc_account_menu' ); ?>
				<div id="main_container" class="woocommerce-MyAccount-content">

					<div class="wkmp-table-action-wrap">
						<div class="wkmp-action-section right wkmp-text-right">
							<button type="submit" class="button" form="wkmp-seller-profile"><?php esc_html_e( 'Save', 'wk-marketplace' ); ?></button>&nbsp;&nbsp;
							<a href="<?php echo esc_url( get_permalink() . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . $seller_info['wkmp_shop_url'] ); ?>" class="button" title="<?php esc_attr_e( 'View Profile', 'wk-marketplace' ); ?>" target="_blank"> <?php esc_html_e( 'View Profile', 'wk-marketplace' ); ?></a>
						</div>
					</div>

					<ul class="wkmp_nav_tabs">
						<li><a data-id="#wkmp-general-tab" class="active"><?php esc_html_e( 'General', 'wk-marketplace' ); ?></a></li>
						<li><a data-id="#wkmp-shop-tab"><?php esc_html_e( 'Shop', 'wk-marketplace' ); ?></a></li>
						<li><a data-id="#wkmp-image-tab"><?php esc_html_e( 'Image', 'wk-marketplace' ); ?></a></li>
						<li><a data-id="#wkmp-social-tab"><?php esc_html_e( 'Social Profile', 'wk-marketplace' ); ?></a></li>
					</ul>

					<form action="" method="post" enctype="multipart/form-data" id="wkmp-seller-profile">
						<div class="wkmp_tab_content">

							<div id="wkmp-general-tab" class="wkmp_tab_pane">

							<!-- JS edit. Add country and city drop down filter and country preference. Step 21 -->
								<style type="text/css">
									.w-50{
										width: 50%;
										float: left;
									}
									.subscribed_country{
										height:100px;
									}
									.subscribed_country select{
										border: 1px solid #000;
									    padding-top: 10px !important;
									    height: 44px;
									    margin-bottom: 20px;
									    color: #000;
									    font-size: 12px;
									    font-weight: 400;
									    background: #fff;
									    max-width: 100%;
									    outline: 0;
									    font-family: inherit;
									    border-radius: 6px;
										font-size: 16px;
										
									}
									@media screen and (min-width:480px) and (max-width:800px) {
										width: 100%;
										float: none;
									}
								</style>
								<div class="form-group w-50">
									<label><?php esc_html_e( 'Subscribed E-Mail', 'wk-marketplace' ); ?></label>
									<p>
										<input type="checkbox" id="wk-seller-subscribe-email" name="wkmp_subscribe_email" value="yes" <?php echo ( 'yes' === $seller_info['wkmp_subscribe_email'] ) ? 'checked' : ''; ?>> <label for="wk-seller-banner-status"><?php esc_html_e( 'Received email on customer order request', 'wk-marketplace' ); ?> </label>
									</p>
								</div>
								<div class="form-group w-50 subscribed_country" id="subscribed_country" <?php echo ( 'yes' !== $seller_info['wkmp_subscribe_email'] ) ? 'style="display: none;"' : ''; ?> >
									<label for="subscribed-country"><?php esc_html_e( 'Country', 'wk-marketplace' ); ?></label>
									<select name="wkmp_subscribed_country" id="subscribed-country" class="form-control" oninvalid="this.setCustomValidity('You need to select the country in the list.')" oninput="this.setCustomValidity('')" <?php echo ( 'yes' == $seller_info['wkmp_subscribe_email'] ) ? 'required' : ''; ?> >
										<option value=""><?php esc_html_e( 'Select Country', 'wk-marketplace' ); ?></option>
										<option value="all" <?php if($seller_info['wkmp_subscribed_country'] == "all" ){ echo "selected"; }  ?> ><?php esc_html_e( 'All', 'wk-marketplace' ); ?></option>
										<?php
										$countries_obj = new \WC_Countries();
										$countries     = $countries_obj->__get( 'countries' );
										foreach ( $countries as $key => $country ) {
											?>
											<?php if ( $key === $seller_info['wkmp_subscribed_country'] ) { ?>
												<option value="<?php echo esc_attr( $key ); ?>" selected><?php echo esc_html( $country ); ?></option>
											<?php } else { ?>
												<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></option>
											<?php } ?>
										<?php } ?>
									</select>
								</div>
								<!-- gemini -->
								<div class="form-group" style="clear:both">
							
									<label for="username"><?php esc_html_e( 'Username', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_username" id="username" value="<?php echo esc_attr( $seller_info['wkmp_username'] ); ?>" readonly>
								</div>

								<div class="form-group">
									<label for="first-name"><?php esc_html_e( 'First Name', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_first_name" id="first-name" value="<?php echo esc_attr( $seller_info['wkmp_first_name'] ); ?>">
									<div class="text-danger"><?php echo isset( $errors['wkmp_first_name'] ) ? esc_html( $errors['wkmp_first_name'] ) : ''; ?></div>
								</div>

								<div class="form-group">
									<label for="last-name"><?php esc_html_e( 'Last Name', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_last_name" id="last-name" value="<?php echo esc_attr( $seller_info['wkmp_last_name'] ); ?>">
									<div class="text-danger"><?php echo isset( $errors['wkmp_last_name'] ) ? esc_html( $errors['wkmp_last_name'] ) : ''; ?></div>
								</div>

								<div class="form-group">
									<label for="user_email"><?php esc_html_e( 'E-Mail', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_seller_email" id="user_email" value="<?php echo esc_attr( $seller_info['wkmp_seller_email'] ); ?>">
									<div class="text-danger"><?php echo isset( $errors['wkmp_seller_email'] ) ? esc_html( $errors['wkmp_seller_email'] ) : ''; ?></div>
								</div>

							</div><!-- wkmp-general-tab end here -->

							<div id="wkmp-shop-tab" class="wkmp_tab_pane">

								<div class="form-group">
									<label for="wkmp-shop-name"><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_shop_name" id="wkmp_shop_name" value="<?php echo esc_attr( $seller_info['wkmp_shop_name'] ); ?>">
									<div class="text-danger"><?php echo isset( $errors['wkmp_shop_name'] ) ? esc_html( $errors['wkmp_shop_name'] ) : ''; ?></div>
								</div>

								<div class="form-group">
									<label for="wkmp-shop-address"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_shop_url" id="wkmp_shop_address" value="<?php echo esc_attr( $seller_info['wkmp_shop_url'] ); ?>" readonly>
								</div>

								<div class="form-group">
									<label for="phone-number"><?php esc_html_e( 'Phone Number', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_shop_phone" id="phone-number" value="<?php echo esc_attr( $seller_info['wkmp_shop_phone'] ); ?>">
									<div class="text-danger"><?php echo isset( $errors['wkmp_shop_phone'] ) ? esc_html( $errors['wkmp_shop_phone'] ) : ''; ?></div>
								</div>

								<div class="form-group">
									<label for="mp_seller_payment_details"><?php esc_html_e( 'Payment Information', 'wk-marketplace' ); ?></label>
									<textarea placeholder="<?php esc_attr_e( 'Enter payment information like bank details or Paypal URL to receive payment from the admin after deducting commission.', 'wk-marketplace' ); ?>" rows="4" id="mp_seller_payment_details" name="wkmp_payment_details"><?php echo esc_html( $seller_info['wkmp_payment_details'] ); ?></textarea>
									<?php do_action( 'marketplace_payment_gateway' ); ?>
								</div>

								<div class="hidden">
									<label for="billing-country"><?php esc_html_e( 'Country', 'wk-marketplace' ); ?></label>
									<select name="wkmp_shop_country" id="billing-country" class="form-control">
										<option value=""><?php esc_html_e( 'Select Country', 'wk-marketplace' ); ?></option>
										<?php
										$countries_obj = new \WC_Countries();
										$countries     = $countries_obj->__get( 'countries' );
										foreach ( $countries as $key => $country ) {
											?>
											<?php if ( $key === $seller_info['wkmp_shop_country'] ) { ?>
												<option value="<?php echo esc_attr( $key ); ?>" selected><?php echo esc_html( $country ); ?></option>
											<?php } else { ?>
												<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></option>
											<?php } ?>
										<?php } ?>
									</select>
								</div>

								<div class="hidden">
									<label for="address-1"><?php esc_html_e( 'Address Line 1', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_shop_address_1" id="address-1" value="<?php echo esc_attr( $seller_info['wkmp_shop_address_1'] ); ?>">
								</div>

								<div class="hidden">
									<label for="address-2"><?php esc_html_e( 'Address Line 2', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_shop_address_2" id="address-2" value="<?php echo esc_attr( $seller_info['wkmp_shop_address_2'] ); ?>">
								</div>

								<div class="hidden">
									<label for="billing-city"><?php esc_html_e( 'City', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_shop_city" id="billing-city" value="<?php echo esc_attr( $seller_info['wkmp_shop_city'] ); ?>">
								</div>

								<div class="hidden">
									<label for="billing-state"><?php esc_html_e( 'State', 'wk-marketplace' ); ?></label>
									<?php
									$get_states = array();
									if ( ! empty( $seller_info['wkmp_shop_country'] ) ) {
										$get_states = $countries_obj->get_states( $seller_info['wkmp_shop_country'] );
									}

									if ( ! empty( $get_states ) || ! empty( $seller_info['wkmp_shop_country'] ) ) {
										?>
										<select name="wkmp_shop_state" id="billing-state" class="form-control">
											<option value=""><?php esc_html_e( 'Select state', 'wk-marketplace' ); ?></option>
											<?php foreach ( is_array( $get_states ) ? $get_states : array() as $key => $state ) { ?>
													<option value="<?php echo esc_attr( $key ); ?>" <?php echo selected( $key, $seller_info['wkmp_shop_state'], false ); ?>><?php echo esc_html( $state ); ?></option>
											<?php } ?>
										</select>
									<?php } else { ?>
										<input id="billing-state" type="text" name="billing_state" class="form-control" value="<?php echo esc_attr( $seller_info['wkmp_shop_state'] ); ?>">
									<?php } ?>
								</div>

								<div class="hidden">
									<label for="billing-postal-code"><?php esc_html_e( 'Postal Code', 'wk-marketplace' ); ?></label>
									<input class="form-control" type="text" name="wkmp_shop_postcode" id="billing-postal-code" value="<?php echo esc_attr( $seller_info['wkmp_shop_postcode'] ); ?>">
									<div class="text-danger"><?php echo isset( $errors['wkmp_shop_postcode'] ) ? esc_html( $errors['wkmp_shop_postcode'] ) : ''; ?></div>
								</div>
	
								<div class="form-group">
									<label for="about-shop"><?php esc_html_e( 'About Shop', 'wk-marketplace' ); ?></label>
									<!-- Jesse: Add max character for About text -->
									<textarea maxlength="200" placeholder="<?php esc_attr_e( 'Details about your shop.', 'wk-marketplace' ); ?>" rows="4" id="about-shop" name="wkmp_about_shop"><?php echo  $seller_info['wkmp_about_shop']; ?></textarea>
								</div>

							</div><!-- wkmp-shop-tab end here -->

							<div id="wkmp-image-tab" class="wkmp_tab_pane">

								<div class="wkmp_avatar_logo_section">

									<div class="wkmp_profile_img">
										<div class="text-danger"><?php echo isset( $errors['wkmp_avatar_file'] ) ? esc_attr( $errors['wkmp_avatar_file'] ) : ''; ?></div>
										<label for="seller_avatar_file"><?php esc_html_e( 'User Image', 'wk-marketplace' ); ?></label>

										<div id="wkmp-thumb-image" class="wkmp-img-thumbnail" style="display:table;">
											<img class="wkmp-img-thumbnail" src="<?php echo empty( $seller_info['wkmp_avatar_file'] ) ? esc_url( $seller_info['wkmp_generic_avatar'] ) : esc_url( $seller_info['wkmp_avatar_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_avatar'] ); ?>"/>
											<input type="hidden" id="thumbnail_id_avatar" name="wkmp_avatar_id" value="<?php echo esc_attr( $seller_info['wkmp_avatar_id'] ); ?>"/>
											<input type="file" name="wkmp_avatar_file" class="wkmp_hide" id="seller_avatar_file"/>
										</div>


										<div class="wkmp-button" style="font-size:13px;margin-top:2px;">
											<button type="button" class="button" id="wkmp-upload-profile-image"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
											<button type="button" class="button wkmp-remove-profile-image" style="color:#fff;background-color:#da2020"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
										</div>
									</div>

									<div class="wkmp_profile_logo">
										<div class="text-danger"><?php echo isset( $errors['wkmp_logo_file'] ) ? esc_html( $errors['wkmp_logo_file'] ) : ''; ?></div>
										<label for="seller_shop_logo_file"><?php esc_html_e( 'Shop Logo', 'wk-marketplace' ); ?></label>

										<div id="wkmp-thumb-image" class="wkmp-img-thumbnail" style="display:table;">
											<img class="wkmp-img-thumbnail" src="<?php echo empty( $seller_info['wkmp_logo_file'] ) ? esc_url( $seller_info['wkmp_generic_logo'] ) : esc_url( $seller_info['wkmp_logo_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_logo'] ); ?>"/>
											<input type="hidden" id="thumbnail_id_company_logo" name="wkmp_logo_id" value="<?php echo esc_attr( $seller_info['wkmp_logo_id'] ); ?>"/>
											<input type="file" name="wkmp_logo_file" class="wkmp_hide" id="seller_shop_logo_file"/>
										</div>
										<br/> <!--Jesse edit: Add breakline between shop logo and Upload button-->

										<div class="wkmp-button" style="font-size:13px;margin-top:2px;">
											<button type="button" class="button" id="wkmp-upload-shop-logo"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
											<button type="button" class="button wkmp-remove-shop-logo" style="color:#fff;background-color:#da2020"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
										<!--Jesse edit: Add reminder to Save after uploading photo -->
                                		<br/><br/><font size="-1" style="color:#8f8d8d">Made changes? Don't forget to hit SAVE above ^</font>
										</div>
									</div>

								</div>

								<div class="hidden">
									<label><b><?php esc_html_e( 'Banner Image', 'wk-marketplace' ); ?></b></label>
									<p>
										<input type="checkbox" id="wk-seller-banner-status" name="wkmp_display_banner" value="yes" <?php echo ( 'yes' === $seller_info['wkmp_display_banner'] ) ? 'checked' : ''; ?>><label for="wk-seller-banner-status"><?php esc_html_e( 'Show banner on seller page', 'wk-marketplace' ); ?> </label>
									</p>

									<div class="wkmp_shop_banner">
										<div class="text-danger"><?php echo empty( $errors['wkmp_banner_file'] ) ? '' : esc_html( $errors['wkmp_banner_file'] ); ?></div>

										<div class="wk_banner_img" id="wk_seller_banner">
											<input type="file" class="wkmp_hide" name="wkmp_banner_file" id="wk_mp_shop_banner"/>
											<input type="hidden" id="thumbnail_id_shop_banner" name="wkmp_banner_id" value="<?php echo esc_attr( $seller_info['wkmp_banner_id'] ); ?>"/>
											<img src="<?php echo empty( $seller_info['wkmp_banner_file'] ) ? esc_url( $seller_info['wkmp_generic_banner'] ) : esc_url( $seller_info['wkmp_banner_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_banner'] ); ?>"/>
										</div>

										<div class="wkmp-shop-banner-buttons">
											<button type="button" class="button wkmp_upload_banner" id="wkmp-upload-seller-banner"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
											<button type="button" class="button wkmp_remove_banner" id="wkmp-remove-seller-banner"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
										</div>
									</div>
								</div>

							</div>

							<div id="wkmp-social-tab" class="wkmp_tab_pane">
								<div class="form-group">
									<label for="social-facebok"><?php esc_html_e( 'Facebook Profile ID', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
									<input class="form-control" type="text" name="wkmp_settings[social][fb]" id="social-facebok" value="<?php echo esc_attr( $seller_info['wkmp_facebook'] ); ?>" placeholder="https://">
								</div>

								<div class="form-group">
									<label for="social-instagram"><?php esc_html_e( 'Instagram Profile ID', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
									<input class="form-control" type="text" name="wkmp_settings[social][insta]" id="social-facebok" value="<?php echo esc_attr( $seller_info['wkmp_instagram'] ); ?>" placeholder="https://">
								</div>

								<div class="form-group">
									<label for="social-twitter"><?php esc_html_e( 'Twitter Profile ID ', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
									<input class="form-control" type="text" name="wkmp_settings[social][twitter]" id="social-twitter" value="<?php echo esc_attr( $seller_info['wkmp_twitter'] ); ?>" placeholder="https://">
								</div>

								<div class="form-group">
									<label for="social-linkedin"><?php esc_html_e( 'Linkedin Profile ID  ', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
									<input class="form-control" type="text" name="wkmp_settings[social][linkedin]" id="social-linkedin" value="<?php echo esc_attr( $seller_info['wkmp_linkedin'] ); ?>" placeholder="https://">
								</div>

								<div class="form-group">
									<label for="social-youtube"><?php esc_html_e( 'Youtube Profile', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
									<input class="form-control" type="text" name="wkmp_settings[social][youtube]" id="social-youtube" value="<?php echo esc_attr( $seller_info['wkmp_youtube'] ); ?>" placeholder="https://">
								</div>

								<?php do_action( 'mp_manage_seller_details', $seller_info ); ?>

							</div>
						</div><!-- wkmp_tab_content end here -->
						<?php wp_nonce_field( 'wkmp-user-nonce-action', 'wkmp-user-nonce' ); ?>
					</form>
					

				</div><!-- Main container end here -->
			</div><!-- Woocommerce-account end here -->
			<?php
		}
	}
}

