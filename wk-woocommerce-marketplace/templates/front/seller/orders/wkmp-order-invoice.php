<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

extract( $data ); ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php esc_html_e( 'Seller Order Invoice', 'wk-marketplace' ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( WKMP_PLUGIN_URL ) . 'assets/admin/build/css/invoice-style.css'; ?>">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
<div class="mp-invoice-wrapper">
	<button onclick="javascript:window.print()"><i class="fa fa-print"></i></button>
	<h1><?php echo sprintf( /* translators: %d: Order number. */ esc_html__( 'Seller Order Invoice Of Order #%d', 'wk-marketplace' ), esc_html( $order_id ) ); ?></h1>
	<table class="table table-bordered">
		<thead>
		<tr>
			<td colspan="2"><b><?php esc_html_e( 'Order Information', 'wk-marketplace' ); ?></b></td>
		</tr>
		</thead>

		<tbody>
		<tr>
			<td>
				<b><?php echo esc_html( $seller_info->shop_name ); ?></b><br>
				<?php echo esc_html( $seller_info->first_name . ' ' . $seller_info->last_name ); ?><br>
				<?php echo esc_html( $seller_info->billing_city . ' , ' . $seller_info->billing_country ); ?><br>
				<b><?php esc_html_e( 'Email :', 'wk-marketplace' ); ?></b><?php echo esc_html( $seller_info->user_email ); ?><br>
				<b><?php esc_html_e( 'Profile Link :', 'wk-marketplace' ); ?></b>
				<a href="<?php echo esc_url( $store_url ); ?>" target="_blank"><?php echo esc_url( $store_url ); ?></a>
			</td>
			<td>
				<b><?php esc_html_e( 'Order Date :', 'wk-marketplace' ); ?></b><?php echo esc_html( $date_created ); ?><br>
				<b><?php esc_html_e( 'Order ID :', 'wk-marketplace' ); ?> </b><?php echo esc_html( $order_id ); ?><br>
				<b><?php esc_html_e( 'Payment Method :', 'wk-marketplace' ); ?></b><?php echo esc_html( $payment_method ); ?><br>
				<b><?php esc_html_e( 'Shipping Method :', 'wk-marketplace' ); ?></b><?php echo esc_html( $shipping_method ); ?><br>
			</td>
		</tr>
		</tbody>
	</table>

	<table class="table table-bordered">
		<tbody>
		<tr>
			<td colspan="2"><b><?php esc_html_e( 'Buyer Details', 'wk-marketplace' ); ?></b></td>
		</tr>
		<tr>
			<td><b><?php esc_html_e( 'Name', 'wk-marketplace' ); ?></b></td>
			<td data-title="<?php esc_attr_e( 'Name', 'wk-marketplace' ); ?>"><?php echo esc_html( $customer_details['name'] ); ?></td>
		</tr>
		<tr>
			<td><b><?php esc_html_e( 'Email', 'wk-marketplace' ); ?></b></td>
			<td data-title="<?php esc_attr_e( 'Email', 'wk-marketplace' ); ?>"><?php echo esc_html( $customer_details['email'] ); ?></td>
		</tr>
		<tr class="alt-table-row">
			<td><b><?php esc_html_e( 'Telephone', 'wk-marketplace' ); ?></b></td>
			<td data-title="<?php esc_attr_e( 'Telephone', 'wk-marketplace' ); ?>"><?php echo esc_html( $customer_details['telephone'] ); ?></td>
		</tr>
		</tbody>
	</table>

	<table class="table table-bordered">
		<thead>
		<tr>
			<td style="width: 50%;"><b><?php esc_html_e( 'Billing Address', 'wk-marketplace' ); ?></b></td>
			<td style="width: 50%;"><b><?php esc_html_e( 'Shipping Address', 'wk-marketplace' ); ?></b></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				<address> <?php echo wp_kses_post( $billing_address ); ?> </address>
			</td>
			<td>
				<address> <?php echo wp_kses_post( $shipping_address ); ?> </address>
			</td>
		</tr>
		</tbody>
	</table>

	<table class="table table-bordered">
		<thead>
		<tr>
			<td class="text-left"><b><?php esc_html_e( 'Product', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><b><?php esc_html_e( 'Quantity', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><b><?php esc_html_e( 'Unit Price', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><b><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></b></td>
		</tr>
		</thead>

		<tbody>
		<?php
		foreach ( $ordered_products as $key => $product ) {
			?>
			<tr>
				<td><?php echo esc_html( $product['product_name'] ); ?>
					<dl class="variation">
						<?php foreach ( $product['meta_data'] as $value ) { ?>
							<?php if ( '_reduced_stock' !== $value['key'] && 'Sold By' === $value['key'] ) { ?>
								<dt class="variation-size"><?php echo esc_html( $value['key'] ) . ' : ' . wp_kses_post( $value['value'] ); ?></dt>
							<?php } ?>
						<?php } ?>
					</dl>
				</td>
				<td class="text-right"><?php echo esc_html( $product['quantity'] ); ?></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . $product['unit_price'] ); ?></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . $product['total_price'] ); ?></td>
			</tr>
		<?php } ?>

		<tr>
			<td class="text-right" colspan="3"><b><?php esc_html_e( 'SubTotals', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><?php echo esc_html( $currency_symbol . $sub_total ); ?></td>
		</tr>
		<?php
		foreach ( $seller_order->get_items( 'fee' ) as $item_id => $item_fee ) {
			$fee_name   = $item_fee->get_name();
			$fee_amount = $item_fee->get_total();
			?>
		<tr>
			<td class="text-right" colspan="3"><b><?php echo apply_filters( 'wkmp_seller_order_fee_name', $fee_name ); ?></b></td>
			<td class="text-right"><?php echo esc_html( $currency_symbol . $fee_amount ); ?></td>
		</tr>
			<?php
		}
		if ( isset( $shipping_cost ) ) {
			?>
			<tr>
				<td class="text-right" colspan="3"><b><?php esc_html_e( 'Shipping', 'wk-marketplace' ); ?></b></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . $shipping_cost ); ?></td>
			</tr>
		<?php }; ?>

		<?php if ( ! empty( $seller_order_tax ) ) { ?>
			<tr>
				<td class="text-right" colspan="3"><b><?php esc_html_e( 'Tax', 'wk-marketplace' ); ?></b></td>
				<td class="text-right"><?php echo wp_kses_data( wc_price( $seller_order_tax, $cur_symbol ) ); ?></td>
			</tr>
		<?php } ?>

		<tr>
			<td class="text-right" colspan="3"><b><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></b></td>
			<?php
			if ( ! empty( $refund_data['refunded_amount'] ) ) {

				?>
				<td class="text-right"><strong>
						<del><?php echo esc_html( $currency_symbol . $subtotal_refunded ); ?></del>
					</strong><?php echo esc_html( $currency_symbol . apply_filters( 'wkmp_add_order_fee_to_total', $order_id, round( floatval( $total ), 2 ) ) ); ?></td>
			<?php } else { ?>
				<td class="text-right"><?php echo esc_html( $currency_symbol . apply_filters( 'wkmp_add_order_fee_to_total', $order_id, $total ) ); ?></td>
			<?php } ?>
		</tr>
		<?php if ( ! empty( $refund_data['refunded_amount'] ) ) { ?>
			<tr>
				<td class="text-right" colspan="3"><b><?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?></b></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . wc_format_decimal( $refund_data['refunded_amount'], 2 ) ); ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
</body>
</html>
