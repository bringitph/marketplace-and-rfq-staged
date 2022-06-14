<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check if product author is same as of logged in user.
if ( $post_row_data && intval( $product_auth ) === get_current_user_id() ) {
	$main_page          = get_query_var( 'main_page' );
	$post_meta_row_data = get_post_meta( $wk_pro_id );
	$meta_arr           = array();

	foreach ( $post_meta_row_data as $key => $value ) {
		$meta_arr[ $key ] = $value[0];
	}

	$product_attributes = get_post_meta( $wk_pro_id, '_product_attributes', true );
	$display_variation  = 'no';

	$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( ! empty( $product_attributes ) ) {
		foreach ( $product_attributes as $variation ) {
			if ( 1 === $variation['is_variation'] ) {
				$display_variation = 'yes';
			}
		}
	}

	$mp_product_types = wc_get_product_types();
	$product          = wc_get_product( $wk_pro_id );
	$thumbnail_img    = wp_get_attachment_image_src( get_post_meta( $wk_pro_id, '_thumbnail_id', true ) );
	$thumbnail_image  = ( is_array( $thumbnail_img ) && count( $thumbnail_img ) > 0 ) ? $thumbnail_img[0] : '';
	?>
	<div class="woocommerce-account woocommerce">
	<?php do_action( 'mp_get_wc_account_menu', 'wk-marketplace' ); ?>
	<div class="form woocommerce-MyAccount-content add-product-form">
		<input type="hidden" name="var_variation_display" id="var_variation_display" value="<?php echo esc_attr( $display_variation ); ?>"/>

		<ul id='edit_product_tab'>
			<li><a id='edit_tab'><?php esc_html_e( 'Edit', 'wk-marketplace' ); ?></a></li>
			<?php
			$show      = '';
			$ship_show = '';
			if ( in_array( $product->get_type(), array( 'grouped', 'external' ), true ) ) {
				$show      = "style='display:none;'";
				$ship_show = "style='display:none;'";
			}

			if ( 'yes' === $meta_arr['_virtual'] ) {
				$ship_show = "style='display:none;'";
			}

			// Set display of inventory Tab.
			$show = apply_filters( 'wkmp_hide_inventory_tab', $show, $product );
			/**
			 * Filter to add Dynamic Tabs in pattern
			 * $tabs[] = array(
			 *    'tab_id'   => Unique Tab Id,
			 *  'tab_name' => Corresponding Tab name to be displayed.
			 * )
			 *
			 * @since  5.0.1
			 *
			 * Corresponding content hook will be generated in Pattern
			 * "wkmp_tab_content_{$tab_id}"
			 */
			$pro_tabs    = apply_filters( 'wkmp_add_tab_after_edit_tab', array(), $product );
			$tab_content = array();
			if ( ! empty( $pro_tabs ) ) {
				foreach ( $pro_tabs as $tab_key => $tab_value ) {
					if ( isset( $tab_value['tab_id'] ) && $tab_value['tab_name'] ) {
						$tab_content[] = $tab_value['tab_id'];
						?>
						<li><a id="<?php echo esc_attr( $tab_value['tab_id'] ); ?>tab"><?php echo esc_html( $tab_value['tab_name'] ); ?></a></li>
						<?php
					}
				}
			}
			?>
			<li <?php echo esc_attr( $show ); ?>><a id='inventorytab'><?php esc_html_e( 'Inventory', 'wk-marketplace' ); ?></a></li>
			<li <?php echo esc_attr( $ship_show ); ?> ><a id='shippingtab'><?php esc_html_e( 'Shipping', 'wk-marketplace' ); ?></a></li>
			<li><a id='linkedtab'><?php esc_html_e( 'Linked Products', 'wk-marketplace' ); ?></a></li>
			<li><a id='attributestab'><?php esc_html_e( 'Attributes', 'wk-marketplace' ); ?></a></li>
			<li style="display:none;"><a id='external_affiliate_tab'><?php esc_html_e( 'External/Affiliate', 'wk-marketplace' ); ?></a></li>
			<li style="display:none;"><a id='avariationtab'><?php esc_html_e( 'Variations', 'wk-marketplace' ); ?></a></li>
			<li><a id='pro_statustab'><?php esc_html_e( 'Product Status', 'wk-marketplace' ); ?></a></li>
			<?php do_action( 'mp_edit_product_tab_links' ); ?>
		</ul>

		<form action="" method="post" enctype="multipart/form-data" id="product-form">
			<div class="wkmp_container form" id="edit_tabwk">
				<div class="wkmp_profile_input">
					<label for="product_type"><?php esc_html_e( 'Product Type', 'wk-marketplace' ) . ':'; ?></label>
					<select name="product_type" id="product_type" class="mp-toggle-select">
						<?php $allowed_product_types = get_option( '_wkmp_seller_allowed_product_types', array() ); ?>
						<?php
						foreach ( $mp_product_types as $key => $pro_type ) {
							if ( in_array( $key, $allowed_product_types, true ) ) {
								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $product->get_type() ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $pro_type ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>

				<div class="wkmp_profile_input">
					<label for="product_name"><?php esc_html_e( 'Product Name', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
					<input class="wkmp_product_input" type="text" name="product_name" id="product_name" size="54" value="<?php echo isset( $post_row_data[0]->post_title ) ? esc_attr( $post_row_data[0]->post_title ) : ''; ?>"/>
					<div id="pro_name_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input" style="display:none">
					<?php
					if ( ! empty( $wk_pro_id ) && ! empty( $main_page ) && 'product' === $main_page ) {
						?>
						<input type="hidden" value="<?php echo esc_attr( $wk_pro_id ); ?>" name="sell_pr_id" id="sell_pr_id"/>
						<input type="hidden" value="<?php echo esc_attr( $product->get_type() ); ?>" name="sell_pr_type" id="sell_pr_type"/>
						<input type="hidden" name="active_product_tab" id="active_product_tab" value="<?php echo isset( $posted_data['active_product_tab'] ) ? esc_attr( $posted_data['active_product_tab'] ) : ''; ?>"/>
					<?php } ?>
				</div>

				<div class="wkmp_profile_input">
					<label for="product_desc"><?php esc_html_e( 'About Product', 'wk-marketplace' ); ?></label>
					<?php
					$settings = array(
						'media_buttons' => true, // show insert/upload button(s).
						'textarea_name' => 'product_desc',
						'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
						'tabindex'      => '',
						'teeny'         => false,
						'dfw'           => false,
						'tinymce'       => true,
						'quicktags'     => false,
					);

					$content = '';

					if ( isset( $post_row_data[0]->post_content ) ) {
						$content = html_entity_decode( $post_row_data[0]->post_content );
					}

					echo wp_editor( $content, 'product_desc', $settings ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<div id="long_desc_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="product_category"><?php esc_html_e( 'Product Category', 'wk-marketplace' ); ?></label>
					<?php
					echo str_replace( '<select', '<select data-placeholder="' . esc_attr__( 'Choose category(s)', 'wk-marketplace' ) . '" multiple="multiple" ', $product_categories ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>

				<div class="wkmp_profile_input">
					<label for="fileUpload"><?php esc_html_e( 'Product Thumbnail', 'wk-marketplace' ); ?></label>
					<?php if ( isset( $meta_arr['image'] ) ) { ?>
						<img src="<?php echo esc_url( $meta_arr['image'] ); ?>" width="50" height="50">
					<?php } ?>
					<div id="product_image"></div>
					<input type="hidden" id="product_thumb_image_mp" name="product_thumb_image_mp" value="<?php echo isset( $meta_arr['_thumbnail_id'] ) ? esc_attr( $meta_arr['_thumbnail_id'] ) : ''; ?>"/>
					<?php
					if ( ! empty( $thumbnail_image ) ) {
						echo '<div id="mp-product-thumb-img-div" style="display:inline-block;position:relative;"><img style="display:inline;vertical-align:middle;" src="' . esc_url( $thumbnail_image ) . '" width=50 height=50 data-placeholder-url="' . esc_url( wc_placeholder_img_src() ) . '" /><span style="right: -20px;top: -12px;" title="' . esc_attr__( 'Remove', 'wk-marketplace' ) . '" class="mp-image-remove-icon">x</span></div>';
					} else {
						echo '<div id="mp-product-thumb-img-div" style="display:inline-block;position:relative;"><img style="display:inline;vertical-align:middle;" src="' . esc_url( wc_placeholder_img_src() ) . '" width=50 height=50 data-placeholder-url="' . esc_url( wc_placeholder_img_src() ) . '" /></div>';
					}
					?>
					<p>
						<a class="upload mp_product_thumb_image button" data-type-error="<?php esc_attr_e( 'Only jpg|png|jpeg files are allowed.', 'wk-marketplace' ); ?>" href="javascript:void(0);"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></a>
					</p>
				</div>

				<div class="wkmp_profile_input">
					<label for="product_sku"><?php esc_html_e( 'Product SKU', 'wk-marketplace' ); ?>
						<span class="required">*</span>: &nbsp;
						<span class="help">
							<div class="wkmp-help-tip-sol">
							<?php esc_html_e( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'wk-marketplace' ); ?>
							</div>
							<span class="help-tip"></span>
						</span>
						<?php
						if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
							?>
							<span class="wkmp-seller-prefix">(<?php echo sprintf( /* Translators: %s: SKU prefix. */ esc_html__( 'Prefix: %s', 'wk-marketplace' ), esc_html( $dynamic_sku_prefix ) ); ?>)</span>
							<?php
						}
						?>
					</label>
					<?php
					$p_sku = isset( $meta_arr['_sku'] ) ? $meta_arr['_sku'] : '';
					if ( empty( $p_sku ) ) {
						echo '<input class="wkmp_product_input" type="text" name="product_sku" id="product_sku" value="" />';
					} else {
						echo '<p>';
						if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
							echo esc_html( $dynamic_sku_prefix );
						}
						echo esc_html( $meta_arr['_sku'] ) . '</p>';
					}
					?>
					<div id="pro_sku_error" class="error-class"></div>
				</div>

				<?php
				$hide_price = in_array( $product->get_type(), array( 'grouped', 'variable' ), true );
				$style      = $hide_price ? 'style=display:none' : '';
				?>
				<div class="wkmp_profile_input" <?php echo esc_attr( $style ); ?> id="regularPrice">
					<label for="regu_price"><?php esc_html_e( 'Regular Price', 'wk-marketplace' ); ?>
						<span class="required">*</span>
					</label>
					<input class="wkmp_product_input" type="text" name="regu_price" id="regu_price" value="<?php echo isset( $meta_arr['_regular_price'] ) ? esc_attr( $meta_arr['_regular_price'] ) : ''; ?>"/>
					<div id="regl_pr_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input" <?php echo esc_attr( $style ); ?> id="salePrice">
					<label for="sale_price"><?php esc_html_e( 'Sale Price', 'wk-marketplace' ); ?></label>
					<input class="wkmp_product_input" type="text" name="sale_price" id="sale_price" value="<?php echo isset( $meta_arr['_sale_price'] ) ? esc_attr( $meta_arr['_sale_price'] ) : ''; ?>"/>
					<div id="sale_pr_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="short_desc"><?php esc_html_e( 'Product Short Description ', 'wk-marketplace' ); ?></label>
					<?php
					$settings = array(
						'media_buttons'    => false, // show insert/upload button(s).
						'textarea_name'    => 'short_desc',
						'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ),
						'tabindex'         => '',
						'editor_class'     => 'backend',
						'teeny'            => false,
						'dfw'              => false,
						'tinymce'          => true,
						'quicktags'        => false,
						'drag_drop_upload' => true,
					);

					$short_content = '';

					if ( isset( $post_row_data[0]->post_excerpt ) ) {
						$short_content = html_entity_decode( $post_row_data[0]->post_excerpt );
					}

					echo wp_editor( $short_content, 'short_desc', $settings ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<div id="short_desc_error" class="error-class"></div>
				</div>
			</div><!-- edit_tabwk end here -->

			<!-- Custom_tab start here -->
			<?php
			if ( ! empty( $tab_content ) ) {
				foreach ( $tab_content as $tab_content_key => $tab_content_value ) {
					?>
					<div class="wkmp_container" id="<?php echo esc_attr( $tab_content_value ); ?>tabwk">
						<?php do_action( "wkmp_tab_content_{$tab_content_value}", $wk_pro_id ); ?>
					</div>
					<?php
				}
			}
			?>
			<!-- Custom_tab end here -->

			<div class="wkmp_container" id="inventorytabwk">
				<div class="wkmp_profile_input">
					<label for="wk-mp-stock"><?php esc_html_e( 'Manage Stock', 'wk-marketplace' ) . '?'; ?></label>
					<p>
						<input type="checkbox" class="wkmp_stock_management" id="wk_stock_management" name="wk_stock_management" value="yes" <?php echo ( 'yes' === $meta_arr['_manage_stock'] ) ? 'checked' : ''; ?>/>
						<label for="wk_stock_management"><?php esc_html_e( 'Enable stock management at product level', 'wk-marketplace' ); ?></label></p>
				</div>

				<?php
				$scss = 'display:none;';
				$css  = 'display:block;';
				if ( 'yes' === $meta_arr['_manage_stock'] ) {
					$scss = 'display:block;';
					$css  = 'display:none;';
				}
				?>
				<div class="wkmp_profile_input" style="<?php echo esc_attr( $scss ); ?>">
					<label for="wk-mp-stock"><?php esc_html_e( 'Stock Qty', 'wk-marketplace' ); ?></label>
					<input type="text" class="wkmp_product_input" placeholder="0" name="wk-mp-stock-qty" id="wk-mp-stock-qty" value="<?php echo isset( $meta_arr['_stock'] ) ? esc_attr( $meta_arr['_stock'] ) : ''; ?>"/>
				</div>

				<div class="wkmp_profile_input" style="<?php echo esc_attr( $scss ); ?>">
					<label for="wk-mp-backorders"><?php esc_html_e( 'Allow Backorders', 'wk-marketplace' ); ?></label>
					<select name="_backorders" id="_backorders" class="form-control">
						<option value="no" <?php echo ( isset( $meta_arr['_backorders'] ) && 'no' === $meta_arr['_backorders'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Do not allow', 'wk-marketplace' ); ?></option>
						<option value="notify" <?php echo ( isset( $meta_arr['_backorders'] ) && 'notify' === $meta_arr['_backorders'] ) ? 'selected="selected"' : ''; ?>> <?php esc_html_e( 'Allow but notify customer', 'wk-marketplace' ); ?></option>
						<option value="yes" <?php echo ( isset( $meta_arr['_backorders'] ) && 'yes' === $meta_arr['_backorders'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Allow', 'wk-marketplace' ); ?></option>
					</select>
				</div>

				<div class="wkmp_profile_input" style="<?php echo esc_attr( $scss ); ?>">
					<label for="wk-mp-stock-threshold"><?php esc_html_e( 'Low stock threshold', 'wk-marketplace' ); ?></label>
					<input type="text" class="wkmp_product_input" placeholder="<?php echo esc_attr__( '0', 'wk-marketplace' ); ?>" name="wk-mp-stock-threshold" id="wk-mp-stock-threshold" value="<?php echo isset( $meta_arr['_low_stock_amount'] ) ? esc_attr( $meta_arr['_low_stock_amount'] ) : ''; ?>"/>
				</div>

				<div class="wkmp_profile_input" style="<?php echo esc_attr( $scss ); ?>">
					<label for="wk-mp-stock"><?php esc_html_e( 'Stock Status', 'wk-marketplace' ); ?></label>
					<select name="_stock_status" id="_stock_status" class="form-control">
						<option value="instock" <?php echo ( isset( $meta_arr['_stock_status'] ) && 'instock' === $meta_arr['_stock_status'] ) ? 'selected="selected"' : ''; ?>> <?php esc_html_e( 'In Stock', 'wk-marketplace' ); ?></option>
						<option value="outofstock" <?php echo ( isset( $meta_arr['_stock_status'] ) && 'outofstock' === $meta_arr['_stock_status'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Out of Stock', 'wk-marketplace' ); ?></option>
					</select>
				</div>

				<div class="wkmp_profile_input">
					<label for="wk-mp-sold-individual"><?php esc_html_e( 'Sold individually', 'wk-marketplace' ); ?></label>
					<p>
						<input type="checkbox" class="wkmp_sold_individual" id="wk_sold_individual" name="wk_sold_individual" value="yes" <?php echo ( isset( $meta_arr['_sold_individually'] ) && 'yes' === $meta_arr['_sold_individually'] ) ? 'checked' : ''; ?>/>
						<label for="wk_sold_individual"><?php esc_html_e( 'Enable this to only allow one of this item to be bought in a single order', 'wk-marketplace' ); ?></label>
					</p>
				</div>
				<?php
				if ( 'grouped' !== $product->get_type() ) {
					$qty_limit         = get_user_meta( $product_auth, '_wkmp_max_product_qty_limit', true );
					$qty_limit         = empty( $qty_limit ) ? get_option( '_wkmp_max_product_qty_limit', 0 ) : $qty_limit;
					$prod_qty_limit    = isset( $meta_arr['_wkmp_max_product_qty_limit'] ) ? $meta_arr['_wkmp_max_product_qty_limit'] : '';
					$sold_individually = $product->get_sold_individually();
					$qty_limit_css     = $sold_individually ? 'style=display:none' : '';
					?>
					<div class="wkmp_profile_input wkmp-max-product-qty-limit" <?php echo esc_attr( $qty_limit_css ); ?>>
						<label for="_wkmp_max_product_qty_limit"><?php echo sprintf( /* Translators: %s: Quantity Limit. */ esc_html__( 'Maximum Purchasable Quantity (Globally set value is: %s)', 'wk-marketplace' ), esc_html( $qty_limit ) ); ?></label>
						<p>
							<input type="number" class="wkmp_product_input" name="_wkmp_max_product_qty_limit" placeholder="<?php esc_attr_e( 'Enter maximum allowed quantity for this product that can be purchased.', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $prod_qty_limit ); ?>"/>
						</p>
					</div>

					<?php
				}
				do_action( 'mp_edit_product_field', $wk_pro_id );
				?>
			</div><!--- inventorytabwk end here -->

			<div class="wkmp_container" id="shippingtabwk">
				<div class="options_group wkmp_profile_input">
					<?php
					if ( wc_product_weight_enabled() ) {
						$this->wkmp_wp_text_input(
							array(
								'id'          => '_weight',
								'label'       => __( 'Weight', 'wk-marketplace' ) . ' (' . get_option( 'woocommerce_weight_unit' ) . ')',
								'placeholder' => wc_format_localized_decimal( 0 ),
								'desc_tip'    => 'true',
								'description' => __( 'Weight in decimal form', 'wk-marketplace' ),
								'type'        => 'text',
								'data_type'   => 'decimal',
								'value'       => esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_weight', true ) ) ),
							),
							$wk_pro_id
						);
					}

					if ( wc_product_dimensions_enabled() ) {
						?>
						<label for="product_length"><?php echo wp_sprintf( /* translators: %s: Dimensions Unit */ esc_html__( 'Dimensions (%s)', 'wk-marketplace' ), esc_attr( get_option( 'woocommerce_dimension_unit', 'cm' ) ) ); ?></label>
						<span class="wrap">
							<input id="product_length" placeholder="<?php esc_attr_e( 'Length', 'wk-marketplace' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_length" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_length', true ) ) ); ?>"/>
							<input placeholder="<?php esc_attr_e( 'Width', 'wk-marketplace' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_width" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_width', true ) ) ); ?>"/>
							<input placeholder="<?php esc_attr_e( 'Height', 'wk-marketplace' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="_height" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_height', true ) ) ); ?>"/>
						</span>
						<?php echo wc_help_tip( esc_html__( 'LxWxH in decimal form', 'wk-marketplace' ) ); ?>
					<?php } ?>
				</div>
				<div class="options_group wkmp_profile_input">
					<?php
					// Shipping Class.
					$classes = get_the_terms( $wk_pro_id, 'product_shipping_class' );

					$current_shipping_class = '';
					if ( $classes && ! is_wp_error( $classes ) ) {
						$current_shipping_class = current( $classes )->term_id;
					}

					$user_shipping_classes = get_user_meta( $product_auth, 'shipping-classes', true );
					$user_shipping_classes = empty( $user_shipping_classes ) ? array() : maybe_unserialize( $user_shipping_classes );

					$args = array(
						'taxonomy'         => 'product_shipping_class',
						'hide_empty'       => 0,
						'show_option_none' => esc_html__( 'No shipping class', 'wk-marketplace' ),
						'name'             => 'product_shipping_class',
						'id'               => 'product_shipping_class',
						'selected'         => $current_shipping_class,
						'class'            => 'select short',
						'include'          => $user_shipping_classes,
					);

					?>
					<label for="product_shipping_class"><?php esc_html_e( 'Shipping class', 'wk-marketplace' ); ?></label>
					<?php wp_dropdown_categories( $args ); ?>
					<?php echo wc_help_tip( esc_html__( 'Shipping classes are used by certain shipping methods to group similar products.', 'wk-marketplace' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php do_action( 'marketplace_product_options_shipping', $wk_pro_id ); ?>
				</div>
			</div><!-- shippingtabwk end here -->

			<div class="wkmp_container" id="linkedtabwk">
				<?php
				if ( $product->is_type( 'grouped' ) ) {
					include __DIR__ . '/wkmp-product-linked.php';
				}
				?>
				<div class="options_group wkmp_profile_input">
					<p class="form-field">
						<label for="upsell_ids"><?php esc_html_e( 'Upsells', 'wk-marketplace' ); ?></label>
						<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="upsell_ids" name="upsell_ids[]" data-placeholder="<?php esc_attr_e( 'Search&hellip;', 'wk-marketplace' ); ?>">
							<?php
							$product_ids = $product->get_upsell_ids( 'edit' );
							foreach ( $product_array as $key => $value ) {
								$item = wc_get_product( $value->ID );
								if ( is_object( $item ) && intval( $wk_pro_id ) !== intval( $value->ID ) ) {
									?>
									<option value="<?php echo esc_attr( $value->ID ); ?>" <?php echo in_array( intval( $value->ID ), $product_ids, true ) ? 'selected' : ''; ?>> <?php echo wp_kses_post( $item->get_formatted_name() ); ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</p>
					<?php if ( ! $product->is_type( 'external' ) && ! $product->is_type( 'grouped' ) ) { ?>
						<p class="form-field hide_if_grouped hide_if_external">
							<label for="crosssell_ids"><?php esc_html_e( 'Cross-sells', 'wk-marketplace' ); ?></label>
							<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="crosssell_ids" name="crosssell_ids[]" data-placeholder="<?php esc_attr_e( 'Search&hellip;', 'wk-marketplace' ); ?>">
								<?php
								$product_ids = $product->get_cross_sell_ids( 'edit' );
								foreach ( $product_array as $key => $value ) {
									$item = wc_get_product( $value->ID );
									if ( is_object( $item ) && intval( $wk_pro_id ) !== intval( $value->ID ) ) {
										?>
										<option value="<?php echo esc_attr( $value->ID ); ?>" <?php echo ( in_array( intval( $value->ID ), $product_ids, true ) ) ? 'selected' : ''; ?>><?php echo wp_kses_post( $item->get_formatted_name() ); ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</p>
					<?php } ?>
				</div>
			</div><!-- linkedtabwk end here -->

			<div class="wkmp_container" id="attributestabwk">
				<div class="input_fields_toolbar">
					<button class="btn btn-success add-variant-attribute"><?php esc_html_e( 'Add an attribute', 'wk-marketplace' ); ?></button>
				</div>
				<div class="wk_marketplace_attributes">
					<?php
					if ( ! empty( $product_attributes ) ) {
						$i = 0;
						foreach ( $product_attributes as $key_at => $proatt ) {
							$optin = $product->get_attribute( $key_at );
							$optin = str_replace( ',', ' |', $optin );
							?>

							<div class="wkmp_attributes">
								<div class="box-header attribute-remove">
									<input type="text" class="mp-attributes-name wkmp_product_input" placeholder="Attribute name" name="pro_att[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( str_replace( '-', ' ', esc_attr( $proatt['name'] ) ) ); ?>"/>
									<input type="text" class="option wkmp_product_input" title="<?php esc_attr_e( 'Attribute value by separating comma eg. a|b|c', 'wk-marketplace' ); ?>" placeholder=" <?php esc_html_e( 'Value eg. a|b|c', 'wk-marketplace' ); ?>" name="pro_att[<?php echo esc_attr( $i ); ?>][value]" value="<?php echo esc_attr( $proatt['value'] ); ?> "/>
									<input type="hidden" name="pro_att[<?php echo esc_attr( $i ); ?>][position]" class="attribute_position" value="<?php echo esc_attr( $proatt['position'] ); ?>"/>
									<span class="mp_actions">
								<button class="mp_attribute_remove btn btn-danger"><?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
							</span>
								</div>

								<div class="box-inside clearfix">
									<div class="wk-mp-attribute-config">
										<div class="checkbox-inline">
											<input type="checkbox" id="is_visible_page[<?php echo esc_attr( $i ); ?>]" class="checkbox" name="pro_att[<?php echo esc_attr( $i ); ?>][is_visible]" value="1" <?php echo ( 1 === intval( $proatt['is_visible'] ) ) ? 'checked' : ''; ?>/>
											<label for="is_visible_page[<?php echo esc_attr( $i ); ?>]"><?php esc_html_e( 'Visible on the product page', 'wk-marketplace' ); ?></label>
										</div>
										<?php if ( $product->is_type( 'variable' ) ) { ?>
											<div class="checkbox-inline">
												<input type="checkbox" class="checkbox" name="pro_att[<?php echo esc_attr( $i ); ?>][is_variation]" value="1" id="used_for_variation[<?php echo esc_attr( $i ); ?>]" <?php echo ( 1 === intval( $proatt['is_variation'] ) ) ? 'checked' : ''; ?>/>
												<label for="used_for_variation[<?php echo esc_attr( $i ); ?>]"><?php esc_html_e( 'Used for variations', 'wk-marketplace' ); ?></label>
											</div>
										<?php } ?>
										<input type="hidden" name="pro_att[<?php echo esc_attr( $i ); ?>][is_taxonomy]" value="<?php echo isset( $proatt['taxonomy'] ) ? esc_attr( $proatt['taxonomy'] ) : ''; ?>"/>
									</div>
									<div class="attribute-options"></div>
								</div>
							</div>
							<?php $i ++; ?>
						<?php } ?>
					<?php } ?>
				</div>
			</div><!--attributestabwk end here -->

			<div class="wkmp_container" id="external_affiliate_tabwk">
				<div class="wkmp_profile_input">
					<label for="product_url"><?php esc_html_e( 'Product URL', 'wk-marketplace' ); ?></label>
					<input class="wkmp_product_input" type="text" name="product_url" id="product_url" size="54" value="<?php echo ( isset( $meta_arr['_product_url'] ) ) ? esc_url( $meta_arr['_product_url'] ) : ''; ?>"/>
					<div id="pro_url_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="button_txt"><?php esc_html_e( 'Button Text', 'wk-marketplace' ); ?></label>
					<input class="wkmp_product_input" type="text" name="button_txt" id="button_txt" size="54" value="<?php echo isset( $meta_arr['_button_text'] ) ? esc_attr( $meta_arr['_button_text'] ) : ''; ?>"/>
					<div id="pro_btn_txt_error" class="error-class"></div>
				</div>
			</div><!--external_affiliate_tabwk end here -->

			<div class="wkmp_container woocommerce" id="avariationtabwk">
				<div id="wkmp_remove_notice_wrap" class="woocommerce-message notice inline wkmp_hide">
				</div>
				<div id="mp_attribute_variations">
					<?php echo $this->wkmp_attributes_variation( $wk_pro_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="input_fields_toolbar_variation">
					<div id="mp-loader"></div>
					<button id="mp_var_attribute_call" class="btn btn-success"><?php esc_html_e( '+ Add Variation', 'wk-marketplace' ); ?></button>
				</div>
			</div><!--avariationtabwk end here -->

			<div class="wkmp_container" id="pro_statustabwk">
				<?php if ( get_option( '_wkmp_allow_seller_to_publish' ) ) { ?>
					<div class="mp-sidebar-container">
						<div class="mp_wk-post-status wkmp-toggle-sidebar">
							<div class="wkmp-status-wrapper">
								<label for="post_status"><?php esc_html_e( 'Product Status: ', 'wk-marketplace' ); ?></label>
								<?php if ( isset( $post_row_data[0]->post_status ) && ! empty( $post_row_data[0]->post_status ) && 'publish' === $post_row_data[0]->post_status ) { ?>
									<span class="mp-toggle-selected-display green"><?php esc_html_e( 'Online', 'wk-marketplace' ); ?></span>
								<?php } else { ?>
									<span class="mp-toggle-selected-display"><?php esc_html_e( 'Draft', 'wk-marketplace' ); ?> </span>
								<?php } ?>
								<a class="mp-toggle-sider-edit label label-success button" href="javascript:void(0);" style="display: inline;"><?php esc_html_e( 'Edit', 'wk-marketplace' ); ?></a>
							</div>
							<div class="wkmp-toggle-select-container mp-hide" style="display: none;">
								<select id="product_post_status" class="wkmp-toggle-select" name="mp_product_status">
									<option value=""><?php esc_html_e( 'Select status', 'wk-marketplace' ); ?></option>
									<option value="publish" <?php echo ( 'publish' === $post_row_data[0]->post_status ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Online', 'wk-marketplace' ); ?></option>
									<option value="draft" <?php echo ( 'draft' === $post_row_data[0]->post_status ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Draft', 'wk-marketplace' ); ?></option>
								</select>
								<a class="mp-toggle-save button" href="javascript:void(0);"><?php esc_html_e( 'OK', 'wk-marketplace' ); ?></a>
								<a class="mp-toggle-cancel button" href="javascript:void(0);"><?php esc_html_e( 'Cancel', 'wk-marketplace' ); ?></a>
							</div>
						</div>
					</div>
				<?php } ?>

				<?php if ( 'simple' === $product->get_type() ) { ?>
					<div class="wkmp-side-head">
						<label class="checkbox-inline">
							<input type="checkbox" id="_ckvirtual" class="wk-dwn-check" name="_virtual" value="yes" <?php echo ( isset( $meta_arr['_virtual'] ) && 'yes' === $meta_arr['_virtual'] ) ? 'checked' : ''; ?>/>&nbsp;&nbsp;
							<?php esc_html_e( 'Virtual', 'wk-marketplace' ); ?>
						</label>
					</div>
					<hr class="mp-section-seperate">
					<!-- downloadable starts -->
					<div class="wkmp-side-head">
						<label class="checkbox-inline">
							<input type="checkbox" id="_ckdownloadable" class="wk-dwn-check" name="_downloadable" value="yes" <?php echo ( isset( $meta_arr['_downloadable'] ) && 'yes' === $meta_arr['_downloadable'] ) ? 'checked' : ''; ?>/>&nbsp;&nbsp;
							<?php esc_html_e( 'Downloadable Product', 'wk-marketplace' ); ?>
						</label>
					</div>

					<div class="wk-mp-side-body" style="display:<?php echo ( isset( $meta_arr['_downloadable'] ) && 'yes' === $meta_arr['_downloadable'] ) ? 'block' : 'none'; ?>">
						<?php $mp_downloadable_files = get_post_meta( $wk_pro_id, '_downloadable_files', true ); ?>
						<div class="form-field downloadable_files">
							<label><?php esc_html_e( 'Downloadable files', 'wk-marketplace' ); ?></label>
							<table class="widefat">
								<thead>
								<tr>
									<th><?php esc_html_e( 'Name', 'wk-marketplace' ); ?></th>
									<th colspan="2"><?php esc_html_e( 'File URL', 'wk-marketplace' ); ?></th>
									<th>&nbsp;</th>
								</tr>
								</thead>
								<tbody>
								<?php
								if ( $mp_downloadable_files ) {
									foreach ( $mp_downloadable_files as $key => $file ) {
										include __DIR__ . '/wkmp-product-download.php';
									}
								}
								?>
								</tbody>
								<tfoot>
								<tr>
									<th colspan="5">
										<a href="#" class="button insert" data-row="
										<?php
										$key  = '';
										$file = array(
											'file' => '',
											'name' => '',
										);
										ob_start();
										include __DIR__ . '/wkmp-product-download.php';
										echo esc_attr( ob_get_clean() );
										?>
										">
											<?php esc_html_e( 'Add File', 'wk-marketplace' ); ?>
										</a>
									</th>
								</tr>
								</tfoot>
							</table>
						</div>
						<?php
						$download_limit  = isset( $meta_arr['_download_limit'] ) ? $meta_arr['_download_limit'] : '';
						$download_expiry = isset( $meta_arr['_download_expiry '] ) ? $meta_arr['_download_expiry '] : '';
						?>
						<p class="form-field _download_limit_field wkmp_profile_input">
							<label for="_download_limit"><?php esc_html_e( 'Download limit', 'wk-marketplace' ); ?></label>
							<input type="number" class="short wkmp_product_input" style="padding: 3px 5px;" name="_download_limit" id="_download_limit" value="<?php echo ( '-1' === $download_limit ) ? '' : esc_attr( $download_limit ); ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'wk-marketplace' ); ?>" step="1" min="0"/>
							<span class="description"><?php esc_html_e( 'Leave blank for unlimited re-downloads.', 'wk-marketplace' ); ?></span>
						</p>

						<p class="form-field _download_expiry_field ">
							<label for="_download_expiry"><?php esc_html_e( 'Download expiry', 'wk-marketplace' ); ?></label>
							<input type="number" class="short wkmp_product_input" style="padding: 3px 5px;" name="_download_expiry" id="_download_expiry" value="<?php echo ( '-1' === $download_expiry ) ? '' : esc_attr( $download_expiry ); ?>" placeholder="<?php esc_attr_e( 'Never', 'wk-marketplace' ); ?>" step="1" min="0"/>
							<span class="description"><?php esc_html_e( 'Enter the number of days before a download link expires, or leave blank.', 'wk-marketplace' ); ?></span>
						</p>
					</div>
				<?php } ?>

				<hr class="mp-section-seperate">
				<!-- downloadable ends -->
				<div class="wkmp-side-head"><label><?php esc_html_e( 'Image Gallery', 'wk-marketplace' ); ?></label></div>
				<div id="wk-mp-product-images">
					<div id="product_images_container">
						<?php
						if ( isset( $meta_arr['_product_image_gallery'] ) && ! empty( $meta_arr['_product_image_gallery'] ) ) {
							$gallery_image_ids = explode( ',', get_post_meta( $wk_pro_id, '_product_image_gallery', true ) );
							foreach ( $gallery_image_ids as $image_id ) {
								$image_url = wp_get_attachment_image_src( $image_id );
								?>
								<div class='mp_pro_image_gallary'><img src='<?php echo esc_url( $image_url[0] ); ?>' width=50 height=50/>
									<a href="javascript:void(0);" id="<?php echo esc_attr( $wk_pro_id . 'i_' . $image_id ); ?>" class="mp-img-delete_gal" title="<?php esc_attr_e( 'Delete image', 'wk-marketplace' ); ?>">
										<?php esc_html_e( 'Delete', 'wk-marketplace' ); ?>
									</a>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
					<div id="handleFileSelectgalaray"></div>
					<input type="hidden" class="wkmp_product_input" name="product_image_Galary_ids" id="product_image_Galary_ids" value="<?php echo isset( $meta_arr['_product_image_gallery'] ) ? esc_attr( $meta_arr['_product_image_gallery'] ) : ''; ?>"/>
				</div>
				<a href="javascript:void(0);" class="add-mp-product-images btn">+ <?php esc_html_e( 'Add product images', 'wk-marketplace' ); ?></a></p>
				<?php wp_nonce_field( 'marketplace-edid_product' ); ?>
			</div><!-- pro_statustabwk end here -->

			<?php do_action( 'mp_edit_product_tabs_content', $wk_pro_id ); ?>
			<br>
			<input type="submit" name="add_product_sub" id="add_product_sub" value="<?php esc_attr_e( 'Update', 'wk-marketplace' ); ?>" class="button"/></td>
		</form>
	</div><!-- add-product-form end here -->
	<?php unset( $_POST ); //phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
<?php } elseif ( empty( $product_auth ) ) { ?>
	<h2> <?php esc_html_e( 'No product found...', 'wk-marketplace' ); ?> </h2>
	<a href="<?php echo esc_url( site_url() . '/' . $this->wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_add_product_endpoint', 'add-product' ) ); ?>"><?php esc_html_e( 'Create New Product', 'wk-marketplace' ); ?></a>
	</div>
<?php } else { ?>
	<div class="woocommerce-account woocommerce">
		<?php do_action( 'mp_get_wc_account_menu', 'wk-marketplace' ); ?>
		<div class="woocommerce-MyAccount-content">
			<div class="woocommerce-Message woocommerce-Message--info woocommerce-error">
				<a class="woocommerce-Button button" href="<?php echo esc_url( site_url( $this->wkmarketplace->seller_page_slug ) . '/' . get_option( '_wkmp_product_list_endpoint', 'product-list' ) ); ?>">
					<?php esc_html_e( 'Go To Products', 'wk-marketplace' ); ?>
				</a>
				<?php esc_html_e( "Sorry, but you can not edit other sellers' product..!", 'wk-marketplace' ); ?>
			</div>
		</div>
	</div>
	<?php
}

