<?php
/**
 * Admin template Functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Notification;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Notification_Orders' ) ) {
	/**
	 * Admin seller profile templates class.
	 *
	 * Class WKMP_Notification_Orders
	 *
	 * @package WkMarketplace\Templates\Admin\Notification
	 */
	class WKMP_Notification_Orders {
		/**
		 * DB Object.
		 *
		 * @var Object $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Notification_Orders constructor.
		 *
		 * @param Object $db_object DB Object.
		 */
		public function __construct( $db_object = null ) {
			$this->db_obj = $db_object;
			add_action( 'wkmp_orders_all_content', array( $this, 'wkmp_orders_all_content' ), 10 );
			add_action( 'wkmp_orders_processing_content', array( $this, 'wkmp_orders_processing_content' ), 10 );
			add_action( 'wkmp_orders_completed_content', array( $this, 'wkmp_orders_completed_content' ), 10 );

			$this->wkmp_display_orders_notification_tabs();
		}

		/**
		 * Display notification tabs.
		 */
		public function wkmp_display_orders_notification_tabs() {
			$config_tabs = array(
				'all'        => esc_html__( 'All', 'wk-marketplace' ),
				'processing' => esc_html__( 'Processing', 'wk-marketplace' ),
				'completed'  => esc_html__( 'Completed', 'wk-marketplace' ),
			);

			$config_tabs = apply_filters( 'wkmp_notification_orders_tabs', $config_tabs );

			$current_section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
			$page_name       = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			$tab             = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			$current_section = empty( $current_section ) ? 'all' : $current_section;
			$tab             = empty( $tab ) ? 'orders' : $tab;

			$url = admin_url( 'admin.php?page=' . $page_name . '&tab=' . $tab );
			?>
			<ul class="subsubsub">
				<?php foreach ( $config_tabs as $name => $lable ) { ?>
					<li>
						<a href="<?php echo esc_url( $url ) . '&section=' . esc_attr( $name ); ?>" class=" <?php echo ( $current_section === $name ) ? 'current' : ''; ?>"><?php echo esc_html( $lable ); ?></a>
						|
					</li>
				<?php } ?>
			</ul>
			<br class="clear">
			<?php
			do_action( 'wkmp_orders_' . esc_attr( $current_section ) . '_content' );
		}

		/**
		 * All content.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_orders_all_content() {
			$notifications = $this->db_obj->wkmp_get_notification_data( 'order', 'all' );
			$this->wkmp_display_notification( $notifications );
		}

		/**
		 * Order processing content.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_orders_processing_content() {
			$notifications = $this->db_obj->wkmp_get_notification_data( 'order', 'processing' );
			$this->wkmp_display_notification( $notifications );
		}

		/**
		 * Completed content.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_orders_completed_content() {
			$notifications = $this->db_obj->wkmp_get_notification_data( 'order', 'complete' );
			$this->wkmp_display_notification( $notifications );
		}

		/**
		 * Display notification.
		 *
		 * @param string $notifications Notification.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_display_notification( $notifications ) {
			global $wkmarketplace;
			$display = array();

			foreach ( $notifications['data'] as $value ) {
				$datetime1 = new \DateTime( gmdate( 'F j, Y', strtotime( $value['timestamp'] ) ) );
				$datetime2 = new \DateTime( 'now' );

				$interval = $datetime1->diff( $datetime2 );

				if ( $value['context'] ) {
					if ( $wkmarketplace->wkmp_user_is_seller( get_current_user_id() ) ) {
						$url = admin_url( 'admin.php?page=order-history&action=view&oid=' . $value['context'] );
					} else {
						$url = admin_url( 'post.php?post=' . $value['context'] . '&action=edit' );
					}

					$link = '<a href="' . $url . '" target="_blank"> #' . $value['context'] . ' </a>';

					$content = sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s  %2$s %3$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $link, $value['content'], $interval->days );
				} else {
					$content = sprintf( /* translators: %1$s: Content, %2%s: Days.  */ esc_html__( ' %1$s %2$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $value['content'], $interval->days );
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
