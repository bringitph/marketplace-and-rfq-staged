<?php
/**
 * This file handles hook.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Front;

use wooMarketplaceRFQ\Templates\Front;
use wooMarketplaceRFQ\Helper;
use \WC_Product_Variation;
use \WC_Product_Data_Store_CPT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Function_Handler' ) ) {
	/**
	 * Load Admin side hooks.
	 */
	class Womprfq_Function_Handler {

		private $helper;

		/**
		 * Class constructor.
		 */
		public function __construct() {

			$this->helper = new Helper\Womprfq_Quote_Handler();
		}

		public function womprfq_add_quote_system_btn() {
			global $product;
			if ( is_user_logged_in() && $this->helper->enabled ) {

				$supported_type = array( 'variable', 'simple' );
				if ( in_array( $product->get_type(), $supported_type ) ) {
					?>
					<div class="womp-rfq-quote-wraper" id="womp-rfq-quote-wraper">
					<?php
					if ( $product->get_type() == 'simple' && $this->helper->womprfq_product_be_quoted( intval( $product->get_id() ), get_current_user_id() ) ) {
						?>
							<button class="wpmp-rfq-button" id="wpmp-rfq-button" data-product="<?php echo intval( $product->get_id() ); ?>" data-variation="0">
							<?php esc_html_e( 'Request for quote', 'wk-mp-rfq' ); ?>
							</button>
							<?php
					} else {
						$def_attr = array();
						if ( ! empty( $product->get_default_attributes() ) ) {
							foreach ( $product->get_default_attributes() as $key => $value ) {
								$def_attr[ 'attribute_' . $key ] = $value;
							}
						}
						if ( ! empty( array_keys( $product->get_attributes() ) ) ) {
							foreach ( array_keys( $product->get_attributes() ) as $attr_key ) {
								if ( isset( $_GET[ 'attribute_' . $attr_key ] ) && ! empty( $_GET[ 'attribute_' . $attr_key ] ) ) {
									$def_attr[ 'attribute_' . $attr_key ] = $_GET[ 'attribute_' . $attr_key ];
								}
							}
						}
						if ( ! empty( $def_attr ) ) {
							$d      = new WC_Product_Data_Store_CPT();
							$var_id = $d->find_matching_product_variation( $product, $def_attr );
							if ( $var_id && intval( $var_id ) > 0 ) {
								if ( $this->helper->womprfq_product_be_quoted( intval( $var_id ), get_current_user_id() ) ) {
									?>
									<button class="wpmp-rfq-button" id="wpmp-rfq-button" data-product="<?php echo intval( $product->get_id() ); ?>" data-variation="<?php echo intval( $var_id ); ?>">
										<?php esc_html_e( 'Request for quote', 'wk-mp-rfq' ); ?>
									</button>
										<?php
								}
							}
						}
					}
					?>
						<script id="tmpl-womprfq_popup_template" type="text/html">
							<div class="wpmp-rfq-quote-dialog-box-wrap" id="wpmp-rfq-quote-dialog-box-wrap">
								<div class="wpmp-rfq-quote-dialog-box" id="wpmp-rfq-quote-dialog-box">
									<div class="wpmp-rfq-quote-form-wrapper">
										<span id="wpmp-rfq-quote-form-close"></span>
										<form id="wpmp-rfq-quote-form" class="wpmp-rfq-quote-form" action="">
											<div class="wpmp-rfq-form-header">
												<h2>
												<?php esc_html_e( 'REQUEST FOR QUOTE', 'wk-mp-rfq' ); ?>
												</h2>
											</div>
											<div class="wpmp-rfq-form-body">
												<div class='wpmp-rfq-form-row'>
													<label for="product_name"><?php esc_html_e( 'Product Name', 'wk-mp-rfq' ); ?></label>
													<h5>{{{data.product_name}}}</h5>
												</div>
												<div class='wpmp-rfq-form-row'>
													<label for="quantity"><?php esc_html_e( 'Enter Quantity', 'wk-mp-rfq' ); ?></label>
													<input type="number" name="wpmp-rfq-quote-quantity" id="wpmp-rfq-quote-quantity" min="1" required="required">
													<div id="wpmp-rfq-quote-quantity-error" class="error-class"></div>
												</div>
											<?php if ( ! empty( $product->get_description() ) ) { ?>
													<div class='wpmp-rfq-form-row'>
														<label for="qdesc"><?php esc_html_e( 'Enter Description', 'wk-mp-rfq' ); ?></label>
														<p id="wpmp-rfq-quote-description">
														<?php echo wp_kses_data( $product->get_description() ); ?>
														</p>
														<div id="wpmp-rfq-quote-description-error" class="error-class"></div>
													</div>
												<?php } ?>

												<div class='wpmp-rfq-form-row'>
													<label for="fileUpload"><?php esc_html_e( 'Add Sample Images', 'wk-mp-rfq' ); ?></label>
													<div id="wpmp-rfq-form-image">
														{{{data.image}}}
													</div>
													<input type="hidden"  id="wpmp-rfq-form-sample-img" name="wpmp-rfq-form-sample-img" />
													<p>
														<a class="wpmp-rfq-form-upload-button" id="wpmp-rfq-form-upload-button" data-type-error="<?php echo esc_html__( 'Only jpg|png|jpeg files are allowed.', 'wk-mp-rfq' ); ?>" href="javascript:void(0);" />
														<?php esc_html_e( 'Add Images', 'wk-mp-rfq' ); ?>
														</a>
													</p>
													<div id="wpmp-rfq-form-sample-img-error" class="error-class"></div>
												</div>
												<div class='wpmp-rfq-form-row'>
												{{{data.admin_attribute}}}
												</div>
											</div>
											<hr>
											<div class="wpmp-rfq-form-submit">
												<button type="submit" id="wpmp-rfq-form-quote-submit" name="wpmp-rfq-form-quote-submit">
												<?php esc_html_e( 'Request for Quote', 'wk-mp-rfq' ); ?>
												</button>
												<div class="womprfq-quote-result"></div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</script>
					</div>
					<div class="wk-mp-loader-rfq" id="wk-mp-loader-rfq"><div class="wk-mp-spinner wk-mp-skeleton"><!--////--></div></div>
					<?php
				}
			} else {
				if ( $this->helper->enabled ) {

					?>
						<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '?product_redirect=' . intval( $product->get_id() ) ); ?>">
							<button class="wpmp-rfq-button">
							<?php esc_html_e( 'LOGIN TO REQUEST FOR QUOTATION', 'wk-mp-rfq' ); ?>
							</button>
						</a>
					<?php
				}
			}
		}

		public function womprfq_add_customer_rfq_menu( $menulist ) {
			$logout = $menulist['customer-logout'];
			unset( $menulist['customer-logout'] );
			$menulist[ $this->helper->endpoint ] = esc_html__( 'Quotations', 'wk-mp-rfq' );
			$menulist['customer-logout']         = $logout;
			return $menulist;
		}

		public function womprfq_customer_endpoint_template() {
			$obj = new Front\Womprfq_Front_Templates();
			$obj->womprfq_get_customer_template();
		}

		/**
		 * Add a new seller tab.
		 *
		 * @param array $tabs tablist
		 */
		public function womprfq_add_manage_rfq_menu( $tabs ) {
			global $wpdb, $wkmarketplace;

			$new_tab = array();

			$page_name = $wkmarketplace->seller_page_slug ? $wkmarketplace->seller_page_slug : get_query_var( 'pagename' );

			$new_tab[ '../' . esc_html( $page_name ) . '/manage-rfq' ] = esc_html__( 'Manage RFQ', 'wk-mp-rfq' );

			$tabs += $new_tab;

			return $tabs;
		}

		 /**
		  * Add new query var.
		  *
		  * @param array $vars var
		  *
		  * @return array
		  */
		public function womprfq_add_query_vars( $vars ) {
			$vars[] = $this->helper->endpoint;
			$vars[] = 'main-quote';
			$vars[] = 'seller-quote';
			$vars[] = 'add-quote';
			return $vars;
		}

		/**
		 * Add calling page.
		 *
		 * @param array $tab tab array
		 *
		 * @return void
		 */
		public function womprfq_add_rfq_calling_pages( $tab ) {
			global $wpdb;

			$seller_info = $wpdb->get_var( 'SELECT user_id FROM ' . esc_html( $wpdb->prefix ) . "mpsellerinfo WHERE user_id = '" . get_current_user_id() . "' and seller_value='seller'" );

			$main_page = get_query_var( 'main_page' );

			if ( ! empty( $main_page ) ) {
				$obj = new Front\Seller\Womprfq_Seller_Template();
				if ( ! empty( $main_page ) && $main_page == 'manage-rfq' ) {
					if ( ! $seller_info ) {
						wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
						die;
					}
					add_shortcode( 'marketplace', array( $obj, 'womprfq_manage_rfq_template' ) );
				} elseif ( ! empty( $main_page ) && $main_page == 'edit-rfq' ) {
					if ( ! $seller_info ) {
						wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
						die;
					}
					add_shortcode( 'marketplace', array( $obj, 'womprfq_get_edit_quotation_template' ) );
				} elseif ( ! empty( $main_page ) && $main_page == 'add-quote' ) {
					if ( ! $seller_info ) {
						wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
						die;
					}
					add_shortcode( 'marketplace', array( $obj, 'womprfq_manage_add_seller_quote_template' ) );
				}
			}
		}

		public function wkmprfq_after_customer_submit_form_handle( $postdta, $sid ) {
			if ( $postdta && $sid ) {
				if ( isset( $postdta['update-customer-quotation-submit'] ) ) {
					if ( ! empty( $postdta['wc-customer-quote-nonce'] ) && wp_verify_nonce( wp_unslash( $postdta['wc-customer-quote-nonce'] ), 'wc-customer-quote-nonce-action' ) ) {
						$is_comment = false;
						$sel_q_data = $this->helper->womprfq_get_seller_quotation_details( $sid );
						if ( $sel_q_data && ( $sel_q_data->status < 3 || $sel_q_data->status == 22 ) ) {
							if ( isset( $postdta['seller-quote-comment'] ) && ! empty( $postdta['seller-quote-comment'] ) ) {
								$q_comment  = $postdta['seller-quote-comment'];
								$is_comment = true;
							}
							if ( isset( $postdta['seller-quote-comment-image'] ) && ! empty( $postdta['seller-quote-comment-image'] ) ) {
								$q_comnt_img = $postdta['seller-quote-comment-image'];
							} else {
								$q_comnt_img = null;
							}
							$q_status = false;
							if ( isset( $postdta['seller_quote_accept_status'] ) && ! empty( $postdta['seller_quote_accept_status'] ) ) {
								$q_status = $postdta['seller_quote_accept_status'];
							}
							$save_res = array(
								'status' => false,
								'msg'    => array(
									array(
										'status' => 'error',
										'msg'    => esc_html__( 'Please enter details and than submit.', 'wk-mp-rfq' ),
									),
								),
							);

							if ( $q_status ) {
								$info = array(
									'id'     => $sid,
									'status' => 3,
								);
							} else {
								$info = array(
									'id'     => $sid,
									'status' => 1,
								);
							}
							$save_res = $this->helper->womprfq_update_customer_quotation( $info );

							if ( $save_res['status'] || $is_comment ) {
								if ( $is_comment ) {
									$comment_info = array(
										'seller_quotation_id' => $sid,
										'comment_text' => stripslashes( $q_comment ),
										'sender_id'    => get_current_user_id(),
										'image'        => $q_comnt_img,
									);
									$this->helper->womprfq_update_seller_quotation_comment( $comment_info );
								}

								if ( $q_status ) {
									$smes = array(
										esc_html__( 'Quotation has been accepted by Customer', 'wk-mp-rfq' ) . ' ( #' . intval( $sid ) . ' ).',
									);
								} else {
									$smes = array(
										esc_html__( 'Quotation status has been updated by Customer', 'wk-mp-rfq' ) . ' ( #' . intval( $sid ) . ' ).',
									);
								}

								$smes = $this->helper->womprfq_get_mail_quotation_detail( $sel_q_data->main_quotation_id, $smes );

								if ( $sel_q_data ) {
									$user = get_user_by( 'ID', intval( $sel_q_data->seller_id ) );
									if ( $user ) {
										$sdata = array(
											'msg'     => $smes,
											'sendto'  => $user->user_email,
											'heading' => esc_html__( 'Quotation Updated', 'wk-mp-rfq' ),
										);
										do_action( 'womprfq_quotation', $sdata );
									}
								}
								wc_print_notice( esc_html__( 'Quotation Updated successfully.', 'wk-mp-rfq' ), 'success' );
							} else {
								if ( ! empty( $save_res['msg'] ) ) {
									foreach ( $save_res['msg'] as $msgg ) {
										if ( $msgg['status'] == 'error' ) {
											$stat = 'error';
										} else {
											$stat = 'success';
										}
										wc_print_notice( esc_html( $msgg['msg'] ), $stat );
									}
								}
							}
							if ( $q_status && $save_res['status'] ) {
								$change_status = $this->helper->womprfq_update_other_seller_quotation( $sid );
							}
						}
					}
				}
			}
		}

		public function womprfq_seller_quotation_save_form_handler( $postdta, $sq_id, $action ) {
			if ( $postdta && $sq_id ) {

				global $wpdb, $wkmarketplace;
				$page_name = $wkmarketplace->seller_page_slug ? $wkmarketplace->seller_page_slug : get_query_var( 'pagename' );
				if ( isset( $postdta['update-seller-new-quotation-submit'] ) ) {
					if ( ! empty( $postdta['wc-seller-quote-nonce'] ) && wp_verify_nonce( wp_unslash( $postdta['wc-seller-quote-nonce'] ), 'wc-seller-quote-nonce-action' ) ) {
						$is_comment = false;
						if ( isset( $postdta['seller-quote-comment'] ) && ! empty( $postdta['seller-quote-comment'] ) ) {
							$q_comment  = $postdta['seller-quote-comment'];
							$is_comment = true;
						}
						if ( isset( $postdta['seller-quote-comment-image'] ) && ! empty( $postdta['seller-quote-comment-image'] ) ) {
							$q_comnt_img = $postdta['seller-quote-comment-image'];
						} else {
							$q_comnt_img = null;
						}

						if ( isset( $postdta['seller-quote-quantity'] ) && ! empty( $postdta['seller-quote-quantity'] ) ) {
							$q_quantity = $postdta['seller-quote-quantity'];
						}
						if ( isset( $postdta['seller-quote-price'] ) && ! empty( $postdta['seller-quote-price'] ) ) {
							$q_price = $postdta['seller-quote-price'];
						}
						if ( isset( $postdta['seller-quote-commission'] ) && ! empty( $postdta['seller-quote-commission'] ) ) {
							$q_commission = $postdta['seller-quote-commission'];
						} else {
							$q_commission = 0;
						}
						if ( ! empty( $q_quantity ) && ! empty( $q_price ) ) {
							$is_comment = false;
							if ( isset( $postdta['seller-quote-comment'] ) && ! empty( $postdta['seller-quote-comment'] ) ) {
								$q_comment  = $postdta['seller-quote-comment'];
								$is_comment = true;
							}
							if ( isset( $postdta['seller-quote-comment-image'] ) && ! empty( $postdta['seller-quote-comment-image'] ) ) {
								$q_comnt_img = $postdta['seller-quote-comment-image'];
							} else {
								$q_comnt_img = null;
							}

							$info = array(
								'id'         => $sq_id,
								'price'      => $q_price,
								'commission' => $q_commission,
								'quantity'   => $q_quantity,
								'status'     => 2,
							);

							$save_res = $this->helper->womprfq_update_seller_quotation( $info, $action );

							if ( $save_res['status'] || $is_comment ) {
								if ( $action == 'add' && isset( $save_res['seller_quote_id'] ) && intval( $save_res['seller_quote_id'] ) > 0 ) {
									$sq_id = intval( $save_res['seller_quote_id'] );
								}
								if ( $is_comment ) {
									$comment_info = array(
										'seller_quotation_id' => $sq_id,
										'comment_text' => stripslashes( $q_comment ),
										'sender_id'    => get_current_user_id(),
										'image'        => $q_comnt_img,
									);
									$this->helper->womprfq_update_seller_quotation_comment( $comment_info );
								}
								$smes       = array(
									esc_html__( 'Quotation status has been updated by Seller', 'wk-mp-rfq' ) . ' ( #' . intval( $sq_id ) . ' ).',
								);
								$sel_q_data = $this->helper->womprfq_get_seller_quotation_details( $sq_id );
								if ( $sel_q_data ) {
									$main_dat = $this->helper->womprfq_get_main_quotation_by_id( $sel_q_data->main_quotation_id );
									$smes     = $this->helper->womprfq_get_mail_quotation_detail( $sel_q_data->main_quotation_id, $smes );
									if ( $main_dat ) {
										$user = get_user_by( 'ID', $main_dat->customer_id );
										if ( $user ) {
											$sdata = array(
												'msg'     => $smes,
												'sendto'  => $user->user_email,
												'heading' => esc_html__( 'Quotation Updated', 'wk-mp-rfq' ),
											);
											do_action( 'womprfq_quotation', $sdata );
										}
									}
								}
								if ( $action == 'add' ) {
									wc_add_notice( esc_html__( 'Quotation Added successfully.', 'wk-mp-rfq' ), 'success' );
									wp_safe_redirect( esc_url( site_url( esc_html( $page_name ) . '/edit-rfq/' . intval( $sq_id ) ) ) );
									die;
								}
								wc_print_notice( esc_html__( 'Quotation Updated successfully.', 'wk-mp-rfq' ), 'success' );
							} else {
								if ( ! empty( $save_res['msg'] ) ) {
									foreach ( $save_res['msg'] as $msgg ) {
										if ( $msgg['status'] == 'error' ) {
											$stat = 'error';
										} else {
											$stat = 'success';
										}
										wc_print_notice( esc_html( $msgg['msg'] ), $stat );
									}
								}
							}
						} else {
							wc_print_notice( esc_html__( 'Please Enter Valid Details.', 'wk-mp-rfq' ), 'error' );
						}
					}
				}
			}
		}

		public function wkmprfq_after_customer_new_product_submit_form_handler( $postdta ) {
			if ( $postdta && isset( $postdta['update-customer-new-quotation-submit'] ) ) {
				if ( ! empty( $postdta['wc-customer-quote-nonce'] ) && wp_verify_nonce( wp_unslash( $postdta['wc-customer-quote-nonce'] ), 'wc-customer-quote-nonce-action' ) ) {
					if ( isset( $postdta['wpmp-rfq-form-product-name'] ) && ! empty( $postdta['wpmp-rfq-form-product-name'] ) ) {
						$q_pro_name = wc_clean( wp_unslash( $postdta['wpmp-rfq-form-product-name'] ) );
					} else {
						$q_pro_name = '';
					}
					if ( isset( $postdta['wpmp-rfq-quote-quantity'] ) && ! empty( $postdta['wpmp-rfq-quote-quantity'] ) ) {
						$q_pro_qty = intval( $postdta['wpmp-rfq-quote-quantity'] );
					} else {
						$q_pro_qty = 0;
					}
					if ( isset( $postdta['wpmp-rfq-quote-desc'] ) && ! empty( $postdta['wpmp-rfq-quote-desc'] ) ) {
						$q_pro_desc = wc_clean( wp_unslash( $postdta['wpmp-rfq-quote-desc'] ) );
					} else {
						$q_pro_desc = '';
					}
					if ( isset( $postdta['wpmp-rfq-form-sample-img'] ) && ! empty( $postdta['wpmp-rfq-form-sample-img'] ) ) {
						$q_pro_img = $postdta['wpmp-rfq-form-sample-img'];
					} else {
						$q_pro_img = '';
					}

					$admin_attrs = preg_grep( '/^(.*)wpmp-rfq-admin-quote-(.*)$/', array_keys( $postdta ) );
					$attr        = array();
					if ( $admin_attrs ) {
						foreach ( $admin_attrs as $admin_attr ) {
							if ( isset( $postdta[ $admin_attr ] ) && ! empty( $postdta[ $admin_attr ] ) ) {
								$attr[ $admin_attr ] = $postdta[ $admin_attr ];
							}
						}
					}

					if ( ! empty( $q_pro_name ) && $q_pro_qty > 0 && ! empty( $q_pro_desc ) ) {
						if ( intval( $this->helper->quote_min_qty ) <= $q_pro_qty ) {
							$quote_data       = array(
								'product_id'   => 0,
								'variation_id' => 0,
								'quantity'     => $q_pro_qty,
								'customer_id'  => get_current_user_id(),
							);
							$attr['image']    = $q_pro_img;
							$attr['pro_name'] = $q_pro_name;
							$attr['pro_desc'] = $q_pro_desc;

							$response = $this->helper->womprfq_addnew_main_quotation( $quote_data, '', $attr );
							if ( $response ) {
								wc_add_notice( esc_html__( 'Quotation Added Successfully', 'wk-mp-rfq' ), 'success' );
								wp_safe_redirect( esc_url( wc_get_page_permalink( 'myaccount' ) . '/main-quote/' . intval( $response ) ) );
							}
						} else {
							wc_print_notice( esc_html__( 'Minimum Quantity required for quote is ', 'wk-mp-rfq' ) . intval( $this->helper->quote_min_qty ) . '.', 'error' );
						}
					} else {
						wc_print_notice( esc_html__( 'Please Enter Valid Details.', 'wk-mp-rfq' ), 'error' );
					}
				}
			}
		}

		public function wkmprfq_add_quoted_price_for_product( $cart_object ) {
			if ( is_user_logged_in() ) {
				WC()->session->__unset( 'commission' );
				$user_id    = get_current_user_ID();
				$commission = 0;
				foreach ( $cart_object->cart_contents as $key => $value ) {
					$id = $value['product_id'];
					if ( $value['variation_id'] ) {
						$id = $value['variation_id'];
					}
					$rfq_dta  = get_post_meta( $id, 'wkmprfq_quote_data', true );
					$qproduct = get_post_meta( $id, 'womprfq_created_product', true );
					if ( isset( $rfq_dta[ $user_id ] ) ) {
						$quantity = $rfq_dta[ $user_id ]['quantity'];
						if ( $value['quantity'] == $quantity && $rfq_dta[ $user_id ]['status'] == 0 ) {
							$commission = $commission + $rfq_dta[ $user_id ]['commission'];
							$value['data']->set_price( $rfq_dta[ $user_id ]['price'] );
							WC()->session->set( 'commission', $commission );
							// add_action( 'woocommerce_cart_calculate_fees', array( $this, 'wkmprfq_add_fee_to_cart_product' ), 10, 2 );
						} elseif ( $qproduct ) {
							WC()->cart->remove_cart_item( $key );
							wc_add_notice( esc_html__( 'You can add exact quantity of product.', 'wk-mp-rfq' ), 'error' );
						}
					}
				}
			}
		}

		public function wkmprfq_add_fee_to_cart_product( $cart ) {
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}

			$fee = WC()->session->get( 'commission' );

			if ( ! empty( $fee ) ) {
				WC()->cart->add_fee( __( 'Commission/Order', 'wk-mp-rfq' ), $fee );
			}
		}

		public function wkmprfq_change_quote_product_price_in_mini_cart( $price, $cart_item, $cart_item_key ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_ID();
				if ( ! empty( $cart_item ) ) {
					$id = $cart_item['product_id'];
					if ( $cart_item['variation_id'] ) {
						$id = $cart_item['variation_id'];
					}
					$rfq_dta = get_post_meta( $id, 'wkmprfq_quote_data', true );
					if ( isset( $rfq_dta[ $user_id ] ) ) {
						$quantity = $rfq_dta[ $user_id ]['quantity'];
						if ( $cart_item['quantity'] == $quantity && $rfq_dta[ $user_id ]['status'] == 0 ) {
							$price = wc_price( $rfq_dta[ $user_id ]['price'] );
						}
					}
				}
			}
			return $price;
		}

		public function wkmprfq_validate_add_to_cart_product( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {

			if ( $product_id ) {
				$product_cart_id = WC()->cart->generate_cart_id( $product_id );
				$in_cart         = WC()->cart->find_product_in_cart( $product_cart_id );

				$rfq_dta  = get_post_meta( $product_id, 'wkmprfq_quote_data', true );
				$qproduct = get_post_meta( $product_id, 'womprfq_created_product', true );

				if ( $qproduct ) {
					$passed  = false;
					$user_id = get_current_user_id();
					if ( isset( $rfq_dta[ $user_id ] ) ) {
						if ( intval( $rfq_dta[ $user_id ]['quantity'] ) == $quantity && $rfq_dta[ $user_id ]['status'] == 0 ) {
							$passed = true;
						}
					}
					if ( ! $passed ) {
						wc_add_notice( esc_html__( 'Unable to Add Product to cart', 'wk-mp-rfq' ), 'error' );
					} elseif ( $in_cart && $qproduct ) {
						$error_message = sprintf( __( 'Product is already in cart.', 'wk-mp-rfq' ) );
						wc_add_notice( $error_message, 'error' );
						wp_redirect( wc_get_cart_url() );
						exit;
					} else {
						return true;
					}
				}
			}
			return $passed;
		}

		public function wkmprfq_redirect_to_cart( $url ) {
			if ( empty( $_GET['add-to-cart'] ) || ! is_numeric( $_GET['add-to-cart'] ) ) {
				return false;
			}
			$url = wc_get_cart_url();
			return $url;
		}

		public function wkmprfq_update_cart_validation( $true, $cart_item_key, $values, $quantity ) {
			if ( 0 !== $values['variation_id'] ) {
				$product_id = $values['variation_id'];
			} else {
				$product_id = $values['product_id'];
			}
			$qproduct = get_post_meta( $product_id, 'womprfq_created_product', true );
			if ( $qproduct ) {
					$error_message = sprintf( __( 'You cannot update the Quoted product.', 'wk-mp-rfq' ) );
					wc_add_notice( $error_message, 'error' );
			} else {
				return true;
			}
		}

		public function wkmprfq_change_my_account_redirect_url( $redirect, $user ) {
			if ( isset( $_REQUEST['product_redirect'] ) ) {
				return esc_url( get_permalink( intval( $_REQUEST['product_redirect'] ) ) );
			}
			return $redirect;
		}

		function tdp_add_dealer_caps() {
			// gets the author role
			$role = get_role( 'customer' );
			$role->add_cap( 'upload_files', true );
		}

		public function wkmp_add_order_item_meta( $item, $cart_item_key, $values, $order ) {

			$prod_id = isset( $values['product_id'] ) ? $values['product_id'] : 0;
			if ( $prod_id > 0 ) {
				$user_id = get_current_user_id();

				$product_id = $item->get_product_id();

				$variation_id = $item->get_variation_id();
				$id           = $product_id;

				if ( $variation_id ) {
					$id = $variation_id;
				}

				$rfq_dta = get_post_meta( $id, 'wkmprfq_quote_data', true );

				if ( isset( $rfq_dta[ $user_id ] ) ) {
					$quantity      = $rfq_dta[ $user_id ]['quantity'];
					$quantityorder = $values['quantity'];
					if ( $quantityorder == $quantity ) {
						$commission = $rfq_dta[ $user_id ]['commission'];
						if ( ! empty( $commission ) ) {
							$item->update_meta_data( '_rfq_commission', $commission );
						}
					}
				}
			}

		}
	}
}
