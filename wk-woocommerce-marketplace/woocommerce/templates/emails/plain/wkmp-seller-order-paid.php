<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;
global $wkmarketplace;

$seller_order = ! empty( $data['order_id'] ) ? wc_get_order( $data['order_id'] ) : new stdClass();
$seller_items = ( $seller_order instanceof WC_Order ) ? $seller_order->get_items() : array();
$seller_id    = ! empty( $data['seller_id'] ) ? $data['seller_id'] : 0;

foreach ( $seller_items as $key => $value ) {
	$product_id = $value->get_product_id();
	$author_id  = get_post_field( 'post_author', $product_id );

	if ( intval( $author_id ) !== intval( $seller_id ) ) {
		continue;
	}
	$variable_id         = $value->get_variation_id();
	$item_data           = array();
	$meta_dat            = $value->get_meta_data();
	$seller_post         = get_post( $product_id );
	$qty                 = $value->get_data()['quantity'];
	$product_total_price = $value->get_data()['subtotal'];

	if ( ! empty( $meta_dat ) ) {
		foreach ( $meta_dat as $key1 => $value1 ) {
			$item_data[] = $meta_dat[ $key1 ]->get_data();
		}
	}

	$order_detail_by_order_id[ $product_id ][] = array(
		'product_name'        => $value['name'],
		'qty'                 => $qty,
		'variable_id'         => $variable_id,
		'product_total_price' => $product_total_price,
		'meta_data'           => $item_data,
	);
}

$total_payment   = 0;
$shipping_method = $seller_order->get_shipping_method();
$payment_method  = $seller_order->get_payment_method_title();
$fees            = $seller_order->get_fees();
$total_discount  = $seller_order->get_total_discount();

echo '= ' . utf8_decode( esc_html( $email_heading ) ) . " =\n\n"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$date = $seller_order->get_date_created();

$arr_day = array(
	'Monday'    => utf8_decode( esc_html__( 'Monday', 'wk-marketplace' ) ),
	'Tuesday'   => utf8_decode( esc_html__( 'Tuesday', 'wk-marketplace' ) ),
	'Wednesday' => utf8_decode( esc_html__( 'Wednesday', 'wk-marketplace' ) ),
	'Thursday'  => utf8_decode( esc_html__( 'Thursday', 'wk-marketplace' ) ),
	'Friday'    => utf8_decode( esc_html__( 'Friday', 'wk-marketplace' ) ),
	'Saturday'  => utf8_decode( esc_html__( 'Saturday', 'wk-marketplace' ) ),
	'Sunday'    => utf8_decode( esc_html__( 'Sunday', 'wk-marketplace' ) ),
);

$arr_month = array(
	'January'   => utf8_decode( esc_html__( 'January', 'wk-marketplace' ) ),
	'February'  => utf8_decode( esc_html__( 'February', 'wk-marketplace' ) ),
	'March'     => utf8_decode( esc_html__( 'March', 'wk-marketplace' ) ),
	'April'     => utf8_decode( esc_html__( 'April', 'wk-marketplace' ) ),
	'May'       => utf8_decode( esc_html__( 'May', 'wk-marketplace' ) ),
	'June'      => utf8_decode( esc_html__( 'June', 'wk-marketplace' ) ),
	'July'      => utf8_decode( esc_html__( 'July', 'wk-marketplace' ) ),
	'August'    => utf8_decode( esc_html__( 'August', 'wk-marketplace' ) ),
	'September' => utf8_decode( esc_html__( 'September', 'wk-marketplace' ) ),
	'October'   => utf8_decode( esc_html__( 'October', 'wk-marketplace' ) ),
	'November'  => utf8_decode( esc_html__( 'November', 'wk-marketplace' ) ),
	'December'  => utf8_decode( esc_html__( 'December', 'wk-marketplace' ) ),
);

$order_day   = gmdate( 'l', strtotime( $date ) );
$order_month = gmdate( 'F', strtotime( $date ) );

$date_string = $arr_day[ $order_day ] . ', ' . $arr_month[ $order_month ] . ', ' . gmdate( 'j, Y', strtotime( $date ) );

echo sprintf( /* translators: %s: Login URL. */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_attr( utf8_decode( $loginurl ) ) ) . "\n\n";

$result = utf8_decode( esc_html__( 'Your following order has been approved.', 'wk-marketplace' ) ) . '&nbsp;' . utf8_decode( $seller_order->get_formatted_billing_full_name() ) . "\n\n" . 'Order #' . $seller_order->get_ID() . ' (' . $date_string . ') ' . "\n\n";

