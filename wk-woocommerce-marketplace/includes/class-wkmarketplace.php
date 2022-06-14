<?php
/**
 * Main Class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

use WkMarketplace\Includes;
use WkMarketplace\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMarketplace' ) ) {

	/**
	 * Marketplace Main Class
	 */
	final class WKMarketplace {

		/**
		 * Marketplace version.
		 *
		 * @var string
		 */
		public $version = '5.0.0';

		/**
		 * Instance variable
		 *
		 * @var object
		 */
		protected static $wk = null;

		/**
		 * Seller page slug
		 *
		 * @var string
		 */
		public $seller_page_slug;

		/**
		 * Seller url
		 *
		 * @var string
		 */
		public $seller_url;

		/**
		 * General query helper
		 *
		 * @var object
		 */
		protected $general_query;

		/**
		 * The object is created from within the class itself only if the class has no instance.
		 *
		 * @return object|WKMarketplace|null
		 */
		public static function instance() {
			if ( is_null( self::$wk ) ) {
				self::$wk = new self();
			}

			return self::$wk;
		}

		/**
		 * Marketplace Constructor.
		 */
		public function __construct() {
			$this->wkmp_init_hooks();
			$this->general_query    = new Helper\WKMP_General_Queries();
			$this->seller_page_slug = $this->general_query->wkmp_get_seller_page_slug();
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @return void
		 */
		public function wkmp_init_hooks() {
			$schema_handler = new WKMP_Install();
			register_activation_hook( WKMP_FILE, array( $schema_handler, 'wkmp_create_schema' ) );

			add_action( 'plugins_loaded', array( $this, 'wkmp_load_plugin' ) );
			add_action( 'wp_login', array( $this, 'wkmp_seller_login' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'wkmp_maybe_create_missing_tables' ), 990 );
			add_action( 'admin_menu', array( $this, 'wkmp_remove_home_menu_wc_admin' ), 990 );
		}

		/**
		 * Load plugin.
		 *
		 * @return void
		 */
		public function wkmp_load_plugin() {
			if ( ! function_exists( 'WC' ) ) {
				add_action(
					'admin_notices',
					function () {
						?>
					<div class="error">
						<p><?php echo sprintf( /* translators: %s woocommerce links */ esc_html__( 'Marketplace plugin depends on the last version of %s or later to work!', 'wk-marketplace' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">' . esc_html__( 'WooCommerce', 'wk-marketplace' ) . '</a>' ); ?></p>
					</div>
						<?php
					}
				);
			} else {
				new Includes\WKMP_File_Handler();
			}
		}

		/**
		 * Seller login.
		 *
		 * @param string   $user_login User login name.
		 * @param \WP_User $user User.
		 */
		public function wkmp_seller_login( $user_login, $user ) {
			global $wkmarketplace;
			if ( in_array( 'wk_marketplace_seller', $user->roles, true ) ) {
				$current_dash = get_user_meta( $user->ID, 'wkmp_seller_backend_dashboard', true );
				if ( get_user_meta( $user->ID, 'show_admin_bar_front', true ) ) {
					update_user_meta( $user->ID, 'show_admin_bar_front', false );
				}

				if ( isset( $current_dash ) && ! empty( $current_dash ) ) {
					$this->wkmp_add_role_cap( $user->ID );
					$this->seller_url = esc_url( admin_url( 'admin.php?page=seller' ) );
				} else {
					$this->wkmp_remove_role_cap( $user->ID );
					$this->seller_url = esc_url( site_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) ) );
					update_user_meta( $user->ID, 'wkmp_seller_backend_dashboard', null );
				}
				add_filter( 'login_redirect', array( $this, 'wkmp_redirect_seller' ), 10 );
			}
		}

		/**
		 * Redirect seller.
		 *
		 * @return string
		 */
		public function wkmp_redirect_seller() {
			return $this->seller_url;
		}

		/**
		 * Add cap
		 *
		 * @param int $user_id User id.
		 */
		public function wkmp_add_role_cap( $user_id ) {
			$user = new \WP_User( $user_id );
			$user->add_cap( 'manage_woocommerce' );
			$user->add_cap( 'edit_others_shop_orders' );
			$user->add_cap( 'read_product' );
			$user->add_cap( 'edit_product' );
			$user->add_cap( 'delete_product' );
			$user->add_cap( 'edit_products' );
			$user->add_cap( 'publish_products' );
			$user->add_cap( 'read_private_products' );
			$user->add_cap( 'delete_products' );
			$user->add_cap( 'edit_published_products' );
			$user->add_cap( 'assign_product_terms' );
		}

		/**
		 * Remove cap.
		 *
		 * @param int $user_id User id.
		 */
		public function wkmp_remove_role_cap( $user_id ) {
			$user = new \WP_User( $user_id );
			$user->remove_cap( 'manage_woocommerce' );
			$user->remove_cap( 'edit_others_shop_orders' );
			$user->remove_cap( 'read_product' );
			$user->remove_cap( 'edit_product' );
			$user->remove_cap( 'delete_product' );
			$user->remove_cap( 'edit_products' );
			$user->remove_cap( 'publish_products' );
			$user->remove_cap( 'read_private_products' );
			$user->remove_cap( 'delete_products' );
			$user->remove_cap( 'edit_published_products' );
			$user->remove_cap( 'assign_product_terms' );
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function wkmp_plugin_url() {
			return untrailingslashit( plugins_url( '/', WKMP_FILE ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function wkmp_plugin_path() {
			return untrailingslashit( WKMP_PLUGIN_FILE );
		}

		/**
		 * Check User is Seller
		 *
		 * @param int $user_id User ID.
		 *
		 * @return bool
		 */
		public function wkmp_user_is_seller( $user_id ) {
			$response    = false;
			$seller_info = $this->general_query->wkmp_check_if_seller( $user_id );

			if ( 0 < $seller_info ) {
				$response = true;
			}

			return $response;
		}

		/**
		 * Check current page is seller
		 *
		 * @return bool
		 */
		public function wkmp_is_seller_page() {
			if ( is_page( $this->seller_page_slug ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check current page is seller.
		 *
		 * @param String $shop_address Shop Address.
		 *
		 * @return int
		 */
		public function wkmp_get_seller_id_by_shop_address( $shop_address ) {
			return $this->general_query->wkmp_get_seller_id_by_shop_address( $shop_address );
		}

		/**
		 * Get Seller Information by ID
		 *
		 * @param int $seller_id Seller ID.
		 *
		 * @return \stdClass
		 */
		public function wkmp_get_seller_info( $seller_id ) {
			$seller_info = array();

			if ( $this->wkmp_user_is_seller( $seller_id ) ) {
				$info = get_user_by( 'ID', $seller_id );
				$meta = get_user_meta( $seller_id, '', true );

				$seller_info        = $info->data;
				$seller_info->caps  = isset( $info->caps ) ? $info->caps : array();
				$seller_info->roles = isset( $info->roles ) ? $info->roles : array();

				foreach ( $meta as $key => $value ) {
					$seller_info->$key = $value[0];
				}
			}

			return $seller_info;
		}

		/**
		 * Get the Pagination
		 *
		 * @param int $total Total items.
		 * @param int $page Which page.
		 * @param int $limit How many items display on single page.
		 * @param int $url Page URL.
		 *
		 * @return array $data Pagination info
		 */
		public function wkmp_get_pagination( $total, $page, $limit, $url ) {
			$data = array();
			$url .= '/page/{page}';

			$pagination        = new WKMP_Pagination();
			$pagination->total = $total;
			$pagination->page  = $page;
			$pagination->limit = $limit;
			$pagination->url   = $url;

			$data['pagination'] = $pagination->wkmp_render();

			$data['results'] = '<p class="woocommerce-result-count">' . sprintf( /* translators: %d total, %d limit, %d offset. */ esc_html__( 'Showing %1$d to %2$d of %3$d (%4$d Pages)', 'wk-marketplace' ), ( $total ) ? ( ( $page - 1 ) * $limit ) + 1 : 0, ( ( ( $page - 1 ) * $limit ) > ( $total - $limit ) ) ? $total : ( ( ( $page - 1 ) * $limit ) + $limit ), $total, ceil( $total / $limit ) ) . '</p>';

			return $data;
		}

		/**
		 * Log function for debugging.
		 *
		 * @param mixed  $message Message string or array.
		 * @param array  $context Additional parameter, like file name 'source'.
		 * @param string $level One of the following:
		 *     'emergency': System is unusable.
		 *     'alert': Action must be taken immediately.
		 *     'critical': Critical conditions.
		 *     'error': Error conditions.
		 *     'warning': Warning conditions.
		 *     'notice': Normal but significant condition.
		 *     'info': Informational messages.
		 *     'debug': Debug-level messages.
		 */
		public function log( $message, $context = array(), $level = 'info' ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				$log_enabled = apply_filters( 'wkmp_is_log_enabled', true );
				if ( $log_enabled ) {
					$source            = ( is_array( $context ) && isset( $context['source'] ) && ! empty( $context['source'] ) ) ? $context['source'] : 'wk-mp';
					$context['source'] = $source;
					$logger            = wc_get_logger();
					$current_user_id   = get_current_user_id();

					$in_action = sprintf( ( /* translators: %s current user id */ esc_html__( 'User in action: %s: ', 'wk-marketplace' ) ), $current_user_id );
					$message   = $in_action . $message;

					$logger->log( $level, $message, $context );
				}
			}
		}

		/**
		 * Returning Google account number if it is enabled.
		 *
		 * @return false|mixed|void
		 */
		public function wkmp_get_ga_number() {
			$enabled   = get_option( '_wkmp_enable_google_analytics', false );
			$ga_number = get_option( '_wkmp_google_account_number', false );

			return ( $enabled ) ? $ga_number : false;
		}

		/**
		 * To get first admin user id. It will return smallest admin user id on the site.
		 *
		 * @return int
		 */
		public function wkmp_get_first_admin_user_id() {
			// Find and return first admin user id.
			$first_admin_user_id = 0;
			$first_admin         = get_users(
				array(
					'role'    => 'administrator',
					'orderby' => 'ID',
					'order'   => 'ASC',
					'number'  => 1,
				)
			);

			if ( count( $first_admin ) > 0 && $first_admin[0] instanceof \WP_User ) {
				$first_admin_user_id = $first_admin[0]->ID;
			}

			return $first_admin_user_id;
		}

		/**
		 * May be create missing MP tables if it was not created during activation due to any reaon (like fatal error on site)
		 */
		public function wkmp_maybe_create_missing_tables() {
			$get_db_version = get_option( '_wkmp_db_version', '0.0.0' );
			if ( version_compare( WKMP_DB_VERSION, $get_db_version, '>' ) ) {
				$schema_handler = new WKMP_Install();
				$schema_handler->wkmp_create_schema();
			}
		}

		/**
		 * Check User is Customer.
		 *
		 * @param int $customer_id User ID.
		 *
		 * @return bool
		 */
		public function wkmp_user_is_customer( $customer_id ) {
			$response = false;
			if ( $customer_id > 0 ) {
				$customer_user = new \WP_User( $customer_id );
				$cust_roles    = ( $customer_user instanceof \WP_User && isset( $customer_user->roles ) ) ? $customer_user->roles : array();
				$allowed_roles = array( 'customer', 'subscriber' );
				if ( count( array_intersect( $allowed_roles, $cust_roles ) ) > 0 && ! in_array( 'wk_marketplace_seller', $cust_roles, true ) && empty( get_user_meta( $customer_id, 'shop_address', true ) ) ) {
					$response = true;
				}
			}

			return $response;
		}

		/**
		 * To check if current page belongs to woocommerce pages.
		 *
		 * @return false
		 */
		public function wkmp_is_woocommerce_page() {
			if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url() || is_product_tag() || is_checkout_pay_page() || is_view_order_page() || is_edit_account_page() || is_order_received_page() ) {
				return true;
			}

			return false;
		}

		/**
		 * Get product sku with prefix if enabled.
		 *
		 * @param int|\WC_Product $sell_product Product id or object.
		 *
		 * @return string
		 */
		public function wkmp_get_sku( $sell_product ) {
			$sell_product_id = is_numeric( $sell_product ) ? $sell_product : 0;

			if ( $sell_product_id > 0 ) {
				$sell_product = wc_get_product( $sell_product_id );
			} elseif ( $sell_product instanceof \WC_Product ) {
				$sell_product_id = $sell_product->get_id();
			}

			$seller_id   = 0;
			$product_sku = '';

			if ( $sell_product instanceof \WC_Product ) {
				$seller_id   = get_post_field( 'post_author', $sell_product_id );
				$product_sku = $sell_product->get_sku();
			}

			if ( $seller_id > 0 ) {
				$dynamic_sku_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_dynamic_sku', true );
				$dynamic_sku_prefix  = get_user_meta( $seller_id, '_wkmp_dynamic_sku_prefix', true );

				if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
					$prod_sku    = empty( $product_sku ) ? $sell_product_id : $product_sku;
					$product_sku = $dynamic_sku_prefix . $prod_sku;
				}
			}

			return empty( $product_sku ) ? $sell_product_id : $product_sku;
		}

		/**
		 * Remove 'Home' menu from woocommerce if seller backend dashboard.
		 *
		 * @hooked 'admin_menu' action hook.
		 *
		 * @return void
		 */
		public function wkmp_remove_home_menu_wc_admin() {
			$seller_id = get_current_user_id();
			if ( $this->wkmp_user_is_seller( $seller_id ) ) {
				remove_submenu_page( 'woocommerce', 'wc-admin' );

				if ( 'disabled' === get_option( 'woocommerce_ship_to_countries', false ) ) {
					remove_submenu_page( 'woocommerce', 'wc-settings' );
				}
			}
		}

		/**
		 * Check if User is pending seller to approve.
		 *
		 * @param int $customer_id User ID.
		 *
		 * @return bool
		 */
		public function wkmp_user_is_pending_seller( $customer_id = 0 ) {
			$response    = false;
			$customer_id = ( $customer_id > 0 ) ? $customer_id : get_current_user_id();

			if ( $customer_id > 0 ) {
				$seller_id = $this->general_query->wkmp_get_pending_seller_id( $customer_id );
				$response  = ( $seller_id > 0 );
			}

			return $response;
		}

		/**
		 * Removing translate capability.
		 *
		 * @param int $user_id User id.
		 *
		 * @return void
		 */
		public function wkmp_remove_seller_translate_capability( $user_id = 0 ) {
			$sellers = array();
			if ( $user_id > 0 ) {
				$sellers[] = get_user_by( 'ID', $user_id );
			} else {
				$sellers = get_users( array( 'role' => 'wk_marketplace_seller' ) );
			}

			foreach ( $sellers as $seller ) {
				$seller->remove_cap( 'translate' );
				$this->general_query->wkmp_delete_icl_user_meta( 'language_pairs', $seller->ID );
			}
		}

		/**
		 * To get common table data for seller separate dashboard and and front order history.
		 *
		 * @param array $filter_data Filter data.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_order_table_data( $filter_data ) {
			$table_data = $this->general_query->wkmp_get_seller_order_data( $filter_data );

			return apply_filters( 'wkmp_seller_order_table_data', $table_data );
		}

		/**
		 * Get parsed seller info.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $posted_data Posted data.
		 *
		 * @return array
		 */
		public function get_parsed_seller_info( $seller_id, $posted_data ) {
			$seller_info = array();

			$field_keys = array(
				'wkmp_username',
				'wkmp_seller_email',
				'wkmp_first_name',
				'wkmp_last_name',
				'wkmp_shop_name',
				'wkmp_shop_url',
				'wkmp_about_shop',
				'wkmp_shop_address_1',
				'wkmp_shop_address_2',
				'wkmp_shop_city',
				'wkmp_shop_postcode',
				'wkmp_shop_phone',
				'wkmp_shop_country',
				'wkmp_shop_state',
				'wkmp_payment_details',
				'wkmp_display_banner',
				'wkmp_avatar_id',
				'wkmp_logo_id',
				'wkmp_banner_id',
				'wkmp_avatar_file',
				'wkmp_logo_file',
				'wkmp_banner_file',
				'wkmp_generic_avatar',
				'wkmp_generic_logo',
				'wkmp_generic_banner',
				'wkmp_facebook',
				'wkmp_instagram',
				'wkmp_twitter',
				'wkmp_linkedin',
				'wkmp_youtube',
			);

			foreach ( $field_keys as  $field_key ) {
				$seller_info[ $field_key ] = '';
			}

			if ( $this->wkmp_user_is_seller( $seller_id ) ) {
				$seller_user_obj = get_user_by( 'ID', $seller_id );
				$user_meta       = get_user_meta( $seller_id );

				$seller_info['wkmp_first_name']      = empty( $posted_data['wkmp_first_name'] ) ? ( empty( $user_meta['first_name'][0] ) ? '' : $user_meta['first_name'][0] ) : $posted_data['wkmp_first_name'];
				$seller_info['wkmp_last_name']       = empty( $posted_data['wkmp_last_name'] ) ? ( empty( $user_meta['last_name'][0] ) ? '' : $user_meta['last_name'][0] ) : $posted_data['wkmp_last_name'];
				$seller_info['wkmp_shop_name']       = empty( $posted_data['wkmp_shop_name'] ) ? ( empty( $user_meta['shop_name'][0] ) ? '' : $user_meta['shop_name'][0] ) : $posted_data['wkmp_shop_name'];
				$seller_info['wkmp_shop_url']        = empty( $posted_data['wkmp_shop_url'] ) ? ( empty( $user_meta['shop_address'][0] ) ? '' : $user_meta['shop_address'][0] ) : $posted_data['wkmp_shop_url'];
				$seller_info['wkmp_about_shop']      = empty( $posted_data['wkmp_about_shop'] ) ? ( empty( $user_meta['about_shop'][0] ) ? '' : $user_meta['about_shop'][0] ) : stripslashes( $posted_data['wkmp_about_shop'] );
				$seller_info['wkmp_shop_address_1']  = empty( $posted_data['wkmp_shop_address_1'] ) ? ( empty( $user_meta['billing_address_1'][0] ) ? '' : $user_meta['billing_address_1'][0] ) : $posted_data['wkmp_shop_address_1'];
				$seller_info['wkmp_shop_address_2']  = empty( $posted_data['wkmp_shop_address_2'] ) ? ( empty( $user_meta['billing_address_2'][0] ) ? '' : $user_meta['billing_address_2'][0] ) : $posted_data['wkmp_shop_address_2'];
				$seller_info['wkmp_shop_city']       = empty( $posted_data['wkmp_shop_city'] ) ? ( empty( $user_meta['billing_city'][0] ) ? '' : $user_meta['billing_city'][0] ) : $posted_data['wkmp_shop_city'];
				$seller_info['wkmp_shop_postcode']   = empty( $posted_data['wkmp_shop_postcode'] ) ? ( empty( $user_meta['billing_postcode'][0] ) ? '' : $user_meta['billing_postcode'][0] ) : $posted_data['wkmp_shop_postcode'];
				$seller_info['wkmp_shop_phone']      = empty( $posted_data['wkmp_shop_phone'] ) ? ( empty( $user_meta['billing_phone'][0] ) ? '' : $user_meta['billing_phone'][0] ) : $posted_data['wkmp_shop_phone'];
				$seller_info['wkmp_shop_country']    = empty( $posted_data['wkmp_shop_country'] ) ? ( empty( $user_meta['billing_country'][0] ) ? '' : $user_meta['billing_country'][0] ) : $posted_data['wkmp_shop_country'];
				$seller_info['wkmp_shop_state']      = empty( $posted_data['wkmp_shop_state'] ) ? ( empty( $user_meta['billing_state'][0] ) ? '' : $user_meta['billing_state'][0] ) : $posted_data['wkmp_shop_state'];
				$seller_info['wkmp_payment_details'] = empty( $posted_data['wkmp_payment_details'] ) ? ( empty( $user_meta['mp_seller_payment_details'][0] ) ? '' : $user_meta['mp_seller_payment_details'][0] ) : $posted_data['wkmp_payment_details'];
				$seller_info['wkmp_display_banner']  = empty( $posted_data['wkmp_display_banner'] ) ? ( empty( $user_meta['shop_banner_visibility'][0] ) ? '' : $user_meta['shop_banner_visibility'][0] ) : $posted_data['wkmp_display_banner'];

				$seller_info['wkmp_facebook']  = empty( $posted_data['wkmp_facebook'] ) ? ( empty( $user_meta['social_facebook'][0] ) ? '' : $user_meta['social_facebook'][0] ) : $posted_data['wkmp_facebook'];
				$seller_info['wkmp_instagram'] = empty( $posted_data['wkmp_instagram'] ) ? ( empty( $user_meta['social_instagram'][0] ) ? '' : $user_meta['social_instagram'][0] ) : $posted_data['wkmp_instagram'];
				$seller_info['wkmp_twitter']   = empty( $posted_data['wkmp_twitter'] ) ? ( empty( $user_meta['social_twitter'][0] ) ? '' : $user_meta['social_twitter'][0] ) : $posted_data['wkmp_twitter'];
				$seller_info['wkmp_linkedin']  = empty( $posted_data['wkmp_linkedin'] ) ? ( empty( $user_meta['social_linkedin'][0] ) ? '' : $user_meta['social_linkedin'][0] ) : $posted_data['wkmp_linkedin'];
				$seller_info['wkmp_youtube']   = empty( $posted_data['wkmp_youtube'] ) ? ( empty( $user_meta['social_youtube'][0] ) ? '' : $user_meta['social_youtube'][0] ) : $posted_data['wkmp_youtube'];

				$seller_info['wkmp_seller_email']   = empty( $posted_data['wkmp_seller_email'] ) ? ( empty( $seller_user_obj->user_email ) ? '' : $seller_user_obj->user_email ) : $posted_data['wkmp_seller_email'];
				$seller_info['wkmp_username']       = empty( $posted_data['wkmp_username'] ) ? ( empty( $seller_user_obj->user_login ) ? '' : $seller_user_obj->user_login ) : $posted_data['wkmp_username'];
				$seller_info['wkmp_generic_avatar'] = esc_url( WKMP_PLUGIN_URL ) . 'assets/images/generic-male.png';
				$seller_info['wkmp_generic_logo']   = esc_url( WKMP_PLUGIN_URL ) . 'assets/images/shop-logo.png';
				$seller_info['wkmp_generic_banner'] = esc_url( WKMP_PLUGIN_URL ) . 'assets/images/mp-banner.png';

				$seller_info['wkmp_avatar_id'] = empty( $user_meta['_thumbnail_id_avatar'][0] ) ? ( empty( $posted_data['wkmp_avatar_id'] ) ? '' : $posted_data['wkmp_avatar_id'] ) : $user_meta['_thumbnail_id_avatar'][0];
				$seller_info['wkmp_logo_id']   = empty( $user_meta['_thumbnail_id_company_logo'][0] ) ? ( empty( $posted_data['wkmp_logo_id'] ) ? '' : $posted_data['wkmp_logo_id'] ) : $user_meta['_thumbnail_id_company_logo'][0];
				$seller_info['wkmp_banner_id'] = empty( $user_meta['_thumbnail_id_shop_banner'][0] ) ? ( empty( $posted_data['wkmp_banner_id'] ) ? '' : $posted_data['wkmp_banner_id'] ) : $user_meta['_thumbnail_id_shop_banner'][0];

				$avatar_file = wp_get_attachment_image_src( $seller_info['wkmp_avatar_id'] );
				$logo_file   = wp_get_attachment_image_src( $seller_info['wkmp_logo_id'] );
				$banner_file = wp_get_attachment_image_src( $seller_info['wkmp_banner_id'], array( 750, 320 ) );

				if ( ! empty( $avatar_file ) && ! empty( $avatar_file[0] ) ) {
					$seller_info['wkmp_avatar_file'] = $avatar_file[0];
				}
				if ( ! empty( $logo_file ) && ! empty( $logo_file[0] ) ) {
					$seller_info['wkmp_logo_file'] = $logo_file[0];
				}
				if ( ! empty( $banner_file ) && ! empty( $banner_file[0] ) ) {
					$seller_info['wkmp_banner_file'] = $banner_file[0];
				}
			}

			return $seller_info;
		}
	}
}
