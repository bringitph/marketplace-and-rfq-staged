<?php
/**
 * Seller registration fields template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$postdata   = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
$user_role  = isset( $postdata['role'] ) ? $postdata['role'] : 'customer';
$role_style = ( 'customer' === $user_role ) ? ' style=display:none' : '';
if ( ! is_account_page() ) {
	$role_style = 'style=display:block';
}
?>
<div class="wkmp-seller-registration-fields">
	<div class="wkmp-show-fields-if-seller" <?php echo esc_attr( $role_style ); ?>>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-firstname"><?php esc_html_e( 'First Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text form-control" name="wkmp_firstname" value="<?php echo empty( $postdata['wkmp_firstname'] ) ? '' : esc_attr( $postdata['wkmp_firstname'] ); ?>" id="wkmp-firstname"/>
		<div class="wkmp-error-class" id="wkmp-seller-firstname-error"></div>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-lastname"><?php esc_html_e( 'Last Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text form-control" name="wkmp_lastname" value="<?php echo empty( $postdata['wkmp_lastname'] ) ? '' : esc_attr( $postdata['wkmp_lastname'] ); ?>" id="wkmp-lastname"/>
		<div class="wkmp-error-class" id="wkmp-seller-lastname-error"></div>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-shopname"><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text form-control" name="wkmp_shopname" value="<?php echo empty( $postdata['wkmp_shopname'] ) ? '' : esc_attr( $postdata['wkmp_shopname'] ); ?>" id="wkmp-shopname"/>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-shopurl" class="pull-left"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text form-control" name="wkmp_shopurl" value="<?php echo empty( $postdata['wkmp_shopurl'] ) ? '' : esc_attr( $postdata['wkmp_shopurl'] ); ?>" id="wkmp-shopurl"/>
			<strong id="wkmp-shop-url-availability"></strong>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-shopphone"><?php esc_html_e( 'Phone Number', 'wk-marketplace' ); ?><span class="required">*</span></label>
			<input type="text" class="input-text form-control" name="wkmp_shopphone" value="<?php echo empty( $postdata['wkmp_shopphone'] ) ? '' : esc_attr( $postdata['wkmp_shopphone'] ); ?>" id="wkmp-shopphone"/>
		</p>

		<?php do_action( 'wk_mkt_add_register_field' ); ?>
	</div>

	<?php if ( is_account_page() ) { ?>
		<div class="wkmp-role-selector-section">
			<ul class="nav wkmp-role-selector" role="tablist">
				<li class="wkmp-button wkmp-customer<?php echo 'customer' === $user_role ? ' active' : ''; ?>" data-target="0">
					<label class="radio wkmp-fw-600" style="padding:0;margin:0;">
						<input type="radio" name="role" value="customer"<?php checked( $user_role, 'customer' ); ?> ><?php esc_html_e( 'I am a customer', 'wk-marketplace' ); ?>
					</label>
				</li>
				<li data-target="1" class="wkmp-button wkmp-seller<?php echo 'seller' === $user_role ? ' active' : ''; ?>">
					<label class="radio wkmp-fw-600" style="padding:0;margin:0;">
						<input type="radio" name="role" value="seller"<?php checked( $user_role, 'seller' ); ?> ><?php esc_html_e( 'I am a seller', 'wk-marketplace' ); ?>
					</label>
				</li>
			</ul>
		</div>
	<?php } else { ?>
		<input type="hidden" name="role" value="seller">
	<?php } ?>

</div>
