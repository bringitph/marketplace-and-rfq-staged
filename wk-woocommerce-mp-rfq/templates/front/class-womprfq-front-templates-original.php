<?php
/**
 * This file handles templates.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Templates\Front;

use wooMarketplaceRFQ\Templates\Front;
use wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Front_Templates' ) ) {
	/**
	 * Load hooks.
	 */
	class Womprfq_Front_Templates {

		public $helper;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->helper = new Helper\Womprfq_Quote_Handler();
		}

		public function womprfq_get_customer_template() {
			$cust_obj = new Front\Customer\Womprfq_Customer_Template();
			$cust_obj->womprfq_get_customer_template_handler();
		}

		public function womprfq_get_seller_template() {
			$sel_obj = new Front\Seller\Womprfq_Seller_Template();
			$sel_obj->womprfq_get_seller_template_handler();
		}

		public function womprfq_get_main_quote_template( $data ) {
			?>
			<div class="wk-rfq-main-quote-wrapper">
				<div class="wk-rfq-main-quote">
					<table class="widefat">
						<tbody>
							<?php
							$quote_d = $this->helper->womprfq_get_quote_meta_info( $data->id );
							if ( $data->variation_id != 0 ) {
								$product = get_the_title( $data->variation_id ) . ' ( #' . intval( $data->variation_id ) . ' )';
							} elseif ( $data->variation_id == 0 && $data->product_id != 0 ) {
								$product = get_the_title( $data->product_id ) . ' ( #' . intval( $data->product_id ) . ' )';
							} else {
								if ( isset( $quote_d['pro_name'] ) ) {
									$product = $quote_d['pro_name'];
								}
							}
							$sh_data = array(
								'main_quotation_id' => array(
									'title' => esc_html__( 'Quotatoin ID', 'wk-mp-rfq' ),
									'value' => '#' . intval( $data->id ),
								),
								'product'           => array(
									'title' => esc_html__( 'Product', 'wk-mp-rfq' ),
									'value' => esc_html( $product ),
								),
								'quantity'          => array(
									'title' => esc_html__( 'Quantity', 'wk-mp-rfq' ),
									'value' => intval( $data->quantity ),
								),
							);

							if ( isset( $quote_d['pro_desc'] ) ) {
								 $sh_data['desc'] = array(
									 'title' => esc_html__( 'Product Description', 'wk-mp-rfq' ),
									 'value' => $quote_d['pro_desc'],
								 );
							}
							if ( isset( $quote_d['image'] ) && ! empty( $quote_d['image'] ) ) {
								$sh_data['image'] = array(
									'title' => esc_html__( 'Sample Images', 'wk-mp-rfq' ),
									'value' => $quote_d['image'],
								);
							}

							foreach ( $sh_data as $key => $s_data ) {
								?>
								<tr class="order_item alt-table-row">
									<td class="product-name toptable">
										<strong>
											<?php echo esc_html( $s_data['title'] ); ?>
										</strong>
									</td>
									<td class="product-total toptable">
										<?php
										if ( $key == 'image' ) {
											$img_str = '';
											$imge    = explode( ',', $s_data['value'] );
											if ( $imge ) {
												foreach ( $imge as $imag ) {
													$url = wp_get_attachment_url( $imag );
													if ( $url ) {
														?>
													<span class="wpmp-rfq-form-pro-img-wrap">
														<img src="<?php echo esc_url( $url ); ?>" class="wpmp-rfq-form-pro-img">
													</span>
														<?php
													}
												}
											}
										} else {
											echo esc_html( $s_data['value'] );
										}
										?>
									</td>
								</tr>
								<?php
							}

							$admin_attr = $this->helper->womprfq_get_quote_attribute_data( $data->id );

							if ( ! empty( $admin_attr ) ) {
								foreach ( $admin_attr as $key => $value ) {
									?>
									<tr class="order_item alt-table-row">
										<td class="product-name toptable">
											<strong>
												<?php echo esc_html( str_replace( '_', ' ', ucfirst( $key ) ) ); ?>
											</strong>
										</td>
										<td class="product-total toptable">
											<?php
												echo esc_html( $value );
											?>
										</td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		}
	}
}
