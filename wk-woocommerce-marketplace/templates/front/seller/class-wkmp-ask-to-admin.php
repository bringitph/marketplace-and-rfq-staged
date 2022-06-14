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

if ( ! class_exists( 'WKMP_Ask_To_Admin' ) ) {
	/**
	 * Ask to Admin class.
	 *
	 * Class WKMP_Ask_To_Admin
	 *
	 * @package WkMarketplace\Templates\Front\Seller
	 */
	class WKMP_Ask_To_Admin {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Ask_Queries $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Ask_To_Admin constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->db_obj    = new Common\WKMP_Seller_Ask_Queries();
			$this->seller_id = $seller_id;
			$this->wkmp_seller_queries_list();
		}

		/**
		 * Seller queries list.
		 */
		public function wkmp_seller_queries_list() {
			global $wkmarketplace;
			$url = get_permalink() . get_option( '_wkmp_asktoadmin_endpoint', 'asktoadmin' );

			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();//phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( ! empty( $posted_data['wkmp-sellerAskQuery-nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp-sellerAskQuery-nonce'] ), 'wkmp-sellerAskQuery-nonce-action' ) ) {
					$posted_data['subject'] = str_replace( '\\', '', $posted_data['subject']);
					$posted_data['message'] = str_replace( '\\', '', $posted_data['message']);
					do_action( 'wkmp_save_seller_ask_query', $this->seller_id, $posted_data );
					wc_print_notice( esc_html__( 'Ask to admin query submitted successfully.', 'wk-marketplace' ), 'success' );
				}
			}

			$filter_name = '';
			$get_data    = isset( $_GET ) ? wc_clean( $_GET ) : array();//phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Filter ask queries.
			if ( ! empty( $get_data['wkmp_query_search_nonce'] ) && wp_verify_nonce( wp_unslash( $get_data['wkmp_query_search_nonce'] ), 'wkmp_query_search_nonce_action' ) ) {
				if ( isset( $get_data['wkmp_search'] ) && $get_data['wkmp_search'] ) {
					$filter_name = filter_input( INPUT_GET, 'wkmp_search', FILTER_SANITIZE_STRING );
				}
			}

			$page  = get_query_var( 'pagenum' ) ? get_query_var( 'pagenum' ) : 1;
			$limit = 20;

			$filter_data = array(
				'start'          => ( $page - 1 ) * $limit,
				'limit'          => $limit,
				'filter_subject' => $filter_name,
				'seller_id'      => $this->seller_id,
			);

			$queries       = $this->db_obj->wkmp_get_all_seller_queries( $filter_data );
			$total_queries = $this->db_obj->wkmp_get_total_seller_queries( $filter_data );

			$pagination = $wkmarketplace->wkmp_get_pagination( $total_queries, $page, $limit, $url );

			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'mp_get_wc_account_menu' ); ?>
				<div id="main_container" class="woocommerce-MyAccount-content">

					<form method="GET" id="wkmp-query-list-form">
						<div class="wkmp-table-action-wrap">
							<div class="wkmp-action-section left">
								<input type="text" name="wkmp_search" placeholder="<?php esc_attr_e( 'Search by subject', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $filter_name ); ?>">
								<?php wp_nonce_field( 'wkmp_query_search_nonce_action', 'wkmp_query_search_nonce' ); ?>
								<input type="submit" value="<?php esc_attr_e( 'Search', 'wk-marketplace' ); ?>" data-action="search"/>
							</div>
							<div class="wkmp-action-section right wkmp-text-right">
								<button type="button" class="button" id="wkmp-ask-query" data-modal_src="#wkmp-seller-query-modal" title="<?php esc_attr_e( 'Ask Query', 'wk-marketplace' ); ?>">
									<span class="dashicons dashicons-plus-alt"></span></button>
							</div>
						</div>
					</form>
					<div class="wkmp-table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
							<tr>
								<td><?php esc_html_e( 'Date', 'wk-marketplace' ); ?></td>
								<td><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?></td>
								<td><?php esc_html_e( 'Message', 'wk-marketplace' ); ?></td>
							</tr>
							</thead>
							<tbody>
							<?php if ( $queries ) { ?>
								<?php foreach ( $queries as $query ) { ?>
									<tr>
										<td><?php echo esc_html( gmdate( get_option( 'date_format' ), strtotime( $query->create_date ) ) ); ?></td>
										<td><?php echo esc_html( wp_unslash( $query->subject ) ); ?></td>
										<td><?php echo esc_html( wp_unslash( $query->message ) ); ?></td>
									</tr>
								<?php } ?>
							<?php } else { ?>
								<tr>
									<td colspan="4" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div><!-- wkmp-table-responsive end here-->
					<?php
					echo wp_kses_post( $pagination['results'] );
					echo wp_kses_post( $pagination['pagination'] );
					?>
				</div><!-- woocommerce-my-account-content end here-->
			</div>

			<div id="wkmp-seller-query-modal" class="wkmp-popup-modal">
				<!-- Modal content -->
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"><?php esc_html_e( 'Ask you query', 'wk-marketplace' ); ?></h4>
					</div>
					<div class="modal-body">
						<form action="" method="post" enctype="multipart/form-data" id="wkmp-seller-query-form">
							<div class="form-group">
								<label for="wkmp-subject"><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
								<input class="form-control" type="text" name="subject" placeholder="<?php esc_attr_e( 'Subject', 'wk-marketplace' ); ?>" id="wkmp-subject" value="">
								<div id="wkmp-subject-error" class="text-danger"></div>
							</div>
							<div class="form-group">
								<label for="wkmp-message"><?php esc_html_e( 'Message', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
								<textarea rows="4" name="message" id="wkmp-message" placeholder="<?php esc_attr_e( 'Message', 'wk-marketplace' ); ?>"></textarea>
								<div id="wkmp-message-error" class="text-danger"></div>
							</div>
							<?php wp_nonce_field( 'wkmp-sellerAskQuery-nonce-action', 'wkmp-sellerAskQuery-nonce' ); ?>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="button close-modal"><?php esc_html_e( 'Close', 'wk-marketplace' ); ?></button>
						<button id="wkmp-submit-ask-form" type="button" form="wkmp-seller-query-form" class="button"><?php esc_html_e( 'Submit', 'wk-marketplace' ); ?></button>
					</div>
				</div>

			</div>
			<?php
		}
	}
}
