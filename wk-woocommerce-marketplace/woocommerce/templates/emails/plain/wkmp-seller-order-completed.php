<?php
/**
 * Email templates
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once WKMP_PLUGIN_FILE . 'helper/common/class-wkmp-commission.php';

use WkMarketplace\Helper\Common;

$seller_order   = is_array( $data ) ? new WC_Order( $data[0]->get_order_id() ) : new WC_Order( $data->get_order_id() ); //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$commission_obj = new Common\WKMP_Commission();

$reward_point_weightage   = ! empty( $GLOBALS['reward'] ) ? $GLOBALS['reward']->get_woocommerce_reward_point_weightage() : 0;
$seller_id                = get_user_by( 'email', $customer_email )->ID;
$order_detail_by_order_id = array();

if ( is_array( $data ) ) {
	foreach ( $data as $key => $value ) {
		$product_id  = $value->get_product_id();
		$variable_id = $value->get_variation_id();
		$item_data   = array();
		$meta_dat    = $value->get_meta_data();
		$qty         = $value->get_data()['quantity'];

		$product_total_price = $value->get_data()['subtotal'];
		if ( ! empty( $meta_dat ) && is_iterable( $meta_dat ) ) {
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
			'tax'                 => $value->get_total_tax(),
		);
	}
} else {
	$product_id          = $data->get_product_id();
	$variable_id         = $data->get_variation_id();
	$item_data           = array();
	$meta_dat            = $data->get_meta_data();
	$qty                 = $data->get_data()['quantity'];
	$product_total_price = $data->get_data()['subtotal'];

	if ( ! empty( $meta_dat ) && is_iterable( $meta_dat ) ) {
		foreach ( $meta_dat as $key1 => $value1 ) {
			$item_data[] = $meta_dat[ $key1 ]->get_data();
		}
	}

	$order_detail_by_order_id[ $product_id ][] = array(
		'product_name'        => $data['name'],
		'qty'                 => $qty,
		'variable_id'         => $variable_id,
		'product_total_price' => $product_total_price,
		'meta_data'           => $item_data,
		'tax'                 => $data->get_total_tax(),
	);
}

$com_data = $commission_obj->wkmp_get_seller_final_order_info( $seller_order->get_id(), $seller_id );

$subtotal      = 0;
$total_tax     = 0;
$total_payment = 0;

$fees = $seller_order->get_fees();

$total_discount  = $seller_order->get_total_discount();
$shipping_method = $seller_order->get_shipping_method();
$payment_method  = $seller_order->get_payment_method_title();

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_header', $email_heading, $email );

$date = $seller_order->get_date_created();

$arr_day = array(
	'Monday'    => html_entity_decode( esc_html__( 'Monday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'Tuesday'   => html_entity_decode( esc_html__( 'Tuesday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'Wednesday' => html_entity_decode( esc_html__( 'Wednesday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'Thursday'  => html_entity_decode( esc_html__( 'Thursday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'Friday'    => html_entity_decode( esc_html__( 'Friday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'Saturday'  => html_entity_decode( esc_html__( 'Saturday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'Sunday'    => html_entity_decode( esc_html__( 'Sunday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
);

$arr_month = array(
	'January'   => html_entity_decode( esc_html__( 'January', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'February'  => html_entity_decode( esc_html__( 'February', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'March'     => html_entity_decode( esc_html__( 'March', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'April'     => html_entity_decode( esc_html__( 'April', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'May'       => html_entity_decode( esc_html__( 'May', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'June'      => html_entity_decode( esc_html__( 'June', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'July'      => html_entity_decode( esc_html__( 'July', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'August'    => html_entity_decode( esc_html__( 'August', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'September' => html_entity_decode( esc_html__( 'September', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'October'   => html_entity_decode( esc_html__( 'October', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'November'  => html_entity_decode( esc_html__( 'November', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
	'December'  => html_entity_decode( esc_html__( 'December', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
);

$order_day   = gmdate( 'l', strtotime( $date ) );
$order_month = gmdate( 'F', strtotime( $date ) );

$date_string = $arr_day[ $order_day ] . ', ' . $arr_month[ $order_month ] . ' ' . gmdate( 'j, Y', strtotime( $date ) );

echo sprintf( /* translators: %s: Login URL. */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_attr( utf8_decode( $loginurl ) ) ) . "\n\n";

$result = utf8_decode( esc_html__( 'We have finished processing your order.', 'wk-marketplace' ) ) . '&nbsp;' . utf8_decode( $seller_order->get_formatted_billing_full_name() ) . "\n\n" . 'Order #' . $seller_order->get_ID() . ' (' . $date_string . ') ' . "\n\n";

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
