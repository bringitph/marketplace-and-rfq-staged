<?php
/**
 * Admin template Functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WKMP_Seller_Templates_Handller' ) ) {
	/**
	 * Admin Seller Handler class.
	 *
	 * Class WKMP_Seller_Templates_Handller
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Templates_Handller {
		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Templates_Handller constructor.
		 */
		public function __construct() {
			add_action( 'wkmp_seller_details_content', array( $this, 'wkmp_seller_details_content' ) );
			add_action( 'wkmp_seller_orders_content', array( $this, 'wkmp_seller_orders_content' ) );
			add_action( 'wkmp_seller_transactions_content', array( $this, 'wkmp_seller_transactions_content' ) );
			add_action( 'wkmp_seller_commission_content', array( $this, 'wkmp_seller_commission_content' ) );
			add_action( 'wkmp_seller_assign_category_content', array( $this, 'wkmp_seller_assign_category_content' ) );

			$this->wkmp_manage_sellers();
		}

		/**
		 * Manage seller tabs and seller list
		 *
		 * @return void
		 */
		public function wkmp_manage_sellers() {
			$action    = filter_input( INPUT_GET, 'tab-action', FILTER_SANITIZE_STRING );
			$seller_id = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );

			if ( 'manage' === $action && ! empty( $seller_id ) ) {
				$this->wkmp_display_seller_tabs();
			} elseif ( 'delete' === $action && ! empty( $seller_id ) && $seller_id > 1 ) {
				$obj = new Admin\WKMP_Seller_Data();
				$obj->wkmp_delete_seller( $seller_id );

				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$url       = 'admin.php?page=' . $page_name . '&success=1';

				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			} else {
				$this->wkmp_display_seller_list();
			}
		}

		/**
		 * Display seller manage tabs
		 *
		 * @return void
		 */
		private function wkmp_display_seller_tabs() {
			global $wkmarketplace;
			$seller_id   = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			$seller_info = $wkmarketplace->wkmp_get_seller_info( $seller_id );

			$config_tabs = array(
				'details'         => esc_html__( 'Details', 'wk-marketplace' ),
				'orders'          => esc_html__( 'Orders', 'wk-marketplace' ),
				'transactions'    => esc_html__( 'Transactions', 'wk-marketplace' ),
				'commission'      => esc_html__( 'Commission', 'wk-marketplace' ),
				'assign_category' => esc_html__( 'Misc. Settings', 'wk-marketplace' ),
			);

			$config_tabs = apply_filters( 'wkmp_admin_seller_tabs', $config_tabs );

			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			$current_tab = empty( $current_tab ) ? 'details' : $current_tab;
			$page_name   = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action      = filter_input( INPUT_GET, 'tab-action', FILTER_SANITIZE_STRING );

			$url = admin_url( 'admin.php?page=' . $page_name . '&tab-action=' . $action . '&seller-id=' . $seller_id );
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Seller - ', 'wk-marketplace' ); ?>
					<?php
					echo isset( $seller_info->first_name ) ? esc_html( $seller_info->first_name ) : '';
					echo isset( $seller_info->last_name ) ? ' ' . esc_html( $seller_info->last_name ) : '';
					?>
				</h1>
				<nav class="nav-tab-wrapper wkmp-admin-seller-list-manage-nav">
					<?php foreach ( $config_tabs as $name => $lable ) { ?>
						<a href="<?php echo esc_url( $url ) . '&tab=' . esc_attr( $name ); ?>" class="nav-tab <?php echo ( $current_tab === $name ) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $lable ); ?></a>
					<?php } ?>
				</nav>
				<?php do_action( 'wkmp_seller_' . esc_attr( $current_tab ) . '_content' ); ?>
			</div>
			<?php
		}

		/**
		 * Display Seller list
		 *
		 * @return void
		 */
		private function wkmp_display_seller_list() {
			$obj       = new WKMP_Admin_Seller_List();
			$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$success   = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_NUMBER_INT );
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Sellers', 'wk-marketplace' ); ?></h1>
				<p class="description"><?php esc_html_e( 'List of Shop Vendors associated with this Marketplace.', 'wk-marketplace' ); ?></p>

				<?php
				if ( ! is_null( $success ) ) {
					$message      = ( $success && $success > 1 ) ? esc_html__( 'Kindly approve the seller first.', 'wk-marketplace' ) : esc_html__( 'Please select atleast one Seller.', 'wk-marketplace' );
					$notice_class = 'notice-error';
					if ( $success && 1 === intval( $success ) ) {
						$message      = esc_html__( 'Seller deleted successfully.', 'wk-marketplace' );
						$notice_class = 'notice-success';
					}
					?>
					<div class="notice my-acf-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
						<p><?php echo esc_html( $message ); ?></p>
					</div>
					<?php
				}
				?>
				<form method="GET">
					<input type="hidden" name="page" value="<?php echo isset( $page_name ) ? esc_attr( $page_name ) : ''; ?>"/>
					<?php
					$obj->prepare_items();
					$obj->search_box( esc_html__( 'Search Seller', 'wk-marketplace' ), 'search-id' );
					$obj->display();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Display seller details in manage section
		 *
		 * @return void
		 */
		public function wkmp_seller_details_content() {
			$seller_id = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			new WKMP_Seller_Profile( $seller_id );
		}

		/**
		 * Display seller orders in manage section
		 *
		 * @return void
		 */
		public function wkmp_seller_orders_content() {
			$obj_orders = new WKMP_Seller_Order_List();

			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			$current_tab = empty( $current_tab ) ? 'details' : $current_tab;
			$page_name   = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action      = filter_input( INPUT_GET, 'tab-action', FILTER_SANITIZE_STRING );
			$seller_id   = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			$success     = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_NUMBER_INT );

			if ( ! is_null( $success ) ) {
				$message      = esc_html__( 'Please select atleast one order.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( $success ) {
					$message      = esc_html__( 'Order status for selected orders has been successfully updated.', 'wk-marketplace' );
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice my-acf-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
			}
			?>
			<div class="notice my-acf-notice is-dismissible notice-success wkmp-hide"></div>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
				<input type="hidden" name="tab-action" value="<?php echo esc_attr( $action ); ?>"/>
				<input type="hidden" name="seller-id" value="<?php echo esc_attr( $seller_id ); ?>"/>
				<input type="hidden" name="tab" value="<?php echo esc_attr( $current_tab ); ?>"/>
				<?php
				$obj_orders->prepare_items();
				$obj_orders->display();
				?>
			</form>
			<?php
		}

		/**
		 * Display seller transactions in manage section
		 *
		 * @return void
		 */
		public function wkmp_seller_transactions_content() {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			$page_name   = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action      = filter_input( INPUT_GET, 'tab-action', FILTER_SANITIZE_STRING );
			$seller_id   = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			$id          = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );

			if ( 'transactions' === $current_tab && $id ) {
				new WKMP_Seller_Transaction_View( $id );
			} else {
				$obj_transaction = new WKMP_Seller_Transaction_List();
				?>
				<form method="get">

					<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
					<input type="hidden" name="tab-action" value="<?php echo esc_attr( $action ); ?>"/>
					<input type="hidden" name="seller-id" value="<?php echo esc_attr( $seller_id ); ?>"/>
					<input type="hidden" name="tab" value="<?php echo esc_attr( $current_tab ); ?>"/>
					<?php
					$obj_transaction->prepare_items();
					$obj_transaction->display();
					?>
				</form>
				<?php
			}
		}

		/**
		 * Display seller commissions in manage section
		 *
		 * @return void
		 */
		public function wkmp_seller_commission_content() {
			$seller_id = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			new WKMP_Seller_Commission( $seller_id );
		}

		/**
		 * Display seller assigned category in manage section
		 *
		 * @return void
		 */
		public function wkmp_seller_assign_category_content() {
			$seller_id = filter_input( INPUT_GET, 'seller-id', FILTER_SANITIZE_NUMBER_INT );
			new WKMP_Seller_Assign_Category( $seller_id );
		}
	}
}
