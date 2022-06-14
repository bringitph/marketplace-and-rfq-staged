<?php

/**
 * Account file.
 */

namespace wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Attribute_Handler' ) ) {
	/**
	 * Class for handle quote.
	 */
	class Womprfq_Attribute_Handler {

		protected $wpdb;

		public function __construct() {
			global $wpdb;
			$this->wpdb       = $wpdb;
			$this->attr_table = $wpdb->prefix . 'womprfq_attribute';
		}

		/**
		 * Returns attribute info
		 *
		 * @param int $search attribute id
		 *
		 * @return $search
		 */
		public function womprfq_get_attribute_info( $search = '' ) {
			$response = array();
			if ( ! empty( $search ) ) {
				$query = $this->wpdb->prepare( "SELECT * FROM $this->attr_table WHERE id =%d", intval( $search ) );
			} else {
				$query = "SELECT * FROM $this->attr_table";
			}

			$response = $this->wpdb->get_results( $query );

			return $response;
		}

		/**
		 * Returns attribute info
		 *
		 * @param array $info attribute info
		 * @param int   $aid  attribute id
		 *
		 * @return void
		 */
		public function wkwooshopify_update_attribute_info( $info, $aid = '' ) {
			$response = array(
				'success' => false,
				'msg'     => esc_html__( 'Unable to update the Attribute.', 'wk-mp-rfq' ),
			);
			if ( $aid ) {
				$res = $this->wpdb->update(
					$this->attr_table,
					$info,
					array(
						'id' => intval( $aid ),
					)
				);
			} else {
				$res = $this->wpdb->insert(
					$this->attr_table,
					$info
				);
			}
			if ( $res ) {
				$response['success'] = true;
				$response['msg']     = esc_html__( 'Updated Successfully', 'wk-mp-rfq' );
			}

			return $response;
		}

		/**
		 * Returns attribute template
		 *
		 * @param int $search attribute id
		 *
		 * @return $search
		 */
		public function womprfq_get_attribute_template() {
			$res        = '';
			$attributes = $this->womprfq_get_attribute_info();
			if ( $attributes ) {
				foreach ( $attributes as $attribute ) {
					if ( $attribute->status == 1 ) {
						$res .= '<div class="wpmp-rfq-form-row">
                                    <label for="' . esc_attr( wc_strtolower( $attribute->label ) ) . '">' . esc_html( $attribute->label );
						if ( $attribute->required == 2 ) {
							$require = 'required="required"';
							$res    .= '<span class="required"> *</span>';
						} else {
							$require = '';
						}
						$res .= '</label><input type="' . esc_attr( $attribute->type ) . '" name="wpmp-rfq-admin-quote-' . esc_attr( wc_strtolower( $attribute->label ) ) . '" ' . esc_html( $require ) . ' >
                                    <div id="wpmp-rfq-quote-' . esc_attr( $attribute->label ) . '-error" class="error-class"></div>
                                </div>';
					}
				}
			}
			return $res;
		}

		public function womprfq_delete_attribute_by_id( $ids ) {
			if ( ! empty( $ids ) ) {
				if ( ! is_array( $ids ) ) {
					$ids = array( $ids );
				}
				foreach ( $ids as $id ) {
					$this->wpdb->delete(
						$this->attr_table,
						array(
							'id' => $id,
						)
					);
				}
			}
		}
	}
}
