<?php
/**
 * Front hooks template
 *
 * @package Multi Vendor Marketplace
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

use WkMarketplace\Helper\Admin;
use WkMarketplace\Helper\Common;
use WkMarketplace\Includes\Shipping;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Common_Functions' ) ) {
	/**
	 * Front hooks class
	 */
	class WKMP_Common_Functions {
		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Order db object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data $order_db_obj Order db object.
		 */
		private $order_db_obj;

		/**
		 * WKMP_Common_Functions constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb         = $wpdb;
			$this->order_db_obj = new Admin\WKMP_Seller_Order_Data();
		}

		/**
		 * Manage shipping.
		 */
		public function wkmp_add_manage_shipping() {
			new Shipping\WKMP_Manage_Shipping();
		}

		/**
		 * Map admin shipping zone with sellers.
		 *
		 * @param int    $instance_id Instance id.
		 * @param string $type Shipping type.
		 * @param int    $id Id.
		 */
		public function wkmp_after_add_admin_shipping_zone( $instance_id, $type, $id ) {
			$wpdb_obj = $this->wpdb;
			$user_id  = get_current_user_id();
			if ( ! empty( $id ) ) {
				$table_name = $wpdb_obj->prefix . 'mpseller_meta';
				$result     = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT count(*) as total from $table_name where zone_id = %s", $id ) );
				if ( $result && intval( $result[0]->total ) < 1 ) {
					$wpdb_obj->insert(
						$table_name,
						array(
							'seller_id' => $user_id,
							'zone_id'   => $id,
						)
					);
				}
			}
		}

		/**
		 * Delete mapped zone.
		 *
		 * @param int $id Shipping zone id.
		 */
		public function wkmp_action_woocommerce_delete_shipping_zone( $id ) {
			$wpdb_obj   = $this->wpdb;
			$table_name = $wpdb_obj->prefix . 'mpseller_meta';
			if ( $id ) {
				$wpdb_obj->delete( $table_name, array( 'zone_id' => $id ), array( '%d' ) );
			}
		}

		/**
		 * Add class data as user meta.
		 *
		 * @param int   $term_id Term id.
		 * @param array $data Data.
		 *
		 * @hooked 'woocommerce_shipping_classes_save_class' action hook.
		 */
		public function wkmp_after_add_admin_shipping_class( $term_id, $data ) {
			global $current_user;
			$seller_sclass = get_user_meta( $current_user->ID, 'shipping-classes', true );
			$seller_sclass = empty( $seller_sclass ) ? array() : maybe_unserialize( $seller_sclass );
			array_push( $seller_sclass, $term_id );
			$seller_sclass_update = maybe_serialize( $seller_sclass );
			update_user_meta( $current_user->ID, 'shipping-classes', $seller_sclass_update );
		}

		/**
		 * Action_on_order_cancel.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked 'woocommerce_order_status_cancelled' action hook.
		 */
		public function wkmp_action_on_order_cancel( $order_id ) {
			$wpdb_obj    = $this->wpdb;
			$commission  = new Common\WKMP_Commission();
			$order       = wc_get_order( $order_id );
			$seller_list = $commission->wkmp_get_sellers_in_order( $order_id );

			foreach ( $seller_list as $seller_id ) {
				$sel_info   = $commission->wkmp_get_sel_comission_via_order( $order_id, $seller_id );
				$seller_amt = $sel_info['total_seller_amount'];
				$admin_amt  = $sel_info['total_commission'];
				$seller     = $wpdb_obj->get_results( $wpdb_obj->prepare( " SELECT * from {$wpdb_obj->prefix}mpcommision WHERE seller_id = %d", $seller_id ) );

				if ( $seller ) {
					$seller        = $seller[0];
					$admin_amount  = floatval( $seller->admin_amount ) - $admin_amt;
					$seller_amount = floatval( $seller->seller_total_ammount ) - $seller_amt;
					$wpdb_obj->get_results( $wpdb_obj->prepare( " UPDATE {$wpdb_obj->prefix}mpcommision set admin_amount = %f, seller_total_ammount = %f WHERE seller_id = %d", $admin_amount, $seller_amount, $seller_id ) );
				}
			}
			$this->wkmp_send_mail_to_inform_seller_for_order_status( $order );
		}

		/**
		 * Action on changing order emails.
		 *
		 * @param int $order_id Order id.
		 */
		public function wkmp_action_on_order_changed_mails( $order_id ) {
			$order               = wc_get_order( $order_id );
			$send_mail_to_seller = apply_filters( 'wkmp_send_notification_mail_to_seller_for_new_order', true, $order );

			if ( $send_mail_to_seller ) {
				$this->wkmp_send_mail_to_inform_seller_for_order_status( $order );
			}
		}

		/**
		 * Mail to seller for order status.
		 *
		 * @param \WC_Order $order Order.
		 */
		public function wkmp_send_mail_to_inform_seller_for_order_status( $order ) {
			global $wkmarketplace;
			$wpdb_obj      = $this->wpdb;
			$items         = $order->get_items();
			$sellers       = array();
			$sell_order_id = $order->get_id();

			foreach ( $items as $item ) {
				$item_id   = isset( $item['product_id'] ) ? $item['product_id'] : 0;
				$author_id = get_post_field( 'post_author', $item_id );

				$is_seller = $wkmarketplace->wkmp_user_is_seller( $author_id );

				if ( ! $is_seller ) {
					continue;
				}

				$order_approval_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_order_approval', true );
				$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $author_id, $sell_order_id );

				if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
					continue;
				}

				$email = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_email FROM {$wpdb_obj->base_prefix}users AS user JOIN {$wpdb_obj->prefix}posts AS post ON post.post_author = user.ID WHERE post.ID = %d", esc_attr( $item_id ) ) );

				$sellers[ $email ][] = $item;
			}

			$order_status = $order->get_status();

			foreach ( $sellers as $seller_email => $items ) {
				if ( 'cancelled' === $order_status ) {
					do_action( 'wkmp_seller_order_cancelled', $sell_order_id, $items, $seller_email );
				} elseif ( 'failed' === $order_status ) {
					do_action( 'wkmp_seller_order_failed', $sell_order_id, $items, $seller_email );
				} elseif ( 'on-hold' === $order_status ) {
					do_action( 'wkmp_seller_order_on_hold', $sell_order_id, $items, $seller_email );
				} elseif ( 'processing' === $order_status ) {
					do_action( 'wkmp_seller_order_processing', $sell_order_id, $items, $seller_email );
				} elseif ( 'completed' === $order_status ) {
					do_action( 'wkmp_seller_order_completed', $sell_order_id, $items, $seller_email );
				} elseif ( 'refunded' === $order_status ) {
					$refund_args = array(
						'order_id'      => $sell_order_id,
						'refund_amount' => $order->get_total() - $order->get_total_refunded(),
					);
					do_action( 'wkmp_seller_order_refunded_completely', $items, $seller_email, $refund_args );
				}
			}
		}

		/**
		 * Action on product approve.
		 *
		 * @param \WP_Post $post Post object.
		 */
		public function wkmp_action_on_product_approve( $post ) {
			if ( 'product' === $post->post_type ) {
				$author_id = get_post_field( 'post_author', $post->ID );
				if ( ! is_super_admin( $author_id ) ) {
					if ( ! get_post_meta( $post->ID, 'mp_admin_view' ) && get_post_meta( $post->ID, 'mp_added_noti', true ) ) {
						delete_post_meta( $post->ID, 'mp_added_noti' );
						update_option( 'wkmp_approved_product_count', (int) ( get_option( 'wkmp_approved_product_count', 1 ) - 1 ) );
					}
					do_action( 'wkmp_product_approve_disapprove', $author_id, $post->ID );
				}
			}
		}

		/**
		 * Action on product disapprove.
		 *
		 * @param int $post_id Post id.
		 */
		public function wkmp_action_on_product_disapprove( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( 'product' === $post_type ) {
				$author_id = get_post_field( 'post_author', $post_id );
				if ( ! is_super_admin( $author_id ) ) {
					if ( ! get_post_meta( $post_id, 'mp_admin_view' ) && get_post_meta( $post_id, 'mp_added_noti', true ) ) {
						delete_post_meta( $post_id, 'mp_added_noti' );
						update_option( 'wkmp_approved_product_count', (int) ( get_option( 'wkmp_approved_product_count', 1 ) - 1 ) );
					}
					do_action( 'wkmp_product_approve_disapprove', $author_id, $post_id, 'disapprove' );
				}
			}
		}

		/**
		 * Plugin row data.
		 *
		 * @param string $links Links.
		 * @param string $file Filepath.
		 *
		 * @return array
		 */
		public function wkmp_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( __FILE__ ) === $file ) {
				$row_meta = array(
					'docs'    => '<a href="' . esc_url( apply_filters( 'wk_marketplace_docs_url', 'https://webkul.com/blog/wordpress-woocommerce-marketplace/' ) ) . '" aria-label="' . esc_attr__( 'View Marketplace documentation', 'wk-marketplace' ) . '">' . esc_html__( 'Docs', 'wk-marketplace' ) . '</a>',
					'support' => '<a href="' . esc_url( apply_filters( 'wk_marketplace_support_url', 'https://webkul.uvdesk.com/' ) ) . '" aria-label="' . esc_attr__( 'Visit customer support', 'wk-marketplace' ) . '">' . esc_html__( 'Support', 'wk-marketplace' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		/**
		 * Adding seller refund data on order refunded.
		 *
		 * @param int $order_id Order id.
		 */
		public function wkmp_add_seller_refund_data_on_order_fully_refunded( $order_id ) {
			$wpdb_obj = $this->wpdb;
			if ( ! empty( $order_id ) ) {
				$sellers_order_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT DISTINCT seller_id, seller_amount FROM {$wpdb_obj->prefix}mporders WHERE order_id = %d", $order_id ) );

				$seller_data = array();
				if ( ! empty( $sellers_order_data ) ) {
					foreach ( $sellers_order_data as $key => $seller_order_data ) {
						$seller_id = $seller_order_data->seller_id;
						if ( array_key_exists( $seller_id, $seller_data ) ) {
							$seller_data[ $seller_id ] += $seller_order_data->seller_amount;
						} else {
							$seller_data[ $seller_id ] = $seller_order_data->seller_amount;
						}
					}
				}

				foreach ( $seller_data as $seller_id => $total_seller_amount ) {
					$shipping_cost = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value from {$wpdb_obj->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'shipping_cost' ", $seller_id, $order_id ) );

					if ( ! empty( $shipping_cost ) ) {
						$total_seller_amount += $shipping_cost;
					}

					$seller_order_tax = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'seller_order_tax' ", $seller_id, $order_id ) );

					if ( ! empty( $seller_order_tax ) ) { // If Tax Calculated.
						$total_seller_amount += (float) $seller_order_tax;
					}

					$seller_order_refund_data = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id=%d AND order_id=%d AND meta_key=%s", $seller_id, $order_id, '_wkmp_refund_status' ) );

					if ( empty( $seller_order_refund_data ) ) {
						$seller_order_refund_data = array(
							'line_items'      => array(),
							'refunded_amount' => wc_format_decimal( $total_seller_amount ),
						);

						$wpdb_obj->insert(
							"{$wpdb_obj->prefix}mporders_meta",
							array(
								'seller_id'  => $seller_id,
								'order_id'   => $order_id,
								'meta_key'   => '_wkmp_refund_status',
								'meta_value' => maybe_serialize( $seller_order_refund_data ),
							),
							array( '%d', '%d', '%s', '%s' )
						);
					} else {
						$seller_order_refund_data                    = maybe_unserialize( $seller_order_refund_data );
						$seller_order_refund_data['refunded_amount'] = wc_format_decimal( $total_seller_amount );
						$wpdb_obj->update(
							"{$wpdb_obj->prefix}mporders_meta",
							array(
								'meta_value' => maybe_serialize( $seller_order_refund_data ),
							),
							array(
								'seller_id' => $seller_id,
								'order_id'  => $order_id,
								'meta_key'  => '_wkmp_refund_status',
							),
							array( '%s' ),
							array( '%d', '%d', '%s' )
						);
					}
				}
			}
		}

		/**
		 * Add seller refund data on order refund.
		 *
		 * @param int   $refund_id Refund id.
		 * @param array $refund_args Refund args.
		 */
		public function wkmp_add_seller_refund_data_on_order_refund( $refund_id, $refund_args ) {
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			if ( is_admin() && ! empty( $page_name ) && 'order-history' !== $page_name && ! empty( $refund_id ) ) {
				$refund_line_items       = $refund_args['line_items'];
				$refund_total_tax_amount = 0;

				$refund_args['amount'] -= $refund_total_tax_amount;

				$order_refund = new Common\WKMP_Order_Refund();

				$order_refund->wkmp_set_refund_args( $refund_args );
				$order_refund->wkmp_set_seller_order_refund_data();
			}
		}

		/**
		 * Save meta info.
		 *
		 * @param int         $post_id Post id.
		 * @param \WC_Product $post Product post obj.
		 * @param boolean     $update Update.
		 *
		 * @hooked 'save_post' Action hook.
		 */
		public function wkmp_save_version_meta( $post_id, $post, $update ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $posted_data['blog_meta_box_nonce'] ) ) {
				$wpdb_obj = $this->wpdb;
				if ( wp_verify_nonce( $posted_data['blog_meta_box_nonce'], 'blog_save_meta_box_data' ) ) {
					$seller_id = isset( $_REQUEST['seller_id'] ) ? wc_clean( $_REQUEST['seller_id'] ) : 0;
					if ( ! empty( $seller_id ) ) {

						$table_name = "{$wpdb_obj->prefix}posts";
						$wpdb_obj->update( $table_name, array( 'post_author' => $seller_id ), array( 'ID' => $post_id ), array( '%d' ), array( '%d' ) );

						$product_obj = wc_get_product( $post_id );
						$variations  = $product_obj->get_children();

						foreach ( $variations as $child_post_id ) {
							$wpdb_obj->update( $table_name, array( 'post_author' => $seller_id ), array( 'ID' => $child_post_id ), array( '%d' ), array( '%d' ) );
						}
					}

					if ( isset( $posted_data['_wkmp_max_product_qty_limit'] ) ) {
						$qty_limit = $posted_data['_wkmp_max_product_qty_limit'];
						update_post_meta( $post_id, '_wkmp_max_product_qty_limit', $qty_limit );
					}
				}
			}
		}

		/**
		 * Save profile page data
		 *
		 * @param int $user_id user id.
		 */
		public function wkmp_save_extra_user_profile_fields( $user_id ) {
			$wpdb_obj     = $this->wpdb;
			$seller_id    = '';
			$seller_table = $wpdb_obj->prefix . 'mpsellerinfo';
			$res_query    = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT seller_id from  {$wpdb_obj->prefix}mpsellerinfo where user_id = %d", $user_id ) );

			if ( $res_query ) {
				$seller_id = $res_query[0]->seller_id;
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$shop_name   = isset( $posted_data['shopname'] ) ? $posted_data['shopname'] : '';
			$shop_name   = wp_strip_all_tags( $shop_name );
			$shop_url    = isset( $posted_data['shopurl'] ) ? wp_unslash( $posted_data['shopurl'] ) : '';
			$role        = isset( $posted_data['role'] ) ? wp_unslash( $posted_data['role'] ) : '';

			if ( 'wk_marketplace_seller' === $role ) {
				update_user_meta( $user_id, 'shop_name', $shop_name );
				if ( ! $res_query ) {
					$mp_sel_arr = array(
						'user_id'      => $user_id,
						'seller_key'   => 'role',
						'seller_value' => 'seller',
					);
					$wpdb_obj->insert(
						$seller_table,
						$mp_sel_arr,
						array(
							'%d',
							'%s',
							'%s',
						)
					);
				} else {
					$mp_sel_arr = array(
						'seller_key'   => 'role',
						'seller_value' => 'seller',
					);
					$wpdb_obj->update( $seller_table, $mp_sel_arr, array( 'user_id' => $user_id ) );
				}
			} else {
				if ( $seller_id ) {
					$admin_id = get_users(
						array(
							'role'   => 'administrator',
							'number' => '1',
						)
					)[0]->ID;

					$seller_product_data = get_posts(
						array(
							'author'    => $user_id,
							'post_type' => 'product',
						)
					);

					foreach ( $seller_product_data as $value ) {
						wp_update_post(
							array(
								'ID'          => $value->ID,
								'post_author' => $admin_id,
							)
						);
					}
					$wpdb_obj->delete( $seller_table, array( 'seller_id' => $seller_id ), array( '%d' ) );
				}
			}
		}

		/**
		 * Validation in profile fields
		 *
		 * @param array  $errors Errors.
		 * @param string $update Update.
		 * @param object $user User data.
		 */
		public function wkmp_validate_extra_profile_fields( &$errors, $update = null, &$user = null ) {
			if ( isset( $user->ID ) ) {
				$seller_id = '';
				$result    = '';
				$wpdb_obj  = $this->wpdb;

				$seller_table = $wpdb_obj->prefix . 'mpsellerinfo';

				$res_query = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT seller_id from {$wpdb_obj->prefix}mpsellerinfo where user_id = %d", $user->ID ) );

				if ( $res_query ) {
					$seller_id = $res_query[0]->seller_id;
				}

				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$shop_url    = isset( $posted_data['shopurl'] ) ? wp_unslash( $posted_data['shopurl'] ) : '';
				$role        = isset( $posted_data['role'] ) ? wp_unslash( $posted_data['role'] ) : '';
				$seller_key  = 'role';

				if ( 'wk_marketplace_seller' === $role ) {
					$sql    = $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->prefix}usermeta where (meta_key = 'shop_address') AND (meta_value = %s	)", $shop_url );
					$result = $wpdb_obj->get_results( $sql );

					if ( $result && $result[0]->user_id && intval( $result[0]->user_id ) !== intval( $user->ID ) ) {
						$shop_err = '<strong>' . __( 'ERROR', 'wk-marketplace' ) . '</strong>: ' . __( 'The shop URL already EXISTS please try different shop url', 'wk-marketplace' ) . '.';
						$errors->add( 'invalid-shop-url', $shop_err );
					} else {
						$shop_url   = get_user_meta( $user->ID, 'shop_address', true ) ? get_user_meta( $user->ID, 'shop_address', true ) : $shop_url;
						$user_creds = array(
							'ID'            => $user->ID,
							'user_nicename' => "$shop_url",
						);

						wp_update_user( $user_creds );

						$check = update_user_meta( $user->ID, 'shop_address', $shop_url );

						if ( $check ) {
							if ( isset( $seller_id ) && ! empty( $seller_id ) ) {
								$seller     = array(
									'user_id'      => $user->ID,
									'seller_key'   => $seller_key,
									'seller_value' => 'seller',
								);
								$seller_res = $wpdb_obj->update( $seller_table, $seller, array( 'seller_id' => $seller_id ) );
							} else {
								$seller     = array(
									'user_id'      => $user->ID,
									'seller_key'   => $seller_key,
									'seller_value' => 'seller',
								);
								$seller_res = $wpdb_obj->insert( $seller_table, $seller );
							}
						}
					}
				}
			}
		}

		/**
		 * Reset shipping method.
		 */
		public function wkmp_reset_previous_chosen_shipping_method() {
			$check = get_option( 'wkmp_shipping_option', 'marketplace' );

			if ( ( is_checkout() || is_cart() ) && ! empty( WC()->session ) ) {
				$wkmp_shipping = WC()->session->get( 'wkmp_shipping' );
				if ( empty( $wkmp_shipping ) ) {
					WC()->session->set( 'wkmp_shipping', $check );
					$check = true;
				} elseif ( $check !== $wkmp_shipping ) {
					WC()->session->set( 'wkmp_shipping', $check );
					$check = true;
				} else {
					$check = false;
				}
				if ( $check ) {
					update_option( 'woocommerce_shipping_debug_mode', 'yes' );
					if ( get_current_user_id() && get_user_meta( get_current_user_id(), 'shipping_method', true ) ) {
						delete_user_meta( get_current_user_id(), 'shipping_method' );
					}
					if ( apply_filters( 'wkmp_unset_shipping_methods', true ) ) {
						WC()->session->__unset( 'chosen_shipping_methods' );
					}
				} else {
					update_option( 'woocommerce_shipping_debug_mode', 'no' );
				}
			} else {
				update_option( 'woocommerce_shipping_debug_mode', 'no' );
			}
		}

		/**
		 * Add link to admin bar.
		 *
		 * @param bool $admin_bar admin bar value.
		 */
		public function wkmp_add_toolbar_items( $admin_bar ) {
			global $current_user;

			if ( in_array( 'wk_marketplace_seller', $current_user->roles, true ) && get_option( '_wkmp_separate_seller_dashboard' ) ) {
				$admin_bar->add_menu(
					array(
						'id'    => 'mp-notification',
						'title' => esc_html__( 'Seller dashboard', 'wk-marketplace' ),
						'meta'  => array(
							'title' => esc_html__( 'Seller dashboard', 'wk-marketplace' ),
						),
					)
				);
				$nonce = wp_create_nonce( 'ajaxnonce' );
				$admin_bar->add_menu(
					array(
						'parent' => 'mp-notification',
						'id'     => 'mp-seperate-seller-dashboard',
						'title'  => esc_html__( 'Default Seller Dashboard', 'wk-marketplace' ),
						'href'   => '?_wp_nonce=' . $nonce,
					)
				);
			}
		}

		/**
		 * Function to restrict media.
		 *
		 * @param object $wp_query_obj Query object.
		 */
		public static function wkmp_restrict_media_library( $wp_query_obj ) {
			global $current_user, $pagenow;

			if ( ! is_a( $current_user, 'WP_User' ) ) {
				return;
			}

			$action = isset( $_REQUEST['action'] ) ? wc_clean( $_REQUEST['action'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'admin-ajax.php' !== $pagenow || 'query-attachments' !== $action ) {
				return;
			}

			if ( ! in_array( $pagenow, array( 'upload.php', 'admin-ajax.php' ), true ) ) {
				return;
			}

			if ( ! current_user_can( 'delete_pages' ) ) {
				$wp_query_obj->set( 'author', $current_user->ID );
			}
		}

		/**
		 * Include widgets.
		 */
		public function wkmp_include_widgets() {
			require WKMP_PLUGIN_FILE . 'widgets/class-wkmp-widget-seller-list.php';
			require WKMP_PLUGIN_FILE . 'widgets/class-wkmp-widget-seller-panel.php';

			register_widget( 'WKMP_Widget_Seller_List' );
			register_widget( 'WKMP_Widget_Seller_Panel' );
		}

		/**
		 * Remove sidebar seller pages.
		 *
		 * @param object $sidebars_widgets Sidebar widgets.
		 *
		 * @return false[]|mixed
		 */
		public function wkmp_remove_sidebar_seller_page( $sidebars_widgets ) {
			global $wkmarketplace;

			$page_name  = $wkmarketplace->seller_page_slug;
			$page_array = array(
				get_option( '_wkmp_store_endpoint', 'store' ),
				get_option( '_wkmp_seller_product_endpoint', 'seller-product' ),
				'feedback',
				'add-feedback',
			);
			if ( is_page( $page_name ) && ! in_array( get_query_var( 'main_page' ), $page_array, true ) ) {
				$sidebars_widgets = array( false );
			}

			return $sidebars_widgets;
		}

		/**
		 * Validate and upload/replace seller images.
		 *
		 * @param array $profile_data Seller form posted data.
		 * @param int   $seller_id Seller id.
		 */
		public function wkmp_process_seller_profile_data( $profile_data, $seller_id ) {
			$errors = array();

			include_once ABSPATH . 'wp-admin/includes/image.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/media.php';

			if ( isset( $profile_data['wkmp_seller_email'] ) && wp_unslash( $profile_data['wkmp_seller_email'] ) && false === filter_var( $profile_data['wkmp_seller_email'], FILTER_VALIDATE_EMAIL ) ) {
				$errors['wkmp_seller_email'] = esc_html__( 'Enter the valid E-Mail', 'wk-marketplace' );
			} else {
				$user_email  = sanitize_email( wp_unslash( $profile_data['wkmp_seller_email'] ) );
				$seller_info = get_user_by( 'email', $user_email );

				if ( $seller_info instanceof \WP_User && ( intval( $seller_id ) !== intval( $seller_info->ID ) ) ) {
					$errors['wkmp_seller_email'] = esc_html__( 'Email already exists.', 'wk-marketplace' );
				}
			}

			if ( isset( $profile_data['wkmp_first_name'] ) && ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $profile_data['wkmp_first_name'] ) ) {
				$errors['wkmp_first_name'] = esc_html__( 'Only letters and numbers are allowed.', 'wk-marketplace' );
			}

			if ( ! empty( $profile_data['wkmp_last_name'] ) && ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $profile_data['wkmp_last_name'] ) ) {
				$errors['wkmp_last_name'] = esc_html__( 'Only letters and numbers are allowed.', 'wk-marketplace' );
			}

			if ( isset( $profile_data['wkmp_shop_name'] ) && ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $profile_data['wkmp_shop_name'] ) ) {
				$errors['wkmp_shop_name'] = esc_html__( 'Enter a valid shop name.', 'wk-marketplace' );
			}

			if ( isset( $profile_data['wkmp_shop_phone'] ) && ! \WC_Validation::is_phone( $profile_data['wkmp_shop_phone'] ) ) {
				$errors['wkmp_shop_phone'] = esc_html__( 'Enter the valid phone no', 'wk-marketplace' );
			}

			if ( isset( $profile_data['wkmp_shop_postcode'] ) && ! \WC_Validation::is_postcode( $profile_data['wkmp_shop_postcode'], $profile_data['wkmp_shop_country'] ) ) {
				$errors['wkmp_shop_postcode'] = esc_html__( 'Enter the valid post code', 'wk-marketplace' );
			}

			if ( isset( $_FILES['wkmp_avatar_file'] ) && isset( $_FILES['wkmp_avatar_file']['name'] ) && wc_clean( $_FILES['wkmp_avatar_file']['name'] ) ) {
				$message = $this->wkmp_validate_image( wc_clean( $_FILES['wkmp_avatar_file'] ) );
				if ( $message ) {
					$errors['wkmp_avatar_file'] = $message;
				} else {
					$profile_data['wkmp_avatar_id'] = media_handle_upload( 'wkmp_avatar_file', $seller_id );
				}
			}

			if ( isset( $_FILES['wkmp_logo_file'] ) && isset( $_FILES['wkmp_logo_file']['name'] ) && wc_clean( $_FILES['wkmp_logo_file']['name'] ) ) {
				$message = $this->wkmp_validate_image( wc_clean( $_FILES['wkmp_logo_file'] ) );
				if ( $message ) {
					$errors['wkmp_logo_file'] = $message;
				} else {
					$profile_data['wkmp_logo_id'] = media_handle_upload( 'wkmp_logo_file', $seller_id );
				}
			}

			if ( isset( $_FILES['wkmp_banner_file'] ) && isset( $_FILES['wkmp_banner_file']['name'] ) && wc_clean( $_FILES['wkmp_banner_file']['name'] ) ) {
				$message = $this->wkmp_validate_image( wc_clean( $_FILES['wkmp_banner_file'] ) );
				if ( $message ) {
					$errors['wkmp_banner_file'] = $message;
				} else {
					$profile_data['wkmp_banner_id'] = media_handle_upload( 'wkmp_banner_file', $seller_id );
				}
			}

			if ( empty( $errors ) ) {
				$this->wkmp_update_seller_profile( $profile_data, $seller_id );
			} else {
				$_POST['wkmp_errors'] = $errors;
			}

		}

		/**
		 * Updating seller profile data after validation successful.
		 *
		 * @param array $posted_data Profile data.
		 * @param int   $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_update_seller_profile( $posted_data, $seller_id ) {
			$final_data = array(
				'first_name'                 => wp_unslash( $posted_data['wkmp_first_name'] ),
				'last_name'                  => wp_unslash( $posted_data['wkmp_last_name'] ),
				'shop_name'                  => empty( $posted_data['wkmp_shop_name'] ) ? '' : wp_unslash( $posted_data['wkmp_shop_name'] ),
				'about_shop'                 => empty( $posted_data['wkmp_about_shop'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_about_shop'] ),
				'billing_address_1'          => empty( $posted_data['wkmp_shop_address_1'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_shop_address_1'] ),
				'billing_address_2'          => empty( $posted_data['wkmp_shop_address_2'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_shop_address_2'] ),
				'billing_city'               => empty( $posted_data['wkmp_shop_city'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_shop_city'] ),
				'billing_postcode'           => empty( $posted_data['wkmp_shop_postcode'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_shop_postcode'] ),
				'billing_phone'              => empty( $posted_data['wkmp_shop_phone'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_shop_phone'] ),
				'billing_country'            => empty( $posted_data['wkmp_shop_country'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_shop_country'] ),
				'mp_seller_payment_details'  => empty( $posted_data['wkmp_payment_details'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_payment_details'] ),
				'shop_banner_visibility'     => empty( $posted_data['wkmp_display_banner'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_display_banner'] ),
				'_thumbnail_id_avatar'       => empty( $posted_data['wkmp_avatar_id'] ) ? '' : intval( $posted_data['wkmp_avatar_id'] ),
				'_thumbnail_id_shop_banner'  => empty( $posted_data['wkmp_banner_id'] ) ? '' : intval( $posted_data['wkmp_banner_id'] ),
				'_thumbnail_id_company_logo' => empty( $posted_data['wkmp_logo_id'] ) ? '' : intval( $posted_data['wkmp_logo_id'] ),
				'social_facebook'            => empty( $posted_data['wkmp_settings']['social']['fb'] ) ? '' : filter_var( wp_unslash( $posted_data['wkmp_settings']['social']['fb'] ), FILTER_SANITIZE_URL ),
				'social_instagram'           => empty( $posted_data['wkmp_settings']['social']['insta'] ) ? '' : filter_var( wp_unslash( $posted_data['wkmp_settings']['social']['insta'] ), FILTER_SANITIZE_URL ),
				'social_twitter'             => empty( $posted_data['wkmp_settings']['social']['twitter'] ) ? '' : filter_var( wp_unslash( $posted_data['wkmp_settings']['social']['twitter'] ), FILTER_SANITIZE_URL ),
				'social_linkedin'            => empty( $posted_data['wkmp_settings']['social']['linkedin'] ) ? '' : filter_var( wp_unslash( $posted_data['wkmp_settings']['social']['linkedin'] ), FILTER_SANITIZE_URL ),
				'social_youtube'             => empty( $posted_data['wkmp_settings']['social']['youtube'] ) ? '' : filter_var( wp_unslash( $posted_data['wkmp_settings']['social']['youtube'] ), FILTER_SANITIZE_URL ),
			);

			$user_state = empty( $posted_data['wkmp_shop_state'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_shop_state'] );

			if ( $user_state ) {
				$country = get_user_meta( $seller_id, 'wkmp_shop_country', true );
				if ( WC()->countries->get_states( $country ) ) {
					$states = WC()->countries->get_states( $country );
					if ( isset( $states[ $user_state ] ) ) {
						$final_data['billing_state'] = $user_state;
					} elseif ( in_array( $user_state, $states, true ) ) {
						$state_code                  = array_search( $user_state, $states, true );
						$final_data['billing_state'] = $state_code;
					}
				} else {
					$final_data['billing_state'] = $user_state;
				}
			}

			$user_email  = sanitize_email( wp_unslash( $posted_data['wkmp_seller_email'] ) );
			$seller_info = get_user_by( 'email', $user_email );

			if ( ! $seller_info instanceof \WP_User || ( $seller_info instanceof \WP_User && $user_email !== $seller_info->user_email ) ) {
				$user_data = array(
					'ID'         => $seller_id,
					'user_email' => $user_email,
				);
				wp_update_user( $user_data );
			}

			foreach ( $final_data as $meta_key => $meta_value ) {
				update_user_meta( $seller_id, $meta_key, $meta_value );
			}

			do_action( 'mp_save_seller_profile_details', $posted_data, $seller_id );
			do_action( 'marketplace_save_seller_payment_details' );
		}

		/**
		 * Validate image.
		 *
		 * @param array $file File.
		 *
		 * @return string
		 */
		private function wkmp_validate_image( $file ) {
			$img_error = '';

			if ( isset( $file['size'] ) && $file['size'] > wp_max_upload_size() ) {
				$img_error = esc_html__( 'File size too large ', 'wk-marketplace' ) . '[ <= ' . number_format( wp_max_upload_size() / 1048576 ) . ' MB ]';
			}

			$file_type = ' ';
			$file_name = empty( $file['tmp_name'] ) ? '' : $file['tmp_name'];

			if ( function_exists( 'mime_content_type' ) ) {
				$file_type = mime_content_type( $file_name );
			} else {
				$file_type = $this->wkmp_get_mime_type( $file );
			}

			$allowed_types = array(
				'image/png',
				'image/jpeg',
				'image/jpg',
			);

			if ( ! $img_error && ! in_array( $file_type, $allowed_types, true ) ) {
				$img_error = esc_html__( 'Upload valid image only', 'wk-marketplace' ) . '[ png, jpeg, jpg ]';
			}

			return $img_error;
		}


		/**
		 * Custom Mime type content function if extension not installed on server.
		 * Or php version not supporting this function.
		 * Or issue due to incorrect php.ini file on client site.
		 *
		 * @param array $filename File name.
		 *
		 * @return string
		 */
		public function wkmp_get_mime_type( $filename ) {
			$mime_types = array(
				// Images.
				'png'  => 'image/png',
				'jpe'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg'  => 'image/jpeg',
				'gif'  => 'image/gif',
				'bmp'  => 'image/bmp',
				'ico'  => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif'  => 'image/tiff',
				'svg'  => 'image/svg+xml',
				'svgz' => 'image/svg+xml',
			);

			$file_name = empty( $filename['tmp_name'] ) ? '' : $filename['tmp_name'];
			$value     = empty( $file_name ) ? array() : explode( '.', $file_name );

			if ( is_iterable( $value ) && count( $value ) < 2 ) {
				$file_name = empty( $filename['name'] ) ? '' : $filename['name'];
				$value     = empty( $file_name ) ? array() : explode( '.', $file_name );
			}

			$ext = strtolower( array_pop( $value ) );

			if ( array_key_exists( $ext, $mime_types ) ) {
				return $mime_types[ $ext ];
			} elseif ( function_exists( 'finfo_open' ) ) {
				$finfo    = finfo_open( FILEINFO_MIME );
				$mimetype = finfo_file( $finfo, $file_name );
				finfo_close( $finfo );
				return $mimetype;
			}

			return 'application/octet-stream';
		}


		public function hide_my_item_meta( $hidden_meta ) {

			$hidden_meta[] = '_rfq_commission';

			return $hidden_meta;
		}
	}
}
