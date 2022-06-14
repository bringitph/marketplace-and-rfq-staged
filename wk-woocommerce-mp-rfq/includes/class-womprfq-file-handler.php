<?php

use wooMarketplaceRFQ\Includes;

if (!defined('ABSPATH')) {
    exit;
}

require_once WK_MP_RFQ_FILE . 'inc/autoload.php';

if (!class_exists('Womprfq_File_Handler')) {
    /**
     * Create file handler class
     */
    class Womprfq_File_Handler
    {
        /**
         * Class constructor
         */
        public function __construct()
        {
            $this->womprfq_include_files();
        }
        
        /**
         * Include files
         *
         * @return void
         */
        public function womprfq_include_files()
        {
            if (!is_admin()) {
                new Includes\Front\Womprfq_Hook_Handler();
            } else {
                new Includes\Admin\Womprfq_Hook_Handler();
            }
            new Includes\Common\Womprfq_Hook_Handler();
        }
    }
    
}