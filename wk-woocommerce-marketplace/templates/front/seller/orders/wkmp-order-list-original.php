<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-account woocommerce">
	<?php do_action( 'mp_get_wc_account_menu' ); ?>
	<div id="main_container" class="woocommerce-MyAccount-content">

		<form method="post" id="wkmp-order-list-form">
			<div class="wkmp-table-action-wrap">
				<div class="wkmp-action-section left">
					<input type="text" name="wkmp_search" placeholder="<?php esc_attr_e( 'Search by Order ID', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $filter_id ); ?>">
					<?php wp_nonce_field( 'wkmp_order_search_nonce_action', 'wkmp_order_search_nonce' ); ?>
					<input type="submit" value="<?php esc_attr_e( 'Search', 'wk-marketplace' ); ?>" data-action="search"/>
				</div>
			</div>
		</form>
		<div class="wkmp-table-responsive">
			<table class="table table-bordered table-hover">
				<thead>
				<tr>
					<td><?php esc_html_e( 'Order ID', 'wk-marketplace' ); ?></td>
					<td><?php esc_html_e( 'Status', 'wk-marketplace' ); ?></td>
					<td><?php esc_html_e( 'Date', 'wk-marketplace' ); ?></td>
					<td><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></td>
					<td><?php esc_html_e( 'Action', 'wk-marketplace' ); ?></td>
				</tr>
				</thead>
				<tbody>
				<?php if ( $orders ) { ?>
					<?php foreach ( $orders as $key => $seller_order ) { ?>
						<tr>
							<td><?php echo '#' . esc_html( $seller_order['order_id'] ); ?></td>
							<td><?php echo esc_html( ucfirst( $seller_order['order_status'] ) ); ?></td>
							<td><?php echo esc_html( $seller_order['order_date'] ); ?></td>
							<td><?php echo wp_kses_post( $seller_order['order_total'] ); ?></td>
							<td><a href="<?php echo esc_url( $seller_order['view'] ); ?>" class="button" style="padding:12px;"><span class="dashicons dashicons-visibility"></span></a></td>
						</tr>
					<?php } ?>
				<?php } else { ?>
					<tr>
						<td colspan="5" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
					</tr>
				<?php } ?>

				</tbody>
			</table>
		</div><!-- wkmp-overflowx-auot end here-->
		<?php
		echo wp_kses_post( $pagination['results'] );
		echo wp_kses_post( $pagination['pagination'] );
		?>
	</div><!-- woocommerce-myaccount-content end here-->
</div>
