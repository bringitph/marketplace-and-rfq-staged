<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
?>

<div class="woocommerce-account woocommerce">
	<?php do_action( 'mp_get_wc_account_menu', 'marketplace' ); ?>
	<div class="form woocommerce-MyAccount-content add-product-form">
		<div class="wkmp_container">
			<?php if ( isset( $posted_data['product_cate'] ) && isset( $posted_data['product_type'] ) && isset( $posted_data['add_product_cat_type'] ) ) { ?>
				<form action="<?php echo esc_url( get_permalink() . 'product/edit' ); ?>" method="post" enctype="multipart/form-data" id="product-form">

					<fieldset>
						<div class="wkmp_profile_input">
							<label for="product_name"><?php esc_html_e( 'Product Name', 'wk-marketplace' ); ?><span class="required">*</span></label>
							<input class="wkmp_product_input" type="text" name="product_name" id="product_name" size="54" value=""/>
							<div id="pro_name_error" class="error-class"></div>
						</div>

						<div class="wkmp_profile_input">
							<label for="product_desc"><?php esc_html_e( 'About Product', 'wk-marketplace' ); ?></label>
							<?php
							$settings = array(
								'media_buttons' => true,
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

							$reg_val = '';
							$sel_val = '';

							if ( 'variable' === $posted_data['product_type'] || 'grouped' === $posted_data['product_type'] ) {
								$reg_val = 'disabled';
								$sel_val = 'disabled';
							}
							?>
							<div id="long_desc_error" class="error-class"></div>
						</div>

						<div class="wkmp_profile_input">
							<?php
							$product_cats = isset( $posted_data['product_cate'] ) ? $posted_data['product_cate'] : array();
							$product_cat  = ( count( $product_cats ) > 0 ) ? $product_cats[0] : '-';
							$product_cat  = ( count( $product_cats ) < 2 ) ? $product_cat : implode( ',', $posted_data['product_cate'] );
							?>
							<input type="hidden" name="product_cate" value="<?php echo esc_attr( $product_cat ); ?>">
							<input type="hidden" name="product_type" value="<?php echo esc_attr( $posted_data['product_type'] ); ?>">
						</div>

						<div class="wkmp_profile_input">
							<label for="fileUpload"><?php esc_html_e( 'Product Thumbnail', 'wk-marketplace' ); ?></label>
							<div id="product_image"></div>
							<input type="hidden" id="product_thumb_image_mp" name="product_thumb_image_mp"/>
							<div id="mp-product-thumb-img-div" style="display:inline-block;position:relative;">
								<img style="display:inline;vertical-align:middle;" src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width=50 height=50 data-placeholder-url="<?php echo esc_url( wc_placeholder_img_src() ); ?>"/>
							</div>
							<p>
								<a class="upload mp_product_thumb_image button" data-type-error="<?php esc_attr_e( 'Only jpg|png|jpeg files are allowed.', 'wk-marketplace' ); ?>" href="javascript:void(0);"><?php esc_html_e( 'Upload Thumb', 'wk-marketplace' ); ?></a>
							</p>
						</div>

						<div class=" wkmp_profile_input">
							<label for="product_sku"><?php esc_html_e( 'Product SKU', 'wk-marketplace' ); ?>
								<span class="required">*</span> &nbsp;
								<span class="help">
							<div class="wkmp-help-tip-sol"><?php esc_html_e( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'wk-marketplace' ); ?></div>
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

							<input class="wkmp_product_input" type="text" name="product_sku" id="product_sku" value=""/>
							<div id="pro_sku_error" class="error-class"></div>
						</div>

						<?php
						$prod_type  = empty( $posted_data['product_type'] ) ? 'simple' : $posted_data['product_type'];
						$show_price = ! in_array( $prod_type, array( 'grouped', 'variable' ), true );

						if ( $show_price ) {
							?>
							<div class="wkmp_profile_input">
								<label for="regu_price"><?php esc_html_e( 'Regular Price', 'wk-marketplace' ); ?><span class="required">*</span></label>
								<input class="wkmp_product_input" type="text" name="regu_price" id="regu_price" value=""/>
								<div id="regl_pr_error" class="error-class"></div>
							</div>

							<div class="wkmp_profile_input">
								<label for="sale_price"><?php esc_html_e( 'Sale Price', 'wk-marketplace' ); ?></label>
								<input class="wkmp_product_input" type="text" name="sale_price" id="sale_price" value=""/>
								<div id="sale_pr_error" class="error-class"></div>
							</div>
						<?php } ?>

						<div class="wkmp_profile_input">
							<label for="short_desc"><?php esc_html_e( 'Product Short Description ', 'wk-marketplace' ); ?></label>
							<?php
							$settings = array(
								'media_buttons'    => false, // show insert/upload button(s).
								'textarea_name'    => 'short_desc',
								'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ),
								'tabindex'         => '',
								'editor_class'     => 'frontend',
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

						<div class="wkmp_profile_input">
							<input type="submit" name="add_product_sub" id="add_product_sub" value='<?php esc_attr_e( 'Save', 'wk-marketplace' ); ?>' class="button"/></td>
						</div>
						<?php
						apply_filters( 'mp_user_redirect', 'redirect user' );
						do_action( 'wkmp_after_add_product_form', $posted_data, $this->seller_id );
						?>
					</fieldset>
				</form>
				<?php
			} else {
				if ( isset( $posted_data['add_product_cat_type'] ) ) {
					wc_print_notice( esc_html__( 'Sorry, Firstly select product category(s) and type.', 'wk-marketplace' ), 'error' );
				}
				$mp_product_type       = wc_get_product_types();
				$allowed_product_types = get_option( '_wkmp_seller_allowed_product_types' );
				?>
				<form action="<?php echo esc_url( get_permalink() . get_option( '_wkmp_add_product_endpoint', 'add-product' ) ); ?>" method="post">
					<table style="width:100%">
						<tbody>
						<tr>
							<td>
								<label for="mp_seller_product_categories"><?php esc_html_e( 'Product categories', 'wk-marketplace' ); ?></label>
							</td>
							<td>
								<?php echo str_replace( '<select', '<select  style="width:100%" data-placeholder="' . __( 'Choose category(s)', 'wk-marketplace' ) . '" multiple="multiple" ', $product_categories ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
						<tr>
							<td>
								<label for="product_type"><?php esc_html_e( 'Product Type', 'wk-marketplace' ); ?></label>
							</td>
							<td>
								<select name="product_type" id="product_type" class="mp-toggle-select">
									<?php
									foreach ( $mp_product_type as $key => $pro_type ) {
										if ( $allowed_product_types ) {
											if ( in_array( $key, $allowed_product_types, true ) ) {
												?>
												<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $pro_type ); ?></option>
												<?php
											}
										} else {
											?>
											<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $pro_type ); ?></option>
										<?php } ?>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input type="submit" name="add_product_cat_type" id="add_product_cat_type" value='<?php esc_attr_e( 'Next', 'wk-marketplace' ); ?>' class="button"/>
							</td>
						</tr>
						</tbody>
					</table>
				</form>
			<?php } ?>
		</div><!-- wkmp_container end here-->
	</div><!-- woocommerce-MyAccount-content end here-->
</div><!-- woocommerce-account end here-->
