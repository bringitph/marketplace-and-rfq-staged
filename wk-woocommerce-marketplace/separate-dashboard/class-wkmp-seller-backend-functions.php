<?php
/**
 * Seller separate dashboard functions class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Separate_Dashboard;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Templates\Front\Seller\Dashboard;
use WkMarketplace\Templates\Admin\Notification;
use WkMarketplace\Templates\Admin\Seller;
use WkMarketplace\Templates\Front\Seller\Orders;

if ( ! class_exists( 'WKMP_Seller_Backend_Functions' ) ) {
	/**
	 * Seller dashboard backend class.
	 */
	class WKMP_Seller_Backend_Functions {
		/**
		 * WPDB object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * WKMP_Seller_Backend_Functions constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Hide marketing menu.
		 *
		 * @param array $features Features.
		 *
		 * @return array
		 */
		public function wkmp_hide_marketing_menu( $features ) {
			return array_values(
				array_filter(
					$features,
					function ( $feature ) {
						return 'marketing' !== $feature;
					}
				)
			);
		}

		/**
		 * Seller menu at backend.
		 */
		public function wkmp_seller_admin_menu() {
			add_menu_page(
				esc_html__( 'Seller', 'wk-marketplace' ),
				esc_html__( 'Marketplace', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'seller',
				array(
					$this,
					'wkmp_seller_admin_dashboard',
				),
				WKMP_PLUGIN_URL . 'assets/images/MP.png',
				55
			);
			add_submenu_page(
				'seller',
				esc_html__( 'Seller Dashboard', 'wk-marketplace' ),
				esc_html__( 'Reports', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'seller',
				array(
					$this,
					'wkmp_seller_admin_dashboard',
				)
			);

			$hook_option = add_submenu_page(
				'seller',
				esc_html__( 'Orders', 'wk-marketplace' ),
				esc_html__( 'Order History', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'order-history',
				array(
					$this,
					'wkmp_seller_admin_order_history',
				)
			);

			add_submenu_page(
				'seller',
				esc_html__( 'Transaction', 'wk-marketplace' ),
				esc_html__( 'Transaction', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'seller-transaction',
				array(
					$this,
					'wkmp_seller_admin_transactions',
				)
			);
			add_submenu_page(
				'seller',
				esc_html__( 'Notifications', 'wk-marketplace' ),
				esc_html__( 'Notifications', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'seller-notifications',
				array(
					$this,
					'wkmp_seller_admin_notifications',
				)
			);
			add_submenu_page(
				'seller',
				esc_html__( 'Shop Followers', 'wk-marketplace' ),
				esc_html__( 'Shop Followers', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'seller-shop-followers',
				array(
					$this,
					'wkmp_seller_admin_shop_followers',
				)
			);
			add_submenu_page(
				'seller',
				esc_html__( 'My Profile', 'wk-marketplace' ),
				esc_html__( 'My Profile', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'seller-profile',
				array(
					$this,
					'wkmp_seller_admin_side_profile',
				)
			);
			add_submenu_page(
				'seller',
				esc_html__( 'Ask to admin', 'wk-marketplace' ),
				esc_html__( 'Ask to admin', 'wk-marketplace' ),
				'wk_marketplace_seller',
				'ask-to-admin',
				array(
					$this,
					'wkmp_seller_ask_to_admin',
				)
			);
			add_action( 'load-' . $hook_option, array( $this, 'wkmp_add_page_option_order_list' ) );
		}

		/**
		 * Hide publish button.
		 */
		public function wkmp_hide_publish_button() {
			if ( 1 !== intval( get_option( '_wkmp_allow_seller_to_publish', 1 ) ) ) {
				?>
				<script type="text/javascript">
					document.addEventListener('DOMContentLoaded', function () {
						if (document.getElementById('publish')) {
							document.getElementById('publish').disabled = true;
						}
					}, false);
				</script>
				<?php
			}
		}

		/**
		 * Seller shop followers tab.
		 */
		public function wkmp_seller_admin_shop_followers() {
			$obj = new WKMP_Seller_Backend_Shop_Followers();
			$obj->prepare_items();
			$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Shop Follower', 'wk-marketplace' ); ?></h1>
				<form method="GET">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
					<?php $obj->search_box( esc_html__( 'Search', 'wk-marketplace' ), 'search-id' ); ?>
					<?php $obj->display(); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Seller notifications menu.
		 */
		public function wkmp_seller_admin_notifications() {
			$dash_obj = new Notification\WKMP_Notification_Templates_Handller();
		}

		/**
		 * Add option for order list.
		 */
		public function wkmp_add_page_option_order_list() {
			$args = array(
				'label'   => esc_html__( 'Order per page', 'wk-marketplace' ),
				'default' => 20,
				'option'  => 'order_per_page',
			);
			add_screen_option( 'per_page', $args );
		}

		/**
		 * Shows seller transaction.
		 */
		public function wkmp_seller_admin_transactions() {
			$action         = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
			$transaction_id = filter_input( INPUT_GET, 'tid', FILTER_SANITIZE_NUMBER_INT );
			if ( 'view' === $action && ! empty( $transaction_id ) ) {
				$_GET['seller-id'] = get_current_user_id();
				$obj               = new Seller\WKMP_Seller_Transaction_View( $transaction_id );
			} else {
				$obj = new WKMP_Seller_Backend_Transaction_List();
				$obj->prepare_items();
				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Transactions', 'wk-marketplace' ); ?></h1>
					<form method="GET">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
						<?php $obj->search_box( esc_html__( 'Search', 'wk-marketplace' ), 'search-id' ); ?>
						<?php $obj->display(); ?>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Admin dashboard.
		 */
		public function wkmp_seller_admin_dashboard() {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				if ( ! class_exists( 'WC_Admin_Report' ) ) {
					require WC_ABSPATH . 'includes/admin/reports/class-wc-admin-report.php';
				}
				$dash_obj = new Dashboard\WKMP_Dashboard();
				echo '<div class="wrap"><h1>' . esc_html__( 'Dashboard', 'wk-marketplace' ) . '</h1><hr>';
				$dash_obj->wkmp_dashboard_page();
				echo '</div>';
			} else {
				wp_safe_redirect( admin_url( '?page=seller' ) );
				exit( 0 );
			}
		}

		/**
		 * Admin order history.
		 */
		public function wkmp_seller_admin_order_history() {
			$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
			$oid    = filter_input( INPUT_GET, 'oid', FILTER_SANITIZE_NUMBER_INT );
			if ( 'view' === $action && ! empty( $oid ) ) {
				$obj = new Orders\WKMP_Orders( get_current_user_id(), false, $oid );
			} else {
				$obj    = new WKMP_Seller_Backend_Order_List();
				$search = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
				if ( ! empty( $search ) ) {
					$obj->prepare_items( $search );
				} else {
					$obj->prepare_items();
				}
				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Orders', 'wk-marketplace' ); ?></h1>
					<hr>
					<form method="GET">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
						<?php
						$obj->search_box( esc_html__( 'Search', 'wk-marketplace' ), 'search-id' );
						$obj->display();
						?>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Admin side profile.
		 */
		public function wkmp_seller_admin_side_profile() {
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			if ( 'seller-profile' === $page_name ) {
				new WKMP_Seller_Backend_Profile();
			}
		}

		/**
		 * Product type selector.
		 *
		 * @param array $types Types.
		 *
		 * @return mixed
		 */
		public function wkmp_seller_product_type_selector( $types ) {
			global $pagenow, $current_user, $post;
			$allowed_product_types = get_option( '_wkmp_seller_allowed_product_types' );
			if ( $allowed_product_types ) {
				if ( 'post.php' === $pagenow && in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
					$product_id = $post->ID;
					$product    = wc_get_product( $product_id );
					$type       = $product->get_type();

					if ( ! in_array( $type, $allowed_product_types, true ) ) {
						array_push( $allowed_product_types, $type );
					}

					foreach ( $types as $key => $value ) {
						if ( ! in_array( $key, $allowed_product_types, true ) ) {
							unset( $types[ $key ] );
						}
					}
				}
			}

			return $types;
		}

		/**
		 * Function for overriding terms.
		 *
		 * @param array $args args array.
		 * @param array $taxonomies taxonomies array.
		 */
		public function wkmp_override_get_terms_args( $args, $taxonomies ) {
			global $pagenow, $current_user;

			if ( ! in_array( 'product_cat', $taxonomies, true ) ) {
				return $args;
			}

			if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && in_array( 'product_cat', $taxonomies, true ) && in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				$allowed_cat = get_user_meta( $current_user->ID, 'wkmp_seller_allowed_categories', true );

				if ( ! $allowed_cat ) {
					$allowed_categories = get_option( '_wkmp_seller_allowed_categories', array() );
				} else {
					$allowed_categories = $allowed_cat;
				}
				$args['slug'] = $allowed_categories;
			}

			return $args;
		}

		/**
		 * Function for filtering product query.
		 *
		 * @param object $query query object.
		 */
		public function wkmp_products_admin_filter_query( $query ) {
			global $typenow, $current_user;
			if ( 'product' === $typenow && in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				$query->query_vars['author'] = $current_user->ID;
			}
		}

		/**
		 * Function for managing seller setting tabs.
		 *
		 * @param array $tabs array of tabs.
		 */
		public function wkmp_manage_wc_settings_tab_seller( $tabs ) {
			global $current_user, $current_tab;
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

			if ( is_admin() && in_array( 'wk_marketplace_seller', $current_user->roles, true ) && 'wc-settings' === $page_name ) {

				if ( 'shipping' !== $current_tab ) {
					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) );
					exit;
				}

				return array(
					'shipping' => $tabs['shipping'],
				);
			}
			return $tabs;

		}

		/**
		 * Function for managing shipping submenu.
		 *
		 * @param array $sections section array.
		 */
		public function wkmp_manage_wc_shipping_submenu( $sections ) {
			global $current_user, $current_section;
			if ( is_admin() && in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {

				if ( ! in_array( $current_section, array( '', 'classes' ), true ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=shipping&section' ) );
					exit;
				}

				return array(
					''        => $sections[''],
					'classes' => $sections['classes'],
				);
			}
			return $sections;
		}

		/**
		 * Function for filtering shipping classes.
		 *
		 * @param array $shipping_classes array of shipping classes.
		 */
		public function wkmp_filter_seller_shipping_classes( $shipping_classes ) {
			global $current_user;

			if ( is_admin() && in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				$user_shipping_classes = get_user_meta( $current_user->ID, 'shipping-classes', true );

				if ( $user_shipping_classes ) {
					$user_shipping_classes = maybe_unserialize( $user_shipping_classes );
					foreach ( $shipping_classes as $key => $value ) {
						if ( ! in_array( $value->term_id, $user_shipping_classes, true ) ) {
							unset( $shipping_classes[ $key ] );
						}
					}
				}
			}

			return $shipping_classes;
		}

		/**
		 * Ask to admin tab in backend.
		 */
		public function wkmp_seller_ask_to_admin() {
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			$action    = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

			if ( 'ask-to-admin' === $page_name && 'add' === $action ) {
				echo '<div class="wrap"><h1 class="wp-heading-inline">' . esc_html__( 'Ask to Admin', 'wk-marketplace' ) . '</h1>';
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=ask-to-admin' ) ) . '" class="page-title-action">' . esc_html__( 'Back', 'wk-marketplace' ) . '</a>';

				$errors = array();

				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( isset( $posted_data['ask_to_admin'] ) ) { // Input var okay.
					if ( isset( $posted_data['ask_to_admin_nonce'] ) && ! empty( $posted_data['ask_to_admin_nonce'] ) ) {
						if ( wp_verify_nonce( $posted_data['ask_to_admin_nonce'], 'ask_to_admin_nonce_action' ) ) {
							$errors = $this->wkmp_admin_mailer();
						} else {
							$errors['nonce-error'] = esc_html__( 'Security check failed, nonce verification failed!', 'wk-marketplace' );
						}
					} else {
						$errors['nonce-error'] = esc_html__( 'Security check failed, nonce empty!', 'wk-marketplace' );
					}

					foreach ( $errors as $value ) {
						if ( is_admin() ) {
							?>
							<div class="wrap">
								<div class="notice notice-error">
									<p><?php echo esc_html( $value ); ?></p>
								</div>
							</div>
							<?php
						} else {
							wc_print_notice( $value, 'error' );
						}
					}
				}
				?>
				<!-- Form -->
				<div id="ask-data">
					<form id="ask-form" method="post" action="">
						<p>
							<label class="label" for="query_user_sub"><b><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?></b><span class="required"> *</span></label>
							<input id='query_user_sub' class="wkmp-querysubject regular-text" type="text" name="subject">
							<span id="askesub_error" class="error-class"></span>
						</p>
						<p>
							<label class="label" for="userquery"><b><?php esc_html_e( 'Message', 'wk-marketplace' ); ?><span class="required"> *</span></b></label>
							<textarea id="userquery" rows="4" class="wkmp-queryquestion regular-text" name="message"></textarea>
							<span id="askquest_error" class="error-class"></span>
						</p>
						<div class="">
						<?php wp_nonce_field( 'ask_to_admin_nonce_action', 'ask_to_admin_nonce' ); ?>
							<input id="askToAdminBtn" type="submit" name="ask_to_admin" value="<?php esc_attr_e( 'Ask', 'wk-marketplace' ); ?>" class="button button-primary">
						</div>
					</form>
				</div>
				<?php
				echo '</div>';
			} else {
				$obj = new WKMP_Seller_Backend_Query_List();
				$obj->prepare_items();
				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Query list', 'wk-marketplace' ); ?></h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ask-to-admin&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Ask Query', 'wk-marketplace' ); ?></a>
					<hr>
					<form method="GET">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
					<?php $obj->search_box( 'Search', 'search-id' ); ?>
					<?php $obj->display(); ?>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Mail to admin.
		 *
		 * @return array
		 */
		public function wkmp_admin_mailer() {
			$errors      = array();
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();//phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $posted_data['subject'] ) && $posted_data['message'] ) {
				$subject = ! empty( $posted_data['subject'] ) ? wp_strip_all_tags( wp_unslash( $posted_data['subject'] ) ) : '';
				$message = ! empty( $posted_data['message'] ) ? wp_strip_all_tags( wp_unslash( $posted_data['message'] ) ) : '';

				if ( ! empty( $subject ) && ! empty( $message ) ) { // Input var okay.
					$current_user   = wp_get_current_user();
					$message_length = strlen( $message );

					if ( ! preg_match( '/^[A-Za-z0-9 ]{1,100}$/', $subject ) ) {
						$errors['subject-invalid'] = __( 'Subject Invalid.', 'wk-marketplace' );
					} elseif ( $message_length > 500 ) {
						$errors['message-length'] = __( 'Message length should be less than 500.', 'wk-marketplace' );
					} else {
						$current_time = gmdate( 'Y-m-d H:i:s' );
						$sql          = $this->wpdb->insert(
							$this->wpdb->prefix . 'mpseller_asktoadmin',
							array(
								'seller_id'   => $current_user->ID,
								'subject'     => $subject,
								'message'     => $message,
								'create_date' => $current_time,
							),
							array( '%d', '%s', '%s', '%s' )
						);

						if ( $sql ) {
							do_action( 'wkmp_ask_to_admin', $current_user->user_email, $subject, $message );

							if ( is_admin() ) {
								?>
								<div class="notice notice-success">
									<p><?php esc_html_e( 'Your query has been received successfully.', 'wk-marketplace' ); ?></p>
								</div>
								<?php
							} else {
								wc_print_notice( esc_html__( 'Your query has been received successfully.', 'wk-marketplace' ), 'success' );
							}
						}
					}
				} else {
					$errors['empty-field'] = __( 'Fill required fields.', 'wk-marketplace' );
				}
			}

			return $errors;
		}

		/**
		 * Backend seller zone function.
		 *
		 * @param obj    $object_data object data.
		 * @param string $handle handle.
		 * @param string $object_name object name.
		 */
		public function wkmp_override_shipping_zones( $object_data, $handle, $object_name ) {
			global $current_user;

			if ( in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				$wpdb_obj         = $this->wpdb;
				$seller_zones_arr = array();
				if ( 'wc-shipping-zones' === $handle ) {
					$seller_zones = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT zone_id FROM {$wpdb_obj->prefix}mpseller_meta WHERE seller_id = %d", $current_user->ID ), ARRAY_A );
					if ( $seller_zones && isset( $object_data['zones'] ) ) {
						foreach ( $seller_zones as $value ) {
							$seller_zones_arr[] = intval( $value['zone_id'] );
						}

						foreach ( $object_data['zones'] as $key => $value ) {
							if ( ! in_array( $value['zone_id'], $seller_zones_arr, true ) ) {
								unset( $object_data['zones'][ $key ] );
							}
						}
					} elseif ( isset( $object_data['zones'] ) ) {
						$seller_zones_arr = array();
						foreach ( $object_data['zones'] as $key => $value ) {
							if ( ! in_array( $value['zone_id'], $seller_zones_arr, true ) ) {
								unset( $object_data['zones'][ $key ] );
							}
						}
					}
				}

				if ( 'wc-shipping-classes' === $handle ) {
					$user_shipping_classes = get_user_meta( $current_user->ID, 'shipping-classes', true );

					if ( $user_shipping_classes && $object_data['classes'] ) {
						$user_shipping_classes = maybe_unserialize( $user_shipping_classes );

						foreach ( $object_data['classes'] as $key => $value ) {
							if ( ! in_array( $value->term_id, $user_shipping_classes, true ) ) {
								unset( $object_data['classes'][ $key ] );
							}
						}
					}
				}
			}

			return $object_data;
		}

		/**
		 * Seller product count.
		 *
		 * @param array $array Array.
		 *
		 * @return mixed
		 */
		public function wkmp_manage_seller_product_count( $array ) {
			global $current_user;

			if ( ! in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				return $array;
			}

			$seller_id = $current_user->ID;
			$wpdb_obj  = $this->wpdb;

			foreach ( $array as $key => $value ) {
				if ( 'all' === $key ) {
					$all_total     = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$wpdb_obj->prefix}posts WHERE post_type='product' AND post_author = %d AND post_status!='auto-draft'", esc_attr( $seller_id ) ) );
					$array[ $key ] = preg_replace( '/\s*\([^)]*\)/', '(' . $all_total . ')', $value );
				}

				if ( 'publish' === $key ) {
					$publish_total = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$wpdb_obj->prefix}posts WHERE post_type='product' AND post_status = 'publish' AND post_author = %d", esc_attr( $seller_id ) ) );
					$array[ $key ] = preg_replace( '/\s*\([^)]*\)/', '(' . $publish_total . ')', $value );
				}

				if ( 'draft' === $key ) {
					$draft_total   = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$wpdb_obj->prefix}posts WHERE post_type='product' AND post_status = 'draft' AND post_author = %d", esc_attr( $seller_id ) ) );
					$array[ $key ] = preg_replace( '/\s*\([^)]*\)/', '(' . $draft_total . ')', $value );
				}

				if ( 'trash' === $key ) {
					$trash_total   = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$wpdb_obj->prefix}posts WHERE post_type='product' AND post_status = 'trash' AND post_author = %d", esc_attr( $seller_id ) ) );
					$array[ $key ] = preg_replace( '/\s*\([^)]*\)/', '(' . $trash_total . ')', $value );
				}
			}

			return $array;
		}

		/**
		 * Removing other seller's shipping classes from Seller backend dashboard product edit page.
		 *
		 * @param array $args Get terms args.
		 * @param array $taxonomies Get term taxonomies.
		 *
		 * @hooked 'get_terms_args' filter hook.
		 *
		 * @return array.
		 */
		public function wkmp_remove_others_shipping_classes( $args, $taxonomies ) {
			global $wkmarketplace;

			if ( ! in_array( 'product_shipping_class', $taxonomies, true ) ) {
				return $args;
			}

			$user_id = get_current_user_id();

			if ( is_admin() && $user_id > 0 && $wkmarketplace->wkmp_user_is_seller( $user_id ) ) {
				$user_shipping_classes = get_user_meta( $user_id, 'shipping-classes', true );
				$user_shipping_classes = empty( $user_shipping_classes ) ? array() : maybe_unserialize( $user_shipping_classes );
				$args['include']       = $user_shipping_classes;
			}

			return $args;
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
		public function wkmp_set_backend_screen( $status, $option, $value ) {
			$options = array( 'order_per_page' );
			if ( in_array( $option, $options, true ) ) {
				return $value;
			}

			return $status;
		}
	}
}
