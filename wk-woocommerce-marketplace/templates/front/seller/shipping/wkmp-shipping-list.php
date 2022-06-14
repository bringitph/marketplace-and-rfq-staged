<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$user_id      = get_current_user_id();
$shop_address = get_user_meta( $user_id, 'shop_address', true );
$sellerurl    = urldecode( get_query_var( 'main_page' ) );
?>
<div class="woocommerce-account woocommerce">
	<?php do_action( 'mp_get_wc_account_menu', 'marketplace' ); ?>
	<div class="woocommerce-MyAccount-content">

		<?php
		$notice_data = get_user_meta( $user_id, '_wkmp_shipping_notice_data', true );

		if ( ! empty( $notice_data['action'] ) ) {
			$zone_action = $notice_data['action'];
			$zon_name    = empty( $notice_data['zone_name'] ) ? '' : $notice_data['zone_name'];
			$message     = wp_sprintf( /* translators: %s: zone name, %s: Action */ esc_html__( '%1$s Zone %2$s Successfully.', 'wk-marketplace' ), $zon_name, $zone_action );
			wc_print_notice( $message, 'success' );
			delete_user_meta( $user_id, '_wkmp_shipping_notice_data' );
		}

		if ( $sellerurl === $shop_address ) :
			$zones = \WC_Shipping_Zones::get_zones();
			?>
			<div class="new-ship-zone">
				<a href="<?php echo esc_url( home_url( $wkmarketplace->seller_page_slug . '/' . $seller_info->shop_address . '/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '/?action=add' ) ); ?>"><?php esc_html_e( 'Add New Shipping Zone', 'wk-marketplace' ); ?></a>
				<img class="wp-spin wkmp-spin-loader wkmp_hide" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>">
			</div>

			<table class="wc-shipping-zones-list widefat">
				<thead>
				<tr>
					<th class="wc-shipping-zone-name"><?php esc_html_e( 'Zone Name', 'wk-marketplace' ); ?></th>
					<th class="wc-shipping-zone-region"><?php esc_html_e( 'Region(s)', 'wk-marketplace' ); ?></th>
					<th class="wc-shipping-zone-methods"><?php esc_html_e( 'Shipping Method(s)', 'wk-marketplace' ); ?></th>
					<th class="wc-shipping-zone-actions"><?php esc_html_e( 'Actions', 'wk-marketplace' ); ?></th>
				</tr>
				</thead>

				<tbody class="wc-shipping-zone-rows ui-sortable">
				<?php
				global $wpdb;
				$table_name   = $wpdb->prefix . 'mpseller_meta';
				$seller_zones = $wpdb->get_results( $wpdb->prepare( "SELECT zone_id FROM {$wpdb->prefix}mpseller_meta where seller_id=%d", $user_id ), ARRAY_A );
				$u_zones      = wp_list_pluck( $seller_zones, 'zone_id' );
				$u_zones      = array_map( 'intval', $u_zones );

				if ( ! empty( $u_zones ) && ! empty( $zones ) ) {
					foreach ( $zones as $key => $value ) {
						if ( in_array( $value['zone_id'], $u_zones, true ) ) {
							$ship_locations = $this->db_shipping_obj->wkmp_get_formatted_location( $value['zone_locations'] );
							$ship_locations = explode( ',', $ship_locations );
							?>
							<tr class="final-editing">
								<td class="wc-shipping-zone-name">
									<div class="view">
										<p><?php echo html_entity_decode( stripslashes( $value['zone_name'] ) );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
									</div>
								</td>

								<td class="wc-shipping-zone-region">
									<div class="mp_select_country">
										<?php
										foreach ( $ship_locations as $value_location ) {
											echo '<div class="mp_ship_tags">' . esc_html( $value_location ) . '</div>';
										}
										?>
									</div>

								</td>
								<td class="wc-shipping-zone-methods list-shipping">
									<div>
										<ul>
											<?php
											$zones   = new \WC_Shipping_Zone( $value['zone_id'] );
											$methods = $zones->get_shipping_methods();
											if ( ! empty( $methods ) ) {
												$method_count = is_iterable( $methods ) ? count( $methods ) : 0;
												$i            = 0;
												foreach ( $methods as $method ) {
													$i++;
													$comma = ( $i === $method_count ) ? '' : ',';
													echo '<li class="wc-shipping-zone-method">' . esc_html( $method->get_title() . $comma ) . '</li>';
												}
											} else {
												echo '<p>' . esc_html__( 'No shipping methods offered to this zone.', 'wk-marketplace' ) . '</p>';
											}
											?>
										</ul>
									</div>
								</td>

								<td>
									<a id="editprod" class="mp-action" href="<?php echo esc_url( home_url( $wkmarketplace->seller_page_slug . '/' . $seller_info->shop_address . '/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '/?wkmp_ship_edit=' . $value['zone_id'] ) ); ?>"><?php esc_html_e( 'edit', 'wk-marketplace' ); ?></a>
									<a id="delprod" class="wc-shipping-zone-delete mp-action" href="javascript:void(0)" data-zone-id="<?php echo esc_attr( $value['zone_id'] ); ?>" class="ask"><?php esc_html_e( 'delete', 'wk-marketplace' ); ?></a>
								</td>
							</tr>
							<?php
						}
					}
				}
				?>
				</tbody>

				<tbody>
				<tr data-id="0">
					<td class="wc-shipping-zone-name">
						<div class="view">
							<p><?php esc_html_e( 'Rest of the World', 'wk-marketplace' ); ?></p>
						</div>
					</td>

					<td class="wc-shipping-zone-region"><?php esc_html_e( 'This zone is used for shipping addresses that aren&lsquo;t included in any other shipping zone. Adding shipping methods to this zone is optional.', 'wk-marketplace' ); ?></td>

					<td class="wc-shipping-zone-methods">
						<ul>
							<?php
							$worldwide = new \WC_Shipping_Zone( 0 );
							$methods   = $worldwide->get_shipping_methods();

							if ( ! empty( $methods ) ) {
								$method_count = is_iterable( $methods ) ? count( $methods ) : 0;
								$i            = 0;
								foreach ( $methods as $method ) {
									$i++;
									$comma = ( $i === $method_count ) ? '' : ',';
									echo '<li class="wc-shipping-zone-method">' . esc_html( $method->get_title() ) . $comma . '</li>';
								}
							} else {
								echo '<li class="wc-shipping-zone-method">' . esc_html__( 'No shipping methods offered to this zone.', 'wk-marketplace' ) . '</li>';
							}
							?>
						</ul>
					</td>
				</tr>
				</tbody>
			</table>
		<?php else : ?>
			<h1><?php esc_html_e( 'Cheating huh ???', 'wk-marketplace' ); ?></h1>
			<p><?php esc_html_e( "Sorry, You can't access other seller's shipping zones.", 'wk-marketplace' ); ?></p>
		<?php endif; ?>
	</div>
</div>
