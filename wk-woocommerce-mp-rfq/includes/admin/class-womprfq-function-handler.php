<?php
/**
 * This file handles hook.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Includes\Admin;

use wooMarketplaceRFQ\Includes;
use wooMarketplaceRFQ\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Function_Handler' ) ) {
	/**
	 * Load Admin side hooks.
	 */
	class Womprfq_Function_Handler {

		/**
		 * Retister Settings
		 *
		 * @return void.
		 */
		public function womprfq_register_settings() {
			register_setting( 'woo-mp-rfq-settings-group', 'womprfq_status' );
			register_setting( 'woo-mp-rfq-settings-group', 'womprfq_minimum_quantity' );
			register_setting( 'woo-mp-rfq-settings-group', 'womprfq_approval_require' );
		}

		/**
		 * Add menus
		 *
		 * @return void.
		 */
		public function womprfq_add_dashboard_menu() {
			$admin_setting_template = new Templates\Admin\Womprfq_Admin_Settings_Template();

			$admin_template = new Templates\Admin\Womprfq_Admin_Menu_Template();

			$hook = add_menu_page(
				esc_html__( 'Marketplace RFQ', 'wk-mp-rfq' ),
				esc_html__( 'Marketplace RFQ', 'wk-mp-rfq' ),
				'manage_options',
				'wk-mp-rfq',
				array(
					$admin_template,
					'womprfq_get_quotations_list',
				),
				'dashicons-groups',
				55
			);

			$hook2 = add_submenu_page(
				'wk-mp-rfq',
				esc_html__( 'Quotations List', 'wk-mp-rfq' ),
				esc_html__( 'Quotations List', 'wk-mp-rfq' ),
				'manage_options',
				'wk-mp-rfq',
				array(
					$admin_template,
					'womprfq_get_quotations_list',
				)
			);

			$hook2 = add_submenu_page(
				'wk-mp-rfq',
				esc_html__( 'Attributes List', 'wk-mp-rfq' ),
				esc_html__( 'Attributes List', 'wk-mp-rfq' ),
				'manage_options',
				'wc-mp-rfq-attributes',
				array(
					$admin_template,
					'womprfq_get_attribute_list',
				)
			);

			add_submenu_page(
				'wk-mp-rfq',
				esc_html__( 'Settings', 'wk-mp-rfq' ),
				esc_html__( 'Settings', 'wk-mp-rfq' ),
				'manage_options',
				'wc-mp-rfq-setting',
				array(
					$admin_setting_template,
					'womprfq_get_setting_templates',
				)
			);

			add_action(
				"load-$hook",
				array(
					$admin_setting_template,
					'womprfq_add_screen_option',
				)
			);
			add_action(
				"load-$hook2",
				array(
					$admin_setting_template,
					'womprfq_add_screen_option',
				)
			);
		}

		/**
		 * Set page option.
		 *
		 * @param boolean $status
		 * @param string  $option
		 * @param string  $value
		 *
		 * @return boolean
		 */
		public function womprfq_set_option( $status, $option, $value ) {
			if ( 'womprfq_per_page' == $option ) {
				return $value;
			}
			return $status;
		}

		/**
		 * Add screen id
		 *
		 * @param array $array screenid array
		 *
		 * @return array
		 */
		public function womprfq_set_wc_screen_ids( $array ) {
			array_push(
				$array,
				'toplevel_page_wk-mp-rfq'
			);

			return $array;
		}
	}
}
