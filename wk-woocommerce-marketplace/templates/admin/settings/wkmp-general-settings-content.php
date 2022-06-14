<?php
/**
 * General settings template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;
settings_errors();
?>

<form method="POST" action="options.php">
	<?php settings_fields( 'wkmp-general-settings-group' ); ?>
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-default-commission">
					<?php esc_html_e( 'Default Commission (in %)', 'wk-marketplace' ); ?>
				</label>
			</th>
			<td class="forminp forminp-text">
				<?php echo wc_help_tip( esc_html__( 'Default commission from seller if not set on seller basis.', 'wk-marketplace' ), true ); ?>
				<input type="text" class="regular-text" id="wkmp-default-commission" name="_wkmp_default_commission" value="<?php echo esc_attr( get_option( '_wkmp_default_commission' ) ); ?>"/>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-auto-approve-seller"><?php esc_html_e( 'Auto Approve Seller', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php echo wc_help_tip( esc_html__( 'If checked, Seller will be approved automatically.', 'wk-marketplace' ), true ); ?>
				<input name="_wkmp_auto_approve_seller" type="checkbox" id="wkmp-auto-approve-seller" value="1" <?php checked( get_option( '_wkmp_auto_approve_seller' ), 1 ); ?> />
			</td>
		</tr>
		<tr>
			<th scope="row" class="titledesc">
				<label for="wkmp-separate-seller-dashboard"><?php esc_html_e( 'Separate Seller Dashboard', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php echo wc_help_tip( esc_html__( 'If checked, Seller will have separate dashboard like Admin.', 'wk-marketplace' ), true ); ?>
				<input type="checkbox" name="_wkmp_separate_seller_dashboard" id="wkmp-separate-seller-dashboard" value="1" <?php checked( get_option( '_wkmp_separate_seller_dashboard' ), 1 ); ?> />
			</td>
		</tr>
		<tr>
			<th scope="row" class="titledesc">
				<label for="wkmp-separate-seller-registration"><?php esc_html_e( 'Separate Seller Registration', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php echo wc_help_tip( esc_html__( 'If checked, Seller registration will be done from separate page than My Account.', 'wk-marketplace' ), true ); ?>
				<input type="checkbox" name="_wkmp_separate_seller_registration" id="wkmp-separate-seller-registration" value="1" <?php checked( get_option( '_wkmp_separate_seller_registration' ), 1 ); ?> />
			</td>
		</tr>

		<tr>
			<th scope="row" class="titledesc">
				<label for="wkmp-seller-delete"><?php esc_html_e( 'Data delete after seller delete', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php echo wc_help_tip( esc_html__( 'If checked, Then data delete after seller delete else assigned to the admin', 'wk-marketplace' ), true ); ?>
				<input type="checkbox" name="_wkmp_seller_delete" id="wkmp-seller-delete" value="1" <?php checked( get_option( '_wkmp_seller_delete' ), 1 ); ?> />
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="wkmp_shipping_option">
					<?php
					$method_title = apply_filters( 'wkmp_general_settings_shipping_option_title', esc_html__( 'Applicable Shipping Methods', 'wk-marketplace' ) );
					echo esc_html( $method_title );
					?>
				</label>
			</th>
			<td>
				<?php
				$shipping_method = array(
					'marketplace' => esc_html__( 'Seller Shipping', 'wk-marketplace' ),
					'woocommerce' => esc_html__( 'Admin Shipping', 'wk-marketplace' ),
				);
				$shipping_method = apply_filters( 'wkmp_general_settings_shipping_methods', $shipping_method );
				?>
				<?php echo wc_help_tip( apply_filters( 'wkmp_general_settings_shipping_option_message', esc_html__( 'Check Whose shipping method is applicable at cart Page', 'wk-marketplace' ) ), true ); ?>
				<select name="wkmp_shipping_option" class="regular-text" id="wkmp_shipping_option">
					<?php
					if ( ! empty( $shipping_method ) && is_iterable( $shipping_method ) ) :
						foreach ( $shipping_method as $key => $methods ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( get_option( 'wkmp_shipping_option' ), $key ); ?> >
								<?php echo esc_html( $methods ); ?>
							</option>
							<?php
						endforeach;
					endif;
					?>
				</select>
				<p class="description"><?php echo sprintf( wp_kses( 'Shipping must be enabled from %s woocommerce %s settings in order to work this functionality.', 'wk-marketplace' ), '<a target="_blank" href="' . esc_url( admin_url( 'admin.php?page=wc-settings' ) ) . '">', '</a>' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="wkmp_seller_page_option">
					<?php
					$seller_page_title = apply_filters( 'wkmp_general_settings_seller_page', esc_html__( 'Select Seller Page', 'wk-marketplace' ) );
					echo esc_html( $seller_page_title );
					?>
				</label>
			</th>
			<td>
				<?php

				$args = apply_filters(
					'wkmp_get_pages_for_seller_dashboard_args',
					array(
						'exclude' => array(
							get_option( 'woocommerce_shop_page_id', 0 ),
							get_option( 'woocommerce_cart_page_id', 0 ),
							get_option( 'woocommerce_checkout_page_id', 0 ),
							get_option( 'woocommerce_pay_page_id', 0 ),
							get_option( 'woocommerce_thanks_page_id', 0 ),
							get_option( 'woocommerce_myaccount_page_id', 0 ),
							get_option( 'woocommerce_edit_address_page_id', 0 ),
							get_option( 'woocommerce_view_order_page_id', 0 ),
							get_option( 'woocommerce_terms_page_id', 0 ),
						),
					)
				);

				$site_pages = get_pages( $args );
				echo wc_help_tip( apply_filters( 'wkmp_general_settings_seller_page_messages', esc_html__( 'Select page to show seller dashboard.', 'wk-marketplace' ) ), true );
				?>
				<select name="wkmp_select_seller_page" class="regular-text" id="wkmp_seller_page">
					<?php
					if ( ! empty( $site_pages ) && is_iterable( $site_pages ) ) {
						$seller_page_id = get_option( 'wkmp_seller_page_id' );
						foreach ( $site_pages as $site_page ) {
							?>
							<option value="<?php echo esc_attr( $site_page->ID ); ?>" <?php selected( get_option( 'wkmp_seller_page_id' ), $site_page->ID ); ?> >
								<?php echo esc_html( $site_page->post_title ); ?>
							</option>
							<?php
						}
					}
					?>
				</select>
				<p class="description"><?php echo sprintf( wp_kses( 'Updating a new seller page will erase the previous content of the newly selected page. Kindly update %s permalinks %s after change.', 'wk-marketplace' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">', '</a>' ); ?></p>
			</td>
		</tr>

		<?php do_action( 'wkmp_add_settings_field' ); ?>
		</tbody>
	</table>
	<?php submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary' ); ?>
</form>
<hr/>
