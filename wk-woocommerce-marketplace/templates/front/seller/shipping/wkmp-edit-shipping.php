<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

try { ?>
	<div class="woocommerce-account woocommerce">
		<?php do_action( 'mp_get_wc_account_menu' ); ?>
		<div id="main_container" class="woocommerce-MyAccount-content">
			<?php
			if ( $main_page === $shop_address && ! empty( $seller_zone_check ) ) :
				?>
				<form action="" method="post">
					<?php wp_nonce_field( 'shipping_action', 'shipping_nonce' ); ?>
					<table class="wc-shipping-zones widefat">
						<thead>
						<tr>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="4">
								<input type="submit" name="update_shipping_details" class="button button-primary wc-shipping-zone-update" value="<?php esc_attr_e( 'Update changes', 'wk-marketplace' ); ?>"/>
							</td>
						</tr>
						</tfoot>
						<tbody class="wc-shipping-zone-rows ui-sortable">
						<?php
						$ship_locations  = $this->db_shipping_obj->wkmp_get_formatted_location( $zone_locations );
						$ship_locations  = explode( ',', $ship_locations );
						$ship_code_array = $this->db_shipping_obj->wkmp_get_formatted_code( $zone_locations );
						$ship_code_array = explode( ',', $ship_code_array );
						?>
						<tr class="final-editing">
							<td><label for="mp_zone_name"><?php esc_html_e( 'Zone Name', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label></td>
							<td class="wc-shipping-zone-name">
								<input type="text" name="mp_zone_name" value="<?php echo html_entity_decode( stripslashes( $zone_name ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-attribute="zone_name" placeholder="<?php esc_attr_e( 'Zone Name', 'wk-marketplace' ); ?>">
							</td>
						</tr>
						<tr>
							<td class="wc-shipping-zone-region"><label for="mp_zone_region"><?php esc_html_e( 'Zone Region', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
							</td>
							<input type="hidden" name="hidden_user" value="<?php echo esc_attr( $this->seller_id ); ?>">
							<td class="wc-shipping-zone-region">
								<div class="edit">
									<div class="mp_shipping_country">
										<?php
										$i    = 0;
										$zone = \WC_Shipping_Zones::get_zone( absint( $zone_id ) );
										if ( ! $zone ) {
											wp_die( esc_html__( 'Zone does not exist!', 'wk-marketplace' ) );
										}
										$allowed_countries = WC()->countries->get_allowed_countries();
										$wc_shipping       = \WC_Shipping::instance();
										$shipping_methods  = $wc_shipping->get_shipping_methods();
										$continents        = WC()->countries->get_continents();

										// Prepare locations.
										$locations = array();
										$postcodes = array();

										foreach ( $zone->get_zone_locations() as $location ) {
											if ( 'postcode' === $location->type ) {
												$postcodes[] = $location->code;
											} else {
												$locations[] = $location->type . ':' . $location->code;
											}
										}
										?>
										<input type="hidden" value="<?php echo esc_attr( $zone_id ); ?>" name="mp_zone_id"/>
										<select multiple="multiple" data-attribute="zone_locations" id="new_zone_locations" name="zone_locations[]" data-placeholder="<?php esc_attr_e( 'Select regions within this zone', 'wk-marketplace' ); ?>" class="wc-shipping-zone-region-select chosen_select">
											<?php
											foreach ( $continents as $continent_code => $continent ) {
												echo '<option value="continent:' . esc_attr( $continent_code ) . '" ' . selected( in_array( "continent:$continent_code", $locations, true ), true, false ) . ' alt="">' . esc_html( $continent['name'] ) . '</option>';
												$countries = array_intersect( array_keys( $allowed_countries ), $continent['countries'] );
												foreach ( $countries as $country_code ) {
													echo '<option value="country:' . esc_attr( $country_code ) . '" ' . selected( in_array( "country:$country_code", $locations, true ), true, false ) . ' alt="' . esc_attr( $continent['name'] ) . '">' . esc_html( '&nbsp;&nbsp; ' . $allowed_countries[ $country_code ] ) . '</option>';

													$states = WC()->countries->get_states( $country_code );
													if ( $states ) {
														foreach ( $states as $state_code => $state_name ) {
															echo '<option value="state:' . esc_attr( $country_code . ':' . $state_code ) . '" ' . selected( in_array( "state:$country_code:$state_code", $locations, true ), true, false ) . ' alt="' . esc_attr( $continent['name'] . ' ' . $allowed_countries[ $country_code ] ) . '">' . esc_html( '&nbsp;&nbsp;&nbsp;&nbsp; ' . $state_name ) . '</option>';
														}
													}
												}
											}
											?>
										</select>
									</div>
									<?php
									$zone_postcodes = '';
									foreach ( $ship_code_array as $key => $value ) {
										if ( strpos( $value, 'postcode:' ) !== false ) {
											$zone_postcodes .= str_replace( 'postcode:', '', $value ) . "\n";
										}
									}
									?>
									<a class="wc-shipping-zone-postcodes-toggle" href="#" style="<?php echo $zone_postcodes ? 'display:none;' : 'display:block;'; ?>"><?php esc_html_e( 'Limit to specific ZIP/postcodes', 'wk-marketplace' ); ?></a>
									<div class="wc-shipping-zone-postcodes" style="<?php echo esc_attr( $zone_postcodes ) ? 'display:block;' : 'display:none;'; ?>">
										<textarea name="zone_postcodes" placeholder="List 1 postcode per line" class="input-text large-text" cols="25" rows="5"><?php echo esc_html( $zone_postcodes ); ?></textarea>
										<span class="description"><?php printf( __( 'Postcodes containing wildcards (e.g. CB23*) or fully numeric ranges (e.g. <code>90210...99000</code>) are also supported. Please see the shipping zones <a href="%s" target="_blank">documentation</a> for more information.', 'wk-marketplace' ), 'https://docs.woocommerce.com/document/setting-up-shipping-zones/#section-3' ); ?></span><?php // @codingStandardsIgnoreLine. ?>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td><label for="mp_zone_shipping"><?php esc_html_e( 'Shipping Method', 'wk-marketplace' ); ?>&nbsp;&nbsp;:</label></td>
							<td class="wc-shipping-zone-methods shipping-extended">
								<div>
									<ul>
										<?php
										$methods = $zones->get_shipping_methods();

										if ( ! empty( $methods ) ) {
											foreach ( $methods as $method ) {
												$settings_html = $method->generate_settings_html( $method->get_instance_form_fields(), false );
												$ship_slug     = $method->get_rate_id();
												$ship_slug     = explode( ':', $ship_slug );
												?>
												<div id="modal-ship-rate<?php echo esc_attr( $ship_slug[1] ); ?>" style="display:none">
													<div class="shipping-method-add-cost">
														<table class="form-table">
															<?php echo $settings_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														</table>
														<input type="hidden" name="instance_id" value="<?php echo esc_attr( $method->instance_id ); ?>">
														<button class='button button-primary btn-save-cost'><?php esc_html_e( 'Save Changes', 'wk-marketplace' ); ?></button>
														<img class="wp-spin wkmp-spin-loader" style="display: none;" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>">
													</div>
												</div>

												<?php
												$class_name = 'yes' === $method->enabled ? 'method_enabled' : 'method_disabled';
												echo '<li class="wc-shipping-zone-method outer-ship-method">
													<span data-methid="' . esc_attr( $zone_id ) . '-' . esc_attr( $method->instance_id ) . '" class="del-ship-method"></span>
													<a href="#TB_inline?width=800&height=500&inlineId=modal-ship-rate' . esc_attr( $ship_slug[1] ) . '" class="' . esc_attr( $class_name ) . ' thickbox" title="' . wp_sprintf( /* translators: %s: Method title. */ esc_attr__( '%s Settings', 'wk-marketplace' ), $method->get_title() ) . ' ">' . esc_html( $method->get_title() ) . '</a></li>';
											}
										} else {
											echo '<p>' . esc_html__( 'No shipping methods offered to this zone.', 'wk-marketplace' ) . '</p>';
										}
										add_thickbox();
										?>

										<li class="wc-shipping-zone-methods-add-row"></li>
									</ul>

									<a href="#TB_inline?width=600&height=280&inlineId=modal-window-id" class="thickbox add_shipping_method tips button" title="<?php esc_attr_e( 'Add Shipping Method', 'wk-marketplace' ); ?>" data-tip="<?php esc_attr_e( 'Add shipping method', 'wk-marketplace' ); ?>" data-disabled-tip="<?php esc_attr_e( 'Save changes to continue adding shipping methods to this zone', 'wk-marketplace' ); ?>"><?php esc_html_e( 'Add shipping method', 'wk-marketplace' ); ?></a>

									<div id="modal-window-id" style="display:none">
										<div class="shipping-method-modal">
											<br/>
											<p><?php esc_html_e( 'Choose the shipping method you wish to add. Only shipping methods which support zones are listed.', 'wk-marketplace' ); ?></p>
											<select name="add_method_id" id="add_method_id" data-get-zone="<?php echo esc_attr( $zone_id ); ?>">
												<?php
												global $woocommerce;
												$shipping_methods = $woocommerce->shipping->load_shipping_methods();

												foreach ( $shipping_methods as $key => $value ) {
													$show_shipping_in_options = apply_filters( 'wk_mp_show_seller_available_shipping', true, $value );
													if ( $show_shipping_in_options && 'flat_rate' !== $value->id ) {
														echo "<option value='" . esc_attr( $value->id ) . "'>" . esc_html( $value->method_title ) . '</option>';
													}
												}
												?>
											</select>
											<br/>
											<br/>
											<p><strong><?php esc_html_e( 'Lets you charge a fixed rate for shipping.', 'wk-marketplace' ); ?></strong></p>
											<button class='button button-primary add-ship-method'><?php esc_html_e( 'Add Shipping Method', 'wk-marketplace' ); ?></button>
											<img class="wp-spin wkmp-spin-loader" style="display: none;" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>">
										</div>
									</div>
								</div>
							</td>
						</tr>
						</tbody>
					</table>
				</form>
			<?php else : ?>
				<h1><?php esc_html_e( 'Cheating huh ???', 'wk-marketplace' ); ?></h1>
				<p><?php esc_html_e( "Sorry, You can't edit other seller's shipping zone.", 'wk-marketplace' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php

} catch ( \Exception $e ) {
	wc_print_notice( $e->getMessage(), 'error' );
}
