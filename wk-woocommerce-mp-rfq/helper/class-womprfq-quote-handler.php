<?php

/**
 * Account file.
 */

namespace wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use \WC_Product;

if ( ! class_exists( 'Womprfq_Quote_Handler' ) ) {
	/**
	 * Class for handle quote.
	 */
	class Womprfq_Quote_Handler {

		protected $wpdb;

		protected $main_quote_table;

		protected $main_quote_meta_table;

		protected $seller_quote_table;

		protected $seller_quote_comment_table;

		protected $seller_quotation_comment_table;

		public $enabled;

		public $quote_min_qty;

		public $admin_approval;

		public $endpoint;
		/**
		 * Class constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb                           = $wpdb;
			$this->endpoint                       = 'rfq';
			$this->posts                          = $wpdb->posts;
			$this->main_quote_table               = $wpdb->prefix . 'womprfq_main_quotation';
			$this->main_quote_meta_table          = $wpdb->prefix . 'womprfq_main_quotation_meta';
			$this->seller_quote_comment_table     = $wpdb->prefix . 'womprfq_seller_quotation_comment';
			$this->seller_quote_table             = $wpdb->prefix . 'womprfq_seller_quotation';
			$this->seller_quotation_comment_table = $wpdb->prefix . 'womprfq_seller_quotation_comment';

			if ( get_option( 'womprfq_status', true ) && intval( get_option( 'womprfq_status', true ) ) == 2 ) {
				$this->enabled = true;
			} else {
				$this->enabled = false;
			}

			$this->quote_min_qty = get_option( 'womprfq_minimum_quantity', true );

			if ( get_option( 'womprfq_approval_require', true ) && intval( get_option( 'womprfq_approval_require', true ) ) == 2 ) {
				$this->admin_approval = true;
			} else {
				$this->admin_approval = false;
			}
		}

		public function wpmprfq_check_if_seller_page() {
			global $wp_query, $wkmarketplace;
			$res       = false;
			$page_name = $wkmarketplace->seller_page_slug ? $wkmarketplace->seller_page_slug : get_query_var( 'pagename' );

			if ( get_query_var( 'pagename' ) == $page_name ) {
				$res = true;
			}
			return $res;
		}

		/**
		 * Returns quote list
		 */
		public function womprfq_get_all_main_quotation_list( $search ) {
			$query = "SELECT * FROM $this->main_quote_table";

			if ( $search ) {
				$query .= " WHERE id = $search";
			}

			$res = $this->wpdb->get_results( $query );
			if ( $res ) {
				return $res;
			} else {
				return false;
			}
		}

		/**
		 * Insert main quotation
		 */
		public function womprfq_addnew_main_quotation( $data, $order_id, $attr ) {
			$response = false;
			if ( $data ) {
				if ( $this->admin_approval ) {
					$status = 0;
				} else {
					$status = 1;
				}
				if ( $order_id ) {
					$sql = $this->wpdb->insert(
						$this->main_quote_table,
						array(
							'product_id'   => intval( $data['product_id'] ),
							'variation_id' => intval( $data['variation_id'] ),
							'quantity'     => intval( $data['quantity'] ),
							'customer_id'  => intval( $data['customer_id'] ),
							'order_id'     => intval( $order_id ),
							'status'       => intval( $status ),
						)
					);
				} else {
					$sql = $this->wpdb->insert(
						$this->main_quote_table,
						array(
							'product_id'   => intval( $data['product_id'] ),
							'variation_id' => intval( $data['variation_id'] ),
							'customer_id'  => intval( $data['customer_id'] ),
							'quantity'     => intval( $data['quantity'] ),
							'status'       => intval( $status ),
						)
					);
				}
				if ( $sql ) {
					$id = $this->wpdb->insert_id;
					do_action( 'womprfq_save_quotation_meta', $id, $attr );
					// to customer
					$cmes  = array(
						//JS edit. New Emails - part 1.1
						'<p>Thanks for submitting your shopping request! </p>
						You\'ll receive an email when you get offers from Personal Shoppers. You can also view the offers sent to you anytime by logging in to your <a href="'.home_url().'/my-account/rfq">Buyer dashboard > Shopping Requests</a>. Best of luck.'
					);
					//JS edit. New Emails - part 1.2
					$cmes  = $this->womprfq_get_mail_quotation_detail_customer($id, $cmes, $data);
					
					$cdata = array(
						'msg'     => $cmes,
						'sendto'  => get_user_by( 'ID', $data['customer_id'] )->user_email,
						//JS edit. New Emails - part 1.3
						'heading' => esc_html__( 'You\'ve submitted a new request', 'wk-mp-rfq' ),
						'subject' => esc_html__('Bringit: Your Shopping Request', 'wk-mp-rfq'),
					);
					do_action( 'womprfq_quotation', $cdata );

					// to seller
					if ( ! $this->admin_approval ) {
						$this->womprfq_notify_sellers_for_quote( $this->womprfq_get_main_quotation_by_id( $id ), $id );
					}

					// to admin
					$ames  = array(
						//JS edit. New Emails - part 1.4
						esc_html__( 'A new shopping request has been submitted by ', 'wk-mp-rfq' ) . get_user_by( 'ID', $data['customer_id'] )->user_login . 
						'.<p>Deliver to: </p>
						<p>Item: </p>
						<p><a href="' . home_url() . '/">Bringit.ph</a><br/><br/></p>',
						
					);
					$ames  = $this->womprfq_get_mail_quotation_detail( $id, $ames );
					$adata = array(
						'msg'     => $ames,
						'sendto'  => get_option( 'admin_email' ),
						'heading' => esc_html__( 'New Request For Quotation', 'wk-mp-rfq' ),
					);
					do_action( 'womprfq_quotation', $adata );

					return $id;
				}
			}

			return $response;
		}

		public function womprfq_get_mail_quotation_detail( $qid, $res ) {
			if ( intval( $qid ) > 0 ) {
				$data  = $this->womprfq_get_main_quotation_by_id( $qid );
				$mdata = (object) $this->womprfq_get_quote_meta_info( $qid );

				if ( $data ) {
					if ( $data->product_id != 0 ) {
						if ( $data->variation_id != 0 ) {
							$product_name = get_the_title( $data->variation_id );
						} else {
							$product_name = get_the_title( $data->product_id );
						}
					} else {
						$product_name = $mdata->pro_name;
					}
					$quantity = $data->quantity;

					//JS edit. New Emails - part 1.5
					//$res[] = esc_html__( 'Please find the following details :', 'wk-mp-rfq' );
					//$res[] = esc_html__( 'Requested Quote Topic : ', 'wk-mp-rfq' ) . esc_html( $product_name );
					//$res[] = esc_html__( 'Bulk Quantity Requested : ', 'wk-mp-rfq' ) . esc_html( $quantity );
				}
				
				//JS edit. Add country and city drop down filter and country preference. Step 1
				//JS edit. New Emails - part 1.6
				//$res[] = esc_html__( 'Deliver to: ', 'wk-mp-rfq' ) . esc_html( WC()->countries->countries[ $mdata->quotation_country ] );
				//$res[] = '<p></p>';
				//$res[] = '<p><a href="' . home_url() . '/">Bringit.ph</a></p>';
				//$res[] = '<p>&nbsp;</p>';
	
			}
			return $res;
		}

		public function womprfq_get_mail_quotation_detail_customer($qid, $res, $data)
		{
			if (intval($qid) > 0) {
				$data  = $this->womprfq_get_main_quotation_by_id($qid);
				$mdata = (object) $this->womprfq_get_quote_meta_info($qid);

				$array_mdata = (array) $mdata;

				$delivery_location  = $array_mdata['wpmp-rfq-admin-quote-your_location_(city_and_country)'];
				$customer = get_userdata($data->customer_id);


				if ($data) {
					if ($data->product_id != 0) {
						if ($data->variation_id != 0) {
							$product_name = get_the_title($data->variation_id);
						} else {
							$product_name = get_the_title($data->product_id);
						}
					} else {
						$product_name = $mdata->pro_name;
					}
					$quantity = $data->quantity;

					$res[] = '<table style="padding-bottom:20px;width:100%;">
						<tbody>
							<tr>
								<td align="center" bgcolor="#ffffff" height="1" style="padding:30px 40px 5px" valign="top" width="100%">
									<table cellpadding="0" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td style="border-top:1px solid #e4e4e4"> </td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td class="content" style="padding:10px 0px 0px 40px">
									<p>Request #' . $qid . '</p>
									<p>Item: ' . $product_name . '</p>
									<p>Buyer: ' . $customer->data->user_login . '</p>
								    <p>Deliver to: ' . esc_html( WC()->countries->countries[ $mdata->quotation_country ] ) . '</p>
								</td>
							</tr>  
							<tr>
								<td align="center" bgcolor="#ffffff" height="1" valign="top" width="100%">
									<table cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;">
										<tbody>
										<tr>
											<td style="text-align:center;" >
												<a href="' . home_url() . '/my-account/main-quote/' . $qid . '" style="color: #ffffff;background-color:#eb9a72;display:inline-block;font-size:16px;line-height:30px;text-align:center;text-decoration:none;padding:5px 20px;border-radius:3px; text-transform:none; margin:0 auto;margin-bottom:10px;margin-top:15px" class="link__btn">View your request</a> 
											</td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr> 
							<tr>
								<td align="center" bgcolor="#ffffff" height="1" style="padding:20px 40px 5px" valign="top" width="100%">
									<table cellpadding="0" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td style="border-top:1px solid #e4e4e4"> </td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table> ';
					$res[] = '<p>Need to change your request? <a href="' . home_url() . '/changing-your-shopping-request">Find out how</a>.</p>';
					$res[] = '<p>You can also read our <a href="' . home_url() . '/category/for-buyers">Tips and Guides</a> for more info, including how we keep your payment safe.</p>';
					$res[] = '<p>&nbsp;</p>';
				}
			}
			return $res;
		}

		public function womprfq_get_mail_quotation_detail_seller($qid, $res)
		{
			if (intval($qid) > 0) {
				$data  = $this->womprfq_get_main_quotation_by_id($qid);
				$mdata = (object) $this->womprfq_get_quote_meta_info($qid);

				if ($data) {
					if ($data->product_id != 0) {
						if ($data->variation_id != 0) {
							$product_name = get_the_title($data->variation_id);
						} else {
							$product_name = get_the_title($data->product_id);
						}
					} else {
						$product_name = $mdata->pro_name;
					}
					$quantity = $data->quantity;

					$res[] = 'Please find the following details :';
					$res[] = 'Requested Quote Topic : ' .  $product_name;
					$res[] = 'Bulk Quantity Requested : ' . $quantity;
					$res[] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. ';
					$res[] = 'Phasellus vel velit pulvinar, posuere nulla quis, faucibus orci. Nulla id diam non nibh pulvinar suscipit.';
				}
			}
			return $res;
		}
		

		public function womprfq_notify_sellers_for_quote( $qdata, $qid ) {
			
			//JS edit. New Emails - part 1.7
			global $woocommerce;
			
			// JS edit. Add country and city drop down filter and country preference. Step 2
			$q_meta_data = $this->womprfq_get_quote_meta_info( $qid );
			$customer_id = intval( $qdata->customer_id );
			$users       = get_users(
				array(
					'role'    => 'wk_marketplace_seller',
					'exclude' => array( $customer_id ),
					
					// JS edit. Add country and city drop down filter and country preference. Step 3
					'meta_query' => array(
						'key'     => 'subscribe_country',
						'value'   => $q_meta_data['quotation_country'],
						'compare' => '='
					)
				)
			);
			foreach ( $users as $user ) {
				
				// JS edit. Add country and city drop down filter and country preference. Step 4
				$subscribe_country = get_user_meta($user->ID, 'subscribe_country', true );
				if($subscribe_country == "all"){
				
				if ( $user->user_email && ( $user->ID != $customer_id ) ) {
					$smes  = array(
						
						//JS edit. New Emails - part 1.8
						'<p>Hi ' . $user->user_login . ',</p>',
						'<p>A new shopping  request has been submitted by ' . get_user_by('ID', $customer_id)->user_login . '.</p>',
						'<p>If you\'re traveling to their location, why not make an offer to earn some cash?</p>',
						//JS edit. New RFQ email to sellers - Negotiate date
						'<p>Try to negotiate the delivery or meet-up date to match your travel plans.</p>',
						
					);
					
					//JS edit. New Emails - part 1.9
					$mdata =  (array) $this->womprfq_get_quote_meta_info($qid);

					$customer = get_userdata($customer_id);

					if ($qdata->product_id != 0) {
						if ($qdata->variation_id != 0) {
							$product_name = get_the_title($qdata->variation_id);
						} else {
							$product_name = get_the_title($qdata->product_id);
						}
					} else {
						$product_name = $mdata['pro_name'];
					}
					$quantity = $qdata->quantity;

					$delivery_location  = $mdata['wpmp-rfq-admin-quote-your_location_(city_and_country)'];

					$smes[] = '<table style="padding-bottom:20px;width:100%;">
					<tbody>
						<tr>
							<td align="center" bgcolor="#ffffff" height="1" style="padding:30px 40px 5px" valign="top" width="100%">
								<table cellpadding="0" cellspacing="0" width="100%">
									<tbody>
										<tr>
											<td style="border-top:1px solid #e4e4e4"> </td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td class="content" style="padding:10px 0px 0px 40px">
								<p>Request #' . $qid . '</p>
								<p>Item: ' . $product_name . '</p>
								<p>Quantity: ' . $quantity . '</p>
								<p>Buyer: ' . $customer->data->user_login . '</p>
								<p>Deliver to: ' . esc_html( WC()->countries->countries[ $mdata->quotation_country ] ) . '</p>
							</td>
						</tr>  
						<tr>
							<td align="center" bgcolor="#ffffff" height="1" valign="top" width="100%">
								<table cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;">
									<tbody>
									<tr>
										<td style="text-align:center;" >
											<a href="' . home_url() . '/seller/add-quote/' . $qid . '" style="color: #ffffff;background-color:#eb9a72;display:inline-block;font-size:16px;line-height:30px;text-align:center;text-decoration:none;padding:5px 20px;border-radius:3px; text-transform:none; margin:0 auto;margin-bottom:10px;margin-top:15px" class="link__btn">View or Make an Offer</a> 
										</td>
									</tr>
									</tbody>
								</table>
							</td>
						</tr> 
						<tr>
							<td align="center" bgcolor="#ffffff" height="1" style="padding:20px 40px 5px" valign="top" width="100%">
								<table cellpadding="0" cellspacing="0" width="100%">
									<tbody>
										<tr>
											<td style="border-top:1px solid #e4e4e4"> </td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table> ';
					$smes[] = '<p>Be quick! If the Buyer accepts an offer from another Personal Shopper, the request will move to Closed.</p>';
					$smes[] = '<p>Read our <a href="' . home_url() . '/category/for-personal-shoppers"><font color="#eb9a72">Tips and Guides</font></a> for more info, including how to propose a Tip for your service.</p>';
					$smes[] = '<p>&nbsp;</p>';					
					
					
					$sdata = array(
						
						//JS edit. New Emails - part 1.10
						'msg'     => join('', $smes),
						
						'sendto'  => $user->user_email,
						
						//JS edit. New Emails - part 1.11
						'heading' => esc_html__('Is it time to earn?', 'wk-mp-rfq'),
						'subject' => esc_html__('Bringit: New shopping request', 'wk-mp-rfq')
						
					);
					
					//JS edit. New Emails - part 1.12
					$mailer = $woocommerce->mailer();
					ob_start();
					wc_get_template('emails/email-header.php', array('email_heading' => $sdata['heading']));
					echo $sdata['msg'];
					wc_get_template('emails/email-footer.php');
					$msg = ob_get_clean();
					$mailer->send($sdata['sendto'], $sdata['subject'], $msg);
					
				}
			
			// JS edit. Add country and city drop down filter and country preference. Step 5
			}else{
				
					if ( $user->user_email && ( $user->ID != $customer_id ) && $subscribe_country == $q_meta_data['quotation_country'] ) {
						$smes  = array(
							esc_html__( 'A new requested has been submitted by ', 'wk-mp-rfq' ) . get_user_by( 'ID', $customer_id )->user_login . '.',
						);
						$smes  = $this->womprfq_get_mail_quotation_detail( $qid, $smes );
						$sdata = array(
							'msg'     => $smes,
							'sendto'  => $user->user_email,
							'heading' => esc_html__( 'New Request For Quotation', 'wk-mp-rfq' ),
						);
						do_action( 'womprfq_quotation', $sdata );
					}
				}
			
			}
		}

		public function womprfq_delete_quote_by_id( $ids ) {
			if ( ! empty( $ids ) ) {
				if ( ! is_array( $ids ) ) {
					$ids = array( $ids );
				}
				foreach ( $ids as $id ) {
					$this->wpdb->delete(
						$this->main_quote_table,
						array(
							'id' => $id,
						)
					);
					$this->wpdb->delete(
						$this->seller_quote_table,
						array(
							'main_quotation_id' => $id,
						)
					);
				}
			}
		}

		public function womprfq_delete_seller_quote_by_id( $ids ) {
			if ( ! empty( $ids ) ) {
				if ( ! is_array( $ids ) ) {
					$ids = array( $ids );
				}
				foreach ( $ids as $id ) {
					$this->wpdb->delete(
						$this->seller_quote_table,
						array(
							'id' => $id,
						)
					);
				}
			}
		}

		public function womprfq_get_all_seller_quotation_list( $q_id, $search ) {
			if ( $q_id ) {
				$query = "SELECT * FROM $this->seller_quote_table WHERE main_quotation_id = $q_id";

				$res = $this->wpdb->get_results( $query );
				if ( $res ) {
					return $res;
				} else {
					return false;
				}
			}

			return false;
		}

		public function womprfq_get_seller_quotation_for_cust( $q_id, $tab ) {

			$tabs = array(
				'open'     => 0,
				'pending'  => 2,
				'answered' => 1,
				'resolved' => 3,
				'closed'   => 4,
				'open'     => 22,
			);
			if ( $q_id ) {
				$query = "SELECT * FROM $this->seller_quote_table WHERE main_quotation_id = $q_id";
				if ( $tab ) {
					$query .= " AND status = $tabs[$tab]";
				}

				$res = $this->wpdb->get_results( $query );
				if ( $res ) {
					return $res;
				} else {
					return false;
				}
			}

			return false;
		}

		public function womprfq_get_main_quotation_by_id( $qid ) {
			if ( $qid ) {
				$query = "SELECT * FROM $this->main_quote_table WHERE id = $qid";
				$res   = $this->wpdb->get_row( $query );
				if ( $res ) {
					return $res;
				}
			}
			return false;
		}

		public function womprfq_get_seller_quotation_details( $sqid ) {
			$response = false;
			if ( $sqid ) {
				$query = $this->wpdb->prepare(
					"SELECT * FROM  $this->seller_quote_table where id=%s",
					intval( $sqid )
				);
				$res   = $this->wpdb->get_row( $query );

				if ( $res ) {
					$response = $res;
				}
			}
			return $response;
		}

		public function womprfq_get_seller_quote_comment_details( $sel_quote_id ) {
			$response = array();

			if ( intval( $sel_quote_id ) ) {
				$query = "SELECT * FROM $this->seller_quote_comment_table WHERE seller_quotation_id = " . intval( $sel_quote_id ) . ' ORDER BY id ASC';

				$res = $this->wpdb->get_results( $query );
				if ( $res ) {
					foreach ( $res as $result ) {
						$response[] = array(
							'id'           => intval( $result->id ),
							'sender_id'    => intval( $result->sender_id ),
							'image'        => $result->image,
							'comment_text' => html_entity_decode( $result->comment_text ),
							'date'         => $result->date,
						);
					}
				}
			}
			return $response;
		}

		public function womprfq_update_main_quotation_status( $qid, $status ) {
			$response = false;
			if ( $qid ) {
				$res = $this->wpdb->update(
					$this->main_quote_table,
					array(
						'status' => $status,
					),
					array(
						'id' => $qid,
					)
				);
				if ( $res ) {
					$response = true;
				}
			}
			return $response;
		}

		public function womprfq_update_seller_quotation_status( $sid, $status ) {
			$response = false;

			if ( $sid && intval( $sid ) > 0 && $status ) {
				$res = $this->wpdb->update(
					$this->seller_quote_table,
					array(
						'status' => $status,
					),
					array(
						'id' => $sid,
					)
				);
				if ( $res ) {
					if ( $status == 4 ) {
						$query         = "SELECT main_quotation_id FROM $this->seller_quote_table WHERE id=$sid";
						$main_quote_id = $this->wpdb->get_var( $query );
						if ( $main_quote_id && intval( $main_quote_id ) > 0 ) {
							$res2 = $this->wpdb->update(
								$this->seller_quote_table,
								array(
									'status' => 4,
								),
								array(
									'main_quotation_id' => $main_quote_id,
								)
							);
						}
					}
					$response = true;
				}
			}

			return $response;
		}

		public function womprfq_update_seller_quotation( $info, $action = '' ) {

			$response = array(
				'status' => false,
				'msg'    => array(),
			);

			if ( $info && isset( $info['id'] ) && ! empty( $info['id'] ) && isset( $info['price'] ) && ! empty( $info['price'] ) && isset( $info['quantity'] ) && ! empty( $info['quantity'] ) && isset( $info['status'] ) && ! empty( $info['status'] ) ) {
				if ( $action == 'add' ) {
					$res = $this->wpdb->insert(
						$this->seller_quote_table,
						array(
							'main_quotation_id' => $info['id'],
							'seller_id'         => get_current_user_id(),
							'price'             => $info['price'],
							'commission'        => $info['commission'],
							'quantity'          => $info['quantity'],
							'status'            => 22,
						)
					);
					if ( $res ) {
						$info['id'] = $this->wpdb->insert_id;
					}
				} else {
					$res = $this->wpdb->update(
						$this->seller_quote_table,
						array(
							'price'      => $info['price'],
							'commission' => $info['commission'],
							'quantity'   => $info['quantity'],
							'status'     => $info['status'],
						),
						array(
							'id' => $info['id'],
						)
					);

				}

				if ( $res ) {
					$response['status']          = true;
					$response['seller_quote_id'] = $info['id'];
				} else {
					$response['status'] = false;
					$response['msg'][]  = array(
						'status' => 'error',
						'msg'    => esc_html__( 'Enter Details to change', 'wk-mp-rfq' ),
					);
				}
			}

			return $response;
		}

		public function womprfq_update_seller_quotation_comment( $comment_info ) {
			$response = false;
			if ( $comment_info ) {
				$sql = $this->wpdb->insert(
					$this->seller_quotation_comment_table,
					$comment_info
				);
				if ( $sql ) {
					return $sql;
				}
			}
			return $response;
		}

		public function womprfq_get_seller_quotations( $sel_id, $tab, $offset, $limit ) {

			$tdata = $data = array();
			$tabs  = array(
				'open'     => 0,
				'pending'  => 1,
				'answered' => 2,
				'resolved' => 3,
				'closed'   => 4,
			);
			$ids   = array();
			if ( $sel_id && $tab ) {
				$status = $tabs[ $tab ];
				if ( $status == 0 ) {
					$query = $this->wpdb->prepare( "SELECT main_quotation_id FROM $this->seller_quote_table WHERE seller_id = %d", $sel_id );
					$res   = $this->wpdb->get_results( $query );
					if ( $res ) {
						$ids = wc_list_pluck( $res, 'main_quotation_id' );
					}
					if ( ! empty( $ids ) ) {
						$ids_str = implode( ',', $ids );
						$query1  = $this->wpdb->prepare( "SELECT * FROM $this->main_quote_table WHERE status = %d AND customer_id != %d AND id NOT IN(" . esc_html( $ids_str ) . ' ) ORDER BY id DESC LIMIT %d, %d', 1, $sel_id, $offset, $limit );
						$query1c = "SELECT count(*) as count FROM $this->main_quote_table WHERE status = 1 AND customer_id != $sel_id AND id NOT IN ( " . esc_html( $ids_str ) . ' )';
						$res1    = $this->wpdb->get_results( $query1 );
						$resc    = $this->wpdb->get_results( $query1c );
					} else {
						$query1  = $this->wpdb->prepare( "SELECT * FROM $this->main_quote_table WHERE status = %d AND customer_id != %d  ORDER BY id DESC LIMIT %d, %d", 1, $sel_id, $offset, $limit );
						$query1c = $this->wpdb->prepare( "SELECT count(*) as count FROM $this->main_quote_table WHERE status = %d AND customer_id != %d", 1, $sel_id );
						$res1    = $this->wpdb->get_results( $query1 );
						$resc    = $this->wpdb->get_results( $query1c );
					}
					if ( ! empty( $res1 ) ) {
						foreach ( $res1 as $rs ) {
							if ( $rs->variation_id != 0 ) {
								$pro_name = get_the_title( $rs->variation_id );
							} elseif ( $rs->product_id != 0 ) {
								$pro_name = get_the_title( $rs->product_id );
							} else {
								$dat = $this->womprfq_get_quote_meta_info( $rs->id );
								if ( isset( $dat['pro_name'] ) ) {
									$pro_name = $dat['pro_name'];
								} else {
									$pro_name = esc_html__( 'N\A', 'wk-mp-rfq' );
								}
							}
							$user = get_user_by( 'ID', $rs->customer_id );

							if ( $user ) {
								$display_name = $user->display_name;
								$user_email   = $user->user_email;
							} else {
								$display_name = esc_html__( 'N\A', 'wk-mp-rfq' );
								$user_email   = esc_html__( 'N\A', 'wk-mp-rfq' );
							}
							$data[] = array(
								'id'             => $rs->id,
								'product_info'   => array(
									'product_id'   => $rs->product_id,
									'variation_id' => $rs->variation_id,
									'name'         => $pro_name,
								),
								'customer_info'  => array(
									'id'           => $rs->customer_id,
									'display_name' => $display_name,
									'email'        => $user_email,
								),
								'quote_status'   => $rs->status,
								'quote_quantity' => $rs->quantity,
								'date_created'   => $rs->date,
							);
						}
					}
				} elseif ( $status == 2 ) {
					$cquery = $this->wpdb->prepare( "SELECT count(*) as count FROM $this->seller_quote_table WHERE seller_id = %d and (status IN (2,22) or status = 0) ", $sel_id );
					$query  = $this->wpdb->prepare( "SELECT * FROM $this->seller_quote_table WHERE seller_id = %d and (status IN (2,22) or status = 0) ORDER BY id DESC LIMIT %d, %d", $sel_id, $offset, $limit );
					$res1   = $this->wpdb->get_results( $query );
					$resc   = $this->wpdb->get_results( $cquery );
					if ( ! empty( $res1 ) ) {
						foreach ( $res1 as $rs ) {
							if ( isset( $rs->main_quotation_id ) && intval( $rs->main_quotation_id ) > 0 ) {
								$main_data = $this->womprfq_get_main_quotation_by_id( intval( $rs->main_quotation_id ) );
								if ( $main_data ) {
									if ( $main_data->variation_id != 0 ) {
										$pro_name = get_the_title( $main_data->variation_id );
									} elseif ( $main_data->product_id != 0 ) {
										$pro_name = get_the_title( $main_data->product_id );
									} else {
										$dat = $this->womprfq_get_quote_meta_info( $main_data->id );
										if ( isset( $dat['pro_name'] ) ) {
											$pro_name = $dat['pro_name'];
										} else {
											$pro_name = 'N\A';
										}
									}
									$user = get_user_by( 'ID', $main_data->customer_id );

									if ( $user ) {
										if ( $user ) {
											$display_name = $user->display_name;
											$user_email   = $user->user_email;
										} else {
											$display_name = esc_html__( 'N\A', 'wk-mp-rfq' );
											$user_email   = esc_html__( 'N\A', 'wk-mp-rfq' );
										}
									}
									$data[] = array(
										'id'             => $rs->id,
										'product_info'   => array(
											'product_id'   => $main_data->product_id,
											'variation_id' => $main_data->variation_id,
											'name'         => $pro_name,
										),
										'customer_info'  => array(
											'id'           => $main_data->customer_id,
											'display_name' => $display_name,
											'email'        => $user_email,
										),
										'quote_status'   => $rs->status,
										'quote_quantity' => $rs->quantity,
										'quote_price'    => $rs->price,
										'main_quote_id'  => $main_data->id,
										'date_created'   => $rs->date,
									);
								}
							}
						}
					}
				} else {
					$cquery = $this->wpdb->prepare( "SELECT count(*) as count FROM $this->seller_quote_table WHERE seller_id = %d and status = %d ", $sel_id, $status );
					$query  = $this->wpdb->prepare( "SELECT * FROM $this->seller_quote_table WHERE seller_id = %d and status = %d ORDER BY id DESC LIMIT %d, %d", $sel_id, $status, $offset, $limit );
					$res1   = $this->wpdb->get_results( $query );
					$resc   = $this->wpdb->get_results( $cquery );
					if ( ! empty( $res1 ) ) {
						foreach ( $res1 as $rs ) {
							if ( isset( $rs->main_quotation_id ) && intval( $rs->main_quotation_id ) > 0 ) {
								$main_data = $this->womprfq_get_main_quotation_by_id( intval( $rs->main_quotation_id ) );
								if ( $main_data ) {
									if ( $main_data->variation_id != 0 ) {
										$pro_name = get_the_title( $main_data->variation_id );
									} elseif ( $main_data->product_id != 0 ) {
										$pro_name = get_the_title( $main_data->product_id );
									} else {
										$dat = $this->womprfq_get_quote_meta_info( $main_data->id );
										if ( isset( $dat['pro_name'] ) ) {
											$pro_name = $dat['pro_name'];
										} else {
											$pro_name = 'N\A';
										}
									}
									$user = get_user_by( 'ID', $main_data->customer_id );

									if ( $user ) {
										if ( $user ) {
											$display_name = $user->display_name;
											$user_email   = $user->user_email;
										} else {
											$display_name = esc_html__( 'N\A', 'wk-mp-rfq' );
											$user_email   = esc_html__( 'N\A', 'wk-mp-rfq' );
										}
									}
									$data[] = array(
										'id'             => $rs->id,
										'product_info'   => array(
											'product_id'   => $main_data->product_id,
											'variation_id' => $main_data->variation_id,
											'name'         => $pro_name,
										),
										'customer_info'  => array(
											'id'           => $main_data->customer_id,
											'display_name' => $display_name,
											'email'        => $user_email,
										),
										'quote_status'   => $rs->status,
										'quote_quantity' => $rs->quantity,
										'quote_price'    => $rs->price,
										'main_quote_id'  => $main_data->id,
										'date_created'   => $rs->date,
									);
								}
							}
						}
					}
				}
			}

			$tdata['data']   = $data;
			$tdata['tcount'] = $resc[0]->count;
			return $tdata;
		}

		// JS edit. Add country and city drop down filter and country preference. Step 6
		public function womprfq_get_seller_quotations_by_country( $sel_id, $tab, $offset, $limit,$country ) {
			
			$tdata = $data = array();
			$tabs  = array(
				'open'     => 0,
				'pending'  => 1,
				'answered' => 2,
				'resolved' => 3,
				'closed'   => 4,
			);
			$ids   = array();
			if ( $sel_id && $tab ) {
				$status = $tabs[ $tab ];
				if ( $status == 0 ) {
					$query = $this->wpdb->prepare( "SELECT main_quotation_id FROM $this->seller_quote_table WHERE seller_id = %d", $sel_id );
					$res   = $this->wpdb->get_results( $query );
					if ( $res ) {
						$ids = wc_list_pluck( $res, 'main_quotation_id' );
					}
					if ( ! empty( $ids ) ) { 
						$ids_str = implode( ',', $ids );
						$query1  = $this->wpdb->prepare( "SELECT $this->main_quote_table.* FROM $this->main_quote_table JOIN $this->main_quote_meta_table ON $this->main_quote_table.id = $this->main_quote_meta_table.main_quotation_id WHERE $this->main_quote_meta_table.key = 'quotation_country' AND $this->main_quote_meta_table.value = '$country' AND $this->main_quote_table.status = %d AND $this->main_quote_table.customer_id != %d AND $this->main_quote_table.id NOT IN(" . esc_html( $ids_str ) . ' ) ORDER BY '.$this->main_quote_table.'.id DESC LIMIT %d, %d', 1, $sel_id, $offset, $limit );
						$query1c = "SELECT count(*) as count FROM $this->main_quote_table JOIN $this->main_quote_meta_table ON $this->main_quote_table.id = $this->main_quote_meta_table.main_quotation_id WHERE $this->main_quote_meta_table.key = 'quotation_country' AND $this->main_quote_meta_table.value = '$country' AND $this->main_quote_table.status = 1 AND $this->main_quote_table.customer_id != $sel_id AND $this->main_quote_table.id NOT IN ( " . esc_html( $ids_str ) . ' )';
 
						$res1    = $this->wpdb->get_results( $query1 );
						$resc    = $this->wpdb->get_results( $query1c );
					} else {
						$query1  = $this->wpdb->prepare( "SELECT $this->main_quote_table.* FROM $this->main_quote_table JOIN $this->main_quote_meta_table ON $this->main_quote_table.id = $this->main_quote_meta_table.main_quotation_id WHERE $this->main_quote_meta_table.key = 'quotation_country' AND $this->main_quote_meta_table.value = '$country' AND $this->main_quote_table.status = %d AND $this->main_quote_table.customer_id != %d  ORDER BY $this->main_quote_table.id DESC LIMIT %d, %d", 1, $sel_id, $offset, $limit );
						
						$query1c = $this->wpdb->prepare( "SELECT count(*) as count FROM $this->main_quote_table JOIN $this->main_quote_meta_table ON $this->main_quote_table.id = $this->main_quote_meta_table.main_quotation_id WHERE $this->main_quote_meta_table.key = 'quotation_country' AND $this->main_quote_meta_table.value = '$country' AND status = %d AND customer_id != %d", 1, $sel_id );
						$res1    = $this->wpdb->get_results( $query1 );
						$resc    = $this->wpdb->get_results( $query1c );
					}
					if ( ! empty( $res1 ) ) {
						foreach ( $res1 as $rs ) {
							if ( $rs->variation_id != 0 ) {
								$pro_name = get_the_title( $rs->variation_id );
							} elseif ( $rs->product_id != 0 ) {
								$pro_name = get_the_title( $rs->product_id );
							} else {
								$dat = $this->womprfq_get_quote_meta_info( $rs->id );
								if ( isset( $dat['pro_name'] ) ) {
									$pro_name = $dat['pro_name'];
								} else {
									$pro_name = esc_html__( 'N\A', 'wk-mp-rfq' );
								}
							}
							$user = get_user_by( 'ID', $rs->customer_id );

							if ( $user ) {
								$display_name = $user->display_name;
								$user_email   = $user->user_email;
							} else {
								$display_name = esc_html__( 'N\A', 'wk-mp-rfq' );
								$user_email   = esc_html__( 'N\A', 'wk-mp-rfq' );
							}
							$data[] = array(
								'id'             => $rs->id,
								'product_info'   => array(
									'product_id'   => $rs->product_id,
									'variation_id' => $rs->variation_id,
									'name'         => $pro_name,
								),
								'customer_info'  => array(
									'id'           => $rs->customer_id,
									'display_name' => $display_name,
									'email'        => $user_email,
								),
								'quote_status'   => $rs->status,
								'quote_quantity' => $rs->quantity,
								'date_created'   => $rs->date,
							);
						}
					}
				} 
			}

			$tdata['data']   = $data;
			$tdata['tcount'] = $resc[0]->count;
// 			echo "<pre>";print_r($tdata);
			return $tdata;
		}
		
		
		
		public function womprfq_get_quote_meta_info( $qid ) {
			$res = false;
			if ( $qid ) {
				$query   = "SELECT * FROM $this->main_quote_meta_table WHERE main_quotation_id = $qid";
				$results = $this->wpdb->get_results( $query );
				if ( $results ) {
					foreach ( $results as $result ) {
						$res[ $result->key ] = $result->value;
					}
				}
			}
			return $res;
		}

		public function womprfq_get_all_customer_quotation_list( $c_id, $search, $offset, $limit ) {
			if ( $c_id ) {
				$query = "SELECT * FROM $this->main_quote_table WHERE customer_id = $c_id";
				if ( $search ) {
					$query .= " AND id = $search";
				}

				$query .= " ORDER BY id DESC LIMIT $offset, $limit ";

				$res = $this->wpdb->get_results( $query );
				if ( $res ) {
					return $res;
				} else {
					return false;
				}
			}

			return false;
		}

		public function womprfq_get_all_customer_quotation_count( $c_id, $search ) {
			if ( $c_id ) {
				$query = "SELECT count(*) as count FROM $this->main_quote_table WHERE customer_id = $c_id";
				if ( $search ) {
					$query .= " AND id = $search";
				}

				$res = $this->wpdb->get_results( $query );
				if ( $res ) {
					return $res;
				} else {
					return false;
				}
			}

			return false;
		}

		public function womprfq_update_customer_quotation( $info ) {
			$response = array(
				'status' => false,
				'msg'    => array(),
			);

			$err = array();
			if ( $info && isset( $info['id'] ) && ! empty( $info['id'] ) && isset( $info['status'] ) && ! empty( $info['status'] ) ) {
				$res = $this->wpdb->update(
					$this->seller_quote_table,
					array(
						'status' => $info['status'],
					),
					array(
						'id' => $info['id'],
					)
				);

				if ( $res ) {
					$response['status'] = true;
				} else {
					$response['status'] = false;
					$response['msg'][]  = array(
						'status' => 'error',
						'msg'    => esc_html__( 'Enter Details to change', 'wk-mp-rfq' ),
					);
				}
			}

			return $response;
		}

		public function womprfq_add_quote_meta( $res_data, $quid ) {
			if ( ! empty( $res_data ) ) {
				foreach ( $res_data as $key => $value ) {
					$this->wpdb->insert(
						$this->main_quote_meta_table,
						array(
							'main_quotation_id' => intval( $quid ),
							'key'               => sanitize_text_field( $key ),
							'value'             => sanitize_text_field( $value ),
						),
						array(
							'%d',
							'%s',
							'%s',
						)
					);
				}
			}
		}

		/**
		 * Returns attribute data
		 *
		 * @param int $qid attribute id
		 *
		 * @return $search
		 */
		public function womprfq_get_quote_attribute_data( $qid ) {
			$res        = array();
			$attributes = $this->womprfq_get_quote_attribute_info( $qid );
			if ( $attributes ) {
				$res = $attributes;
			}
			return $res;
		}

		public function womprfq_get_quote_attribute_info( $qid ) {
			$res = false;
			if ( $qid ) {
				$query   = "SELECT * FROM $this->main_quote_meta_table WHERE main_quotation_id = $qid";
				$results = $this->wpdb->get_results( $query );
				if ( $results ) {
					foreach ( $results as $result ) {
						if ( strpos( $result->key, 'wpmp-rfq-admin-quote-' ) !== false ) {
							$res[ str_replace( 'wpmp-rfq-admin-quote-', '', $result->key ) ] = $result->value;
						}
					}
				}
			}
			return $res;
		}

		public function womprfq_add_new_quotated_product( $data, $sel_data ) {
			$response['status'] = false;

			if ( $data ) {
				$main_quote_id = $data->id;

				$q_meta_data = $this->womprfq_get_quote_meta_info( $main_quote_id );

				if ( isset( $q_meta_data['pro_name'] ) && ! empty( $q_meta_data['pro_name'] ) ) {
					$name = $q_meta_data['pro_name'];
				} else {
					$error = true;
				}
				if ( isset( $q_meta_data['pro_desc'] ) && ! empty( $q_meta_data['pro_desc'] ) ) {
					$desc = $q_meta_data['pro_desc'];
				} else {
					$error = true;
				}
				if ( isset( $q_meta_data['image'] ) ) {
					$image = $q_meta_data['image'];
				} else {
					$error = true;
				}

				if ( ! $error ) {
					$image_id = explode( ',', $image );

					$objProduct = new WC_Product();
					$objProduct->set_name( $name );
					if ( get_option( '_wkmp_allow_seller_to_publish' ) ) {
						$objProduct->set_status( 'Publish' );
					} else {
						$objProduct->set_status( 'Publish' );
					}
					$objProduct->set_catalog_visibility( 'visible' );
					$objProduct->set_description( $desc );
					$objProduct->set_price( $sel_data->price );
					$objProduct->set_regular_price( $sel_data->price );
					$objProduct->set_manage_stock( false );
					if ( count( $image_id > 1 ) ) {
						$gallry = $image_id;
						unset( $gallry[0] );
						$objProduct->set_gallery_image_ids( $gallry );
					}
					$objProduct->set_image_id( $image_id[0] );
					$objProduct->set_stock_status( 'instock' );
					$objProduct->set_backorders( 'no' );
					$product_id = $objProduct->save();
					add_post_meta( $product_id, 'womprfq_created_product', true );
					$response['product_id'] = $product_id;
					$response['status']     = true;
					wc_add_notice( esc_html__( 'Quotation Updated Successfully', 'wk-mp-rfq' ), 'success' );
				}
			}

			return $response;
		}

		public function womprfq_product_be_quoted( $product_id, $customer_id ) {
			$res = true;
			if ( $product_id ) {
				$product = wc_get_product( $product_id );
				$rfq_dta = get_post_meta( $product_id, 'wkmprfq_quote_data', true );
				if ( isset( $rfq_dta[ $customer_id ] ) ) {
					if ( isset( $rfq_dta[ $customer_id ]['quantity'] ) && isset( $rfq_dta[ $customer_id ]['status'] ) ) {
						$res = false;
					}
				}

				if ( $res ) {
					if ( $product->get_type() == 'variation' ) {
						$main_p_id = $product->get_parent_id();

						if ( intval( $main_p_id ) != 0 ) {
							$check_main = "SELECT * FROM $this->main_quote_table WHERE product_id = $main_p_id AND variation_id = $product_id AND status < 2 AND customer_id = $customer_id";
							$result     = $this->wpdb->get_results( $check_main );
							if ( $result ) {
								$res = false;
							}
						}
					} elseif ( $product->get_type() == 'simple' ) {
						$check_main = "SELECT * FROM $this->main_quote_table WHERE product_id = $product_id AND status < 2 AND customer_id = $customer_id";
						$result     = $this->wpdb->get_results( $check_main );
						if ( $result ) {
							$res = false;
						}
					}
				}
			}
			return $res;
		}

		public function womprfq_update_other_seller_quotation( $sqid ) {
			if ( $sqid ) {
				$dta = $this->womprfq_get_seller_quotation_details( $sqid );
				if ( $dta ) {
					$sel_qs = $this->womprfq_get_all_seller_quotation_list( $dta->main_quotation_id, '' );
					if ( $sel_qs ) {
						foreach ( $sel_qs as $sel_q ) {
							if ( $sel_q->id != $sqid ) {
								$this->womprfq_update_customer_quotation(
									array(
										'id'     => $sel_q->id,
										'status' => 4,
									)
								);
							}
						}
					}
				}
			}
		}
	}
}
