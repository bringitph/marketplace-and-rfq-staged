<?php
/**
 * Admin Seller commission template class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

use WkMarketplace\Helper;
use WkMarketplace\Helper\Admin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Seller_Commission' ) ) {
	/**
	 * Admin Seller commission template class.
	 *
	 * Class WKMP_Seller_Commission
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Commission {
		/**
		 * Form field builder
		 *
		 * @var object
		 */
		protected $form_helper;

		/**
		 * Seller DB variable
		 *
		 * @var object
		 */
		protected $seller_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Commission constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->seller_id   = $seller_id;
			$this->form_helper = new Helper\WKMP_Form_Field_Builder();
			$this->seller_obj  = new Admin\WKMP_Seller_Data();

			$this->wkmp_display_commission_templates();
		}

		/**
		 * Commission templates.
		 */
		public function wkmp_display_commission_templates() {
			$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? wc_clean( $_SERVER['REQUEST_METHOD'] ) : '';
			if ( 'POST' === $request_method ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $posted_data['wkmp_seller_commission'] ) && isset( $posted_data['submit'] ) ) {
					$commission = $posted_data['wkmp_seller_commission'];
					if ( empty( $commission ) || ( is_numeric( $commission ) && $commission >= 0 && $commission <= 100 ) ) {
						do_action( 'wkmp_save_seller_commission', $posted_data, $this->seller_id );
						?>
						<div class="notice notice-success my-acf-notice is-dismissible">
							<p><?php esc_html_e( 'Commission saved successfully.', 'wk-marketplace' ); ?></p>
						</div>
						<?php
					} else {
						?>
						<div class="notice notice-error my-acf-notice is-dismissible">
							<p><?php echo sprintf( /* translators: %s: Commission. */ esc_html__( 'Invalid default commission value %s. Must be between 0 & 100.', 'wk-marketplace' ), esc_attr( $commission ) ); ?></p>
						</div>
						<?php
					}
				}
			}

			$cur_symbol         = get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) );
			$commission_info    = $this->seller_obj->wkmp_get_seller_commission_info( $this->seller_id );
			$default_commission = empty( $commission_info->commision_on_seller ) ? get_option( '_wkmp_default_commission', 0 ) : $commission_info->commision_on_seller;

			$form_fields = array(
				'entry' => array(
					'fields' => array(
						'wkmp_seller_commission'      => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Commission Rate (in %)', 'wk-marketplace' ),
							'description' => '',
							'value'       => $commission_info->commision_on_seller,
							'placeholder' => esc_html__( 'Enter a positive number upto 100.', 'wk-marketplace' ) . '...',
						),
						'wkmp_total_sale'             => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Total Sale', 'wk-marketplace' ),
							'description' => '',
							'value'       => ( $commission_info->seller_total_ammount + $commission_info->admin_amount ) . esc_attr( $cur_symbol ),
							'readonly'    => 'readonly',
							'placeholder' => esc_html__( 'Total Sale', 'wk-marketplace' ) . '...',
						),
						'wkmp_total_admin_commission' => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Total Admin Commission', 'wk-marketplace' ),
							'description' => '',
							'value'       => $commission_info->admin_amount . esc_attr( $cur_symbol ),
							'readonly'    => 'readonly',
							'placeholder' => esc_html__( 'Total Admin Commission', 'wk-marketplace' ) . '...',
						),
						'wkmp_existing_commission'    => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Existing Commission (in %)', 'wk-marketplace' ),
							'description' => '',
							'value'       => $default_commission,
							'readonly'    => 'readonly',
							'placeholder' => '',
						),
					),
				),
			);
			?>
			<div class="wrap">
				<h1> <?php esc_html_e( 'Set Seller Commission', 'wk-marketplace' ); ?> </h1>
				<p></p>
				<hr>
				<form action='' method='post' class="form-table" name='commision-form'>
					<?php $this->form_helper->wkmp_form_field_builder( $form_fields ); ?>
					<?php submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary' ); ?>
				</form>
			</div>
			<?php
		}
	}
}