foreach ( $order_detail_by_order_id as $product_id => $details ) {
	$product  = new WC_Product( $product_id );
	$detail_c = 0;
	if ( count( $details ) > 0 ) {
		$detail_c = count( $details );
	}
	for ( $i = 0; $i < $detail_c; ++ $i ) {
		$total_payment = floatval( $total_payment ) + floatval( $details[ $i ]['product_total_price'] ) + floatval( $seller_order->get_total_shipping() );
		if ( 0 === intval( $details[ $i ]['variable_id'] ) ) {
			$result .= utf8_decode( $details[ $i ]['product_name'] ) . utf8_decode( esc_html__( 'SKU: ', 'wk-marketplace' ) ) . $wkmarketplace->wkmp_get_sku( $product ) . ' X ' . $details[ $i ]['qty'] . ' = ' . $seller_order->get_currency() . ' ' . $details[ $i ]['product_total_price'] . "\n\n";
		} else {
			$attribute = $product->get_attributes();

			$attribute_name = '';
			foreach ( $attribute as $key => $value ) {
				$attribute_name = $value['name'];
			}
			$result .= utf8_decode( $details[ $i ]['product_name'] ) . ' (' . utf8_decode( esc_html__( 'SKU: ', 'wk-marketplace' ) ) . $wkmarketplace->wkmp_get_sku( $product ) . ' )';
			if ( ! empty( $details[ $i ]['meta_data'] ) ) {
				foreach ( $details[ $i ]['meta_data'] as $m_data ) {
					if ( 'Sold By' === $m_data['key'] ) {
						$result .= '(' . wc_attribute_label( $m_data['key'] ) . ' : ' . strtoupper( $m_data['value'] ) . ')';
					}
				}
			}

			$result .= ' X ' . $details[ $i ]['qty'] . ' = ' . $seller_order->get_currency() . ' ' . $details[ $i ]['product_total_price'] . "\n\n";
		}
	}
}

if ( ! empty( $total_discount ) ) {
	$total_payment -= $total_discount;
	$result        .= utf8_decode( esc_html__( 'Discount', 'wk-marketplace' ) ) . ' : -' . wc_price( $total_discount, array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";
}

if ( ! empty( $shipping_method ) ) :
	$result .= utf8_decode( esc_html__( 'Shipping', 'wk-marketplace' ) ) . ' : ' . wc_price( ( $seller_order->get_total_shipping() ? $seller_order->get_total_shipping() : 0 ), array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";
endif;

$total_fee_amount = 0;

if ( ! empty( $fees ) ) {
	foreach ( $fees as $key => $fee ) {
		$fee_name   = $fee->get_data()['name'];
		$fee_amount = floatval( $fee->get_data()['total'] );

		$total_fee_amount += $fee_amount;

		$result .= utf8_decode( $fee_name ) . ' : ' . wc_price( $fee_amount, array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";
	}
}

$total_payment += $total_fee_amount;

if ( ! empty( $payment_method ) ) :
	$result .= utf8_decode( esc_html__( 'Payment Method', 'wk-marketplace' ) ) . ' : ' . $payment_method . "\n\n";
endif;

$result .= utf8_decode( esc_html__( 'Total', 'wk-marketplace' ) ) . ' : ' . wc_price( $total_payment, array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";

$text_align = is_rtl() ? 'right' : 'left';

$result .= utf8_decode( esc_html__( 'Billing address', 'wk-marketplace' ) ) . ' : ' . "\n\n";

foreach ( $seller_order->get_address( 'billing' ) as $add ) {
	if ( $add ) {
		$result .= utf8_decode( $add ) . "\n";
	}
}
if ( ! wc_ship_to_billing_address_only() && $seller_order->needs_shipping_address() ) :
	$shipping = '';
	if ( $seller_order->get_formatted_shipping_address() ) :
		$shipping = utf8_decode( $seller_order->get_formatted_shipping_address() );
	endif;

	if ( ! empty( $shiping ) ) {
		$result .= utf8_decode( esc_html__( 'Shipping address', 'wk-marketplace' ) ) . ' : ' . "\n\n";
		foreach ( $seller_order->get_address( 'billing' ) as $add ) {
			if ( $add ) {
				$result .= utf8_decode( $add ) . "\n";
			}
		}
	}
endif;

echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $email );
