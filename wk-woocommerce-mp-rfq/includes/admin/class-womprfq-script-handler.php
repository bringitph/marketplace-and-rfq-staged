<?php
/**
 * Load scripts.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Admin;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Womprfq_Script_Handler')) {
    /**
     * Class loads scripts.
     */
    class Womprfq_Script_Handler
    {
        /**
         * Contructor function.
         */
        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'womprfq_enqueue_scripts_admin'));
        }

        /**
         * Admin scripts and style enqueue.
         */
        public function womprfq_enqueue_scripts_admin()
        {
            wp_enqueue_media();
            wp_enqueue_style(
                'womprfq-admin-style', 
                WK_MP_RFQ_URL.'assets/css/admin.css'
            );   
            wp_enqueue_script(
                'womprfq-admin-page-script', 
                WK_MP_RFQ_URL.'assets/js/admin.js', 
                WK_MP_RFQ_SCRIPT_VERSION
            );
            wp_localize_script(
                'womprfq-admin-page-script', 
                'womprfq_script_obj', 
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'admin_ajax_nonce' => wp_create_nonce('womprfq_admin_ajax_nonce'),
                    'rfq_trans_arr' => array(
                        'rfq1' => esc_html__('Select Image', 'wk-mp-rfq'),
                        'rfq2' => esc_html__('Done', 'wk-mp-rfq'),
                    ),
                )
            );
        }
    }
}