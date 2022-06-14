<?php
/**
 * Marketplace email class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Widget_Seller_Panel' ) ) {
	/**
	 * Class WKMP_Widget_Seller_Panel
	 */
	class WKMP_Widget_Seller_Panel extends WP_Widget {
		/**
		 * WKMP_Widget_Seller_Panel constructor.
		 */
		public function __construct() {
			parent::__construct(
				'mp_marketplace-widget',
				esc_html__( 'Display seller panel.', 'wk-marketplace' ),
				array(
					'classname'   => 'mp_marketplace',
					'description' => esc_html__( 'Marketplace Seller Panel.', 'wk-marketplace' ),
				) 
			);
		}

		/**
		 * Widget data.
		 *
		 * @param array $args args.
		 * @param array $instance instance.
		 */
		public function widget( $args, $instance ) {
			global $wkmarketplace, $wpdb;

			$user_id      = get_current_user_id();
			$shop_address = get_user_meta( $user_id, 'shop_address', true );
			$page_name    = $wkmarketplace->seller_page_slug;

			$seller_info = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}mpsellerinfo WHERE user_id = %d and seller_value='seller'", $user_id ) );

			if ( $seller_info > 0 ) {
				do_action( 'chat_with_me' );
				echo '<div class="wkmp_seller"><h2>' . esc_html( $wkmarketplace->seller_page_slug ) . '</h2>';
				echo '<ul class="wkmp_sellermenu">';

				echo '<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . get_option( '_wkmp_profile_endpoint', 'profile' ) ) ) . '">';
				esc_html_e( 'My Profile', 'wk-marketplace' );
				echo '</a></li>
  							 <li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . get_option( '_wkmp_add_product_endpoint', 'add-product' ) ) ) . '">';
				esc_html_e( 'Add Product', 'wk-marketplace' );
				echo '</a></li>
  							<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . get_option( '_wkmp_product_list_endpoint', 'product-list' ) ) ) . '">';
				esc_html_e( 'Product List', 'wk-marketplace' );
				echo '</a></li>
  							<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . get_option( '_wkmp_order_history_endpoint', 'order-history' ) ) ) . '">';
				esc_html_e( 'Order History', 'wk-marketplace' );
				echo '</a></li>
  							<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . $shop_address . '/' . get_option( '_wkmp_shipping_endpoint', 'shipping' ) ) ) . '">';
				esc_html_e( 'Manage Shipping', 'wk-marketplace' );
				echo '</a></li>';

				do_action( 'marketplace_list_seller_option', $page_name );

				echo '<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . get_option( '_wkmp_shop_follower_endpoint', 'shop-follower' ) ) ) . '">';
				esc_html_e( 'Shop Follower', 'wk-marketplace' );
				echo '</a></li>
  							<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . get_option( '_wkmp_dashboard_endpoint', 'dashboard' ) ) ) . '">';
				esc_html_e( 'Dashboard', 'wk-marketplace' );
				echo '</a></li>';
				echo '<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $page_name . '/' . get_option( '_wkmp_asktoadmin_endpoint', 'asktoadmin' ) ) ) . '">';
				esc_html_e( 'Ask To Admin', 'wk-marketplace' );
				echo '</a></li></ul></div>';
			}

			if ( $user_id > 0 && $seller_info < 1 ) {
				echo '<div class="wkmp_seller"><h2>' . esc_html__( 'Buyer Menu', 'wk-marketplace' ) . '</h2>';
				echo '<ul class="wkmp_sellermenu">';
				echo '<li class="wkmp-selleritem"><a href="' . esc_url( home_url( '/' . $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_profile_endpoint', 'profile' ) . '' ) ) . '">';
				esc_html_e( 'My Profile', 'wk-marketplace' );
				echo '</a></li></ul></div>';
			}
		}
	}
}
