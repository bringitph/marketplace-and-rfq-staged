<?php
/**
 * Admin template Functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Notification;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Notification_Templates_Handller' ) ) {
	/**
	 * Class WKMP_Notification_Templates_Handller.
	 *
	 * @package WkMarketplace\Templates\Admin\Notification
	 */
	class WKMP_Notification_Templates_Handller {
		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Notification
		 */
		private $db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Notification_Templates_Handller constructor.
		 */
		public function __construct() {
			$this->db_obj = new Common\WKMP_Seller_Notification();

			add_action( 'wkmp_notification_orders_content', array( $this, 'wkmp_notification_orders_content' ) );
			add_action( 'wkmp_notification_product_content', array( $this, 'wkmp_notification_product_content' ) );

			$this->wkmp_notification_templates();
		}

		/**
		 * Display notifications tabs
		 */
		public function wkmp_notification_templates() {
			$config_tabs = array(
				'orders'  => esc_html__( 'Orders', 'wk-marketplace' ),
				'product' => esc_html__( 'Product', 'wk-marketplace' ),
			);

			$config_tabs = apply_filters( 'wkmp_admin_notification_tabs', $config_tabs );

			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			$page_name   = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			$current_tab = empty( $current_tab ) ? 'orders' : $current_tab;

			$url = admin_url( 'admin.php?page=' . $page_name );
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Notifications', 'wk-marketplace' ); ?></h1>
				<nav class="nav-tab-wrapper wkmp-admin-seller-list-manage-nav">
					<?php foreach ( $config_tabs as $name => $lable ) { ?>
						<a href="<?php echo esc_url( $url ) . '&tab=' . esc_attr( $name ); ?>" class="nav-tab <?php echo ( $current_tab === $name ) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $lable ); ?></a>
					<?php } ?>
				</nav>
				<?php do_action( 'wkmp_notification_' . esc_attr( $current_tab ) . '_content' ); ?>
			</div>
			<?php
		}

		/**
		 * Call back method for orders content
		 */
		public function wkmp_notification_orders_content() {
			new WKMP_Notification_Orders( $this->db_obj );
		}

		/**
		 *  Call back methods for product content
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_notification_product_content() {
			$notifications = $this->db_obj->wkmp_get_notification_data( 'product', 'all' );
			$display       = array();
			foreach ( $notifications['data'] as $key => $value ) {

				$datetime1 = new \DateTime( gmdate( 'F j, Y', strtotime( $value['timestamp'] ) ) );
				$datetime2 = new \DateTime( 'now' );
				$interval  = $datetime1->diff( $datetime2 );

				if ( $value['context'] ) {
					$link    = '<a href="' . admin_url( 'post.php?post=' . $value['context'] . '&action=edit' ) . '" target="_blank"> #' . $value['context'] . ' </a>';
					$content = sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s  %2$s %3$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $link, $value['content'], $interval->days );
				} else {
					$content = sprintf( /* translators: %1$s: Content, %2%s: Days. */ esc_html__( ' %1$s %2$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $value['content'], $interval->days );
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
					<?php esc_html_e( 'No data Found', 'wk-marketplace' ); ?>
				<?php } ?>
			</ul>
			<?php
			echo wp_kses_post( $notifications['pagination'] );
		}
	}
}
