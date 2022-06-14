<?php
/**
 * Admin End Hooks
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

use WkMarketplace\Helper\Admin;
use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Admin_Ajax_Functions' ) ) {
	/**
	 * Admin Functions class
	 */
	class WKMP_Admin_Ajax_Functions {
		/**
		 * Seller db Class object.
		 *
		 * @var Admin\WKMP_Seller_Data
		 */
		private $seller_db_obj;

		/**
		 * Order DB class object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data
		 */
		private $order_db_obj;

		/**
		 * Commission DB class object.
		 *
		 * @var Common\WKMP_Commission
		 */
		private $commission_db_obj;

		/**
		 * Transaction class object.
		 *
		 * @var Common\WKMP_Transaction
		 */
		private $transaction_db_obj;

		/**
		 * Marketplace class obj.
		 *
		 * @var $wkmarketplace
		 */
		private $wkmarketplace;

		/**
		 * Admin function constructor.
		 *
		 * WKMP_Admin_Ajax_Functions constructor.
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->seller_db_obj      = new Admin\WKMP_Seller_Data();
			$this->order_db_obj       = new Admin\WKMP_Seller_Order_Data();
			$this->commission_db_obj  = new Common\WKMP_Commission();
			$this->transaction_db_obj = new Common\WKMP_Transaction();
			$this->wkmarketplace      = $wkmarketplace;
		}

		/**
		 * Approve/Disapprove Seller.
		 *
		 * @return void
		 */
		public function wkmp_approve_seller() {
			$json = array(
				'error'   => false,
				'message' => '',
				'action'  => 'approve',
				'success' => false,
			);

			if ( ! check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) ) {
				$json['error']   = true;
				$json['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $json );
				die();
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();

			if ( isset( $posted_data['seller_id'] ) && $posted_data['seller_id'] ) {
				global $wpdb;

				$seller_id      = $posted_data['seller_id'];
				$wp_user_object = new \WP_User( $seller_id );

				if ( $this->wkmarketplace->wkmp_user_is_seller( $seller_id ) ) {
					$role            = 'customer';
					$json['message'] = esc_html__( 'Approve', 'wk-marketplace' );

					$wp_user_object->set_role( get_option( 'default_role' ) );
					$wpdb->get_results( $wpdb->prepare( "UPDATE {$wpdb->prefix}posts SET post_status = 'draft' WHERE post_author = %d", $seller_id ) );
					do_action( 'wkmp_seller_account_disapproved', $seller_id );
				} else {
					$role            = 'seller';
					$json['message'] = esc_html__( 'Disapprove', 'wk-marketplace' );
					$json['action']  = 'disapprove';

					$wp_user_object->set_role( 'wk_marketplace_seller' );

					$wpdb->get_results( $wpdb->prepare( "UPDATE {$wpdb->prefix}posts SET post_status = 'publish' WHERE post_author = %d", $seller_id ) );

					do_action( 'wkmp_seller_account_approved', $seller_id );
				}
				$this->seller_db_obj->wkmp_approve_seller( $seller_id, $role );
				$json['success'] = true;
			}
			wp_send_json( $json );
			die();
		}

		/**
		 * Replied to seller.
		 */
		public function wkmp_admin_replied_to_seller() {
			$json = array();

			if ( ! check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) ) {
				$json['error']   = true;
				$json['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $json );
				die();
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();

			if ( isset( $posted_data['reply_message'] ) && isset( $posted_data['qid'] ) ) {
				$query_db_obj              = new Common\WKMP_Seller_Ask_Queries();
				$query_id                  = $posted_data['qid'];
				$query_info                = $query_db_obj->wkmp_get_query_info_by_id( $query_id );
				$seller_email              = get_userdata( $query_info->seller_id )->user_email;
				$posted_data['query_info'] = $query_info;

				if ( $seller_email && ! empty( $posted_data['reply_message'] ) ) {
					$posted_data['reply_message'] = str_replace( '\\', '', $posted_data['reply_message']);
					do_action( 'wkmp_seller_query_replied', $seller_email, $query_id, $posted_data );
					$query_db_obj->wkmp_update_seller_reply_status( $query_id );
					$json['success'] = true;
					$json['message'] = esc_html__( 'Replied mail send to the seller.', 'wk-marketplace' );
				} else {
					$json['success'] = false;
					$json['message'] = esc_html__( 'Oops, Unable to send mail to the seller.', 'wk-marketplace' );
				}
			}

			wp_send_json( $json );
			die();
		}

		/**
		 * Check my shop values.
		 */
		public function wkmp_check_myshop_value() {
			if ( check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();
				$url_slug    = $posted_data['shop_slug'];
				$check       = false;
				$user        = get_user_by( 'slug', $url_slug );
				if ( empty( $url_slug ) ) {
					$check = false;
				} elseif ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $url_slug ) ) {
					$check = false;
				} elseif ( ctype_space( $posted_data['shop_slug'] ) ) {
					$check = false;
				} elseif ( ! empty( $user ) ) {
					$check = 2;
				} else {
					$check = true;
				}
				echo esc_html( $check );
				die;
			}
		}

		/**
		 * Change seller dashboard settings.
		 */
		public function wkmp_change_seller_dashboard() {
			if ( check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) ) {
				global $wkmarketplace;
				$data = array();
				if ( isset( $_POST['change_to'] ) ) {
					$posted_data  = isset( $_POST ) ? wc_clean( $_POST ) : array();
					$current_user = wp_get_current_user();
					$current_dash = get_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', true );
					if ( 'front_dashboard' === $posted_data['change_to'] ) {
						if ( $current_dash ) {
							update_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', null );
							$data['redirect'] = site_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) );
						}
					} elseif ( 'backend_dashboard' === $posted_data['change_to'] ) {
						update_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', true );
						$data['redirect'] = admin_url( 'admin.php?page=seller' );
					}
				}
				wp_send_json( $data );
			}
		}

		/**
		 * Updating order status.
		 */
		public function wkmp_update_seller_order_status() {
			$result = array(
				'success' => false,
				'message' => esc_html__( 'There is some error!! Please try again later!!', 'wk-marketplace' ),
			);

			if ( ! check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) ) {
				$result['error']   = true;
				$result['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $result );
				die();
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();

			if ( isset( $posted_data['action_data'] ) && $posted_data['action_data'] ) {
				$ids           = explode( '-', $posted_data['action_data'] );
				$order_id      = $ids[0];
				$seller_id     = $ids[1];
				$action        = count( $ids ) > 2 ? $ids[2] : 'pay'; // If Approve/Disapprove option was selected from the dropdown.
				$seller_amount = count( $ids ) > 3 ? $ids[3] : 0; // If pay option was selected from the dropdown.

				if ( $order_id && $seller_id ) {
					$result['seller_id']     = $seller_id;
					$result['order_id']      = $order_id;
					$result['action']        = $action;
					$result['seller_amount'] = $seller_amount;

					$paid_status = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

					$this->order_db_obj->wkmp_update_seller_order_status( $order_id, $seller_id, $action );

					$action_text     = ( 'pay' === $action ) ? esc_html__( 'Paid', 'wk-marketplace' ) : ( 'approve' === $action ? esc_html__( 'Approved', 'wk-marketplace' ) : esc_html__( 'Disapproved', 'wk-marketplace' ) );
					$message         = ( 'pay' === $action ) ? sprintf( /* Translators: %d Order id. */ esc_html__( 'Payment has been successfully done for order id: %d', 'wk-marketplace' ), esc_html( $order_id ) ) : sprintf( /* Translators: %d Order id. */ esc_html__( 'Order status for order id: %d has been successfully updated to disapproved.', 'wk-marketplace' ), esc_attr( $order_id ) );
					$new_action_html = '<button class="button button-primary" class="admin-order-pay" disabled>' . esc_html( $action_text ) . '</button>';

					if ( 'approve' === $action && $seller_amount > 0 ) {
						$new_action_html = '<a href="javascript:void(0)" data-id="' . esc_attr( $order_id ) . '-' . esc_attr( $seller_id ) . '" class="page-title-action admin-order-pay">' . __( 'Pay', 'wk-marketplace' ) . '</a>';
						$message         = sprintf( /* Translators: %d Order id. */ esc_html__( 'Order status for order id: %d has been successfully updated to approved.', 'wk-marketplace' ), esc_attr( $order_id ) );
					}

					$result['success']         = true;
					$result['message']         = $message;
					$result['new_action_html'] = $new_action_html;

					if ( 'approve' === $action || ( 'pay' === $action && 'approved' !== $paid_status ) ) {
						do_action( 'wkmp_seller_order_paid', $result );
					}
				}
			}

			wp_send_json( $result );
		}
	}
}
