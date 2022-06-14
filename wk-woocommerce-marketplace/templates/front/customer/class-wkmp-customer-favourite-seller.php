<?php
/**
 * Customer Favourite Seller Class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Customer;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Customer_Favourite_Seller' ) ) {
	/**
	 * Customer Favourite Seller Class.
	 *
	 * Class WKMP_Customer_Favourite_Seller
	 *
	 * @package WkMarketplace\Templates\Front\Customer
	 */
	class WKMP_Customer_Favourite_Seller {
		/**
		 * Customer id.
		 *
		 * @var int $customer_id Customer id.
		 */
		private $customer_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Customer_Favourite_Seller constructor.
		 *
		 * @param int $customer_id Customer id.
		 */
		public function __construct( $customer_id = 0 ) {
			$this->customer_id = $customer_id;

			$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? wc_clean( $_SERVER['REQUEST_METHOD'] ) : '';
			if ( 'POST' === $request_method ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $posted_data['wkmp-delete-favourite-nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['wkmp-delete-favourite-nonce'] ), 'wkmp-delete-favourite-nonce-action' ) ) {
					// Delete favourite seller.
					$this->wkmp_delete_customer_favourite_list( $posted_data['selected'] );
				}
			}

			$seller_id = filter_input( INPUT_GET, 'seller_id', FILTER_SANITIZE_NUMBER_INT );

			// Delete favourite seller.
			if ( $seller_id > 0 ) {
				$this->wkmp_delete_customer_favourite_list( array( $seller_id ) );
			}

			$this->wkmp_display_customer_favourite_list();
		}

		/**
		 * Display Seller List
		 */
		public function wkmp_display_customer_favourite_list() {
			global $wkmarketplace;

			$seller_ids = get_user_meta( $this->customer_id, 'favourite_seller', true );
			$seller_ids = $seller_ids ? explode( ',', $seller_ids ) : array();

			$sellers = array();

			foreach ( $seller_ids as $seller_id ) {
				$seller_info = $wkmarketplace->wkmp_get_seller_info( $seller_id );

				if ( isset( $seller_info->_thumbnail_id_avatar ) && $seller_info->_thumbnail_id_avatar ) {
					$avatar_image = wp_get_attachment_image_src( $seller_info->_thumbnail_id_avatar )[0];
				} else {
					$avatar_image = WKMP_PLUGIN_URL . 'assets/images/generic-male.png';
				}

				$sellers[] = array(
					'seller_id'  => $seller_id,
					'image'      => $avatar_image,
					'name'       => $seller_info->first_name . ' ' . $seller_info->last_name,
					'store_name' => $seller_info->shop_address,
					'store_href' => home_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . $seller_info->shop_address ),
					'delete'     => get_permalink() . 'favourite-seller/?seller_id=' . $seller_id,
				);
			}

			$pagination = $wkmarketplace->wkmp_get_pagination( count( $sellers ), 1, count( $sellers ) ? count( $sellers ) : 20, '' );

			?>
			<div class="wkmp-action-section right wkmp-text-right">
				<button type="button" data-form_id="#wkmp-delete-favourite-seller" class="button wkmp-bulk-delete" title="<?php esc_attr_e( 'Delete', 'wk-marketplace' ); ?>">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>
			<form action="" method="post" enctype="multipart/form-data" id="wkmp-delete-favourite-seller" style="margin-top:10px;margin-bottom:unset;">
				<div class="wkmp-table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
						<tr>
							<th style="width:1px;"><input type="checkbox" id="wkmp-checked-all"></th>
							<th><?php esc_html_e( 'Seller Profile', 'wk-marketplace' ); ?></th>
							<th><?php esc_html_e( 'Seller Name', 'wk-marketplace' ); ?></th>
							<th><?php esc_html_e( 'Seller Collection', 'wk-marketplace' ); ?></th>
							<th><?php esc_html_e( 'Action', 'wk-marketplace' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php if ( $sellers ) { ?>
							<?php foreach ( $sellers as $seller ) { ?>
								<tr>
									<td><input type="checkbox" name="selected[]" value="<?php echo esc_attr( $seller['seller_id'] ); ?>"/></td>
									<td><img src="<?php echo esc_url( $seller['image'] ); ?>" height="50" width="60" class="wkmp-img-thumbnail" style="display:unset;"/></td>
									<td><?php echo esc_html( $seller['name'] ); ?></td>
									<td><a href="<?php echo esc_url( $seller['store_href'] ); ?>"><?php echo esc_html( $seller['store_name'] ); ?></a></td>
									<td><a href="<?php echo esc_url( $seller['delete'] ); ?>" class="button"><span class="dashicons dashicons-trash"></span></a></td>
								</tr>
							<?php } ?>
						<?php } else { ?>
							<tr>
								<td colspan="7" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				<?php wp_nonce_field( 'wkmp-delete-favourite-nonce-action', 'wkmp-delete-favourite-nonce' ); ?>
			</form>
			<?php
			echo wp_kses_post( $pagination['results'] );
			echo wp_kses_post( $pagination['pagination'] );
		}

		/**
		 * Delete seller from customer favourite list
		 *
		 * @param array $seller_ids Seller ids.
		 *
		 * @return void
		 */
		public function wkmp_delete_customer_favourite_list( $seller_ids ) {
			$sellers    = get_user_meta( $this->customer_id, 'favourite_seller', true );
			$sellers    = $sellers ? explode( ',', $sellers ) : array();
			$sellers    = array_map( 'intval', $sellers );
			$seller_ids = array_map( 'intval', $seller_ids );

			if ( $seller_ids ) {
				foreach ( $seller_ids as $seller_id ) {
					$key = array_search( $seller_id, $sellers, true );

					if ( false !== $key ) {
						unset( $sellers[ $key ] );
					}
				}

				delete_user_meta( $this->customer_id, 'favourite_seller' );
				update_user_meta( $this->customer_id, 'favourite_seller', implode( ',', $sellers ) );

				wc_print_notice( esc_html__( 'Seller deleted successfully from your favourite seller list', 'wk-marketplace' ), 'success' );
			}
		}
	}
}
