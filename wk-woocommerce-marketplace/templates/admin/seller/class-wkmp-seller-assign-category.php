<?php
/**
 * Admin Assign category template class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

use WkMarketplace\Helper as Form;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Seller_Assign_Category' ) ) {
	/**
	 * Admin Assign category template class.
	 *
	 * Class WKMP_Seller_Assign_Category
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Assign_Category {
		/**
		 * Form field builder
		 *
		 * @var object
		 */
		protected $form_helper;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Assign_Category constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->seller_id   = $seller_id;
			$this->form_helper = new Form\WKMP_Form_Field_Builder();
			$this->wkmp_display_assign_category_templates();
		}

		/**
		 * Display assign category templates
		 */
		public function wkmp_display_assign_category_templates() {
			$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? wc_clean( $_SERVER['REQUEST_METHOD'] ) : '';

			if ( 'POST' === $request_method ) {
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array();  //phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $posted_data['_wkmp_submit_seller_misc_settings'] ) ) {
					$enable_order_approval = isset( $posted_data['_wkmp_enable_seller_order_approval'] ) ? $posted_data['_wkmp_enable_seller_order_approval'] : false;
					$enable_dynamic_sku    = isset( $posted_data['_wkmp_enable_seller_dynamic_sku'] ) ? $posted_data['_wkmp_enable_seller_dynamic_sku'] : false;
					$dynamic_sku_prefix    = isset( $posted_data['_wkmp_dynamic_sku_prefix'] ) ? $posted_data['_wkmp_dynamic_sku_prefix'] : '';
					$allow_translate       = isset( $posted_data['_wkmp_wcml_allow_product_translate'] ) ? $posted_data['_wkmp_wcml_allow_product_translate'] : false;

					update_user_meta( $this->seller_id, 'wkmp_seller_allowed_categories', $posted_data['wkmp_seller_allowed_categories'] );
					update_user_meta( $this->seller_id, '_wkmp_enable_seller_order_approval', $enable_order_approval );
					update_user_meta( $this->seller_id, '_wkmp_enable_seller_dynamic_sku', $enable_dynamic_sku );
					update_user_meta( $this->seller_id, '_wkmp_dynamic_sku_prefix', $dynamic_sku_prefix );
					update_user_meta( $this->seller_id, '_wkmp_wcml_allow_product_translate', $allow_translate );
					?>
					<div class="notice notice-success my-acf-notice is-dismissible">
						<p><?php esc_html_e( 'Settings are saved successfully.', 'wk-marketplace' ); ?></p>
					</div>
					<?php
				}
			}

			$categories         = array();
			$product_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );

			foreach ( $product_categories as $value ) {
				$categories[ $value->slug ] = $value->name;
			}
			?>
			<form method="POST" action="">
				<?php
				$form_fields = array(
					'entry' => array(
						'fields' => array(
							'_wkmp_seller_category_options' => array(
								'title'       => __( 'Product Category', 'wk-marketplace' ),
								'type'        => 'section_start',
								'description' => '',
							),
							'wkmp_seller_allowed_categories' => array(
								'type'        => 'multi-select',
								'label'       => esc_html__( 'Allowed Categories', 'wk-marketplace' ),
								'options'     => $categories,
								'description' => 'Allowed Categories for a seller to add products.',
								'value'       => get_user_meta( $this->seller_id, 'wkmp_seller_allowed_categories', true ),
								'placeholder' => esc_html__( 'Select Categories', 'wk-marketplace' ) . '...',
							),
							'_wkmp_seller_order_approval' => array(
								'title'       => __( 'Order Approval', 'wk-marketplace' ),
								'type'        => 'section_start',
								'description' => '',
							),
							'_wkmp_enable_seller_order_approval' => array(
								'type'        => 'checkbox',
								'label'       => esc_html__( 'Enable', 'wk-marketplace' ),
								'description' => esc_html__( 'If Checked, seller can see order and get notification only after manual approval by Admin.', 'wk-marketplace' ),
								'value'       => get_user_meta( $this->seller_id, '_wkmp_enable_seller_order_approval', true ),
							),
							'_wkmp_seller_dynamic_sku'    => array(
								'title'       => __( 'Dynamic SKU', 'wk-marketplace' ),
								'type'        => 'section_start',
								'description' => '',
							),
							'_wkmp_enable_seller_dynamic_sku' => array(
								'type'        => 'checkbox',
								'label'       => esc_html__( 'Enable', 'wk-marketplace' ),
								'description' => esc_html__( 'If Checked, below prefix will be prefixed with seller actual SKU.', 'wk-marketplace' ),
								'value'       => get_user_meta( $this->seller_id, '_wkmp_enable_seller_dynamic_sku', true ),
							),
							'_wkmp_dynamic_sku_prefix'    => array(
								'type'        => 'input',
								'label'       => esc_html__( 'Product SKU Prefix', 'wk-marketplace' ),
								'description' => esc_html__( 'Prefix to seller\'s SKU.', 'wk-marketplace' ),
								'value'       => get_user_meta( $this->seller_id, '_wkmp_dynamic_sku_prefix', true ),
							),
						),
					),
				);

				if ( defined( 'WCML_VERSION' ) && version_compare( WCML_VERSION, '4.12.0', '>' ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
					$form_fields['entry']['fields']['_wkmp_wcml_translate_product'] = array(
						'title'       => __( 'Translate Products', 'wk-marketplace' ),
						'type'        => 'section_start',
						'description' => esc_html__( 'Disabling this setting will not work if it is enabled globally from Product options in Marketplace settings.', 'wk-marketplace' ),
					);

					$form_fields['entry']['fields']['_wkmp_wcml_allow_product_translate'] = array(
						'type'        => 'checkbox',
						'label'       => esc_html__( 'Allow seller to translate products', 'wk-marketplace' ),
						'description' => esc_html__( 'If Checked, Seller can translate their products from their backend dashboard.', 'wk-marketplace' ),
						'value'       => get_user_meta( $this->seller_id, '_wkmp_wcml_allow_product_translate', true ),
					);

				}

				$this->form_helper->wkmp_form_field_builder( $form_fields );
				submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary', '_wkmp_submit_seller_misc_settings' );
				?>
			</form>
			<?php
		}
	}
}
