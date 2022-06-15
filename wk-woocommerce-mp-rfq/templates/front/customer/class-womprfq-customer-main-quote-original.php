<?php
/**
 * This file handles templates.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Templates\Front\Customer;

use wooMarketplaceRFQ\Helper;
use wooMarketplaceRFQ\Templates\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Customer_Main_Quote' ) ) {
	/**
	 * Load hooks.
	 */
	class Womprfq_Customer_Main_Quote {

		public $helper;
		public $q_id;

		/**
		 * Class constructor.
		 */
		public function __construct( $q_id ) {
			$this->q_id   = $q_id;
			$this->helper = new Helper\Womprfq_Quote_Handler();
		}

		/**
		 * Customer template handler
		 */
		public function womprfq_get_customer_main_quote_template_handler() {
			$post_data = $_REQUEST;

			if ( isset( $post_data['tab'] ) && ! empty( $post_data['tab'] ) ) {
				$tab = $post_data['tab'];
			} else {
				$tab = 'open';
			}
			if ( $this->q_id ) {
				$main_quote_info = $this->helper->womprfq_get_main_quotation_by_id( $this->q_id );
				if ( $main_quote_info->customer_id == get_current_user_id() ) {
					$temp_obj = new Front\Womprfq_Front_Templates();

					?>
					<div class="wk-mp-rfq-header">
						<h2>
							<?php echo ucfirst( esc_html__( 'Main Quotation Details', 'wk-mp-rfq' ) ); ?>
						</h2>
					</div>
					<div id="main_container" class="wk_transaction woocommerce-MyAccount-content wk-mp-rfq" style="display: contents;">
					<?php
					$temp_obj->womprfq_get_main_quote_template( $main_quote_info );
					$this->womprfq_get_quote_tab_template( $tab );
					$this->womprfq_get_quote_data_template( $tab );
					?>
					</div>
					<?php
				}
			} else {
				// redirect
			}
		}

		public function womprfq_get_quote_tab_template( $tab ) {
			$tabs = array(
				'open'     => esc_html__( 'Open', 'wk-mp-rfq' ),
				'pending'  => esc_html__( 'Pending', 'wk-mp-rfq' ),
				'answered' => esc_html__( 'Answered', 'wk-mp-rfq' ),
				'resolved' => esc_html__( 'Resolved', 'wk-mp-rfq' ),
				'closed'   => esc_html__( 'Closed', 'wk-mp-rfq' ),
			);
			$tabs = apply_filters( 'womprfq_add_status_tab', $tabs );
			?>
			<nav class="nav-tab-wrapper woo-nav-tab-wrapper">

			<?php

			foreach ( $tabs as $slug => $value ) {
				$ac = '';
				if ( $tab == $slug ) {
					$ac = 'nav-tab-active';
				}
				$editurl = wc_get_page_permalink( 'myaccount' ) . 'main-quote/' . intval( $this->q_id ) . '/?tab=' . esc_html( $slug );
				?>
				<a href="<?php echo esc_url( $editurl ); ?>" class="nav-tab <?php echo esc_attr( $ac ); ?> ">
					<?php echo esc_html( $value ); ?>
				</a>
				<?php
			}
			?>
			</nav>
			<?php
		}

		public function womprfq_get_quote_data_template( $tab ) {

			$data     = array(
				'tab'  => $tab,
				'data' => array(),
			);
			$tab_data = array();

			$tab_data = $this->helper->womprfq_get_seller_quotation_for_cust( $this->q_id, $tab );

			if ( ! empty( $tab_data ) ) {
				$data['data'] = $tab_data;
			}
			$table = $this->womprfq_list_seller_quote_template( $data, $tab );
		}

		/**
		 * List Quotations done by customers
		 *
		 * @param array
		 */
		public function womprfq_list_seller_quote_template( $sel_quote_data, $tab ) {

			 $tabs = array(
				 'open'     => esc_html__( 'Open', 'wk-mp-rfq' ),
				 'pending'  => esc_html__( 'Pending', 'wk-mp-rfq' ),
				 'answered' => esc_html__( 'Answered', 'wk-mp-rfq' ),
				 'resolved' => esc_html__( 'Resolved', 'wk-mp-rfq' ),
				 'closed'   => esc_html__( 'Closed', 'wk-mp-rfq' ),
			 );
				?>
			<div class="quotation-list-wrapper">
				<h2>
					<?php echo esc_html( $tabs[ $tab ] ) . ' ' . esc_html__( 'Quotations List', 'wk-mp-rfq' ); ?>
				</h2>
				<?php
				$quote_columns = array(
					'quote-id'      => esc_html__( 'Quotation Id', 'wk-mp-rfq' ),
					'seller-id'     => esc_html__( 'Seller', 'wk-mp-rfq' ),
					'created-on'    => esc_html__( 'Created On', 'wk-mp-rfq' ),
					'quote-actions' => esc_html__( 'Actions', 'wk-mp-rfq' ),
				);

				$paged = ! empty( get_query_var( 'pre-order' ) ) ? get_query_var( 'pre-order' ) : 1;

				if ( ! is_numeric( get_query_var( 'pre-order' ) ) ) {
					$paged = 1;
				}

				$pagenum = isset( $paged ) ? absint( $paged ) : 1;
				$limit   = 10;
				$offset  = ( $pagenum == 1 ) ? 0 : ( $pagenum - 1 ) * $limit;
				$user_id = get_current_user_ID();

				?>
			<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
				<thead>
					<tr>
					<?php foreach ( $quote_columns as $column_id => $column_name ) : ?>
					  <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
					<?php endforeach; ?>
					</tr>
				</thead>

				<tbody>
				<?php
				$meta = $this->helper->womprfq_get_quote_meta_info( $this->q_id );
				if ( isset( $meta['quote_product_id'] ) && ! empty( $meta['quote_product_id'] ) ) {
					$auth = intval( get_post_field( 'post_author', intval( $meta['quote_product_id'] ) ) );
				} else {
					$auth = 0;
				}
				if ( ! empty( $sel_quote_data['data'] ) ) {
					foreach ( $sel_quote_data['data'] as $data_quote ) :
						$ac_class = '';
						$seller   = 'N/A';
						if ( $data_quote->seller_id ) {
							$user = get_user_by( 'ID', $data_quote->seller_id );
							if ( $user ) {
								$seller = $user->user_email;
							}
						}
						if ( $auth == intval( $data_quote->seller_id ) ) {
							$ac_class = 'wk_accepted_quote';
						}
						$data = array(
							'id'     => $data_quote->id,
							'seller' => $seller,
							'date'   => $data_quote->date,
						);
						?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-quote-id" data-title="<?php esc_attr_e( 'Quotation Id', 'wk-mp-rfq' ); ?>">
							<span class="<?php echo esc_attr( $ac_class ); ?>"></span>
							<?php echo esc_html( '#' . intval( $data['id'] ) ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-quote-product" data-title="<?php esc_attr_e( 'Product Name', 'wk-mp-rfq' ); ?>">
							<?php echo esc_html( ( $data['seller'] ) ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-created-on" data-title="<?php esc_attr_e( 'Created On', 'wk-mp-rfq' ); ?>">
							<?php echo esc_html( $data['date'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-quote-actions" data-title="<?php esc_attr_e( 'Actions', 'wk-mp-rfq' ); ?>">
							<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) . 'seller-quote/' . intval( $data['id'] ) ); ?>"><?php esc_html_e( 'View', 'wk-mp-rfq' ); ?></a>
						</td>
					</tr>
						<?php
					endforeach;
				} else {
					?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
						<td colspan="4" class="wkmp-nodata-td" id="wkmp-nodata-td" data-title="<?php esc_attr_e( 'Quotation Id', 'wk-mp-rfq' ); ?>">
							<?php echo esc_html__( 'No Data Found', 'wk-mp-rfq' ); ?>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
			<?php
		}
	}
}
