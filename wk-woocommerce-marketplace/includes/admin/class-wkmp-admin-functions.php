<?php
/**
 * Admin End Functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Admin as Helper;

if ( ! class_exists( 'WKMP_Admin_Functions' ) ) {

	/**
	 * Admin hooks class
	 */
	class WKMP_Admin_Functions {
		/**
		 * Template handler
		 *
		 * @var object
		 */
		protected $template_handler;

		/**
		 * Seller class object.
		 *
		 * @var Helper\WKMP_Seller_Data
		 */
		protected $seller_obj;

		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Functions constructor.
		 *
		 * @param null $template_handler Template handler.
		 */
		public function __construct( $template_handler = null ) {
			global $wpdb;
			$this->template_handler = $template_handler;
			$this->seller_obj       = new Helper\WKMP_Seller_Data();
			$this->wpdb             = $wpdb;
		}

		/**
		 * Prevent seller admin access.
		 *
		 * @return void
		 */
		public function wkmp_prevent_seller_admin_access() {
			global $wkmarketplace;
			if ( wp_doing_ajax() ) {
				return;
			}
			$redirect = esc_url( site_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) ) );
			if ( is_user_logged_in() ) {
				$user         = wp_get_current_user();
				$current_dash = get_user_meta( $user->ID, 'wkmp_seller_backend_dashboard', true );
				if ( in_array( 'wk_marketplace_seller', $user->roles, true ) && empty( $current_dash ) ) {
					wp_safe_redirect( $redirect );
					exit;
				}
			}
		}

		/**
		 * Register Options
		 *
		 * @return void
		 */
		public function wkmp_register_marketplace_options() {
			register_setting(
				'wkmp-general-settings-group',
				'_wkmp_default_commission',
				function ( $input ) {
					if ( is_numeric( $input ) && $input >= 0 && $input <= 100 ) {
						return $input;
					} else {
						add_settings_error( '_wkmp_default_commission', 'commission-error', ( /* translators: %s Commission */ sprintf( esc_html__( 'Invalid default commission value %s. Must be between 0 & 100.', 'wk-marketplace' ), esc_attr( $input ) ) ), 'error' );

						return '';
					}
				}
			);

			register_setting( 'wkmp-general-settings-group', '_wkmp_auto_approve_seller' );
			register_setting( 'wkmp-general-settings-group', '_wkmp_separate_seller_dashboard' );
			register_setting( 'wkmp-general-settings-group', '_wkmp_separate_seller_registration' );
			register_setting( 'wkmp-general-settings-group', '_wkmp_seller_delete' );
			register_setting( 'wkmp-general-settings-group', 'wkmp_shipping_option' );
			register_setting(
				'wkmp-general-settings-group',
				'wkmp_select_seller_page',
				function ( $new_seller_page_id ) {
					$seller_page_id = get_option( 'wkmp_seller_page_id' );
					if ( intval( $seller_page_id ) !== intval( $new_seller_page_id ) ) {
						$new_seller_page = get_post( $new_seller_page_id );
						$new_seller_slug = isset( $new_seller_page->post_name ) ? $new_seller_page->post_name : '';
						if ( empty( $new_seller_slug ) ) {
							return $seller_page_id;
						}
						update_option( 'wkmp_seller_page_id', $new_seller_page_id );
						update_option( 'wkmp_seller_page_slug', $new_seller_slug );

						// Updating marketplace shortcode in new page content.
						$new_content = array(
							'ID'           => $new_seller_page_id,
							'post_content' => '[marketplace]',
						);

						// Update the post into the database.
						wp_update_post( $new_content );
						flush_rewrite_rules( false );
					}

					return $new_seller_page_id;
				}
			);

			register_setting( 'wkmp-product-settings-group', '_wkmp_allow_seller_to_publish' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_wcml_allow_product_translate' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_seller_allowed_product_types' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_seller_allowed_categories' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_enable_minimum_order_amount' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_minimum_order_amount' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_seller_min_amount_admin_default' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_enable_product_qty_limit' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_max_product_qty_limit' );

			register_setting( 'wkmp-assets-settings-group', '_wkmp_is_seller_email_visible' );
			register_setting( 'wkmp-assets-settings-group', '_wkmp_is_seller_contact_visible' );
			register_setting( 'wkmp-assets-settings-group', '_wkmp_is_seller_address_visible' );
			register_setting( 'wkmp-assets-settings-group', '_wkmp_is_seller_social_links_visible' );

			$endpoint_settings = apply_filters(
				'wkmp_endpoint_settings',
				array(
					'_wkmp_dashboard_endpoint',
					'_wkmp_dashboard_endpoint_name',
					'_wkmp_product_list_endpoint',
					'_wkmp_product_list_endpoint_name',
					'_wkmp_add_product_endpoint',
					'_wkmp_add_product_endpoint_name',
					'_wkmp_order_history_endpoint',
					'_wkmp_order_history_endpoint_name',
					'_wkmp_transaction_endpoint',
					'_wkmp_transaction_endpoint_name',
					'_wkmp_shipping_endpoint',
					'_wkmp_shipping_endpoint_name',
					'_wkmp_profile_endpoint',
					'_wkmp_profile_endpoint_name',
					'_wkmp_notification_endpoint',
					'_wkmp_notification_endpoint_name',
					'_wkmp_shop_follower_endpoint',
					'_wkmp_shop_follower_endpoint_name',
					'_wkmp_asktoadmin_endpoint',
					'_wkmp_asktoadmin_endpoint_name',
					'_wkmp_seller_product_endpoint',
					'_wkmp_seller_product_endpoint_name',
					'_wkmp_store_endpoint',
					'_wkmp_store_endpoint_name',
				)
			);

			foreach ( $endpoint_settings as $value ) {
				register_setting( 'wkmp-endpoint-settings-group', $value );
			}

			register_setting( 'wkmp-google-analytics-settings-group', '_wkmp_enable_google_analytics' );
			register_setting( 'wkmp-google-analytics-settings-group', '_wkmp_google_account_number' );
			register_setting( 'wkmp-google-analytics-settings-group', '_wkmp_analytics_anonymize_ip' );
		}

		/**
		 * Dashboard Menus for Marketplace
		 *
		 * @return void
		 */
		public function wkmp_create_dashboard_menu() {
			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_marketplace' );

			$allowed_roles = array( 'administrator' );
			if ( 'edit_posts' === $capability ) {
				$allowed_roles[] = 'editor';
			}

			$return = true;

			foreach ( $allowed_roles as $allowed_role ) {
				if ( current_user_can( $allowed_role ) ) {
					$return = false;
					break;
				}
			}
			if ( $return ) {
				return;
			}

			add_menu_page( esc_html__( 'Marketplace', 'wk-marketplace' ), esc_html__( 'Marketplace', 'wk-marketplace' ), $capability, 'wk-marketplace', null, WKMP_PLUGIN_URL . 'assets/images/marketplace.png', 55 );

			$sellers  = add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Marketplace', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Sellers', 'wk-marketplace' ),
				$capability,
				'wk-marketplace',
				array(
					$this->template_handler,
					'wkmp_marketplace_sellers',
				)
			);
			$products = add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Products', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Products', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-products',
				array(
					$this->template_handler,
					'wkmp_marketplace_products',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Notifications', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Notifications', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-notifications',
				array(
					$this->template_handler,
					'wkmp_marketplace_notifications',
				)
			);
			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Feedback', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Reviews & Rating', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-feedback',
				array(
					$this->template_handler,
					'wkmp_marketplace_feedback',
				)
			);
			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Queries', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Queries', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-queries',
				array(
					$this->template_handler,
					'wkmp_marketplace_queries',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Settings', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Settings', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-settings',
				array(
					$this->template_handler,
					'wkmp_marketplace_settings',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Extensions', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Extensions', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-extensions',
				array(
					$this->template_handler,
					'wkmp_marketplace_extensions',
				)
			);

			do_action( 'wkmp_admin_menu_action' );

			add_action( "load-{$sellers}", array( $this, 'wkmp_seller_list_screen_option' ) );
			add_action( "load-{$products}", array( $this, 'wkmp_seller_product_list_screen_option' ) );
		}

		/**
		 * Seller List Screen Options
		 *
		 * @return void
		 */
		public function wkmp_seller_list_screen_option() {
			$option = 'per_page';
			$args   = array(
				'label'   => esc_html__( 'Data Per Page', 'wk-marketplace' ),
				'default' => 10,
				'option'  => 'product_per_page',
			);

			add_screen_option( $option, $args );
		}

		/**
		 * Seller Product List Screen Options
		 *
		 * @return void
		 */
		public function wkmp_seller_product_list_screen_option() {
			$option = 'per_page';
			$args   = array(
				'label'   => esc_html__( 'Product Per Page', 'wk-marketplace' ),
				'default' => 10,
				'option'  => 'product_per_page',
			);

			add_screen_option( $option, $args );
		}

		/**
		 * Screen
		 *
		 * @param string  $status Status.
		 * @param string  $option Option Name.
		 * @param integer $value Option Value.
		 *
		 * @return $value
		 */
		public function wkmp_set_screen( $status, $option, $value ) {
			$options = array( 'wkmp_seller_per_page', 'wkmp_product_per_page' );
			if ( in_array( $option, $options, true ) ) {
				return $value;
			}

			return $status;
		}

		/**
		 * Set screen ids
		 *
		 * @param array $ids IDs.
		 *
		 * @return array
		 */
		public function wkmp_set_wc_screen_ids( $ids ) {
			array_push( $ids, 'toplevel_page_wk-marketplace', 'marketplace_page_wk-marketplace-settings', 'marketplace_page_wk-marketplace' );

			return $ids;
		}

		/**
		 * Admin footer text.
		 *
		 * @param string $text footer text.
		 */
		public function wkmp_admin_footer_text( $text ) {
			return wp_sprintf( __( 'If you like <strong>Marketplace</strong> please leave us a <a href="https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/reviews/19214408" target="_blank" class="wc-rating-link" data-rated="Thanks :)">★★★★★</a> rating. A huge thanks in advance!', 'wk-marketplace' ) );
		}

		/**
		 * Admin end scripts
		 *
		 * @return void
		 */
		public function wkmp_admin_scripts() {
			wp_enqueue_style( 'wkmp-admin-style', WKMP_PLUGIN_URL . 'assets/admin/build/css/admin.css', array(), WKMP_SCRIPT_VERSION );
			wp_enqueue_script( 'wkmp-admin-script', WKMP_PLUGIN_URL . 'assets/admin/dist/js/admin.min.js', array( 'select2' ), WKMP_SCRIPT_VERSION, true );

			$ajax_obj = array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce' => wp_create_nonce( 'wkmp-admin-nonce' ),
			);

			wp_localize_script(
				'wkmp-admin-script',
				'wkmpObj',
				array(
					'ajax'             => $ajax_obj,
					'text_required'    => esc_html__( 'This field is required', 'wk-marketplace' ),
					'text_unique'      => esc_html__( 'This field must be unique', 'wk-marketplace' ),
					'commonConfirmMsg' => esc_html__( 'Are you sure?', 'wk-marketplace' ),
					'already_paid'     => esc_html__( 'Payment has already been done for order id: ', 'wk-marketplace' ),
					'shop_name'        => esc_html__( 'Please fill shop name.', 'wk-marketplace' ),
					'failed_btn'       => esc_html__( 'Failed', 'wk-marketplace' ),
				)
			);

			if ( get_option( '_wkmp_separate_seller_dashboard' ) && get_user_meta( get_current_user_id(), 'wkmp_seller_backend_dashboard', true ) ) {
				$page_name   = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
				$action_name = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
				$oid         = filter_input( INPUT_GET, 'oid', FILTER_SANITIZE_NUMBER_INT );

				if ( is_admin() && 'seller' === $page_name ) {
					wp_enqueue_style( 'wkmp-front-style-css', WKMP_PLUGIN_URL . 'assets/front/build/css/front.css', array(), WKMP_SCRIPT_VERSION );
					wp_enqueue_style( 'wkmp-front-style', WKMP_PLUGIN_URL . 'assets/front/build/css/style.css', array(), WKMP_SCRIPT_VERSION );
					wp_register_script( 'jquery', '//code.jquery.com/jquery-2.2.4.min.js', array(), WKMP_SCRIPT_VERSION, true );
					wp_enqueue_script( 'jquery' );
					wp_enqueue_script( 'google_chart', 'https://www.gstatic.com/charts/loader.js', array(), WKMP_SCRIPT_VERSION, false );
					wp_enqueue_script( 'mp_chart_script', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js', array(), WKMP_SCRIPT_VERSION, false );
				}

				if ( ( 'order-history' === $page_name && 'view' === $action_name && $oid > 0 ) || 'seller-profile' === $page_name ) {
					wp_enqueue_script( 'wkmp-front-script', WKMP_PLUGIN_URL . 'assets/front/dist/js/front.min.js', array( 'select2', 'wp-util' ), WKMP_SCRIPT_VERSION, true );
				}
			}
		}

		/**
		 * Call back method for Save seller commission
		 *
		 * @param int $commission_info commission info.
		 * @param int $seller_id seller id.
		 */
		public function wkmp_save_seller_commission( $commission_info, $seller_id ) {
			if ( $seller_id ) {
				$this->seller_obj->wkmp_update_seller_commission_info( $seller_id, $commission_info );
			}
		}

		/**
		 * Menu invoice page.
		 *
		 * @return void
		 */
		public function wkmp_virtual_menu_invoice_page() {
			$hook = add_submenu_page(
				null,
				'Invoice',
				'Invoice',
				'administrator',
				'invoice',
				function () {
				}
			);
			add_action(
				'load-' . $hook,
				function () {
					if ( is_user_logged_in() && is_admin() ) {
						$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
						$order_id = base64_decode( $order_id ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
						$this->wkmp_admin_end_invoice( $order_id );
					} else {
						wp_die( '<h1>' . esc_html__( 'Cheatin’ uh?', 'wk-marketplace' ) . '</h1><p>' . esc_html__( 'Sorry, you are not allowed to access invoice.', 'wk-marketplace' ) . '</p>' );
					}
					exit;
				}
			);
		}

		/**
		 * Order invoice button.
		 *
		 * @param \WC_Order $order order object.
		 */
		public function wkmp_order_invoice_button( $order ) {
			if ( 'trash' === $order->get_status() ) {
				return;
			}
			$listing_actions = array(
				'invoice' => array(
					'name' => 'Invoice',
					'alt'  => 'Invoice',
					'url'  => wp_nonce_url( admin_url( 'edit.php?page=invoice&order_id=' . base64_encode( $order->get_id() ) ), 'generate_invoice', 'invoice_nonce' ),
				),
			);

			foreach ( $listing_actions as $action => $data ) {
				?>
				<a href="<?php echo esc_url( $data['url'] ); ?>" class="<?php echo esc_attr( $action ); ?>" target="_blank" title="<?php echo esc_attr( $data['alt'] ); ?>"><span class="dashicons dashicons-media-default"></span></a>
				<?php
			}
		}

		/**
		 * Admin side invoice.
		 *
		 * @param int $order_id order id.
		 */
		public function wkmp_admin_end_invoice( $order_id ) {
			require_once WKMP_PLUGIN_FILE . 'templates/admin/wkmp-admin-order-invoice.php';
		}

		/**
		 * Admin Notice.
		 *
		 * @return void.
		 */
		public function wkmp_admin_notices() {
			$posted_data = isset( $_POST['oid'] ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $posted_data['oid'] ) && is_array( $posted_data['oid'] ) && ! empty( $posted_data['oid'] ) ) {
				echo '<div  class="notice notice-success is-dismissible">';
				echo '<p>' . esc_html__( 'Payment has been successfully done.', 'wk-marketplace' ) . '</p>';
				echo '</div>';
			}
		}

		/**
		 * Extra user profile.
		 *
		 * @param object $user user.
		 */
		public function wkmp_extra_user_profile_fields( $user ) {
			require_once WKMP_PLUGIN_FILE . 'templates/admin/user/wkmp-user-profile.php';
		}

		/**
		 * Add Seller meta-box.
		 *
		 * @return void
		 */
		public function wkmp_add_seller_metabox() {
			global $current_user;
			if ( ! in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				add_meta_box( 'seller-meta-box', esc_html__( 'Seller', 'wk-marketplace' ), array( $this, 'wkmp_seller_metabox' ), 'product', 'side', 'low', null );
			}
		}

		/**
		 * Seller meta-box.
		 *
		 * @return void
		 */
		public function wkmp_seller_metabox() {
			global $post;
			$wpdb_obj = $this->wpdb;
			wp_nonce_field( 'blog_save_meta_box_data', 'blog_meta_box_nonce' );
			$result = $wpdb_obj->get_results( "SELECT user_id FROM {$wpdb_obj->prefix}mpsellerinfo WHERE seller_value = 'seller'" );
			?>
			<div class="return-seller">
				<select name="seller_id" style="width:100%">
					<option value="<?php echo esc_attr( get_current_user_id() ); ?>">--<?php esc_html_e( 'Select Seller', 'wk-marketplace' ); ?>--</option>
					<?php
					foreach ( $result as $key ) {
						$wkmp_first_name = get_user_meta( $key->user_id, 'first_name', true );
						if ( empty( $wkmp_first_name ) ) {
							$wkmp_first_name = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_nicename FROM {$wpdb_obj->base_prefix}users WHERE ID = %d ", $key->user_id ) );
						}

						?>
						<option value="<?php echo esc_attr( $key->user_id ); ?>" <?php echo ( intval( $post->post_author ) === intval( $key->user_id ) ) ? 'selected' : ''; ?>><?php echo esc_html( $wkmp_first_name ); ?></option>
						<?php
					}
					?>
				</select>
			</div>
			<?php
		}

		/**
		 * Functions changing the order status.
		 *
		 * @param int $order_id order id.
		 * @param int $old_status order old status.
		 * @param int $new_status order new status.
		 */
		public function wkmp_order_status_changed_action( $order_id, $old_status, $new_status ) {
			$obj = new Helper\WKMP_Seller_Order_Data();
			$obj->wkmp_update_order_status_on_changed( $order_id, $new_status );
		}

		/**
		 * Deleting seller from MP table on deletion of the seller from WP users screen.
		 *
		 * @param int      $user_id User id.
		 * @param int      $reassign Userid to re-assign.
		 * @param \WP_User $user User object.
		 */
		public function wkmp_delete_seller_on_user_delete( $user_id, $reassign, $user ) {
			global $wkmarketplace;
			if ( $user instanceof \WP_User && in_array( 'wk_marketplace_seller', $user->roles, true ) ) {
				$seller_db_obj = new Helper\WKMP_Seller_Data();
				$seller_db_obj->wkmp_delete_seller( $user_id );
				$wkmarketplace->log( "Seller deleted on deleted wp user from users screen: $user_id, Reassign user: $reassign" );
			}
		}

		/**
		 * Adding max qty filed.
		 *
		 * @hooked woocommerce_product_options_inventory_product_data
		 */
		public function wkmp_add_max_qty_field() {
			global $product_object;
			if ( 'grouped' !== $product_object->get_type() ) {
				woocommerce_wp_text_input(
					array(
						'id'          => '_wkmp_max_product_qty_limit',
						'value'       => $product_object->get_meta( '_wkmp_max_product_qty_limit' ),
						'label'       => __( 'Maximum Purchasable Quantity', 'wk-marketplace' ),
						'placeholder' => __( 'Enter Maximum Purchasable Quantity', 'wk-marketplace' ),
						'desc_tip'    => true,
						'description' => __( 'Customer can add only this max quantity in their carts.', 'wk-marketplace' ),
					)
				);
			}
		}

		/**
		 * Removing seller's shipping classes from Admin product edit page.
		 *
		 * @param array $args Get terms args.
		 * @param array $taxonomies Get term taxonomies.
		 *
		 * @hooked 'get_terms_args' filter hook.
		 *
		 * @return array.
		 */
		public function wkmp_remove_sellers_shipping_classes( $args, $taxonomies ) {
			global $current_user;

			if ( ! in_array( 'product_shipping_class', $taxonomies, true ) || ! in_array( 'administrator', $current_user->roles, true ) ) {
				return $args;
			}

			if ( is_admin() ) {
				$user_shipping_classes = get_user_meta( $current_user->ID, 'shipping-classes', true );
				$user_shipping_classes = empty( $user_shipping_classes ) ? array() : maybe_unserialize( $user_shipping_classes );
				$args['include']       = $user_shipping_classes;
			}

			return $args;
		}

		/**
		 * Removing restricted categories for seller product category filter in backend product listing.
		 *
		 * @param array $filters Product filters.
		 *
		 * @return array
		 */
		public function wkmp_remove_restricted_cats( $filters ) {
			global $wkmarketplace;
			$seller_id = get_current_user_id();
			if ( $wkmarketplace->wkmp_user_is_seller( $seller_id ) && ! empty( $filters['product_category'] ) ) {
				$filters['product_category'] = array( $this, 'wkmp_filtered_product_category' );
			}
			return $filters;
		}

		/**
		 * Filtered product category for admin listing.
		 *
		 * @return void
		 */
		public function wkmp_filtered_product_category() {
			$categories_count = (int) wp_count_terms( 'product_cat' );

			if ( $categories_count <= apply_filters( 'woocommerce_product_category_filter_threshold', 100 ) ) {
				$seller_id    = get_current_user_id();
				$allowed_cats = get_user_meta( $seller_id, 'wkmp_seller_allowed_categories', true );

				if ( empty( $allowed_cats ) ) {
					$allowed_cats = get_option( '_wkmp_seller_allowed_categories', array() );
				}

				$allowed_cat_ids = array();

				if ( ! empty( $allowed_cats ) ) {
					foreach ( $allowed_cats as $allowed_cat ) {
						$cat               = get_term_by( 'slug', $allowed_cat, 'product_cat' );
						$allowed_cat_ids[] = $cat->term_id;
					}
				}

				$categories_ids = get_terms(
					array( 'product_cat' ), // Taxonomies.
					array( 'fields' => 'ids' ) // Fields.
				);

				$allowed_ids = array_diff( $categories_ids, $allowed_cat_ids );

				$args = array(
					'option_select_text' => __( 'Filter by category', 'wk-marketplace' ),
					'hide_empty'         => 0,
					'show_count'         => 0,
				);

				if ( ! empty( $allowed_ids ) ) {
					$args['exclude'] = $allowed_ids;
				}

				wc_product_dropdown_categories( $args );
			} else {
				$current_category_slug = isset( $_GET['product_cat'] ) ? wc_clean( wp_unslash( $_GET['product_cat'] ) ) : false; // WPCS: input var ok, CSRF ok.
				$current_category      = $current_category_slug ? get_term_by( 'slug', $current_category_slug, 'product_cat' ) : false;
				?>
			<select class="wc-category-search" name="product_cat" data-placeholder="<?php esc_attr_e( 'Filter by category', 'wk-marketplace' ); ?>" data-allow_clear="true">
				<?php if ( $current_category_slug && $current_category ) : ?>
					<option value="<?php echo esc_attr( $current_category_slug ); ?>" selected="selected"><?php echo esc_html( htmlspecialchars( wp_kses_post( $current_category->name ) ) ); ?></option>
				<?php endif; ?>
			</select>
				<?php
			}
		}

		/**
		 * Method wkmp_add_email_options_to_translate
		 *
		 * @param array $email_options Email options.
		 *
		 * @return array
		 */
		public function wkmp_add_email_options_to_translate( $email_options ) {
			$email_options = is_array( $email_options ) ? $email_options : array();

			$email_options[] = 'woocommerce_wkmp_ask_to_admin_settings';
			$email_options[] = 'woocommerce_wkmp_customer_become_seller_to_admin_settings';
			$email_options[] = 'woocommerce_wkmp_customer_become_seller_settings';
			$email_options[] = 'woocommerce_wkmp_product_approve_disapprove_settings';
			$email_options[] = 'woocommerce_wkmp_seller_published_product_settings';
			$email_options[] = 'woocommerce_wkmp_seller_account_approved_settings';
			$email_options[] = 'woocommerce_wkmp_seller_account_disapproved_settings';
			$email_options[] = 'woocommerce_wkmp_seller_order_paid_settings';
			$email_options[] = 'woocommerce_wkmp_seller_order_cancelled_settings';
			$email_options[] = 'woocommerce_wkmp_seller_order_completed_settings';
			$email_options[] = 'woocommerce_wkmp_seller_order_failed_settings';
			$email_options[] = 'woocommerce_wkmp_seller_order_on_hold_settings';
			$email_options[] = 'woocommerce_wkmp_seller_product_ordered_settings';
			$email_options[] = 'woocommerce_wkmp_seller_order_processing_settings';
			$email_options[] = 'woocommerce_wkmp_seller_order_refunded_settings';
			$email_options[] = 'woocommerce_wkmp_seller_query_replied_settings';
			$email_options[] = 'woocommerce_wkmp_new_seller_registration_to_admin_settings';
			$email_options[] = 'woocommerce_wkmp_registration_details_to_seller_settings';
			$email_options[] = 'woocommerce_wkmp_seller_to_shop_followers_settings';

			return $email_options;
		}

	}
}
