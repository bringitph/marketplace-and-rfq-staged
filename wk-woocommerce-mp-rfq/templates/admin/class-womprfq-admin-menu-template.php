<?php
/**
 * Load ajax functions.
 *
 * @author     Webkul.
 * @implements Assets_Interface
 */

namespace wooMarketplaceRFQ\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use wooMarketplaceRFQ\Templates\Admin\Menu;
use wooMarketplaceRFQ\Helper;
use DateTime;

if ( ! class_exists( 'Womprfq_Admin_Menu_Template' ) ) {
	/**
	 * Class menu template.
	 */
	class Womprfq_Admin_Menu_Template {

		/**
		 * Display quotation template.
		 *
		 * @return void.
		 */
		public function womprfq_get_quotations_list() {
			$postdta      = $_GET;
			$this->helper = new Helper\Womprfq_Quote_Handler();
			if ( isset( $postdta['perform'] ) && sanitize_key( $postdta['perform'] ) == 'edit-seller-quote' && isset( $postdta['sqid'] ) && ! empty( $postdta['sqid'] ) ) {
				new Menu\Quote\Womprfq_Seller_Quotation_Edit( intval( $postdta['sqid'] ) );
			} elseif ( isset( $postdta['perform'] ) && sanitize_key( $postdta['perform'] ) == 'seller-quote' && isset( $postdta['qid'] ) && ! empty( $postdta['qid'] ) ) {
				$main_quotation_info  = $this->helper->womprfq_get_main_quotation_by_id( intval( $postdta['qid'] ) );
				$wk_seller_quote_list = new Menu\Quote\Womprfq_Seller_Quotation_List( intval( $postdta['qid'] ), $main_quotation_info );
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline">
						<?php esc_html_e( 'Seller Quotation Lists', 'wk-mp-rfq' ); ?>
					</h1>
					<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=wk-mp-rfq' ) ); ?>">
						<?php esc_html_e( 'Back', 'wk-mp-rfq' ); ?>
					</a>
					<div>
						<?php
							$this->womprfq_get_main_quote_template( $main_quotation_info );
						?>
					</div>
					<br>
					<br>
					<form method="get">
						<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
						<input type="hidden" name="perform" value="<?php echo esc_attr( $_REQUEST['perform'] ); ?>" />
						<input type="hidden" name="qid" value="<?php echo esc_attr( $_REQUEST['qid'] ); ?>" />
						<?php
							$wk_seller_quote_list->prepare_items();
							$wk_seller_quote_list->search_box( esc_html__( 'Search by Seller Name', 'wk-mp-rfq' ), 'search-box-id' );
							$wk_seller_quote_list->display();
						?>
					</form>
				</div>
				<?php
			} else {

				$wk_main_quote_list = new Menu\Quote\Womprfq_Main_Quotation_List();
				?>
				<div class="wrap">
				<h1 class="wp-heading-inline">
					<?php esc_html_e( 'Main Quotation Lists', 'wk-mp-rfq' ); ?>
				</h1>
				<form method="get">
					<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
					<?php
						$wk_main_quote_list->prepare_items();
						$wk_main_quote_list->search_box( esc_html__( 'Search by Quote Id', 'wk-mp-rfq' ), 'search-box-id' );
						$wk_main_quote_list->display();
					?>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Display quotation template.
		 *
		 * @return void.
		 */
		public function womprfq_get_attribute_list() {
			$postdta = $_GET;

			if ( isset( $postdta['perform'] ) && sanitize_key( $postdta['perform'] ) == 'manage-attr' ) {

				if ( isset( $postdta['aid'] ) ) {
					$data   = intval( $postdta['aid'] );
					$action = 'update';
				} else {
					$data   = '';
					$action = 'add';
				}

				new Menu\Attribute\Womprfq_Manage_Attribute( $action, $data );

			} else {

				$wk_attribute_list = new Menu\Attribute\Womprfq_Attribute_List();
				?>
				<div class="wrap">
				<h1 class="wp-heading-inline">
					<?php esc_html_e( 'Attribute List', 'wk-mp-rfq' ); ?>
				</h1>
				<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-mp-rfq-attributes&perform=manage-attr' ) ); ?>">
					<?php esc_html_e( 'Add Attribute', 'wk-mp-rfq' ); ?>
				</a>
				<form method="get">
					<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
					<?php
						$wk_attribute_list->prepare_items();
						$wk_attribute_list->search_box( esc_html__( 'Search by Attribute Id', 'wk-mp-rfq' ), 'search-box-id' );
						$wk_attribute_list->display();
					?>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Returns main quote data template
		 *
		 * @param object $data main quotation onject
		 *
		 * @return void
		 */
		public function womprfq_get_main_quote_template( $data ) {
			$helper  = new Helper\Womprfq_Quote_Handler();
			$quote_d = $helper->womprfq_get_quote_meta_info( $data->id );
			if ( $data->variation_id != 0 ) {
				$product = get_the_title( $data->variation_id ) . ' ( #' . intval( $data->variation_id ) . ' )';
			} elseif ( $data->variation_id == 0 && $data->product_id != 0 ) {
				$product = get_the_title( $data->product_id ) . ' ( #' . intval( $data->product_id ) . ' )';
			} else {
				if ( isset( $quote_d['pro_name'] ) ) {
					$product = $quote_d['pro_name'];
				}
			}
			?>
			<table class="wc_status_table widefat" cellspacing="0">
				<thead>
					<tr>
						<td colspan="4">
							<h2><?php esc_html_e( 'MAIN QUOTATION INFORMATION', 'wk-mp-rfq' ); ?></h2>
						</td>
					</tr>
					<tr>
						<th>
							<h2><?php esc_html_e( 'Quotation ID', 'wk-mp-rfq' ); ?> </h2>
						</th>
						<th>
							<h2><?php esc_html_e( 'Quotation Total Quantity', 'wk-mp-rfq' ); ?> </h2>
						</th>
						<th>
							<h2><?php esc_html_e( 'Quotation Product Name', 'wk-mp-rfq' ); ?> </h2>
						</th>
						<th>
							<h2><?php esc_html_e( 'Created On', 'wk-mp-rfq' ); ?> </h2>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td ><?php echo esc_html( '#' . intval( $data->id ) ); ?></td>
						<td ><?php echo esc_html( $data->quantity ); ?></td>
						<td>
						<p>
							<?php echo esc_html( $product ); ?>
						</p>
						</td>
						<td>
						<?php

						$date = new DateTime( $data->date );
						echo esc_html( $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) );
						?>
						 </td>
					</tr>
					<?php
					$admin_attr = $helper->womprfq_get_quote_attribute_data( $data->id );
					if ( ! empty( $admin_attr ) ) {
						?>
						<tr>
							<td colspan="4">
								<b>
									<u>
										<?php esc_html_e( 'ATTRIBUTE INFO', 'wk-mp-rfq' ); ?>
									</u>
								</b>
							</td>
						</tr>
						<?php
						foreach ( $admin_attr as $key => $value ) {
							?>
							<tr>
								<td >
									<strong>
										<?php echo esc_html( ucfirst( $key ) ); ?>
									</strong>
								</td>
								<td>
									<?php
										echo esc_html( $value );
									?>
								</td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
			<?php
		}
	}
}
