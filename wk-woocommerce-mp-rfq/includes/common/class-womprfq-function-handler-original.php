<?php
/**
 * This file handles hook.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Common;

use wooMarketplaceRFQ\Includes\Admin;
use wooMarketplaceRFQ\Helper;
use WkMarketplace\Helper\Common;
use \WC_Order;
use \WC_Admin_Duplicate_Product;
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

		public function __construct() {
			$this->helper = new Helper\Womprfq_Quote_Handler();
		}

		/**
		 * Returns product template.
		 */
		public function womprfq_get_product_template() {
			$res = array(
				'status' => false,
			);
			if ( check_ajax_referer( 'womprfq_product_ajax_nonce', 'nonce', false ) ) {
				$postdata = $_POST;
				if ( ! empty( $postdata ) ) {
					if ( isset( $postdata['product'] ) && intval( $postdata['product'] ) > 0 && isset( $postdata['variation_id'] ) && intval( $postdata['variation_id'] ) > 0 ) {
						$can_be_quoted = $this->helper->womprfq_product_be_quoted( intval( $postdata['variation_id'] ), get_current_user_id() );
						if ( $can_be_quoted ) {
							$res['template'] = '<button class="wpmp-rfq-button" id="wpmp-rfq-button" data-product="' . esc_html( $postdata['product'] ) . '" data-variation="' . esc_html( $postdata['variation_id'] ) . '">' . esc_html__( 'Request for quote', 'wk-mp-rfq' ) . '</button>';
							$res['status']   = true;
						}
					}
				}
			}
			wp_send_json( $res );
			wp_die();
		}

		/**
		 * Submit quote form.
		 */
		public function womprfq_submit_quotation_form() {
			$res = array(
				'success' => false,
			);
			if ( check_ajax_referer( 'womprfq_product_ajax_nonce', 'nonce', false ) ) {
				$postdata = $_POST;
				if ( ! empty( $postdata ) ) {
					$form_data = (array) json_decode( stripslashes( $postdata['form_data'] ) );
					$result    = array();
					foreach ( $form_data as $form_field ) {
						$result[ $form_field->name ] = $form_field->value;
					}
					if ( isset( $postdata['product'] ) && intval( $postdata['product'] ) > 0 ) {
						$product_id = intval( $postdata['product'] );
						$user_id    = intval( $postdata['user_id'] );
						$product    = wc_get_product( $product_id );
						$var_id     = 0;
						if ( isset( $postdata['variation_id'] ) && intval( $postdata['variation_id'] ) > 0 ) {
							$var_id = intval( $postdata['variation_id'] );
						}
						if ( $product ) {
							if ( ! empty( $result ) && isset( $result['wpmp-rfq-quote-quantity'] ) && intval( $result['wpmp-rfq-quote-quantity'] ) > 0 ) {
								if ( intval( $this->helper->quote_min_qty ) <= intval( $result['wpmp-rfq-quote-quantity'] ) ) {
									$quote_data      = array(
										'product_id'   => $product_id,
										'variation_id' => $var_id,
										'quantity'     => $result['wpmp-rfq-quote-quantity'],
										'customer_id'  => $user_id,
									);
									$result['image'] = $result['wpmp-rfq-form-sample-img'];
									$response        = $this->helper->womprfq_addnew_main_quotation( $quote_data, '', $result );
									if ( $response ) {
										$res['success'] = true;
										wc_add_notice( esc_html__( 'Quotation Added Successfully', 'wk-mp-rfq' ), 'success' );
									}
								} else {
									$res['msg'] = esc_html__( 'Minimum Quantity required for quote is ', 'wk-mp-rfq' ) . intval( $this->helper->quote_min_qty ) . '.';
								}
							}
						}
					}
				}
			}
			wp_send_json( $res );
			wp_die();
		}

		/**
		 * Return quote form data.
		 */
		public function womprfq_return_productdata_quotation_form() {
			$res = array(
				'status'  => false,
				'data'    => array(),
				'message' => '',
			);
			if ( check_ajax_referer( 'womprfq_product_ajax_nonce', 'nonce', false ) ) {
				$postdata = $_POST;
				if ( ! empty( $postdata ) ) {
					$pro_id = 0;
					$var_id = 0;
					$data   = array();
					if ( isset( $postdata['product_id'] ) && intval( $postdata['product_id'] ) > 0 ) {
						$pro_id = intval( $postdata['product_id'] );
						if ( isset( $postdata['variation_id'] ) ) {
							$var_id = intval( $postdata['variation_id'] );
						}

						$product = wc_get_product( $pro_id );
						if ( $product ) {
							if ( $product->get_type() == 'variable' && $var_id > 0 ) {
								$var_product   = wc_get_product( $var_id );
								$can_be_quoted = $this->helper->womprfq_product_be_quoted( intval( $var_product->get_id() ), get_current_user_id() );
								if ( $var_product && $can_be_quoted ) {
									$data['pname'] = $var_product->get_formatted_name();
									// $data['pname'] = $var_product->get_name().' ( '.wc_get_formatted_variation($var_product, true, true, true).' )';
									$img = '';
									if ( $var_product->get_image_id() ) {
										$url = '';
										if ( wp_get_attachment_url( $var_product->get_image_id() ) ) {
											$url = wp_get_attachment_url( $var_product->get_image_id() );
										}
										if ( $url ) {
											$img = '<span class="wpmp-rfq-form-pro-img-wrap"><img src="' . esc_url( $url ) . '" class="wpmp-rfq-form-pro-img"></span>';
										}
									}
									$data['imgdata'] = $img;
								}
							} elseif ( $product->get_type() == 'simple' ) {
								$can_be_quoted = $this->helper->womprfq_product_be_quoted( intval( $product->get_id() ), get_current_user_id() );
								if ( $can_be_quoted ) {
									$data['pname'] = $product->get_formatted_name();
									$img           = '';
									if ( $product->get_image_id() ) {
										$url = '';
										if ( wp_get_attachment_url( $product->get_image_id() ) ) {
											$url = wp_get_attachment_url( $product->get_image_id() );
										}
										if ( $url ) {
											$img = '<span class="wpmp-rfq-form-pro-img-wrap"><img src="' . esc_url( $url ) . '" class="wpmp-rfq-form-pro-img"></span>';
										}
									}
									$data['imgdata'] = $img;
								}
							}
							$data['adminattrdata'] = apply_filters( 'womprfq_add_admin_attribute_template', $product );
							$res                   = array(
								'status' => true,
								'data'   => $data,
							);
						}
					}
				}
			}
			wp_send_json( $res );
			wp_die();
		}

		public function womprfq_add_endpoints() {
			add_rewrite_endpoint( $this->helper->endpoint, EP_ROOT | EP_PAGES );
		}

		public function womprfq_get_admin_attribute_template( $str ) {
			$attr_obj = new Helper\Womprfq_Attribute_Handler();

			$temp = $attr_obj->womprfq_get_attribute_template();

			return $temp;
		}

		public function womprfq_save_quotation_meta_on_create( $response, $result ) {
			if ( ! empty( $result ) ) {
				$res     = array();
				$str_arr = array( 'image', 'pro_name', 'pro_desc' );
				foreach ( $result as $key => $value ) {
					if ( strpos( $key, 'wpmp-rfq-admin-quote-' ) !== false || in_array( $key, $str_arr ) ) {
						$res[ $key ] = $value;
					}
				}
				if ( ! empty( $res ) ) {
					$this->helper->womprfq_add_quote_meta( $res, $response );
				}
			}
		}

		public function womprfq_product_update_after_approval() {
			$res = array(
				'status'  => false,
				'data'    => array(),
				'message' => '',
			);
			if ( check_ajax_referer( 'womprfq_seller_ajax_nonce', 'nonce', false ) ) {
				$postdata = $_POST;
				if ( ! empty( $postdata ) ) {
					if ( isset( $postdata['m_quote_id'] ) && ! empty( $postdata['m_quote_id'] ) && intval( $postdata['m_quote_id'] ) > 0 ) {
						$m_quote_id = intval( $postdata['m_quote_id'] );
					} else {
						$m_quote_id = 0;
					}
					if ( isset( $postdata['s_quote_id'] ) && ! empty( $postdata['s_quote_id'] ) && intval( $postdata['s_quote_id'] ) > 0 ) {
						$s_quote_id = intval( $postdata['s_quote_id'] );
					} else {
						$s_quote_id = 0;
					}

					if ( $m_quote_id != 0 && $s_quote_id != 0 ) {
						$sup_q = $this->helper->womprfq_get_seller_quotation_details( $s_quote_id );
						if ( $sup_q->main_quotation_id == $m_quote_id && $sup_q->status == 3 ) {
							$main_data = $this->helper->womprfq_get_main_quotation_by_id( $m_quote_id );
							if ( $main_data ) {
								$main_pid = 0;
								$main_vid = 0;
								if ( isset( $main_data->product_id ) && intval( $main_data->product_id ) > 0 ) {
									$update_p = false;
									if ( intval( get_post_field( 'post_author', $main_data->product_id ) ) != intval( $sup_q->seller_id ) ) {
										$sel_data = get_post_meta( $main_data->product_id, 'wkmprfq_main_quote_data', true );
										if ( isset( $sel_data[ $sup_q->seller_id ] ) ) {
											$update_p = true;
										}
									} else {
										$update_p = true;
									}
									if ( $update_p ) {
										if ( intval( get_post_field( 'post_author', $main_data->product_id ) ) == intval( $sup_q->seller_id ) ) {
											$main_pid = $main_data->product_id;
											$product  = intval( $main_pid );
											$produt   = wc_get_product( $product );
											$mid      = intval( $main_pid );
											if ( $produt->get_type() == 'simple' ) {
												$q_dta = get_post_meta( $mid, 'wkmprfq_quote_data', true );
												if ( ! is_array( $q_dta ) ) {
													$q_dta = array();
												}
												$q_dta[ $main_data->customer_id ] = array(
													'quantity' => $sup_q->quantity,
													'price' => $sup_q->price,
													'commission' => $sup_q->commission,
													'status' => 0,
												);
												$result                           = add_post_meta( $mid, 'wkmprfq_quote_data', $q_dta );
												$main_pid                         = $mid;
											} else {
												$mproduct = new WC_Product_Variation( $main_data->variation_id );
												$d        = new WC_Product_Data_Store_CPT();
												$main_vid = $d->find_matching_product_variation( $produt, $mproduct->get_variation_attributes() );

												$q_dta = get_post_meta( $main_vid, 'wkmprfq_quote_data', true );
												if ( ! is_array( $q_dta ) ) {
													$q_dta = array();
												}
												$q_dta[ $main_data->customer_id ] = array(
													'quantity' => $sup_q->quantity,
													'price' => $sup_q->price,
													'commission' => $sup_q->commission,
													'status' => 0,
												);
												$result                           = update_post_meta( $main_vid, 'wkmprfq_quote_data', $q_dta );
												$main_pid                         = $mid;
											}
										} else {
											$product_id = $main_data->product_id;
											$meta_data  = get_post_meta( intval( $main_data->product_id ), 'wkmprfq_main_quote_data', true );
											if ( isset( $meta_data[ intval( $sup_q->seller_id ) ] ) ) {
												$product_id = intval( $meta_data[ intval( $sup_q->seller_id ) ] );
											}
											$produt = wc_get_product( $product_id );
											$mid    = $product_id;
											if ( $produt->get_type() == 'simple' ) {
												$q_dta = get_post_meta( $mid, 'wkmprfq_quote_data', true );
												if ( ! is_array( $q_dta ) ) {
													$q_dta = array();
												}
												$q_dta[ $main_data->customer_id ] = array(
													'quantity' => $sup_q->quantity,
													'price' => $sup_q->price,
													'commission' => $sup_q->commission,
													'status' => 0,
												);
												$result                           = add_post_meta( $mid, 'wkmprfq_quote_data', $q_dta );
												$main_pid                         = $mid;
											} else {
												$mproduct = new WC_Product_Variation( $main_data->variation_id );
												$d        = new WC_Product_Data_Store_CPT();
												$main_vid = $d->find_matching_product_variation( $produt, $mproduct->get_variation_attributes() );

												$q_dta = get_post_meta( $main_vid, 'wkmprfq_quote_data', true );
												if ( ! is_array( $q_dta ) ) {
													$q_dta = array();
												}
												$q_dta[ $main_data->customer_id ] = array(
													'quantity' => $sup_q->quantity,
													'price' => $sup_q->price,
													'commission' => $sup_q->commission,
													'status' => 0,
												);
												$result                           = update_post_meta( $main_vid, 'wkmprfq_quote_data', $q_dta );
												$main_pid                         = $mid;
											}
										}
									} else {
										$produt = wc_get_product( $main_data->product_id );
										if ( $produt->get_type() == 'simple' ) {
											$wc_adp  = new WC_Admin_Duplicate_Product();
											$product = $wc_adp->product_duplicate( $produt );
											$base_id = $main_data->product_id;
											$mid     = $product->get_ID();
											$title   = $product->get_title();
											$title   = explode( '(Copy)', $title );
											update_post_meta( $mid, '_manage_stock', 'no' );
											update_post_meta( $mid, '_stock', '' );
											update_post_meta( $mid, '_stock_status', 'instock' );
											wp_update_post(
												array(
													'ID' => $mid,
													'post_title' => $title[0],
													'post_status' => 'Publish',
												)
											);
											// Add data to product
											add_post_meta( $mid, 'rfq_base_product', $base_id );
											$q_dta[ $main_data->customer_id ] = array(
												'quantity' => $sup_q->quantity,
												'price'    => $sup_q->price,
												'commission' => $sup_q->commission,
												'status'   => 0,
											);
											$result                           = add_post_meta( $mid, 'wkmprfq_quote_data', $q_dta );
											$main_pid                         = $mid;
										} else {
											$wc_adp  = new WC_Admin_Duplicate_Product();
											$product = $wc_adp->product_duplicate( $produt );
											$base_id = $main_data->product_id;
											$mid     = $product->get_ID();
											$title   = $product->get_title();
											$title   = explode( '(Copy)', $title );
											update_post_meta( $mid, '_manage_stock', 'no' );
											update_post_meta( $mid, '_stock', '' );
											update_post_meta( $mid, '_stock_status', 'instock' );
											wp_update_post(
												array(
													'ID' => $mid,
													'post_title' => $title[0],
													'post_status' => 'Publish',
												)
											);
											$mproduct = new WC_Product_Variation( $main_data->variation_id );
											$d        = new WC_Product_Data_Store_CPT();
											$main_vid = $d->find_matching_product_variation( $product, $mproduct->get_variation_attributes() );

											// Add data to product
											add_post_meta( $mid, 'rfq_base_product', $base_id );
											$q_dta[ $main_data->customer_id ] = array(
												'quantity' => $sup_q->quantity,
												'price'    => $sup_q->price,
												'commission' => $sup_q->commission,
												'status'   => 0,
											);
											$result                           = add_post_meta( $main_vid, 'wkmprfq_quote_data', $q_dta );
											$main_pid                         = $mid;
										}
										$main_prodct = get_post_meta( $main_data->product_id, 'wkmprfq_main_quote_data', true );
										if ( ! is_array( $main_prodct ) ) {
											$main_prodct = array();
										}
										if ( ! isset( $main_prodct[ intval( $sup_q->seller_id ) ] ) ) {
											$main_prodct[ intval( $sup_q->seller_id ) ] = $mid;
										}
										update_post_meta( $main_data->product_id, 'wkmprfq_main_quote_data', $main_prodct );
									}
								} else {
									$response = $this->helper->womprfq_add_new_quotated_product( $main_data, $sup_q );
									if ( $response['status'] ) {
										$product                          = intval( $response['product_id'] );
										$q_dta[ $main_data->customer_id ] = array(
											'quantity'   => $sup_q->quantity,
											'price'      => $sup_q->price,
											'commission' => $sup_q->commission,
											'status'     => 0,
										);
										$result                           = add_post_meta( $product, 'wkmprfq_quote_data', $q_dta );
										$main_pid                         = $product;
									}
								}
								if ( $result ) {
									// add data on main quote data
									$mn_data = array(
										'quote_product_id' => $main_pid,
										'quote_var_id'     => $main_vid,
									);
									$this->helper->womprfq_add_quote_meta( $mn_data, $main_data->id );
									$res['status'] = true;
									$this->helper->womprfq_update_seller_quotation_status( $sup_q->id, 4 );
									$this->helper->womprfq_update_main_quotation_status( $main_data->id, 2 );

									$smes       = array(
										esc_html__( 'Quotation has been approved by Seller', 'wk-mp-rfq' ) . ' ( #' . intval( $sup_q->id ) . ' ).',
									);
									$sel_q_data = $this->helper->womprfq_get_seller_quotation_details( $sup_q->id );
									if ( $sel_q_data ) {
										$main_dat = $this->helper->womprfq_get_main_quotation_by_id( $sel_q_data->main_quotation_id );
										$smes     = $this->helper->womprfq_get_mail_quotation_detail( $sel_q_data->main_quotation_id, $smes );
										if ( $main_dat ) {
											$user = get_user_by( 'ID', $main_dat->customer_id );
											if ( $user ) {
												$sdata = array(
													'msg' => $smes,
													'sendto' => $user->user_email,
													'heading' => esc_html__( 'Quotation Updated', 'wk-mp-rfq' ),
												);
												do_action( 'womprfq_quotation', $sdata );
											}
										}
									}
								}
							}
						}
					}
				}
			}
			wp_send_json( $res );
			wp_die();
		}

		public function womprfq_add_action_on_checkout_processed( $order_id ) {
			if ( $order_id ) {
				$order = new WC_Order( $order_id );
				if ( $order ) {
					$customer_id = $order->get_customer_id();
					$items       = $order->get_items();
					foreach ( $items as $key => $item ) {
						if ( isset( $item['variation_id'] ) && $item['variation_id'] ) {
							$product_id = $item['variation_id'];
						} else {
							$product_id = $item['product_id'];
						}

						$rfq_dta = get_post_meta( $product_id, 'wkmprfq_quote_data', true );

						if ( isset( $rfq_dta[ $customer_id ] ) ) {
							$quantity = $rfq_dta[ $customer_id ]['quantity'];
							if ( $item['quantity'] == $quantity && $rfq_dta[ $customer_id ]['status'] == 0 ) {
								$rfq_dta[ $customer_id ]['status'] = 1;
								update_post_meta( $product_id, 'wkmprfq_quote_data', $rfq_dta );
							}
						}
					}
				}
			}
		}

		public function womprfq_after_order_completed( $order_id ) {
			if ( $order_id ) {
				$order = new WC_Order( $order_id );
				if ( $order ) {
					$customer_id = intval( $order->get_customer_id() );
					$items       = $order->get_items();
					foreach ( $items as $key => $item ) {
						if ( isset( $item['variation_id'] ) && $item['variation_id'] ) {
							$product_id = $item['variation_id'];
						} else {
							$product_id = $item['product_id'];
						}

						$rfq_dta = get_post_meta( $product_id, 'wkmprfq_quote_data', true );

						if ( isset( $rfq_dta[ $customer_id ] ) ) {
							$quantity = $rfq_dta[ $customer_id ]['quantity'];
							if ( $item['quantity'] == $quantity && $rfq_dta[ $customer_id ]['status'] == 1 ) {
								error_log( print_r( $rfq_dta, true ) );
								unset( $rfq_dta[ $customer_id ] );
								error_log( print_r( $rfq_dta, true ) );
								update_post_meta( $product_id, 'wkmprfq_quote_data', $rfq_dta );
							}
						}
					}
				}
			}
		}

		public function womprfq_after_order_cancel( $order_id ) {
			if ( $order_id ) {
				$order = new WC_Order( $order_id );
				if ( $order ) {
					$customer_id = $order->get_customer_id();
					$items       = $order->get_items();
					foreach ( $items as $key => $item ) {
						if ( isset( $item['variation_id'] ) && $item['variation_id'] ) {
							$product_id = $item['variation_id'];
						} else {
							$product_id = $item['product_id'];
						}

						$rfq_dta = get_post_meta( $product_id, 'wkmprfq_quote_data', true );

						if ( isset( $rfq_dta[ $customer_id ] ) ) {
							$quantity = $rfq_dta[ $customer_id ]['quantity'];
							if ( $item['quantity'] == $quantity && $rfq_dta[ $customer_id ]['status'] == 1 ) {
								$rfq_dta[ $customer_id ]['status'] = 0;
								update_post_meta( $product_id, 'wkmprfq_quote_data', $rfq_dta );
							}
						}
					}
				}
			}
		}

		/**
		 * Add email action
		 *
		 * @param array $actions actions.
		 *
		 * @return array
		 */
		public function womprfq_add_woocommerce_email_actions( $actions ) {
			$actions[] = 'womprfq_quotation';
			return $actions;
		}

		/**
		 * Add mail class
		 *
		 * @param array $email email.
		 *
		 * @return array
		 */
		public function womprfq_add_new_email_notification( $email ) {
			$email['WC_EMAIL_Mp_Rfq_Notification'] = include WK_MP_RFQ_FILE . '/includes/class-wc-email-mp-rfq-notification.php';

			return $email;
		}

		public function womprfq_notify_seller_via_mail() {
			$res = array(
				'status'  => false,
				'message' => '',
			);
			if ( check_ajax_referer( 'womprfq_admin_ajax_nonce', 'nonce', false ) ) {
				$postdata = $_POST;
				if ( ! empty( $postdata ) ) {
					if ( isset( $postdata['q_id'] ) && ! empty( $postdata['q_id'] ) && intval( $postdata['q_id'] ) > 0 ) {
						$m_quote_id = intval( $postdata['q_id'] );
					} else {
						$m_quote_id = 0;
					}
					if ( $m_quote_id != 0 ) {
						$result = $this->helper->womprfq_update_main_quotation_status( $m_quote_id, 1 );
						if ( $result ) {
							$this->helper->womprfq_notify_sellers_for_quote( $this->helper->womprfq_get_main_quotation_by_id( $m_quote_id ), $m_quote_id );
							$res['status'] = true;
						}
					}
				}
			}
			wp_send_json( $res );
			wp_die();
		}


		public function wkmp_add_fee_to_total( $order_id, $total_payment ) {

			if ( is_admin() ) {
				$seller_id = $_GET['seller-id'];
			} else {
				$seller_id = get_current_user_id();
			}
			$commission_db_obj = new Common\WKMP_Commission();

			$mp_order = $commission_db_obj->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			if ( ! empty( $order_id ) ) {
				$order         = wc_get_order( $order_id );
				$total_payment = floatval( preg_replace( '/[^\d.]/', '', $total_payment ) );
				$user_id       = $order->get_user_id();
				$items         = $order->get_items();
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item->get_product_id();

					$variation_id = $item->get_variation_id();
					$id           = $product_id;
					if ( $variation_id ) {
						$id = $variation_id;
					}

					if ( isset( $mp_order['product'][ $id ] ) && ! empty( $mp_order['product'][ $id ] ) ) {
						$commission = $item->get_meta( '_rfq_commission' );
						if ( ! empty( $commission ) ) {
							$total_payment = $total_payment + $commission;
						}
					}
				}
			}

			// if ( ! empty( $order_id ) ) {
			// $order         = wc_get_order( $order_id );
			// $total_payment = floatval( preg_replace( '/[^\d.]/', '', $total_payment ) );

			// echo '<pre>';
			// print_r( $order->get_items( 'fee' ) );
			// echo '<pre>';
			// exit;

			// Iterating through order fee items ONLY
			// foreach ( $order->get_items( 'fee' ) as $item_id => $item_fee ) {

			// $fee_name   = $item_fee->get_name();
			// $commission = $item_fee->get_total();
			// if ( 'Commission' == $fee_name ) {
			// $total_payment = $total_payment + $commission;
			// }
			// }
			// }
			return $total_payment;
		}

		public function wkmp_rfq_add_fee_to_seller_order( $order_id, $cur_symbol ) {

			if ( is_admin() ) {
				$seller_id = $_GET['seller-id'];
			} else {
				$seller_id = get_current_user_id();
			}
			$commission_db_obj = new Common\WKMP_Commission();
			$mp_order          = $commission_db_obj->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			if ( ! empty( $order_id ) ) {
				$order   = wc_get_order( $order_id );
				$user_id = $order->get_user_id();
				$items   = $order->get_items();
				foreach ( $order->get_items() as $item_id => $item ) {

					$product_id = $item->get_product_id();

					$variation_id = $item->get_variation_id();
					$id           = $product_id;
					if ( $variation_id ) {
						$id = $variation_id;
					}

					if ( isset( $mp_order['product'][ $id ] ) && ! empty( $mp_order['product'][ $id ] ) ) {
						$commission = '';
						$commission = $item->get_meta( '_rfq_commission' );

						if ( ! empty( $commission ) ) {
							?>

					<tr class="alt-table-row">
						<th scope="row"><b><?php esc_html_e( 'Commission/Order ', 'wk-marketplace' ); ?></b></th>
						<td class="toptable" colspan="3">
							<span class="amount"><?php echo esc_html( $cur_symbol . $commission ); ?></span>
						</td>
					</tr>
							<?php
						}
					}
				}
			}
		}

		public function wkmp_rfq_add_fee_to_tooltip( $order_id, $tip ) {
			$order_id = $order_id['order_id'];
			if ( is_admin() ) {
				$seller_id = $_GET['seller-id'];
			} else {
				$seller_id = get_current_user_id();
			}
			$commission_db_obj = new Common\WKMP_Commission();

			// $order_id = $mp_order['order_id'];
			$mp_order = $commission_db_obj->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			$order = wc_get_order( $order_id );

			$order_id = $mp_order['order_id'];
			$com      = 0;
			if ( ! empty( $order_id ) ) {
					$order   = wc_get_order( $order_id );
					$user_id = $order->get_user_id();
					$items   = $order->get_items();
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item->get_product_id();

					$variation_id = $item->get_variation_id();
					$id           = $product_id;
					if ( $variation_id ) {
						$id = $variation_id;
					}

					if ( isset( $mp_order['product'][ $id ] ) && ! empty( $mp_order['product'][ $id ] ) ) {

								$commission = $item->get_meta( '_rfq_commission' );
								$commission = $commission + $com;
								$com        = $commission;

					}
				}
				if ( $commission > 0 ) {
							$tip .= ' + ';
							$tip .= ( $commission ) . ' ( ' . __( 'Commission/Order', 'wk-marketplace' ) . ' ) ';
							return $tip;
				}
			}
		}


		public function wkmp_rfq_change_fee_name( $fee_name ) {

			if ( 'Commission' == $fee_name ) {
				$fee_name = 'Commission/Order';
			}
			return $fee_name;
		}

		public function wkmp_rfq_change_fee_amount( $fee_amount, $seller_id, $order_id ) {
			$fee_amount = 0;

			$commission_db_obj = new Common\WKMP_Commission();

			// $order_id = $mp_order['order_id'];
			$mp_order = $commission_db_obj->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			$order = wc_get_order( $order_id );

			$order_id = $mp_order['order_id'];

			if ( ! empty( $order_id ) ) {
					$order   = wc_get_order( $order_id );
					$user_id = $order->get_user_id();
					$items   = $order->get_items();
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item->get_product_id();

					$variation_id = $item->get_variation_id();
					$id           = $product_id;
					if ( $variation_id ) {
						$id = $variation_id;
					}

					if ( isset( $mp_order['product'][ $id ] ) && ! empty( $mp_order['product'][ $id ] ) ) {

							$commission = $item->get_meta( '_rfq_commission' );
						if ( $commission > 0 ) {

							$fee_amount = $commission;
						}
					}
				}
			}
			return $fee_amount;
		}

		public function wkmp_rfq_change_email_fee_amount( $fee_amount, $seller_email, $order_id ) {

			$seller_id = 0;

			if ( ! empty( $seller_email ) ) {
				$seller_user = get_user_by( 'email', $seller_email );
				$seller_id   = ( is_a( $seller_user, 'WP_User' ) ) ? $seller_user->ID : $seller_id;

			}
			// echo $seller_id;exit;

			$fee_amount        = 0;
			$com               = 0;
			$commission_db_obj = new Common\WKMP_Commission();

			// $order_id = $mp_order['order_id'];
			$mp_order = $commission_db_obj->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			$order = wc_get_order( $order_id );

			$order_id = $mp_order['order_id'];

			if ( ! empty( $order_id ) ) {
					$order   = wc_get_order( $order_id );
					$user_id = $order->get_user_id();
					$items   = $order->get_items();
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item->get_product_id();

					$variation_id = $item->get_variation_id();
					$id           = $product_id;
					if ( $variation_id ) {
						$id = $variation_id;
					}

					if ( isset( $mp_order['product'][ $id ] ) && ! empty( $mp_order['product'][ $id ] ) ) {

								$commission = $item->get_meta( '_rfq_commission' );
						if ( $commission > 0 ) {

							$fee_amount = $commission + $com;
							$com        = $fee_amount;
						}
					}
				}
			}
			return $fee_amount;
		}

		public function wkmp_rfq_add_order_list_column_fee( $columns ) {

			return array(
				'cb'                  => '<input type="checkbox" />',
				'order_id'            => esc_html__( 'Order Id', 'wk-marketplace' ),
				'product'             => esc_html__( 'Product', 'wk-marketplace' ),
				'quantity'            => esc_html__( 'Quantity', 'wk-marketplace' ),
				'product_total'       => esc_html__( 'Product Total', 'wk-marketplace' ),
				'shipping'            => esc_html__( 'Shipping', 'wk-marketplace' ),
				'discount'            => esc_html__( 'Discount', 'wk-marketplace' ),
				'total_commission'    => esc_html__( 'Total Commission', 'wk-marketplace' ),
				'order_commission'    => esc_html__( 'Commission/Order', 'wk-marketplace' ),
				'total_seller_amount' => esc_html__( 'Total Seller Amount', 'wk-marketplace' ),
				'action'              => esc_html__( 'Action', 'wk-marketplace' ),
			);
		}


		public function wkmp_rfq_add_order_list_column_fee_data( $column_data ) {

			$order_id         = $column_data['order_id'];
			$order            = wc_get_order( $order_id );
			$currency         = array( 'currency' => $order->get_currency() );
			$order_commission = '';
			$com              = 0;
			if ( is_admin() ) {
				$seller_id = $_GET['seller-id'];
			} else {
				$seller_id = get_current_user_id();
			}
			$commission_db_obj = new Common\WKMP_Commission();
			$mp_order          = $commission_db_obj->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			if ( ! empty( $order_id ) ) {
				$order   = wc_get_order( $order_id );
				$user_id = $order->get_user_id();
				$items   = $order->get_items();
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item->get_product_id();

					$variation_id = $item->get_variation_id();
					$id           = $product_id;
					if ( $variation_id ) {
						$id = $variation_id;
					}
					// echo $id;

					if ( isset( $mp_order['product'][ $id ] ) && ! empty( $mp_order['product'][ $id ] ) ) {

								$order_commission = $item->get_meta( '_rfq_commission' );

						if ( ! empty( $order_commission ) && $order_commission >= 0 ) {
							$com                             = $order_commission + $com;
							$column_data['order_commission'] = wc_price( $com, $currency );
						} else {
							$column_data['order_commission'] = '-';
						}
					}
				}

				return $column_data;
			}
		}

		public function wkmp_rfq_transactions_columns() {
				return array(
					'order_id'         => esc_html__( 'Order Id', 'wk-marketplace' ),
					'product_name'     => esc_html__( 'Product Name', 'wk-marketplace' ),
					'quantity'         => esc_html__( 'Quantity', 'wk-marketplace' ),
					'price'            => esc_html__( 'Total Price', 'wk-marketplace' ),
					'commission'       => esc_html__( 'Commission', 'wk-marketplace' ),
					'order_commission' => esc_html__( 'Commission/Order', 'wk-marketplace' ),
					'subtotal'         => esc_html__( 'Subtotal', 'wk-marketplace' ),
				);
		}

		public function wkmp_rfq_transactions_columns_data( $order_id ) {

			$order            = wc_get_order( $order_id );
			$currency         = array( 'currency' => $order->get_currency() );
			$order_commission = '';
			$com              = 0;
			if ( is_admin() ) {
				$seller_id = $_GET['seller-id'];
			} else {
				$seller_id = get_current_user_id();
			}
			$commission_db_obj = new Common\WKMP_Commission();
			$mp_order          = $commission_db_obj->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			if ( ! empty( $order_id ) ) {
				$order   = wc_get_order( $order_id );
				$user_id = $order->get_user_id();
				$items   = $order->get_items();
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item->get_product_id();

					$variation_id = $item->get_variation_id();
					$id           = $product_id;
					if ( $variation_id ) {
						$id = $variation_id;
					}
					// echo $id;

					if ( isset( $mp_order['product'][ $id ] ) && ! empty( $mp_order['product'][ $id ] ) ) {
								$commission = $item->get_meta( '_rfq_commission' );
								$commission = $commission + $com;
								$com        = $commission;
					}
				}
				if ( ! empty( $commission ) && $commission >= 0 ) {
					?>

					<td>
					<?php echo wp_kses_data( wc_price( $commission, $currency ) ); ?>
						</td>
							<?php
				} else {
					?>

					<td>
						<?php echo '-'; ?>
						</td>
							<?php
				}
					// }
				// }
			}

		}
	}
}
