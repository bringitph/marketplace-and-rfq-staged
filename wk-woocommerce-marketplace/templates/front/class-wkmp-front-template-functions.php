<?php
/**
 * Front template Functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front;

use WkMarketplace\Templates\Front\Customer;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Front_Template_Functions' ) ) {
	/**
	 * Front template functions class.
	 *
	 * Class WKMP_Front_Template_Functions
	 *
	 * @package WkMarketplace\Templates\Front
	 */
	class WKMP_Front_Template_Functions {
		/**
		 * WKMP_Front_Template_Functions constructor.
		 */
		public function __construct() {
		}

		/**
		 * Seller registration fields in form
		 *
		 * @return void
		 */
		public function wkmp_seller_registration_fields() {
			require __DIR__ . '/wkmp-registration-fields.php';
		}

		/**
		 * Templates to use in js.
		 *
		 * @return void
		 */
		public function wkmp_front_footer_templates() {
			?>
			<script id="tmpl-wkmp_field_empty" type="text/html">
				<div class="wkmp-error">
					<p><?php esc_html_e( 'This is required data.', 'wk-marketplace' ); ?></p>
				</div>
			</script>
			<input type="hidden" name="wkmp-version" value="<?php echo esc_attr( MARKETPLACE_VERSION ); ?>">
			<?php
		}

		/**
		 * Call back method for display seller details on product page.
		 *
		 * @return void
		 */
		public function wkmp_product_by() {
			global $wkmarketplace;

			$seller_id = get_the_author_meta( 'ID' );
			$rating    = '';

			if ( $wkmarketplace->wkmp_user_is_seller( $seller_id ) ) {
				$customer_id = 0;
				$class       = 'style=display:none;';

				if ( is_user_logged_in() && intval( $seller_id ) !== get_current_user_id() ) {
					$customer_id = get_current_user_id();
					$class       = 'style=display:inline-block;';
				}

				$sellers = get_user_meta( $customer_id, 'favourite_seller', true );
				$sellers = $sellers ? explode( ',', $sellers ) : array();
				$sellers = array_map( 'intval', $sellers );

				$style = '';
				if ( in_array( $seller_id, $sellers, true ) ) {
					$style = 'color:#96588a;';
				}

				$url      = site_url() . '/' . $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . get_user_meta( $seller_id, 'shop_address', true );
				$shop_url = get_user_meta( $seller_id, 'shop_address', true );
				$url      = apply_filters( 'update_seller_profile_url', $url, $seller_id, $shop_url );
				?>
				<p class="mp-product-author-shop"><?php esc_html_e( 'Seller :', 'wk-marketplace' ); ?>
					<a href="<?php echo esc_url( $url ); ?>"> <?php echo esc_html( ucfirst( get_user_meta( $seller_id, 'shop_name', true ) ) ); ?> </a> <?php echo wp_kses_post( $rating ); ?>

					<span <?php echo esc_attr( $class ); ?> id="wkmp-add-seller-as-favourite" title="<?php esc_attr_e( 'Add As Favourite Seller', 'wk-marketplace' ); ?>">
						<input type="hidden" name="wkmp_seller_id" value="<?php echo esc_attr( $seller_id ); ?>"/>
						<input type="hidden" name="wkmp_customer_id" value="<?php echo esc_attr( $customer_id ); ?>"/>
						<span class="dashicons dashicons-heart" style="font-size:25px;margin-top:3px;cursor:pointer;<?php echo esc_attr( $style ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></span>
					</span>
					<span class="wkmp-loader-wrapper"><img class="wp-spin wkmp-spin-loader wkmp_hide" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>"></span>
				</p>
				<div class="wkmp-confirmation-msg wkmp_hide"></div>
				<?php
			} else {
				echo '<p> ' . esc_html__( 'Seller :', 'wk-marketplace' ) . esc_html( ucfirst( get_the_author() ) ) . '</p>';
			}
		}

		/**
		 * Callback method for Add new query var.
		 *
		 * @param array $vars Variables.
		 *
		 * @return array
		 */
		public function wkmp_add_query_vars( $vars ) {
			global $wkmarketplace;
			$customer_id = get_current_user_id();
			$vars[]      = 'favourite-seller';

			if ( $wkmarketplace->wkmp_user_is_customer( $customer_id ) ) {
				$vars[] = 'become-mp-seller';
			}

			return $vars;
		}

		/**
		 * Callback method for Insert new endpoints into the My Account menu.
		 *
		 * @param array $items menu items.
		 *
		 * @return array
		 */
		public function wkmp_new_menu_items( $items ) {
			global $wkmarketplace;
			$items       = is_array( $items ) ? $items : array();
			$customer_id = get_current_user_id();
			$logout      = '';

			if ( isset( $items['customer-logout'] ) ) {
				// Remove the logout menu item.
				$logout = $items['customer-logout'];
				unset( $items['customer-logout'] );
			}

			// Insert your custom endpoint 'favourite-seller'.
			$items['favourite-seller'] = esc_html__( 'My Favourite Seller', 'wk-marketplace' );

			// Insert Become mp seller endpoint.
			if ( $wkmarketplace->wkmp_user_is_customer( $customer_id ) ) {
				$items['become-mp-seller'] = esc_html__( 'Become a Seller', 'wk-marketplace' );
			}

			if ( ! empty( $logout ) ) {
				// Insert back the logout item.
				$items['customer-logout'] = $logout;
			}

			return $items;
		}

		/**
		 * Callback method for Set endpoint title
		 *
		 * @param string $title Title.
		 *
		 * @return string $title
		 */
		public function wkmp_endpoint_title( $title ) {
			global $wp_query, $wkmarketplace;

			$customer_id               = get_current_user_id();
			$is_fav_seller_endpoint    = isset( $wp_query->query_vars['favourite-seller'] );
			$is_become_seller_endpoint = isset( $wp_query->query_vars['become-mp-seller'] );

			if ( $is_fav_seller_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				// New page title.
				$title = esc_html__( 'My Favourite Seller', 'wk-marketplace' );
				remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );
			}

			if ( $is_become_seller_endpoint && $wkmarketplace->wkmp_user_is_customer( $customer_id ) && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				// New page title.
				$title = esc_html__( 'Become a Seller', 'wk-marketplace' );
				remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );
			}

			return $title;
		}

		/**
		 * Callback method for display Customer favourite seller list.
		 *
		 * @return void
		 */
		public function wkmp_endpoint_content() {
			new Customer\WKMP_Customer_Favourite_Seller( get_current_user_id() );
		}

		/**
		 * Callback method for display Become a seller form.
		 *
		 * @return void
		 */
		public function wkmp_mp_become_seller_endpoint_content() {
			new Customer\WKMP_Customer_Become_Seller( get_current_user_id() );
		}

		/**
		 * Adding sold by cart item data.
		 *
		 * @param array $item_data Item data.
		 * @param array $cart_item_data Cart item data.
		 *
		 * @return array
		 */
		public function wkmp_add_sold_by_cart_data( $item_data, $cart_item_data ) {
			global $wkmarketplace;
			$prod_id = isset( $cart_item_data['product_id'] ) ? $cart_item_data['product_id'] : 0;
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

				$shop_link = sprintf( /* translators: %1$s: Shop link, %2$s: Shop Name, %3$s: Closing anchor.  */ esc_html__( '%1$s %2$s %3$s', 'wk-marketplace' ), '<a target="_blank" href="' . esc_url( $shop_url ) . '">', esc_html( $display_name ), '</a>' );

				if ( ! empty( $author_id ) ) {
					$item_data[] = array(
						'key'   => esc_html__( 'Sold By ', 'wk-marketplace' ),
						'value' => $shop_link,
					);
				}
			}

			return $item_data;
		}

		/**
		 * Setting dynamic sku on product single page.
		 *
		 * @throws \WC_Data_Exception Throwing exception.
		 */
		public function wkmp_add_seller_prefix_to_sku() {
			global $product;
			if ( $product instanceof \WC_Product ) {
				$author_id = get_post_field( 'post_author', $product->get_id() );
				$author    = get_user_by( 'ID', $author_id );

				if ( in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
					$dynamic_sku_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_dynamic_sku', true );
					$dynamic_sku_prefix  = get_user_meta( $author_id, '_wkmp_dynamic_sku_prefix', true );

					if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
						$product_sku = $product->get_sku();
						$prod_sku    = empty( $product_sku ) ? $product->get_id() : $product_sku;
						$product_sku = $dynamic_sku_prefix . $prod_sku;
						$product->set_sku( $product_sku );
					}
				}
			}
		}

		/**
		 * Resetting sku on product single page.
		 *
		 * @throws \WC_Data_Exception Throwing exception.
		 */
		public function wkmp_remove_seller_prefix_to_sku() {
			global $product;
			if ( $product instanceof \WC_Product ) {
				$author_id = get_post_field( 'post_author', $product->get_id() );
				$author    = get_user_by( 'ID', $author_id );

				if ( in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
					$dynamic_sku_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_dynamic_sku', true );
					$dynamic_sku_prefix  = get_user_meta( $author_id, '_wkmp_dynamic_sku_prefix', true );

					if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
						$product_sku = $product->get_sku();
						if ( ! empty( $product_sku ) && 0 === strpos( $product_sku, $dynamic_sku_prefix ) ) {
							$product_sku = str_replace( $dynamic_sku_prefix, '', $product_sku );
							$product->set_sku( $product_sku );
						}
					}
				}
			}
		}
	}
}
