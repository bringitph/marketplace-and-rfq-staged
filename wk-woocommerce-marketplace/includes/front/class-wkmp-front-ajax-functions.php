<?php
/**
 * Front ajax functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Front_Ajax_Functions' ) ) {
	/**
	 * Front ajax functions
	 */
	class WKMP_Front_Ajax_Functions {
		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Front_Ajax_Functions constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Check availability of shop url requested.
		 *
		 * @return void
		 */
		public function wkmp_check_shop_url() {
			global $wkmarketplace;
			$response = array();

			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$slug = isset( $_POST['shop_slug'] ) ? wc_clean( $_POST['shop_slug'] ) : '';  //phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( ! empty( $slug ) ) {
					$user = $wkmarketplace->wkmp_get_seller_id_by_shop_address( $slug );

					if ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $slug ) ) {
						$response = array(
							'error'   => true,
							'message' => esc_html__( 'You can not use special characters in shop url except HYPHEN(-).', 'wk-marketplace' ),
						);
					} elseif ( ctype_space( $slug ) ) {

						$response = array(
							'error'   => true,
							'message' => esc_html__( 'White space(s) aren\'t allowed in shop url.', 'wk-marketplace' ),
						);
					} elseif ( str_contains( $slug, ' ' ) ) {
						$response = array(
							'error'   => true,
							'message' => __( "White space(s) aren't allowed in shop url.", 'wk-marketplace' ),
						);
					} elseif ( $user ) {
						$response = array(
							'error'   => true,
							'message' => esc_html__( 'This shop URl already EXISTS, please try different shop url.', 'wk-marketplace' ),
						);
					} else {
						$response = array(
							'error'   => false,
							'message' => esc_html__( 'This shop URl is available, kindly proceed.', 'wk-marketplace' ),
						);
					}
				} else {
					$response = array(
						'error'   => true,
						'message' => esc_html__( 'Shop url not found!', 'wk-marketplace' ),
					);
				}
			} else {
				$response = array(
					'error'   => true,
					'message' => esc_html__( 'Security check failed!', 'wk-marketplace' ),
				);
			}

			wp_send_json( $response );
		}

		/**
		 * Add favourite seller.
		 */
		public function wkmp_add_favourite_seller() {
			$json = array();
			if ( ! check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$json['error']   = true;
				$json['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $json );
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();//phpcs:ignore WordPress.Security.NonceVerification.Missing
			$seller_id   = isset( $posted_data['seller_id'] ) ? $posted_data['seller_id'] : 0;
			$customer_id = isset( $posted_data['customer_id'] ) ? $posted_data['customer_id'] : 0;

			if ( $seller_id > 0 && $customer_id > 0 ) {
				$sellers = get_user_meta( $customer_id, 'favourite_seller', true );
				$sellers = $sellers ? explode( ',', $sellers ) : array();

				$key = array_search( $seller_id, $sellers, true );
				if ( false !== $key ) {
					unset( $sellers[ $key ] );
					$json['success'] = 'removed';
					$json['message'] = esc_html__( 'Seller removed from your favourite seller list.', 'wk-marketplace' );
				} else {
					$sellers[]       = $seller_id;
					$json['success'] = 'added';
					$json['message'] = esc_html__( 'Seller added to your favourite seller list.', 'wk-marketplace' );
				}

				delete_user_meta( $customer_id, 'favourite_seller' );
				add_user_meta( $customer_id, 'favourite_seller', implode( ',', $sellers ) );
			}
			wp_send_json( $json );
		}

		/**
		 * State by country code.
		 */
		public function wkmp_get_state_by_country_code() {
			$json = array();
			if ( ! check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) && ! check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) ) {
				$json['error']   = true;
				$json['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $json );
			}

			$country_code = isset( $_POST['country_code'] ) ? wc_clean( $_POST['country_code'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( ! empty( $country_code ) ) {
				$states = WC()->countries->get_states( $country_code );
				$html   = '';
				if ( $states ) {
					$html .= '<select name="billing_state" id="billing-state" class="form-control">';
					$html .= '<option value="">' . esc_html__( 'Select state', 'wk-marketplace' ) . '</option>';
					foreach ( $states as $key => $state ) {
						$html .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $state ) . '</option>';
					}
					$html .= '</select>';
				} else {
					$html .= '<input id="billing-state" type="text" placeholder="' . esc_attr__( 'State', 'wk-marketplace' ) . '" name="billing_state" class="form-control" />';
				}
				$json['success'] = true;
				$json['html']    = $html;
			}

			wp_send_json( $json );
		}

		/**
		 * Add shipping Cost to zone.
		 */
		public function wkmp_save_shipping_cost() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$ship_cost  = isset( $_POST['ship_cost'] ) ? wc_clean( $_POST['ship_cost'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$final_data = array();
				parse_str( $ship_cost, $final_data );
				$instance_id     = absint( $final_data['instance_id'] );
				$shipping_method = \WC_Shipping_Zones::get_shipping_method( $instance_id );
				$shipping_method->set_post_data( $final_data );
				$shipping_method->process_admin_options();
				die;
			}
		}

		/**
		 * Delete shipping Class.
		 */
		public function wkmp_delete_shipping_class() {
			global $wkmarketplace;
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$term_id = isset( $_POST['get-term'] ) ? wc_clean( $_POST['get-term'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$resp    = array( 'success' => true );

				if ( ! empty( $term_id ) ) {
					$user_id         = get_current_user_id();
					$term_id         = intval( wc_clean( $term_id ) );
					$res             = wp_delete_term( $term_id, 'product_shipping_class' );
					$resp['success'] = $res;

					$notice_data = array(
						'wkmp_ship_action' => 'deleted',
					);
					update_user_meta( $user_id, '_wkmp_shipping_notice_data', $notice_data );

					$shop_address     = get_user_meta( $user_id, 'shop_address', true );
					$resp['redirect'] = home_url( $wkmarketplace->seller_page_slug . '/' . $shop_address . '/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '?action=add' );

				}
				wp_send_json( $resp );
			}
		}

		/**
		 * Add shipping Class
		 */
		public function wkmp_add_shipping_class() {
			global $wkmarketplace;
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$data       = empty( $_POST['data'] ) ? array() : $_POST['data']; //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$final_data = array();
				$arr        = array();
				$new_arr    = array();
				$json       = array(
					'redirect' => '',
					'success'  => false,
				);
				parse_str( $data, $final_data );

				foreach ( $final_data as $s_key => $s_value ) {
					$i = 0;
					$j = 0;
					foreach ( $s_value as $main_key => $main_value ) {
						if ( is_int( $main_key ) ) {
							$arr[ $i ][ $s_key ] = $main_value;
							$i ++;
						} else {
							$new_arr[ $j ][ $s_key ] = $main_value;
							$j ++;
						}
					}
				}

				foreach ( $arr as $arr_value ) {
					if ( array_key_exists( 'term_id', $arr_value ) ) {
						wp_update_term( $arr_value['term_id'], 'product_shipping_class', $arr_value );
					}
				}

				$user_id = get_current_user_id();

				foreach ( $new_arr as $new_arr_value ) {
					$term          = wp_insert_term( $new_arr_value['name'], 'product_shipping_class', $new_arr_value );
					$seller_sclass = get_user_meta( $user_id, 'shipping-classes', true );
					if ( ! empty( $seller_sclass ) ) {
						$seller_sclass = maybe_unserialize( $seller_sclass );
						array_push( $seller_sclass, $term['term_id'] );
						$seller_sclass_update = maybe_serialize( $seller_sclass );
						update_user_meta( $user_id, 'shipping-classes', $seller_sclass_update );
					} else {
						$term_arr   = array();
						$term_arr[] = $term['term_id'];
						$term_arr   = maybe_serialize( $term_arr );
						add_user_meta( $user_id, 'shipping-classes', $term_arr );
					}
				}
				$notice_data = array(
					'wkmp_ship_action' => 'updated',
				);
				update_user_meta( $user_id, '_wkmp_shipping_notice_data', $notice_data );

				$shop_address     = get_user_meta( $user_id, 'shop_address', true );
				$json['redirect'] = home_url( $wkmarketplace->seller_page_slug . '/' . $shop_address . '/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) . '?action=add' );
				wp_send_json( $json );
			}
		}

		/**
		 * Add shipping method to zone
		 */
		public function wkmp_add_shipping_method() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$zone_id      = isset( $_POST['zone-id'] ) ? wc_clean( $_POST['zone-id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$ship_method  = isset( $_POST['ship-method'] ) ? wc_clean( $_POST['ship-method'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$current_zone = new \WC_Shipping_Zone( $zone_id );
				$confirm      = $current_zone->add_shipping_method( $ship_method );
				$result       = array( 'success' => $confirm );
				wp_send_json( $result );
			}
		}

		/**
		 * Delete Zone details list ajax.
		 */
		public function wkmp_del_zone() {
			global $wkmarketplace;
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$wpdb_obj          = $this->wpdb;
				$current_seller_id = get_current_user_id();
				$zone_id           = isset( $_POST['zone-id'] ) ? wc_clean( $_POST['zone-id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing

				// Using where formatting.
				$zone      = \WC_Shipping_Zones::get_zone( $zone_id );
				$zone_name = $zone->get_data()['zone_name'];

				$wpdb_obj->delete( $wpdb_obj->prefix . 'mpseller_meta', array( 'zone_id' => $zone_id ), array( '%d' ) );
				\WC_Shipping_Zones::delete_zone( $zone_id );

				$notice_data = array(
					'action'    => 'Deleted',
					'zone_name' => $zone_name,
				);
				update_user_meta( $current_seller_id, '_wkmp_shipping_notice_data', $notice_data );

				$json             = array();
				$shop_address     = get_user_meta( $current_seller_id, 'shop_address', true );
				$json['message']  = esc_html__( 'Shipping class has been saved', 'wk-marketplace' );
				$json['redirect'] = home_url( $wkmarketplace->seller_page_slug . '/' . $shop_address . '/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) );
				wp_send_json( $json );
			}
		}

		/**
		 * Delete Shipping Method.
		 */
		public function wkmp_delete_shipping_method() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$result      = array( 'success' => false );
				$wpdb_obj    = $this->wpdb;
				$table_name  = $wpdb_obj->prefix . 'woocommerce_shipping_zone_methods';
				$zone_id     = isset( $_POST['zone-id'] ) ? wc_clean( $_POST['zone-id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$instance_id = isset( $_POST['instance-id'] ) ? wc_clean( $_POST['instance-id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$res         = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT method_id FROM {$wpdb_obj->prefix}woocommerce_shipping_zone_methods WHERE zone_id = %d AND instance_id = %d", $zone_id, $instance_id ) );
				$response    = $wpdb_obj->delete(
					$table_name,
					array(
						'zone_id'     => $zone_id,
						'instance_id' => $instance_id,
					),
					array( '%d' )
				);

				if ( $response ) {
					delete_option( 'woocommerce_' . $res->method_id . '_' . $instance_id . '_settings' );
					$result['success'] = true;
				}
				wp_send_json( $result );
			}

		}

		/**
		 * Marketplace variation function
		 *
		 * @param int $var_id Variable id.
		 */
		public function wkmp_marketplace_attributes_variation( $var_id ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $posted_data['product'] ) && ! empty( $posted_data['product'] ) ) {
				$wk_pro_id    = $posted_data['product'];
				$post_title   = sprintf( /* translators: %d Product id. */ esc_html__( 'Variation # %d of Product', 'wk-marketplace' ), $wk_pro_id );
				$post_name    = 'product-' . $wk_pro_id . '-variation';
				$product_data = array(
					'post_author'           => get_current_user_id(),
					'post_date'             => '',
					'post_date_gmt'         => '',
					'post_content'          => '',
					'post_content_filtered' => '',
					'post_title'            => $post_title,
					'post_excerpt'          => '',
					'post_status'           => 'publish',
					'post_type'             => 'product_variation',
					'comment_status'        => 'open',
					'ping_status'           => 'open',
					'post_password'         => '',
					'post_name'             => $post_name,
					'to_ping'               => '',
					'pinged'                => '',
					'post_modified'         => '',
					'post_modified_gmt'     => '',
					'post_parent'           => $wk_pro_id,
					'menu_order'            => '',
					'guid'                  => '',
				);

				wp_set_object_terms( $wk_pro_id, 'variable', 'product_type' );
				$var_id = wp_insert_post( $product_data );
				\WC_Product_Variable::sync( $wk_pro_id );

				require_once WKMP_PLUGIN_FILE . 'templates/front/seller/product/wkmp-variations.php';
				die;
			} else {
				$wk_pro_id = $var_id;

				$args = array(
					'post_parent'    => $wk_pro_id,
					'post_type'      => 'product_variation',
					'posts_per_page' => - 1,
					'post_status'    => 'publish',
				);

				$children_array = get_children( $args );
				$i              = 0;

				foreach ( $children_array as $var_att ) {
					$this->wkmp_attribute_variation_data( $var_att->ID, $wk_pro_id );
					$i ++;
				}
			}
			if ( isset( $posted_data['product'] ) ) {
				wp_die();
			}
		}

		/**
		 * Attribute variation data.
		 *
		 * @param int $var_id Variable id.
		 * @param int $wk_pro_id Product id.
		 */
		public function wkmp_attribute_variation_data( $var_id, $wk_pro_id ) {
			require_once WKMP_PLUGIN_FILE . 'templates/front/seller/product/wkmp-variations.php';
		}

		/**
		 * Remove variation attribute.
		 */
		public function wkmp_attributes_variation_remove() {
			$result = array(
				'success' => false,
				'msg'     => esc_html__( 'Some error in removing, kindly reload the page and try again!!', 'wk-marketplace' ),
			);
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$var_id = isset( $_POST['var_id'] ) ? wc_clean( $_POST['var_id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( $var_id > 0 ) {
					wp_delete_post( $var_id );
					$result['success'] = true;
					$result['msg']     = esc_html__( 'The variation has been removed successfully.', 'wk-marketplace' );
				}
			}
			wp_send_json( $result );
		}

		/**
		 * Product sku validation.
		 */
		public function wkmp_product_sku_validation() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$wpdb_obj = $this->wpdb;
				$chk_sku  = isset( $_POST['psku'] ) ? wc_clean( $_POST['psku'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$response = array(
					'success' => true,
					'message' => esc_html__( 'SKU is OK', 'wk-marketplace' ),
				);

				if ( ! empty( $chk_sku ) ) {
					$sku_exist = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}postmeta WHERE meta_key='_sku' AND meta_value=%s", $chk_sku ) );
					if ( ! empty( $sku_exist ) ) {
						$response = array(
							'success' => false,
							'message' => esc_html__( 'SKU already exist please select another SKU', 'wk-marketplace' ),
						);
					}
				}
				wp_send_json( $response );
			}
		}

		/**
		 * Gallery image delete.
		 */
		public function wkmp_productgallary_image_delete() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$img_id     = isset( $_POST['img_id'] ) ? wc_clean( $_POST['img_id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$ip         = explode( 'i_', $img_id );
				$img_id     = get_post_meta( $ip[0], '_product_image_gallery', true );
				$arr        = array_diff( explode( ',', $img_id ), array( $ip[1] ) );
				$remain_ids = implode( ',', $arr );
				update_post_meta( $ip[0], '_product_image_gallery', $remain_ids );
				wp_send_json( $remain_ids );
			}
		}

		/**
		 * Downloadable file adding.
		 */
		public function wkmp_downloadable_file_add() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$y = isset( $_POST['var_id'] ) ? wc_clean( $_POST['var_id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$i = isset( $_POST['eleme_no'] ) ? wc_clean( $_POST['eleme_no'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				?>
				<div class="tr_div">
					<div>
						<label for="downloadable_upload_file_name_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'File Name', 'wk-marketplace' ); ?></label>
						<input type="text" class="input_text" placeholder="File Name" id="downloadable_upload_file_name_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>" name="_mp_variation_downloads_files_name[<?php echo esc_attr( $y ); ?>][<?php echo esc_attr( $i ); ?>]" value="">
					</div>
					<div class="file_url">
						<label for="downloadable_upload_file_url_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'File Url', 'wk-marketplace' ); ?></label>
						<input type="text" class="input_text" placeholder="http://" id="downloadable_upload_file_url_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>" name="_mp_variation_downloads_files_url[<?php echo esc_attr( $y ); ?>][<?php echo esc_attr( $i ); ?>]" value="">
						<a href="javascript:void(0);" class="button wkmp_downloadable_upload_file" id="<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'Choose&nbsp;file', 'wk-marketplace' ); ?></a>
						<a href="javascript:void(0);" class="delete mp_var_del" id="mp_var_del_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'Delete', 'wk-marketplace' ); ?></a>
					</div>
					<div class="file_url_choose">

					</div>
				</div>
				<?php
				die;
			}
		}

		/**
		 * Change seller dashboard settings.
		 */
		public function wkmp_change_seller_dashboard() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				global $wkmarketplace;
				$data      = array();
				$change_to = isset( $_POST['change_to'] ) ? wc_clean( $_POST['change_to'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( ! empty( $change_to ) ) {
					$current_user = wp_get_current_user();
					$current_dash = get_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', true );
					if ( 'front_dashboard' === $change_to ) {
						if ( $current_dash ) {
							update_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', null );
							$data['redirect'] = esc_url( site_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) ) );
						}
					} elseif ( 'backend_dashboard' === $change_to ) {
						update_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', true );
						$data['redirect'] = esc_url( admin_url( 'admin.php?page=seller' ) );
					}
				}

				wp_die( wp_json_encode( $data ) );
			}
		}
	}
}
