<?php
/**
 * Front functions template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper;
use WkMarketplace\Helper\Front;
use WkMarketplace\Helper\Common;
use WkMarketplace\Templates\Front\Seller;

if ( ! class_exists( 'WKMP_Front_Functions' ) ) {
	/**
	 * Front functions class
	 */
	class WKMP_Front_Functions {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Front template handler variable
		 *
		 * @var object
		 */
		protected $template_handler;

		/**
		 * General query helper variable
		 *
		 * @var object
		 */
		protected $query_handler;

		/**
		 * Order query helper variable
		 *
		 * @var object
		 */
		protected $db_obj_order;

		/**
		 * Seller template handler
		 *
		 * @var object
		 */
		protected $seller_template;

		/**
		 * Variable for page_title_display.
		 *
		 * @var int
		 */
		private static $page_title_display = 1;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Front_Functions constructor.
		 *
		 * @param object $template_handler Front template handler.
		 */
		public function __construct( $template_handler ) {
			global $wpdb;
			$this->wpdb             = $wpdb;
			$this->template_handler = $template_handler;
			$this->query_handler    = new Helper\WKMP_General_Queries();
			$this->db_obj_order     = new Front\WKMP_Order_Queries();
			$this->seller_template  = new Seller\WKMP_Seller_Template_Functions();
		}


		public function wkmp_registration_redirect( $url ) {
			$user_id = get_current_user_id();
			if ( get_user_meta( $user_id, 'show_admin_bar_front', true ) ) {
				update_user_meta( $user_id, 'show_admin_bar_front', false );
			}

			return $url;
		}

		/**
		 * Front Scripts Enqueue
		 *
		 * @hooked 'wp_enqueue_scripts' Action hook.
		 *
		 * @return void
		 */
		public function wkmp_front_scripts() {
			global $wkmarketplace;

			$mkt_tr_arr = array(
				'mkt1'    => esc_html__( 'Please select customer from the list', 'wk-marketplace' ),
				'mkt2'    => esc_html__( 'This field could not be left blank', 'wk-marketplace' ),
				'mkt3'    => esc_html__( 'Please enter valid product sku, it should be equal or larger than 3 characters', 'wk-marketplace' ),
				'mkt4'    => esc_html__( 'Please Enter SKU', 'wk-marketplace' ),
				'mkt5'    => esc_html__( 'Sale Price cannot be greater than Regular Price.', 'wk-marketplace' ),
				'mkt6'    => esc_html__( 'Invalid Price.', 'wk-marketplace' ),
				'mkt7'    => esc_html__( 'Invalid input.', 'wk-marketplace' ),
				'mkt8'    => esc_html__( 'Please Enter Product Name!!!', 'wk-marketplace' ),
				'mkt9'    => esc_html__( 'First name is not valid', 'wk-marketplace' ),
				'mkt10'   => esc_html__( 'Last name is not valid', 'wk-marketplace' ),
				'mkt11'   => esc_html__( 'E-mail is not valid', 'wk-marketplace' ),
				'mkt12'   => esc_html__( 'Shop name is not valid', 'wk-marketplace' ),
				'mkt13'   => esc_html__( 'Phone number length must not exceed 10.', 'wk-marketplace' ),
				'mkt14'   => esc_html__( 'Phone number not valid.', 'wk-marketplace' ),
				'mkt15'   => esc_html__( 'Field left blank!!!', 'wk-marketplace' ),
				'mkt16'   => esc_html__( 'Seller User Name is not valid', 'wk-marketplace' ),
				'mkt17'   => esc_html__( 'user name available', 'wk-marketplace' ),
				'mkt18'   => esc_html__( 'User Name Already Taken', 'wk-marketplace' ),
				'mkt19'   => esc_html__( 'Cannot Leave Field Blank', 'wk-marketplace' ),
				'mkt20'   => esc_html__( 'Email Id Already Registered', 'wk-marketplace' ),
				'mkt21'   => esc_html__( 'Email adress is not valid', 'wk-marketplace' ),
				'mkt22'   => esc_html__( 'select seller option', 'wk-marketplace' ),
				'mkt23'   => esc_html__( 'seller store name is too short,contain white space or empty', 'wk-marketplace' ),
				'mkt24'   => esc_html__( 'address is too short or empty', 'wk-marketplace' ),
				'mkt25'   => esc_html__( 'Subject field can not be blank.', 'wk-marketplace' ),
				'mkt26'   => esc_html__( 'Subject not valid.', 'wk-marketplace' ),
				'mkt27'   => esc_html__( 'Ask Your Question (Message length should be less than 500).', 'wk-marketplace' ),
				'mkt28'   => esc_html__( 'Online', 'wk-marketplace' ),
				'mkt29'   => esc_html__( 'Attribute name', 'wk-marketplace' ),
				'mkt30'   => esc_html__( 'attribue value by seprating comma eg. a|b|c', 'wk-marketplace' ),
				'mkt31'   => esc_html__( 'Attribute Value eg. a|b|c', 'wk-marketplace' ),
				'mkt32'   => esc_html__( 'Remove', 'wk-marketplace' ),
				'mkt33'   => esc_html__( 'Visible on the product page', 'wk-marketplace' ),
				'mkt34'   => esc_html__( 'Used for variations', 'wk-marketplace' ),
				'mkt35'   => esc_html__( 'Price, Value, Quality rating cannot be empty.', 'wk-marketplace' ),
				'mkt36'   => esc_html__( 'Required field.', 'wk-marketplace' ),
				'mkt37'   => esc_html__( 'Please enter username or email address.', 'wk-marketplace' ),
				'mkt38'   => esc_html__( 'Please enter password.', 'wk-marketplace' ),
				'mkt39'   => esc_html__( 'Please enter username', 'wk-marketplace' ),
				'mkt40'   => esc_html__( 'Warning : Subject should be 3 to 50 character', 'wk-marketplace' ),
				'mkt41'   => esc_html__( 'Warning : Message should be 5 to 255 character', 'wk-marketplace' ),
				'mkt42'   => esc_html__( 'Enter a valid numeric amount greater than 0.', 'wk-marketplace' ),
				'mkt43'   => esc_html__( 'Enter minimum amount.', 'wk-marketplace' ),
				'mkt44'   => esc_html__( 'Clear', 'wk-marketplace' ),
				'mkt45'   => esc_html__( 'No Restrictions.', 'wk-marketplace' ),
				'mkt46'   => esc_html__( 'Enable', 'wk-marketplace' ),
				'mkt47'   => esc_html__( 'Enter a positive integer value.', 'wk-marketplace' ),
				'mkt48'   => esc_html__( 'Enter maximum purchasable product quantity.', 'wk-marketplace' ),
				'fajax1'  => esc_html__( 'Are You sure you want to delete this Seller..?', 'wk-marketplace' ),
				'fajax2'  => esc_html__( 'Are You sure you want to delete this Customer..?', 'wk-marketplace' ),
				'fajax3'  => esc_html__( 'No Sellers Available.', 'wk-marketplace' ),
				'fajax4'  => esc_html__( 'No Followers Available.', 'wk-marketplace' ),
				'fajax5'  => esc_html__( 'There was some issue in process. Please try again.!', 'wk-marketplace' ),
				'fajax6'  => esc_html__( 'Are You sure you want to delete customer(s) from list..?', 'wk-marketplace' ),
				'fajax7'  => esc_html__( 'Select customers to delete from list.', 'wk-marketplace' ),
				'fajax8'  => esc_html__( 'Subject field cannot be empty.', 'wk-marketplace' ),
				'fajax9'  => esc_html__( 'Message field cannot be empty.', 'wk-marketplace' ),
				'fajax10' => esc_html__( 'Mail Sent Successfully', 'wk-marketplace' ),
				'fajax11' => esc_html__( 'Error Sending Mail.', 'wk-marketplace' ),
				'fajax12' => esc_html__( 'Not Available', 'wk-marketplace' ),
				'fajax13' => esc_html__( 'Already Exists', 'wk-marketplace' ),
				'fajax14' => esc_html__( 'Available', 'wk-marketplace' ),
				'fajax15' => esc_html__( 'No Group found', 'wk-marketplace' ),
				'fajax16' => esc_html__( 'Refund Cancel', 'wk-marketplace' ),
				'fajax17' => esc_html__( 'Refund', 'wk-marketplace' ),
				'ship1'   => esc_html__( 'Remove', 'wk-marketplace' ),
				'ship2'   => esc_html__( 'Shipping Class Name', 'wk-marketplace' ),
				'ship3'   => esc_html__( 'Cancel changes', 'wk-marketplace' ),
				'ship4'   => esc_html__( 'Slug', 'wk-marketplace' ),
				'ship5'   => esc_html__( 'Description for your reference', 'wk-marketplace' ),
				'ship6'   => esc_html__( 'Are you sure you want to delete this zone?', 'wk-marketplace' ),
			);

			$ajax_obj = array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce' => wp_create_nonce( 'wkmp-front-nonce' ),
			);

			wp_enqueue_script( 'wkmp-front-script', WKMP_PLUGIN_URL . 'assets/front/dist/js/front.min.js', array( 'select2', 'wp-util' ), WKMP_SCRIPT_VERSION, true );
			wp_enqueue_script( 'select2-js', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js', array(), WKMP_SCRIPT_VERSION, true );

			wp_localize_script(
				'wkmp-front-script',
				'wkmpObj',
				array(
					'ajax'             => $ajax_obj,
					'commonConfirmMsg' => esc_html__( 'Are you sure?', 'wk-marketplace' ),
					'mkt_tr'           => $mkt_tr_arr,
				)
			);

			if ( null !== get_query_var( 'main_page' ) && get_query_var( 'main_page' ) === get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) ) {
				wp_register_script( 'jquery', '//code.jquery.com/jquery-2.2.4.min.js', array(), WKMP_SCRIPT_VERSION, false );
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'mp_chart_script', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js', array(), WKMP_SCRIPT_VERSION, false );
			}

			wp_dequeue_style( 'bootstrap-css' );
			wp_enqueue_style( 'dashicons' );

			if ( $wkmarketplace->wkmp_is_seller_page() || $wkmarketplace->wkmp_is_woocommerce_page() ) {
				wp_enqueue_style( 'wkmp-front-style-css', WKMP_PLUGIN_URL . 'assets/front/build/css/style.css', array(), WKMP_SCRIPT_VERSION );

				if ( $wkmarketplace->wkmp_is_seller_page() ) {
					wp_enqueue_style( 'wkmp-front-style', WKMP_PLUGIN_URL . 'assets/front/build/css/front.css', array(), WKMP_SCRIPT_VERSION );
					wp_enqueue_style( 'select2-css', plugins_url() . '/woocommerce/assets/css/select2.css', array(), WKMP_SCRIPT_VERSION );
				}
			}

			// Theme compatibility CSS.
			if ( in_array( get_template(), array( 'flatsome', 'woodmart' ), true ) ) {
				$rtl = is_rtl() ? '-rtl' : '';
				wp_enqueue_style( 'wkmp-compatibility', WKMP_PLUGIN_URL . 'assets/front/build/css/wkmp-theme-compatibility.css', array(), WKMP_SCRIPT_VERSION );
				if ( 'woodmart' === get_template() ) {
					wp_enqueue_style( 'wkmp-page-my-account', get_template_directory_uri() . '/css/parts/woo-page-my-account' . $rtl . '.min.css', array(), '6.0.3' );
				}
			}
		}

		/**
		 * Seller related fields in registration fields
		 *
		 * @return void
		 */
		public function wkmp_seller_registration_form() {
			global $wkmarketplace;
			$separate_reg_enabled = ( 1 === intval( get_option( '_wkmp_separate_seller_registration', true ) ) );
			if ( ! $separate_reg_enabled || ( $separate_reg_enabled && ! is_user_logged_in() && $wkmarketplace->wkmp_is_seller_page() && ! is_account_page() ) ) {
				$this->template_handler->wkmp_seller_registration_fields();
			}
		}

		/**
		 * Validates seller registration form
		 *
		 * @param \WP_Error $error Error.
		 *
		 * @return \WP_Error
		 */
		public function wkmp_seller_registration_errors( $error ) {
			$data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$role = isset( $data['role'] ) ? wp_unslash( $data['role'] ) : '';

			if ( 'seller' === $role ) {
				$first_name = ! empty( $data['wkmp_firstname'] ) ? trim( wp_unslash( $data['wkmp_firstname'] ) ) : '';
				$last_name  = ! empty( $data['wkmp_lastname'] ) ? trim( wp_unslash( $data['wkmp_lastname'] ) ) : '';
				$shop_name  = ! empty( $data['wkmp_shopname'] ) ? trim( wp_unslash( $data['wkmp_shopname'] ) ) : '';
				$shop_url   = ! empty( $data['wkmp_shopurl'] ) ? trim( wp_unslash( $data['wkmp_shopurl'] ) ) : '';
				$shop_phone = ! empty( $data['wkmp_shopphone'] ) ? trim( wp_unslash( $data['wkmp_shopphone'] ) ) : '';
				$user       = get_user_by( 'slug', $shop_url );

				if ( ! $first_name ) {
					return new \WP_Error( 'firstname-error', esc_html__( 'Please enter your first name.', 'wk-marketplace' ) );
				}

				if ( ! $last_name ) {
					return new \WP_Error( 'lastname-error', esc_html__( 'Please enter your last name.', 'wk-marketplace' ) );
				}

				if ( ! $shop_name ) {
					return new \WP_Error( 'lastname-error', esc_html__( 'Please enter your shop name.', 'wk-marketplace' ) );
				}

				if ( empty( $shop_url ) ) {
					return new \WP_Error( 'shopurl-error', esc_html__( 'Please enter valid shop URL.', 'wk-marketplace' ) );
				} elseif ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $shop_url ) ) {
					return new \WP_Error( 'shopurl-error', esc_html__( 'You can not use special characters in shop url except HYPHEN(-).', 'wk-marketplace' ) );
				} elseif ( ctype_space( $shop_url ) || str_contains( $shop_url, ' ' ) ) {
					return new \WP_Error( 'shopurl-error', esc_html__( 'White space(s) aren\'t allowed in shop url.', 'wk-marketplace' ) );
				} elseif ( $user ) {
					return new \WP_Error( 'shopurl-error', esc_html__( 'This shop URl already EXISTS, please try different shop url.', 'wk-marketplace' ) );
				}

				if ( empty( $shop_phone ) ) {
					return new \WP_Error( 'phone-error', esc_html__( 'Please enter your phone number.', 'wk-marketplace' ) );
				} elseif ( ! preg_match( '/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$/', $shop_phone ) ) {
					return new \WP_Error( 'phone-error', esc_html__( 'Please enter valid phone number.', 'wk-marketplace' ) );
				}
			}

			return $error;
		}

		/**
		 * Inject seller data into form data
		 *
		 * @param array $data Data.
		 *
		 * @return $data
		 */
		public function wkmp_new_user_data( $data ) {
			$posted_data   = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$allowed_roles = array( 'customer', 'seller' );
			$role          = ( isset( $posted_data['role'] ) && in_array( wp_unslash( $posted_data['role'] ), $allowed_roles, true ) ) ? wp_unslash( $posted_data['role'] ) : 'customer';

			if ( 'seller' === $role ) {
				$data['role']      = $role;
				$data['firstname'] = trim( wp_unslash( $posted_data['wkmp_firstname'] ) );
				$data['lastname']  = trim( wp_unslash( $posted_data['wkmp_lastname'] ) );
				$data['nicename']  = trim( wp_unslash( $posted_data['wkmp_shopurl'] ) );
				$data['storename'] = trim( wp_unslash( $posted_data['wkmp_shopname'] ) );
				$data['phone']     = trim( wp_unslash( $posted_data['wkmp_shopphone'] ) );
				$data['register']  = $posted_data['register'];
			}

			return $data;
		}

		/**
		 * Process seller registration
		 *
		 * @param int   $user_id New User ID.
		 * @param array $data Data Array.
		 *
		 * @return void
		 * @throws \Exception Success Message.
		 */
		public function wkmp_process_registration( $user_id, $data ) {
			if ( isset( $data['register'] ) ) {
				if ( isset( $data['user_login'] ) && isset( $data['firstname'] ) && isset( $data['lastname'] ) && isset( $data['user_login'] ) && isset( $data['nicename'] ) && isset( $data['storename'] ) ) {
					$user_login   = $data['user_login'];
					$store_url    = $data['nicename'];
					$first_name   = $data['firstname'];
					$last_name    = $data['lastname'];
					$user_email   = $data['user_email'];
					$role         = $data['role'];
					$shop_name    = $data['storename'];
					$sel_phone    = $data['phone'];
					$auto_approve = get_option( '_wkmp_auto_approve_seller', false );

					$data['auto_approve'] = $auto_approve;

					if ( email_exists( $user_email ) ) {
						$user_data = array(
							'user_nicename' => $store_url,
							'display_name'  => $user_login,
						);
						wp_update_user( $user_data );

						update_user_meta( $user_id, 'first_name', $first_name );
						update_user_meta( $user_id, 'last_name', $last_name );

						if ( ! empty( $role ) && 'customer' !== $role ) {
							update_user_meta( $user_id, 'shop_name', $shop_name );
							update_user_meta( $user_id, 'shop_address', $store_url );
							update_user_meta( $user_id, 'billing_phone', $sel_phone );

							$this->query_handler->wkmp_set_seller_meta( $user_id );
							$this->query_handler->wkmp_set_seller_default_commission( $user_id );
						}
						update_user_meta( $user_id, 'wkmp_show_register_notice', esc_html__( 'Registration complete check your mail for password!', 'wk-marketplace' ) );
					}
					unset( $_POST ); // wpcs: input var okay; wpcs: csrf okay.

					do_action( 'wkmp_registration_details_to_seller', $data );

					do_action(
						'wkmp_new_seller_registration_to_admin',
						array(
							'user_email' => $user_email,
							'user_name'  => $user_login,
							'shop_url'   => $store_url,
						)
					);
				}
			}
		}

		/**
		 * Redirect the user to seller page if logged in user is seller
		 *
		 * @param string   $redirect Redirect URL.
		 * @param \WP_User $user Logged in user object.
		 *
		 * @return $redirect
		 */
		public function wkmp_seller_login_redirect( $redirect, $user ) {
			global $wkmarketplace;

			if ( user_can( $user, 'wk_marketplace_seller' ) ) {
				$page_name = $wkmarketplace->seller_page_slug;
				$redirect  = get_permalink( get_page_by_path( $page_name ) ) . '/' . get_option( '_wkmp_dashboard_endpoint', 'dashboard' );
			}

			return $redirect;
		}

		/**
		 *  Add seller menu items in my account menu
		 *
		 * @param array $items items array.
		 *
		 * @return array $new_items Items array with seller options if seller.
		 */
		public function wkmp_seller_menu_items_my_account( $items ) {
			global $wkmarketplace;
			$user_id   = get_current_user_id();
			$new_items = array();

			if ( $user_id ) {
				$seller_info  = $wkmarketplace->wkmp_user_is_seller( $user_id );
				$shop_address = get_user_meta( $user_id, 'shop_address', true );
				$page_name    = $wkmarketplace->seller_page_slug;

				if ( $seller_info ) {

					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) ]         = esc_html( get_option( '_wkmp_dashboard_endpoint_name', esc_html__( 'Marketplace', 'wk-marketplace' ) ) );
					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_product_list_endpoint', 'product-list' ) ]   = esc_html( get_option( '_wkmp_product_list_endpoint_name', esc_html__( 'Products', 'wk-marketplace' ) ) );
					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_order_history_endpoint', 'order-history' ) ] = esc_html( get_option( '_wkmp_order_history_endpoint_name', esc_html__( 'Order History', 'wk-marketplace' ) ) );
					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_transaction_endpoint', 'transaction' ) ]     = esc_html( get_option( '_wkmp_transaction_endpoint_name', esc_html__( 'Transaction', 'wk-marketplace' ) ) );

					$check = get_option( 'wkmp_shipping_option', 'marketplace' );

					if ( 'marketplace' === $check && 'disabled' !== get_option( 'woocommerce_ship_to_countries', false ) ) {
						$new_items[ '../' . $page_name . '/' . $shop_address . '/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) ] = esc_html( get_option( 'mp_shipping_name', esc_html__( 'Shipping', 'wk-marketplace' ) ) );
					}

					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_profile_endpoint', 'profile' ) . '/edit' ]   = esc_html( get_option( '_wkmp_profile_endpoint_name', esc_html__( 'My Profile', 'wk-marketplace' ) ) );
					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_notification_endpoint', 'notification' ) ]   = esc_html( get_option( '_wkmp_notification_endpoint_name', esc_html__( 'Notifications', 'wk-marketplace' ) ) );
					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_shop_follower_endpoint', 'shop-follower' ) ] = esc_html( get_option( '_wkmp_shop_follower_endpoint_name', esc_html__( 'Shop Followers', 'wk-marketplace' ) ) );
					$new_items = apply_filters( 'mp_woocommerce_account_menu_options', $new_items );
					$new_items[ '../' . $page_name . '/' . get_option( '_wkmp_asktoadmin_endpoint', 'asktoadmin' ) ] = esc_html( get_option( '_wkmp_asktoadmin_endpoint_name', esc_html__( 'Ask Admin', 'wk-marketplace' ) ) );

					if ( 1 === intval( get_option( '_wkmp_separate_seller_dashboard' ) ) ) {
						$new_items['../seperate-dashboard'] = esc_html__( 'Admin Dashboard', 'wk-marketplace' );
					}
					?>
					<style>
						.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--seperate-dashboard {
							margin-bottom: 40px;
						}
					</style>
					<?php
				}

				$new_items += $items;
			}

			return $new_items;
		}

		/**
		 * My account menu for seller pages
		 *
		 * @return mixed
		 */
		public function wkmp_shipping_icon_style() {
			global $wkmarketplace;
			$page_name   = $wkmarketplace->seller_page_slug;
			$seller_info = $wkmarketplace->wkmp_get_seller_info( get_current_user_id() );

			if ( $seller_info ) {
				$obj_notification = new Common\WKMP_Seller_Notification();
				$total_count      = $obj_notification->wkmp_seller_panel_notification_count( get_current_user_id() );
				?>
				<style type="text/css" media="screen">
					/** Shipping menu */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . $seller_info->shop_address . get_option( '_wkmp_shipping_endpoint', 'shipping' ) ); ?> a:before {
						content: "\e95a";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/** Notification menu */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_notification_endpoint', 'notification' ) ); ?> a:after {
						content: "<?php echo esc_attr( $total_count ); ?>";
						display: inline-block;
						margin-left: 5px;
						background-color: #96588a;
						color: #fff;
						padding: 0 6px;
						border-radius: 3px;
						line-height: normal;
						vertical-align: middle;
					}

					/**Dashboard */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) ); ?> a:before {
						content: "\e94e";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Product list */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_product_list_endpoint', 'product-list' ) ); ?> a:before {
						content: "\e947";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Notification */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_notification_endpoint', 'notification' ) ); ?> a:before {
						content: "\e90c";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Shop follower */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_shop_follower_endpoint', 'shop-follower' ) ); ?> a:before {
						content: "\e953";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Order history */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_order_history_endpoint', 'order-history' ) ); ?> a:before {
						content: "\e92b";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Transaction */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_transaction_endpoint', 'transaction' ) ); ?> a:before {
						content: "\e925";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Ask to admin */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_asktoadmin_endpoint', 'asktoadmin' ) ); ?> a:before {
						content: "\e928";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Profile Edit */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $page_name . get_option( '_wkmp_profile_endpoint', 'profile' ) ); ?>edit a:before {
						content: "\e960";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Admin Dashboard. */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--seperate-dashboard a:before {
						content: "\f120";
						font-family: 'dashicons';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
						}

					/**Favourite seller. */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--favourite-seller a:before {
						content: "\e953";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						}

				</style>
				<?php
			}
		}

		/**
		 * My account menu for seller pages
		 */
		public function wkmp_return_wc_account_menu() {
			wc_print_notices();
			?>
			<nav class="woocommerce-MyAccount-navigation">
				<ul class="wkmp-account-nav wkmp-nav-vertical">
					<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
						<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
							<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<?php
		}

		/**
		 * Call seller sub pages in seller page shortcode.
		 *
		 * @return void
		 */
		public function wkmp_call_seller_pages() {
			global $wkmarketplace;

			$seller_id   = get_current_user_id();
			$seller_info = $wkmarketplace->wkmp_get_seller_info( $seller_id );

			if ( ! $seller_info && get_query_var( 'info' ) ) {
				$seller_id   = $wkmarketplace->wkmp_get_seller_id_by_shop_address( get_query_var( 'info' ) );
				$seller_info = $wkmarketplace->wkmp_get_seller_info( $seller_id );
			}

			$page_name = get_query_var( 'pagename' );
			$main_page = get_query_var( 'main_page' );

			if ( ! empty( $page_name ) && $seller_info && $page_name === $wkmarketplace->seller_page_slug ) {

				switch ( $main_page ) {
					case get_option( '_wkmp_dashboard_endpoint', 'dashboard' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_dashboard' ) );
						break;

					case get_option( '_wkmp_product_list_endpoint', 'product-list' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_product_list' ) );
						break;

					case get_option( '_wkmp_add_product_endpoint', 'add-product' ):
					case 'product':
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_product_form' ) );
						break;

					case get_option( '_wkmp_order_history_endpoint', 'order-history' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_order_history' ) );
						break;

					case 'invoice':
						$this->seller_template->wkmp_seller_order_invoice();
						break;

					case get_option( '_wkmp_transaction_endpoint', 'transaction' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_transaction' ) );
						break;

					case $seller_info->shop_address:
						if ( get_query_var( 'ship_page' ) === get_option( '_wkmp_shipping_endpoint', 'shipping' ) || get_query_var( 'action' ) ) {
							add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_shipping' ) );
						}
						break;

					case get_option( '_wkmp_profile_endpoint', 'profile' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_profile_edit' ) );
						break;

					case get_option( '_wkmp_notification_endpoint', 'notification' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_notification' ) );
						break;

					case get_option( '_wkmp_shop_follower_endpoint', 'shop-follower' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_shop_follower' ) );
						break;

					case get_option( '_wkmp_asktoadmin_endpoint', 'asktoadmin' ):
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_asktoadmin' ) );
						break;

					case get_option( '_wkmp_store_endpoint', 'store' ):
					case get_option( '_wkmp_seller_product_endpoint', 'seller-product' ):
					case 'add-feedback':
					case 'feedback':
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_store_info' ) );
						break;

					default:
						add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_profile_info' ) );
						break;
				}
			} elseif ( $page_name === $wkmarketplace->seller_page_slug ) {
				add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_profile_info' ) );
			}
		}

		/**
		 * Set page title as per the template requested
		 *
		 * @param string $title Title of page.
		 *
		 * @return string
		 */
		public function wkmp_update_page_title( $title ) {
			global $wkmarketplace;
			$page_name = $wkmarketplace->seller_page_slug;

			if ( in_the_loop() && is_page( $page_name ) && 1 === intval( self::$page_title_display ) ) {
				self::$page_title_display = 0;
				$shipping_page            = array( get_query_var( 'ship_page' ), get_query_var( 'ship' ) );

				if ( ! empty( $shipping_page ) && in_array( get_option( '_wkmp_shipping_endpoint', 'shipping' ), $shipping_page, true ) ) {
					return esc_html( get_option( '_wkmp_shipping_endpoint_name', esc_html__( 'Shipping Zone', 'wk-marketplace' ) ) );
				}

				if ( null !== get_query_var( 'main_page' ) ) {
					$main_page = get_query_var( 'main_page' );

					switch ( $main_page ) {
						case get_option( '_wkmp_asktoadmin_endpoint', 'asktoadmin' ):
							return esc_html( get_option( '_wkmp_asktoadmin_endpoint_name', esc_html__( 'Ask Admin', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_product_list_endpoint', 'product-list' ):
							return esc_html( get_option( '_wkmp_product_list_endpoint_name', esc_html__( 'Products', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_add_product_endpoint', 'add-product' ):
							return esc_html( get_option( '_wkmp_add_product_endpoint_name', esc_html__( 'Add Product', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_order_history_endpoint', 'order-history' ):
							return esc_html( get_option( '_wkmp_order_history_endpoint_name', esc_html__( 'Order History', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_notification_endpoint', 'notification' ):
							return esc_html( get_option( '_wkmp_notification_endpoint_name', esc_html__( 'Notification', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_shop_follower_endpoint', 'shop-follower' ):
							return esc_html( get_option( '_wkmp_shop_follower_endpoint_name', esc_html__( 'Shop Follower', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_dashboard_endpoint', 'dashboard' ):
							return esc_html( get_option( '_wkmp_dashboard_endpoint_name', esc_html__( 'Dashboard', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_profile_endpoint', 'profile' ):
							return esc_html( get_option( '_wkmp_profile_endpoint_name', esc_html__( 'Profile', 'wk-marketplace' ) ) );

						case get_option( '_wkmp_transaction_endpoint', 'transaction' ):
							return esc_html( get_option( '_wkmp_transaction_endpoint_name', esc_html__( 'Transaction', 'wk-marketplace' ) ) );

						default:
							return $title;
					}
				}
			}

			return $title;
		}

		/**
		 * Clearing shipping packages.
		 */
		public function wkmp_clear_shipping_session() {
			$wc_session_key = 'shipping_for_package_0';
			WC()->session->__unset( $wc_session_key );
		}

		/**
		 * New Order map seller.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked woocommerce_checkout_order_processed.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_new_order_map_seller( $order_id ) {
			$order        = new \WC_Order( $order_id );
			$items        = $order->get_items();
			$author_array = array();

			foreach ( $items as $item ) {
				$assigned_seller = wc_get_order_item_meta( $item->get_id(), 'assigned_seller', true );
				if ( isset( $assigned_seller ) && ! empty( $assigned_seller ) ) {
					$author_array[] = $assigned_seller;
				} else {
					$author_array[] = get_post_field( 'post_author', $item->get_product_id() );
				}
			}

			$author_array = array_unique( $author_array );

			$this->db_obj_order->wkmp_new_order_map_seller( $author_array, $order_id );

			$this->wkmp_add_order_commission_data( $order_id );
		}

		/**
		 * Add order commission data.
		 *
		 * @param int $order_id Order id.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_add_order_commission_data( $order_id ) {
			if ( ! $this->db_obj_order->wkmp_check_seller_order_exists( $order_id ) ) {
				$order         = new \WC_Order( $order_id );
				$items         = $order->get_items();
				$mp_commission = new Common\WKMP_Commission();

				if ( class_exists( 'wk_advanced_commission' ) && ( 1 === get_option( 'advanced_commission_enabled' ) ) ) {
					require_once ABSPATH . 'wp-content/plugins/wp-marketplace-advanced-commission/includes/class-process-commission.php';
					$mp_commission = new \Process_Commission();
				}

				foreach ( $items as $item ) {
					$item_id         = $item->get_id();
					$assigned_seller = wc_get_order_item_meta( $item_id, 'assigned_seller', true );
					$tax_total       = 0;

					if ( isset( $item['variation_id'] ) && $item['variation_id'] ) {
						$product_id      = $item['variation_id'];
						$commission_data = $mp_commission->wkmp_calculate_product_commission( $item['variation_id'], $item['quantity'], $item['line_total'], $assigned_seller, $tax_total );
					} else {
						$product_id      = $item['product_id'];
						$commission_data = $mp_commission->wkmp_calculate_product_commission( $item['product_id'], $item['quantity'], $item['line_total'], $assigned_seller, $tax_total );
					}

					$seller_id        = $commission_data['seller_id'];
					$amount           = (float) $item['line_total'];
					$product_qty      = $item['quantity'];
					$discount_applied = number_format( (float) ( $item->get_subtotal() - $item->get_total() ), 2, '.', '' );
					$admin_amount     = $commission_data['admin_commission'];
					$seller_amount    = $commission_data['seller_amount'];
					$comm_applied     = $commission_data['commission_applied'];
					$comm_type        = $commission_data['commission_type'];

					$data = array(
						'order_id'           => $order_id,
						'product_id'         => $product_id,
						'seller_id'          => $seller_id,
						'amount'             => number_format( (float) $amount, 2, '.', '' ),
						'admin_amount'       => number_format( (float) $admin_amount, 2, '.', '' ),
						'seller_amount'      => number_format( (float) $seller_amount, 2, '.', '' ),
						'quantity'           => $product_qty,
						'commission_applied' => number_format( (float) $comm_applied, 2, '.', '' ),
						'discount_applied'   => $discount_applied,
						'commission_type'    => $comm_type,
					);

					$this->db_obj_order->wkmp_insert_mporders_data( $data );
				}

				// Shipping calculation.
				$chosen_ship_method = WC()->session->get( 'chosen_shipping_methods', array() );
				$ship_sess          = WC()->session->get( 'shipping_sess_cost', array() );
				$shipping_cost_list = $ship_sess;

				if ( ! empty( WC()->session->get( 'shipping_cost_list' ) ) ) {
					$shipping_cost_list = WC()->session->get( 'shipping_cost_list' );
				}

				$ship_sess = ( ! empty( $chosen_ship_method ) && is_iterable( $chosen_ship_method ) && count( $chosen_ship_method ) > 0 && isset( $shipping_cost_list[ $chosen_ship_method[0] ] ) ) ? $shipping_cost_list[ $chosen_ship_method[0] ] : '';
				$ship_sess = apply_filters( 'wk_mp_modify_shipping_session', $ship_sess, $order_id );

				WC()->session->__unset( 'shipping_sess_cost' );
				WC()->session->__unset( 'shipping_cost_list' );

				$ship_cost = 0;

				if ( ! empty( $ship_sess ) ) {
					foreach ( $ship_sess as $sel_id => $sel_detail ) {
						$sel_id     = apply_filters( 'wkmp_shipping_session_seller_id', $sel_id );
						$ship_title = empty( $sel_detail['title'] ) ? '' : $sel_detail['title'];

						if ( in_array( $ship_title, $chosen_ship_method, true ) ) {
							$shiping_cost = empty( $sel_detail['cost'] ) ? 0 : $sel_detail['cost'];
							$shiping_cost = number_format( (float) $shiping_cost, 2, '.', '' );
							$ship_cost    = $ship_cost + $shiping_cost;

							$push_arr = array(
								'shipping_method_id' => $ship_title,
								'shipping_cost'      => $shiping_cost,
							);

							foreach ( $push_arr as $key => $value ) {
								$insert = array(
									'seller_id'  => $sel_id,
									'order_id'   => $order_id,
									'meta_key'   => $key,
									'meta_value' => $value,
								);
								$this->db_obj_order->wkmp_insert_mporders_meta_data( $insert );
							}
						}
					}
				}

				$coupon_detail = WC()->cart->get_coupons();

				if ( $coupon_detail ) {
					foreach ( $coupon_detail as $key => $value ) {
						$coupon_code     = $key;
						$coupon_post_obj = get_page_by_title( $coupon_code, OBJECT, 'shop_coupon' );
						$coupon_create   = $coupon_post_obj->post_author;

						$insert = array(
							'seller_id'  => $coupon_create,
							'order_id'   => $order_id,
							'meta_key'   => 'discount_code',
							'meta_value' => $coupon_code,
						);

						$this->db_obj_order->wkmp_insert_mporders_meta_data( $insert );
					}
				}

				if ( 'yes' !== get_post_meta( $order_id, '_wkmpsplit_order', true ) ) {
					$mp_commission = new Common\WKMP_Commission();
					$mp_commission->wkmp_update_seller_order_info( $order_id );
				}
			}
		}

		/**
		 * Seller collection pagination
		 *
		 * @param int $max_num_pages max page count.
		 */
		public function wkmp_seller_collection_pagination( $max_num_pages ) {
			if ( $max_num_pages > 1 ) {
				?>
				<nav class="woocommerce-pagination">
					<?php
					echo wp_kses_post(
						paginate_links(
							apply_filters(
								'woocommerce_pagination_args',
								array(
									'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
									'format'    => '',
									'add_args'  => false,
									'current'   => max( 1, get_query_var( 'pagenum' ) ),
									'total'     => $max_num_pages,
									'prev_text' => '&larr;',
									'next_text' => '&rarr;',
									'type'      => 'list',
									'end_size'  => 3,
									'mid_size'  => 3,
								)
							)
						)
					);
					?>
				</nav>
				<?php
			}
		}

		/**
		 * Function to redirect seller.
		 */
		public function wkmp_redirect_seller_tofront() {
			global $wkmarketplace;

			$current_user = wp_get_current_user();
			$role_name    = $current_user->roles;
			$sep_dash     = get_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', true );

			$page_name     = $wkmarketplace->seller_page_slug;
			$allowed_pages = array(
				get_option( '_wkmp_store_endpoint', 'store' ),
				'profile',
				'add-feedback',
				'feedback',
				get_option( '_wkmp_seller_product_endpoint', 'seller-product' ),
			);

			if ( get_option( '_wkmp_separate_seller_dashboard' ) && ! empty( $sep_dash ) && in_array( 'wk_marketplace_seller', $role_name, true ) && ( get_query_var( 'pagename' ) === $page_name ) && ! in_array( get_query_var( 'main_page' ), $allowed_pages, true ) ) {
				if ( ! is_admin() ) {
					$wkmarketplace->wkmp_add_role_cap( $current_user->ID );
					wp_safe_redirect( esc_url( admin_url( 'admin.php?page=seller' ) ) );
					exit;
				}
			} elseif ( empty( get_option( '_wkmp_separate_seller_dashboard' ) ) || empty( $sep_dash ) && ! in_array( get_query_var( 'main_page' ), $allowed_pages, true ) ) {

				$wkmarketplace->wkmp_remove_role_cap( $current_user->ID );

				$php_self = isset( $_SERVER['PHP_SELF'] ) ? wc_clean( $_SERVER['PHP_SELF'] ) : '';

				if ( defined( 'DOING_AJAX' ) || '/wp-admin/async-upload.php' === $php_self ) {
					return;
				}

				if ( in_array( 'wk_marketplace_seller', $role_name, true ) && is_admin() ) {
					wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
					exit;
				}
			}
		}

		/**
		 * Showing success notice on completing seller registration.
		 *
		 * @hooked 'woocommerce_account_navigation' Action hook
		 */
		public function wkmp_show_register_success_notice() {
			global $wkmarketplace;
			$user_id     = get_current_user_id();
			$wkmp_notice = get_user_meta( $user_id, 'wkmp_show_register_notice', true );
			if ( ! empty( $wkmp_notice ) ) {
				wc_print_notice( esc_html( $wkmp_notice ) );
				delete_user_meta( $user_id, 'wkmp_show_register_notice' );
			}

			if ( empty( $wkmp_notice ) ) {
				$is_pending_seller = $wkmarketplace->wkmp_user_is_pending_seller( $user_id );

				if ( $is_pending_seller ) {
					$wkmp_notice = esc_html__( 'Your seller account is under review and will be approved by the admin.', 'wk-marketplace' );
					wc_print_notice( $wkmp_notice, 'notice' );
				}
			}
		}

		/**
		 * Storing data into WC session for rendering on woocommerce thank you.
		 *
		 * @hooked woocommerce_checkout_order_processed
		 *
		 * @param int       $order_id Order id.
		 * @param array     $posted_data Posted data.
		 * @param \WC_Order $order Order object.
		 */
		public function collect_order_data_for_ga( $order_id, $posted_data, $order ) {
			global $wkmarketplace;
			$ga_tracking_number = $wkmarketplace->wkmp_get_ga_number();
			if ( ! empty( $ga_tracking_number ) && ! empty( $order_id ) ) {
				$order       = ( $order instanceof \WC_Order ) ? $order : wc_get_order( $order_id );
				$order_id    = $order->get_id();
				$ga_products = array();

				$items = $order->get_items( 'line_item' );

				foreach ( $items as $item ) {
					$prod_id = $item->get_product_id();
					$product = wc_get_product( $prod_id );
					if ( $product instanceof \WC_product ) {
						$category_ids   = $product->get_category_ids();
						$category_name  = '';
						$category_names = array();

						if ( is_array( $category_ids ) && count( $category_ids ) > 0 ) {
							$category_id = $category_ids[0];
							if ( is_numeric( $category_id ) && $category_id > 0 ) {
								$cat_term = get_term_by( 'id', $category_id, 'product_cat' );
								if ( $cat_term ) {
									$category_name    = $cat_term->name;
									$category_names[] = $category_name;
								}
							}
						}

						$post_author = get_post_field( 'post_author', $prod_id );
						$shop_name   = get_user_meta( $post_author, 'shop_name', true );
						$brand       = empty( $shop_name ) ? get_bloginfo( 'name' ) : $shop_name;

						$ga_products[ $prod_id ] = array_map(
							'html_entity_decode',
							array(
								'id'       => $prod_id,
								'sku'      => $wkmarketplace->wkmp_get_sku( $product ),
								'category' => implode( ',', $category_names ),
								'name'     => $product->get_title() . '(' . $post_author . ')',
								'quantity' => $item->get_quantity(),
								'price'    => $order->get_item_subtotal( $item ),
								'currency' => $order->get_currency(),
								'brand'    => $brand,
								'position' => $post_author,
								'variant'  => $product->get_type(),
							)
						);
					}
				}

				$ga_data = array(
					'products'    => $ga_products,
					'transaction' => array(
						'id'          => $order_id,
						'affiliation' => esc_attr( get_bloginfo( 'name' ) ),
						'revenue'     => $order->get_total(),
						'shipping'    => $order->get_shipping_total(),
						'tax'         => $order->get_total_tax(),
						'currency'    => $order->get_currency(),
					),
				);

				$cookie_key = '_wkmp_google_analytics_data';
				setcookie( $cookie_key, base64_encode( wp_json_encode( $ga_data ) ), time() + ( 30 * 24 * 3600 ), '/' ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
				$_COOKIE[ $cookie_key ] = $ga_data; //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE

				$wkmarketplace->log( "Google analytics data for order id: $order_id: " . print_r( $ga_data, true ) );
			}
		}

		/**
		 * Adding Google analytics scripts for tracking.
		 */
		public function wkmp_add_ga_script() {
			global $wkmarketplace, $post;
			$ga_tracking_number = $wkmarketplace->wkmp_get_ga_number();
			if ( ! empty( $ga_tracking_number ) ) {
				$post_id      = isset( $post->ID ) ? $post->ID : 0;
				$post_title   = isset( $post->post_title ) ? $post->post_title : 'NA';
				$anonymize_ip = get_option( '_wkmp_analytics_anonymize_ip', false );
				$anonymize_ip = $anonymize_ip ? '1' : '0';
				$wkmarketplace->log( "Google analytics data sending page view with ID: $post_id, Post title: $post_title, for tracking number: $ga_tracking_number, anonymize_ip: $anonymize_ip" );
				?>
				<!-- Google Analytics Script -->
				<script>
					(function (i, s, o, g, r, a, m) {
						i['GoogleAnalyticsObject'] = r;
						i[r] = i[r] || function () {
							(i[r].q = i[r].q || []).push(arguments)
						}, i[r].l = 1 * new Date();
						a = s.createElement(o),
							m = s.getElementsByTagName(o)[0];
						a.async = 1;
						a.src = g;
						m.parentNode.insertBefore(a, m)
					})(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
				</script>
				<script>
					ga('create', '<?php echo esc_attr( $ga_tracking_number ); ?>', 'auto');
					ga('set', 'userId', <?php echo esc_attr( get_current_user_id() ); ?>);
					ga('set', 'anonymizeIp', <?php echo esc_attr( $anonymize_ip ); ?>);
					<?php esc_js( $this->print_ga_advanced_tracking_code() ); ?>
					ga('send', 'pageview');
				</script>
				<?php
			}
		}

		/**
		 * Printing google analytics code on thank you page.
		 */
		public function print_ga_advanced_tracking_code() {
			global $wkmarketplace;
			if ( is_checkout() && ! empty( is_wc_endpoint_url( 'order-received' ) ) ) {
				$cookie_key = '_wkmp_google_analytics_data';
				$ga_data    = isset( $_COOKIE[ $cookie_key ] ) ? base64_decode( $_COOKIE[ $cookie_key ] ) : 0; //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE

				$wkmarketplace->log( 'Google analytics data on wp_head base 64: ' . print_r( $ga_data, true ) );

				$ga_data = empty( $ga_data ) ? array() : json_decode( $ga_data, true );

				$wkmarketplace->log( 'Google analytics data on wp_head array: ' . print_r( $ga_data, true ) );

				if ( ! empty( $ga_data ) ) {
					file_get_contents( plugin_dir_path( WKMP_FILE ) . 'templates/front/tracking-blocks/wkmp-ga-codes.phtml' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingNonPHPFile.IncludingNonPHPFile
					$ga_data = array();
					setcookie( $cookie_key, base64_encode( wp_json_encode( $ga_data ) ), time() + ( 30 * 24 * 3600 ), '/' ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
					$_COOKIE[ $cookie_key ] = $ga_data;
				}
			}
		}

		/**
		 * Adding seller profile link with each order item.
		 *
		 * @param \WC_Order_Item_Product $item Order item object.
		 * @param string                 $cart_item_key Cart Item key.
		 * @param array                  $values array of values.
		 * @param \WC_Order              $order Order object.
		 *
		 * @hooked 'woocommerce_checkout_create_order_line_item' Action link.
		 */
		public function wkmp_add_order_item_meta( $item, $cart_item_key, $values, $order ) {
			global $wkmarketplace;
			$prod_id = isset( $values['product_id'] ) ? $values['product_id'] : 0;
			if ( $prod_id > 0 ) {
				$author_id    = get_post_field( 'post_author', $prod_id );
				$display_name = get_the_author_meta( 'shop_name', $author_id );
				$display_name = empty( $display_name ) ? get_bloginfo( 'name' ) : $display_name;

				$seller_shop_address = get_user_meta( $author_id, 'shop_address', true );
				$shop_url            = '#';

				if ( empty( $seller_shop_address ) ) {
					$shop_page_id = wc_get_page_id( 'shop' );
					$shop_page    = get_post( $shop_page_id );
					$shop_url     = get_permalink( $shop_page );
				} else {
					$shop_url = home_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . $seller_shop_address );
				}

				$shop_link = sprintf( /* translators: %1$s: Shop link, %2$s: Shop Name, %3$s: Closing anchor.  */ esc_html__( '%1$s %2$s %3$s', 'wk-marketplace' ), '<a href="' . esc_url( $shop_url ) . '">', esc_html( $display_name ), '</a>' );
				$item->update_meta_data( 'Sold By', $shop_link );
			}
		}

		/**
		 * Validate and show notice for minimum order amount on cart.
		 *
		 * @hooked woocommerce_checkout_process
		 */
		public function wkmp_validate_minimum_order_amount() {
			$threshold_notes = $this->is_threshold_reached();
			$qty_notes       = $this->is_qty_allowed();

			if ( count( $threshold_notes ) > 0 ) {
				$this->show_invalid_order_total_notice( $threshold_notes );
			}

			if ( count( $qty_notes ) > 0 ) {
				$this->show_invalid_qty_notice( $qty_notes );
			}

			if ( count( $threshold_notes ) > 0 || count( $qty_notes ) > 0 ) {
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
			}
		}

		/**
		 * Validating order total on checkout.
		 *
		 * @hooked woocommerce_checkout_update_order_review
		 *
		 * @param array $posted_data Posted data.
		 */
		public function wkmp_validate_minimum_order_amount_checkout( $posted_data ) {
			$threshold_notes = $this->is_threshold_reached();
			$qty_notes       = $this->is_qty_allowed();

			if ( count( $threshold_notes ) > 0 ) {
				$this->show_invalid_order_total_notice( $threshold_notes );
			}

			if ( count( $qty_notes ) > 0 ) {
				$this->show_invalid_qty_notice( $qty_notes );
			}
		}

		/**
		 * Removing 'Place Order' button from checkout if order total doesn't exceed threshold amount.
		 *
		 * @hooked woocommerce_order_button_html
		 *
		 * @param string $order_button Order button.
		 *
		 * @return mixed|string
		 */
		public function wkmp_remove_place_order_button( $order_button ) {
			if ( count( $this->is_threshold_reached() ) > 0 || count( $this->is_qty_allowed() ) > 0 ) {
				$order_button = '';
			}

			return $order_button;
		}

		/**
		 * Validate minimum order total for products.
		 *
		 * @return array|false
		 */
		public function is_threshold_reached() {
			$minimum_enabled = get_option( '_wkmp_enable_minimum_order_amount', 0 );
			$threshold_notes = array();

			if ( ! $minimum_enabled ) {
				return $threshold_notes;
			}

			$seller_totals = array();

			foreach ( WC()->cart->get_cart() as $item ) {
				$sell_product_id = isset( $item['product_id'] ) ? $item['product_id'] : 0;
				$sell_product_id = ( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) ? $item['variation_id'] : $sell_product_id;

				if ( $sell_product_id > 0 ) {
					$author_id  = get_post_field( 'post_author', $sell_product_id );
					$author     = get_user_by( 'ID', $author_id );
					$item_total = $item['line_subtotal'] + $item['line_tax'];
					if ( in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
						if ( isset( $seller_totals[ $author_id ] ) ) {
							$seller_totals[ $author_id ] += $item_total;
						} else {
							$seller_totals[ $author_id ] = $item_total;
						}
					} else {
						$seller_totals['admin']  = isset( $seller_totals['admin'] ) ? $seller_totals['admin'] : 0;
						$seller_totals['admin'] += $item_total;
					}
				}
			}

			$minimum_amount                  = get_option( '_wkmp_minimum_order_amount', 0 );
			$seller_min_amount_admin_default = get_option( '_wkmp_seller_min_amount_admin_default', false );

			foreach ( $seller_totals as $seller_id => $seller_total ) {
				if ( 'admin' === $seller_id ) {
					if ( $seller_totals['admin'] < $minimum_amount ) {
						$threshold_notes['admin'] = array(
							'min_amount'    => $minimum_amount,
							'current_total' => $seller_totals['admin'],
						);
					}
				} else {
					$minimum_seller_amount = get_user_meta( $seller_id, '_wkmp_minimum_order_amount', true );
					$minimum_sell_amount   = empty( $minimum_seller_amount ) ? ( $seller_min_amount_admin_default ? $minimum_amount : 0 ) : $minimum_seller_amount;
					if ( $seller_totals[ $seller_id ] < $minimum_sell_amount ) {
						$threshold_notes[ $seller_id ] = array(
							'min_amount'    => $minimum_sell_amount,
							'current_total' => $seller_totals[ $seller_id ],
						);
					}
				}
			}

			return $threshold_notes;
		}

		/**
		 * Check if products quantities are allowed to purchased.
		 *
		 * @return array
		 */
		public function is_qty_allowed() {
			$max_qty_enabled = get_option( '_wkmp_enable_product_qty_limit', 0 );
			$qty_notes       = array();

			if ( ! $max_qty_enabled ) {
				return $qty_notes;
			}

			foreach ( WC()->cart->get_cart() as $item ) {
				$sell_product_id = isset( $item['product_id'] ) ? $item['product_id'] : 0;

				if ( $sell_product_id > 0 ) {
					$sell_product_obj = wc_get_product( $sell_product_id );

					if ( $sell_product_obj->get_sold_individually() ) {
						continue;
					}

					$qty_limit   = get_post_meta( $sell_product_id, '_wkmp_max_product_qty_limit', true );
					$product_qty = isset( $item['quantity'] ) ? $item['quantity'] : 0;

					if ( empty( $qty_limit ) ) {
						$author_id = get_post_field( 'post_author', $sell_product_id );
						$author    = get_user_by( 'ID', $author_id );
						if ( in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
							$qty_limit = get_user_meta( $author_id, '_wkmp_max_product_qty_limit', true );
						}
						$qty_limit = empty( $qty_limit ) ? get_option( '_wkmp_max_product_qty_limit', true ) : $qty_limit;
					}

					if ( $qty_limit > 0 && $product_qty > $qty_limit ) {
						$qty_notes[ $sell_product_id ] = $qty_limit;
					}
				}
			}

			return $qty_notes;
		}

		/**
		 * Showing notices when order total is less than threshold value.
		 *
		 * @param array $notes Seller notes.
		 */
		public function show_invalid_order_total_notice( $notes ) {
			foreach ( $notes as $seller_id => $min_data ) {
				$minimum_amount = isset( $min_data['min_amount'] ) ? $min_data['min_amount'] : 0;
				$current_total  = isset( $min_data['current_total'] ) ? $min_data['current_total'] : 0;
				$seller_name    = ( 'admin' === $seller_id ) ? 'Admin' : get_user_meta( $seller_id, 'shop_name', true );

				$message = sprintf( /* translators: %1$s: Shop name, %2$s: Minimum amount, %3$s: Current total. */ esc_html__( 'Minimum products total for %1$s Shop product(s) should be %2$s. Current total (inclusive tax) is: %3$s.', 'wk-marketplace' ), '<strong>' . $seller_name . '</strong>', wc_price( $minimum_amount ), wc_price( $current_total ) );

				if ( is_cart() ) {
					wc_print_notice( $message, 'error' );
				} else {
					wc_add_notice( $message, 'error' );
				}
			}
		}

		/**
		 * Showing notices when product quantity is greater than threshold value.
		 *
		 * @param array $notes Qty notes.
		 */
		public function show_invalid_qty_notice( $notes ) {
			foreach ( $notes as $prod_id => $max_allowed_qty ) {
				$cart_product = wc_get_product( $prod_id );
				$message      = sprintf( /* translators: %1$s: Shop name, %2$s: Minimum amount. */ esc_html__( 'Sorry, but you can only add maximum %1$s quantity of %2$s in this cart.', 'wk-marketplace' ), '<strong>' . $max_allowed_qty . '</strong>', '<strong>' . $cart_product->get_title() . '</strong>' );

				if ( is_cart() ) {
					wc_print_notice( $message, 'error' );
				} else {
					wc_add_notice( $message, 'error' );
				}
			}
		}

		/**
		 * Adding woocommerce-account class on seller pages.
		 *
		 * @param array $classes Body classes.
		 *
		 * @hooked body_class.
		 *
		 * @return array|mixed
		 */
		public function wkmp_add_body_class( $classes ) {
			global $wkmarketplace;
			if ( $wkmarketplace->wkmp_is_seller_page() ) {
				$user_id = get_current_user_id();
				if ( $user_id > 0 && $wkmarketplace->wkmp_user_is_seller( $user_id ) ) {
					$classes   = is_array( $classes ) ? $classes : array();
					$classes[] = 'woocommerce-account wkmp-seller-endpoints';
				}
			}

			return $classes;
		}

		/**
		 * WC Active menu class.
		 *
		 * @param array  $classes Menu classes.
		 * @param string $endpoint Endpoint.
		 *
		 * @return array
		 */
		public function wkmp_wc_menu_active_class( $classes, $endpoint ) {
			global $wp;

			$classes = is_array( $classes ) ? $classes : array();

			// Set current item class.
			$current = isset( $wp->query_vars[ $endpoint ] );

			if ( isset( $wp->query_vars['main_page'] ) && strpos( $endpoint, $wp->query_vars['main_page'] ) > 0 ) {
				$current = true;
			}

			if ( $current ) {
				$classes[] = 'is-active';
			}

			return $classes;
		}

		/**
		 * Disable User editor in front end.
		 */
		public function wkmp_remove_admin_bar() {
			if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
				show_admin_bar( false );
			}
		}
	}
}
