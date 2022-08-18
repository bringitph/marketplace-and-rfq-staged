<?php
/**
 * Wallet Notification email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/wallet-notification.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 *
 * @version 3.5.2
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

echo '= ' . utf8_decode(esc_html($email_heading)) . " =\n\n";

/* translators: %s Customer email */
echo sprintf(utf8_decode(esc_html__('Hi %s,', 'wk-mp-rfq'), esc_html($customer_email))) . "\n\n";

if (!empty($email_message)) {
    foreach ($email_message as $key => $message) {
        echo utf8_decode(esc_html($message)) . "\n\n";
    }
}

echo utf8_decode(esc_html__('We look forward to seeing you soon.', 'wk-mp-rfq')) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped