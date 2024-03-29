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
			//JS edit. Prevent Reply to own RFQ. Step 4
			global $wp_query;
			if ( isset( $wp_query->query_vars['main_page'] ) && 'add-quote' === $wp_query->query_vars['main_page'] ) {
				if ( intval( $data->customer_id ) === get_current_user_id() ) {
					wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
					die;
				}
			}
			$quote_d = $this->helper->womprfq_get_quote_meta_info( $data->id );
			
			?>
			<div class="wk-rfq-main-quote-wrapper">
				<div class="wk-rfq-main-quote">
					<table class="widefat">
						<tbody>
							<?php
							
							//JS edit. Prevent Reply to own RFQ. Step 5 (line deleted)
							
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
									// JS edit: Change header of Quotation ID in Main RFQ Details table visible to Buyer and Seller
									'title' => esc_html__( 'Request', 'wk-mp-rfq' ),
									'value' => '' . intval( $data->id ),
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
							
							// JS edit. Add country and city drop down filter and country preference. Step 7
							$country = WC()->countries->countries[ $quote_d['quotation_country'] ];
							$state = WC()->countries->get_states( $quote_d['quotation_country'] )[$quote_d['quotation_state']];
							if ( isset( $quote_d['quotation_country'] ) ) {
								 $sh_data['country'] = array(
									 // JS edit. Change Country to Deliver To for both buyer and seller Main RFQ table.
									 'title' => esc_html__( 'Deliver To', 'wk-mp-rfq' ),
									 'value' => $country,
								 );
								if ( !empty($state) ) {
									$sh_data['state'] = array(
										 'title' => esc_html__( 'Region/State', 'wk-mp-rfq' ),
										 'value' => $state,
									 );
								}
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
										
										//JS edit. Prevent Reply to own RFQ. Step 6
										if ( 'image' === $key ) {
										
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
												
												//JS edit: Change date format in main RFQ table
 												if($key=="deliverbefore") {
 												$time = strtotime($value);
 												$newformat = date('Y M-d',$time);
 												echo $newformat;    } 
 												else 
												
												echo esc_html( $value );
											?>
										</td>
									</tr>
									<?php
								}
							}
							// JS edit: Add Total Quotes received on Main quotation table
							$user = wp_get_current_user();
							$current_login_user_id = ($user->data->ID);
 							$roles = ( array ) $user->roles;
							 
							$main_creator_ID =  $data->customer_id;
							//condition if its customer or main quotation creator	 
							if($roles[0]=='customer' || ($current_login_user_id == $main_creator_ID) ){
								global $wpdb;							 
								$query1c = $wpdb->prepare( "SELECT count(*) as count FROM ".$wpdb->prefix."womprfq_seller_quotation WHERE main_quotation_id = ".$data->id );						
								$resc    = $wpdb->get_results( $query1c ); 
							
							
							?>
							
							 <tr class="order_item alt-table-row">
								<td class="product-name toptable">
									<strong>
										Offers received 
									</strong>
								</td>
								<td class="product-total toptable">
								 <?php echo $resc[0]->count; ?>
								</td>
							</tr>
							<?php
							}
							/*************Total quotation END***************/
							
							//JS edit. Cancel this request button on Buyers RFQ. Step 1
							if($data->customer_id == get_current_user_id() &&  $data->status != 3 ):
							?> 
								<tr>
									<td colspan="2" id="cancel-button">
										<button class="markasclosed" data-product= "<?= $product; ?>" data-id ="<?= $data->id ; ?>">Cancel</button>
									</td>
								</tr>	
							
							<?php  endif; ?>
							<?php 
							if($data->status == 3) : 
								echo '<tr><td colspan="2">This request is now closed.</td></tr>';
							endif;
							//End 
							 
							?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		}
	}
}
