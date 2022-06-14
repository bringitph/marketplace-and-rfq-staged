<?php
/**
 * User profile.
 *
 * @package @package @package WkMarketplace\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$disable = '';
$address = '';
$style   = '';

$payment_information = array();

foreach ( $user->roles as $key => $value ) {
	if ( 'wk_marketplace_seller' === $value ) {
		$address = get_user_meta( $user->ID, 'shop_address', true );
		if ( $address ) {
			$style   = '';
			$disable = 'disabled';
		}
	} else {
		$style   = 'style=display:none;';
		$disable = '';
	}
}

if ( null !== get_user_meta( $user->ID, 'mp_seller_payment_details' ) ) {
	$payment_information = get_user_meta( $user->ID, 'mp_seller_payment_details', true );
}

?>
<div class="mp-seller-details" <?php echo esc_html( $style ); ?>>
	<h3 class="heading"><?php esc_html_e( 'Marketplace Seller Details', 'wk-marketplace' ); ?></h3>
	<table class="form-table">
		<tr>
			<th>
				<label for="company-name">
					<?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?> <span style="display:inline-block;" class="required">*</span>
				</label>
			</th>
			<td>
				<input type="text" class="input-text form-control" name="shopname" id="org-name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'shop_name', true ) ); ?>" required="required"/>
			</td>
		</tr>

		<tr>
			<th><label for="seller-url" class="pull-left"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?> <span class="required" style="display:inline-block;">*</span></label></th>
			<td>
				<input type="text" class="input-text form-control" name="shopurl" id="seller-shop" value="<?php echo esc_attr( get_user_meta( $user->ID, 'shop_address', true ) ); ?>" required="required" <?php echo esc_html( $disable ); ?>>
				<p><strong id="seller-shop-alert-msg" class="pull-right"></strong></p>
			</td>
		</tr>

		<?php if ( ! empty( $payment_information ) ) { ?>
			<tr>
				<th>
					<label for="seller-payment-info" class="pull-left">
						<?php esc_html_e( 'Payment Information', 'wk-marketplace' ); ?>
					</label>
				</th>
				<td>
					<?php echo esc_html( $payment_information ); ?><br>
				</td>
			</tr>
			<?php
		}
		do_action( 'wkmp_after_seller_profile_fields', $user, $disable );
		?>
	</table>
</div>
