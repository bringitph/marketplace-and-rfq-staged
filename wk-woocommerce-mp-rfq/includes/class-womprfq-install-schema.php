<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Install_Schema' ) ) {
	/**
	 * Create table class
	 */
	class Womprfq_Install_Schema {

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->womprfq_create_table();
		}

		/**
		 * Create table
		 *
		 * @return void
		 */
		public function womprfq_create_table() {
			 global $wpdb;

			if ( ! function_exists( 'dbDelta' ) ) {
				include_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			$charset_collate = $wpdb->get_charset_collate();

			flush_rewrite_rules();

			global $wp_roles;

			$attribute_table = $wpdb->prefix . 'womprfq_attribute';

			$attribute = "CREATE TABLE IF NOT EXISTS $attribute_table (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`label` varchar(250) NOT NULL,
				`type` varchar(250) NOT NULL,
				`required` int(2) NOT NULL,
				`status` int(2) NOT NULL,
				`created` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $attribute );

			// main quotation table.
			$main_quotation_table = $wpdb->prefix . 'womprfq_main_quotation';

			$main_quotation = "CREATE TABLE IF NOT EXISTS $main_quotation_table (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`product_id` int(15) NOT NULL,
				`variation_id` int(15) DEFAULT 0 NOT NULL,
				`customer_id` int(15) DEFAULT 0 NOT NULL,
				`quantity` int(15) NOT NULL,
				`order_id` int(15),
				`status` int(15) DEFAULT 0 NOT NULL,
				`date` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $main_quotation );

			// main quotation table.
			$main_quotation_table_meta = $wpdb->prefix . 'womprfq_main_quotation_meta';

			$main_quotation_meta = "CREATE TABLE IF NOT EXISTS $main_quotation_table_meta (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`main_quotation_id` bigint(15) NOT NULL,
				`key` varchar(500) NOT NULL,
				`value` longtext NOT NULL,
				`date` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			    ) $charset_collate;";

			dbDelta( $main_quotation_meta );

			// seller quotation table.
			$seller_quotation_table = $wpdb->prefix . 'womprfq_seller_quotation';

			$seller_quoatation = "CREATE TABLE $seller_quotation_table (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`main_quotation_id` int(15) NOT NULL,
				`seller_id` int(15) NOT NULL,
				`quantity` int(15) NOT NULL,
				`price` float NOT NULL,
                 `commission` float NOT NULL,
				`status` int(5) NOT NULL,
				`date` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $seller_quoatation );

			// rfq quotation comment table.
			$seller_quotation_comment_table = $wpdb->prefix . 'womprfq_seller_quotation_comment';

			$seller_quotation_comment = "CREATE TABLE IF NOT EXISTS $seller_quotation_comment_table (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`seller_quotation_id` int(15) NOT NULL,
				`comment_text` longtext NOT NULL,
				`sender_id` int(5) NOT NULL,
				`image` varchar(500) NULL,
				`date` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $seller_quotation_comment );
		}
	}

}
