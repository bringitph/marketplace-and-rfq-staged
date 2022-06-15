<?php
/**
 * This file handles templates.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Templates\Front\Customer;

use wooMarketplaceRFQ\Helper;
use wooMarketplaceRFQ\Templates\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Customer_Add_New_Product_Quote' ) ) {
	/**
	 * Load hooks.
	 */
	class Womprfq_Customer_Add_New_Product_Quote {

		public $helper;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->helper = new Helper\Womprfq_Quote_Handler();
		}

		/**
		 * Customer template handler
		 */
		public function womprfq_get_customer_new_product_quote_template_handler() {
			$post_data = $_REQUEST;
			do_action( 'wkmprfq_after_customer_new_product_submit_form', $post_data );

			?>
			<div class="wk-mp-rfq-header">
				<h2>
					<?php echo ucfirst( esc_html__( 'Add New Product RFQ', 'wk-mp-rfq' ) ); ?>
				</h2>
			</div>
			<div id="main_container" class="wk_transaction woocommerce-MyAccount-content wk-mp-rfq" style="display: contents;">
				<form method="POST" id="wpmp-rfq-new-quote-form" class="wpmp-rfq-new-quote-form" action="">
					<table class="form-table wc_status_table widefat">
						<tbody>
							<tr valign="top">
								<th>
									<label for="product_name"><?php esc_html_e( 'Product Name', 'wk-mp-rfq' ); ?></label>
							   <span class="required">*</span>
								</th>
								<td class="forminp">

									<input type="text" name="wpmp-rfq-form-product-name" required="required">
								</td>
							</tr>
							<tr valign="top">
								<th>
									<label for="quantity"><?php esc_html_e( 'Enter Quantity', 'wk-mp-rfq' ); ?></label>
								<span class="required">*</span>
								</th>
								<td class="forminp">

									<input type="number" name="wpmp-rfq-quote-quantity" id="wpmp-rfq-quote-quantity" min="1" required="required">
								</td>
							</tr>
							<tr valign="top">
								<th>
									<label for="qdesc"><?php esc_html_e( 'Enter Description', 'wk-mp-rfq' ); ?></label><span class="required">*</span>
								</th>
								<td class="forminp">

								   <textarea rows="6" cols="23" id="wpmp-rfq-quote-desc"  required="required" class="regular-text" name="wpmp-rfq-quote-desc"></textarea>
								   <?php echo wc_help_tip( esc_html__( 'Enter text to add desc to quote.', 'wk-mp-rfq' ), false ); ?>
								</td>
							</tr>
							<tr valign="top">
								<th>
									<label for="qdesc"><?php esc_html_e( 'Add Sample Images', 'wk-mp-rfq' ); ?></label>
								</th>
								<td class="forminp">
								<div id="wpmp-rfq-form-image">
									</div>
									<input type="hidden"  id="wpmp-rfq-form-sample-img" name="wpmp-rfq-form-sample-img" />
									<p>
										<a class="wpmp-rfq-form-upload-button" id="wpmp-rfq-form-upload-button" data-type-error="<?php echo esc_html__( 'Only jpg|png|jpeg files are allowed.', 'wk-mp-rfq' ); ?>" href="javascript:void(0);" />
											<?php esc_html_e( 'Add Images', 'wk-mp-rfq' ); ?>
										</a>
									</p>
									<div id="wpmp-rfq-form-sample-img-error" class="error-class"></div>
								</td>
							</tr>
							<?php
							$attr_obj   = new Helper\Womprfq_Attribute_Handler();
							$attributes = $attr_obj->womprfq_get_attribute_info();
							if ( $attributes ) {
								foreach ( $attributes as $attribute ) {
									if ( $attribute->status == 1 ) {
										if ( $attribute->required == 2 ) {
											$require = 'required="required"';
										} else {
											$require = '';
										}
										?>
										<tr valign="top">
											<th>
												<label for="<?php echo esc_attr( wc_strtolower( $attribute->label ) ); ?>"><?php echo esc_html( ucfirst( $attribute->label ) ); ?></label>
											<?php
											if ( $require ) {
												?>
													<span class="required">*</span>
												<?php
											}
											?>
											</th>
											<td class="forminp">
											<input type="<?php echo esc_html( $attribute->type ); ?>" name="wpmp-rfq-admin-quote-<?php echo esc_attr( wc_strtolower( $attribute->label ) ); ?>" <?php echo esc_html( $require ); ?> >
											<div id="wpmp-rfq-quote-<?php echo esc_attr( wc_strtolower( $attribute->label ) ); ?>-error" class="error-class"></div>
											</td>
										</tr>
										<?php
									}
								}
							}

							?>
							<tr valign="top">
								<td colspan="2" class="forminp">
									<?php wp_nonce_field( 'wc-customer-quote-nonce-action', 'wc-customer-quote-nonce' ); ?>
									<input type="submit" name="update-customer-new-quotation-submit" value="<?php esc_html_e( 'Request for Quote', 'wk-mp-rfq' ); ?>" class="button button-primary" />
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<?php
		}
	}
}
