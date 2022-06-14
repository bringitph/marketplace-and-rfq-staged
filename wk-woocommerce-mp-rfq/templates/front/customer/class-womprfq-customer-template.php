<?php
/**
 * This file handles templates.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Templates\Front\Customer;

use wooMarketplaceRFQ\Helper;
use wooMarketplaceRFQ\Templates\Front\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Customer_Template' ) ) {
	/**
	 * Load hooks.
	 */
	class Womprfq_Customer_Template {

		public $helper;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->helper = new Helper\Womprfq_Quote_Handler();
		}

		/**
		 * Customer template handler
		 */
		public function womprfq_get_customer_template_handler() {
			global $wp_query;
			if ( isset( $wp_query->query_vars['rfq'] ) ) {
				$this->womprfq_list_quote_template();
			} elseif ( isset( $wp_query->query_vars['main-quote'] ) ) {
				if ( intval( $wp_query->query_vars['main-quote'] ) > 0 ) {
					$qid = intval( $wp_query->query_vars['main-quote'] );
					$obj = new Customer\Womprfq_Customer_Main_Quote( $qid );
					$obj->womprfq_get_customer_main_quote_template_handler();
				}
			} elseif ( isset( $wp_query->query_vars['seller-quote'] ) ) {
				if ( intval( $wp_query->query_vars['seller-quote'] ) > 0 ) {
					$qid = intval( $wp_query->query_vars['seller-quote'] );
					$obj = new Customer\Womprfq_Customer_Seller_Quote_Edit( $qid );
					$obj->womprfq_get_customer_seller_quote_template_handler();
				}
			} elseif ( isset( $wp_query->query_vars['add-quote'] ) ) {
				$obj = new Customer\Womprfq_Customer_Add_New_Product_Quote();
				$obj->womprfq_get_customer_new_product_quote_template_handler();
			}
		}

		/**
		 * List Quotations done by customers
		 */
		public function womprfq_list_quote_template() {
			?>
			<div class="quotation-list-wrapper">
				<h2><?php esc_html_e( 'Requested Quotations List', 'wk-mp-rfq' ); ?></h2>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) . 'add-quote/' ); ?>" class="button add-new-quotation"><?php esc_html_e( 'Add New Product RFQ', 'wk-mp-rfq' ); ?></a>
				<?php
				$quote_columns = array(
					'quote-id'       => esc_html__( 'Quotation Id', 'wk-mp-rfq' ),
					'quote-product'  => esc_html__( 'Product Name', 'wk-mp-rfq' ),
					'quote-quantity' => esc_html__( 'Requested Quantity', 'wk-mp-rfq' ),
					'created-on'     => esc_html__( 'Created On', 'wk-mp-rfq' ),
					'quote-actions'  => esc_html__( 'Actions', 'wk-mp-rfq' ),
				);
				$paged         = ! empty( get_query_var( 'rfq' ) ) ? get_query_var( 'rfq' ) : 1;

				if ( ! is_numeric( get_query_var( 'rfq' ) ) ) {
					$paged = 1;
				}

				$pagenum               = isset( $paged ) ? absint( $paged ) : 1;
				$limit                 = 10;
				$offset                = ( $pagenum == 1 ) ? 0 : ( $pagenum - 1 ) * $limit;
				$user_id               = get_current_user_ID();
				$customer_quotes       = $this->helper->womprfq_get_all_customer_quotation_list( get_current_user_id(), '', $offset, $limit );
				$customer_quotes_count = $this->helper->womprfq_get_all_customer_quotation_count( get_current_user_id(), '' );
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

				if ( ! empty( $customer_quotes ) ) {
					foreach ( $customer_quotes as $customer_quote ) :
						if ( $customer_quote->product_id == 0 ) {
							$quote_d     = $this->helper->womprfq_get_quote_meta_info( $customer_quote->id );
							$quo_product = '';
							if ( isset( $quote_d['pro_name'] ) ) {
								$quo_product = $quote_d['pro_name'];
							}
							$data = array(
								'id'           => $customer_quote->id,
								'product'      => 0,
								'product_name' => $quo_product,
								'quantity'     => $customer_quote->quantity,
								'date'         => $customer_quote->date,
							);
						} else {
							$data = array(
								'id'           => $customer_quote->id,
								'product'      => $customer_quote->product_id,
								'product_name' => get_the_title( $customer_quote->product_id ),
								'quantity'     => $customer_quote->quantity,
								'date'         => $customer_quote->date,
							);
						}
						?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-quote-id" data-title="<?php esc_attr_e( 'Quotation Id', 'wk-mp-rfq' ); ?>">
							<?php echo esc_html( '#' . intval( $data['id'] ) ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-quote-product" data-title="<?php esc_attr_e( 'Product Name', 'wk-mp-rfq' ); ?>">
							<?php echo esc_html( $data['product_name'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-quote-quantity" data-title="<?php esc_attr_e( 'Requested Quantity', 'wk-mp-rfq' ); ?>">
							<?php echo esc_html( $data['quantity'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-created-on" data-title="<?php esc_attr_e( 'Created On', 'wk-mp-rfq' ); ?>">
							<?php echo esc_html( $data['date'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-quote-actions" data-title="<?php esc_attr_e( 'Actions', 'wk-mp-rfq' ); ?>">
							<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) . 'main-quote/' . intval( $data['id'] ) ); ?>"><?php esc_html_e( 'View Quotation', 'wk-mp-rfq' ); ?></a>
						</td>
					</tr>
						<?php
					endforeach;
				} else {
					?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
						<td colspan="5" class="wkmp-nodata-td" id="wkmp-nodata-td" data-title="<?php esc_attr_e( 'Quotation Id', 'wk-mp-rfq' ); ?>">
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
			if ( 1 < $customer_quotes_count[0]->count ) :
				?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination wallet-pagination" style="margin-top:10px;">
					<?php
					if ( 1 !== $paged && $paged > 1 ) :
						?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'rfq', $paged - 1 ) ); ?>">
						<?php esc_html_e( 'Previous', 'wk-mp-rfq' ); ?>
				</a>
					<?php endif; ?>

				<?php if ( ceil( $customer_quotes_count[0]->count / 10 ) > $paged ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'rfq', $paged + 1 ) ); ?>">
					<?php esc_html_e( 'Next', 'wk-mp-rfq' ); ?>
				</a>
			<?php endif; ?>
		</div>
				<?php
	endif;
		}
	}
}
