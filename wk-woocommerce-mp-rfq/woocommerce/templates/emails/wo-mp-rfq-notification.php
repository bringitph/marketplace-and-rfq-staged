<?php
/**
 * Wallet Notification email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/wallet-notification.php.
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

?>

<?php do_action('woocommerce_email_header', $email_heading, $email);?>

<?php /* translators: %s Customer email */?>

<!--JS edit. New Emails - part 1.23-->
<?php $user  = get_user_by('email', $customer_email);  ?>

<p><?php printf(utf8_decode(esc_html__('Hi %s,', 'wk-mp-rfq')), utf8_decode(esc_html($user->data->user_login))); ?></p>

<?php

if (!empty($email_message)) {
    foreach ($email_message as $key => $message) {
        ?>
        
        <!-- JS edit. New Emails - part 1.24 -->
        <?php echo utf8_decode($message); ?>
        
    <?php
    }
}

?>

<p><?php echo utf8_decode(esc_html__('We look forward to seeing you soon.', 'wk-mp-rfq'));?></p>

<?php
do_action('woocommerce_email_footer', $email);