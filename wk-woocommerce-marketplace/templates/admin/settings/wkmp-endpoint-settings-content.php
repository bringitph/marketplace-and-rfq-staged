<?php
/**
 * Marketplace Admin Endpoints Settings Template.
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$endpoints_array = apply_filters(
	'wkmp_endpoint_settings_array',
	array(
		array(
			'slug'  => esc_attr( 'dashboard' ),
			'title' => esc_html__( 'Dashboard', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'product-list' ),
			'title' => esc_html__( 'Product List', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'add-product' ),
			'title' => esc_html__( 'Add Product', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'order-history' ),
			'title' => esc_html__( 'Order History', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'transaction' ),
			'title' => esc_html__( 'Transaction', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'shipping' ),
			'title' => esc_html__( 'Shipping', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'profile' ),
			'title' => esc_html__( 'Seller profile', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'notification' ),
			'title' => esc_html__( 'Notification', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'shop-follower' ),
			'title' => esc_html__( 'Shop Follower', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'asktoadmin' ),
			'title' => esc_html__( 'Ask to Admin', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'seller-product' ),
			'title' => esc_html__( 'Product from Seller', 'wk-marketplace' ),
		),
		array(
			'slug'  => esc_attr( 'store' ),
			'title' => esc_html__( 'Recent Product from Seller', 'wk-marketplace' ),
		),
	)
);

settings_errors();
?>

<h1><?php esc_html_e( 'Marketplace Account endpoints', 'wk-marketplace' ); ?></h1>

<p><?php esc_html_e( 'Endpoints are appended to your page URLs to handle specific actions on the accounts pages. They should be unique.', 'wk-marketplace' ); ?></p>

<form method="post" action="options.php" id="wkmp-endpoint-form">
	<?php
	settings_fields( 'wkmp-endpoint-settings-group' );

	foreach ( $endpoints_array as $key => $value ) {
		$name = str_replace( '-', '_', $value['slug'] );
		?>
		<fieldset class="mp-fieldset">
			<legend><?php echo esc_html( wc_strtoupper( $value['title'] ) ); ?></legend>
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row">
						<label for=""><?php esc_html_e( 'Endpoint', 'wk-marketplace' ); ?></label>
					</th>
					<td class="forminp">
						<?php echo wc_help_tip( /* translators: %s: username. */ sprintf( esc_html__( 'Endpoint for "My Account → %s" page.', 'wk-marketplace' ), esc_html( $value['title'] ) ) ); ?>
						<input type="text" etype="endpoint" class="regular-text mp-endpoints-text" name="_wkmp_<?php echo esc_attr( $name ); ?>_endpoint" value="<?php echo esc_attr( get_option( '_wkmp_' . $name . '_endpoint', $value['slug'] ) ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for=""><?php esc_html_e( 'Title', 'wk-marketplace' ); ?></label>
					</th>
					<td class="forminp">
						<?php echo wc_help_tip( sprintf( /* translators: %s: username. */ esc_html__( 'Title for "My Account → %s" page.', 'wk-marketplace' ), esc_html( $value['title'] ) ) ); ?>
						<input type="text" class="regular-text" name="_wkmp_<?php echo esc_attr( $name ); ?>_endpoint_name" value="<?php echo esc_attr( get_option( '_wkmp_' . $name . '_endpoint_name', esc_attr( $value['title'] ) ) ); ?>" required>
					</td>
				</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	?>
</form>
<p class="submit"><input type="submit" name="submit" id="wk-endpoint-submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'wk-marketplace' ); ?>"></p>
<hr/>
