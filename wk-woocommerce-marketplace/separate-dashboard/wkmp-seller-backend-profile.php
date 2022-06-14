<?php
/**
 * Backend Profile.
 *
 * @package @package WkMarketplace\Includes\Shipping
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="woocommerce-account woocommerce">

	<div class="wk_profileupdate woocommerce-MyAccount-content" style="width: 60%;">

		<div class="wkmp_profile_preview_link">
			<h1 style="float:left;"><?php esc_html_e( 'My Profile', 'wk-marketplace' ); ?> </h1></br>
			<a href="<?php echo esc_url( site_url() . '/' . $page_name . '/' . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . $seller_info['wkmp_shop_url'] ); ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'View Profile', 'wk-marketplace' ); ?></a>
		</div>

		<form action="" method="post" enctype="multipart/form-data" id="wkmp-seller-profile">
			<div class="wkmp-tab-content">
				<div class="wkmp_profileinfo">
					<div class="wkmp_profile_input">
						<label for="wk_username"><?php esc_html_e( 'Username', 'wk-marketplace' ); ?></label>
						<input type="text" name="wkmp_username" value="<?php echo esc_attr( $seller_info['wkmp_username'] ); ?>" id="wk_username" readonly disabled="disabled"/><br>
						<i><?php esc_html_e( 'Username cannot be changed.', 'wk-marketplace' ); ?></i>
						<input type="hidden" name="wk_user_nonece" value="<?php echo esc_attr( $seller_info['wkmp_username'] ); ?>" id="wk_user_nonece" readonly/>
						<div id=""></div>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_firstname"><?php esc_html_e( 'First Name', 'wk-marketplace' ); ?></label>
						<input type="text" value="<?php echo esc_attr( $seller_info['wkmp_first_name'] ); ?>" name="wkmp_first_name" id="wk_firstname"/>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_lastname"><?php esc_html_e( 'Last Name', 'wk-marketplace' ); ?></label>
						<input type="text" value="<?php echo esc_attr( $seller_info['wkmp_last_name'] ); ?>" name="wkmp_last_name" id="wk_lastname"/>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_useremail"><?php esc_html_e( 'E-mail', 'wk-marketplace' ); ?></label>
						<input type="text" value="<?php echo esc_attr( $seller_info['wkmp_seller_email'] ); ?>" name="wkmp_seller_email" id="wk_useremail"/>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_store_name"><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?></label>
						<input type="text" placeholder="" value="<?php echo esc_attr( $seller_info['wkmp_shop_name'] ); ?>" name="wkmp_shop_name" id="wk_storename" class="wk_loginput"/>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_shop_add"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?></label>
						<input type="text" placeholder="<?php esc_attr_e( 'Shop Address', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $seller_info['wkmp_shop_url'] ); ?>" name="wkmp_shop_url" id="wk_storeurl" class="wk_loginput" disabled="disabled" readonly/>
						<i><?php esc_html_e( 'Shop URL cannot be changed.', 'wk-marketplace' ); ?></i>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_shop_add"><?php esc_html_e( 'Phone Number', 'wk-marketplace' ); ?></label>
						<input type="text" placeholder="<?php esc_attr_e( 'Shop Phone Number', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $seller_info['wkmp_shop_phone'] ); ?>" name="wkmp_shop_phone" id="wk_storephone" class="wk_loginput"/>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_store_add1"><?php esc_html_e( 'Address Line 1', 'wk-marketplace' ); ?></label>
						<input type="text" placeholder="<?php esc_attr_e( 'Shop Address 1', 'wk-marketplace' ); ?>" name="wkmp_shop_address_1" id="wk-store-add1" value="<?php echo esc_attr( $seller_info['wkmp_shop_address_1'] ); ?>" class="wk_loginput"/>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_store_add2"><?php esc_html_e( 'Address Line 2', 'wk-marketplace' ); ?></label>
						<input type="text" placeholder="<?php esc_attr_e( 'Shop Address 2', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $seller_info['wkmp_shop_address_2'] ); ?>" name="wkmp_shop_address_2" id="wk-store-add2" class="wk_loginput"/>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_store_country"><?php esc_html_e( 'Country', 'wk-marketplace' ); ?></label>
						<?php
						global $woocommerce;
						$countries_obj = new \WC_Countries();
						$countries     = $countries_obj->__get( 'countries' );
						?>
						<div id="seller_countries_field">
							<?php
							woocommerce_form_field(
								'wkmp_shop_country',
								array(
									'type'        => 'select',
									'id'          => 'billing-country',
									'default'     => $seller_info['wkmp_shop_country'],
									'class'       => array( 'chzn-drop' ),
									'options'     => $countries,
									'placeholder' => __( 'Select a country', 'wk-marketplace' ),
								)
							);
							?>
						</div>
					</div>
					<?php
					if ( WC()->countries->get_states( $seller_info['wkmp_shop_country'] ) ) {
						$states = WC()->countries->get_states( $seller_info['wkmp_shop_country'] );
						?>
						<div class="wkmp_profile_input">
							<label for="wk_store_state"><?php esc_html_e( 'State / County', 'wk-marketplace' ); ?></label>
							<?php
							woocommerce_form_field(
								'wkmp_shop_state',
								array(
									'id'          => 'billing-state',
									'type'        => 'select',
									'default'     => $seller_info['wkmp_shop_state'],
									'class'       => array( 'chzn-drop' ),
									'options'     => $states,
									'placeholder' => __( 'Select a country', 'wk-marketplace' ),
								)
							);
							?>
						</div>
					<?php } else { ?>
						<div class="wkmp_profile_input">
							<label for="wk_store_state"><?php esc_html_e( 'State / County', 'wk-marketplace' ); ?></label>
							<input id="wk_store_state" type="text" placeholder="<?php esc_attr_e( 'State', 'wk-marketplace' ); ?>" name="wkmp_shop_state" class="wk_loginput" value="<?php echo esc_attr( $seller_info['wkmp_shop_state'] ); ?>"/>
						</div>
					<?php } ?>

					<div class="wkmp_profile_input">
						<label for="wk_store_city"><?php esc_html_e( 'City', 'wk-marketplace' ); ?></label>
						<input type="text" placeholder="<?php esc_attr_e( 'City', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $seller_info['wkmp_shop_city'] ); ?>" name="wkmp_shop_city" id="wk-store-city" class="wk_loginput"/>
						<div class="error-class" id="seller_store_city"></div>
					</div>

					<div class="wkmp_profile_input">
						<label for="wk_store_postcode"><?php esc_html_e( 'Postal Code', 'wk-marketplace' ); ?></label>
						<input type="text" placeholder="<?php esc_attr_e( 'Postcode', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $seller_info['wkmp_shop_postcode'] ); ?>" name="wkmp_shop_postcode" id="wk-store-postcode" class="wk_loginput"/>
					</div>

					<div class="wkmp_avatar_logo_section">

						<div class="wkmp_profile_img">
							<label class="wkmp-seller-profile" for="seller_avatar_file"><?php esc_html_e( 'User Image', 'wk-marketplace' ); ?>
								<i><?php esc_html_e( '(Upload image jpeg or png)', 'wk-marketplace' ); ?></i></label>
							<div id="wkmp-thumb-image" class="wkmp-img-thumbnail" style="display:table;">
								<img class="wkmp-img-thumbnail" src="<?php echo empty( $seller_info['wkmp_avatar_file'] ) ? esc_url( $seller_info['wkmp_generic_avatar'] ) : esc_url( $seller_info['wkmp_avatar_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_avatar'] ); ?>"/>
								<input type="hidden" id="thumbnail_id_avatar" name="wkmp_avatar_id" value="<?php echo esc_attr( $seller_info['wkmp_avatar_id'] ); ?>"/>
								<input type="file" name="wkmp_avatar_file" class="wkmp_hide" id="seller_avatar_file"/>
							</div>
							<div class="wkmp-fileUpload wkmp_profile_input">
								<button type="button" class="button" id="wkmp-upload-profile-image"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
								<button type="button" class="button wkmp-remove-profile-image" style="color:#fff;background-color:#da2020"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
							</div>
						</div>

						<div class="wkmp_profile_logo">
							<label class="wkmp-seller-profile" for="seller_shop_logo_file"><?php esc_html_e( 'Shop Logo', 'wk-marketplace' ); ?>
								<i><?php esc_html_e( '(Upload image jpeg or png)', 'wk-marketplace' ); ?></i>
							</label>
							<div id="wkmp-thumb-image" class="wkmp-img-thumbnail" style="display:table;">
								<img class="wkmp-img-thumbnail" src="<?php echo empty( $seller_info['wkmp_logo_file'] ) ? esc_url( $seller_info['wkmp_generic_logo'] ) : esc_url( $seller_info['wkmp_logo_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_logo'] ); ?>"/>
								<input type="hidden" id="thumbnail_id_company_logo" name="wkmp_logo_id" value="<?php echo esc_attr( $seller_info['wkmp_logo_id'] ); ?>"/>
								<input type="file" name="wkmp_logo_file" class="wkmp_hide" id="seller_shop_logo_file"/>
							</div>
							<div class="wkmp-button" style="font-size:13px;margin-top:2px;">
								<button type="button" class="button" id="wkmp-upload-shop-logo"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
								<button type="button" class="button wkmp-remove-shop-logo" style="color:#fff;background-color:#da2020"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
							</div>
						</div>
					</div>

					<!-- shop banner -->
					<div class="wkmp_profile_input">
						<label><?php esc_html_e( 'Banner Image', 'wk-marketplace' ); ?></label>
						<div class="banner-checkbox">
							<input type="checkbox" name="wkmp_display_banner" id="banner_visibility" value="yes" <?php echo ( 'yes' === $seller_info['wkmp_display_banner'] ) ? 'checked' : ''; ?>/>
							<label for="banner_visibility"><?php esc_html_e( 'Show banner on seller page', 'wk-marketplace' ); ?></label></div>

						<div class="wkmp_shop_banner">
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
					<!-- shop banner end-->

					<div class="wkmp_profile_input">
						<label for="wk_marketplace_about_shop"><?php esc_html_e( 'About Shop', 'wk-marketplace' ); ?></label>
						<textarea name="wkmp_about_shop" rows="4" id="wk_marketplace_about_shop" class="wk_loginput"><?php echo esc_html( $seller_info['wkmp_about_shop'] ); ?></textarea>
					</div>

					<h3><b><?php esc_html_e( 'Social Profile', 'wk-marketplace' ); ?></b></h3>

					<div class="wkmp_profile_input">

						<label for="settings[social][fb]"><?php esc_html_e( 'Facebook Profile ID', 'wk-marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'wk-marketplace' ) . ')'; ?></i>
						<div class="social-seller-input">
							<input id="settings[social][fb]" type="text" placeholder="https://" name="wkmp_settings[social][fb]" value="<?php echo esc_url( $seller_info['wkmp_facebook'] ); ?>">
						</div>
						<div class="error-class" id="seller_user_address"></div>

					</div>


					<div class="wkmp_profile_input">

						<label for="settings[social][fb]"><?php esc_html_e( 'Instagram Profile ID', 'wk-marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'wk-marketplace' ) . ')'; ?></i>
						<div class="social-seller-input">
							<input id="settings[social][ins]" type="text" placeholder="https://" name="wkmp_settings[social][insta]" value="<?php echo esc_url( $seller_info['wkmp_instagram'] ); ?>">
						</div>
						<div class="error-class" id="seller_user_address"></div>

					</div>

					<div class="wkmp_profile_input">
						<label for="settings[social][twitter]"><?php esc_html_e( 'Twitter Profile ID', 'wk-marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'wk-marketplace' ) . ')'; ?></i>
						<div class="social-seller-input">
							<input id="settings[social][twitter]" type="text" placeholder="https://" name="wkmp_settings[social][twitter]" value="<?php echo esc_url( $seller_info['wkmp_twitter'] ); ?>">
						</div>
						<div class="error-class" id="seller_user_address"></div>
					</div>

					<div class="wkmp_profile_input">
						<label for="settings[social][linked]"><?php esc_html_e( 'LinkedIn Profile ID', 'wk-marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'wk-marketplace' ) . ')'; ?></i>
						<div class="social-seller-input">
							<input id="settings[social][linked]" type="text" placeholder="https://" name="wkmp_settings[social][linkedin]" value="<?php echo esc_url( $seller_info['wkmp_linkedin'] ); ?>">
						</div>
						<div class="error-class" id="seller_user_address"></div>
					</div>
					<div class="wkmp_profile_input">
						<label for="settings[social][youtube]"><?php esc_html_e( 'Youtube Profile ID', 'wk-marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'wk-marketplace' ) . ')'; ?></i>
						<div class="social-seller-input">
							<input id="settings[social][youtube]" type="text" placeholder="https://" name="wkmp_settings[social][youtube]" value="<?php echo esc_url( $seller_info['wkmp_youtube'] ); ?>">
						</div>
						<div class="error-class" id="seller_user_address"></div>
					</div>
				</div>

				<!-- Seller Payment Method -->
				<div class="wkmp_profile_input">
					<label for="mp_seller_payment_details"><?php esc_html_e( 'Payment Information', 'wk-marketplace' ); ?></label>
					<textarea name="wkmp_payment_details" placeholder="<?php esc_attr_e( 'eg : test@paypal.com', 'wk-marketplace' ); ?>"><?php echo esc_html( $seller_info['wkmp_payment_details'] ); ?></textarea><br/><br/>
					<?php
					$paymet_gateways = WC()->payment_gateways->payment_gateways();
					do_action( 'marketplace_payment_gateway' );
					?>
				</div>

				<?php do_action( 'mp_add_seller_profile_field', $current_user_id ); ?>

				<div class="wkmp_profile_btn">
					<input type="submit" class='button button-primary' value="<?php esc_attr_e( 'Update', 'wk-marketplace' ); ?>" name="update_profile_submit" id="update_profile_submit"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="button"><?php esc_html_e( 'Cancel', 'wk-marketplace' ); ?></a>
				</div>

				<?php wp_nonce_field( 'edit_profile', 'wk_user_nonece' ); ?>
			</div>
		</form>
	</div>
</div>
