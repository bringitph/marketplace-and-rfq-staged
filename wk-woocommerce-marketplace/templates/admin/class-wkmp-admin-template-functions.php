<?php
/**
 * Admin template Functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Templates\Admin\Seller;
use WkMarketplace\Templates\Admin\Product;
use WkMarketplace\Templates\Admin\Notification;
use WkMarketplace\Templates\Admin\Feedback;
use WkMarketplace\Templates\Admin\Queries;
use WkMarketplace\Templates\Admin\Settings;
use WkMarketplace\Templates\Admin\Extension;

use WkMarketplace\Helper as Form;

if ( ! class_exists( 'WKMP_Admin_Template_Functions' ) ) {

	/**
	 * Admin template class
	 */
	class WKMP_Admin_Template_Functions {

		/**
		 * Form field builder
		 *
		 * @var object
		 */
		protected $form_helper;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Template_Functions constructor.
		 */
		public function __construct() {
			$this->form_helper = new Form\WKMP_Form_Field_Builder();
		}

		/**
		 * Marketplace Sellers
		 *
		 * @return void
		 */
		public function wkmp_marketplace_sellers() {
			new Seller\WKMP_Seller_Templates_Handller();
		}

		/**
		 * Notification callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_products() {
			$obj_product = new Product\WKMP_Admin_Product();
			$page_name   = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$success     = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_NUMBER_INT );
			?>
			<h1><?php esc_html_e( 'Product List', 'wk-marketplace' ); ?></h1>

			<?php
			if ( ! is_null( $success ) ) {
				$message      = esc_html__( 'Please select atleast one product.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( $success ) {
					$message      = ( $success > 1 ) ? esc_html__( 'Product assigned successfully.', 'wk-marketplace' ) : esc_html__( 'Product trashed successfully. You can restore them from woocommerce product page.', 'wk-marketplace' );
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice my-acf-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
			}
			?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
				<?php
				$obj_product->prepare_items();
				$obj_product->search_box( esc_html__( 'Search Products', 'wk-marketplace' ), 'search-box-id' );
				$obj_product->display();
				?>
			</form>
			<?php
		}

		/**
		 * Notification callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_notifications() {
			new Notification\WKMP_Notification_Templates_Handller();
		}

		/**
		 * Reviews & rating callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_feedback() {
			$obj_feedback = new Feedback\WKMP_Admin_Feedback();
			$page_name    = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$success      = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_NUMBER_INT );
			?>
			<h1><?php esc_html_e( 'Manage Feedback', 'wk-marketplace' ); ?></h1>
			<?php
			if ( ! is_null( $success ) ) {
				$message      = esc_html__( 'Please select atleast one feedback.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( $success ) {
					$message      = ( $success > 1 ) ? esc_html__( 'Feedback has been disapproved successfully.', 'wk-marketplace' ) : esc_html__( 'Feedback has been approved successfully.', 'wk-marketplace' );
					$message      = ( $success > 2 ) ? esc_html__( 'Feedback has been deleted successfully.', 'wk-marketplace' ) : $message;
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice my-acf-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
			}
			?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
				<?php
				$obj_feedback->prepare_items();
				$obj_feedback->search_box( esc_html__( 'Search By summary', 'wk-marketplace' ), 'search-box-id' );
				$obj_feedback->display();
				?>
			</form>
			<?php
		}

		/**
		 * Queries callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_queries() {
			$obj_queries = new Queries\WKMP_Admin_Queries();
			$page_name   = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$success     = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_NUMBER_INT );
			?>
			<h1><?php esc_html_e( 'Queries List', 'wk-marketplace' ); ?></h1>
			<?php
			if ( ! is_null( $success ) ) {
				$message      = esc_html__( 'Please select atleast one query.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( $success ) {
					$message      = esc_html__( 'Seller queries has been deleted successfully.', 'wk-marketplace' );
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice my-acf-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
			}
			?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
				<?php
				$obj_queries->prepare_items();
				$obj_queries->search_box( esc_html__( 'Search By Subject', 'wk-marketplace' ), 'search-box-id' );
				$obj_queries->display();
				?>
			</form>
			<?php
		}

		/**
		 * Marketplace settings callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_settings() {
			new Settings\WKMP_Setting_Templates_Handller();
		}

		/**
		 * Marketplace extensions callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_extensions() {
			new Extension\WKMP_Extensions();
		}
	}
}
