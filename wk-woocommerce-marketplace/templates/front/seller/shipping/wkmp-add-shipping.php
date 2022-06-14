<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id(); ?>

<div class="woocommerce-account woocommerce">
	<?php do_action( 'mp_get_wc_account_menu' ); ?>
	<div id="main_container" class="woocommerce-MyAccount-content">
	<?php
	$notice_data = get_user_meta( $user_id, '_wkmp_shipping_notice_data', true );

	if ( ! empty( $notice_data['wkmp_ship_action'] ) ) {
		$ship_action = $notice_data['wkmp_ship_action'];
		$message     = wp_sprintf( /* translators: %s: Action */ esc_html__( 'Shipping class has been %s Successfully.', 'wk-marketplace' ), $ship_action );
		wc_print_notice( $message, 'success' );
		delete_user_meta( $user_id, '_wkmp_shipping_notice_data' );
	}
	?>
		<ul class="wkmp_nav_tabs">
			<li><a data-id="#ship_zones" class="active"><?php esc_html_e( 'Shipping Zone', 'wk-marketplace' ); ?></a></li>
			<li><a data-id="#ship_class"><?php esc_html_e( 'Shipping Classes', 'wk-marketplace' ); ?></a></li>
		</ul>

		<div class="wkmp_tab_content">
			<div id="ship_zones" class="wkmp_tab_pane">
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
								<input type="submit" name="save_shipping_details" class="button button-primary wc-shipping-zone-save" value="<?php esc_attr_e( 'Add shipping zone', 'wk-marketplace' ); ?>"/>
							</td>
						</tr>
						</tfoot>
						<tbody class="wc-shipping-zone-rows ui-sortable">
						<tr>
							<td class="wc-shipping-zones-blank-state" colspan="4">
								<p class="main"><?php esc_html_e( 'A shipping zone is a geographic region where a certain set of shipping methods and rates apply.', 'wk-marketplace' ); ?></p>
								<p><?php esc_html_e( 'For example', 'wk-marketplace' ) . ':'; ?></p>
								<ul>
									<li><?php esc_html_e( 'Local Zone = California ZIP 90210 = Local pickup', 'wk-marketplace' ); ?></li>
									<li><?php esc_html_e( 'US Domestic Zone = All US states = Flat rate shipping', 'wk-marketplace' ); ?></li>
									<li><?php esc_html_e( 'Europe Zone = Any country in Europe = Flat rate shipping', 'wk-marketplace' ); ?></li>
								</ul>
								<p><?php esc_html_e( 'Add as many zones as you need – customers will only see the methods available for their address.', 'wk-marketplace' ); ?></p>
							</td>
						</tr>
						<tr>
							<td><label for="mp_zone_name"><?php esc_html_e( 'Zone Name', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label></td>
							<td class="wc-shipping-zone-name">
								<input type="text" name="mp_zone_name" data-attribute="zone_name" placeholder="Zone Name">
							</td>
						</tr>
						<tr>
							<td><label for="mp_zone_region"><?php esc_html_e( 'Zone Region(s)', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label></td>
							<td class="wc-shipping-zone-region">
								<div class="edit">
									<div class="mp_shipping_country">
										<input type="hidden" name="mp_zone_id"/>
										<select multiple="multiple" data-attribute="zone_locations" id="new_zone_locations" name="zone_locations[]" data-placeholder="<?php esc_attr_e( 'Select regions within this zone', 'wk-marketplace' ); ?>" class="wc-shipping-zone-region-select chosen_select">
											<?php
											$zone = new \WC_Shipping_Zone();


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

									<a class="wc-shipping-zone-postcodes-toggle" href="#"><?php esc_html_e( 'Limit to specific ZIP/postcodes', 'wk-marketplace' ); ?></a>
									<div class="wc-shipping-zone-postcodes" style="display:none">
										<textarea name="zone_postcodes" placeholder="List 1 postcode per line" class="input-text large-text" cols="25" rows="5"></textarea>
										<input type="hidden" name="hidden_user" value="<?php echo esc_attr( $user_id ); ?>">
										<span class="description"><?php printf( __( 'Postcodes containing wildcards (e.g. CB23*) or fully numeric ranges (e.g. <code>90210...99000</code>) are also supported. Please see the shipping zones <a href="%s" target="_blank">documentation</a> for more information.', 'wk-marketplace' ), 'https://docs.woocommerce.com/document/setting-up-shipping-zones/#section-3' ); ?></span><?php // @codingStandardsIgnoreLine. ?>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<label><?php esc_html_e( 'Add Shipping Method', 'wk-marketplace' ); ?></label>
							</td>

							<td class="wc-shipping-zone-methods">
								<div>
									<ul>
										<li class="wc-shipping-zone-method"><?php esc_html_e( 'You can add shipping method once you have create shipping zone.', 'wk-marketplace' ); ?></li>
									</ul>
								</div>
							</td>
						</tr>
						</tbody>
					</table>
				</form>
			</div>
			<div class="shipping-container wkmp_tab_pane" id="ship_class">
				<?php
				$shipping_class_columns = apply_filters(
					'woocommerce_shipping_classes_columns',
					array(
						'wc-shipping-class-name'        => __( 'Shipping Class', 'wk-marketplace' ),
						'wc-shipping-class-slug'        => __( 'Slug', 'wk-marketplace' ),
						'wc-shipping-class-description' => __( 'Description', 'wk-marketplace' ),
						'wc-shipping-class-count'       => __( 'Product Count', 'wk-marketplace' ),
						'wc-remove'                     => __( 'Action', 'wk-marketplace' ),
					)
				);
				?>
				<form id="ship_data">
					<h2>
						<?php
						echo wc_help_tip( esc_html__( 'Shipping classes can be used to group products of similar type and can be used by some Shipping Methods (such as Flat Rate Shipping) to provide different rates to different classes of product.', 'wk-marketplace' ) );
						?>
					</h2>

					<table class="wc-shipping-classes widefat">
						<thead>
						<tr>
							<?php foreach ( $shipping_class_columns as $class => $heading ) : ?>
								<th class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $heading ); ?></th>
							<?php endforeach; ?>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="<?php echo absint( count( $shipping_class_columns ) ); ?>">
								<input type="submit" name="wc-shipping-class-save" class="button button-primary wc-shipping-class-save" value="<?php esc_attr_e( 'Save Shipping Classes', 'wk-marketplace' ); ?>" disabled/>
								<a class="button button-secondary wc-shipping-class-add" href="#"><?php esc_html_e( 'Add Shipping Class', 'wk-marketplace' ); ?></a>
								<img class="wp-spin wkmp-spin-loader wkmp_hide" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>">
							</td>
						</tr>
						</tfoot>
						<tbody class="wc-shipping-class-rows">
						<?php
						$ship_class_obj        = new \WC_Shipping();
						$ship_classes          = $ship_class_obj->get_shipping_classes();
						$user_shipping_classes = get_user_meta( $user_id, 'shipping-classes', true );
						if ( ! empty( $user_shipping_classes ) ) :
							$u_shipping_classes = maybe_unserialize( $user_shipping_classes );
							foreach ( $ship_classes as $ship_key => $ship_value ) :
								if ( in_array( $ship_value->term_id, $u_shipping_classes, true ) ) :
									?>
									<tr data-id="<?php echo esc_attr( $ship_value->term_id ); ?>">
										<?php
										foreach ( $shipping_class_columns as $class => $heading ) {
											echo '<td class="' . esc_attr( $class ) . '">';
											switch ( $class ) {
												case 'wc-shipping-class-name':
													?>
													<div class="view">
														<?php echo esc_attr( $ship_value->name ); ?>
														<div class="row-actions">
															<a class="wc-shipping-class-edit" href="#"><?php esc_html_e( 'Edit', 'wk-marketplace' ); ?></a>
														</div>
													</div>
													<div class="edit">
														<input type="hidden" value="<?php echo esc_attr( $ship_value->term_id ); ?>" name="term_id[<?php echo esc_attr( $ship_value->term_id ); ?>]">
														<input type="text" name="name[<?php echo esc_attr( $ship_value->term_id ); ?>]" data-attribute="name" value="<?php echo esc_attr( $ship_value->name ); ?>" placeholder="<?php esc_attr_e( 'Shipping Class Name', 'wk-marketplace' ); ?>"/>
													</div>
													<?php
													break;
												case 'wc-shipping-class-slug':
													?>
													<div class="view"> <?php echo esc_html( $ship_value->slug ); ?> </div>
													<div class="edit">
														<input type="text" name="slug[<?php echo esc_attr( $ship_value->term_id ); ?>]" data-attribute="slug" value="<?php echo esc_attr( $ship_value->slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'wk-marketplace' ); ?>"/>
													</div>
													<?php
													break;
												case 'wc-shipping-class-description':
													?>
													<div class="view"><?php echo esc_html( $ship_value->description ); ?></div>
													<div class="edit">
														<input type="text" name="description[<?php echo esc_attr( $ship_value->term_id ); ?>]" data-attribute="description" value="<?php echo esc_attr( $ship_value->description ); ?>" placeholder="<?php esc_attr_e( 'Description for your reference', 'wk-marketplace' ); ?>"/>
													</div>
													<?php
													break;
												case 'wc-shipping-class-count':
													?>
													<a href="javascript:void(0)"> <?php echo esc_html( $ship_value->count ); ?>  </a>
													<?php
													break;
												case 'wc-remove':
													?>
													<button class="wk_del_ship_class" data-term="<?php echo esc_attr( $ship_value->term_id ); ?>"><?php esc_html_e( 'Delete', 'wk-marketplace' ); ?></button>
													<?php
													break;
												default:
													do_action( 'woocommerce_shipping_classes_column_' . $class );
													break;
											}
											echo '</td>';
										}
										?>
									</tr>
									<?php
								endif;
							endforeach;
						endif;
						?>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
	<style>
		table td, table th {
			padding: 5px;
		}
	</style>
</div>
