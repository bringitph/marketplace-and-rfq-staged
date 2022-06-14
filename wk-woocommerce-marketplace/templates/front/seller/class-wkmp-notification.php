<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Notification' ) ) {
	/**
	 * Seller notifications.
	 *
	 * Class WKMP_Notification
	 *
	 * @package WkMarketplace\Templates\Front\Seller
	 */
	class WKMP_Notification {
		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Notification $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Notification constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->seller_id = $seller_id;
			$this->db_obj    = new Common\WKMP_Seller_Notification();
			$this->wkmp_display_notifications();
		}

		/**
		 * Display seller notification
		 *
		 * @return void
		 */
		public function wkmp_display_notifications() {
			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'mp_get_wc_account_menu' ); ?>
				<div id="main_container" class="woocommerce-MyAccount-content">

					<ul class="wkmp_nav_tabs">
						<li><a data-id="#wkmp-orders-tab" class="active"><?php esc_html_e( 'Orders', 'wk-marketplace' ); ?></a></li>
						<li><a data-id="#wkmp-product-tab"><?php esc_html_e( 'Product', 'wk-marketplace' ); ?></a></li>
						<li><a data-id="#wkmp-seller-tab"><?php esc_html_e( 'Seller', 'wk-marketplace' ); ?></a></li>
					</ul>

					<div class="wkmp_tab_content">
						<div id="wkmp-orders-tab" class="wkmp_tab_pane">
							<?php $this->wkmp_display_orders_notification(); ?>
						</div>

						<div id="wkmp-product-tab" class="wkmp_tab_pane">
							<?php $this->wkmp_display_product_notification(); ?>
						</div>

						<div id="wkmp-seller-tab" class="wkmp_tab_pane">
							<?php $this->wkmp_display_seller_notification(); ?>
						</div>
					</div><!-- Tab content end here -->

				</div>
			</div>
			<?php
		}

		/**
		 * Display seller order notification
		 *
		 * @return void
		 */
		private function wkmp_display_orders_notification() {
			$paged = ( ! empty( get_query_var( 'pagenum' ) ) && 'orders' === get_query_var( 'info' ) ) ? get_query_var( 'pagenum' ) : 1;

			if ( ! is_numeric( get_query_var( 'pagenum' ) ) ) {
				$paged = 1;
			}

			$page_num = isset( $paged ) ? absint( $paged ) : 1;
			$limit    = 10;
			$offset   = ( 1 === $page_num ) ? 0 : ( $page_num - 1 ) * $limit;

			$total_count    = $this->db_obj->wkmp_get_seller_notification_count( 'order' );
			$orders         = array();
			$orders['data'] = $this->db_obj->wkmp_get_seller_notification_data( 'order', $offset, $limit );

			$this->wkmp_display_notification_html( $orders, 'order' );

			$pagination = array(
				'total_count' => $total_count,
				'page'        => $paged,
				'previous'    => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'notification' ) . '/orders/page', $paged - 1 ),
				'next'        => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'notification' ) . '/orders/page', $paged + 1 ),
			);

			$this->wkmp_display_pagination( $pagination );
		}

		/**
		 * Display seller product notification
		 *
		 * @return void
		 */
		private function wkmp_display_product_notification() {
			$paged = ( ! empty( get_query_var( 'pagenum' ) ) && 'products' === get_query_var( 'info' ) ) ? get_query_var( 'pagenum' ) : 1;

			if ( ! is_numeric( get_query_var( 'pagenum' ) ) ) {
				$paged = 1;
			}

			$products = array();

			$page_num = isset( $paged ) ? absint( $paged ) : 1;
			$limit    = 10;
			$offset   = ( 1 === $page_num ) ? 0 : ( $page_num - 1 ) * $limit;

			$total_count      = $this->db_obj->wkmp_get_seller_notification_count( 'product' );
			$products['data'] = $this->db_obj->wkmp_get_seller_notification_data( 'product', $offset, $limit );

			$this->wkmp_display_notification_html( $products, 'product' );

			$pagination = array(
				'total_count' => $total_count,
				'page'        => $paged,
				'previous'    => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'notification' ) . '/products/page', $paged - 1 ),
				'next'        => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'notification' ) . '/products/page', $paged + 1 ),
			);

			$this->wkmp_display_pagination( $pagination );
		}

		/**
		 * Display seller notification data.
		 *
		 * @return void
		 */
		private function wkmp_display_seller_notification() {
			global $wkmarketplace;

			$paged = ( ! empty( get_query_var( 'pagenum' ) ) && 'seller' === get_query_var( 'info' ) ) ? get_query_var( 'pagenum' ) : 1;

			if ( ! is_numeric( get_query_var( 'pagenum' ) ) ) {
				$paged = 1;
			}

			$sellers = array();

			$page_num = isset( $paged ) ? absint( $paged ) : 1;
			$limit    = 10;
			$offset   = ( 1 === $page_num ) ? 0 : ( $page_num - 1 ) * $limit;

			$total_count     = $this->db_obj->wkmp_get_seller_notification_count( 'seller' );
			$sellers['data'] = $this->db_obj->wkmp_get_seller_notification_data( 'seller', $offset, $limit );

			$this->wkmp_display_notification_html( $sellers, 'seller' );

			$pagination = array(
				'total_count' => $total_count,
				'page'        => $paged,
				'previous'    => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'notification' ) . '/' . $wkmarketplace->seller_page_slug . '/page', $paged - 1 ),
				'next'        => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'notification' ) . '/' . $wkmarketplace->seller_page_slug . '/page', $paged + 1 ),
			);

			$this->wkmp_display_pagination( $pagination );
		}

		/**
		 * Display seller notification HTML.
		 *
		 * @param array  $notifications Notifications.
		 * @param string $action Actions.
		 *
		 * @throws \Exception Throwing exception.
		 */
		private function wkmp_display_notification_html( $notifications, $action ) {
			$display = array();
			foreach ( $notifications['data'] as $value ) {
				$context_id = isset( $value['context'] ) ? $value['context'] : 0;
				if ( 'product' === $action && empty( get_the_title( $context_id ) ) ) {
					continue;
				}

				if ( 'order' === $action && ( ! wc_get_order( $context_id ) instanceof \WC_Order ) ) {
					continue;
				}

				$datetime1 = new \DateTime( gmdate( 'F j, Y', strtotime( $value['timestamp'] ) ) );
				$datetime2 = new \DateTime( 'now' );
				$interval  = $datetime1->diff( $datetime2 );

				if ( 'order' === $action ) {
					$url     = get_permalink() . get_option( '_wkmp_order_history_endpoint', 'order-history' ) . '/' . $context_id;
					$link    = '<a href="' . esc_url( $url ) . '" target="_blank"> #' . esc_html( $context_id ) . ' </a>';
					$content = sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s  %2$s %3$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $link, $value['content'], $interval->days );
				}

				if ( 'product' === $action ) {
					$url     = get_permalink() . 'product/edit/' . $context_id;
					$link    = '<a href="' . esc_url( $url ) . '" target="_blank"> #' . esc_html( get_the_title( $context_id ) ) . ' </a>';
					$content = sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s  %2$s %3$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $link, $value['content'], $interval->days );
				}

				if ( 'seller' === $action ) {
					$user    = get_user_by( 'id', $context_id );
					$content = $value['content'];
					if ( $user && isset( $user->display_name ) ) {
						$link    = '<a href="javascript:void(0);" target="_blank">' . esc_html( $user->display_name ) . ' </a>';
						$content = sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s  %2$s %3$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $value['content'], $link, $interval->days );
					}
				}

				$display[] = array(
					'content' => $content,
				);

				$this->db_obj->wkmp_update_notification_read_status( $value );
			}
			?>
			<ul class="mp-notification-list">
				<?php if ( $display ) { ?>
					<?php foreach ( $display as $value ) { ?>
						<li class="notification-link"><?php echo html_entity_decode( $value['content'], ENT_QUOTES, 'UTF-8' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
					<?php } ?>
				<?php } else { ?>
					<?php esc_html_e( 'No data Found!', 'wk-marketplace' ); ?>
				<?php } ?>
			</ul>
			<?php
		}

		/**
		 * Display notification pagination
		 *
		 * @param array $data Data.
		 *
		 * @return void
		 */
		private function wkmp_display_pagination( $data ) {
			if ( 1 < $data['total_count'] ) {
				?>
				<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination wallet-pagination" style="margin-top:10px;">

					<?php if ( 1 !== $data['page'] && $data['page'] > 1 ) { ?>
						<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( $data['previous'] ); ?>"><?php esc_html_e( 'Previous', 'wk-marketplace' ); ?></a>
					<?php } ?>

					<?php if ( ceil( $data['total_count'] / 10 ) > $data['page'] ) { ?>
						<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $data['next'] ); ?>"><?php esc_html_e( 'Next', 'wk-marketplace' ); ?></a>
					<?php } ?>
				</div>
				<?php
			}
		}
	}
}
